<?php
require_once __DIR__ . '/../../../../configuracion/db_config.php'; // Ruta al archivo de configuración

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Obtener los datos del formulario

    $descripcionMaterial = $_POST['descripcionMaterial'];
    $cantidadMaterial = $_POST['cantidadMaterial'];
    $precioTotal = $_POST['precioMaterial'];
    $compradorMaterial = $_POST['comprador'];
    $proveedorMaterial = $_POST['proveedorMaterial'];
    $fechaCompra = $_POST['fechaCompra'];
    $noComprobante = $_POST['noComprobante'];

    $conn->begin_transaction();

    try {
        //Verificar si ya está registrado el código del material
        $codigoMaterial = verificarExistencia('materiales', 'ID_Material', 'descripcionMaterial', $descripcionMaterial);
        //Verificar si ya está registrado el código del comprador
        $idComprador = verificarExistencia('empleado', 'codigoEmpleado', 'nombre', $compradorMaterial);
        //Verificar si ya está registrado el código del proveedor
        $idProveedor = verificarExistencia('proveedor', 'codigoProveedor', 'descripcion', $proveedorMaterial);

        $ingresarCompra = $conn->prepare("INSERT INTO compramaterial(idMaterial, cantidad, Precio, FechaCompra, idProveedor, NO_Comprobante, codigoEmpleado)VALUES(?,?,?,?,?,?,?)");
        $ingresarCompra->bind_param("iidsiii",$codigoMaterial,$cantidadMaterial, $precioTotal, $fechaCompra, $idProveedor, $noComprobante, $idComprador);
        $ingresarCompra->execute();
        if ($ingresarCompra->affected_rows > 0){
            actualizarInventario($conn, $codigoMaterial, $cantidadMaterial);
            $conn->commit();
            header('Location: ../vistas/compraMateriales.php?status=success');
            exit();
        }
        else {
            throw new Exception("No se pudo registrar la compra.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: ../vistas/compraMateriales.php?status=error');
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

    if ($resultado->num_rows > 0) {
        // Si existe, devuelve el ID del atributo asignado
        $id = $resultado->fetch_assoc();
        return $id[$campoId];
    } else {
        // En caso contrario, se debe hacer un nuevo insert en la tabla correspondiente
        $insercionSQL = $conn->prepare("INSERT INTO $tabla($atributo) VALUES (?)");
        $insercionSQL->bind_param("s", $valor);
        $insercionSQL->execute();
        return $conn->insert_id;
    }
}

function actualizarInventario ($conn, $idMaterial, $cantidadNueva) {
    $revisionInventario = $conn->prepare("SELECT CantidadActual FROM InventarioMateriales WHERE idMaterial =?");
    $revisionInventario->bind_param("i", $idMaterial);
    $revisionInventario->execute();
    $resultadoRevision = $revisionInventario->get_result();

    if ($resultadoRevision->num_rows > 0){
        //Si ya existe en el inventario, se actualiza la cantidad
        $fila = $resultadoRevision->fetch_assoc();
        $nuevaCantidad = $fila['CantidadActual'] + $cantidadNueva;
        
        $actualizarInventario = $conn->prepare("UPDATE InventarioMateriales SET CantidadActual =? WHERE idMaterial =?");
        $actualizarInventario->bind_param("ii", $nuevaCantidad, $idMaterial);
        $actualizarInventario->execute();
    } else {
        //Si no existe, ingresar el material a inventario
        $ingresoInventario = $conn->prepare("INSERT INTO InventarioMateriales (idMaterial, CantidadActual) VALUES (?, ?)");
        $ingresoInventario->bind_param("ii", $idMaterial, $cantidadNueva);
        $ingresoInventario->execute();
    }
}
?>