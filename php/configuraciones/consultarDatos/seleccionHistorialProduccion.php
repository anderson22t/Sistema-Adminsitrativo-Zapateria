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

    $sql = "SELECT ip.codigoIngreso as codigoIngresoProduccion, ip.ID_Calzado AS codigoCalzado, c.Color AS Color, ip.Talla AS Talla, 
                   ip.Cantidad AS Cantidad, ip.FechaProduccion AS FechaProduccion
            FROM ingresoproduccion ip
            INNER JOIN colorcalzado c ON ip.idColor = c.ID_Color";

    if ($codigoCalzado !== '') {
        $sql .= " WHERE ip.ID_Calzado = ?";
    }

    $sql .= " ORDER BY ip.FechaProduccion DESC, ip.ID_Calzado ASC";
    $consultaProducciones = $conn->prepare($sql);

    if ($codigoCalzado !== '') {
        $codigoCalzado = (int) $codigoCalzado;
        $consultaProducciones->bind_param("i", $codigoCalzado);
    }

    $consultaProducciones->execute();
    $resultado = $consultaProducciones->get_result();
    $datos = [];

    while ($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }

    echo json_encode($datos);
    $consultaProducciones->close();
    $conn->close();
} catch (Throwable $error) {
    error_log('Error al consultar el historial de producción: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar el historial de producción.']);
}