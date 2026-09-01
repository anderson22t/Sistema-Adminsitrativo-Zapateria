const btnReportePedidos = document.getElementById("btnReportePedidos");
const btnReporteVenta = document.getElementById("btnReporteVenta");
const reportesPedidos = document.querySelector(".reportesPedidos");
const reportesVentas = document.querySelector(".reportesVentas");
const btnMenuModulos = document.getElementById("menuModulos");
const sidebar = document.querySelector(".sidebar");
const btnMostrarFiltrosPedidos = document.getElementById("mostrarFiltrosPedidos");
const filtrosReportesPedidos = document.querySelector(".filtrosReportesPedidos");
const btnMostrarFiltrosVentas = document.getElementById("mostrarFiltrosVentas");
const filtrosReportesVentas = document.querySelector(".filtrosReportesVentas");

btnMenuModulos.addEventListener("click", function (){
    sidebar.style.display = "block";
    sidebar.style.zIndex = "1000";
});

btnMostrarFiltrosPedidos.addEventListener("click", function(){
    if (filtrosReportesPedidos.style.display === "grid") {
        filtrosReportesPedidos.style.display = "none";
    } else {
        filtrosReportesPedidos.style.display = "grid";
    }
});

btnMostrarFiltrosVentas.addEventListener("click", function(){
    if (filtrosReportesVentas.style.display === "grid") {
        filtrosReportesVentas.style.display = "none";
    } else {
        filtrosReportesVentas.style.display = "grid";
    }
});

const botones = [btnReportePedidos, btnReporteVenta];

botones.forEach(btn => {
  btn.addEventListener("click", () => {

    // quitar activo a todos
    botones.forEach(b => b.classList.remove("activo"));

    // activar el clickeado
    btn.classList.add("activo");

    // mostrar / ocultar reportes
    if (btn === btnReportePedidos) {
      reportesPedidos.style.display = "block";
      reportesVentas.style.display = "none";
    } else {
      reportesPedidos.style.display = "none";
      reportesVentas.style.display = "block";
      mostrarReporteVenta();
    }
  });
});

/*-------------------- Funcionalidad para imprimir datos en la tabla de reportes de pedidos -------------------*/

document.addEventListener("DOMContentLoaded", function () {
  let paginaActual = 1;
  const registrosPorPagina = 20;

  const filtrosFormularios = document.getElementById("formularioFiltroPedidos");
  const tablaReportePedido = document.getElementById("bodyTablaPedidos");

  const filtros = {
    filtroNoPedido: "",
    filtroFechaPedido: "",
    filtroCliente: "",
    filtroNit: "",
    filtroFechaEntrega: ""
  };

  function cargarDatos(pagina) {
    const params = new URLSearchParams({
      p: pagina,
      ...filtros,
    });

    function formatearFecha(fecha) {
    if (!fecha) return '';
    const [año, mes, día] = fecha.split('-');
    return `${día}/${mes}/${año}`;
    }

    fetch(`../../php/configuraciones/seleccionPedido.php?${params.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        const { datos, totalPaginas, paginaActual: paginaResp } = data;

        tablaReportePedido.innerHTML = "";
        datos.forEach((fila) => {
          const tr = document.createElement("tr");
          let claseEstado = '';
          if (fila.estado === 'Pendiente') {
            claseEstado = 'estadoPendiente';
          } else if (fila.estado === 'Entregado') {
            claseEstado = 'estadoEntregado';
          } else if (fila.estado === 'Cancelado') {
            claseEstado = 'estadoCancelado';
          }

          tr.innerHTML = `
                        <td>${fila.No_Pedido}</td>
                        <td>${formatearFecha(fila.fechaPedido)}</td>
                        <td>${fila.nombreCliente}</td>
                        <td>${fila.direccionCompleta}</td>
                        <td>${formatearFecha(fila.fechaEntrega)}</td>
                        <td><button class="btnVerDetalles">Ver Detalles</button></td>
                        <td><span class="${claseEstado}">${fila.estado}</span></td>
                        <td><img src="../../componentes/iconos/editar.svg" id="Editar" class="iconoEditar">
                            <ion-icon name="trash-outline" id="Eliminar" class="iconoEliminar"></ion-icon>
                        </td>
                    `;
          tablaReportePedido.appendChild(tr);
        });
        actualizarPaginacion(totalPaginas, paginaResp);
        paginaActual = paginaResp;
      })
      .catch((error) => console.error("Error al cargar los datos:", error));
  }

  function actualizarPaginacion(totalPaginas, paginaActual) {
    document.getElementById("paginacionAnterior").disabled = paginaActual <= 1;
    document.getElementById("paginacionSiguiente").disabled =
      paginaActual >= totalPaginas;
    document.getElementById(
      "contadorPaginas"
    ).textContent = `Página ${paginaActual} de ${totalPaginas}`;
  }

  // Cargar la primera vez
  cargarDatos(paginaActual);

  // Botones de paginación
  document
    .getElementById("paginacionAnterior")
    .addEventListener("click", () => {
      if (paginaActual > 1) cargarDatos(--paginaActual);
    });

  document
    .getElementById("paginacionSiguiente")
    .addEventListener("click", () => {
      cargarDatos(++paginaActual);
    });

  // Aplicar filtro
  filtrosFormularios.addEventListener("submit", function (e) {
    e.preventDefault();

    filtros.filtroNoPedido = filtrosFormularios.filtroNoPedido.value.trim();
    filtros.filtroFechaPedido = filtrosFormularios.filtroFechaPedido.value.trim();
    filtros.filtroCliente = filtrosFormularios.filtroCliente.value.trim();
    filtros.filtroNit = filtrosFormularios.filtroNit.value.trim();
    filtros.filtroFechaEntrega = filtrosFormularios.filtroFechaEntrega.value.trim();

    paginaActual = 1;
    cargarDatos(paginaActual);
  });
});

document.getElementById("btnFiltroPedidos").addEventListener("click", function(){
    const inputFecha = document.getElementById("filtroFechaPedido").value;
    console.log(inputFecha);
    
})

function mostrarReporteVenta() {
    let paginaActual = 1;
    const registrosPorPagina = 20;
    const filtrosFormularios = document.getElementById("formularioFiltroVentas");
    const tablaReporteVentas = document.getElementById("bodyTablaVentas");
    const filtros = {
        filtroFechaVenta: "",
        filtroTipoVenta: ""
    };

    function cargarDatos(pagina) {
    const params = new URLSearchParams({
      p: pagina,
      ...filtros,
    });
    fetch(`../../php/configuraciones/reportesVentas.php?${params.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        const { datos, totalPaginas, paginaActual: paginaResp } = data;

        tablaReporteVentas.innerHTML = "";
        datos.forEach((fila) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
                        <td>${formatearFecha(fila.fechaPedido)}</td>
                        <td>${fila.cantidad_formaPago1}</td>
                        <td>${fila.montoTotal_formaPago1}</td>
                        <td>${fila.cantidad_formaPago2}</td>
                        <td>${fila.montoTotal_formaPago2}</td>
                        <td>${fila.cantidad_formaPago3}</td>
                        <td>${fila.montoTotal_formaPago3}</td>
                        <td>${fila.cantidad_formaPago4}</td>
                        <td>${fila.montoTotal_formaPago4}</td>
                        <td>${fila.cantidad_total}</td>
                        <td>${Number(fila.montoTotal_total).toLocaleString('es-GT', { style: 'currency', currency: 'GTQ' }).replace('GTQ', '').trim()}</td>
                    `;
          tablaReporteVentas.appendChild(tr);
        });
        actualizarPaginacion(totalPaginas, paginaResp);
        paginaActual = paginaResp;
      })
      .catch((error) => console.error("Error al cargar los datos:", error));
  }

  function actualizarPaginacion(totalPaginas, paginaActual) {
    document.getElementById("paginacionAnterior").disabled = paginaActual <= 1;
    document.getElementById("paginacionSiguiente").disabled =
      paginaActual >= totalPaginas;
    document.getElementById(
      "contadorPaginas"
    ).textContent = `Página ${paginaActual} de ${totalPaginas}`;
  }

  // Cargar la primera vez
  cargarDatos(paginaActual);

  // Botones de paginación
  document
    .getElementById("paginacionAnterior")
    .addEventListener("click", () => {
      if (paginaActual > 1) cargarDatos(--paginaActual);
    });

  document
    .getElementById("paginacionSiguiente")
    .addEventListener("click", () => {
      cargarDatos(++paginaActual);
    });

  // Aplicar filtro
  filtrosFormularios.addEventListener("submit", function (e) {
    e.preventDefault();

    filtros.filtroFechaVenta = filtrosFormularios.filtroFechaVenta.value.trim();
    filtros.filtroTipoVenta = filtrosFormularios.filtroTipoVenta.value.trim();
    paginaActual = 1;
    cargarDatos(paginaActual);
  });
}
