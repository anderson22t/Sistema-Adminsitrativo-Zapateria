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

$campos = [
    'idCompra',
    'descripcionMaterial',
    'cantidadMaterial',
    'precioMaterial',
    'comprador',
    'proveedorMaterial',
    'fechaCompra',
    'noComprobante'
];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
        http_response_code(400);
        echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos de la compra.']);
        exit();
    }
}

$idCompra = trim($_POST['idCompra']);
$descripcionMaterial = trim($_POST['descripcionMaterial']);
$cantidad = trim($_POST['cantidadMaterial']);
$precio = trim($_POST['precioMaterial']);
$comprador = trim($_POST['comprador']);
$proveedor = trim($_POST['proveedorMaterial']);
$fechaCompra = trim($_POST['fechaCompra']);
$noComprobante = trim($_POST['noComprobante']);
$fechaValidada = DateTime::createFromFormat('!Y-m-d', $fechaCompra);
$idCompraValidado = filter_var($idCompra, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cantidadValidada = filter_var($cantidad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$comprobanteNormalizado = ltrim($noComprobante, '0');
$noComprobanteValidado = ctype_digit($noComprobante)
    ? filter_var($comprobanteNormalizado === '' ? '0' : $comprobanteNormalizado, FILTER_VALIDATE_INT)
    : false;

if (
    !ctype_digit($idCompra) || $idCompraValidado === false ||
    $descripcionMaterial === '' ||
    !ctype_digit($cantidad) || $cantidadValidada === false ||
    !is_numeric($precio) || !is_finite((float) $precio) || (float) $precio < 0 ||
    $comprador === '' || $proveedor === '' ||
    !$fechaValidada || $fechaValidada->format('Y-m-d') !== $fechaCompra ||
    $noComprobanteValidado === false || $noComprobanteValidado < 0
) {
    http_response_code(400);
    echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos de la compra.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    $conn->set_charset('utf8mb4');
    $conn->begin_transaction();

    $idCompra = (int) $idCompraValidado;
    $cantidad = (int) $cantidadValidada;
    $precio = (float) $precio;
    $noComprobante = (int) $noComprobanteValidado;

    $consultaCompra = $conn->prepare(
        'SELECT idMaterial, cantidad
         FROM compramaterial
         WHERE ID_CompraMaterial = ?
         FOR UPDATE'
    );
    $consultaCompra->bind_param('i', $idCompra);
    $consultaCompra->execute();
    $compraAnterior = $consultaCompra->get_result()->fetch_assoc();
    $consultaCompra->close();

    if (!$compraAnterior) {
        throw new OutOfBoundsException('No se encontró la compra que deseas actualizar.');
    }

    $idMaterial = obtenerOCrearId($conn, 'materiales', 'ID_Material', 'descripcionMaterial', $descripcionMaterial);
    $idComprador = obtenerOCrearId($conn, 'empleado', 'codigoEmpleado', 'nombre', $comprador);
    $idProveedor = obtenerOCrearId($conn, 'proveedor', 'codigoProveedor', 'descripcion', $proveedor);

    $materialAnterior = (int) $compraAnterior['idMaterial'];
    $cantidadAnterior = (int) $compraAnterior['cantidad'];
    if ($materialAnterior === $idMaterial) {
        ajustarInventarioCompra($conn, $idMaterial, $cantidad - $cantidadAnterior);
    } else {
        ajustarInventarioCompra($conn, $materialAnterior, -$cantidadAnterior);
        ajustarInventarioCompra($conn, $idMaterial, $cantidad);
    }

    $actualizarCompra = $conn->prepare(
        'UPDATE compramaterial
         SET idMaterial = ?, cantidad = ?, Precio = ?, FechaCompra = ?,
             idProveedor = ?, NO_Comprobante = ?, codigoEmpleado = ?
         WHERE ID_CompraMaterial = ?'
    );
    $actualizarCompra->bind_param(
        'iidsiiii',
        $idMaterial,
        $cantidad,
        $precio,
        $fechaCompra,
        $idProveedor,
        $noComprobante,
        $idComprador,
        $idCompra
    );
    $actualizarCompra->execute();
    $actualizarCompra->close();

    $conn->commit();
    $conn->close();
    echo json_encode(['estado' => true, 'mensaje' => 'Compra actualizada exitosamente.']);
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $errorRollback) {
            error_log('Error al revertir actualización de compra: ' . $errorRollback->getMessage());
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

    error_log('Error al actualizar compra: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No fue posible actualizar la compra.']);
}

function obtenerOCrearId(mysqli $conn, string $tabla, string $campoId, string $campoNombre, string $valor): int
{
    $consulta = $conn->prepare("SELECT $campoId FROM $tabla WHERE $campoNombre = ? LIMIT 1");
    $consulta->bind_param('s', $valor);
    $consulta->execute();
    $registro = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if ($registro) {
        return (int) $registro[$campoId];
    }

    $insertar = $conn->prepare("INSERT INTO $tabla ($campoNombre) VALUES (?)");
    $insertar->bind_param('s', $valor);
    $insertar->execute();
    $id = (int) $conn->insert_id;
    $insertar->close();
    return $id;
}

function ajustarInventarioCompra(mysqli $conn, int $idMaterial, int $cambio): void
{
    if ($cambio === 0) {
        return;
    }

    if ($cambio > 0) {
        $agregarInventario = $conn->prepare(
            'INSERT INTO inventariomateriales (idMaterial, CantidadActual)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE CantidadActual = CantidadActual + VALUES(CantidadActual)'
        );
        $agregarInventario->bind_param('ii', $idMaterial, $cambio);
        $agregarInventario->execute();
        $agregarInventario->close();
        return;
    }

    $consultaInventario = $conn->prepare(
        'SELECT CantidadActual
         FROM inventariomateriales
         WHERE idMaterial = ?
         FOR UPDATE'
    );
    $consultaInventario->bind_param('i', $idMaterial);
    $consultaInventario->execute();
    $inventario = $consultaInventario->get_result()->fetch_assoc();
    $consultaInventario->close();

    if (!$inventario || (int) $inventario['CantidadActual'] + $cambio < 0) {
        throw new DomainException(
            'No se puede reducir o eliminar la compra porque parte de ese material ya fue utilizado.'
        );
    }

    $cantidadNueva = (int) $inventario['CantidadActual'] + $cambio;
    $actualizarInventario = $conn->prepare(
        'UPDATE inventariomateriales SET CantidadActual = ? WHERE idMaterial = ?'
    );
    $actualizarInventario->bind_param('ii', $cantidadNueva, $idMaterial);
    $actualizarInventario->execute();
    $actualizarInventario->close();
}
