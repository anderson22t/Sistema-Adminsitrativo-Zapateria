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
    <?php include '../vistas/sidebarModulos.php'; ?>
    <section class="moduloPrincipal">
      <ion-icon name="menu" id="menuModulos"></ion-icon>
      <h1 class="titulo InvMateriales">Inventario Materiales</h1>
      <form action="" class="contenedorFiltrosMateriales" id="formularioFiltro">
        <div class="camposfiltros">
          <label for="filtroDescripcion">Descripción de Material</label>
          <input type="text" name="filtroDescripcion" id="filtroDescripcion"/>
        </div>
        <button type="submit" class="btnFiltroCompras">Buscar Material</button>
      </form>
      <table class="tablaInventarioMateriales" border="1">
        <thead class="encabezadosTablas">
          <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cantidad</th>
          </tr>
        </thead>
        <tbody id="tablaMaterialesBody">
          <!-- Aquí se llenarán los datos de la tabla mediante JavaScript -->
        </tbody>
      </table>
      <div class="paginacion Materiales">
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
      <script
      type="module"
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"
      ></script>
      <script
      nomodule
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"
      ></script>
      <script src="../../js/scriptInventarioMateriales.js"></script>
      <script src="../../js/cerrarSesionModulos.js"></script>
  </body>
</html>
