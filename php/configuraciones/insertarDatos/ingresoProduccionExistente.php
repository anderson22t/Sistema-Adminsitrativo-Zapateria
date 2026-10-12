<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'mensaje' => 'No autorizado.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php'; // Ruta al archivo de configuración
header('Content-Type: application/json');

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'Error de conexión.']);
    exit();
} 


if ($_SERVER["REQUEST_METHOD"]== 'POST') {
    // Obtener los datos del formulario
    $codigoCalzadoExistente = $_POST['codigoCalzadoExistente'];
    $colorCalzado = $_POST['colorCalzado'];
    $tallaCalzado = $_POST['tallaCalzado'];
    $cantidadCalzado = $_POST['cantidadCalzado'];
    $fechaProduccion = $_POST['fechaProduccion'];
    
    $conn->begin_transaction();

    try {
        //Verificar si existe el color ingresado
        $idColorCalzado = verificarExistencia('colorcalzado', 'ID_Color', 'color', $colorCalzado);
        
        $ingresarProduccion = $conn->prepare("INSERT INTO ingresoproduccion(ID_Calzado, idColor, Talla, Cantidad, fechaProduccion) VALUES(?,?,?,?,?)");
        $ingresarProduccion->bind_param("iidis", $codigoCalzadoExistente, $idColorCalzado, $tallaCalzado, $cantidadCalzado, $fechaProduccion);
        $ingresarProduccion->execute();
        if ($ingresarProduccion->affected_rows > 0) {
            actualizarInventarioProduccion($conn, $codigoCalzadoExistente, $idColorCalzado, $tallaCalzado, $cantidadCalzado);
            $conn->commit();
            echo json_encode([
                "estado" => true,
                "mensaje" => "Producción ingresada exitosamente."
            ]);
            exit(); 
        } else {
            throw new Exception("No se pudo registrar la producción.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
        exit();
    } 
}

function verificarExistencia($tabla, $campoId, $atributo, $valor){
    global $conn;
    $sql = "SELECT $campoId FROM $tabla WHERE $atributo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $valor);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0){
        // Si existe, devuelve el ID del atributo asignado
        $id = $resultado->fetch_assoc();
        return $id[$campoId];
    }
    else {
        // En caso contrario, se debe hacer un nuevo insert en la tabla correspondiente
        $insercionSQL = $conn->prepare("INSERT INTO $tabla($atributo) VALUES (?)");
        $insercionSQL->bind_param("s", $valor);
        $insercionSQL->execute();
        return $conn->insert_id;
    }
}

function actualizarInventarioProduccion ($conn, $codigoCalzado, $idColor, $tallaCalzado, $cantidadNueva){
    $verificarInventario = $conn->prepare("SELECT Cantidad FROM inventarioproduccion 
    WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
    $verificarInventario->bind_param("iid", $codigoCalzado, $idColor, $tallaCalzado);
    $verificarInventario->execute();
    $resultado = $verificarInventario->get_result();

    if ($resultado->num_rows > 0 ){
        $fila = $resultado->fetch_assoc();
        $nuevaCantidad = $fila['Cantidad'] + $cantidadNueva;

        $actualizarInventario = $conn->prepare("UPDATE inventarioproduccion SET Cantidad = ? 
        WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
        $actualizarInventario->bind_param("iiid", $nuevaCantidad, $codigoCalzado, $idColor, $tallaCalzado);
        $actualizarInventario->execute();
    } else {
        $ingresarInventario = $conn->prepare("INSERT INTO inventarioproduccion (ID_Calzado, idColor, Talla, Cantidad) VALUES (?,?,?,?)");
        $ingresarInventario->bind_param("iidi", $codigoCalzado, $idColor, $tallaCalzado, $cantidadNueva);
        $ingresarInventario->execute();
    }
}
?>