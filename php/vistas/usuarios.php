<?php
if (!defined('USUARIOS_VISTA')) {
    http_response_code(404);
    exit();
}

$esAdministrador = (bool) ($esAdministrador ?? false);
$usuarioActual = $usuarioActual ?? [];
$empleados = $empleados ?? [];
$roles = $roles ?? [];
$tokenFormulario = (string) ($tokenFormulario ?? '');
$errorCarga = $errorCarga ?? null;
$mensajeFormulario = $mensajeFormulario ?? null;
$escape = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
$nombreUsuarioActual = $usuarioActual['nombre_Usuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <base href="../../vistas/" />
  <title>Configuración de Usuario</title>
  <link rel="icon" type="image/png" href="../../componentes/iconos/favicon-32x32.png" />
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link rel="stylesheet" href="../../css/responsive.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/sidebarModulos.php'; ?>
  <section class="moduloPrincipal">
    <header class="headerModulo">
      <div class="headerModuloIzquierda">
        <ion-icon name="menu" id="menuModulos"></ion-icon>
        <h1 class="titulo tituloUsuarios">Configuración</h1>
      </div>
      <div class="usuarioWrapper">
        <div class="contenedorUsuario Modulos">
          <ion-icon name="person-circle-outline" id="iconoUsuario"></ion-icon>
          <p id="nombreUsuarioEncabezado"><?= $escape($nombreUsuarioActual) ?></p>
        </div>
        <div class="configuracionUsuario Modulos">
          <div class="opcionesUsuario" id="cerrarSesion">
            <ion-icon name="log-out-outline"></ion-icon>
            <p>Cerrar sesión</p>
          </div>
        </div>
      </div>
    </header>

    <main class="gestionUsuarios">
      <?php if ($errorCarga !== null): ?>
        <p class="mensajeUsuarios error" role="alert"><?= $escape($errorCarga) ?></p>
      <?php else: ?>
        <?php if ($esAdministrador): ?>
          <div class="opcionesConfiguracion" role="tablist" aria-label="Opciones de configuración">
            <button type="button" id="tabMisDatos" role="tab" aria-selected="true" aria-controls="panelMisDatos">
              Mis datos
            </button>
            <button type="button" id="tabCrearUsuario" role="tab" aria-selected="false" aria-controls="panelCrearUsuario" tabindex="-1">
              Crear usuario
            </button>
          </div>
        <?php endif; ?>

        <section
          class="formularioUsuarios panelConfiguracion"
          id="panelMisDatos"
          <?php if ($esAdministrador): ?>role="tabpanel" aria-labelledby="tabMisDatos"<?php endif; ?>
        >
          <h2>Mis datos</h2>
          <?php if ($mensajeFormulario !== null): ?>
            <p class="mensajeUsuarios exito" role="status"><?= $escape($mensajeFormulario) ?></p>
          <?php endif; ?>

          <p class="ayudaUsuarios">Actualiza los datos asociados a tu cuenta.</p>
          <form id="formularioActualizarUsuario" action="../configuraciones/usuarios/actualizarUsuario.php" method="post">
              <input type="hidden" name="token_csrf" value="<?= $escape($tokenFormulario) ?>" />
              <div class="camposUsuario">
                <div class="campoUsuario">
                  <label for="nombreUsuarioActual">Nombre de usuario</label>
                  <input type="text" id="nombreUsuarioActual" name="nombreUsuario" maxlength="255" autocomplete="username" value="<?= $escape($nombreUsuarioActual) ?>" required />
                </div>
                <div class="campoUsuario">
                  <label for="correoActual">Correo electrónico</label>
                  <input type="email" id="correoActual" name="correo" maxlength="255" autocomplete="email" value="<?= $escape($usuarioActual['correo'] ?? '') ?>" required />
                </div>
                <div class="campoUsuario">
                  <label for="empleadoActual">Nombre de empleado</label>
                  <input type="text" id="empleadoActual" name="nombreEmpleado" list="opcionesEmpleadosActual" maxlength="255" value="<?= $escape($usuarioActual['nombreEmpleado'] ?? '') ?>" required />
                  <datalist id="opcionesEmpleadosActual">
                    <?php foreach ($empleados as $empleado): ?>
                      <option value="<?= $escape($empleado['nombre'] ?? '') ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                </div>
                <div class="campoUsuario">
                  <label for="contrasenaActual">Nueva contraseña (opcional)</label>
                  <div class="campoContraseñaUsuario">
                    <input type="password" id="contrasenaActual" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" />
                    <button type="button" class="btnMostrarContrasenaUsuario" data-target="contrasenaActual" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                      <ion-icon name="eye-outline"></ion-icon>
                    </button>
                  </div>
                  <small>Mínimo 8 caracteres. Déjala vacía si no deseas cambiarla.</small>
                </div>
                <div class="campoUsuario">
                  <label for="rolActual">Rol</label>
                  <input type="text" id="rolActual" value="<?= $escape($usuarioActual['rolUsuario'] ?? 'Sin rol asignado') ?>" readonly />
                </div>
              </div>
              <p id="mensajeActualizarUsuario" class="mensajeUsuarios" role="status" aria-live="polite"></p>
              <button type="submit" class="btnCrearUsuario">Guardar cambios</button>
          </form>
        </section>

        <?php if ($esAdministrador): ?>
          <section
            class="formularioUsuarios panelConfiguracion"
            id="panelCrearUsuario"
            role="tabpanel"
            aria-labelledby="tabCrearUsuario"
            hidden
          >
            <h2>Crear usuario</h2>
            <form id="formularioCrearUsuario" action="../configuraciones/usuarios/crearUsuario.php" method="post">
              <input type="hidden" name="token_csrf" value="<?= $escape($tokenFormulario) ?>" />
              <div class="camposUsuario">
                <div class="campoUsuario">
                  <label for="nombreUsuario">Nombre de usuario</label>
                  <input type="text" id="nombreUsuario" name="nombreUsuario" maxlength="255" autocomplete="username" required />
                </div>
                <div class="campoUsuario">
                  <label for="contrasena">Contraseña</label>
                  <div class="campoContraseñaUsuario">
                    <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" required />
                    <button type="button" class="btnMostrarContrasenaUsuario" data-target="contrasena" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                      <ion-icon name="eye-outline"></ion-icon>
                    </button>
                  </div>
                  <small>Mínimo 8 caracteres</small>
                </div>
                <div class="campoUsuario">
                  <label for="correo">Correo electrónico</label>
                  <input type="email" id="correo" name="correo" maxlength="255" autocomplete="email" required />
                </div>
                <div class="campoUsuario">
                  <label for="nombreEmpleado">Nombre de empleado</label>
                  <input type="text" id="nombreEmpleado" name="nombreEmpleado" list="opcionesEmpleadosCrear" maxlength="255" required />
                  <datalist id="opcionesEmpleadosCrear">
                    <?php foreach ($empleados as $empleado): ?>
                      <option value="<?= $escape($empleado['nombre'] ?? '') ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                </div>
                <div class="campoUsuario">
                  <label for="rolUsuario">Rol del usuario</label>
                  <input type="text" id="rolUsuario" name="rolUsuario" list="opcionesRoles" maxlength="255" required />
                  <datalist id="opcionesRoles">
                    <?php foreach ($roles as $rol): ?>
                      <option value="<?= $escape($rol['rolUsuario'] ?? '') ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                </div>
              </div>
              <p id="mensajeCrearUsuario" class="mensajeUsuarios" role="status" aria-live="polite"></p>
              <button type="submit" class="btnCrearUsuario">Crear usuario</button>
            </form>
          </section>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </section>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="../../js/cerrarSesionModulos.js"></script>
  <script src="../../js/crearUsuario.js"></script>
  <?php if ($esAdministrador): ?>
    <script>
      const tabsConfiguracion = document.querySelectorAll('[role="tab"]');
      const panelesConfiguracion = document.querySelectorAll('[role="tabpanel"]');

      tabsConfiguracion.forEach((tab) => {
        tab.addEventListener("click", () => {
          tabsConfiguracion.forEach((item) => {
            const seleccionado = item === tab;
            item.setAttribute("aria-selected", String(seleccionado));
            item.tabIndex = seleccionado ? 0 : -1;
          });

          panelesConfiguracion.forEach((panel) => {
            panel.hidden = panel.id !== tab.getAttribute("aria-controls");
          });
        });
      });
    </script>
  <?php endif; ?>
</body>
</html>
