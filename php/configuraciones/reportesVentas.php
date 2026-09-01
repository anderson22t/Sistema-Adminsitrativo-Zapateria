<?php
require_once __DIR__ . '/../../../../configuracion/db_config.php';

header('Content-Type: application/json');

$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$registrosPorPagina = 20;
$offset = ($pagina - 1) * $registrosPorPagina;
$clave = $key;

// Conexión
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit();
}

$filtros = [];
if (!empty($_GET['filtroFechaVenta'])) {
    $filtros[] = "Rp.fechaPedido = '" . $conn->real_escape_string($_GET['filtroFechaVenta']) . "'";
}
if (!empty($_GET['filtroTipoVenta'])) {
    $filtros[] = "tp.descripcionTipoVenta LIKE '%" . $conn->real_escape_string($_GET['filtroTipoVenta']) . "%'";
}

$condicion = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

// Total
$sqlContar = "SELECT COUNT(*) AS total FROM registropedido Rp 
              INNER JOIN tipoventa tp ON Rp.codigoTipoVenta = tp.codigoTipoVenta
              $condicion";

$totalRegistros = $conn->query($sqlContar)->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);


$sql = "
    SELECT fechaPedido,
       SUM(CASE WHEN idFormaPago = 1 THEN 1 ELSE 0 END) AS cantidad_formaPago1,
       SUM(CASE WHEN idFormaPago = 1 THEN total ELSE 0 END) AS montoTotal_formaPago1,
       SUM(CASE WHEN idFormaPago = 2 THEN 1 ELSE 0 END) AS cantidad_formaPago2,
       SUM(CASE WHEN idFormaPago = 2 THEN total ELSE 0 END) AS montoTotal_formaPago2,
       SUM(CASE WHEN idFormaPago = 3 THEN 1 ELSE 0 END) AS cantidad_formaPago3,
       SUM(CASE WHEN idFormaPago = 3 THEN total ELSE 0 END) AS montoTotal_formaPago3,
       SUM(CASE WHEN idFormaPago = 4 THEN 1 ELSE 0 END) AS cantidad_formaPago4,
       SUM(CASE WHEN idFormaPago = 4 THEN total ELSE 0 END) AS montoTotal_formaPago4,
       SUM(1) AS cantidad_total,
       SUM(total) AS montoTotal_total
    FROM RegistroPedido RP
    INNER JOIN tipoventa tp ON RP.codigoTipoVenta = tp.codigoTipoVenta
    $condicion
    GROUP BY fechaPedido ORDER BY fechaPedido DESC
    LIMIT $registrosPorPagina OFFSET $offset
";

$resultado = $conn->query($sql);
$datos = [];
while ($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode([
    'datos' => $datos,
    'totalPaginas' => $totalPaginas,
    'paginaActual' => $pagina
]);
?>
