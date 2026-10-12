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

$consultaProductosVendidos = $conn->prepare(
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
            d.idCalzado AS idCalzado,
            d.cantidad AS Cantidad,
            d.precio AS Precio
        FROM Pedidos p
        JOIN detallepedido d 
            ON p.No_pedido = d.No_Pedido
    )
    SELECT 
        d.idCalzado AS codigoCalzado,
        c.descripcionCalzado AS Calzado,
        t.descripcionTipo AS Categoria,
        SUM(d.Cantidad) AS CantidadVendida,
        SUM(d.Cantidad * d.Precio) AS TotalVendido
    FROM Detalle d
    JOIN calzado c ON d.idCalzado = c.ID_Calzado
    JOIN tipocalzado t 	ON c.ID_Tipo = t.ID_Tipo
    GROUP BY d.idCalzado, c.descripcionCalzado,	t.descripcionTipo
    ORDER BY CantidadVendida DESC, TotalVendido DESC, codigoCalzado DESC LIMIT 5"
);

// Ejecutar la consulta
$consultaProductosVendidos->bind_param("ssiiii",
    $fechaInicialActual, $fechaFinalActual,
    $codigoCanalVenta, $codigoCanalVenta,
    $codigoTipoVenta, $codigoTipoVenta
);
$consultaProductosVendidos->execute();

// Obtener resultados
$resultado = $consultaProductosVendidos->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaProductosVendidos->close();
$conn->close();

?>
