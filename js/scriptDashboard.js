document.addEventListener("DOMContentLoaded", () => {
  const btnFiltrosAvanzados = document.getElementById("btnFiltrosAvanzados");
  const panelFiltrosLateral = document.getElementById("panelFiltrosLateral");
  const overlayFiltros = document.getElementById("overlayFiltros");
  const btnCerrarFiltros = document.getElementById("btnCerrarFiltros");
  const formFiltros = document.getElementById("formularioFiltrosAvanzados");
  const btnMenuModulos = document.getElementById("menuModulos");
  const sidebar = document.querySelector(".sidebar");

  // Abrir sidebar de navegación de módulos
  if (btnMenuModulos && sidebar) {
    btnMenuModulos.addEventListener("click", () => {
      sidebar.classList.toggle("activo");
      if (sidebar.style.display === "block") {
        sidebar.style.display = "none";
      } else {
        sidebar.style.display = "block";
        sidebar.style.zIndex = "1000";
      }
    });
  }

  // Función para abrir el panel de filtros
  function abrirPanelFiltros() {
    if (panelFiltrosLateral && overlayFiltros) {
      panelFiltrosLateral.classList.add("activo");
      overlayFiltros.classList.add("activo");
      document.body.style.overflow = "hidden"; // Evitar scroll de fondo
    }
  }

  // Función para cerrar el panel de filtros
  function cerrarPanelFiltros() {
    if (panelFiltrosLateral && overlayFiltros) {
      panelFiltrosLateral.classList.remove("activo");
      overlayFiltros.classList.remove("activo");
      document.body.style.overflow = ""; // Restaurar scroll
    }
  }

  // Event Listeners para abrir / cerrar
  if (btnFiltrosAvanzados) {
    btnFiltrosAvanzados.addEventListener("click", abrirPanelFiltros);
  }

  if (btnCerrarFiltros) {
    btnCerrarFiltros.addEventListener("click", cerrarPanelFiltros);
  }

  if (overlayFiltros) {
    overlayFiltros.addEventListener("click", cerrarPanelFiltros);
  }

  // Cerrar con tecla Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && panelFiltrosLateral?.classList.contains("activo")) {
      cerrarPanelFiltros();
    }
  });

  // Manejo de botones de rango de tiempo (Hoy, Semana, Mes, Año)
  const btnsRango = document.querySelectorAll(".btnFiltrosGenerales");
  btnsRango.forEach((btn) => {
    btn.addEventListener("click", () => {
      btnsRango.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
    });
  });

  // Limpiar filtros y restablecer valores
  const btnLimpiar = document.getElementById("btnLimpiarFiltros");
  if (btnLimpiar && formFiltros) {
    btnLimpiar.addEventListener("click", () => {
      formFiltros.reset();
    });
  }
});
