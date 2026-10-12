const formularioSesion = document.getElementById("formularioInicioSesion");
const contraseña = document.getElementById("contraseña");
const btnMostrarContraseña = document.getElementById("btnMostrarContraseña");
const iconoContraseña = btnMostrarContraseña.querySelector("ion-icon");

btnMostrarContraseña.addEventListener("click", () => {
  const mostrandoContraseña = contraseña.type === "text";

  contraseña.type = mostrandoContraseña ? "password" : "text";
  iconoContraseña.name = mostrandoContraseña
    ? "eye-outline"
    : "eye-off-outline";

  const descripcion = mostrandoContraseña
    ? "Mostrar contraseña"
    : "Ocultar contraseña";

  btnMostrarContraseña.setAttribute("aria-label", descripcion);
  btnMostrarContraseña.setAttribute("title", descripcion);
});

formularioSesion.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(formularioSesion);
  const respuesta = await fetch("php/configuraciones/usuarios/login.php", {
    method: "POST",
    body: formData,
  });
  const data = await respuesta.json();
  if (data.success) {
    window.location.href = data.redirect;
  } else {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: data.message,
    });
  }
});