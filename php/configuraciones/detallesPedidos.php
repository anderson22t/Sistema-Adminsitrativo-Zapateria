<?php 
require_once __DIR__ . '/../../../../configuracion/db_config.php'; // Ruta al archivo de configuración
$clave = $key;

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} 

$codigoPedido = isset($_GET['codigoPedido']) ? $_GET['codigoPedido'] : null;

if ($codigoPedido !== null) {
    $sql = "SELECT
                Rp.No_Pedido,
                P.periodo AS periodo,
                Rp.fechaPedido,
                Rp.fechaEntrega,
                c.nombre AS nombreCliente,
                c.nitCliente AS nitCliente,
                Co.correo AS correoCliente,
                Tel.numeroTelefono AS telefonoCliente,
                Pe.nombreUsuario AS nombreUsuario,
                dc.direccion AS direccion,
                d.nombre AS departamento,
                dc.indicaciones AS indicaciones,
                Men.descripcionMensajeria AS mensajeria,
                Rp.No_guia AS NoGuia,
                Rp.costoEnvio AS costoEnvio,
                Fp.formaPago AS formaPago,
                Rp.total AS Total,
                Tv.descripcionTipoVenta AS TipoVenta,
                Cv.canalVenta AS canalVenta,
                Es.estado AS estadoPedido,
                E.nombre AS vendedor
            FROM registropedido Rp
            INNER JOIN periodo P ON Rp.No_Periodo = P.codigoPeriodo
            INNER JOIN cliente c ON Rp.idCliente = c.codigoCliente
            INNER JOIN CorreoCliente Co ON c.codigoCliente = Co.codigoCliente
            INNER JOIN TelefonoCliente Tel ON c.codigoCliente = Tel.codigoCliente
            INNER JOIN CanalVenta Cv ON Rp.codigoCanalVenta = Cv.IDCanalVenta
            INNER JOIN FormaPago Fp ON Rp.idFormaPago = Fp.codigoFormaPago
            INNER JOIN TipoVenta Tv ON Rp.codigoTipoVenta = TV.codigoTipoVenta
            INNER JOIN Mensajeria Men ON Rp.codigoMensajeria = Men.codigoMensajeria
            INNER JOIN Empleado E ON Rp.idVendedor = E.codigoEmpleado
            INNER JOIN PerfilCliente Pe ON c.codigoCliente = Pe.codigoCliente AND Cv.IDCanalVenta = Pe.codigoCanalVenta
            INNER JOIN direccionCliente dc ON Rp.codigoDireccion = dc.codigoDireccion
            INNER JOIN departamento d ON dc.codigoDepartamento = d.codigoDepartamento
            INNER JOIN estadoPedido Es ON Rp.codigoEstadoPedido = Es.codigoEstadoPedido
            WHERE Rp.No_Pedido = ?";
    $consultaPedido = $conn->prepare($sql);
    $consultaPedido->bind_param("i", $codigoPedido);
    $consultaPedido->execute();
    $resultadoPedido = $consultaPedido->get_result();
    $pedido = $resultadoPedido->fetch_assoc();
    if (!$pedido) {
        echo json_encode(['error' => 'No se encontró el pedido']);
        $conn->close();
        exit;
    }

    $sqlProductos = "SELECT
                        Dp.codigoDetallePedido AS codigoDetallePedido,
                        Dp.idCalzado AS codigoCalzado,
                        Calz.descripcionCalzado AS descripcionCalzado,
                        Color.color AS color,
                        Dp.talla AS talla,
                        Dp.cantidad AS cantidad,
                        Dp.precio AS precio
                    FROM detallepedido Dp
                    INNER JOIN registropedido Rp ON Dp.No_Pedido = Rp.No_Pedido
                    INNER JOIN calzado Calz ON Dp.idCalzado = Calz.ID_Calzado
                    INNER JOIN colorcalzado Color ON Dp.ID_Color = Color.ID_Color
                    WHERE Rp.No_Pedido = ?";
    $consultaProductos = $conn->prepare($sqlProductos);
    $consultaProductos->bind_param("i", $codigoPedido);
    $consultaProductos->execute();
    $resultadoProductos = $consultaProductos->get_result();
    $datosProductos = [];
    while ($fila = $resultadoProductos->fetch_assoc()) {
        $datosProductos[] = $fila;
    }

    if (empty($datosProductos)) {
        echo json_encode(['error' => 'No se encontraron productos para este pedido']);
        $conn->close();
        exit;
    }

    echo json_encode([
    'pedido' => $pedido,
    'datosProductos' => $datosProductos
    ]);
}

$conn->close();