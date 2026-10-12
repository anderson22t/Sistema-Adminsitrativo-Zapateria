<div class="usuarioWrapper">
  <div class="contenedorUsuario Modulos">
    <ion-icon name="person-circle-outline" id="iconoUsuario"></ion-icon>
    <p><?php echo $_SESSION['usuario']; ?> </p>
  </div>
  <div class="configuracionUsuario Modulos">
    <a class="opcionesUsuario" href="../configuraciones/usuarios/usuario.php">
      <ion-icon name="settings-outline"></ion-icon>
      <p>Configuración</p>
    </a>
    <div class="opcionesUsuario" id="cerrarSesion">
      <ion-icon name="log-out-outline"></ion-icon>
      <p>Cerrar Sesión</p>
    </div>
  </div>
</div>
