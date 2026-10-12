const formulariosUsuario = [
  {
    formulario: document.getElementById("formularioCrearUsuario"),
    mensajeId: "mensajeCrearUsuario",
    textoProgreso: "Creando usuario...",
  },
  {
    formulario: document.getElementById("formularioActualizarUsuario"),
    mensajeId: "mensajeActualizarUsuario",
    textoProgreso: "Guardando cambios...",
  },
];

document.querySelectorAll(".btnMostrarContrasenaUsuario").forEach((boton) => {
  const campo = document.getElementById(boton.dataset.target);
  const icono = boton.querySelector("ion-icon");

  if (!campo || !icono) {
    return;
  }

  boton.addEventListener("click", () => {
    const mostrandoContrasena = campo.type === "text";
    campo.type = mostrandoContrasena ? "password" : "text";
    icono.name = mostrandoContrasena ? "eye-outline" : "eye-off-outline";

    const descripcion = mostrandoContrasena
      ? "Mostrar contraseña"
      : "Ocultar contraseña";
    boton.setAttribute("aria-label", descripcion);
    boton.setAttribute("title", descripcion);
  });
});

formulariosUsuario.forEach(({ formulario, mensajeId, textoProgreso }) => {
  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    if (!formulario.reportValidity()) {
      return;
    }

    const botonEnviar = formulario.querySelector('button[type="submit"]');
    const mensaje = document.getElementById(mensajeId);
    botonEnviar.disabled = true;
    mensaje.textContent = textoProgreso;
    mensaje.classList.remove("error", "exito");

    try {
      const respuesta = await fetch(formulario.action, {
        method: "POST",
        body: new FormData(formulario),
        headers: { Accept: "application/json" },
      });
      const resultado = await respuesta.json();

      if (!respuesta.ok || !resultado.success) {
        throw new Error(resultado.message || "No fue posible completar la operación.");
      }

      mensaje.textContent = resultado.message;
      mensaje.classList.add("exito");

      if (formulario.id === "formularioCrearUsuario") {
        formulario.reset();
      } else {
        const nombreActualizado = new FormData(formulario).get("nombreUsuario");
        const nombreEncabezado = document.getElementById("nombreUsuarioEncabezado");
        if (nombreEncabezado && typeof nombreActualizado === "string") {
          nombreEncabezado.textContent = nombreActualizado;
        }
        formulario.querySelector('[name="contrasena"]').value = "";
      }
    } catch (error) {
      mensaje.textContent = error.message || "Ocurrió un error al comunicarse con el servidor.";
      mensaje.classList.add("error");
    } finally {
      botonEnviar.disabled = false;
    }
  });
});
