function seleccionRango(rango){
  const fecha = new Date();
  let fechaInicialActual;
  let fechaFinalActual;
  let fechaInicialAnterior;
  let fechaFinalAnterior;

  switch (rango) {
    case "Hoy":
      fechaInicialActual = formatearFecha(fecha, 1);
      fechaFinalActual = fechaInicialActual;
      fecha.setDate(fecha.getDate() - 1);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      fechaFinalAnterior = fechaInicialAnterior;
      break;

    case "7dias":
      fechaFinalActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 6);
      fechaInicialActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 1);
      fechaFinalAnterior = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 6);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      break;

    case "30dias":
      fechaFinalActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 29);
      fechaInicialActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 1);
      fechaFinalAnterior = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 29);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      break;
    default:
      break;
  }
  return {
    fechaInicialActual,
    fechaFinalActual,
    fechaInicialAnterior,
    fechaFinalAnterior
  };
}

function seleccionRangoEvolucionVentas(rango){
  const fecha = new Date();
  let fechaInicialActual;
  let fechaFinalActual;
  let fechaInicialAnterior;
  let fechaFinalAnterior;

  switch (rango) {
    case "Hoy":
      fechaFinalActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 4);
      fechaInicialActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 1);
      fechaFinalAnterior = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 4);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      break;

    case "7dias":
      fechaFinalActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 6);
      fechaInicialActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 1);
      fechaFinalAnterior = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 6);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      break;

    case "30dias":
      fechaFinalActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 29);
      fechaInicialActual = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 1);
      fechaFinalAnterior = formatearFecha(fecha, 1);
      fecha.setDate(fecha.getDate() - 29);
      fechaInicialAnterior = formatearFecha(fecha, 1);
      break;
    default:
      break;
  }
  return {
    fechaInicialActual,
    fechaFinalActual,
    fechaInicialAnterior,
    fechaFinalAnterior
  };
}


function formatearFecha(fecha, tipoConversión) {
  
  if(tipoConversión === 1){
    return fecha.getFullYear() + '-' +
      String(fecha.getMonth() + 1).padStart(2, '0') + '-' +
      String(fecha.getDate()).padStart(2, '0');
  }
  else if(tipoConversión === 2){
    return new Intl.DateTimeFormat('es-GT', {
      day: 'numeric',
      month: 'short'
    }).format(fecha);
  }
}

function crearFechaLocal(fechaStr) {
  if (!fechaStr) return new Date();
  if (fechaStr instanceof Date) return new Date(fechaStr);
  const [año, mes, dia] = fechaStr.split('-');
  return new Date(año, mes - 1, dia);
}

function formatearRangoTexto(rango, fechas) {
  const fInicio = crearFechaLocal(fechas.fechaInicialActual);
  const fFin = crearFechaLocal(fechas.fechaFinalActual);

  const opcionesConAnio = { day: 'numeric', month: 'short', year: 'numeric' };
  const opcionesSinAnio = { day: 'numeric', month: 'short' };
  
  const textoFin = fFin.toLocaleDateString('es-GT', opcionesConAnio);
  const textoInicio = fInicio.toLocaleDateString('es-GT', fInicio.getFullYear() === fFin.getFullYear() ? opcionesSinAnio : opcionesConAnio);

  let tituloPeriodo = '';
  let rangoFechasTexto = '';

  if (rango === 'Hoy') {
    tituloPeriodo = '';
    rangoFechasTexto = textoFin;
  } else if (rango === '7dias') {
    tituloPeriodo = '';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  } else if (rango === '30dias') {
    tituloPeriodo = '';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  } else if (rango === 'mesFiltro'){
    tituloPeriodo = 'Mes';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  }
  else if (rango === 'anioAFechaActual') {
    tituloPeriodo = 'Año a la fecha';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  }
  else if (rango === 'anioHistorico') {
    tituloPeriodo = 'Año';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  }
  else {
    tituloPeriodo = 'Periodo seleccionado';
    rangoFechasTexto = `${textoInicio} — ${textoFin}`;
  }

  return { tituloPeriodo, rangoFechasTexto };
}

/* ---------------------------- Función para obtener fechas de los filtros ---------------------------- */
function obtenerFechasRangoFiltro(mes, anio) {

  const fechaActual = new Date();

  let fechaInicio;
  let fechaFin;
  let fechaInicioAnterior;
  let fechaFinAnterior;

  // Solo año
  if (anio && !mes) {

      const anioSeleccionado = Number(anio);
      const anioActual = fechaActual.getFullYear();

      // Rango actual
      fechaInicio = new Date(anioSeleccionado, 0, 1);

      if (anioSeleccionado === anioActual) {
          // Año actual: desde enero hasta hoy
          fechaFin = fechaActual;
      } else {
          // Año pasado: año completo
          fechaFin = new Date(anioSeleccionado, 11, 31);
      }

      // Rango anterior
      fechaInicioAnterior = new Date(anioSeleccionado - 1, 0, 1);

      if (anioSeleccionado === anioActual) {
          // Mismo período del año anterior
          fechaFinAnterior = new Date(
              anioSeleccionado - 1,
              fechaActual.getMonth(),
              fechaActual.getDate()
          );
      } else {
          // Año completo anterior
          fechaFinAnterior = new Date(anioSeleccionado - 1, 11, 31);
      }

  }

  // Mes + año
  else if (anio && mes) {

      // Rango actual
      fechaInicio = new Date(anio, mes - 1, 1);
      fechaFin = new Date(anio, mes, 0);

      // Mes anterior
      fechaInicioAnterior = new Date(anio, mes - 2, 1);
      fechaFinAnterior = new Date(anio, mes - 1, 0);
  }

  return {
      fechaInicialActual: formatearFecha(fechaInicio, 1),
      fechaFinalActual: formatearFecha(fechaFin, 1),
      fechaInicialAnterior: formatearFecha(fechaInicioAnterior, 1),
      fechaFinalAnterior: formatearFecha(fechaFinAnterior, 1)
  };
}