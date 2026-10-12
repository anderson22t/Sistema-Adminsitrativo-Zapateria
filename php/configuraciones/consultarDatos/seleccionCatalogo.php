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

$codigoCalzado = trim($_GET['codigoCalzado'] ?? '');
if ($codigoCalzado !== '' && !ctype_digit($codigoCalzado)) {
    http_response_code(400);
    echo json_encode(['error' => 'El código de calzado debe ser numérico.']);
    exit();
}

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

    $sql = "SELECT c.ID_Calzado AS codigoCalzado, t.descripcionTipo AS Tipo, l.Descripcion AS Linea,
                   c.descripcionCalzado AS Descripcion, c.precioCosto AS PrecioCosto,
                   IF(c.estado = 1, 'Activo', 'Inactivo') AS Estado
            FROM calzado c
            INNER JOIN tipocalzado t ON c.ID_Tipo = t.ID_Tipo
            INNER JOIN lineacalzado l ON c.ID_Linea = l.ID_linea";

    if ($codigoCalzado !== '') {
        $sql .= " WHERE c.ID_Calzado = ?";
    }

    $sql .= " ORDER BY c.ID_Calzado ASC";
    $consultaCatalogo = $conn->prepare($sql);

    if ($codigoCalzado !== '') {
        $codigoCalzado = (int) $codigoCalzado;
        $consultaCatalogo->bind_param("i", $codigoCalzado);
    }

    $consultaCatalogo->execute();
    $resultado = $consultaCatalogo->get_result();
    $datos = [];

    while ($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }

    echo json_encode($datos);
    $consultaCatalogo->close();
    $conn->close();
} catch (Throwable $error) {
    error_log('Error al consultar el catálogo de calzados: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar el catálogo de calzados.']);
}