
<?php
session_start(); // Iniciar la sesión para manejar el estado del usuario

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php'; // Ruta al archivo de configuración
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);
    exit();
} 

$conn->set_charset("utf8mb4");

// Obtener los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuarioNombre = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Preparar la consulta para obtener el usuario y la contraseña encriptada
    $stmt = $conn->prepare("SELECT ID_Usuario, nombre_Usuario, contraseña FROM usuario WHERE nombre_Usuario = ?");
    $stmt->bind_param("s", $usuarioNombre);

    // Ejecutar la consulta
    $stmt->execute();
    // Obtener el resultado
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $user = $resultado->fetch_assoc(); 
        // Verificar si la contraseña proporcionada coincide con la contraseña encriptada
        if (password_verify($contraseña, $user['contraseña'])) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['usuario'] = $user['nombre_Usuario'];
            $_SESSION['usuario_id'] = $user['ID_Usuario'];

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'redirect' => 'php/vistas/paginaPrincipal.php'
            ]);
            exit();
        } else {
            // Contraseña incorrecta
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.'
            ]);
            exit();
        }
    } else {
        // Usuario no encontrado
        echo json_encode([
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ]);
        exit();
    }

    // Cerrar la declaración
    $stmt->close();
}

$conn->close();
?>
