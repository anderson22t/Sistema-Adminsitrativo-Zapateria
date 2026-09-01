
<?php
session_start(); // Iniciar la sesión para manejar el estado del usuario

require_once __DIR__ . '/../../../../configuracion/db_config.php'; // Ruta al archivo de configuración
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} 

// Obtener los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuarioNombre = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    //Evitar inyecciones SQL
    $usuarioNombre = $conn->real_escape_string($usuarioNombre);

// Preparar la consulta para obtener el usuario y la contraseña encriptada
$stmt = $conn->prepare("SELECT ID_Usuario, nombre_Usuario, contraseña FROM usuario WHERE nombre_Usuario = ?");
$stmt->bind_param("s", $usuarioNombre);

// Ejecutar la consulta
$stmt->execute();
// Obtener el resultado
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $user = $resultado->fetch_assoc(); 

    var_dump($user); // Para depuración, eliminar en producción
    // Verificar si la contraseña proporcionada coincide con la contraseña encriptada
    if (password_verify($contraseña, $user['contraseña'])) {
        // Contraseña correcta, iniciar sesión
        $_SESSION['usuario'] = $user['nombre_Usuario'];
        $_SESSION['usuario_id'] = $user['ID_Usuario'];

        // Redirigir a la página principal (o dashboard)
        header("Location: ../vistas/paginaPrincipal.php");
        exit();
    } else {
        // Contraseña incorrecta
        echo "Usuario o contraseña incorrectos.";
    }
} else {
    // Usuario no encontrado
    echo "Usuario o contraseña incorrectos.";
}

// Cerrar la declaración
$stmt->close();
}

$conn->close();
?>
