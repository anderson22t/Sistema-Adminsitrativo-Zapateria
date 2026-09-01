<?php
require_once __DIR__ . '/../../../../configuracion/db_config.php';
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

// Filtros (después de conectar)
$filtros = [];
if (!empty($_GET['filtroCodigoCalzado'])) {
    $filtros[] = "c.ID_Calzado = " . intval($_GET['filtroCodigoCalzado']);
}
if (!empty($_GET['filtroTipoCalzado'])) {
    $filtros[] = "t.descripcionTipo LIKE '%" . $conn->real_escape_string($_GET['filtroTipoCalzado']) . "%'";
}
if (!empty($_GET['filtroLineaCalzado'])) {
    $filtros[] = "l.Descripcion LIKE '%" . $conn->real_escape_string($_GET['filtroLineaCalzado']) . "%'";
}
if (!empty($_GET['filtroTallaCalzado'])) {
    $filtros[] = "ip.Talla = " . intval($_GET['filtroTallaCalzado']);
}
if (!empty($_GET['filtroColorCalzado'])) {
    $filtros[] = "col.color LIKE '%" . $conn->real_escape_string($_GET['filtroColorCalzado']) . "%'";
}

$condicion = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

// Total
$sqlContar = "SELECT COUNT(*) AS total FROM inventarioproduccion ip
    INNER JOIN calzado c ON ip.ID_Calzado = c.ID_Calzado
    INNER JOIN tipocalzado t ON c.ID_Tipo = t.ID_Tipo
    INNER JOIN lineacalzado l ON c.ID_Linea = l.ID_Linea
    INNER JOIN colorcalzado col ON ip.idColor = col.ID_Color
    $condicion";

$totalRegistros = $conn->query($sqlContar)->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Datos
$sql = "SELECT 
            c.ID_Calzado,
            c.descripcionCalzado,
            t.descripcionTipo,
            l.Descripcion AS linea,
            col.color,
            ip.Talla,
            ip.Cantidad
        FROM inventarioproduccion ip
        INNER JOIN calzado c ON ip.ID_Calzado = c.ID_Calzado
        INNER JOIN tipocalzado t ON c.ID_Tipo = t.ID_Tipo
        INNER JOIN lineacalzado l ON c.ID_Linea = l.ID_Linea
        INNER JOIN colorcalzado col ON ip.idColor = col.ID_Color
        $condicion
        ORDER BY c.ID_Calzado DESC
        LIMIT $registrosPorPagina OFFSET $offset";

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
?>