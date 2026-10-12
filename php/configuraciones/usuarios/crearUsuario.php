<?php
session_start();
require_once __DIR__ . '/comun.php';

usuarioExigirPost();
$usuarioId = usuarioExigirSesion();
usuarioValidarToken();

$nombreUsuario = isset($_POST['nombreUsuario']) && is_string($_POST['nombreUsuario'])
    ? trim($_POST['nombreUsuario'])
    : '';
$contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena'])
    ? $_POST['contrasena']
    : '';
$correo = isset($_POST['correo']) && is_string($_POST['correo'])
    ? trim($_POST['correo'])
    : '';
$nombreEmpleado = isset($_POST['nombreEmpleado']) && is_string($_POST['nombreEmpleado'])
    ? trim($_POST['nombreEmpleado'])
    : '';
$rolUsuario = isset($_POST['rolUsuario']) && is_string($_POST['rolUsuario'])
    ? trim($_POST['rolUsuario'])
    : '';

usuarioValidarCamposCuenta($nombreUsuario, $correo);
usuarioValidarNombreEmpleado($nombreEmpleado);
$longitudRol = usuarioLongitud($rolUsuario);
if ($rolUsuario === '' || $longitudRol === false || $longitudRol > 255) {
    usuarioResponderJson(400, false, 'Escribe o selecciona un rol de hasta 255 caracteres.');
}
$longitudContrasena = usuarioLongitud($contrasena);
if ($longitudContrasena === false || $longitudContrasena < 8 || $longitudContrasena > 72) {
    usuarioResponderJson(400, false, 'La contraseña debe tener entre 8 y 72 caracteres.');
}

$conn = usuarioConectarBaseDatos();
if (usuarioObtenerRol($conn, $usuarioId) !== 'Administrador') {
    usuarioResponderJson(403, false, 'Solo un administrador puede crear usuarios.');
}

$stmtRol = $conn->prepare('SELECT codigoRolUsuario FROM rolusuario WHERE rolUsuario = ? ORDER BY codigoRolUsuario LIMIT 1');
if ($stmtRol === false) {
    error_log('Error al preparar validación del rol: ' . $conn->error);
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}
$stmtRol->bind_param('s', $rolUsuario);
if (!$stmtRol->execute()) {
    error_log('Error al validar rol seleccionado: ' . $stmtRol->error);
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}
$filaRol = $stmtRol->get_result()->fetch_assoc();
$stmtRol->close();

$stmtDuplicados = $conn->prepare('SELECT ID_Usuario FROM usuario WHERE nombre_Usuario = ? OR correo = ? LIMIT 1');
if ($stmtDuplicados === false) {
    error_log('Error al preparar validación de usuario duplicado: ' . $conn->error);
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}
$stmtDuplicados->bind_param('ss', $nombreUsuario, $correo);
if (!$stmtDuplicados->execute()) {
    error_log('Error al validar duplicados: ' . $stmtDuplicados->error);
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}
$duplicado = $stmtDuplicados->get_result()->num_rows > 0;
$stmtDuplicados->close();
if ($duplicado) {
    usuarioResponderJson(409, false, 'Ya existe un usuario con ese nombre o correo electrónico.');
}

$hashContrasena = password_hash($contrasena, PASSWORD_ARGON2ID);
if ($hashContrasena === false) {
    error_log('No fue posible generar el hash al crear usuario.');
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}

if (!$conn->begin_transaction()) {
    error_log('No fue posible iniciar la transacción al crear usuario: ' . $conn->error);
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}

try {
    $codigoEmpleado = usuarioBuscarOCrearEmpleado($conn, $nombreEmpleado);
    if ($filaRol === null) {
        $stmtCrearRol = $conn->prepare('INSERT INTO rolusuario (rolUsuario) VALUES (?)');
        if ($stmtCrearRol === false) {
            throw new RuntimeException('No se pudo preparar el registro del rol: ' . $conn->error);
        }
        $stmtCrearRol->bind_param('s', $rolUsuario);
        if (!$stmtCrearRol->execute()) {
            $error = $stmtCrearRol->error;
            $stmtCrearRol->close();
            throw new RuntimeException('No se pudo registrar el rol: ' . $error);
        }
        $codigoRolUsuario = (int) $conn->insert_id;
        $stmtCrearRol->close();
    } else {
        $codigoRolUsuario = (int) $filaRol['codigoRolUsuario'];
    }

    $stmtCrear = $conn->prepare("
        INSERT INTO usuario (nombre_Usuario, contraseña, correo, codigoEmpleado, codigoRolUsuario)
        VALUES (?, ?, ?, ?, ?)
    ");
    if ($stmtCrear === false) {
        throw new RuntimeException('No se pudo preparar el registro del usuario: ' . $conn->error);
    }

    $stmtCrear->bind_param('sssii', $nombreUsuario, $hashContrasena, $correo, $codigoEmpleado, $codigoRolUsuario);
    if (!$stmtCrear->execute()) {
        $error = $stmtCrear->error;
        $stmtCrear->close();
        throw new RuntimeException('No se pudo registrar el usuario: ' . $error);
    }
    $stmtCrear->close();

    if (!$conn->commit()) {
        throw new RuntimeException('No se pudo confirmar la transacción: ' . $conn->error);
    }
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Error al crear usuario: ' . $error->getMessage());
    usuarioResponderJson(500, false, 'No fue posible crear el usuario. Intenta nuevamente más tarde.');
}

$conn->close();
usuarioResponderJson(201, true, 'Usuario creado exitosamente.');
