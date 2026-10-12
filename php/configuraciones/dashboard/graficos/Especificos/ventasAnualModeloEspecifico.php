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
$fechaInicialAnterior = $rango['fechaInicialAnterior'];
$fechaFinalAnterior = $rango['fechaFinalAnterior'];

//Consulta de Datos

$consultaVentas = $conn->prepare(
    "SELECT 
        SUM(total) AS TotalMes,
        month(fechaPedido) AS MES,
        year(fechaPedido) AS Anio,
        CASE
            WHEN fechaPedido BETWEEN ? AND ? THEN 'Actual'
            WHEN fechaPedido BETWEEN ? AND ? THEN 'Anterior'
        END AS Periodo
    FROM registropedido rp
    JOIN detallepedido d 
        ON rp.No_Pedido = d.No_Pedido
    WHERE 
        (fechaPedido BETWEEN ? AND ? OR fechaPedido BETWEEN ? AND ?)
        AND (? IS NULL OR codigoCanalVenta = ?)
        AND (? IS NULL OR codigoTipoVenta = ?)
        AND d.idCalzado = ?
    group by mes, periodo, anio
    ORDER BY mes, anio ASC" 
);

// Ejecutar la consulta
$consultaVentas->bind_param("ssssssssiiiii",
    $fechaInicialActual, $fechaFinalActual, 
    $fechaInicialAnterior, $fechaFinalAnterior,
    $fechaInicialActual, $fechaFinalActual, 
    $fechaInicialAnterior, $fechaFinalAnterior,
    $codigoCanalVenta, $codigoCanalVenta, $codigoTipoVenta, $codigoTipoVenta,
    $codigoCalzado 
);
$consultaVentas->execute();

// Obtener resultados
$resultado = $consultaVentas->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaVentas->close();
$conn->close();


?>