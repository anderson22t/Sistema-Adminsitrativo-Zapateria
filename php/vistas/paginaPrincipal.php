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
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../css/responsive.css" />
</head>

<body>
  <header>
    <ion-icon name="menu" id="menu"></ion-icon>
    <h1>ZAPATERÍA</h1>
    <div class="contenedorUsuario">
      <ion-icon name="person-circle-outline" id="iconoUsuario"></ion-icon>
      <p><?php echo $_SESSION['usuario']; ?> </p>
    </div>
  </header>
  <div class="menuLateral" id="menuLateral"></div>
  <ion-icon name="close-outline" id="iconoCerrar"></ion-icon>
  <div class="configuracionUsuario">
    <div class="opcionesUsuario">
      <ion-icon name="settings-outline"></ion-icon>
      <p>Configuración</p>
    </div>
    <div class="opcionesUsuario" id="cerrarSesion">
      <ion-icon name="log-out-outline"></ion-icon>
      <p>Cerrar Sesión</p>
    </div>
  </div>
  <?php include '../vistas/sidebarPaginaInicial.php'; ?>
  <!-- Sección de Modulos del Sistema -->
  <section class="modulos">
    <h2>Bienvenido,
      <?php echo $_SESSION['usuario']; ?>
    </h2>
    <div class="contenedorModulos">
      <div class="cardModulo">
        <a href="pedidos.php">
          <img src="../../componentes/imagenes/Registro Pedidos.svg" />
          <h2>Registro de Pedidos</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="produccion.php">
          <img src="../../componentes/imagenes/Imagen Produccion.svg" />
          <h2>Ingreso de Producción</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="compraMateriales.php">
          <img src="../../componentes/imagenes/Imagen Compra Materiales.svg" />
          <h2>Compra de Materiales</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="reportes.php">
          <img src="../../componentes/imagenes/Imagen Reportes.svg" />
          <h2>Reportes</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="inventarioProduccion.php">
          <img src="../../componentes/imagenes/Imagen Inventario Produccion.svg" />
          <h2>Inventario Producción</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="inventarioMateriales.php">
          <img src="../../componentes/imagenes/Imagen Inventario Materiales.svg" />
          <h2>Inventario Materiales</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="">
          <img src="../../componentes/imagenes/VentasGenerales.svg" />
          <h2>Análisis General</h2>
        </a>
      </div>
      <div class="cardModulo">
        <a href="">
          <img src="../../componentes/imagenes/DetalleVentas.svg" />
          <h2>Análisis de Productos</h2>
        </a>
      </div>      
    </div>
  </section>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="../../js/scriptPaginaInicial.js"></script>
</body>
</html>