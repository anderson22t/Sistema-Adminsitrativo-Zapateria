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
    $codigoCalzadoNuevo = $_POST['codigoCalzadoNuevo']; 
    $tipoCalzadoNuevo = $_POST['tipo'];
    $descripcionCalzadoNuevo = $_POST['descripcionCalzadoNuevo']; 
    $lineaCalzadoNuevo = $_POST['linea'];
    $precioCosto = $_POST['precioCosto'];

    $conn->begin_transaction();
    try {
        //Verificar si existe el tipo de linea ingresado
        $idTipoCalzado = verificarExistencia('tipocalzado', 'ID_Tipo', 'descripcionTipo', $tipoCalzadoNuevo);
        //Verificar si existe la linea ingresada
        $idLineaCalzado = verificarExistencia('lineacalzado', 'ID_linea', 'Descripcion', $lineaCalzadoNuevo);

        $consultaCalzado = $conn->prepare("SELECT * FROM calzado WHERE ID_Calzado = ? FOR UPDATE");
        $consultaCalzado->bind_param("i", $codigoCalzadoNuevo);
        $consultaCalzado->execute();
        $result = $consultaCalzado->get_result();

        if ($result->num_rows == 0){
            //Si no hay resultados, se inserta el nuevo calzado
            $ingresarCalzado = $conn->prepare("INSERT INTO calzado(ID_Calzado, ID_Linea, ID_Tipo, descripcionCalzado, precioCosto) 
                                                VALUES (?,?,?,?,?)");
            $ingresarCalzado->bind_param("iiisd", $codigoCalzadoNuevo, $idLineaCalzado ,$idTipoCalzado, $descripcionCalzadoNuevo, $precioCosto);
            $ingresarCalzado->execute();
            if ($ingresarCalzado->affected_rows > 0) {
                $conn->commit();
                echo json_encode([
                    "estado" => true,
                    "mensaje" => "Calzado ingresado exitosamente."
                ]);
                exit(); 
            } else {
                throw new Exception("Error al insertar el calzado.");
            }
        } else {
            throw new Exception("El código de calzado ya existe.");
        }
    } catch (Exception $e) {
        // Si hay algún error, revertir la transacción
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

    $conn->begin_transaction();
    try {
        // Consultar si el atributo ya existe en la tabla
        $sql = "SELECT $campoId FROM $tabla WHERE $atributo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Si existe, devuelve el ID del atributo asignado
            $id = $resultado->fetch_assoc();
            $conn->commit();
            return $id[$campoId];
        } else {
            // En caso contrario, se debe hacer un nuevo insert en la tabla correspondiente
            $insercionSQL = $conn->prepare("INSERT INTO $tabla($atributo) VALUES (?)");
            $insercionSQL->bind_param("s", $valor);
            $insercionSQL->execute();
            // Obtener el ID del nuevo registro insertado
            $insertedId = $conn->insert_id;
            // Confirmar la transacción
            $conn->commit();
            return $insertedId;
        }
    } catch (Exception $e) {
        // Si hay un error, revertimos la transacción
        $conn->rollback();
        throw new Exception("Error al verificar o insertar: " . $e->getMessage());
    }
}
?>
