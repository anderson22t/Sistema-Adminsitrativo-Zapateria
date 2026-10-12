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

$conexion = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la conexión: ' . $conexion->connect_error]);
    exit();
}

$descripcion = isset($_GET['descripcion']) ? $conexion->real_escape_string($_GET['descripcion']) : '';

// Definir el límite de registros por página
$limite = 20;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Construir la consulta SQL
$sql = "SELECT 
            m.ID_Material, 
            m.descripcionMaterial, 
            im.CantidadActual 
        FROM inventariomateriales im
        JOIN materiales m ON im.idMaterial = m.ID_Material";

if (!empty($descripcion)) {
    $sql .= " WHERE m.descripcionMaterial LIKE '%$descripcion%'";
}

// Obtener los resultados con paginación
$sql .= " LIMIT $limite OFFSET $offset";

$resultado = $conexion->query($sql);

$materiales = array();
while ($fila = $resultado->fetch_assoc()) {
    $materiales[] = $fila;
}

// Contar el total de registros para la paginación
$sqlTotal = "SELECT COUNT(*) AS total FROM materiales m JOIN inventariomateriales im ON im.idMaterial = m.ID_Material";
if (!empty($descripcion)) {
    $sqlTotal .= " WHERE m.descripcionMaterial LIKE '%$descripcion%'";
}

$resultadoTotal = $conexion->query($sqlTotal);
$totalRegistros = $resultadoTotal->fetch_assoc()['total'];

// Calcular el número total de páginas
$totalPaginas = ceil($totalRegistros / $limite);

echo json_encode([
    'materiales' => $materiales,
    'totalPaginas' => $totalPaginas,
    'paginaActual' => $pagina
]);

$conexion->close();