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

$codigoCalzado = $datos['codigoCalzado'];
// Datos fechas
$fechaInicialActual = $rango['fechaInicialActual'];
$fechaFinalActual = $rango['fechaFinalActual'];

//Consulta de Datos

$consultaVentasPorColor = $conn->prepare(
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
                d.ID_Color as codigoColor,
                d.Talla as Talla,
                d.cantidad as Cantidad,
                d.precio AS Precio
            FROM Pedidos p
            JOIN detallepedido d ON p.No_pedido = d.No_Pedido
            WHERE d.idCalzado = ?
        )

        SELECT 
            c.Color as Color,
            Talla,
            SUM(d.Cantidad) as CantidadVendida,
            SUM(d.Cantidad * d.Precio) as TotalVendido
        FROM Detalle d
        JOIN colorcalzado c ON d.codigoColor = c.ID_Color
        GROUP BY c.Color, Talla
        ORDER BY CantidadVendida DESC, TotalVendido DESC
        LIMIT 5;" 
);

// Ejecutar la consulta
$consultaVentasPorColor->bind_param("ssiiiis",
    $fechaInicialActual, $fechaFinalActual, 
    $codigoCanalVenta, $codigoCanalVenta, $codigoTipoVenta, $codigoTipoVenta, 
    $codigoCalzado
);
$consultaVentasPorColor->execute();

// Obtener resultados
$resultado = $consultaVentasPorColor->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaVentasPorColor->close();
$conn->close();

?>









