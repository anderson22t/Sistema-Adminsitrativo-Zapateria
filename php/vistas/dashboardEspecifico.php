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
    <title>Dashboard Análisis Específico</title>
    <link rel="icon" type="image/png" href="../../componentes/iconos/favicon-32x32.png" />
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
      <header class="headerModulo">
        <div class="headerModuloIzquierda">
          <ion-icon name="menu" id="menuModulos"></ion-icon>
          <h1 class="titulo Dashboard">Análisis Específico Por Producto</h1>
        </div>
        <?php include 'usuarioModulos.php'; ?>
      </header>
      <div class="filtrosDashboard General">
        <div class="filtrosBarraPrincipal">
          <div class="filtrosRango">
              <button id="btnFiltroHoy" class="btnFiltrosGenerales" value="Hoy"> Hoy </button>
              <button id="btnFiltroSemana" class="btnFiltrosGenerales" value="7dias">Últimos 7 días</button>
              <button id="btnFiltroMes" class="btnFiltrosGenerales" value="30dias">Últimos 30 días</button>
          </div>
          <button type="button" id="btnToggleAcciones" class="btnToggleAcciones" title="Ver más opciones" aria-expanded="false">
            <span>Opciones</span>
            <ion-icon name="chevron-down-outline"></ion-icon>
          </button>
        </div>
        <div class="filtrosAcciones" id="filtrosAcciones">
            <select name="filtroCalzado" id="filtroCalzado">
                <option value="">Filtrar por Calzado</option>
            </select> 
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

      <!-- Banner de Contexto de Modelo y Período -->
      <div class="bannerDashboard info" id="bannerDashboardEspecifico">
        <div class="encabezadoBanner">
          <span class="bannerTitulo" id="bannerTitulo">Detalles de Consulta:</span>
          <span><ion-icon name="chevron-down-outline" id="iconoFiltroBanner"></ion-icon></span>
        </div>
        <div class="opcionBanner" id="opcionPeriodoBanner">
          <span class="iconoOpcion" id="iconoPeriodo"></span>
          <span class="leyendaOpcion" id="leyendaPeriodo"></span>
          <span class="bannerBadgeFechas" id="opcionPeriodo"></span>
        </div> 
        <div class="opcionBanner" id="opcionCalzadoBanner">
          <span class="leyendaOpcion" id="leyendaCalzado">Modelo: </span>
          <span class="bannerTexto" id="opcionCalzado"></span>
        </div>          
        <div class="opcionBanner" id="opcionCanalVentaBanner">
          <span class="iconoOpcion" id="iconoCanalVenta"><ion-icon name="storefront-outline"></ion-icon></span>
          <span class="leyendaOpcion">Canal de Venta: </span>
          <span class="bannerTexto" id="opcionCanalVenta"></span>
        </div>
        <div class="opcionBanner" id="opcionTipoVentaBanner">
          <span class="iconoOpcion"><ion-icon name="pricetag-outline"></ion-icon></span>
          <span class="leyendaOpcion">Tipo de Venta: </span>
          <span class="bannerTexto" id="opcionTipoVenta"></span>
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
                <?php include '../configuraciones/dashboard/filtros/canalesVenta.php'; ?>
              </select>
            </div>
            <div class="camposfiltros">
              <label for="tipoVenta">
                <ion-icon name="pricetag-outline"></ion-icon>
                <span>Tipo de Venta</span>
              </label>
              <select name="tipoVenta" id="tipoVenta">
                <option value="">Todos los Tipos de Venta</option>
                <?php include '../configuraciones/dashboard/filtros/tiposVenta.php'; ?>
              </select>
            </div>
            <div class="camposfiltros">
              <label for="filtroMes">
                <ion-icon name="calendar-number-outline"></ion-icon>
                <span>Mes</span>
              </label>
              <select name="filtroMes" id="filtroMes">
                <option value="">Seleccionar mes</option>
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
            <div class="camposfiltros">
              <label for="anio">
                <ion-icon name="calendar-outline"></ion-icon>
                <span>Año</span>
              </label>
              <select name="anio" id="anio">
                <option value="">Seleccionar Año</option>
                <?php include '../configuraciones/dashboard/filtros/anioVenta.php'; ?>
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
            <span class="comparativaIcono" id="iconoVentaTotal">
              <ion-icon name="trending-up-outline"></ion-icon>
            </span>
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
            <span class="comparativaIcono" id="iconoTotalPedidos">
              <ion-icon name="trending-up-outline"></ion-icon>
            </span>
            <span id="comparativaPedidos"> + 10% vs ayer</span>
          </div>
        </div>
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Pares Vendidos</h3>
            <img src="../../componentes/iconos/Icono Pares.svg" alt="">
          </div>
          <span class="datoConsultado" id="totalParesVendidos">1,000 pares</span>
          <div class="comparativa">
            <span class="comparativaIcono" id="iconoTotalParesVendidos">
              <ion-icon name="trending-up-outline"></ion-icon>
            </span>
            <span id="comparativaParesVendidos"> +10% vs ayer</span>
          </div>
        </div> 
        <div class="cardKPI">
          <div class="encabezadoCardKPI">
            <h3>Promedio Pares/Pedido</h3>
            <img src="../../componentes/iconos/Icono Ticket.svg" alt="">
          </div>
          <span class="datoConsultado" id="promedioPares">2.5 Pares</span>
          <div class="comparativa">
            <span class="comparativaIcono" id="iconoParesPromedio">
              <ion-icon name="trending-up-outline"></ion-icon>
            </span>
            <span id="comparativaParesPromedio"> +10% vs ayer</span>
          </div>
        </div>                       
      </div>
      <div class="graficos Primarios">
        <div class="cardGrafico">
          <div class="graficoHeader">
            <h3 class="tituloGrafico">Evolución de Ventas</h3>
            <p id="descripcionEvolucion">Comparativa: 5 Días Anteriores vs Últimos 5 Días</p>
          </div>
          <div class="contenedorGrafico" id="graficoEvolucionVentas"></div>  
        </div>    
        <div class="cardGrafico">
          <div class="graficoHeader">
            <h3 class="tituloGrafico">Ventas por Color</h3>
          </div>
          <div class="contenedorGrafico" id="graficoVentasColor"></div>  
        </div>       
      </div>
      <div class="graficos Secundarios">
        <div class="cardGrafico">
          <div class="graficoHeader">
            <h3 class="tituloGrafico">Ventas Por Tallas</h3>
          </div>
          <div class="contenedorGrafico" id="graficoVentasTallas"></div>  
        </div>  
        <div class="cardGrafico">
          <div class="graficoHeader">
            <h3 class="tituloGrafico">Top 5 Combinaciones Color-Talla</h3>
          </div>
          <div class="contenedorGrafico" id="graficoCombinacionesColorTalla">
            <div class="tablaTopCalzadosContenedor">
              <table id="tablaCombinaciones">
                <thead>
                  <tr>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Total Vendido</th>
                  </tr>
                </thead>
                <tbody id="tablaCombinacionesColorTalla"></tbody>
              </table>
            </div>
          </div>  
        </div>            
      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/alertas.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="../../js/funcionesDashboards.js"></script>
    <script src="../../js/scriptDashboardEspecifico.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js"></script>
    <script src="../../js/PDFEspecifico.js"></script>
    <script src="../../js/cerrarSesionModulos.js"></script>
    <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  </body>
</html>