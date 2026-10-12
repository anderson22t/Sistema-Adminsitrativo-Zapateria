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
$fechaInicialAnterior = $rango['fechaInicialAnterior'];
$fechaFinalAnterior = $rango['fechaFinalAnterior'];

//Consulta de Datos

$consultaIndicadores = $conn->prepare(
    "WITH Pedidos AS
    (
        SELECT 
            No_pedido, 
            total,
            CASE
                WHEN fechaPedido BETWEEN ? AND ? THEN 'Actual'
                WHEN fechaPedido BETWEEN ? AND ? THEN 'Anterior'
            END AS Periodo
        FROM registropedido rp
        WHERE (fechaPedido BETWEEN ? AND ? OR fechaPedido BETWEEN ? AND ?)
        AND (? IS NULL OR rp.codigoTipoVenta = ?)
        AND (? IS NULL OR rp.codigoCanalVenta = ?)
    ),

    Detalle AS 
    (
        SELECT 
            p.Periodo,
            p.No_pedido AS Pedido,
            p.total AS Total,
            SUM(d.cantidad) AS CantidadPares
        FROM Pedidos p
        JOIN detallepedido d 
            ON p.No_pedido = d.No_Pedido
        GROUP BY p.No_pedido, p.total
    )

    SELECT 
        d.Periodo,
        COUNT(*) AS TotalPedidos,
        SUM(d.Total) AS TotalVentas,
        SUM(d.CantidadPares) AS TotalPares,
        ROUND(SUM(Total) / COUNT(*), 2) AS TicketPromedio
    FROM Detalle d
    GROUP BY d.Periodo
    ORDER BY d.Periodo ASC
    " 
);


// Ejecutar la consulta
$consultaIndicadores->bind_param("ssssssssiiii",
    $fechaInicialActual, $fechaFinalActual, 
    $fechaInicialAnterior, $fechaFinalAnterior,
    $fechaInicialActual, $fechaFinalActual, 
    $fechaInicialAnterior, $fechaFinalAnterior,
    $codigoTipoVenta, $codigoTipoVenta,
    $codigoCanalVenta, $codigoCanalVenta
);
$consultaIndicadores->execute();

// Obtener resultados
$resultado = $consultaIndicadores->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaIndicadores->close();
$conn->close();


?>