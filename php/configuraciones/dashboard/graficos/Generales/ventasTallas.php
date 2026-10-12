<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php'; 

// Verificar la conexión
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} 

$datos = json_decode(file_get_contents('php://input'), true);
$rango = $datos['fechas'];
$filtros = $datos['filtros'];

// Datos de filtros
$codigoCanalVenta = !empty($filtros['canalVenta']) ? (int)$filtros['canalVenta'] : null;
$codigoTipoVenta = !empty($filtros['tipoVenta']) ? (int)$filtros['tipoVenta'] : null;

// Datos fechas
$fechaInicialActual = $rango['fechaInicialActual'];
$fechaFinalActual = $rango['fechaFinalActual'];

//Consulta de Datos

$consultaVentasTallas = $conn->prepare(
    "WITH Pedidos AS
        (
            SELECT 
		        No_pedido
            FROM registropedido
            WHERE fechaPedido BETWEEN ? AND ?
            AND (? IS NULL OR codigoCanalVenta = ?)
            AND (? IS NULL OR codigoTipoVenta = ?)
        ),

        Detalle AS 
        (
            SELECT 
                d.idCalzado as idCalzado,
                d.talla as Talla,
                d.cantidad as Cantidad,
                d.precio AS Precio
            FROM Pedidos p
            JOIN detallepedido d ON p.No_pedido = d.No_Pedido
        )

        SELECT 
            d.Talla,
            SUM(d.Cantidad) as CantidadVendida,
            SUM(d.Cantidad * d.Precio) as TotalPorTalla
        FROM Detalle d
        GROUP BY d.Talla"
);
// Ejecutar la consulta
$consultaVentasTallas->bind_param("ssiiii",
    $fechaInicialActual, $fechaFinalActual,
    $codigoCanalVenta, $codigoCanalVenta,
    $codigoTipoVenta, $codigoTipoVenta
);
$consultaVentasTallas->execute();

// Obtener resultados
$resultado = $consultaVentasTallas->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaVentasTallas->close();
$conn->close();

?>
