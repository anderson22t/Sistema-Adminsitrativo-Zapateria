<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../../index.html');
    exit();
}

mysqli_report(MYSQLI_REPORT_OFF);
require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

/** @var string $host */
/** @var string $usuario */
/** @var string $contraseña */
/** @var string $baseDeDatos */
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
$usuarioActual = [];
$empleados = [];
$roles = [];
$errorCarga = null;
$mensajeFormulario = null;
$esAdministrador = false;

if ($conn->connect_error) {
    error_log('Error de conexión al cargar configuración de usuario: ' . $conn->connect_error);
    http_response_code(500);
    $errorCarga = 'No fue posible cargar los datos de usuario. Intenta nuevamente más tarde.';
} elseif (!$conn->set_charset('utf8mb4')) {
    error_log('Error al establecer el charset en configuración de usuario: ' . $conn->error);
    http_response_code(500);
    $errorCarga = 'No fue posible cargar los datos de usuario. Intenta nuevamente más tarde.';
} else {
    $usuarioId = (int) $_SESSION['usuario_id'];
    $stmtUsuario = $conn->prepare("
        SELECT u.ID_Usuario, u.nombre_Usuario, u.correo, u.codigoEmpleado,
               e.nombre AS nombreEmpleado, ru.rolUsuario
        FROM usuario u
        LEFT JOIN empleado e ON e.codigoEmpleado = u.codigoEmpleado
        LEFT JOIN rolusuario ru ON ru.codigoRolUsuario = u.codigoRolUsuario
        WHERE u.ID_Usuario = ?
        LIMIT 1
    ");

    if ($stmtUsuario === false) {
        error_log('Error al preparar consulta del usuario actual: ' . $conn->error);
        http_response_code(500);
        $errorCarga = 'No fue posible cargar los datos de usuario. Intenta nuevamente más tarde.';
    } else {
        $stmtUsuario->bind_param('i', $usuarioId);
        if (!$stmtUsuario->execute()) {
            error_log('Error al consultar usuario actual: ' . $stmtUsuario->error);
            http_response_code(500);
            $errorCarga = 'No fue posible cargar los datos de usuario. Intenta nuevamente más tarde.';
        } else {
            $resultadoUsuario = $stmtUsuario->get_result();
            $usuarioActual = $resultadoUsuario->fetch_assoc() ?: [];
            if ($usuarioActual === []) {
                http_response_code(404);
                $errorCarga = 'No se encontró la cuenta asociada a esta sesión. Inicia sesión nuevamente.';
            } else {
                $esAdministrador = ($usuarioActual['rolUsuario'] ?? null) === 'Administrador';
            }
        }
        $stmtUsuario->close();
    }

    if ($errorCarga === null) {
        $resultadoEmpleados = $conn->query('SELECT codigoEmpleado, nombre FROM empleado ORDER BY nombre');
        if ($resultadoEmpleados === false) {
            error_log('Error al consultar empleados para configuración: ' . $conn->error);
            http_response_code(500);
            $errorCarga = 'No fue posible cargar los empleados. Intenta nuevamente más tarde.';
        } else {
            while ($empleado = $resultadoEmpleados->fetch_assoc()) {
                $empleados[] = $empleado;
            }
            $resultadoEmpleados->free();
        }
    }

    if ($errorCarga === null && $esAdministrador) {
        $resultadoRoles = $conn->query('SELECT codigoRolUsuario, rolUsuario FROM rolusuario ORDER BY rolUsuario');
        if ($resultadoRoles === false) {
            error_log('Error al consultar roles para creación de usuarios: ' . $conn->error);
            http_response_code(500);
            $errorCarga = 'No fue posible cargar los roles. Intenta nuevamente más tarde.';
        } else {
            while ($rol = $resultadoRoles->fetch_assoc()) {
                $roles[] = $rol;
            }
            $resultadoRoles->free();
        }
    }
}

if (!isset($_SESSION['token_crear_usuario'])) {
    $_SESSION['token_crear_usuario'] = bin2hex(random_bytes(32));
}
$tokenFormulario = $_SESSION['token_crear_usuario'];

if (!$conn->connect_error) {
    $conn->close();
}

define('USUARIOS_VISTA', true);
require __DIR__ . '/../../vistas/usuarios.php';
