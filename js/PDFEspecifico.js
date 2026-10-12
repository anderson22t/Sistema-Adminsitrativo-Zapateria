const { jsPDF: jsPDFEspecifico } = window.jspdf;

function obtenerIndicadoresEspecificos() {
    return Array.from(document.querySelectorAll('.cardKPI')).map((tarjeta) => ({
        titulo: tarjeta.querySelector('h3')?.textContent.trim() || '',
        valor: tarjeta.querySelector('.datoConsultado')?.textContent.trim() || '',
        comparativa: tarjeta.querySelector('.comparativa')?.textContent.trim() || ''
    }));
}

function obtenerContextoEspecifico() {
    const texto = (id) => document.getElementById(id)?.textContent.trim() || '';
    const periodo = [texto('leyendaPeriodo').replace(/^Período:\s*/, ''), texto('opcionPeriodo')]
        .filter(Boolean)
        .join(': ');
    const modelo = texto('opcionCalzado') ||
        document.querySelector('#filtroCalzado option:checked')?.textContent.trim() || 'Sin modelo';

    return {
        periodo: periodo || 'Período no disponible',
        modelo,
        canal: texto('opcionCanalVenta') || 'Todos',
        tipo: texto('opcionTipoVenta') || 'Todos'
    };
}

async function obtenerImagenGraficoEspecifico(grafico) {
    if (!grafico || typeof grafico.dataURI !== 'function') {
        return null;
    }

    try {
        const resultado = await grafico.dataURI();
        return resultado?.imgURI || null;
    } catch (error) {
        console.error('No fue posible capturar un gráfico para el PDF:', error);
        return null;
    }
}

async function obtenerProporcionImagenEspecifica(imagen) {
    return new Promise((resolve) => {
        const elemento = new Image();
        elemento.onload = () => resolve(elemento.width / elemento.height || 1.6);
        elemento.onerror = () => resolve(1.6);
        elemento.src = imagen;
    });
}

function agregarEncabezadoEspecifico(pdf, contexto, pagina) {
    const ancho = pdf.internal.pageSize.getWidth();
    pdf.setTextColor(44, 62, 80);
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(17);
    pdf.text('Reporte de Análisis Específico de Ventas', ancho / 2, 14, { align: 'center' });

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(9);
    pdf.setTextColor(90, 100, 110);
    pdf.text(`Modelo: ${contexto.modelo}`, ancho / 2, 21, { align: 'center', maxWidth: ancho - 28 });
    pdf.text(`Período: ${contexto.periodo}`, ancho / 2, 27, { align: 'center', maxWidth: ancho - 28 });
    pdf.setFontSize(8);
    pdf.text(`Filtros: Canal de Venta: ${contexto.canal} | Tipo de Venta: ${contexto.tipo}`, ancho / 2, 33, { align: 'center', maxWidth: ancho - 28 });
    pdf.text(`Página ${pagina}`, ancho - 14, 202, { align: 'right' });
}

function agregarIndicadoresEspecificos(pdf, indicadores) {
    const margen = 14;
    const espacio = 5;
    const anchoTotal = pdf.internal.pageSize.getWidth() - margen * 2;
    const anchoTarjeta = (anchoTotal - espacio * 3) / 4;

    indicadores.forEach((indicador, indice) => {
        const x = margen + indice * (anchoTarjeta + espacio);
        pdf.setFillColor(248, 247, 247);
        pdf.setDrawColor(220, 224, 228);
        pdf.roundedRect(x, 40, anchoTarjeta, 25, 2, 2, 'FD');
        pdf.setTextColor(44, 62, 80);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9);
        pdf.text(indicador.titulo, x + 4, 47, { maxWidth: anchoTarjeta - 8 });
        pdf.setFontSize(13);
        pdf.text(indicador.valor, x + 4, 55, { maxWidth: anchoTarjeta - 8 });
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(90, 100, 110);
        pdf.text(indicador.comparativa || 'Sin comparación', x + 4, 61, { maxWidth: anchoTarjeta - 8 });
    });
}

function agregarTituloSeccionEspecifico(pdf, texto, y) {
    pdf.setTextColor(44, 62, 80);
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(12);
    pdf.text(texto, 14, y);
}

async function agregarGraficoEspecifico(pdf, imagen, titulo, y, altoMaximo = 67) {
    agregarTituloSeccionEspecifico(pdf, titulo, y);
    if (!imagen) {
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(110, 110, 110);
        pdf.text('No hay datos disponibles para este gráfico.', 14, y + 10);
        return;
    }

    const anchoMaximo = 235;
    const proporcion = await obtenerProporcionImagenEspecifica(imagen);
    const alto = Math.min(altoMaximo, anchoMaximo / proporcion);
    const ancho = alto * proporcion;
    const x = (pdf.internal.pageSize.getWidth() - ancho) / 2;
    pdf.addImage(imagen, 'PNG', x, y + 4, ancho, alto, undefined, 'FAST');
}

function agregarTablaCombinaciones(pdf, y = 48) {
    const filas = Array.from(document.querySelectorAll('#tablaCombinacionesColorTalla tr'))
        .map((fila) => Array.from(fila.cells).map((celda) => celda.textContent.trim()));
    const columnas = ['Color', 'Talla', 'Cantidad', 'Total vendido'];
    const x = 14;
    const ancho = pdf.internal.pageSize.getWidth() - 28;
    const anchos = [100, 45, 55, 69];
    const altoFila = 10;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);
    pdf.setFillColor(229, 231, 233);
    pdf.rect(x, y, ancho, altoFila, 'F');
    let posicion = x;
    columnas.forEach((columna, indice) => {
        pdf.text(columna, posicion + 3, y + 6);
        posicion += anchos[indice];
    });

    pdf.setFont('helvetica', 'normal');
    y += altoFila;
    if (!filas.length || /no se encontraron/i.test(filas[0][0] || '')) {
        pdf.text('No se encontraron combinaciones para el período seleccionado.', x + 3, y + 6);
        return;
    }

    filas.slice(0, 10).forEach((fila, indice) => {
        if (indice % 2 === 1) {
            pdf.setFillColor(248, 249, 250);
            pdf.rect(x, y, ancho, altoFila, 'F');
        }

        posicion = x;
        fila.slice(0, 4).forEach((valor, valorIndice) => {
            pdf.text(String(valor).slice(0, valorIndice === 0 ? 45 : 28), posicion + 3, y + 6);
            posicion += anchos[valorIndice];
        });
        y += altoFila;
    });
    pdf.setDrawColor(210, 214, 218);
    pdf.rect(x, 48, ancho, y - 48);
}

async function generarPDFEspecifico() {
    const boton = document.getElementById('btnExportarPDF');
    const textoOriginal = boton?.innerHTML;

    try {
        if (boton) {
            boton.disabled = true;
            boton.innerHTML = 'Generando PDF...';
        }

        const [imagenEvolucion, imagenColor, imagenTallas] = await Promise.all([
            obtenerImagenGraficoEspecifico(window.graficoEvolucionVentasEspecifico),
            obtenerImagenGraficoEspecifico(window.graficoVentasPorColorEspecifico),
            obtenerImagenGraficoEspecifico(window.graficoVentasPorTallasEspecifico)
        ]);
        const pdf = new jsPDFEspecifico({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const contexto = obtenerContextoEspecifico();

        agregarEncabezadoEspecifico(pdf, contexto, 1);
        agregarIndicadoresEspecificos(pdf, obtenerIndicadoresEspecificos());
        await agregarGraficoEspecifico(pdf, imagenEvolucion, 'Evolución de Ventas', 73, 105);

        pdf.addPage();
        agregarEncabezadoEspecifico(pdf, contexto, 2);
        await agregarGraficoEspecifico(pdf, imagenColor, 'Ventas por Color', 43, 68);
        await agregarGraficoEspecifico(pdf, imagenTallas, 'Ventas por Talla', 122, 68);

        pdf.addPage();
        agregarEncabezadoEspecifico(pdf, contexto, 3);
        agregarTituloSeccionEspecifico(pdf, 'Top 5 Combinaciones Color-Talla', 43);
        agregarTablaCombinaciones(pdf, 48);

        pdf.save(`reporte-analisis-especifico-${new Date().toISOString().slice(0, 10)}.pdf`);
    } catch (error) {
        console.error('Error al generar el PDF específico:', error);
        if (window.Swal) {
            Swal.fire('Error', 'No fue posible generar el PDF.', 'error');
        }
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
        }
    }
}

document.getElementById('btnExportarPDF')?.addEventListener('click', generarPDFEspecifico);