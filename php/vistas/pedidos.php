<?php
session_start(); // Iniciar la sesión
//Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
  // Si no ha iniciado sesión, redirigir a la página de inicio de sesión
  header("Location: ../../index.html");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pedidos</title>
  <link rel="icon" type="image/png" href="../../componentes/iconos/favicon-32x32.png" />
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link rel="stylesheet" href="../../css/responsive.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
  <?php include '../vistas/sidebarModulos.php'; ?>
  <section class="moduloPrincipal">
    <header class="headerModulo">
      <div class="headerModuloIzquierda">
        <ion-icon name="menu" id="menuModulos"></ion-icon>
        <h1 class="titulo RegistroPedido">Registro de Pedidos</h1>
      </div>
      <?php include 'usuarioModulos.php'; ?>
    </header>
    <form action="" method="" id="formularioRegistroPedido">
      <div class="formularioDatosPedido">
        <h3>Datos del Pedido</h3>
        <div class="camposFormulario Pedido">
          <div class="camposRP">
            <label for="noPeriodo">No. Período</label>
            <input type="text" name="noPeriodo" id="noPeriodo" required />
          </div>
          <div class="camposRP">
            <label for="fechaRegistro">Fecha de Pedido</label>
            <input type="date" name="fechaRegistro" id="fechaRegistro" required />
          </div>
          <div class="camposRP">
            <label for="fechaEntrega">Fecha de Entrega</label>
            <input type="date" name="fechaEntrega" id="fechaEntrega" required />
          </div>
          <div class="camposRP">
            <label for="tipoVenta">Tipo de Venta</label>
            <input list="opciones" name="tipoVenta" id="tipoVenta" />
            <datalist id="opciones">
              <option value="Mayorista">
              <option value="Detalle">
            </datalist>
          </div>
          <div class="camposRP">
            <label for="canalVenta">Canal de Venta</label>
            <input list="opcionesCanal" name="canalVenta" id="canalVenta" />
            <datalist id="opcionesCanal">
              <option value="Facebook">
              <option value="Instagram">
              <option value="Whatsapp">
            </datalist>
          </div>
          <div class="camposRP">
            <label for="vendedor">Vendedor</label>
            <input type="text" name="vendedor" id="vendedor" required />
          </div>
        </div>
      </div>
      <div class="formularioDatosProducto">
        <h3>Producto</h3>
        <div class="camposFormulario producto">
          <div class="camposRP">
            <label for="codigoProducto">Seleccione el modelo del calzado</label>
            <select name="codigoProducto" id="codigoProducto" required>
              <option value="">Códigos de calzado</option> 
              <?php include '../configuraciones/consultarDatos/codigosProductos.php'; ?>
            </select>
          </div>
          <div class="camposRP">
            <label for="color">Color</label>
            <input type="text" name="color" id="color" required />
          </div>
          <div class="camposRP">
            <label for="talla">Talla</label>
            <input type="number" name="talla" id="talla" required />
          </div>
          <div class="camposRP">
            <label for="cantidad">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" required />
          </div>
          <div class="camposRP">
            <label for="precio">Precio</label>
            <input type="number" name="precio" id="precio" required />
          </div>
        </div>
        <div class="botonesProductos">
          <button type="button" class="btnProductos" id="btnAgregarProducto">
            <ion-icon name="add-circle"></ion-icon>
            Añadir
          </button>
          <button type="button" class="btnProductos" id="btnListaProductos">
            <ion-icon name="eye-outline"></ion-icon>
            Productos Añadidos: <span id="cantidadProductos"></span>
          </button>
        </div>
      </div>
      <?php include '../vistas/listadoProductos.php'; ?>      
      <button type="button" class="btnFormulario uno" id="btnSiguiente">
        <ion-icon name="arrow-forward-outline"></ion-icon>
        Siguiente
      </button>
      <div class="formularioDatosCliente">
        <h3>Datos del Cliente</h3>
        <div class="camposFormulario cliente">
          <div class="camposRP">
            <label for="nombreCliente">Nombre</label>
            <input type="text" name="nombreCliente" id="nombreCliente" required />
          </div>
          <div class="camposRP">
            <label for="correoCliente">Correo</label>
            <input type="email" name="correoCliente" id="correoCliente" required />
          </div>
          <div class="camposRP">
            <label for="telefonoCliente">Teléfono</label>
            <input type="tel" name="telefonoCliente" id="telefonoCliente" required />
          </div>
          <div class="camposRP">
            <label for="perfilCliente">Perfil de Usuario</label>
            <input type="text" name="perfilCliente" id="perfilCliente" required />
          </div>
          <div class="camposRP">
            <label for="nitCliente">NIT</label>
            <input type="text" name="nitCliente" id="nitCliente" required />
          </div>
        </div>
      </div>
      <div class="formularioDatosEnvio">
        <h3>Datos de Envío</h3>
        <div class="camposFormulario envio">
          <div class="camposRP">
            <label for="direccion">Dirección</label>
            <input type="text" name="direccion" id="direccion" required />
          </div>
          <div class="camposRP">
            <label for="departamento">Departamento</label>
            <input type="text" name="departamento" id="departamento" required />
          </div>
          <div class="camposRP">
            <label for="indicaciones">Indicaciones</label>
            <input type="text" name="indicaciones" id="indicaciones" required />
          </div>
          <div class="camposRP">
            <label for="mensajeria">Mensajería</label>
            <input type="text" name="mensajeria" id="mensajeria" required />
          </div>
          <div class="camposRP">
            <label for="noGuia">No. de Guía</label>
            <input type="text" name="noGuia" id="noGuia" />
          </div>
          <div class="camposRP">
            <label for="costoEnvio">Costo Envío</label>
            <input type="number" name="costoEnvio" id="costoEnvio" required />
          </div>
          <div class="camposRP">
            <label for="formaPago">Forma de Pago</label>
            <select name="formaPago" id="formaPago" required>
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="transferencia">Transferencia</option>
              <option value="cargo">Cargo Expreso</option>
            </select>
          </div>
        </div>
      </div>
      <div class="botones">
        <button type="button" class="btnFormulario" id="btnAnterior">
          <ion-icon name="arrow-back-outline"></ion-icon>
          Anterior
        </button>
        <button type="button" class="btnFormulario" id="btnSiguienteDos">
          <ion-icon name="arrow-forward-outline"></ion-icon>
          Siguiente
        </button>
      </div>
      <div class="tablaResumen">
        <h3>Resumen del Pedido</h3>
        <div class="tablaResumenScroll">
          <table id="resumenDatosPedidos" border="1">
            <thead>
              <tr>
                <th>Período</th>
                <th>Tipo de Venta</th>
                <th>Canal de Venta</th>
                <th>Vendedor</th>
                <th>Fecha de Pedido</th>
                <th>Fecha de Entrega</th>
              </tr>
            </thead>
            <tbody id="datosPedido">
            </tbody>
          </table>
        </div>
        <div class="tablaResumenScroll">
          <table id="resumenDatosCliente" border="1">
            <thead>
              <tr>
                <th>Nombre</th>
                <td>${datos.nombre}</td>
                <th>Correo</th>
                <td>${datos.correo}</td>
              </tr>
              <tr>
                <th>Teléfono</th>
                <td>${datos.telefono}</td>
                <th>Perfil de Usuario</th>
                <td>${datos.perfil}</td>
              </tr>
              <tr>
                <th>Dirección</th>
                <td>${datos.direccion}</td>
                <th>Indicaciones</th>
                <td>${datos.indicaciones}</td>
              </tr>
              <tr>
                <th>NIT</th>
                <td>${datos.nit}</td>
                <th>Mensajería</th>
                <td>${datos.mensajeria}</td>
              </tr>
              <tr>
                <th>Costo de Envío</th>
                <td>${datos.costoEnvio}</td>
                <th>Forma de Pago</th>
                <td>${datos.formaPago}</td>
              </tr>
            </thead>
          </table>
        </div>
        <div class="tablaResumenScroll">
          <table id="resumenDatosProducto" border="1">
            <thead>
              <tr>
                <th>Código Producto</th>
                <th>Descripción</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody id="datosProducto"></tbody>
            <tfoot>
              <tr>
                <td colspan="6" id="filaTotal">Total con envío</td>
                <td id="totalPedido"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="botones dos">
        <button type="button" class="btnFormulario" id="btnAnteriorDos">
          <ion-icon name="arrow-back-outline"></ion-icon>
          Anterior
        </button>
        <button type="button" class="btnFormulario" id="btnEnviarPedido">
          Confirmar Pedido
          <ion-icon name="checkmark-circle"></ion-icon>
        </button>
      </div>
    </form>
  </section>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../js/alertas.js"></script>
  <script src="../../js/scriptPedidos.js"></script>
  <script src="../../js/cerrarSesionModulos.js"></script>
  <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>