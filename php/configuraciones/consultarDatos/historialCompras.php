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

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit();
}

$descripcion = $_GET['descripcion'] ?? '';
$fecha = $_GET['fecha'] ?? '';
$proveedor = $_GET['proveedor'] ?? '';
$comprador = $_GET['comprador'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$params = [];
$types = "";
$where = "WHERE 1=1";

if (!empty($descripcion)) {
    $where .= " AND m.descripcionMaterial LIKE ?";
    $params[] = "%$descripcion%";
    $types .= "s";
}
if (!empty($fecha)) {
    $where .= " AND cm.FechaCompra = ?";
    $params[] = $fecha;
    $types .= "s";
}
if (!empty($proveedor)) {
    $where .= " AND p.descripcion LIKE ?";
    $params[] = "%$proveedor%";
    $types .= "s";
}
if (!empty($comprador)) {
    $where .= " AND e.nombre LIKE ?";
    $params[] = "%$comprador%";
    $types .= "s";
}

// Total de resultados
$sqlCount = "
SELECT COUNT(*) FROM compramaterial cm
INNER JOIN materiales m ON cm.idMaterial = m.ID_Material
INNER JOIN proveedor p ON cm.idProveedor = p.codigoProveedor
INNER JOIN empleado e ON cm.codigoEmpleado = e.codigoEmpleado
$where
";

$stmtCount = $conn->prepare($sqlCount);
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$stmtCount->bind_result($total);
$stmtCount->fetch();
$stmtCount->close();

// Datos de la página actual
$sql = "
SELECT 
    cm.ID_CompraMaterial AS idCompra,
    cm.FechaCompra,
    m.descripcionMaterial,
    cm.cantidad,
    cm.precio,
    p.descripcion AS proveedor,
    e.nombre AS comprador,
    cm.NO_Comprobante
FROM compramaterial cm
INNER JOIN materiales m ON cm.idMaterial = m.ID_Material
INNER JOIN proveedor p ON cm.idProveedor = p.codigoProveedor
INNER JOIN empleado e ON cm.codigoEmpleado = e.codigoEmpleado
$where
ORDER BY cm.FechaCompra DESC
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

// Agregar tipos para limit y offset
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

$datos = [];
while ($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;  // Almacenar los resultados en un array
}

// Calcular las páginas totales
$totalPages = ceil($total / $limit);

// Enviar los datos y la cantidad total de páginas como JSON
echo json_encode([
    'data' => $datos,
    'totalPages' => $totalPages
]);

// Cerrar la conexión
$conn->close();