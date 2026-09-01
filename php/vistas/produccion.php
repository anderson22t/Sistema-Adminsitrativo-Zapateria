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
    <?php include '../../php/vistas/sidebarModulos.php' ?>
    <section class="moduloPrincipal">
      <ion-icon name="menu" id="menuModulos"></ion-icon>
      <h1 class="titulo IngresoProduccion">Ingreso de Producción</h1>
      <div class="botonesFormularios">
        <button class="btnCalzado" id="calzadoNuevo">Calzado Nuevo</button>
        <button class="btnCalzado" id="calzadoExistente">
          Calzado Existente
        </button>
      </div>
      <form action="../configuraciones/ingresoNuevoCalzado.php" method="POST" class="formularioCalzado Nuevo" id="formCalzadoNuevo">
        <div class="campos">
          <label for="codigoCalzadoNuevo">Codigo Calzado</label>
          <input type="number" name="codigoCalzadoNuevo" id="codigoCalzadoNuevo" required />
        </div>
        <div class="campos">
          <label for="tipo">Tipo</label>
          <input type="text" name="tipo" id="tipo" required />
        </div>
        <div class="campos">
          <label for="descripcionCalzadoNuevo">Descripción</label>
          <input type="text" name="descripcionCalzadoNuevo" id="descripcionCalzadoNuevo" required />
        </div>
        <div class="campos">
          <label for="linea">Linea</label>
          <input type="text" name="linea" id="linea" required />
        </div>
        <div class="campos">
          <label for="precioCosto">Precio Costo</label>
          <input type="number" name="precioCosto" id="precioCosto" required />
        </div>
        <input
          type="submit"
          value="Añadir Nuevo Calzado"
          class="btnRegistrarCalzado"
        />
      </form>
      <form
        action="../configuraciones/ingresoProduccionExistente.php"
        method="POST"
        class="formularioCalzado Existente"
        id="formCalzadoExistente"
      >
        <div class="campos">
          <label for="codigoCalzadoExistente">Codigo Calzado</label>
          <select name="codigoCalzadoExistente" id="codigoCalzadoExistente" required>
            <option value="">Código de Calzados</option>
            <?php include '../configuraciones/codigosProductos.php'; ?>
          </select>
        </div>
        <div class="campos">
            <label for="descripcionProducto">Descripción</label>
            <p class="descripcionProducto" id="descripcionProducto">Selecciona un código</p>
          </div>
        <div class="campos">
          <label for="colorCalzado">Color</label>
          <input type="text" name="colorCalzado" id="colorCalzado" required />
        </div>
        <div class="campos">
          <label for="tallaCalzado">Talla</label>
          <input type="number" name="tallaCalzado" id="tallaCalzado" required />
        </div>
        <div class="campos">
          <label for="cantidadCalzado">Cantidad</label>
          <input type="number" name="cantidadCalzado" id="cantidadCalzado" required />
        </div>
        <div class="campos">
          <label for="fechaProduccion">Fecha de Producción</label>
          <input type="date" name="fechaProduccion" id="fechaProduccion" required />
        </div>
        <input type="submit" value="Añadir a Inventario" class="btnRegistrarCalzado"/>
      </form>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/alertas.js"></script>
    <script src="../../js/scriptProducción.js"></script>
    <script src="../../js/cerrarSesionModulos.js"></script>
    <script
    type="module"
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script
    nomodule
    src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  </body>
</html>