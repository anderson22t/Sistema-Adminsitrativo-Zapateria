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

$idCompra = $_POST['idCompra'] ?? '';
if (!is_string($idCompra) || !ctype_digit($idCompra) || (int) $idCompra < 1) {
    http_response_code(400);
    echo json_encode(['estado' => false, 'mensaje' => 'El identificador de la compra no es válido.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    $conn->set_charset('utf8mb4');
    $conn->begin_transaction();
    $idCompra = (int) $idCompra;

    $consultaCompra = $conn->prepare(
        'SELECT idMaterial, cantidad
         FROM compramaterial
         WHERE ID_CompraMaterial = ?
         FOR UPDATE'
    );
    $consultaCompra->bind_param('i', $idCompra);
    $consultaCompra->execute();
    $compra = $consultaCompra->get_result()->fetch_assoc();
    $consultaCompra->close();

    if (!$compra) {
        throw new OutOfBoundsException('No se encontró la compra que deseas eliminar.');
    }

    $idMaterial = (int) $compra['idMaterial'];
    $cantidadComprada = (int) $compra['cantidad'];

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

    if (!$inventario || (int) $inventario['CantidadActual'] < $cantidadComprada) {
        throw new DomainException(
            'No se puede eliminar la compra porque parte de ese material ya fue utilizado.'
        );
    }

    $cantidadNueva = (int) $inventario['CantidadActual'] - $cantidadComprada;
    $actualizarInventario = $conn->prepare(
        'UPDATE inventariomateriales SET CantidadActual = ? WHERE idMaterial = ?'
    );
    $actualizarInventario->bind_param('ii', $cantidadNueva, $idMaterial);
    $actualizarInventario->execute();
    $actualizarInventario->close();

    $eliminarCompra = $conn->prepare('DELETE FROM compramaterial WHERE ID_CompraMaterial = ?');
    $eliminarCompra->bind_param('i', $idCompra);
    $eliminarCompra->execute();
    if ($eliminarCompra->affected_rows !== 1) {
        throw new RuntimeException('No fue posible eliminar la compra.');
    }
    $eliminarCompra->close();

    $conn->commit();
    $conn->close();
    echo json_encode(['estado' => true, 'mensaje' => 'Compra eliminada exitosamente.']);
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $errorRollback) {
            error_log('Error al revertir eliminación de compra: ' . $errorRollback->getMessage());
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

    error_log('Error al eliminar compra: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No fue posible eliminar la compra.']);
}
