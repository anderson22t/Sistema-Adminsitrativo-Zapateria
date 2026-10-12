document.addEventListener("DOMContentLoaded", () => {
  const formulario = document.getElementById("formularioFiltro");
  const inputDescripcion = document.getElementById("filtroDescripcion");
  const tabla = document.getElementById("tablaMaterialesBody");
  const contadorPaginas = document.getElementById("contadorPaginas");
  const btnAnterior = document.getElementById("paginacionAnterior");
  const btnSiguiente = document.getElementById("paginacionSiguiente");
  const btnMostrarFiltros = document.getElementById("mostrarFiltrosMateriales");
  const contenedorFiltros = document.querySelector(".contenedorFiltrosMateriales");

  // Toggle de filtros del inventario.
  btnMostrarFiltros.addEventListener("click", () => {
    contenedorFiltros.hidden = !contenedorFiltros.hidden;
    btnMostrarFiltros.setAttribute("aria-expanded", String(!contenedorFiltros.hidden));
  });

  let paginaActual = 1;
  let totalPaginas = 1;

  // Función para cargar materiales
  const cargarMateriales = async (descripcion = "", pagina = 1) => {
    try {
      const response = await fetch(
        `../../php/configuraciones/consultarDatos/seleccionMateriales.php?descripcion=${encodeURIComponent(descripcion)}&pagina=${pagina}`
      );
      if (!response.ok) throw new Error("No se pudo obtener los materiales");

      const datos = await response.json();
      
      if (!datos.materiales) {
        throw new Error("Los datos de materiales no están disponibles");
      }

      tabla.innerHTML = "";

      if (datos.materiales.length === 0) {
        tabla.innerHTML = "<tr><td colspan='3'>No se encontraron resultados.</td></tr>";
      } else {
        datos.materiales.forEach((material) => {
          const fila = document.createElement("tr");
          fila.innerHTML = `
            <td>${material.ID_Material}</td>
            <td>${material.descripcionMaterial}</td>
            <td>${material.CantidadActual}</td>
          `;
          tabla.appendChild(fila);
        });
      }

      // Actualizar total de páginas y mostrar el contador
      totalPaginas = datos.totalPaginas;
      paginaActual = datos.paginaActual;

      contadorPaginas.textContent = `Página ${paginaActual} de ${totalPaginas}`;

      // Activar/desactivar botones de paginación
      btnAnterior.disabled = paginaActual <= 1;
      btnSiguiente.disabled = paginaActual >= totalPaginas;
    } catch (error) {
      console.error("Error al cargar materiales:", error);
    }
  };

  // Cargar todos los materiales al inicio
  cargarMateriales();

  // Capturar evento del formulario
  formulario.addEventListener("submit", (e) => {
    e.preventDefault();
    const descripcion = inputDescripcion.value.trim();
    cargarMateriales(descripcion);
  });

  // Paginación anterior
  btnAnterior.addEventListener("click", () => {
    if (paginaActual > 1) {
      cargarMateriales(inputDescripcion.value.trim(), paginaActual - 1);
    }
  });

  // Paginación siguiente
  btnSiguiente.addEventListener("click", () => {
    if (paginaActual < totalPaginas) {
      cargarMateriales(inputDescripcion.value.trim(), paginaActual + 1);
    }
  });
});
