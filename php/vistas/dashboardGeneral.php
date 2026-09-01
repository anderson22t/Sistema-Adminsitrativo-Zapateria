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
    <link rel="stylesheet" href="../../css/dashboard.css" />
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
      <h1 class="titulo Dashboard">Análisis General de Ventas</h1>
      <div class="filtrosDashboard General">
        <div class="filtrosRango">
            <button id="btnFiltroHoy" class="btnFiltrosGenerales" value="Hoy"> Hoy </button>
            <button id="btnFiltroSemana" class="btnFiltrosGenerales" value="Semana"> Semana </button>
            <button id="btnFiltroMes" class="btnFiltrosGenerales" value="Mes"> Mes </button>
            <button id="btnFiltroAnio" class="btnFiltrosGenerales" value="Año"> Año </button>
        </div>
        <div class="filtrosAcciones">
          <button type="button" id="btnFiltrosAvanzados" class="btnFiltrosAvanzados">
            <ion-icon name="filter-outline"></ion-icon>
            Filtros Avanzados
          </button>
          <button type="button" id="btnExportarPDF" class="btnFiltrosAvanzados">
            Exportar a PDF
            <ion-icon name="document-outline"></ion-icon>
          </button>
        </div>
      </div>

      <!-- Overlay para el Panel Lateral de Filtros -->
      <div class="overlayFiltros" id="overlayFiltros"></div>

      <!-- Panel Lateral Deslizante de Filtros Avanzados -->
      <aside class="panelFiltrosLateral" id="panelFiltrosLateral">
        <form action="" class="filtrosAvanzados" id="formularioFiltrosAvanzados">
          <div class="headerFiltros">
            <div class="tituloFiltrosContainer">
              <div class="iconoFiltroWrapper">
                <ion-icon name="options-outline"></ion-icon>
              </div>
              <div>
                <h3 class="tituloFiltros">Filtros Avanzados</h3>
                <p class="subtituloFiltros">Personaliza las métricas de venta</p>
              </div>
            </div>
            <button type="button" class="btnCerrarFiltros" id="btnCerrarFiltros" title="Cerrar panel">
              <ion-icon name="close-outline"></ion-icon>
            </button>
          </div>

          <div class="cuerpoFiltros">
            <div class="camposfiltros">
              <label for="canalVenta">
                <ion-icon name="storefront-outline"></ion-icon>
                <span>Canal de Venta</span>
              </label>
              <select name="canalVenta" id="canalVenta">
                <option value="">Todos los Canales de Venta</option>
                <?php include '../configuraciones/dashboard/canalesVenta.php'; ?>
              </select>
            </div>
            <div class="camposfiltros">
              <label for="tipoVenta">
                <ion-icon name="pricetag-outline"></ion-icon>
                <span>Tipo de Venta</span>
              </label>
              <select name="tipoVenta" id="tipoVenta">
                <option value="">Todos los Tipos de Venta</option>
                <?php include '../configuraciones/dashboard/tiposVenta.php'; ?>
              </select>
            </div>
            <div class="camposfiltros">
              <label for="anio">
                <ion-icon name="calendar-outline"></ion-icon>
                <span>Año</span>
              </label>
              <select name="anio" id="anio">
                <option value="">Seleccionar Año</option>
                <?php include '../configuraciones/dashboard/anioVenta.php'; ?>
              </select>
            </div>          
            <div class="camposfiltros">
              <label for="filtroMes">
                <ion-icon name="calendar-number-outline"></ion-icon>
                <span>Mes</span>
              </label>
              <select name="filtroMes" id="filtroMes">
                <option value="">Todos los Meses</option>
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
              </select>
            </div>
          </div>

          <div class="footerFiltros">
            <button type="reset" class="btnFormFiltros btnLimpiar" id="btnLimpiarFiltros">
              <ion-icon name="trash-outline"></ion-icon>
              Limpiar
            </button>
            <button type="submit" class="btnFormFiltros btnAplicar" id="btnAplicarFiltros">
              <ion-icon name="checkmark-outline"></ion-icon>
              Aplicar Filtros
            </button>
          </div>
        </form>
      </aside>

      <div class="cardsIndicadores">
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Total Ventas</h3>
            <img src="../../componentes/iconos/Icono Dinero.svg" alt="">
          </div>
          <span class="datoConsultado" id="totalVentas">Q 1,000.00</span>
          <div class="comparativa">
            <span id="comparativaVentas"> +10% vs ayer</span>
          </div>
        </div>
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Total Pedidos</h3>
            <img src="../../componentes/iconos/Icono Pedido.svg" alt="">
          </div>
          <span class="datoConsultado" id="totalPedidos"> 1,000</span>
          <div class="comparativa">
            <span id="comparativaPedidos"> +10% vs ayer</span>
          </div>
        </div>
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Pares Vendidos</h3>
            <img src="../../componentes/iconos/Icono Pares.svg" alt="">
          </div>
          <span class="datoConsultado" id="totalParesVendidos">1,000 pares</span>
          <div class="comparativa">
            <span id="comparativaParesVendidos"> +10% vs ayer</span>
          </div>
        </div> 
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Ticket Promedio</h3>
            <img src="../../componentes/iconos/Icono Ticket.svg" alt="">
          </div>
          <span class="datoConsultado" id="ticketPromedio">Q 1,000.00</span>
          <div class="comparativa">
            <span id="comparativaTicketPromedio"> +10% vs ayer</span>
          </div>
        </div>                       
      </div>
      <div class="graficos Primarios">
        <div class="cardGrafico">
          <h3 class="tituloGrafico">Evolución de Ventas</h3>
          <p id="descripcionEvolucion"></p>
          <div id="graficoVentas"></div>  
        </div>
        <div class="cardGrafico">
          <h3 class="tituloGrafico">Ventas por Categoría</h3>
          <div id="graficoVentasCategoria"></div>  
        </div>        
      </div>
      <div class="graficos Secundarios">
        <div class="cardGrafico">
          <h3 class="tituloGrafico">Top 5 Calzados Más Vendidos</h3>
          <div id="graficoTop5Calzados"></div>  
        </div>
        <div class="cardGrafico">
          <h3 class="tituloGrafico">Ventas por Talla</h3>
          <div id="graficoVentasTalla"></div>  
        </div>        
      </div>

    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/alertas.js"></script>
    <script src="../../js/scriptDashboard.js"></script>
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