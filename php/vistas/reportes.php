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
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <?php include 'sidebarModulos.php' ?>
    <section class="moduloPrincipal">
      <ion-icon name="menu" id="menuModulos"></ion-icon>
      <h1 class="titulo Reportes">Reportes</h1>
      <div class="contenedorBtnReportes">
        <button class="btnReportes activo" id="btnReportePedidos">
          Reporte de Pedidos
        </button>
        <button class="btnReportes" id="btnReporteVenta">
          Reporte de Ventas
        </button>
      </div>
      <div class="reportesPedidos">
        <button type="button" id="mostrarFiltrosPedidos" class="btnMostrarFiltros">
          <ion-icon name="filter-outline"></ion-icon>
          Filtrar búsqueda
        </button>
        <form action="" class="filtrosReportesPedidos" id="formularioFiltroPedidos">
          <div class="camposfiltros">
            <label for="filtroNoPedido">No. Pedido</label>
            <input type="number" name="filtroNoPedido" id="filtroNoPedido" />
          </div>
          <div class="camposfiltros">
            <label for="filtroFechaPedido">Fecha Pedido</label>
            <input type="date" name="filtroFechaPedido" id="filtroFechaPedido"/>
          </div>
          <div class="camposfiltros">
            <label for="filtroCliente">Cliente</label>
            <input type="text" name="filtroCliente" id="filtroCliente" />
          </div>
          <div class="camposfiltros">
            <label for="filtroNit">NIT Cliente</label>
            <input type="number" name="filtroNit" id="filtroNit" />
          </div>
          <div class="camposfiltros">
            <label for="filtroFechaEntrega">Fecha Entrega</label>
            <input type="date" name="filtroFechaEntrega" id="filtroFechaEntrega"/>
          </div>
          <button type="submit" class="btnFiltroCompras" id="btnFiltroPedidos">Aplicar Filtro</button>
        </form>
        <table class="tablaReportesPedidos" border="1">
          <thead class="encabezadosTablas">
            <tr>
              <th>No. Pedido</th>
              <th>Fecha de Pedido</th>
              <th>Cliente</th>
              <th>Dirección</th>
              <th>Fecha de Entrega</th>
              <th>Detalles</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="bodyTablaPedidos">
          </tbody>
        </table>
      </div>
      <?php include 'componenteDetallesPedido.php'; ?>
      <div class="reportesVentas">
        <button type="button" id="mostrarFiltrosVentas" class="btnMostrarFiltros">
          <ion-icon name="filter-outline"></ion-icon>
          Filtrar búsqueda
        </button>
        <form  action="" class="filtrosReportesVentas" id="formularioFiltroVentas">
          <div class="camposfiltros">
            <label for="filtroFechaVenta">Fecha</label>
            <input type="date" name="filtroFechaVenta" />
          </div>
          <div class="camposfiltros">
            <label for="filtroTipoVenta">Tipo de Venta</label>
            <input type="text" name="filtroTipoVenta" />
          </div>
          <button type="submit" class="btnFiltroCompras">Aplicar Filtro</button>
        </form>
        <table class="tablaReportesVentas" border="1">
            <thead class="encabezadosTablas">
              <tr>
                <th rowspan="2">Fecha</th> 
                <th colspan="2">Efectivo</th> 
                <th colspan="2">Tarjeta</th> 
                <th colspan="2">Transferencia</th> 
                <th colspan="2">Cargo Expreso</th> 
                <th rowspan="2">Total Pedidos</th> 
                <th rowspan="2">Monto Total</th> 
              </tr>
              <tr>
                <th>Cantidad</th> 
                <th>Monto</th>    
                <th>Cantidad</th> 
                <th>Monto</th>    
                <th>Cantidad</th> 
                <th>Monto</th>    
                <th>Cantidad</th> 
                <th>Monto</th>   
              </tr>
            </thead>
            <tbody id="bodyTablaVentas">
              <!-- Aquí se llenarán los datos de la tabla mediante JavaScript -->
            </tbody>
        </table> 
      </div>
      <div class="paginacion Reportes">
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
      <div id="toast">Los datos han sido actualizados</div>      
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/alertas.js"></script>
    <script src="../../js/scriptReportes.js"></script>
    <script src="../../js/detallesPedido.js"></script>
    <script src="../../js/actualizarPedido.js"></script>
    <script src="../../js/cerrarSesionModulos.js"></script>
    <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  </body>
</html>