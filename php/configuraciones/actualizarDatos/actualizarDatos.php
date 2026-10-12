<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['estado' => 0, 'mensaje' => 'No autorizado.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

header('Content-Type: application/json');
$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

$clave = $key;

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['estado' => 0, 'mensaje' => 'Error de conexión.']);
    exit();
} 

$data = json_decode(file_get_contents('php://input'), true);
$codigoPedido = $data['No_Pedido'];
$pedido = $data['pedidoNuevo'];
$productos = $data['nuevoDatoProductos'];

$estado = 0;

if ($codigoPedido !== null) {
    $sql = "SELECT idCliente, idFormaPago, codigoMensajeria, idVendedor, codigoDireccion, codigoCanalVenta
        FROM registropedido WHERE No_Pedido = ?";
    $consultaPedido = $conn->prepare($sql);
    $consultaPedido->bind_param("i", $codigoPedido);
    $consultaPedido->execute();
    $resultadoConsulta = $consultaPedido->get_result();  

    if($resultadoConsulta->num_rows > 0 && $pedido !== null){
        $datosPedido = $resultadoConsulta->fetch_assoc();
        $camposRegistroPedido = [];
        $valores = [];
        $tipos = '';

        if (isset($pedido['No_Periodo'])) {
            $noPeriodo = verificarExistencia('periodo', 'codigoPeriodo', 'periodo', $pedido['No_Periodo']);
            $camposRegistroPedido[] = "No_Periodo = ?";
            $valores[] = $noPeriodo;
            $tipos .= 'i';   
        }

        if (isset($pedido['TipoVenta'])) {
            $codigoTipoVenta = verificarExistencia('tipoventa', 'codigoTipoVenta', 'descripcionTipoVenta', $pedido['TipoVenta']);
            $camposRegistroPedido[] = "codigoTipoVenta = ?";
            $valores[] = $codigoTipoVenta;  
            $tipos .= 'i';
        }
        
        if (isset($pedido['formaPago'])) {
            $codigoFormaPago = verificarExistencia('formapago', 'codigoFormaPago', 'formaPago', $pedido['formaPago']);
            $camposRegistroPedido[] = "idFormaPago = ?";
            $valores[] = $codigoFormaPago;
            $tipos .= 'i';
        }
        if (isset($pedido['mensajeria'])) {
            $codigoMensajeria = verificarExistencia('mensajeria', 'codigoMensajeria', 'descripcionMensajeria', $pedido['mensajeria']);
            $camposRegistroPedido[] = "codigoMensajeria = ?";
            $valores[] = $codigoMensajeria;
            $tipos .= 'i';
        }
        if (isset($pedido['vendedor'])) {
            $codigoVendedor = verificarExistencia('empleado', 'codigoEmpleado', 'nombre', $pedido['vendedor']);
            $camposRegistroPedido[] = "idVendedor = ?";
            $valores[] = $codigoVendedor;
            $tipos .= 'i';
        }

        // Si el nombre y le nit han cambiado, se crea un nuevo cliente
        if (isset($pedido['nombreCliente']) && isset($pedido['nitCliente'])) {
            $codigoNuevoCliente = nuevoCliente($pedido); 
            $camposRegistroPedido[] = "idCliente = ?";
            $valores[] = $codigoNuevoCliente;
            $tipos .= 'i';
            if (isset($pedido['canalVenta'])) {
                $codigoCanalVenta = verificarExistencia('canalventa', 'IDCanalVenta', 'canalVenta', $pedido['canalVenta']);
                contactoNuevoCliente($pedido, $codigoNuevoCliente, $codigoCanalVenta);
            } else {
                contactoNuevoCliente($pedido, $codigoNuevoCliente, $datosPedido['codigoCanalVenta']);
            }
        }
        // De lo contrario, se actualizan los datos del cliente existente        
        else {
            actualizarCamposCliente($conn, $clave, $pedido, $datosPedido['idCliente']);
            if (isset($pedido['canalVenta'])) {
                $codigoCanalVenta = verificarExistencia('canalventa', 'IDCanalVenta', 'canalVenta', $pedido['canalVenta']);
                actualizarContactoCliente($conn, $clave, $pedido, $datosPedido['idCliente'], $codigoCanalVenta);
                $camposRegistroPedido[] = "codigoCanalVenta = ?";
                $valores[] = $codigoCanalVenta;
                $tipos .= 'i';
            } else {
                actualizarContactoCliente($conn, $clave, $pedido, $datosPedido['idCliente'], $datosPedido['codigoCanalVenta']);
            }
        }
        
        if (isset($pedido['fechaPedido'])) {
            $camposRegistroPedido[] = "fechaPedido = ?";
            $valores[] = $pedido['fechaPedido'];
            $tipos .= 's';
        }
        if (isset($pedido['fechaEntrega'])) {
            $camposRegistroPedido[] = "fechaEntrega = ?";
            $valores[] = $pedido['fechaEntrega'];
            $tipos .= 's';
        }
        if (isset($pedido['NoGuia'])) {
            $camposRegistroPedido[] = "No_guia = ?";
            $valores[] = $pedido['NoGuia'];
            $tipos .= 's';
        }
        if (isset($pedido['costoEnvio'])) {
            $camposRegistroPedido[] = "costoEnvio = ?";
            $valores[] = $pedido['costoEnvio'];
            $tipos .= 'd';
        }
        if (isset($pedido['Total'])) {
            $camposRegistroPedido[] = "total = ?";
            $valores[] = $pedido['Total'];
            $tipos .= 'd';
        }

        if(isset($pedido['estadoPedido'])) {
            $codigoEstadoPedido = verificarExistencia('estadopedido', 'codigoEstadoPedido', 'estado', $pedido['estadoPedido']);
            $camposRegistroPedido[] = "codigoEstadoPedido = ?";
            $valores[] = $codigoEstadoPedido;
            $tipos .= 'i';
        }

        if (!empty($camposRegistroPedido)) {
            $sql = "UPDATE registropedido SET " . implode(', ', $camposRegistroPedido) . " WHERE No_Pedido = ?";
            $valores[] = $codigoPedido;
            $tipos .= 'i';

            $actualizarPedido = $conn->prepare($sql);
            $actualizarPedido->bind_param($tipos, ...$valores);
            $actualizarPedido->execute();
            if ($actualizarPedido->error) {
                $estado = 0;
            } else {
                $estado = 1;
            }
            $actualizarPedido->close();
        }
        actualizarDireccion($conn, $clave, $pedido, $datosPedido['codigoDireccion']);
    }

    if (!empty($productos)) {
        foreach ($productos as $producto) {
            $datoCalzado = [];
            $valoresProducto = [];
            $tiposProducto = '';
            actualizarInventario($producto, 1); // Aumentar inventario antes de actualizar el nuevo producto
            if(isset($producto['color'])){
                $codigoColor = verificarExistencia('colorcalzado', 'ID_Color', 'color', $producto['color']);
                $datoCalzado[] = "ID_Color = ?";
                $valoresProducto[] = $codigoColor;
                $tiposProducto .= 'i';
            }
            if(isset($producto['talla'])){
                $datoCalzado[] = "talla = ?";
                $valoresProducto[] = $producto['talla'];
                $tiposProducto .= 'd';
            }
            if(isset($producto['cantidad'])){
                $datoCalzado[] = "cantidad = ?";
                $valoresProducto[] = $producto['cantidad'];
                $tiposProducto .= 'i';
            }
            if(isset($producto['precio'])){
                $datoCalzado[] = "precio = ?";
                $valoresProducto[] = $producto['precio'];
                $tiposProducto .= 'd';
            }
            if(!empty($datoCalzado)) {
                $sql = "UPDATE detallepedido SET " . implode(', ', $datoCalzado) . " WHERE codigoDetallePedido = ?";
                $valoresProducto[] = $producto['codigoDetallePedido'];
                $tiposProducto .= 'i';

                $actualizarProducto = $conn->prepare($sql);
                $actualizarProducto->bind_param($tiposProducto, ...$valoresProducto);
                $actualizarProducto->execute();
                if ($actualizarProducto->error) {
                    $estado = 0;
                } else {
                    $estado = 1;
                    actualizarInventario($producto, 2); // Disminuir inventario después de actualizar el producto
                }
                $actualizarProducto->close();
            }
        }
    }
    
    echo json_encode([
        'estado' => $estado,
        'mensaje' => $estado ? 'Datos actualizados exitosamente!' : 'Error al actualizar los datos.'
    ]);
}

function nuevoCliente($datos){
    global $conn;
    $ingresarCliente = $conn->prepare("INSERT INTO cliente(nombre, nitCliente) VALUES(?, ?)");
    $ingresarCliente->bind_param("ss", $datos['nombreCliente'], $datos['nitCliente']);
    if ($ingresarCliente->execute()) {
        $idCliente = $conn->insert_id;
        return $idCliente;
    }
}

function contactoNuevoCliente($datos, $idCliente, $codigoCanalVenta) {
    global $conn, $estado;
    $ingresarCorreo = $conn->prepare("INSERT INTO correocliente(codigoCliente, correo) VALUES(?, ?)");
    $ingresarCorreo->bind_param("is", $idCliente, $datos['correoCliente']);
    $ingresarCorreo->execute();
    $ingresarCorreo->close();
    $ingresarTelefono = $conn->prepare("INSERT INTO telefonocliente(codigoCliente, numeroTelefono) VALUES(?, ?)");
    $ingresarTelefono->bind_param("is", $idCliente, $datos['telefonoCliente']);
    $ingresarTelefono->execute();
    $ingresarTelefono->close();
    $ingresarPerfil = $conn->prepare("INSERT INTO perfilcliente(codigoCliente, codigoCanalVenta, nombreUsuario) VALUES(?, ?, ?)");
    $ingresarPerfil->bind_param("iis", $idCliente, $codigoCanalVenta, $datos['nombreUsuario']);
    $ingresarPerfil->execute();
    if ($ingresarPerfil->error) {
        $estado = 0;
    } else {
        $estado = 1;
    }
}

function actualizarCamposCliente ($conn, $clave, $pedido, $idCliente) {
    global $estado;
    $camposActualizar = [];
    $valores = [];
    $tipos = '';

    if (isset($pedido['nombreCliente'])) {
        $camposActualizar[] = "nombre = ?";
        $valores[] = $pedido['nombreCliente'];
        $tipos .= 's';
    }
    if (isset($pedido['nitCliente'])) {
        $camposActualizar[] = "nitCliente = ?";
        $valores[] = $pedido['nitCliente'];
        $tipos .= 's';
    }

    if (!empty($camposActualizar)) {
        $sql = "UPDATE cliente SET " . implode(', ', $camposActualizar) . " WHERE codigoCliente = ?";
        $valores[] = $idCliente;
        $tipos .= 'i';

        $actualizarCliente = $conn->prepare($sql);
        $actualizarCliente->bind_param($tipos, ...$valores);
        $actualizarCliente->execute();
        if ($actualizarCliente->error) {
            $estado = 0;
        } else {
            $estado = 1;
        }
        $actualizarCliente->close();
        return true;
    }    
}

function actualizarContactoCliente ($conn, $clave, $pedido, $idCliente, $codigoCanalVenta) {
    global $estado;

    if (isset($pedido['correoCliente'])) {
        $sql = "UPDATE correocliente SET correo = ? WHERE codigoCliente = ?";
        $actualizarCorreo = $conn->prepare($sql);
        $actualizarCorreo->bind_param('si', $pedido['correoCliente'], $idCliente);
        $actualizarCorreo->execute();
        if ($actualizarCorreo->error) {
            $estado = 0;
        } else {
            $estado = 1;
        }
        $actualizarCorreo->close();
    }

    if (isset($pedido['telefonoCliente'])) {
        $sql = "UPDATE telefonocliente SET numeroTelefono = ? WHERE codigoCliente = ?";
        $actualizarTelefono = $conn->prepare($sql);
        $actualizarTelefono->bind_param('si', $pedido['telefonoCliente'], $idCliente);
        $actualizarTelefono->execute();
        if ($actualizarTelefono->error) {
            $estado = 0;
        } else {
            $estado = 1;
        }
        $actualizarTelefono->close();
    }

    if (isset($pedido['nombreUsuario'])) {
        $sql = "UPDATE perfilcliente SET nombreUsuario = ? WHERE codigoCliente = ? AND codigoCanalVenta = ?";
        $actualizarPerfil = $conn->prepare($sql);
        $actualizarPerfil->bind_param('sii', $pedido['nombreUsuario'], $idCliente, $codigoCanalVenta);
        $actualizarPerfil->execute();
        if ($actualizarPerfil->error) {
            $estado = 0;
        } else {
            $estado = 1;
        }
        $actualizarPerfil->close();
    }
}

function actualizarDireccion ( $conn, $clave, $pedido, $codigoDireccion) {
    global $estado;
    $camposActualizar = [];
    $valores = [];
    $tipos = '';

    if (isset($pedido['direccion'])) {
        $camposActualizar[] = "direccion = ?";
        $valores[] = $pedido['direccion'];
        $tipos .= 's';
    }
    if (isset($pedido['indicaciones'])) {
        $camposActualizar[] = "indicaciones = ?";
        $valores[] = $pedido['indicaciones'];
        $tipos .= 's';
    }
    if (isset($pedido['departamento'])){
        $codigoDepartamento = verificarExistencia('departamento', 'codigoDepartamento', 'nombre', $pedido['departamento']);
        $camposActualizar[] = "codigoDepartamento = ?";
        $valores[] = $codigoDepartamento;
        $tipos .= 'i';
    }
    if (!empty($camposActualizar)) {
        $sql = "UPDATE direccioncliente SET " . implode(', ', $camposActualizar) . " WHERE codigoDireccion = ?";
        $valores[] = $codigoDireccion;
        $tipos .= 'i';

        $actualizarDireccionCliente = $conn->prepare($sql);
        $actualizarDireccionCliente->bind_param($tipos, ...$valores);
        $actualizarDireccionCliente->execute();
        if ($actualizarDireccionCliente->error) {
            $estado = 0;
        } else {
            $estado = 1;
        }
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

function actualizarInventario($producto, $caso) {
    global $conn, $estado;
    $seleccionDatos = $conn->prepare("SELECT idCalzado, ID_Color, talla, cantidad FROM detallepedido WHERE codigoDetallePedido = ?");
    $seleccionDatos->bind_param("i", $producto['codigoDetallePedido']);
    $seleccionDatos->execute();
    $resultado = $seleccionDatos->get_result();
    $datosProducto = $resultado->fetch_assoc();
    $seleccionDatos->close();
    if ($caso === 1){
        $actualizarInventario = $conn->prepare("UPDATE inventarioproduccion SET cantidad = cantidad + ? WHERE ID_Calzado = ? AND idColor = ? AND talla = ?");
        $actualizarInventario->bind_param("iiid", $datosProducto['cantidad'], $datosProducto['idCalzado'], $datosProducto['ID_Color'], $datosProducto['talla']);
        $actualizarInventario->execute();
        $actualizarInventario->close();
    }
    elseif ($caso === 2) {
        //Verificar existencia del producto en inventario
        $verificarInventario = $conn->prepare("SELECT Cantidad FROM inventarioproduccion 
        WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
        $verificarInventario->bind_param("iid", $datosProducto['idCalzado'], $datosProducto['ID_Color'], $datosProducto['talla']);
        $verificarInventario->execute();
        $resultadoInventario = $verificarInventario->get_result();

        if ($resultadoInventario->num_rows > 0 ){
            $fila = $resultadoInventario->fetch_assoc();
            $nuevaCantidad = max(0, $fila['Cantidad'] - $datosProducto['cantidad']);
            $verificarInventario->close();
            // Actualizar la cantidad en inventario
            $actualizarInventario = $conn->prepare("UPDATE inventarioproduccion SET Cantidad = ? 
            WHERE ID_Calzado = ? AND idColor = ? AND Talla = ?");
            $actualizarInventario->bind_param("iiid", $nuevaCantidad,  $datosProducto['idCalzado'], $datosProducto['ID_Color'], $datosProducto['talla']);
            $actualizarInventario->execute();
            if ($actualizarInventario->error) {
                $estado = 0;
            } else {
                $estado = 1;
            }
            $actualizarInventario->close();
        } else {
            $nuevaCantidad = max(0, 0 - $datosProducto['cantidad']);
            $ingresarInventario = $conn->prepare("INSERT INTO inventarioproduccion (ID_Calzado, idColor, Talla, Cantidad) VALUES (?,?,?,?)");
            $ingresarInventario->bind_param("iidi", $datosProducto['idCalzado'], $datosProducto['ID_Color'], $datosProducto['talla'], $nuevaCantidad);
            $ingresarInventario->execute();
            if ($ingresarInventario->error) {
                $estado = 0;
            } else {
                $estado = 1;
            }
            $ingresarInventario->close();
        }
    }
}
// Cerrar la conexión
$conn->close();
