document.addEventListener("DOMContentLoaded", async () => {
  const btnFiltrosAvanzados = document.getElementById("btnFiltrosAvanzados");
  const panelFiltrosLateral = document.getElementById("panelFiltrosLateral");
  const overlayFiltros = document.getElementById("overlayFiltros");
  const btnCerrarFiltros = document.getElementById("btnCerrarFiltros");
  const btnToggleAcciones = document.getElementById("btnToggleAcciones");
  const filtrosAcciones = document.getElementById("filtrosAcciones");

  if (btnToggleAcciones && filtrosAcciones) {
    btnToggleAcciones.addEventListener("click", () => {
      const expandido = filtrosAcciones.classList.toggle("activo");
      btnToggleAcciones.classList.toggle("activo", expandido);
      btnToggleAcciones.setAttribute("aria-expanded", String(expandido));
    });
  }

  // Interacción táctil para desplegar/colapsar detalles del banner en móviles y tablets
  const bannerEspecifico = document.getElementById("bannerDashboardEspecifico");
  const encabezadoBannerEspecifico = bannerEspecifico?.querySelector(".encabezadoBanner");
  if (encabezadoBannerEspecifico && bannerEspecifico) {
    encabezadoBannerEspecifico.addEventListener("click", () => {
      bannerEspecifico.classList.toggle("abierto");
    });
  }

  const formFiltros = document.getElementById("formularioFiltrosAvanzados");
  let fechas;
  let fechasEvolucionVentas;
  let periodo;
  let filtrosAplicados = null;
  let rangoFechasFiltros = null;
  let filtroMes = null;
  let filtroAnio = null;
  let periodoInterfaz = null;

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
    btn.addEventListener("click", async () => {
      btnsRango.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      if(filtroMes || filtroAnio){
        document.getElementById('filtroMes').value = '';
        document.getElementById('anio').value = '';
        filtroMes = null;
        filtroAnio = null;
        rangoFechasFiltrosActual = null;
      }
      periodoInterfaz = btn.value;
      periodo = btn.value;
      fechas = seleccionRango(periodo);
      fechasEvolucionVentas = seleccionRangoEvolucionVentas(periodo);

      actualizarEstadoBotonFiltros();
      await cargarDashboardActual(periodo, fechas, fechasEvolucionVentas);
      
    });
  });

  /*--------------------------- Función de recargar dashboard con los datos ---------------------------*/
  async function cargarDashboardActual(periodoCarga, fechasIndicadores, fechasGrafico, esPeriodoAnual = false) {
    periodoActual = periodoCarga;
    fechasActuales = fechasIndicadores;
    fechasEvolucionActuales = fechasGrafico;
    filtrosAplicadosActual = filtrosAplicados;

    await cargarCalzados(periodoCarga, fechasIndicadores, filtrosAplicados);
    indicadoresModeloEspecifico(codigoCalzadoMasVendido,periodoCarga,fechasIndicadores,filtrosAplicados);

    if (esPeriodoAnual) {
      CargarEvolucionVentasAnual(codigoCalzadoMasVendido, periodoCarga, fechasIndicadores, filtrosAplicados);
    } 
    else {
      CargarEvolucionVentas(codigoCalzadoMasVendido,periodoCarga,fechasGrafico,filtrosAplicados);
    }

    CargarVentasPorColor(codigoCalzadoMasVendido, fechasIndicadores, filtrosAplicados);
    CargarVentasPorTallas(codigoCalzadoMasVendido, fechasIndicadores, filtrosAplicados);
    CargarCombinacionesColorTalla(codigoCalzadoMasVendido, fechasIndicadores, filtrosAplicados);
  }

  function actualizarEstadoBotonFiltros() {
    const hayFiltroFecha = Boolean(filtroMes || filtroAnio);
    const hayFiltroVenta = Boolean(filtrosAplicados?.canalVenta || filtrosAplicados?.tipoVenta);
    const hayFiltrosAvanzados = hayFiltroFecha || hayFiltroVenta;

    btnFiltrosAvanzados.classList.toggle('activo', hayFiltrosAvanzados);
    if (btnToggleAcciones) {
      btnToggleAcciones.classList.toggle('conFiltrosActivos', hayFiltrosAvanzados);
    }
  }
  
  /* ----------------------------------- Funcion para aplicar filtros ------------------------------*/
  formFiltros.addEventListener("submit", async (e) => {
    e.preventDefault();
    const datosFiltros = Object.fromEntries(new FormData(formFiltros));
    if (datosFiltros.filtroMes && !datosFiltros.anio) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Debe seleccionar un año.",
      });
      return;
    }

    const selectCanalVenta = document.getElementById("canalVenta");
    const opcionCanalVenta = selectCanalVenta.options[selectCanalVenta.selectedIndex];
    const selectTipoVenta = document.getElementById("tipoVenta");
    const opcionTipoVenta = selectTipoVenta.options[selectTipoVenta.selectedIndex];
    filtrosAplicados = {
      canalVenta: datosFiltros.canalVenta,
      descripcionCanalVenta: opcionCanalVenta.text,
      tipoVenta: datosFiltros.tipoVenta,
      descripcionTipoVenta: opcionTipoVenta.text
    };
    filtrosAplicadosActual = filtrosAplicados;


    if(datosFiltros.anio && datosFiltros.filtroMes){
      filtroMes = datosFiltros.filtroMes;
      filtroAnio = datosFiltros.anio;
      rangoFechasFiltros = obtenerFechasRangoFiltro(datosFiltros.filtroMes, datosFiltros.anio);
      periodo = 'mesFiltro';
      periodoActual = periodo;
      rangoFechasFiltrosActual = rangoFechasFiltros;

      btnsRango.forEach((b) => b.classList.remove('active'));
      actualizarEstadoBotonFiltros();
      
      await cargarDashboardActual(periodo, rangoFechasFiltros, rangoFechasFiltros); 
      return;
    }
    else if(datosFiltros.anio && !datosFiltros.filtroMes){
      filtroMes = null;
      filtroAnio = datosFiltros.anio;
      rangoFechasFiltros = obtenerFechasRangoFiltro(null, datosFiltros.anio);
      const anioSeleccionado = Number(datosFiltros.anio);
      const anioActual = new Date().getFullYear();

      actualizarEstadoBotonFiltros();
      btnsRango.forEach((b) => b.classList.remove('active'));

      periodo = anioSeleccionado === anioActual ? 'anioAFechaActual' : 'anioHistorico';
      periodoActual = periodo;
      rangoFechasFiltrosActual = rangoFechasFiltros;
      
      await cargarDashboardActual(periodo, rangoFechasFiltros, rangoFechasFiltros, true);
      return;
    } 
    else if (!datosFiltros.anio && !datosFiltros.filtroMes && (datosFiltros.canalVenta || datosFiltros.tipoVenta)) {
      filtroMes = null;
      filtroAnio = null;
      rangoFechasFiltrosActual = null;
      periodo = periodoInterfaz || periodo;
      fechas = seleccionRango(periodo);
      fechasEvolucionVentas = seleccionRangoEvolucionVentas(periodo);
      btnsRango.forEach((b) => b.classList.toggle('active', b.value === periodo));
      actualizarEstadoBotonFiltros();
      await cargarDashboardActual(periodo, fechas, fechasEvolucionVentas);
      return;
    }
    else {
      filtrosAplicados = null;
      filtrosAplicadosActual = null;
      filtroMes = null;
      filtroAnio = null;
      rangoFechasFiltros = null;
      rangoFechasFiltrosActual = null;
      periodo = periodoInterfaz || periodo;
      fechas = seleccionRango(periodo);
      fechasEvolucionVentas = seleccionRangoEvolucionVentas(periodo);
      btnsRango.forEach((b) => b.classList.toggle('active', b.value === periodo));
      actualizarEstadoBotonFiltros();
      await cargarDashboardActual(periodo, fechas, fechasEvolucionVentas);
      return;
    }
  });

  // Limpiar filtros y restablecer valores
  const btnLimpiar = document.getElementById("btnLimpiarFiltros");
  if (btnLimpiar && formFiltros) {
    btnLimpiar.addEventListener("click", () => {
      formFiltros.reset();
      filtrosAplicados = null;
      rangoFechasFiltros = null;
      filtroMes = null;
      filtroAnio = null;
      btnFiltrosAvanzados.classList.remove('activo');
      const periodoInicial = document.getElementById('btnFiltroHoy');
      if (periodoInicial) {
        periodoInicial.click();
      }
    });
  }

  // Cargar por defecto el primer rango disponible (ej: Hoy o 7 días)
  const btnActivoInicial = document.querySelector(".btnFiltrosGenerales.active") || document.getElementById("btnFiltroHoy") || btnsRango[0];
  if (btnActivoInicial) {
    btnActivoInicial.click();
  }
});

const selectCalzados = document.getElementById('filtroCalzado');
let codigoCalzadoMasVendido;
let modeloCalzadoMasVendido;
let periodoActual;
let fechasActuales;
let fechasEvolucionActuales;
let rangoFechasFiltrosActual;
let filtrosAplicadosActual = null;

function actualizarBannerDashboardEspecifico(hayVentas, periodo, fechas, filtros) {
  const banner = document.getElementById('bannerDashboardEspecifico');
  const iconoPeriodo = document.getElementById('iconoPeriodo');
  const leyendaPeriodo = document.getElementById('leyendaPeriodo');
  const opcionPeriodo = document.getElementById('opcionPeriodo');

  const opcionCalzadoBanner = document.getElementById('opcionCalzadoBanner');
  const opcionCalzado = document.getElementById('opcionCalzado');
  const opcionCanalVentaBanner = document.getElementById('opcionCanalVentaBanner');
  const opcionCanalVenta = document.getElementById('opcionCanalVenta');

  const opcionTipoVentaBanner = document.getElementById('opcionTipoVentaBanner');
  const opcionTipoVenta = document.getElementById('opcionTipoVenta');

  if (!banner) return;


  const { tituloPeriodo, rangoFechasTexto } =
    formatearRangoTexto(periodo, fechas);

  // Información del período
  opcionPeriodo.textContent = rangoFechasTexto;

  // Valores predeterminados
  opcionCalzadoBanner.style.display = 'none';
  opcionCanalVentaBanner.style.display = 'none';
  opcionTipoVentaBanner.style.display = 'none';

  /*--------------------------- Caso 1: No hay modelo para el período --------------------------- */
  if (!codigoCalzadoMasVendido) {
    banner.className = 'bannerDashboard warning';
    iconoPeriodo.innerHTML ='<ion-icon name="alert-circle-outline"></ion-icon>';
    leyendaPeriodo.textContent = 'No hay ventas registradas para el período';
    banner.classList.add('visible');
    return;
  }

  const nombreModelo = modeloCalzadoMasVendido || 'Modelo seleccionado';
  /*--------------------------- Caso 2: El modelo no tiene ventas --------------------------- */
  if (!hayVentas) {
    banner.className = 'bannerDashboard warning';
    iconoPeriodo.innerHTML = '<ion-icon name="alert-circle-outline"></ion-icon>';
    leyendaPeriodo.textContent = `Sin ventas registradas para ${nombreModelo} en el período.`;
    banner.classList.add('visible');
    return;
  }

  /*--------------------------- Caso 3: El modelo tiene ventas --------------------------- */
  banner.className = 'bannerDashboard info';
  iconoPeriodo.innerHTML = '<ion-icon name="calendar-outline"></ion-icon>';
  leyendaPeriodo.textContent = 'Período: ' + tituloPeriodo;
  opcionCalzado.textContent = nombreModelo;
  opcionCalzadoBanner.style.display = 'inline-flex';

  /* Canal de venta */
  if (filtros && filtros.canalVenta) {
    opcionCanalVenta.textContent = filtros.descripcionCanalVenta;
  } else {
    opcionCanalVenta.textContent = 'Todos';
  }
  opcionCanalVentaBanner.style.display = 'inline-flex';

  /* Tipo de venta */
  if (filtros && filtros.tipoVenta) {
    opcionTipoVenta.textContent = filtros.descripcionTipoVenta;
  } else {
    opcionTipoVenta.textContent = 'Todos';
  }
  opcionTipoVentaBanner.style.display = 'inline-flex';

  // Revelar el banner ahora que el contenido está listo
  banner.classList.add('visible');
}


let filtrosSeleccionados = null;

async function cargarCalzados(periodo, fechas, filtros) {
  filtrosSeleccionados = filtros;
  const response = await fetch('../configuraciones/dashboard/filtros/calzados.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({fechas: fechas, filtros: filtros })
    });
    const datos = await response.json();
    const modelosConVentas = datos.filter(item => item.ResultadoVenta === 'Vendido');
    const modelosSinVentas = datos.filter(item => item.ResultadoVenta === 'No vendido');
    
    if (modelosConVentas.length > 0) {
        codigoCalzadoMasVendido = modelosConVentas[0].codigoCalzado;
        modeloCalzadoMasVendido = modelosConVentas[0].descripcionCalzado;
    } else {
        codigoCalzadoMasVendido = null;
        modeloCalzadoMasVendido = null;
    }

    selectCalzados.innerHTML = '<option value="">Filtrar por Calzado</option>';
    if (modelosConVentas.length > 0) {
        const optGroup = document.createElement('optgroup');
        optGroup.label = 'Modelos con ventas';
        modelosConVentas.forEach(calzado => {
            const option = document.createElement('option');
            option.value = calzado.codigoCalzado;
            option.dataset.descripcion = calzado.descripcionCalzado;
            option.textContent = calzado.codigoCalzado + ' - ' + calzado.descripcionCalzado;
            optGroup.appendChild(option);
        });
        selectCalzados.appendChild(optGroup);
    }
    
    if (modelosSinVentas.length > 0) {
        const optGroup = document.createElement('optgroup');
        optGroup.label = 'Modelos sin ventas';
        modelosSinVentas.forEach(calzado => {
            const option = document.createElement('option');
            option.value = calzado.codigoCalzado;
            option.dataset.descripcion = calzado.descripcionCalzado;
            option.textContent = calzado.codigoCalzado + ' - ' + calzado.descripcionCalzado;
            optGroup.appendChild(option);
        });
        selectCalzados.appendChild(optGroup);
    }

    if (codigoCalzadoMasVendido) {
        selectCalzados.value = codigoCalzadoMasVendido;
    } else {
        selectCalzados.value = "";
    }
}

selectCalzados.addEventListener('change', async () => {
    const codigoCalzado = selectCalzados.value;
    if (codigoCalzado) {
        modeloCalzadoMasVendido = selectCalzados.options[selectCalzados.selectedIndex]?.dataset?.descripcion || '';
        codigoCalzadoMasVendido = codigoCalzado;
    } else {
        modeloCalzadoMasVendido = null;
        codigoCalzadoMasVendido = null;
    }
    const fechasSeleccionadas = rangoFechasFiltrosActual || fechasActuales;
    await indicadoresModeloEspecifico(codigoCalzadoMasVendido, periodoActual, fechasSeleccionadas, filtrosAplicadosActual);

    if (periodoActual === 'anioAFechaActual' || periodoActual === 'anioHistorico') {
      CargarEvolucionVentasAnual(codigoCalzadoMasVendido, periodoActual, fechasSeleccionadas, filtrosAplicadosActual);
    } else {
      CargarEvolucionVentas(
        codigoCalzadoMasVendido,
        periodoActual,
        rangoFechasFiltrosActual ? fechasSeleccionadas : fechasEvolucionActuales,
        filtrosAplicadosActual
      );
    }

    CargarVentasPorColor(codigoCalzadoMasVendido, fechasSeleccionadas, filtrosAplicadosActual);
    CargarVentasPorTallas(codigoCalzadoMasVendido, fechasSeleccionadas, filtrosAplicadosActual);
    CargarCombinacionesColorTalla(codigoCalzadoMasVendido, fechasSeleccionadas, filtrosAplicadosActual);
});


  // Datos de los indicadores
  const datoVentaTotal = document.getElementById('totalVentas');
  const datoTotalPedidos = document.getElementById('totalPedidos');
  const datoTotalPares = document.getElementById('totalParesVendidos');
  const datoPromedioPares = document.getElementById('promedioPares');

  //Etiquetas Comparativas
  const etiquetaVentaTotal = document.getElementById('comparativaVentas');
  const etiquetaTotalPedidos = document.getElementById('comparativaPedidos');
  const etiquetaTotalPares = document.getElementById('comparativaParesVendidos');
  const etiquetaPromedioPares = document.getElementById('comparativaParesPromedio');

  //Iconos Comparativas
  const iconoVentaTotal = document.getElementById('iconoVentaTotal');
  const iconoTotalPedidos = document.getElementById('iconoTotalPedidos');
  const iconoTotalPares = document.getElementById('iconoTotalParesVendidos');
  const iconoPromedioPares = document.getElementById('iconoParesPromedio');


async function indicadoresModeloEspecifico(codigoCalzado, periodo, fechas, filtros) {
    if (!codigoCalzado) {
      actualizarIndicadores([], periodo, fechas);
      return;
    }
    const response = await fetch('../configuraciones/dashboard/indicadores/indicadoresEspecificos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ codigoCalzado, fechas, filtros })
    });
    const datos = await response.json();
    actualizarIndicadores(datos, periodo, fechas, filtros);
}

  function actualizarIndicadores(datos, periodo, fechas, filtros){
  const actual = datos.find(item => item.Periodo === 'Actual');
  const anterior = datos.find(item => item.Periodo === 'Anterior');
  const hayVentas = (actual && (Number(actual.TotalVentas) > 0 || Number(actual.TotalPedidos) > 0));

  actualizarBannerDashboardEspecifico(hayVentas, periodo, fechas, filtros);
  
  // Actualizar los indicadores
  let porcentajeVenta;
  let porcentajePedidos;
  let porcentajePares;
  let porcentajePromedioPares;

  if(actual && anterior){
    datoVentaTotal.textContent = 'Q ' + actual.TotalVentas;
    datoTotalPedidos.textContent = actual.TotalPedidos + ' pedidos';
    datoTotalPares.textContent = actual.TotalPares + ' pares';
    datoPromedioPares.textContent = actual.PromedioPares + ' pares';

    porcentajeVenta = calcularPorcentaje(actual.TotalVentas, anterior.TotalVentas);
    porcentajePedidos = calcularPorcentaje(actual.TotalPedidos, anterior.TotalPedidos);
    porcentajePares = calcularPorcentaje(actual.TotalPares, anterior.TotalPares);
    porcentajePromedioPares = calcularPorcentaje(actual.PromedioPares, anterior.PromedioPares);
    totalPeriodo = actual.TotalVentas;
  }

  else if(actual && !anterior){
    datoVentaTotal.textContent = 'Q ' + actual.TotalVentas;
    datoTotalPedidos.textContent = actual.TotalPedidos + ' pedidos';
    datoTotalPares.textContent = actual.TotalPares + ' pares';
    datoPromedioPares.textContent = actual.PromedioPares + ' pares';

    porcentajeVenta = calcularPorcentaje(actual.TotalVentas, 0);
    porcentajePedidos = calcularPorcentaje(actual.TotalPedidos, 0);
    porcentajePares = calcularPorcentaje(actual.TotalPares, 0);
    porcentajePromedioPares = calcularPorcentaje(actual.PromedioPares, 0);
    totalPeriodo = actual.TotalVentas;
  }
  
  else if(anterior && !actual){
    datoVentaTotal.textContent = 'Q ' + 0;
    datoTotalPedidos.textContent = 0 + ' pedidos';
    datoTotalPares.textContent = 0 + ' pares';
    datoPromedioPares.textContent = 0 + ' pares';

    porcentajeVenta = calcularPorcentaje(0, anterior.TotalVentas);
    porcentajePedidos = calcularPorcentaje(0, anterior.TotalPedidos);
    porcentajePares = calcularPorcentaje(0, anterior.TotalPares);
    porcentajePromedioPares = calcularPorcentaje(0, anterior.PromedioPares);
    totalPeriodo = 0;
  }

  else {
    datoVentaTotal.textContent = 'Q ' + 0;
    datoTotalPedidos.textContent = 0 + ' pedidos';
    datoTotalPares.textContent = 0 + ' pares';
    datoPromedioPares.textContent = 0 + ' pares';

    porcentajeVenta = calcularPorcentaje(0, 0);
    porcentajePedidos = calcularPorcentaje(0, 0);
    porcentajePares = calcularPorcentaje(0, 0);
    porcentajePromedioPares = calcularPorcentaje(0, 0);
    totalPeriodo = 0;
  }

  //Actualizar comparativas
  actualizarComparativas(etiquetaVentaTotal, porcentajeVenta, iconoVentaTotal, periodo);
  actualizarComparativas(etiquetaTotalPedidos, porcentajePedidos, iconoTotalPedidos, periodo);
  actualizarComparativas(etiquetaTotalPares, porcentajePares, iconoTotalPares, periodo);
  actualizarComparativas(etiquetaPromedioPares, porcentajePromedioPares, iconoPromedioPares, periodo);
}


function calcularPorcentaje(actual, anterior) {
  if (anterior === 0) {
    return actual > 0 ? null : 0;
  }
  return ((actual - anterior) / anterior) * 100;
}

function actualizarComparativas(etiqueta, porcentaje, icono, periodo) {
  const contenedor = etiqueta.parentElement;
  contenedor.classList.remove('positivo', 'negativo', 'sinResultados');
  let texto = '';

  if(periodo === 'Hoy' ){
    texto = ' vs ayer';
  }
  else {
    texto = ' vs periodo anterior';
  }

  if (porcentaje === null) {
      contenedor.classList.add('sinResultados');
      icono.innerHTML = '';
      etiqueta.textContent = 'Período anterior sin ventas';
  } else if (porcentaje > 0) {

      contenedor.classList.add('positivo');
      icono.innerHTML = '<ion-icon name="trending-up-outline"></ion-icon>';
      etiqueta.textContent = '+' + porcentaje.toFixed(1) + '%' + texto;

  } else if (porcentaje < 0) {

      contenedor.classList.add('negativo');
      icono.innerHTML = '<ion-icon name="trending-down-outline"></ion-icon>';
      etiqueta.textContent = porcentaje.toFixed(1) + '%' + texto;

  } else {
      contenedor.classList.add('sinResultados');
      icono.innerHTML = '<ion-icon name="remove-outline"></ion-icon>';
      etiqueta.textContent = 'Sin cambios  ' + texto;

  }
}

/* ---------------------------------- Actualización de Gráficos -------------------------------------*/
const contenedorGraficoColor = document.getElementById('graficoVentasColor');
const contenedorGraficoTallas = document.getElementById('graficoVentasTallas');
const contenedorCombinacionesColorTalla = document.getElementById('graficoCombinacionesColorTalla');
const contenedorGraficoEvolucionVentas = document.getElementById('graficoEvolucionVentas');
let descripcionEvolucionVentas = document.getElementById('descripcionEvolucion');

let graficoVentasPorColor;
let graficoVentasPorTallas;
let graficoEvoluciónVentas;

/*----------------------------------------- Gráfico Evolución de Ventas Por Modelo --------------------------------------*/
async function CargarEvolucionVentas(codigoCalzado, periodo, fechas, filtros) {
    try {
        const response = await fetch('../configuraciones/dashboard/graficos/Especificos/ventasModeloEspecifico.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ fechas, codigoCalzado, filtros }),
        });
        const data = await response.json();
        const datosGraficos = clasificaciónDatosEvolucionVentas(data, periodo, fechas);
        actualizarGraficoEvolucionVentas(datosGraficos);
    } catch (error) {
        console.error('Error al cargar los indicadores:', error);
    }
}

function clasificaciónDatosEvolucionVentas(datos, periodo, fechaPeriodos){
  let datosActuales = datos.filter(item => item.Periodo === 'Actual').map(item => Number(item.TotalVendido));
  let datosAnteriores = datos.filter(item => item.Periodo === 'Anterior').map(item => Number(item.TotalVendido));
  let fechaActual = datos.filter(item => item.Periodo === 'Actual').map(item => item.fechaPedido);
  let fechaAnterior = datos.filter(item => item.Periodo === 'Anterior').map(item => item.fechaPedido);

  let etiquetasEvolucionVentasActual = '';
  let etiquetasEvolucionVentasAnterior = '';
  let categorias = [];
  
  if(periodo === 'Hoy'){
    etiquetasEvolucionVentasActual = "Últimos 5 días";
    etiquetasEvolucionVentasAnterior = "5 días anteriores";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    categorias =  ['Día 1', 'Día 2', 'Día 3', 'Día 4', 'Día 5'];
  }
  else if(periodo === '7dias'){
    etiquetasEvolucionVentasActual = "Últimos 7 Días";
    etiquetasEvolucionVentasAnterior = "7 Días Anteriores";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    categorias = ['Día 1', 'Día 2', 'Día 3', 'Día 4', 'Día 5', 'Día 6', 'Día 7']; 
  }
  else if(periodo === '30dias'){
    etiquetasEvolucionVentasActual = "Últimos 30 Días";
    etiquetasEvolucionVentasAnterior = "30 Días Anteriores";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    categorias = Array.from({ length: 30 }, (_, i) => `Día ${i + 1}`);
  }
  else if(periodo === 'mesFiltro'){
    etiquetasEvolucionVentasActual = "Mes seleccionado";
    etiquetasEvolucionVentasAnterior = "Mes anterior";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    const cantidadDiasMes = crearFechaLocal(fechaPeriodos.fechaFinalActual).getDate();
    categorias = Array.from({ length: cantidadDiasMes }, (_, i) => `Día ${i + 1}`);
  }

  let fechaFinActual = fechaPeriodos.fechaFinalActual;
  let fechaFinAnterior = fechaPeriodos.fechaFinalAnterior;
  let fechaInicioActual = fechaPeriodos.fechaInicialActual;
  let fechaInicioAnterior = fechaPeriodos.fechaInicialAnterior;

  let ventasTotalesActual = [];
  let fechasActual = [];
  let ventasTotalesAnteriores = [];
  let fechasAnteriores = [];

  for (let fecha = crearFechaLocal(fechaInicioActual); fecha <= crearFechaLocal(fechaFinActual); fecha.setDate(fecha.getDate() + 1)) {
      let fechaFormateada = formatearFecha(fecha, 1);
      let fechaAbreviada  = formatearFecha(fecha, 2);
      fechasActual.push(fechaAbreviada);
      if (fechaActual.includes(fechaFormateada)) {
          ventasTotalesActual.push(datosActuales[fechaActual.indexOf(fechaFormateada)]);
      }
      else {
          ventasTotalesActual.push(0);
      }
  }

  if (periodo === 'mesFiltro') {
    const fechaInicioMesAnterior = crearFechaLocal(fechaInicioAnterior);

    for (let dia = 1; dia <= categorias.length; dia++) {
      const fecha = new Date(
        fechaInicioMesAnterior.getFullYear(),
        fechaInicioMesAnterior.getMonth(),
        dia
      );

      if (fecha.getMonth() !== fechaInicioMesAnterior.getMonth()) {
        fechasAnteriores.push('');
        ventasTotalesAnteriores.push(0);
        continue;
      }

      const fechaFormateada = formatearFecha(fecha, 1);
      fechasAnteriores.push(formatearFecha(fecha, 2));
      ventasTotalesAnteriores.push(
        fechaAnterior.includes(fechaFormateada)
          ? datosAnteriores[fechaAnterior.indexOf(fechaFormateada)]
          : 0
      );
    }
  } else {
    for (let fecha = crearFechaLocal(fechaInicioAnterior); fecha <= crearFechaLocal(fechaFinAnterior); fecha.setDate(fecha.getDate() + 1)) {
      let fechaFormateada = formatearFecha(fecha,1);
      let fechaAbreviada  = formatearFecha(fecha, 2);
      fechasAnteriores.push(fechaAbreviada);
      if (fechaAnterior.includes(fechaFormateada)) {
          ventasTotalesAnteriores.push(datosAnteriores[fechaAnterior.indexOf(fechaFormateada)]);
      }
      else {
        ventasTotalesAnteriores.push(0);
      }
    }
  }
  
  return {
    ventasTotalesActual,
    ventasTotalesAnteriores,
    fechasActual,
    fechasAnteriores,
    etiquetasEvolucionVentasActual,
    etiquetasEvolucionVentasAnterior,
    categorias,
    periodo
  };
}

function crearFechaLocal(fecha) {
    const [año, mes, dia] = fecha.split('-');
    return new Date(año, mes - 1, dia);
}

async function CargarEvolucionVentasAnual(codigoCalzado, periodo, rangosFechas, filtros) {
    try {
        const response = await fetch('../configuraciones/dashboard/graficos/Especificos/ventasAnualModeloEspecifico.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({codigoCalzado, fechas: rangosFechas, filtros: filtros }),
        });
        const data = await response.json();

        const datosGraficos = clasificaciónDatosEvolucionVentasAnual(data, periodo, rangosFechas);
        actualizarGraficoEvolucionVentas(datosGraficos);
    } catch (error) {
        console.error('Error al cargar los indicadores:', error);
    }
}

let nombresMesesLargos = [];
function clasificaciónDatosEvolucionVentasAnual(datos, periodo, fechaPeriodos){
  let mesesActual = datos.filter(item => item.Periodo === 'Actual').map(item => Number(item.MES));
  let mesesAnteriores = datos.filter(item => item.Periodo === 'Anterior').map(item => Number(item.MES));
    
  let datosActuales = datos.filter(item => item.Periodo === 'Actual').map(item => Number(item.TotalMes));
  let datosAnteriores = datos.filter(item => item.Periodo === 'Anterior').map(item => Number(item.TotalMes));

  let etiquetasEvolucionVentasActual = '';
  let etiquetasEvolucionVentasAnterior = '';
  let categorias = [];
  
  const cantidadMeses = periodo === 'anioAFechaActual' ? new Date().getMonth() + 1: 12;
  const nombresMeses = Array.from(
    { length: cantidadMeses },
    (_, i) => {
      const nombreMes = new Intl.DateTimeFormat('es-GT', {
        month: 'short'
      }).format(new Date(2000, i, 1));

      return nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);
    }
  );
  nombresMesesLargos = Array.from(
    { length: cantidadMeses },
    (_, i) => {
      const nombreMes = new Intl.DateTimeFormat('es-GT', {
        month: 'long'
      }).format(new Date(2000, i, 1));

      return nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);
    }
  );

  if(periodo === 'anioAFechaActual'){
    etiquetasEvolucionVentasActual = "Año a la fecha";
    etiquetasEvolucionVentasAnterior = "Mismo período Año anterior";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    categorias = nombresMeses;
  }

  else if(periodo === 'anioHistorico'){
    etiquetasEvolucionVentasActual = "Año seleccionado";
    etiquetasEvolucionVentasAnterior = "Año anterior";
    descripcionEvolucionVentas.textContent = 'Comparativa: ' + etiquetasEvolucionVentasActual + ' vs ' + etiquetasEvolucionVentasAnterior;
    categorias = nombresMeses;
  }
  let ventasTotalesActual = [];
  let ventasTotalesAnteriores = [];

  for (let mes = 1; mes <= cantidadMeses; mes++){
    if(mesesActual.includes(mes)){
      ventasTotalesActual.push(datosActuales[mesesActual.indexOf(mes)]);
    }
    else{
      ventasTotalesActual.push(0);
    }

    if(mesesAnteriores.includes(mes)){
      ventasTotalesAnteriores.push(datosAnteriores[mesesAnteriores.indexOf(mes)]);
    }
    else{
      ventasTotalesAnteriores.push(0);
    }
  }

  return {
    ventasTotalesActual,
    ventasTotalesAnteriores,
    fechasActual: categorias,
    fechasAnteriores: categorias,
    etiquetasEvolucionVentasActual,
    etiquetasEvolucionVentasAnterior,
    categorias,
    periodo,
  };
}


function actualizarGraficoEvolucionVentas(datos) {
  const {
    ventasTotalesActual,
    ventasTotalesAnteriores,
    fechasActual,
    fechasAnteriores,
    etiquetasEvolucionVentasActual,
    etiquetasEvolucionVentasAnterior,
    categorias,
    periodo
  } = datos;

  if (graficoEvoluciónVentas) {
    graficoEvoluciónVentas.destroy();
  }

  let options;

  if (periodo === '30dias' || periodo === 'mesFiltro') {
    // Configuración especial para los últimos 30 días 
    options = {
      chart: {
        type: 'area',
        height: 310,
        width: '100%',
        redrawOnParentResize: true,
        redrawOnWindowResize: true,
        toolbar: { show: false },
        zoom: { enabled: false },
        fontFamily: 'Poppins, sans-serif'
      },

      colors: ['#2563EB', '#94A3B8'],

      fill: {
        type: ['gradient', 'solid'],
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.25,
          opacityTo: 0.02,
          stops: [0, 100]
        },
        opacity: [1, 0]
      },

      stroke: {
        curve: 'smooth',
        width: [2.5, 2],
        dashArray: [0, 5]
      },

      dataLabels: {
        enabled: false
      },

      series: [
        {
          name: etiquetasEvolucionVentasActual,
          data: ventasTotalesActual
        },
        {
          name: etiquetasEvolucionVentasAnterior,
          data: ventasTotalesAnteriores
        }
      ],

      grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4,
        yaxis: {
          lines: {
            show: true
          }
        }
      },

      xaxis: {
        categories: categorias,
        tickPlacement: 'on',
        hideOverlappingLabels: false,
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11.5px',
            fontFamily: 'Poppins, sans-serif'
          },
          formatter: function(val) {
            const diasVisibles = ['Día 1', 'Día 5', 'Día 10', 'Día 15', 'Día 20', 'Día 25', `Día ${categorias.length}`];
            return diasVisibles.includes(val) ? val : '';
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },

      yaxis: {
        labels: {
          formatter: (val) => `Q ${val.toLocaleString('es-GT')}`,
          style: {
            colors: '#64748b',
            fontSize: '12px',
            fontFamily: 'Poppins, sans-serif'
          }
        }
      },

      legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '13px',
        fontFamily: 'Poppins, sans-serif',
        markers: {
          radius: 12
        }
      },

      tooltip: {
        shared: true,
        intersect: false,
        custom: function({ series, dataPointIndex }) {
          const valActual = series[0][dataPointIndex];
          const valAnterior = series[1][dataPointIndex];
          const fechaAct = fechasActual[dataPointIndex] || '';
          const fechaAnt = fechasAnteriores[dataPointIndex] || '';
          const esPeriodoAnual = periodo === 'anioAFechaActual' || periodo === 'anioHistorico';
          const tituloPunto = esPeriodoAnual
            ? `Mes: ${categorias[dataPointIndex] || ''}`
            : `Día ${dataPointIndex + 1} del Período`;
          const dif = valActual - valAnterior;

          let porcentaje;
          let textoVariacion;

          if (valAnterior === 0) {
            if (valActual === 0) {
              porcentaje = '0%';
              textoVariacion = 'Sin cambios';
            } else {
              porcentaje = null;
              textoVariacion = 'Sin ventas anteriores';
            }
          } else {
            const pct = ((dif / valAnterior) * 100).toFixed(1);
            porcentaje = `${dif >= 0 ? '+' : ''}${pct}%`;
            textoVariacion = `${porcentaje} (Q ${dif.toLocaleString('es-GT')})`;
          }

          const esPositivo = dif >= 0;

          return `
            <div style="
              padding: 12px;
              font-family: 'Poppins', sans-serif;
              font-size: 13px;
              line-height: 1.5;
              background: #ffffff;
              border-radius: 8px;
              box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
              border: 1px solid #e2e8f0;
            ">
              <div style="
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 8px;
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 4px;
              ">
                ${tituloPunto}
              </div>
              <div style="
                color: #2563eb;
                font-weight: 600;
              ">
                • ${fechaAct} (Actual): Q ${valActual.toLocaleString('es-GT')}
              </div>
              <div style="
                color: #64748b;
              ">
                • ${fechaAnt} (Anterior): Q ${valAnterior.toLocaleString('es-GT')}
              </div>
              <div style="
                margin-top: 6px;
                font-weight: 700;
                color: ${esPositivo ? '#059669' : '#dc2626'};
              ">
                Variación: ${textoVariacion}
              </div>
            </div>
          `;
        }
      }
    };
  } else {
    // Configuración estándar para períodos cortos (Hoy: 5 días, 7 días)
    options = {
      chart: {
        type: 'area',
        height: 310,
        width: '100%',
        redrawOnParentResize: true,
        redrawOnWindowResize: true,
        toolbar: { show: false },
        zoom: { enabled: false },
        fontFamily: 'Poppins, sans-serif'
      },

      colors: ['#2563EB', '#94A3B8'],

      fill: {
        type: ['gradient', 'solid'],
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.25,
          opacityTo: 0.02,
          stops: [0, 100]
        },
        opacity: [1, 0]
      },

      stroke: {
        curve: 'smooth',
        width: [3, 2],
        dashArray: [0, 5]
      },

      dataLabels: {
        enabled: false
      },

      series: [
        {
          name: etiquetasEvolucionVentasActual,
          data: ventasTotalesActual
        },
        {
          name: etiquetasEvolucionVentasAnterior,
          data: ventasTotalesAnteriores
        }
      ],

      grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4,
        yaxis: {
          lines: {
            show: true
          }
        }
      },

      xaxis: {
        categories: categorias,
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px',
            fontFamily: 'Poppins, sans-serif'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },

      yaxis: {
        labels: {
          formatter: (val) => `Q ${val.toLocaleString('es-GT')}`,
          style: {
            colors: '#64748b',
            fontSize: '12px',
            fontFamily: 'Poppins, sans-serif'
          }
        }
      },

      legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '13px',
        fontFamily: 'Poppins, sans-serif',
        markers: {
          radius: 12
        }
      },

      tooltip: {
        shared: true,
        intersect: false,
        custom: function({ series, dataPointIndex }) {
          const valActual = series[0][dataPointIndex];
          const valAnterior = series[1][dataPointIndex];
          const esPeriodoAnual = periodo === 'anioAFechaActual' || periodo === 'anioHistorico';
          let fechaAct;
          let fechaAnt;

          if (esPeriodoAnual) {
            fechaAct = nombresMesesLargos[dataPointIndex] || '';
            fechaAnt = nombresMesesLargos[dataPointIndex] || '';
          } else {
            fechaAct = fechasActual?.[dataPointIndex] || '';
            fechaAnt = fechasAnteriores?.[dataPointIndex] || '';
          }

          const tituloPunto = esPeriodoAnual
            ? `Mes: ${fechaAct || ''}`
            : `Día ${dataPointIndex + 1} del Período`;
          const dif = valActual - valAnterior;

          let porcentaje;
          let textoVariacion;

          if (valAnterior === 0) {
            if (valActual === 0) {
              porcentaje = '0%';
              textoVariacion = 'Sin cambios';
            } else {
              porcentaje = null;
              textoVariacion = 'Sin ventas anteriores';
            }
          } else {
            const pct = ((dif / valAnterior) * 100).toFixed(1);
            porcentaje = `${dif >= 0 ? '+' : ''}${pct}%`;
            textoVariacion = `${porcentaje} (Q ${dif.toLocaleString('es-GT')})`;
          }

          const esPositivo = dif >= 0;

          return `
            <div style="
              padding: 12px;
              font-family: 'Poppins', sans-serif;
              font-size: 13px;
              line-height: 1.5;
              background: #ffffff;
              border-radius: 8px;
              box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
              border: 1px solid #e2e8f0;
            ">
              <div style="
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 8px;
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 4px;
              ">
                ${tituloPunto}
              </div>
              <div style="
                color: #2563eb;
                font-weight: 600;
              ">
                • ${fechaAct} (Actual): Q ${valActual.toLocaleString('es-GT')}
              </div>
              <div style="
                color: #64748b;
              ">
                • ${fechaAnt} (Anterior): Q ${valAnterior.toLocaleString('es-GT')}
              </div>
              <div style="
                margin-top: 6px;
                font-weight: 700;
                color: ${esPositivo ? '#059669' : '#dc2626'};
              ">
                Variación: ${textoVariacion}
              </div>
            </div>
          `;
        }
      }
    };
  }

  graficoEvoluciónVentas = new ApexCharts(contenedorGraficoEvolucionVentas, options);
  window.graficoEvolucionVentasEspecifico = graficoEvoluciónVentas;
  graficoEvoluciónVentas.render();
}


/* ----------------------------------- Mapa de Colores para Gráficos y Tablas -----------------------*/
const mapaColores = {
  'blanco':      '#F8F8F8',
  'negro':       '#1a1a1a',
  'rojo':        '#dc2626',
  'azul':        '#2563eb',
  'azul marino': '#1e3a8a',
  'celeste':     '#38bdf8',
  'verde':       '#16a34a',
  'verde olivo': '#4d7c0f',
  'amarillo':    '#facc15',
  'anaranjado':  '#f97316',
  'café':        '#92400e',
  'cafe':        '#92400e',
  'beige':       '#d4b896',
  'nude':        '#c4966a',
  'palo rosa':   '#f9a8d4',
  'vino tinto':  '#7f1d1d',
  'corinto':     '#6b1e2a',
  'plateado':    '#94a3b8',
  'plata':       '#94a3b8',
  'dorado':      '#d97706',
  'oro rosa':    '#b76e79',
  'gena':        '#9c4221',
  'henna':       '#9c4221'
};

function obtenerColorHex(nombreColor) {
  if (!nombreColor) return '#94a3b8';
  const clave = nombreColor.trim().toLowerCase();
  return mapaColores[clave] || '#94a3b8';
}

/* ----------------------------------- Funciones para Gráfico de Ventas por Color -----------------------*/

async function CargarVentasPorColor(codigoCalzado, fechas, filtros) {
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Especificos/ventasPorColor.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ codigoCalzado, fechas, filtros })
    });
    const datos = await response.json();
    actualizarVentasPorColor(datos);
  } catch (error) {
    console.error('Error al cargar ventas por color:', error);
  }
}

function actualizarVentasPorColor(datos) {
  contenedorGraficoColor.innerHTML = '';
  if (graficoVentasPorColor) {
    graficoVentasPorColor.destroy();
  }

  if (!datos || datos.length === 0) {
    contenedorGraficoColor.innerHTML = 
      `<p class="mensajeSinDatos">
         No se encontraron ventas para el período seleccionado.
      </p>`;
    return; 
  }
  
  const colores = datos.map(item => item.Color);
  const cantidades = datos.map(item => Number(item.CantidadVendida));
  const coloresHex = colores.map(color => obtenerColorHex(color));

  const opcionesVentasPorColor = {
    chart: {
      type: 'donut',
      height: 310,
      width: '100%',
      fontFamily: 'Poppins, sans-serif'
    },
    colors: coloresHex,
    series: cantidades,
    labels: colores,
    dataLabels: {
      enabled: false
    },
    plotOptions: {
      pie: {
        borderRadius: 6,
        donut: {
          size: '65%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total Pares',
              fontFamily: 'Poppins, sans-serif',
              fontSize: '13px',
              fontWeight: 600,
              color: '#64748b',
              formatter: function(w) {
                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                return total.toLocaleString('es-GT') + ' pares';
              }
            },
            value: {
              fontFamily: 'Poppins, sans-serif',
              fontSize: '16px',
              fontWeight: 700,
              color: '#1e293b',
              formatter: (val) => Number(val).toLocaleString('es-GT')
            }
          }
        }
      }
    },
    stroke: {
      width: 2,
      colors: ['#ffffff']
    },
    legend: {
      position: 'bottom',
      horizontalAlign: 'center',
      fontFamily: 'Poppins, sans-serif',
      fontSize: '12px',
      markers: {
        radius: 12
      },
      itemMargin: {
        horizontal: 8,
        vertical: 2
      }
    },
    tooltip: {
      style: {
        fontFamily: 'Poppins, sans-serif'
      },
      custom: function({ series, seriesIndex, dataPointIndex, w }) {
        const colorNombre = w.globals.labels[seriesIndex];
        const pares = series[seriesIndex];
        const itemDato = datos[seriesIndex];
        const totalQ = itemDato ? Number(itemDato.TotalVendido) || 0 : 0;
        const hex = obtenerColorHex(colorNombre);
        return `
          <div style="
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            line-height: 1.5;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
          ">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
              <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:${hex}; border:1.5px solid rgba(0,0,0,0.15);"></span>
              ${colorNombre}
            </div>
            <div style="color: #475569; margin-bottom: 2px;">• Pares vendidos: <strong style="color:#0f172a;">${pares.toLocaleString('es-GT')}</strong></div>
            <div style="color: #0d6efd; font-weight: 600;">• Total vendido: Q ${totalQ.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
          </div>
        `;
      }
    }
  };

  graficoVentasPorColor = new ApexCharts(contenedorGraficoColor, opcionesVentasPorColor);
  window.graficoVentasPorColorEspecifico = graficoVentasPorColor;
  graficoVentasPorColor.render();
}


/* ---------------------------------------------- Funciones para Gráfico de Ventas por Tallas ---------------*/
async function CargarVentasPorTallas(codigoCalzado, fechas, filtros) {
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Especificos/ventasPorTallas.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ codigoCalzado, fechas, filtros })
    });
    const datos = await response.json();
    actualizarVentasPorTallas(datos);
  } catch (error) {
    console.error('Error al cargar ventas por tallas:', error);
  }
}

function actualizarVentasPorTallas(datos) {
  let tallasPredeterminadas = [33, 34, 35, 36, 37, 38, 39, 40];
  contenedorGraficoTallas.innerHTML = '';

  if (graficoVentasPorTallas) {
    graficoVentasPorTallas.destroy();
  }

  if (!datos || datos.length === 0) {
    contenedorGraficoTallas.innerHTML = 
      `<p class="mensajeSinDatos">
         No se encontraron ventas para el período seleccionado.
      </p>`;
    return; 
  }
  
  let datosGrafico = datos.map(item => ({
    talla: Number(item.Talla),
    cantidad: Number(item.CantidadVendida),
    total: Number(item.TotalVendido) || 0,
  }));

  for (let dato of datosGrafico) {
    if (!tallasPredeterminadas.includes(dato.talla)) {
      tallasPredeterminadas.push(dato.talla);
    }
  }

  tallasPredeterminadas.sort((a, b) => a - b);

  let datosGraficoFinal = tallasPredeterminadas.map(talla => {
    const datoTalla = datosGrafico.find(item => item.talla == talla);
    return {
      talla: talla,
      cantidad: datoTalla ? datoTalla.cantidad : 0,
      total: datoTalla ? datoTalla.total : 0,
    };
  });
  
  const opcionesVentasPorTallas = {
    chart: {
      type: 'bar',
      height: 310,
      width: '100%',
      toolbar: { show: false },
      fontFamily: 'Poppins, sans-serif'
    },
    colors: ['#0d6efd'],
    plotOptions: {
      bar: {
        borderRadius: 6,
        borderRadiusApplication: 'end',
        horizontal: true,
        barHeight: '62%',
        dataLabels: {
          position: 'top'
        }
      }
    },
    dataLabels: {
      enabled: true,
      textAnchor: 'start',
      style: {
        colors: ['#1e293b'],
        fontFamily: 'Poppins, sans-serif',
        fontSize: '11.5px',
        fontWeight: 600
      },
      formatter: (val) => val > 0 ? val : '',
      offsetX: 6
    },
    series: [
      {
        name: 'Cantidad Vendida',
        data: datosGraficoFinal.map(item => item.cantidad)
      }
    ],
    grid: {
      borderColor: '#f1f5f9',
      strokeDashArray: 4,
      xaxis: {
        lines: { show: true }
      },
      yaxis: {
        lines: { show: false }
      }
    },
    xaxis: {
      title: {
        text: 'Pares Vendidos',
        style: {
          color: '#64748b',
          fontFamily: 'Poppins, sans-serif',
          fontWeight: 500,
          fontSize: '12px'
        }
      },
      categories: datosGraficoFinal.map(item => item.talla),
      labels: {
        style: {
          colors: '#64748b',
          fontSize: '11.5px',
          fontFamily: 'Poppins, sans-serif'
        }
      },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: {
      title: {
        text: 'Talla',
        style: {
          color: '#64748b',
          fontFamily: 'Poppins, sans-serif',
          fontWeight: 500,
          fontSize: '12px'
        }
      },
      labels: {
        style: {
          colors: '#334155',
          fontFamily: 'Poppins, sans-serif',
          fontWeight: 600,
          fontSize: '12px'
        }
      }
    },
    tooltip: {
      style: {
        fontFamily: 'Poppins, sans-serif'
      },
      custom: function({ series, seriesIndex, dataPointIndex, w }) {
        const talla = datosGraficoFinal[dataPointIndex].talla;
        const cantidad = series[seriesIndex][dataPointIndex];
        const total = datosGraficoFinal[dataPointIndex].total;
        return `
          <div style="
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            line-height: 1.5;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
          ">
            <div style="font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px; margin-bottom: 6px;">
              Talla ${talla}
            </div>
            <div style="color: #475569; margin-bottom: 2px;">
              • Pares vendidos: <strong style="color:#0f172a;">${cantidad.toLocaleString('es-GT')}</strong>
            </div>
            <div style="color: #0d6efd; font-weight: 600;">
              • Total vendido: Q ${total.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </div>
          </div>
        `;
      }
    }
  };

  graficoVentasPorTallas = new ApexCharts(contenedorGraficoTallas, opcionesVentasPorTallas);
  window.graficoVentasPorTallasEspecifico = graficoVentasPorTallas;
  graficoVentasPorTallas.render();
}

/* -------------------- Funciones para Tabla Top 5 Combinaciones Color-Talla -------------------- */
const tablaCombinacionesColorTalla = document.getElementById('tablaCombinacionesColorTalla');

async function CargarCombinacionesColorTalla(codigoCalzado, fechas, filtros) {
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Especificos/ventasColorTalla.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ codigoCalzado, fechas, filtros })
    });
    const datos = await response.json();
    actualizarCombinacionesColorTalla(datos);
  } catch (error) {
    console.error('Error al cargar combinaciones color-talla:', error);
  }
}

function actualizarCombinacionesColorTalla(datos) {
  if (!tablaCombinacionesColorTalla) return;

  tablaCombinacionesColorTalla.innerHTML = '';

  if (!datos || datos.length === 0) {
    tablaCombinacionesColorTalla.innerHTML = `
      <tr>
        <td colspan="4" class="mensajeSinDatos">
          No se encontraron combinaciones para el período seleccionado.
        </td>
      </tr>
    `;
    return;
  }

  datos.forEach(item => {
    const tr = document.createElement('tr');
    const hexColor = obtenerColorHex(item.Color);
    const totalVendidoNum = Number(item.TotalVendido) || 0;
    const totalFormateado = 'Q ' + totalVendidoNum.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const cantidadVendidaNum = Number(item.CantidadVendida) || 0;

    tr.innerHTML = `
      <td>
        <div class="tdColor">
          <span class="muestraColor" style="background-color: ${hexColor};"></span>
          <span class="nombreColor">${item.Color}</span>
        </div>
      </td>
      <td class="tdTalla">
        <span class="badgeTalla">${item.Talla}</span>
      </td>
      <td class="tdCantidad">
        <span class="badgeCantidad">${cantidadVendidaNum}</span>
      </td>
      <td class="tdTotal">${totalFormateado}</td>
    `;
    tablaCombinacionesColorTalla.appendChild(tr);
  });
}
