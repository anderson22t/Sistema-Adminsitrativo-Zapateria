document.addEventListener("DOMContentLoaded", () => {
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
  const bannerGeneral = document.getElementById("bannerDashboardGeneral");
  const encabezadoBannerGeneral = bannerGeneral?.querySelector(".encabezadoBanner");
  if (encabezadoBannerGeneral && bannerGeneral) {
    encabezadoBannerGeneral.addEventListener("click", () => {
      bannerGeneral.classList.toggle("abierto");
    });
  }

  const formFiltros = document.getElementById("formularioFiltrosAvanzados");
  let fechas;
  let fechasEvolucionVentas;
  let periodo;
  let filtrosAplicados = null;
  let rangoFechasFiltros = null;
  let periodoInterfaz = null;
  let filtroMes = null;
  let filtroAnio = null;

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
        rangoFechasFiltros = null;
      }
      periodoInterfaz = btn.value;

      periodo = btn.value;
      fechas = seleccionRango(periodo);
      fechasEvolucionVentas = seleccionRangoEvolucionVentas(periodo);

      actualizarEstadoBotonFiltros();
      await cargarDashboardActual(periodo, fechas, fechasEvolucionVentas);
    });
  });

  async function cargarDashboardActual(periodoCarga, fechasIndicadores, fechasGrafico, esPeriodoAnual = false) {
    periodo = periodoCarga;
    fechas = fechasIndicadores;
    fechasEvolucionVentas = fechasGrafico;

    await CargarIndicadores(periodoCarga, fechasIndicadores, filtrosAplicados);
    actualizarBannerDashboardGeneral(periodoCarga, fechasIndicadores, filtrosAplicados);

    if (esPeriodoAnual) {
      CargarEvolucionVentasAnual(periodoCarga, fechasIndicadores, filtrosAplicados);
    } else {
      CargarEvolucionVentas(periodoCarga, fechasGrafico, filtrosAplicados);
    }

    CargarVentasCategoria(periodoCarga, fechasIndicadores, filtrosAplicados);
    cargarProductosMasVendidos(periodoCarga, fechasIndicadores, filtrosAplicados);
    cargarTallasMasVendidas(periodoCarga, fechasIndicadores, filtrosAplicados);
  }

  function actualizarEstadoBotonFiltros() {
    const hayFiltroFecha = Boolean(filtroMes || filtroAnio);
    const hayFiltroVenta = Boolean(
      filtrosAplicados?.canalVenta || filtrosAplicados?.tipoVenta
    );

    const tieneFiltros = hayFiltroFecha || hayFiltroVenta;
    btnFiltrosAvanzados.classList.toggle('activo', tieneFiltros);
    if (btnToggleAcciones) {
      btnToggleAcciones.classList.toggle('conFiltrosActivos', tieneFiltros);
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

    if(datosFiltros.anio && datosFiltros.filtroMes){
      filtroMes = datosFiltros.filtroMes;
      filtroAnio = datosFiltros.anio;
      rangoFechasFiltros = obtenerFechasRangoFiltro(datosFiltros.filtroMes, datosFiltros.anio);
      periodo = 'mesFiltro';
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

      if (anioSeleccionado === anioActual) {
          periodo = 'anioAFechaActual';
      } else {
          periodo = 'anioHistorico';
      }
      btnsRango.forEach((b) => b.classList.remove('active'));
      actualizarEstadoBotonFiltros();
      await cargarDashboardActual(periodo, rangoFechasFiltros, rangoFechasFiltros, true);
      return;
    }
    else if (!datosFiltros.anio && !datosFiltros.filtroMes && (datosFiltros.canalVenta || datosFiltros.tipoVenta)) {
      filtroMes = null;
      filtroAnio = null;
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
      filtroMes = null;
      filtroAnio = null;
      rangoFechasFiltros = null;
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
      filtroMes = null;
      filtroAnio = null;
      rangoFechasFiltros = null;
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

function actualizarBannerDashboardGeneral(rango, fechas, filtros) {
  const banner = document.getElementById('bannerDashboardGeneral');
  const iconoPeriodo = document.getElementById('iconoPeriodo');

  const opcionCanalVentaBanner = document.getElementById('opcionCanalVentaBanner');
  const opcionTipoVentaBanner = document.getElementById('opcionTipoVentaBanner');
  
  const leyendaPeriodo = document.getElementById('leyendaPeriodo');
  const opcionPeriodo = document.getElementById('opcionPeriodo');
  const opcionCanalVenta = document.getElementById('opcionCanalVenta');
  const opcionTipoVenta = document.getElementById('opcionTipoVenta');
  if (!banner) return;

  const { tituloPeriodo, rangoFechasTexto } = formatearRangoTexto(rango, fechas);

  if (filtros && filtros.canalVenta){
    opcionCanalVenta.textContent = filtros.descripcionCanalVenta;
  }
  else{
    opcionCanalVenta.textContent = 'Todos';
  }
  if (filtros && filtros.tipoVenta){
    opcionTipoVenta.textContent = filtros.descripcionTipoVenta;
  }
  else{
    opcionTipoVenta.textContent = 'Todos';
  }

  if(!hayVentasActuales){
    banner.className = 'bannerDashboard warning';
    banner.classList.remove('info');
    iconoPeriodo.innerHTML = '<ion-icon name="alert-circle-outline"></ion-icon>';
    leyendaPeriodo.textContent = 'No hay ventas registradas para el período';
    opcionPeriodo.textContent = rangoFechasTexto;
    opcionCanalVentaBanner.style.display = 'none';
    opcionTipoVentaBanner.style.display = 'none';
  }
  
  else{
    banner.className = 'bannerDashboard info';
    banner.classList.remove('warning');
    iconoPeriodo.innerHTML = '<ion-icon name="calendar-outline"></ion-icon>';
    leyendaPeriodo.textContent = 'Periodo: ' + tituloPeriodo;
    opcionPeriodo.textContent = rangoFechasTexto;
    opcionCanalVentaBanner.style.display = 'inline-flex';
    opcionTipoVentaBanner.style.display = 'inline-flex';
  }

  // Revelar el banner ahora que el contenido está listo
  banner.classList.add('visible');
  
}



  // Datos de los indicadores
  let hayVentasActuales;
  const datoVentaTotal = document.getElementById('totalVentas');
  const datoTotalPedidos = document.getElementById('totalPedidos');
  const datoTotalPares = document.getElementById('totalParesVendidos');
  const datoTicketPromedio = document.getElementById('ticketPromedio');

  //Etiquetas Comparativas
  const etiquetaVentaTotal = document.getElementById('comparativaVentas');
  const etiquetaTotalPedidos = document.getElementById('comparativaPedidos');
  const etiquetaTotalPares = document.getElementById('comparativaParesVendidos');
  const etiquetaTicketPromedio = document.getElementById('comparativaTicketPromedio');

  //Iconos Comparativas
  const iconoVentaTotal = document.getElementById('iconoVentaTotal');
  const iconoTotalPedidos = document.getElementById('iconoTotalPedidos');
  const iconoTotalPares = document.getElementById('iconoTotalPares');
  const iconoTicketPromedio = document.getElementById('iconoTicketPromedio');


async function CargarIndicadores (periodo, rangosFechas, filtros) {

  try {
    const response = await fetch('../configuraciones/dashboard/indicadores/datosIndicadores.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json', 
      },
      body: JSON.stringify({fechas: rangosFechas, filtros: filtros}),
    });
    const data = await response.json();

    actualizarIndicadores(data, periodo);    
  } catch (error) {
    console.error('Error al cargar los indicadores:', error);
  }
    
}


function actualizarIndicadores(datos, periodo){
  // Actualizar los indicadores
  const actual = datos.find(item => item.Periodo === 'Actual');
  const anterior = datos.find(item => item.Periodo === 'Anterior');
  let porcentajeVenta;
  let porcentajePedidos;
  let porcentajePares;
  let porcentajeTicketPromedio;

  if(actual && anterior){
    hayVentasActuales = true;
    datoVentaTotal.textContent = 'Q ' + actual.TotalVentas;
    datoTotalPedidos.textContent = actual.TotalPedidos + ' pedidos';
    datoTotalPares.textContent = actual.TotalPares + ' pares';
    datoTicketPromedio.textContent = 'Q ' + actual.TicketPromedio;

    porcentajeVenta = calcularPorcentaje(actual.TotalVentas, anterior.TotalVentas);
    porcentajePedidos = calcularPorcentaje(actual.TotalPedidos, anterior.TotalPedidos);
    porcentajePares = calcularPorcentaje(actual.TotalPares, anterior.TotalPares);
    porcentajeTicketPromedio = calcularPorcentaje(actual.TicketPromedio, anterior.TicketPromedio);
    totalPeriodo = actual.TotalVentas;
  }

  else if(actual && !anterior){
    hayVentasActuales = true;
    datoVentaTotal.textContent = 'Q ' + actual.TotalVentas;
    datoTotalPedidos.textContent = actual.TotalPedidos + ' pedidos';
    datoTotalPares.textContent = actual.TotalPares + ' pares';
    datoTicketPromedio.textContent = 'Q ' + actual.TicketPromedio;

    porcentajeVenta = calcularPorcentaje(actual.TotalVentas, 0);
    porcentajePedidos = calcularPorcentaje(actual.TotalPedidos, 0);
    porcentajePares = calcularPorcentaje(actual.TotalPares, 0);
    porcentajeTicketPromedio = calcularPorcentaje(actual.TicketPromedio, 0);
    totalPeriodo = actual.TotalVentas;
  }
  
  else if(anterior && !actual){
    hayVentasActuales = false;
    datoVentaTotal.textContent = 'Q ' + 0;
    datoTotalPedidos.textContent = 0 + ' pedidos';
    datoTotalPares.textContent = 0 + ' pares';
    datoTicketPromedio.textContent = 'Q ' + 0;

    porcentajeVenta = calcularPorcentaje(0, anterior.TotalVentas);
    porcentajePedidos = calcularPorcentaje(0, anterior.TotalPedidos);
    porcentajePares = calcularPorcentaje(0, anterior.TotalPares);
    porcentajeTicketPromedio = calcularPorcentaje(0, anterior.TicketPromedio);
    totalPeriodo = 0;
  }

  else {
    hayVentasActuales = false;
    datoVentaTotal.textContent = 'Q ' + 0;
    datoTotalPedidos.textContent = 0 + ' pedidos';
    datoTotalPares.textContent = 0 + ' pares';
    datoTicketPromedio.textContent = 'Q ' + 0;

    porcentajeVenta = calcularPorcentaje(0, 0);
    porcentajePedidos = calcularPorcentaje(0, 0);
    porcentajePares = calcularPorcentaje(0, 0);
    porcentajeTicketPromedio = calcularPorcentaje(0, 0);
    totalPeriodo = 0;
  }

  //Actualizar comparativas
  actualizarComparativas(etiquetaVentaTotal, porcentajeVenta, iconoVentaTotal, periodo);
  actualizarComparativas(etiquetaTotalPedidos, porcentajePedidos, iconoTotalPedidos, periodo);
  actualizarComparativas(etiquetaTotalPares, porcentajePares, iconoTotalPares, periodo);
  actualizarComparativas(etiquetaTicketPromedio, porcentajeTicketPromedio, iconoTicketPromedio, periodo);
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

/*--------------------------------------------------- Gráficos Generales-------------------------------------------- */
const graficoVentas = document.getElementById('graficoVentas');
const contenedorVentasCategoria = document.getElementById('graficoVentasCategoria');
const contenedorTop5Calzados = document.getElementById('graficoTop5Calzados');
const contenedorVentasTalla = document.getElementById('graficoVentasTalla');

let descripcionEvolucionVentas = document.getElementById('descripcionEvolucion');
let graficoEvolucionVentas;

async function CargarEvolucionVentas(periodo, rangosFechas, filtros) {
    try {
        const response = await fetch('../configuraciones/dashboard/graficos/Generales/evolucionVentas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({fechas: rangosFechas, filtros: filtros }),
        });
        const data = await response.json();
        const datosGraficos = clasificaciónDatosEvolucionVentas(data, periodo, rangosFechas);
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
  }

  let fechaFinActual = fechaPeriodos.fechaFinalActual;
  let fechaFinAnterior = fechaPeriodos.fechaFinalAnterior;
  let fechaInicioActual = fechaPeriodos.fechaInicialActual;
  let fechaInicioAnterior = fechaPeriodos.fechaInicialAnterior;

  let ventasTotalesActual = [];
  let fechasActual = [];
  let ventasTotalesAnteriores = [];
  let fechasAnteriores = [];

  if (periodo === 'mesFiltro') {
    const fechaInicioMesActual = crearFechaLocal(fechaInicioActual);
    const fechaInicioMesAnterior = crearFechaLocal(fechaInicioAnterior);
    // Cantidad de días del mes seleccionado
    const cantidadDiasMes = crearFechaLocal(fechaFinActual).getDate();

    for (let dia = 1; dia <= cantidadDiasMes; dia++) {
      /*Mes Actual*/
      let fecha = new Date(
          fechaInicioMesActual.getFullYear(),
          fechaInicioMesActual.getMonth(),
          dia
      );

      let fechaFormateada = formatearFecha(fecha, 1);
      let fechaAbreviada = formatearFecha(fecha, 2);

      fechasActual.push(fechaAbreviada);

      if (fechaActual.includes(fechaFormateada)) {
          ventasTotalesActual.push(
              datosActuales[fechaActual.indexOf(fechaFormateada)]
          );
      }
      else {
          ventasTotalesActual.push(0);
      }
      /*Mes Anterior*/
      let fechaComparacionAnterior = new Date(
          fechaInicioMesAnterior.getFullYear(),
          fechaInicioMesAnterior.getMonth(),
          dia
      );

      // Verificar que no se haya avanzado al siguiente mes
      if (fechaComparacionAnterior.getMonth() !== fechaInicioMesAnterior.getMonth()) {
          ventasTotalesAnteriores.push(0);
          fechasAnteriores.push('');
      }
      else {
          let fechaAnteriorFormateada = formatearFecha(fechaComparacionAnterior, 1);
          let fechaAnteriorAbreviada = formatearFecha(fechaComparacionAnterior, 2);

          fechasAnteriores.push(fechaAnteriorAbreviada);

          if (fechaAnterior.includes(fechaAnteriorFormateada)) {
              ventasTotalesAnteriores.push(
                  datosAnteriores[fechaAnterior.indexOf(fechaAnteriorFormateada)]
              );
          }
          else {
              ventasTotalesAnteriores.push(0);
          }
      }
    }

    categorias = Array.from(
        { length: cantidadDiasMes },
        (_, i) => `Día ${i + 1}`
    );  
  }
  else {
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

async function CargarEvolucionVentasAnual(periodo, rangosFechas, filtros) {
    try {
        const response = await fetch('../configuraciones/dashboard/graficos/Generales/evolucionAnualVentas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({fechas: rangosFechas, filtros: filtros }),
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

  if (graficoEvolucionVentas) {
    graficoEvolucionVentas.destroy();
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
          const esPeriodoAnual = periodo === 'anioAFechaActual' || periodo === 'anioHistorico';
          const fechaAct = fechasActual?.[dataPointIndex] || '';
          const fechaAnt = fechasAnteriores?.[dataPointIndex] || '';
          const tituloPunto = esPeriodoAnual
            ? `Mes: ${fechaAct}`
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
            fechasActual: categorias,
            fechasAnteriores: categorias,
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
            ? `Mes: ${fechaAct}`
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

  graficoEvolucionVentas = new ApexCharts(graficoVentas, options);
  window.graficoEvolucionVentas = graficoEvolucionVentas;
  graficoEvolucionVentas.render();
}

/*------------------------------- Gráfico Ventas por Categoria ---------------------*/
let totalPeriodo = 0;

async function CargarVentasCategoria (periodo, rangosFechas, filtros){
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Generales/ventasCategoria.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({fechas: rangosFechas, filtros: filtros}),
    });
    const data = await response.json();

    actualizarGraficoVentasCategoria(data);    
  } catch (error) {
    console.error('Error al cargar las ventas por categoría:', error);
  }
}

let graficoVentasCategoria;
function actualizarGraficoVentasCategoria(datos){
  contenedorVentasCategoria.innerHTML = "";

  if (graficoVentasCategoria) {
    graficoVentasCategoria.destroy();
  }

  if(datos.length === 0) {
    contenedorVentasCategoria.innerHTML = 
      `<p class="mensajeSinDatos">
         No se encontraron ventas para el período seleccionado.
      </p>`;
    return; 
  }

  let datosGrafico = datos.map(item => ({
    categoria: item.Categoria,
    ventas: Number(item.TotalCategoria),
    cantidad: Number(item.CantidadVendida),
  }));

  const opcionesCategoria = {
    chart: {
      type: 'donut',
      height: 310,
      width: '100%',
      fontFamily: 'Poppins, sans-serif'
    },
    colors: [
      '#1E3A8A', // Azul marino profundo
      '#1D4ED8', // Azul rey
      '#2563EB', // Azul vivo
      '#3B82F6', // Azul medio
      '#60A5FA', // Azul claro
      '#93C5FD', // Azul pastel
      '#BFDBFE', // Azul muy suave
      '#DBEAFE', // Azul casi blanco
    ],
    series: datosGrafico.map(item => item.ventas),
    labels: datosGrafico.map(item => item.categoria),
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
              label: 'Total',
              fontFamily: 'Poppins, sans-serif',
              fontSize: '14px',
              fontWeight: 600,
              color: '#64748b',
              formatter: function(w) {
                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                return 'Q ' + total.toLocaleString('es-GT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
              }
            },
            value: {
              fontFamily: 'Poppins, sans-serif',
              fontSize: '16px',
              fontWeight: 700,
              color: '#1e293b'
            }
          }
        },
      },
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
      y: {
        formatter: (val) => 'Q ' + val.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      }
    }
  };
  
  graficoVentasCategoria = new ApexCharts(contenedorVentasCategoria, opcionesCategoria);
  window.graficoVentasCategoria = graficoVentasCategoria;
  graficoVentasCategoria.render();  
}

/*--------------------------------------- Gráfico de Productos más vendidos ----------------------*/
async function cargarProductosMasVendidos (periodo, rangosFechas, filtros){
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Generales/top5Calzados.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({fechas: rangosFechas, filtros: filtros}),
    });
    const data = await response.json();

    actualizarProductosMasVendidos(data);    
  } catch (error) {
    console.error('Error al cargar los productos más vendidos:', error);
  }
}

let datosProductosMasVendidos = [];

function actualizarProductosMasVendidos(datos){
  const datosTabla = document.getElementById("datosTablaTopCalzados");

  datosTabla.innerHTML = ""; 
  
  if(datos.length === 0) {
    datosProductosMasVendidos = datos;
    datosTabla.innerHTML = 
      `<tr>
         <td colspan="5" class="mensajeSinDatos">No se encontraron ventas para el período seleccionado.</td>
      </tr>`;
    return; 
  }

  datos.forEach(item => {
    const tr = document.createElement("tr");
    const totalVendidoNum = Number(item.TotalVendido) || 0;
    const totalFormateado = 'Q ' + totalVendidoNum.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const cantidadVendidaNum = Number(item.CantidadVendida) || 0;

    tr.innerHTML = `
      <td class="tdCodigo"><span class="badgeCodigo">#${item.codigoCalzado}</span></td>
      <td class="tdCalzado" title="${item.Calzado}">${item.Calzado}</td>
      <td class="tdCategoria"><span class="badgeCategoria">${item.Categoria}</span></td>
      <td class="tdCantidad"><span class="badgeCantidad">${cantidadVendidaNum}</span></td>
      <td class="tdTotal">${totalFormateado}</td>
    `;
    datosTabla.appendChild(tr);
  });

}


/*--------------------------------------- Gráfico de Tallas más vendidas ----------------------*/
async function cargarTallasMasVendidas (periodo, rangosFechas, filtros){
  try {
    const response = await fetch('../configuraciones/dashboard/graficos/Generales/ventasTallas.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({fechas: rangosFechas, filtros: filtros}),
    });
    const data = await response.json();

    actualizarTallasMasVendidas(data);    
  } catch (error) {
    console.error('Error al cargar las tallas más vendidas:', error);
  }
}

let graficoTallasMasVendidas;
function actualizarTallasMasVendidas(datos){

  let tallasPredeterminadas = [33,34,35,36,37,38,39,40] 
  contenedorVentasTalla.innerHTML = ""; 
  
  if(graficoTallasMasVendidas) {
    graficoTallasMasVendidas.destroy();
  }
  
  if(datos.length === 0) {
    contenedorVentasTalla.innerHTML = 
      `<p class="mensajeSinDatos">
         No se encontraron ventas para el período seleccionado.
      </p>`;
    return; 
  }

  let datosGrafico = datos.map(item => ({
    talla: Number(item.Talla),
    cantidad: Number(item.CantidadVendida),
    total: Number(item.TotalPorTalla),
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

  const opcionesTallas = {
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

  graficoTallasMasVendidas = new ApexCharts(contenedorVentasTalla, opcionesTallas);
  window.graficoTallasMasVendidas = graficoTallasMasVendidas;
  graficoTallasMasVendidas.render();  
}