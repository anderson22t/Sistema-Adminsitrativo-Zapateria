<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['estado' => false, 'mensaje' => 'No autorizado.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

header('Content-Type: application/json');
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'Error de conexión.']);
    exit();
} 

$codigoPedido = isset($_GET['codigoPedido']) ? $_GET['codigoPedido'] : null;

$estadoEliminacion = false;

if ($codigoPedido !== null) {
    $consultaPedido = $conn->prepare("SELECT idCalzado, ID_Color, talla, cantidad FROM detallepedido WHERE No_Pedido = ?");
    $consultaPedido->bind_param("i", $codigoPedido);
    $consultaPedido->execute();
    $resultadoPedido = $consultaPedido->get_result();
    while ($producto = $resultadoPedido->fetch_assoc()) {
        actualizarInventarioProduccion(
            $producto['idCalzado'], $producto['ID_Color'], $producto['talla'], $producto['cantidad']
        );
    }
    eliminarPedido($codigoPedido);
    echo json_encode([
        'estado' => $estadoEliminacion,
        'mensaje' => $estadoEliminacion ? 'Pedido eliminado exitosamente!' : 'Error al eliminar el pedido.'
    ]);
}

function actualizarInventarioProduccion ($codigoCalzado, $idColor, $tallaCalzado, $cantidadNueva){
    global $conn;
    $sql = "UPDATE inventarioproduccion SET cantidad = cantidad + ? WHERE ID_Calzado = ? AND idColor = ? AND talla = ?";
    $actualizarInventario = $conn->prepare($sql);
    $actualizarInventario->bind_param("isis", $cantidadNueva, $codigoCalzado, $idColor, $tallaCalzado);
    $actualizarInventario->execute();
}

function eliminarPedido($codigoPedido) {
    global $conn;
    global $estadoEliminacion;
    $eliminarDetalles = "DELETE FROM detallepedido WHERE No_Pedido = ?";
    $eliminarDetallesPedido = $conn->prepare($eliminarDetalles);
    $eliminarDetallesPedido->bind_param("i", $codigoPedido);
    $eliminarDetallesPedido->execute();
    if ( $eliminarDetallesPedido->affected_rows === 0) {
        $estadoEliminacion = false;
    }
    else {
        $estadoEliminacion = true;
    }

    $sql = "DELETE FROM registropedido WHERE No_Pedido = ?";
    $eliminarPedido = $conn->prepare($sql);
    $eliminarPedido->bind_param("i", $codigoPedido);
    $eliminarPedido->execute();
    if ($eliminarPedido->affected_rows === 0) {
        $estadoEliminacion = false;
    } else {
        $estadoEliminacion = true;
    }
}

$conn->close();
?>