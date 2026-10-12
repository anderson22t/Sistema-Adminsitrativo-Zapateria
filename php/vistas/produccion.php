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
    <title>Producción</title>
    <link rel="icon" type="image/png" href="../../componentes/iconos/favicon-32x32.png" />
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
      <header class="headerModulo centrado">
        <div class="headerModuloIzquierda">
          <ion-icon name="menu" id="menuModulos"></ion-icon>
          <h1 class="titulo IngresoProduccion">Ingreso de Producción</h1>
        </div>
        <?php include 'usuarioModulos.php'; ?>
      </header>
      <div class="botonesFormularios">
        <button class="btnCalzado" id="calzadoNuevo">Añadir a Catálogo</button>
        <button class="btnCalzado" id="calzadoExistente">
          Registrar Producción
        </button>
      </div>
      <div class="catalogoCalzado">
        <div class="encabezadoProduccion Catalogo encabezadoCatalogoProduccion">
            <div class="textoEncabezadoProduccion">
                <h2>Catálogo de Calzados</h2>
                <p>Consulta y administra los modelos de calzado disponibles para producción y pedidos.</p>
            </div>
            <div class="opcionesProduccion Catalogo">
                <div class="opcionFiltro">
                    <input type="search" name="buscarCodigoCatalogo" id="buscarCodigo" placeholder="Buscar por código">
                </div>
                <div class="opcionRegistro">
                    <button class="btnRegistroCalzado" id="btnNuevoCalzado">Añadir Calzado</button>
                </div>
            </div>
        </div>
        <div class="contenedorTablaProduccion">
            <table class="tablaCatalogo">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Línea</th>
                        <th>Descripción</th>
                        <th>Precio Costo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="datosTablaCatalogo">
                    
                </tbody>
            </table>
        </div>
      </div>
      <div class="modalCatalogo" id="modalCatalogo" hidden>
        <div class="contenidoModalCatalogo" role="dialog" aria-modal="true" aria-labelledby="tituloModalCalzado">
          <div class="encabezadoModalCatalogo">
            <h2 id="tituloModalCalzado">Registrar nuevo calzado</h2>
            <button type="button" class="btnCerrarModalCatalogo" id="btnCerrarModalCatalogo" aria-label="Cerrar formulario">
              <ion-icon name="close"></ion-icon>
            </button>
          </div>
          <form class="formularioCalzado Nuevo" id="formCalzadoNuevo">
            <div class="campos">
              <label for="codigoCalzadoNuevo">Código Calzado</label>
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
              <label for="linea">Línea</label>
              <input type="text" name="linea" id="linea" required />
            </div>
            <div class="campos">
              <label for="precioCosto">Precio Costo</label>
              <input type="number" name="precioCosto" id="precioCosto" min="0" step="0.01" required />
            </div>
            <div class="campos" id="campoEstadoCalzado" hidden>
              <label for="estadoCalzado">Estado</label>
              <select name="estado" id="estadoCalzado" disabled>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
            <input type="submit" value="Añadir Nuevo Calzado" class="btnRegistrarCalzado" id="btnGuardarCalzado" />
          </form>
        </div>
      </div>
      <div class="panelRegistrarProduccion" id="panelRegistrarProduccion" hidden>
        <div class="encabezadoProduccion Panel encabezadoHistorialProduccion">
            <div class="textoEncabezadoHistorialProduccion">
                <h2>Historial de Producción</h2>
                <p>Consulta y administra los registros de producción.</p>
            </div>
            <div class="opcionesProduccion Panel">
                <div class="opcionFiltro">
                    <input type="search" name="buscarCodigoProduccion" id="buscarCodigoProduccion" placeholder="Buscar por código">
                </div>
                <div class="opcionRegistro">
                    <button class="btnRegistroCalzado" id="btnNuevaProduccion">Añadir Registro</button>
                </div>
            </div>
        </div>
        <div class="contenedorTablaProduccion">
            <table class="tablaProduccion">
                <thead>
                    <tr>
                        <th>Fecha Producción</th>
                        <th>Código Calzado</th>
                        <th>Color</th>
                        <th>Talla</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="datosTablaProduccion">
                    
                </tbody>
            </table>
        </div>
      </div>
      <div class="modalCatalogo" id="modalProduccion" hidden>
        <div class="contenidoModalCatalogo" role="dialog" aria-modal="true" aria-labelledby="tituloModalProduccion">
          <div class="encabezadoModalCatalogo">
            <h2 id="tituloModalProduccion">Registrar Producción</h2>
            <button type="button" class="btnCerrarModalCatalogo" id="btnCerrarModalProduccion" aria-label="Cerrar formulario">
              <ion-icon name="close"></ion-icon>
            </button>
          </div>
          <form class="formularioCalzado Existente" id="formCalzadoExistente">
            <input type="hidden" name="codigoIngresoProduccion" id="codigoIngresoProduccion" />
            <div class="campos">
              <label for="codigoCalzadoExistente">Código Calzado</label>
              <select name="codigoCalzadoExistente" id="codigoCalzadoExistente" required>
                <option value="">Código de Calzados</option>
                <?php include '../configuraciones/consultarDatos/codigosProductos.php'; ?>
              </select>
            </div>
            <div class="campos">
              <label for="colorCalzado">Color</label>
              <input type="text" name="colorCalzado" id="colorCalzado" required />
            </div>
            <div class="campos">
              <label for="tallaCalzado">Talla</label>
              <input type="number" name="tallaCalzado" id="tallaCalzado" min="0" step="0.5" required />
            </div>
            <div class="campos">
              <label for="cantidadCalzado">Cantidad</label>
              <input type="number" name="cantidadCalzado" id="cantidadCalzado" min="1" step="1" required />
            </div>
            <div class="campos">
              <label for="fechaProduccion">Fecha de Producción</label>
              <input type="date" name="fechaProduccion" id="fechaProduccion" required />
            </div>
            <input type="submit" value="Añadir a Inventario" class="btnRegistrarCalzado" id="btnGuardarProduccion"/>
          </form>
        </div>
      </div>
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