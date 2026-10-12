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
  <title>Inventario Producción</title>
  <link rel="icon" type="image/png" href="../../componentes/iconos/favicon-32x32.png" />
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
  <?php include '../vistas/sidebarModulos.php'; ?>
  <section class="moduloPrincipal">
    <header class="headerModulo">
      <div class="headerModuloIzquierda">
        <ion-icon name="menu" id="menuModulos"></ion-icon>
        <h1 class="titulo InvProduccion">Inventario Producción</h1>
      </div>
      <?php include 'usuarioModulos.php'; ?>
    </header>
    <section class="panelInventarioConsulta">
      <div class="encabezadoInventarioConsulta">
        <div class="textoEncabezadoInventario">
          <h2>Inventario de Producción</h2>
          <p>Consulta las existencias de calzado por modelo, color y talla.</p>
        </div>
        <div class="accionesInventarioConsulta">
          <button type="button" id="mostrarFiltrosProduccion" class="btnMostrarFiltros" aria-controls="filtrosInventarioProduccion" aria-expanded="false">
            <ion-icon name="filter-outline"></ion-icon>
            Filtrar búsqueda
          </button>
        </div>
      </div>
      <form action="" id="filtrosInventarioProduccion" class="contenedorFiltrosProduccion filtrosInventario" hidden>
        <div class="camposfiltros">
          <label for="codigoCalzado">Código Calzado</label>
          <input type="number" name="filtroCodigoCalzado" />
        </div>
        <div class="camposfiltros">
          <label for="tipoCalzado">Tipo</label>
          <input type="text" name="filtroTipoCalzado" />
        </div>
        <div class="camposfiltros">
          <label for="lineaCalzado">Línea</label>
          <input type="text" name="filtroLineaCalzado" />
        </div>
        <div class="camposfiltros">
          <label for="tallaCalzado">Talla</label>
          <input type="number" name="filtroTallaCalzado" />
        </div>
        <div class="camposfiltros">
          <label for="colorCalzado">Color</label>
          <input type="text" name="filtroColorCalzado" />
        </div>
        <button type="submit" class="btnFiltroCompras">Aplicar Filtro</button>
      </form>
      <div class="contenedorInventario Produccion">
        <table class="tablaInventarioProduccion" >
          <thead class="encabezadosTablas">
            <tr>
              <th>Código</th>
              <th>Descripción</th>
              <th>Tipo</th>
              <th>Línea</th>
              <th>Color</th>
              <th>Talla</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody id="datosTablaMateriales">
          </tbody>
        </table>
      </div>
      <div class="paginacion Produccion">
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
    </section>
  </section>
  <script type="module" src="../../js/scriptInventarioProduccion.js"></script>
  <script src="../../js/cerrarSesionModulos.js"></script>
  <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>