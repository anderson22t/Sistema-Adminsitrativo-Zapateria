<?php
session_start();
require_once __DIR__ . '/comun.php';

usuarioExigirPost();
$usuarioId = usuarioExigirSesion();
usuarioValidarToken();

$nombreUsuario = isset($_POST['nombreUsuario']) && is_string($_POST['nombreUsuario'])
    ? trim($_POST['nombreUsuario'])
    : '';
$correo = isset($_POST['correo']) && is_string($_POST['correo'])
    ? trim($_POST['correo'])
    : '';
$nombreEmpleado = isset($_POST['nombreEmpleado']) && is_string($_POST['nombreEmpleado'])
    ? trim($_POST['nombreEmpleado'])
    : '';
$contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena'])
    ? $_POST['contrasena']
    : '';

usuarioValidarCamposCuenta($nombreUsuario, $correo);
usuarioValidarNombreEmpleado($nombreEmpleado);
if ($contrasena !== '') {
    $longitudContrasena = usuarioLongitud($contrasena);
    if ($longitudContrasena === false || $longitudContrasena < 8 || $longitudContrasena > 72) {
        usuarioResponderJson(400, false, 'La contraseña debe tener entre 8 y 72 caracteres.');
    }
}

$conn = usuarioConectarBaseDatos();

$stmtDuplicados = $conn->prepare("
    SELECT ID_Usuario
    FROM usuario
    WHERE (nombre_Usuario = ? OR correo = ?) AND ID_Usuario <> ?
    LIMIT 1
");
if ($stmtDuplicados === false) {
    error_log('Error al preparar validación de duplicados de usuario: ' . $conn->error);
    usuarioResponderJson(500, false, 'No fue posible actualizar los datos. Intenta nuevamente más tarde.');
}
$stmtDuplicados->bind_param('ssi', $nombreUsuario, $correo, $usuarioId);
if (!$stmtDuplicados->execute()) {
    error_log('Error al validar duplicados al actualizar cuenta: ' . $stmtDuplicados->error);
    usuarioResponderJson(500, false, 'No fue posible actualizar los datos. Intenta nuevamente más tarde.');
}
$duplicado = $stmtDuplicados->get_result()->num_rows > 0;
$stmtDuplicados->close();
if ($duplicado) {
    usuarioResponderJson(409, false, 'Ya existe otra cuenta con ese nombre o correo electrónico.');
}

$hashContrasena = null;
if ($contrasena !== '') {
    $hashContrasena = password_hash($contrasena, PASSWORD_ARGON2ID);
    if ($hashContrasena === false) {
        error_log('No fue posible generar el hash al actualizar usuario.');
        usuarioResponderJson(500, false, 'No fue posible actualizar los datos. Intenta nuevamente más tarde.');
    }
}

if (!$conn->begin_transaction()) {
    error_log('No fue posible iniciar la transacción al actualizar usuario: ' . $conn->error);
    usuarioResponderJson(500, false, 'No fue posible actualizar los datos. Intenta nuevamente más tarde.');
}

try {
    $codigoEmpleado = usuarioBuscarOCrearEmpleado($conn, $nombreEmpleado);
    if ($hashContrasena === null) {
        $stmtActualizar = $conn->prepare("
            UPDATE usuario
            SET nombre_Usuario = ?, correo = ?, codigoEmpleado = ?
            WHERE ID_Usuario = ?
        ");
        if ($stmtActualizar === false) {
            throw new RuntimeException('No se pudo preparar la actualización: ' . $conn->error);
        }
        $stmtActualizar->bind_param('ssii', $nombreUsuario, $correo, $codigoEmpleado, $usuarioId);
    } else {
        $stmtActualizar = $conn->prepare("
            UPDATE usuario
            SET nombre_Usuario = ?, correo = ?, codigoEmpleado = ?, contraseña = ?
            WHERE ID_Usuario = ?
        ");
        if ($stmtActualizar === false) {
            throw new RuntimeException('No se pudo preparar la actualización: ' . $conn->error);
        }
        $stmtActualizar->bind_param('ssisi', $nombreUsuario, $correo, $codigoEmpleado, $hashContrasena, $usuarioId);
    }

    if (!$stmtActualizar->execute()) {
        $error = $stmtActualizar->error;
        $stmtActualizar->close();
        throw new RuntimeException('No se pudo actualizar la cuenta: ' . $error);
    }
    $stmtActualizar->close();

    if (!$conn->commit()) {
        throw new RuntimeException('No se pudo confirmar la actualización: ' . $conn->error);
    }
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Error al actualizar datos de usuario: ' . $error->getMessage());
    usuarioResponderJson(500, false, 'No fue posible actualizar los datos. Intenta nuevamente más tarde.');
}

$_SESSION['usuario'] = $nombreUsuario;
$conn->close();
usuarioResponderJson(200, true, 'Tus datos se actualizaron correctamente.');
