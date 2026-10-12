<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

header('Content-Type: application/json');

$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$registrosPorPagina = 20;
$offset = ($pagina - 1) * $registrosPorPagina;

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
       SUM(CASE WHEN fp.formaPago = 'Efectivo' THEN 1 ELSE 0 END) AS cantidad_formaPago1,
       SUM(CASE WHEN fp.formaPago = 'Efectivo' THEN total ELSE 0 END) AS montoTotal_formaPago1,
       SUM(CASE WHEN fp.formaPago = 'Tarjeta' THEN 1 ELSE 0 END) AS cantidad_formaPago2,
       SUM(CASE WHEN fp.formaPago = 'Tarjeta' THEN total ELSE 0 END) AS montoTotal_formaPago2,
       SUM(CASE WHEN fp.formaPago = 'Transferencia' THEN 1 ELSE 0 END) AS cantidad_formaPago3,
       SUM(CASE WHEN fp.formaPago = 'Transferencia' THEN total ELSE 0 END) AS montoTotal_formaPago3,
       SUM(CASE WHEN fp.formaPago = 'Cargo' THEN 1 ELSE 0 END) AS cantidad_formaPago4,
       SUM(CASE WHEN fp.formaPago = 'Cargo' THEN total ELSE 0 END) AS montoTotal_formaPago4,
       SUM(1) AS cantidad_total,
       SUM(total) AS montoTotal_total
    FROM registropedido RP
    INNER JOIN formapago fp ON RP.idFormaPago = fp.codigoFormaPago
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

$conn->close();
