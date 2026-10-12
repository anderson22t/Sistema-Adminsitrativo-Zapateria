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

$consultaVentasCategoria = $conn->prepare(
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
                d.cantidad as Cantidad,
                d.precio AS Precio
            FROM Pedidos p
            JOIN detallepedido d ON p.No_pedido = d.No_Pedido
        )

        SELECT 
            t.ID_Tipo as idCategoria,
            t.descripcionTipo AS Categoria,
            SUM(d.Cantidad) as CantidadVendida,
            SUM(d.Cantidad * d.Precio) as TotalCategoria
        FROM Detalle d
        JOIN calzado c ON d.idCalzado = c.ID_Calzado
        JOIN tipocalzado t ON c.ID_Tipo = t.ID_Tipo
        GROUP BY t.ID_Tipo, t.descripcionTipo;" 
);

// Ejecutar la consulta
$consultaVentasCategoria->bind_param("ssiiii",
    $fechaInicialActual, $fechaFinalActual,
    $codigoCanalVenta, $codigoCanalVenta,
    $codigoTipoVenta, $codigoTipoVenta
);
$consultaVentasCategoria->execute();

// Obtener resultados
$resultado = $consultaVentasCategoria->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaVentasCategoria->close();
$conn->close();

?>









