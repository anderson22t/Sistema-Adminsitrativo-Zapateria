const btnMostrarFiltros = document.getElementById('mostrarFiltrosProduccion');
const contenedorFiltros = document.getElementById('filtrosInventarioProduccion');
btnMostrarFiltros.addEventListener('click', function() {
    contenedorFiltros.hidden = !contenedorFiltros.hidden;
    btnMostrarFiltros.setAttribute('aria-expanded', String(!contenedorFiltros.hidden));
});


document.addEventListener('DOMContentLoaded', function () {
    let paginaActual = 1;
    const registrosPorPagina = 20;
    const tabla = document.querySelector('#datosTablaMateriales');

    const formFiltro = document.querySelector('.contenedorFiltrosProduccion');
    const filtros = {
        filtroCodigoCalzado: '',
        filtroTipoCalzado: '',
        filtroLineaCalzado: '',
        filtroTallaCalzado: '',
        filtroColorCalzado: ''
    };

    function cargarDatos(pagina) {
        const params = new URLSearchParams({
            p: pagina,
            ...filtros
        });

        fetch(`../../php/configuraciones/consultarDatos/seleccionProduccion.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const { datos, totalPaginas, paginaActual: paginaResp } = data;

                tabla.innerHTML = '';
                datos.forEach(fila => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${fila.ID_Calzado}</td>
                        <td>${fila.descripcionCalzado}</td>
                        <td>${fila.descripcionTipo}</td>
                        <td>${fila.linea}</td>
                        <td>${fila.color}</td>
                        <td>${fila.Talla}</td>
                        <td>${fila.Cantidad}</td>
                    `;
                    tabla.appendChild(tr);
                });

                actualizarPaginacion(totalPaginas, paginaResp);
                paginaActual = paginaResp;
            })
            .catch(error => console.error('Error al cargar los datos:', error));
    }

    function actualizarPaginacion(totalPaginas, paginaActual) {
        document.getElementById('paginacionAnterior').disabled = (paginaActual <= 1);
        document.getElementById('paginacionSiguiente').disabled = (paginaActual >= totalPaginas);
        document.getElementById('contadorPaginas').textContent = `Página ${paginaActual} de ${totalPaginas}`;
    }

    // Cargar la primera vez
    cargarDatos(paginaActual);

    // Botones de paginación
    document.getElementById('paginacionAnterior').addEventListener('click', () => {
        if (paginaActual > 1) cargarDatos(--paginaActual);
    });

    document.getElementById('paginacionSiguiente').addEventListener('click', () => {
        cargarDatos(++paginaActual);
    });

    // Aplicar filtro
    formFiltro.addEventListener('submit', function (e) {
        e.preventDefault();

        filtros.filtroCodigoCalzado = formFiltro.filtroCodigoCalzado.value.trim();
        filtros.filtroTipoCalzado = formFiltro.filtroTipoCalzado.value.trim();
        filtros.filtroLineaCalzado = formFiltro.filtroLineaCalzado.value.trim();
        filtros.filtroTallaCalzado = formFiltro.filtroTallaCalzado.value.trim();
        filtros.filtroColorCalzado = formFiltro.filtroColorCalzado.value.trim();

        paginaActual = 1;
        cargarDatos(paginaActual);
    });
});
