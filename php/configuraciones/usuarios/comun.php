<?php

function usuarioResponderJson(int $estado, bool $exito, string $mensaje): void
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $exito,
        'message' => $mensaje
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function usuarioExigirSesion(): int
{
    if (!isset($_SESSION['usuario_id']) || (int) $_SESSION['usuario_id'] < 1) {
        usuarioResponderJson(401, false, 'Debes iniciar sesión para realizar esta acción.');
    }

    return (int) $_SESSION['usuario_id'];
}

function usuarioExigirPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        usuarioResponderJson(405, false, 'Método de solicitud no permitido.');
    }
}

function usuarioValidarToken(): void
{
    $token = $_POST['token_csrf'] ?? null;
    if (!is_string($token) || !isset($_SESSION['token_crear_usuario']) ||
        !hash_equals($_SESSION['token_crear_usuario'], $token)) {
        usuarioResponderJson(403, false, 'La sesión del formulario venció. Recarga la página e intenta nuevamente.');
    }
}

function usuarioConectarBaseDatos(): mysqli
{
    require $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

    /** @var string $host */
    /** @var string $usuario */
    /** @var string $contraseña */
    /** @var string $baseDeDatos */
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    if ($conn->connect_error) {
        error_log('Error de conexión en configuración de usuarios: ' . $conn->connect_error);
        usuarioResponderJson(500, false, 'No fue posible completar la operación. Intenta nuevamente más tarde.');
    }
    if (!$conn->set_charset('utf8mb4')) {
        error_log('Error al establecer charset en configuración de usuarios: ' . $conn->error);
        usuarioResponderJson(500, false, 'No fue posible completar la operación. Intenta nuevamente más tarde.');
    }

    return $conn;
}

function usuarioObtenerRol(mysqli $conn, int $usuarioId): ?string
{
    $stmt = $conn->prepare("
        SELECT ru.rolUsuario
        FROM usuario u
        LEFT JOIN rolusuario ru ON ru.codigoRolUsuario = u.codigoRolUsuario
        WHERE u.ID_Usuario = ?
        LIMIT 1
    ");
    if ($stmt === false) {
        error_log('Error al preparar consulta de rol: ' . $conn->error);
        usuarioResponderJson(500, false, 'No fue posible completar la operación. Intenta nuevamente más tarde.');
    }

    $stmt->bind_param('i', $usuarioId);
    if (!$stmt->execute()) {
        error_log('Error al consultar rol: ' . $stmt->error);
        usuarioResponderJson(500, false, 'No fue posible completar la operación. Intenta nuevamente más tarde.');
    }

    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila === null) {
        usuarioResponderJson(401, false, 'La cuenta de esta sesión ya no está disponible.');
    }

    return $fila['rolUsuario'] === null ? null : (string) $fila['rolUsuario'];
}

function usuarioLongitud(string $valor): int|false
{
    return preg_match_all('/./us', $valor);
}

function usuarioValidarNombreEmpleado(string $nombre): void
{
    $longitud = usuarioLongitud($nombre);
    if ($nombre === '' || $longitud === false || $longitud > 255) {
        usuarioResponderJson(400, false, 'El nombre del empleado es obligatorio y no puede superar 255 caracteres.');
    }
}

function usuarioBuscarOCrearEmpleado(mysqli $conn, string $nombre): int
{
    $stmtBuscar = $conn->prepare('SELECT codigoEmpleado FROM empleado WHERE nombre = ? ORDER BY codigoEmpleado LIMIT 1');
    if ($stmtBuscar === false) {
        throw new RuntimeException('No se pudo preparar la consulta del empleado: ' . $conn->error);
    }
    $stmtBuscar->bind_param('s', $nombre);
    if (!$stmtBuscar->execute()) {
        $error = $stmtBuscar->error;
        $stmtBuscar->close();
        throw new RuntimeException('No se pudo buscar el empleado: ' . $error);
    }

    $fila = $stmtBuscar->get_result()->fetch_assoc();
    $stmtBuscar->close();
    if ($fila !== null) {
        return (int) $fila['codigoEmpleado'];
    }

    $stmtCrear = $conn->prepare('INSERT INTO empleado (nombre) VALUES (?)');
    if ($stmtCrear === false) {
        throw new RuntimeException('No se pudo preparar el registro del empleado: ' . $conn->error);
    }
    $stmtCrear->bind_param('s', $nombre);
    if (!$stmtCrear->execute()) {
        $error = $stmtCrear->error;
        $stmtCrear->close();
        throw new RuntimeException('No se pudo registrar el empleado: ' . $error);
    }

    $codigoEmpleado = (int) $conn->insert_id;
    $stmtCrear->close();
    return $codigoEmpleado;
}

function usuarioValidarCamposCuenta(string $nombreUsuario, string $correo): void
{
    $longitudUsuario = usuarioLongitud($nombreUsuario);
    $longitudCorreo = usuarioLongitud($correo);

    if ($nombreUsuario === '' || $longitudUsuario === false || $longitudUsuario > 255) {
        usuarioResponderJson(400, false, 'El nombre de usuario es obligatorio y no puede superar 255 caracteres.');
    }
    if ($correo === '' || $longitudCorreo === false || $longitudCorreo > 255 ||
        filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
        usuarioResponderJson(400, false, 'Ingresa un correo electrónico válido de hasta 255 caracteres.');
    }
}
