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

// Verificar la conexión
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'Error de conexión.']);
    exit();
} 

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'El cuerpo de la solicitud no contiene un JSON válido.'
    ]);
    exit();
}

if (!is_array($data) || !array_key_exists('formulario', $data) ||
    !array_key_exists('productos', $data) || !array_key_exists('total', $data)) {
    http_response_code(400);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'La solicitud no contiene todos los datos del pedido.'
    ]);
    exit();
}

if (!is_array($data['productos']) || count($data['productos']) === 0) {
    http_response_code(400);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'El pedido debe contener al menos un producto.'
    ]);
    exit();
}

if ($data) {
    // Almacenar la información del pedido
    $formulario = $data['formulario'];
    $productos = $data['productos'];
    $total = $data['total'];

    $estadoIngreso = false;

    $fechaPedido = $formulario['fechaRegistro'];
    $fechaEntrega = $formulario['fechaEntrega'];
    $costoEnvio = $formulario['costoEnvio'];
    $NoGuia = $formulario['noGuia'];

    $conn->begin_transaction();
    try {
        //Verificar si existe el numero de Periodo ingresado
        $NoPeriodo = verificarExistencia('periodo', 'codigoPeriodo', 'periodo', $formulario['noPeriodo']);
        //Verificar si ya se ha registrado el tipo de Venta
        $codigoTipoVenta = verificarExistencia('tipoventa', 'codigoTipoVenta', 'descripcionTipoVenta', $formulario['tipoVenta'] );
        //Verificar si ya se ha registrado la forma de pago
        $idFormaPago = verificarExistencia('formapago', 'codigoFormaPago', 'formaPago', $formulario['formaPago']);
        //Verificar si ya se ha registrado la mensajeria
        $idMensajeria = verificarExistencia('mensajeria', 'codigoMensajeria', 'descripcionMensajeria', $formulario['mensajeria']);
        //Verificar si ya se ha registrado el vendedor
        $idVendedor = verificarExistencia('empleado', 'codigoEmpleado', 'nombre', $formulario['vendedor']);
        //Verificar si el cliente ya esta registrado
        $idCliente = verificarCliente($formulario);
        //Verificar si ya se ha registrado el departamento
        $codigoDepartamento = verificarExistencia('departamento', 'codigoDepartamento', 'nombre', $formulario['departamento']);

        //Verificar si ya se ha registrado el canal de venta
        $codigoCanalVenta = verificarExistencia('canalVenta', 'IDCanalVenta', 'canalVenta', $formulario['canalVenta']);
        //Verificar si ya esta registrada la dirección del cliente
        $codigoDireccion = verificarDireccion($formulario, $idCliente, $codigoDepartamento);
        ingresarPerfil($formulario, $idCliente, $codigoCanalVenta);
        ingresarCorreo($formulario, $idCliente);
        ingresarTelefono($formulario, $idCliente);

        //Obtener el numero de pedido
        $codigoPedido = $conn->prepare("SELECT MAX(No_Pedido) AS codigoAnterior FROM registropedido FOR UPDATE");
        $codigoPedido->execute();
        $resultado = $codigoPedido->get_result();
        $filaResultado = $resultado->fetch_assoc();

        if($filaResultado['codigoAnterior'] !== NULL){
            $codigo = $filaResultado['codigoAnterior'];
            $NoPedidoNuevo = $codigo + 1;
            //Ingresar Pedido
            $ingresarPedido = $conn->prepare("INSERT INTO registropedido(No_Pedido, No_Periodo, codigoTipoVenta, idCliente, fechaPedido, 
            fechaEntrega, costoEnvio, total, idFormaPago, codigoMensajeria, No_guia, idVendedor, codigoDireccion, codigoCanalVenta)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ingresarPedido->bind_param("iiiissddiisiii", $NoPedidoNuevo, $NoPeriodo, $codigoTipoVenta, $idCliente, $fechaPedido, $fechaEntrega, $costoEnvio, $total, 
             $idFormaPago, $idMensajeria, $NoGuia, $idVendedor, $codigoDireccion, $codigoCanalVenta);
            $ingresarPedido->execute();
            if($ingresarPedido->affected_rows > 0) {
                ingresarDetallePedido($productos, $NoPedidoNuevo);
                $estadoIngreso = true;
                $conn->commit();
                echo json_encode([
                    'estado' => $estadoIngreso,
                    "mensaje" => "Pedido registrado exitosamente."
                ]);
                exit(); 
            }  else {
                throw new Exception("No se pudo registrar el pedido.");

            }
        } else {
            $NoPedido = 1;
            //Ingresar Pedido
            $ingresarPedido = $conn->prepare("INSERT INTO registropedido(No_Pedido, No_Periodo, codigoTipoVenta, idCliente, fechaPedido, 
            fechaEntrega, costoEnvio, total, idFormaPago, codigoMensajeria, No_guia, idVendedor, codigoDireccion, codigoCanalVenta)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ingresarPedido->bind_param("iiiissddiisiii", $NoPedido, $NoPeriodo, $codigoTipoVenta, $idCliente, $fechaPedido, $fechaEntrega, $costoEnvio, $total, 
             $idFormaPago, $idMensajeria, $NoGuia, $idVendedor, $codigoDireccion, $codigoCanalVenta);
            $ingresarPedido->execute();
            if($ingresarPedido->affected_rows > 0) {
                ingresarDetallePedido($productos, $NoPedido);
                $estadoIngreso = true;
                echo json_encode([
                    'estado' => $estadoIngreso,
                    "mensaje" => "Pedido registrado exitosamente."
                ]);
                $conn->commit();
                exit(); 
            }  else {
                throw new Exception("No se pudo registrar el pedido.");
            }
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['estado' => $estadoIngreso, 'mensaje' => $e->getMessage()]);
        exit();
    } 
}

function verificarCliente($datos) {
    global $conn;
    $selectCliente = $conn->prepare("SELECT codigoCliente FROM cliente 
    WHERE nombre = ? AND nitCliente = ?");
    $selectCliente->bind_param("ss", $datos['nombreCliente'], $datos['nitCliente']);
    $selectCliente->execute();
    $resultado = $selectCliente->get_result();

    if ($resultado->num_rows > 0) {
        $id = $resultado->fetch_assoc();
        return $id['codigoCliente'];
    } else {
        $ingresarCliente = $conn->prepare("INSERT INTO cliente(nombre, nitCliente) VALUES(?, ?)");
        $ingresarCliente->bind_param("ss", $datos['nombreCliente'], $datos['nitCliente']);

        if ($ingresarCliente->execute()) {
            $idCliente = $conn->insert_id;
            return $idCliente;
        }
    }
}

function verificarDireccion($formulario, $idCliente, $codigoDepartamento){
    global $conn;

    $direccionTexto = trim($formulario['direccion'] ?? '');
    $indicaciones = trim($formulario['indicaciones'] ?? '');

    $direccion = $conn->prepare("SELECT codigoDireccion FROM direccioncliente WHERE codigoCliente = ? 
    AND direccion = ? AND indicaciones = ? AND codigoDepartamento = ? ");
    $direccion->bind_param("issi", $idCliente, $direccionTexto, $indicaciones, $codigoDepartamento);

    $direccion->execute();
    $resultado = $direccion->get_result();

    if ($resultado->num_rows > 0) {
        $id = $resultado->fetch_assoc();
        return $id['codigoDireccion'];
    } else {
        $ingresarDireccion = $conn->prepare("INSERT INTO direccioncliente(codigoCliente, direccion, indicaciones, codigoDepartamento) VALUES(?, ?, ?, ?)");
        $ingresarDireccion->bind_param("issi", $idCliente, $direccionTexto, $indicaciones, $codigoDepartamento);
        $ingresarDireccion->execute();
        return $conn->insert_id;
    }
}


function verificarExistencia($tabla, $campoId, $atributo, $valor){
    global $conn;
    // Consultar si el atributo ya existe en la tabla
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
        // Obtener el ID del nuevo registro insertado
        $insertedId = $conn->insert_id;
        return $insertedId;
    }
}

function ingresarPerfil($formulario, $idCliente, $codigoCanalVenta) {
    global $conn;
    $perfilUsuario = $formulario['perfilCliente'];

    $perfil = $conn->prepare("SELECT codigoPerfilCliente FROM perfilcliente 
    WHERE codigoCliente = ? AND nombreUsuario = ? AND codigoCanalVenta = ?");
    $perfil->bind_param("isi", $idCliente, $perfilUsuario, $codigoCanalVenta);
    $perfil->execute();
    $resultado = $perfil->get_result();

    if ($resultado->num_rows == 0) {
        $ingresarPerfil = $conn->prepare("INSERT INTO perfilcliente(codigoCliente, nombreUsuario, codigoCanalVenta) VALUES (?, ?, ?)");
        $ingresarPerfil->bind_param("isi", $idCliente, $perfilUsuario, $codigoCanalVenta);
        $ingresarPerfil->execute();
    }
}

function ingresarCorreo($formulario, $idCliente){
    global $conn;
    $correoCliente = $formulario['correoCliente'];

    $correo = $conn->prepare("SELECT codigoCorreoCliente FROM correocliente WHERE codigoCliente = ? AND correo = ?");
    $correo->bind_param("is", $idCliente, $correoCliente);
    $correo->execute();
    $resultado = $correo->get_result();
    if ($resultado->num_rows == 0) {
        $ingresarCorreo = $conn->prepare("INSERT INTO correocliente(codigoCliente, correo) VALUES(?, ?)");
        $ingresarCorreo->bind_param("is", $idCliente, $correoCliente);
        $ingresarCorreo->execute();
    }
}

function ingresarTelefono($formulario, $idCliente){
    global $conn;
    $telefonoCliente = $formulario['telefonoCliente'];

    $telefono = $conn->prepare("SELECT codigoTelefonoCliente FROM telefonocliente WHERE codigoCliente = ? AND numeroTelefono = ?");
    $telefono->bind_param("is", $idCliente, $telefonoCliente);
    $telefono->execute();
    $resultado = $telefono->get_result();

    if ($resultado->num_rows == 0) {
        $ingresarTelefono = $conn->prepare("INSERT INTO telefonocliente(codigoCliente, numeroTelefono) VALUES(?, ?)");
        $ingresarTelefono->bind_param("is", $idCliente, $telefonoCliente);
        $ingresarTelefono->execute();
    }
}

function ingresarDetallePedido ($productos, $noPedido) {
    global $conn;
    $ingresarDetallePedido = $conn->prepare("INSERT INTO detallepedido(No_Pedido, idCalzado, ID_Color, talla, cantidad, precio)
    VALUES (?,?,?,?,?,?)");
    foreach($productos as $producto){
        $codigoProducto = $producto['codigoProducto'];
        $color = $producto['color'];
        $talla = $producto['talla'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];
        //Verificar si ya está registrado el color
        $idColor = verificarExistencia('colorcalzado', 'ID_Color', 'Color', $color);
        //Pasar parametros para ingresar
        $ingresarDetallePedido->bind_param("iiidid", $noPedido, $codigoProducto, $idColor, $talla, $cantidad, $precio);
        $ingresarDetallePedido->execute();
        actualizarInventarioProduccion($codigoProducto, $idColor, $talla, $cantidad);
    }
}

function actualizarInventarioProduccion ($codigoCalzado, $idColor, $tallaCalzado, $cantidadNueva){
    global $conn;

    $verificarInventario = $conn->prepare("SELECT Cantidad FROM inventarioproduccion 
    WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
    $verificarInventario->bind_param("iid", $codigoCalzado, $idColor, $tallaCalzado);
    $verificarInventario->execute();
    $resultado = $verificarInventario->get_result();

    if ($resultado->num_rows > 0 ){
        $fila = $resultado->fetch_assoc();
        $nuevaCantidad = max(0, $fila['Cantidad'] - $cantidadNueva);
        $actualizarInventario = $conn->prepare("UPDATE inventarioproduccion SET Cantidad = ? 
        WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
        $actualizarInventario->bind_param("iiid", $nuevaCantidad, $codigoCalzado, $idColor, $tallaCalzado);
        $actualizarInventario->execute();
    } else {
        $nuevaCantidad = max(0, 0 - $cantidadNueva);
        $ingresarInventario = $conn->prepare("INSERT INTO inventarioproduccion (ID_Calzado, idColor, Talla, Cantidad) VALUES (?,?,?,?)");
        $ingresarInventario->bind_param("iidi", $codigoCalzado, $idColor, $tallaCalzado, $nuevaCantidad);
        $ingresarInventario->execute();
    }
}

$conn->close();
?>

