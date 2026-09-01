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

// Filtros
$filtros = [];
if (!empty($_GET['filtroNoPedido'])) {
    $filtros[] = "Rp.No_Pedido = " . intval($_GET['filtroNoPedido']);
}

if (!empty($_GET['filtroFechaPedido'])) {
    $filtros[] = "Rp.fechaPedido = '" . $conn->real_escape_string($_GET['filtroFechaPedido']) . "'";
}

if (!empty($_GET['filtroCliente'])) {
    $filtros[] = "c.nombre LIKE '%" . $conn->real_escape_string($_GET['filtroCliente']) . "%'";
}

if (!empty($_GET['filtroNit'])) {
    $filtros[] = "c.nitCliente = '" . $conn->real_escape_string($_GET['filtroNit']) . "'";
}

if (!empty($_GET['filtroFechaEntrega'])) {
    $filtros[] = "Rp.fechaEntrega = '" . $conn->real_escape_string($_GET['filtroFechaEntrega']) . "'";
}

$condicion = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

// Total
$sqlContar = "SELECT COUNT(*) AS total FROM registropedido Rp 
              INNER JOIN cliente c ON Rp.idCliente = c.codigoCliente
              $condicion";

$resultCount = $conn->query($sqlContar);
if (!$resultCount) {
    echo json_encode(['error' => 'Error en COUNT: ' . $conn->error, 'sql' => $sqlContar]);
    exit();
}

$totalRegistros = (int)$resultCount->fetch_assoc()['total'];
$totalPaginas = $totalRegistros > 0 ? ceil($totalRegistros / $registrosPorPagina) : 1;

// Datos
$sql = "SELECT
            Rp.No_Pedido,
            Rp.fechaPedido,
            c.nombre AS nombreCliente,
            CONCAT(dc.direccion, ', ', d.nombre) AS direccionCompleta,
            Rp.fechaEntrega,
            E.estado
        FROM registropedido Rp
        INNER JOIN cliente c ON Rp.idCliente = c.codigoCliente
        INNER JOIN direccionCliente dc ON Rp.codigoDireccion = dc.codigoDireccion
        INNER JOIN departamento d ON dc.codigoDepartamento = d.codigoDepartamento
        INNER JOIN estadoPedido E ON Rp.codigoEstadoPedido = E.codigoEstadoPedido
        $condicion
        ORDER BY Rp.No_Pedido DESC
        LIMIT $registrosPorPagina OFFSET $offset";

$resultado = $conn->query($sql);
if (!$resultado) {
    echo json_encode(['error' => 'Error en SELECT: ' . $conn->error, 'sql' => $sql]);
    exit();
}

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