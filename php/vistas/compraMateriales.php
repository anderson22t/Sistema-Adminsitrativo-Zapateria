<?php
session_start(); // Iniciar la sesión
//Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
  // Si no ha iniciado sesión, redirigir a la página de inicio de sesión
  header("Location: ../../html/index.html");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Zapatería</title>
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link rel="stylesheet" href="../../css/responsive.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet" />
</head>

<body>
  <?php include 'sidebarModulos.php' ?>
  <section class="moduloPrincipal">
    <ion-icon name="menu" id="menuModulos"></ion-icon>
    <h1 class="titulo RegistroCompras">Registro de Compras de Materiales</h1>
    <div class="botonesFormularios">
      <button class="btnCompras" id="btnCompra">Registro de Compras</button>
      <button class="btnCompras" id="btnHistorial">
        Historial de Compras
      </button>
    </div>
    <form action="../configuraciones/ingresoMaterial.php" method="POST" class="formularioCompras" id="formRegistroCompras">
      <div class="campos">
        <label for="descripcionMaterial">Descripción del Material</label>
        <input type="text" name="descripcionMaterial" id="descripcionMaterial" required />
      </div>
      <div class="campos">
        <label for="cantidadMaterial">Cantidad</label>
        <input type="number" name="cantidadMaterial" id="cantidadMaterial" required />
      </div>
      <div class="campos">
        <label for="precioMaterial">Precio Total</label>
        <input type="number" name="precioMaterial" id="precioMaterial" required />
      </div>
      <div class="campos">
        <label for="comprador">Comprador</label>
        <input type="text" name="comprador" id="comprador" required />
      </div>
      <div class="campos">
        <label for="proveedorMaterial">Proveedor</label>
        <input type="text" name="proveedorMaterial" id="proveedorMaterial" required />
      </div>
      <div class="campos">
        <label for="fechaCompra">Fecha de Compra</label>
        <input type="date" name="fechaCompra" id="fechaCompra" required />
      </div>
      <div class="campos">
        <label for="noComprobante">No. de Comprobante</label>
        <input type="text" name="noComprobante" id="noComprobante" required />
      </div>
      <input
        type="submit"
        value="Añadir Registro de Compra"
        class="btnRegistrarCompra" />
    </form>
    <div class="contenedorTabla">
      <button type="button" id="mostrarFiltrosCompras" class="btnMostrarFiltros">
        <ion-icon name="filter-outline"></ion-icon>
        Filtrar búsqueda
      </button>
      <form action="" class="filtrosHistorialCompra">
        <div class="camposfiltros">
          <label for="descripcionCompra">Descripción</label>
          <input type="text" name="descripcionCompra" id="descripcionCompra" />
        </div>
        <div class="camposfiltros">
          <label for="filtroFechaCompra">Fecha de Compra</label>
          <input type="date" name="filtroFechaCompra" id="filtroFechaCompra" />
        </div>
        <div class="camposfiltros">
          <label for="proveedorCompra">Proveedor</label>
          <input type="text" name="proveedorCompra" id="proveedorCompra" />
        </div>
        <div class="camposfiltros">
          <label for="compradorCompra">Comprador</label>
          <input type="text" name="compradorCompra" id="compradorCompra" />
        </div>
        <button type="button" class="btnFiltroCompras" id="btnFiltroCompras">Aplicar Filtro</button>
      </form>
      <table class="tablaHistorialCompra" border="1">
        <thead class="encabezadosTablas">
          <tr>
            <th style="width: 15%">Fecha de Compra</th>
            <th style="width: 20%">Descripción</th>
            <th>Cantidad</th>
            <th>Precio Total</th>
            <th>Comprador</th>
            <th>Proveedor</th>
            <th>No. de Comprobante</th>
          </tr>
        </thead>
        <tbody id="historialCompras">
          <!-- Aquí se llenarán los datos del historial de compras -->
        </tbody>
      </table>
      <div class="paginacion HistorialCompras">
        <button class="btnPaginacion" id="paginacionAnterior">
          <ion-icon name="arrow-back-outline"></ion-icon>
          Anterior
        </button>
        <span id="contadorPaginas">Página 1 de 1</span>
        <button class="btnPaginacion" id="paginacionSiguiente">
          Siguiente
          <ion-icon name="arrow-forward-outline"></ion-icon>
        </button>
      </div>
    </div>
    <div id="toast">Compra ingresada exitosamente!</div>
  </section>
  <script src="../../js/scriptCompras.js"></script>
  <script src="../../js/cerrarSesionModulos.js"></script>
  <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>