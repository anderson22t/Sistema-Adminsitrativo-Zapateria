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

$codigoIngreso = $_POST['codigoIngresoProduccion'] ?? '';
if (!is_string($codigoIngreso) || !ctype_digit($codigoIngreso) || (int) $codigoIngreso < 1) {
    http_response_code(400);
    echo json_encode(['estado' => false, 'mensaje' => 'El identificador del registro no es válido.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    $conn->begin_transaction();
    $codigoIngreso = (int) $codigoIngreso;

    $consultaRegistro = $conn->prepare(
        'SELECT ID_Calzado, idColor, Talla, Cantidad
         FROM ingresoproduccion
         WHERE codigoIngreso = ?
         FOR UPDATE'
    );
    $consultaRegistro->bind_param('i', $codigoIngreso);
    $consultaRegistro->execute();
    $registro = $consultaRegistro->get_result()->fetch_assoc();
    $consultaRegistro->close();

    if (!$registro) {
        throw new OutOfBoundsException('No se encontró el registro de producción.');
    }

    ajustarInventarioProduccion(
        $conn,
        (int) $registro['ID_Calzado'],
        (int) $registro['idColor'],
        (float) $registro['Talla'],
        -(int) $registro['Cantidad']
    );

    $eliminarRegistro = $conn->prepare('DELETE FROM ingresoproduccion WHERE codigoIngreso = ?');
    $eliminarRegistro->bind_param('i', $codigoIngreso);
    $eliminarRegistro->execute();
    if ($eliminarRegistro->affected_rows !== 1) {
        throw new RuntimeException('No fue posible eliminar el registro de producción.');
    }
    $eliminarRegistro->close();

    $conn->commit();
    echo json_encode(['estado' => true, 'mensaje' => 'Registro de producción eliminado exitosamente.']);
    $conn->close();
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $errorRollback) {
            error_log('Error al revertir eliminación de producción: ' . $errorRollback->getMessage());
        }
    }

    if ($error instanceof OutOfBoundsException) {
        http_response_code(404);
        echo json_encode(['estado' => false, 'mensaje' => $error->getMessage()]);
        exit();
    }
    if ($error instanceof DomainException) {
        http_response_code(409);
        echo json_encode(['estado' => false, 'mensaje' => $error->getMessage()]);
        exit();
    }

    error_log('Error al eliminar producción: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No fue posible eliminar el registro de producción.']);
}

function ajustarInventarioProduccion(mysqli $conn, int $codigoCalzado, int $idColor, float $talla, int $cambio): void
{
    $consultaInventario = $conn->prepare(
        'SELECT Cantidad FROM inventarioproduccion
         WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?
         FOR UPDATE'
    );
    $consultaInventario->bind_param('iid', $codigoCalzado, $idColor, $talla);
    $consultaInventario->execute();
    $inventario = $consultaInventario->get_result()->fetch_assoc();
    $consultaInventario->close();

    if (!$inventario) {
        throw new DomainException('No se encontró la combinación en el inventario para revertir la producción.');
    }

    $cantidadNueva = (int) $inventario['Cantidad'] + $cambio;
    if ($cantidadNueva < 0) {
        throw new DomainException(
            'No se puede eliminar el registro porque parte de esa producción ya no está disponible en el inventario.'
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
}
