<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['estado' => false, 'mensaje' => 'No autorizado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['estado' => false, 'mensaje' => 'Método no permitido.']);
    exit();
}

$campos = ['codigoIngresoProduccion', 'codigoCalzadoExistente', 'colorCalzado', 'tallaCalzado', 'cantidadCalzado', 'fechaProduccion'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
        http_response_code(400);
        echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos de producción.']);
        exit();
    }
}

$codigoIngreso = trim($_POST['codigoIngresoProduccion']);
$codigoCalzado = trim($_POST['codigoCalzadoExistente']);
$color = trim($_POST['colorCalzado']);
$talla = trim($_POST['tallaCalzado']);
$cantidad = trim($_POST['cantidadCalzado']);
$fechaProduccion = trim($_POST['fechaProduccion']);
$fechaValidada = DateTime::createFromFormat('!Y-m-d', $fechaProduccion);

if (
    !ctype_digit($codigoIngreso) || (int) $codigoIngreso < 1 ||
    !ctype_digit($codigoCalzado) || (int) $codigoCalzado < 1 ||
    $color === '' || !is_numeric($talla) || (float) $talla <= 0 ||
    !ctype_digit($cantidad) || (int) $cantidad < 1 ||
    !$fechaValidada || $fechaValidada->format('Y-m-d') !== $fechaProduccion
) {
    http_response_code(400);
    echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos de producción.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    $conn->set_charset('utf8mb4');
    $conn->begin_transaction();

    $codigoIngreso = (int) $codigoIngreso;
    $codigoCalzado = (int) $codigoCalzado;
    $talla = (float) $talla;
    $cantidad = (int) $cantidad;

    $consultaExistencia = $conn->prepare('SELECT ID_Calzado FROM calzado WHERE ID_Calzado = ?');
    $consultaExistencia->bind_param('i', $codigoCalzado);
    $consultaExistencia->execute();
    if ($consultaExistencia->get_result()->num_rows === 0) {
        throw new InvalidArgumentException('El modelo seleccionado no existe.');
    }
    $consultaExistencia->close();

    $consultaRegistro = $conn->prepare(
        'SELECT ID_Calzado, idColor, Talla, Cantidad
         FROM ingresoproduccion
         WHERE codigoIngreso = ?
         FOR UPDATE'
    );
    $consultaRegistro->bind_param('i', $codigoIngreso);
    $consultaRegistro->execute();
    $registroAnterior = $consultaRegistro->get_result()->fetch_assoc();
    $consultaRegistro->close();

    if (!$registroAnterior) {
        throw new OutOfBoundsException('No se encontró el registro de producción.');
    }

    $idColor = verificarColorProduccion($conn, $color);
    $mismaCombinacion = (int) $registroAnterior['ID_Calzado'] === $codigoCalzado
        && (int) $registroAnterior['idColor'] === $idColor
        && (float) $registroAnterior['Talla'] === $talla;

    if ($mismaCombinacion) {
        $diferenciaCantidad = $cantidad - (int) $registroAnterior['Cantidad'];
        if ($diferenciaCantidad !== 0) {
            ajustarInventarioProduccion(
                $conn,
                (int) $registroAnterior['ID_Calzado'],
                (int) $registroAnterior['idColor'],
                (float) $registroAnterior['Talla'],
                $diferenciaCantidad
            );
        }
    } else {
        ajustarInventarioProduccion(
            $conn,
            (int) $registroAnterior['ID_Calzado'],
            (int) $registroAnterior['idColor'],
            (float) $registroAnterior['Talla'],
            -(int) $registroAnterior['Cantidad']
        );
        ajustarInventarioProduccion($conn, $codigoCalzado, $idColor, $talla, $cantidad);
    }

    $actualizarRegistro = $conn->prepare(
        'UPDATE ingresoproduccion
         SET ID_Calzado = ?, idColor = ?, Talla = ?, Cantidad = ?, fechaProduccion = ?
         WHERE codigoIngreso = ?'
    );
    $actualizarRegistro->bind_param(
        'iidisi',
        $codigoCalzado,
        $idColor,
        $talla,
        $cantidad,
        $fechaProduccion,
        $codigoIngreso
    );
    $actualizarRegistro->execute();
    $actualizarRegistro->close();

    $conn->commit();
    echo json_encode(['estado' => true, 'mensaje' => 'Registro de producción actualizado exitosamente.']);
    $conn->close();
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $errorRollback) {
            error_log('Error al revertir actualización de producción: ' . $errorRollback->getMessage());
        }
    }

    if ($error instanceof InvalidArgumentException || $error instanceof OutOfBoundsException) {
        http_response_code($error instanceof OutOfBoundsException ? 404 : 400);
        echo json_encode(['estado' => false, 'mensaje' => $error->getMessage()]);
        exit();
    }
    if ($error instanceof DomainException) {
        http_response_code(409);
        echo json_encode(['estado' => false, 'mensaje' => $error->getMessage()]);
        exit();
    }

    error_log('Error al actualizar producción: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No fue posible actualizar el registro de producción.']);
}

function verificarColorProduccion(mysqli $conn, string $color): int
{
    $consultaColor = $conn->prepare('SELECT ID_Color FROM colorcalzado WHERE color = ? LIMIT 1');
    $consultaColor->bind_param('s', $color);
    $consultaColor->execute();
    $resultado = $consultaColor->get_result();

    if ($resultado->num_rows > 0) {
        $registroColor = $resultado->fetch_assoc();
        $consultaColor->close();
        return (int) $registroColor['ID_Color'];
    }
    $consultaColor->close();

    $insertarColor = $conn->prepare('INSERT INTO colorcalzado (color) VALUES (?)');
    $insertarColor->bind_param('s', $color);
    $insertarColor->execute();
    $idColor = (int) $conn->insert_id;
    $insertarColor->close();
    return $idColor;
}

function ajustarInventarioProduccion(mysqli $conn, int $codigoCalzado, int $idColor, float $talla, int $cambio): void
{
    if ($cambio === 0) {
        return;
    }

    $consultaInventario = $conn->prepare(
        'SELECT Cantidad FROM inventarioproduccion
         WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?
         FOR UPDATE'
    );
    $consultaInventario->bind_param('iid', $codigoCalzado, $idColor, $talla);
    $consultaInventario->execute();
    $inventario = $consultaInventario->get_result()->fetch_assoc();
    $consultaInventario->close();

    if ($inventario) {
        $cantidadNueva = (int) $inventario['Cantidad'] + $cambio;
        if ($cantidadNueva < 0) {
            throw new DomainException(
                'No se puede reducir o eliminar el registro porque parte de esa producción ya no está disponible en el inventario.'
            );
        }

        $actualizarInventario = $conn->prepare(
            'UPDATE inventarioproduccion
             SET Cantidad = ?
             WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?'
        );
        $actualizarInventario->bind_param('iiid', $cantidadNueva, $codigoCalzado, $idColor, $talla);
        $actualizarInventario->execute();
        $actualizarInventario->close();
        return;
    }

    if ($cambio < 0) {
        throw new DomainException('No se encontró la combinación en el inventario para revertir la producción.');
    }

    $insertarInventario = $conn->prepare(
        'INSERT INTO inventarioproduccion (ID_Calzado, idColor, Talla, Cantidad)
         VALUES (?, ?, ?, ?)'
    );
    $insertarInventario->bind_param('iidi', $codigoCalzado, $idColor, $talla, $cambio);
    $insertarInventario->execute();
    $insertarInventario->close();
}
