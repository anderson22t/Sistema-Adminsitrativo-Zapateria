<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php'; 

// Verificar la conexión
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} 

$datos = json_decode(file_get_contents('php://input'), true);
$idCalzado = $datos['codigoCalzado'];
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

$consultaIndicadoresEspecificos = $conn->prepare(
    "WITH Pedidos AS
        (
            SELECT 
                No_pedido,
                CASE
                    WHEN fechaPedido BETWEEN ? AND ? THEN 'Actual'
                    WHEN fechaPedido BETWEEN ? AND ? THEN 'Anterior'
                END AS Periodo
            FROM registropedido
            WHERE (
                fechaPedido BETWEEN ? AND ?
                OR fechaPedido BETWEEN ? AND ?
            )
            AND (? IS NULL OR codigoCanalVenta = ?)
            AND (? IS NULL OR codigoTipoVenta = ?)
        ),

        Detalle AS 
        (
            SELECT 
                p.Periodo,
                p.No_pedido AS Pedido,
                d.idCalzado,
                SUM(d.cantidad * d.precio) AS Total,
                SUM(d.cantidad) AS CantidadPares
            FROM Pedidos p
            JOIN detallepedido d ON p.No_pedido = d.No_Pedido
            WHERE d.idCalzado = ?    
            GROUP BY p.No_pedido, d.idCalzado
        )

        SELECT 
            d.Periodo,
            d.idCalzado,
            COUNT(*) AS TotalPedidos,
            SUM(d.Total) AS TotalVentas,
            SUM(d.CantidadPares) AS TotalPares,
            ROUND(SUM(d.CantidadPares) / COUNT(*), 1) AS PromedioPares
        FROM Detalle d
        GROUP BY d.Periodo, d.idCalzado;"
);
// Ejecutar la consulta
$consultaIndicadoresEspecificos->bind_param("ssssssssiiiii",
    $fechaInicialActual, $fechaFinalActual, $fechaInicialAnterior, 
    $fechaFinalAnterior, $fechaInicialActual, $fechaFinalActual, 
    $fechaInicialAnterior, $fechaFinalAnterior, $codigoCanalVenta, 
    $codigoCanalVenta, $codigoTipoVenta, $codigoTipoVenta, $idCalzado
); 
$consultaIndicadoresEspecificos->execute();

// Obtener resultados
$resultado = $consultaIndicadoresEspecificos->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaIndicadoresEspecificos->close();
$conn->close();

?>