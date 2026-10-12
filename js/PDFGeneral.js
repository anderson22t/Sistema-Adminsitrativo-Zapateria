const { jsPDF } = window.jspdf;

function obtenerDatosKPI() {
    return Array.from(document.querySelectorAll('.cardKPI')).map((tarjeta) => ({
        titulo: tarjeta.querySelector('h3')?.textContent.trim() || '',
        valor: tarjeta.querySelector('.datoConsultado')?.textContent.trim() || '',
        comparativa: tarjeta.querySelector('.comparativa')?.textContent.trim() || ''
    }));
}

function obtenerTextoReporte() {
    const periodo = 'Período: ';
    const fechas = document.getElementById('opcionPeriodo').textContent.trim() || '';
    const canal = document.getElementById('opcionCanalVenta').textContent.trim();
    const tipo = document.getElementById('opcionTipoVenta').textContent.trim();

    return { periodo, fechas, canal, tipo };
}

function obtenerGrafico(nombre) {
    try {
        return typeof window[nombre] !== 'undefined' ? window[nombre] : undefined;
    } catch (error) {
        return undefined;
    }
}

async function obtenerImagenGrafico(grafico) {
    if (!grafico || typeof grafico.dataURI !== 'function') {
        return null;
    }

    const resultado = await grafico.dataURI();
    return resultado?.imgURI || null;
}

async function obtenerProporcionImagen(imagen) {
    return new Promise((resolve) => {
        const elemento = new Image();
        elemento.onload = () => resolve(elemento.width / elemento.height || 1.6);
        elemento.onerror = () => resolve(1.6);
        elemento.src = imagen;
    });
}

function agregarEncabezado(pdf, contexto, pagina) {
    const ancho = pdf.internal.pageSize.getWidth();

    pdf.setTextColor(44, 62, 80);
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(18);
    pdf.text('Reporte de Análisis General de Ventas', ancho / 2, 16, { align: 'center' });

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(10);
    pdf.text(contexto.periodo, ancho / 2, 23, { align: 'center' });
    pdf.setTextColor(90, 100, 110);
    pdf.text(contexto.fechas, ancho / 2, 29, { align: 'center' });
    pdf.setFontSize(9);
    pdf.text(`Filtros: Canal de Venta: ${contexto.canal} | Tipo de Venta: ${contexto.tipo}`, ancho / 2, 35, { align: 'center' });

    pdf.setFontSize(8);
    pdf.text(`Página ${pagina}`, ancho - 14, 202, { align: 'right' });
}

function agregarIndicadores(pdf, indicadores) {
    const margen = 14;
    const espacio = 5;
    const anchoTotal = pdf.internal.pageSize.getWidth() - margen * 2;
    const anchoTarjeta = (anchoTotal - espacio * 3) / 4;
    const y = 44;

    indicadores.forEach((indicador, indice) => {
        const x = margen + indice * (anchoTarjeta + espacio);
        pdf.setFillColor(248, 247, 247);
        pdf.setDrawColor(220, 224, 228);
        pdf.roundedRect(x, y, anchoTarjeta, 25, 2, 2, 'FD');
        pdf.setTextColor(44, 62, 80);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9);
        pdf.text(indicador.titulo, x + 4, y + 7);
        pdf.setFontSize(13);
        pdf.text(indicador.valor, x + 4, y + 15);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(90, 100, 110);
        pdf.text(indicador.comparativa || 'Sin comparación', x + 4, y + 21);
    });
}

function agregarTituloSeccion(pdf, texto, y) {
    pdf.setTextColor(44, 62, 80);
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(12);
    pdf.text(texto, 14, y);
}

async function agregarGrafico(pdf, imagen, titulo, y, altoMaximo = 67) {
    agregarTituloSeccion(pdf, titulo, y);
    if (!imagen) {
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(110, 110, 110);
        pdf.text('No hay datos disponibles para este gráfico.', 14, y + 10);
        return;
    }

    const anchoMaximo = 235;
    const proporcion = await obtenerProporcionImagen(imagen);
    const alto = Math.min(altoMaximo, anchoMaximo / proporcion);
    const ancho = alto * proporcion;
    const x = (pdf.internal.pageSize.getWidth() - ancho) / 2;
    pdf.addImage(imagen, 'PNG', x, y + 4, ancho, alto, undefined, 'FAST');
}

function agregarTablaProductos(pdf, y = 58) {
    const datos = Array.isArray(window.datosProductosMasVendidos)
        ? window.datosProductosMasVendidos
        : Array.from(document.querySelectorAll('#datosTablaTopCalzados tr')).map((fila) => ({
                codigoCalzado: fila.cells[0]?.textContent.trim() || '',
                Calzado: fila.cells[1]?.textContent.trim() || '',
                Categoria: fila.cells[2]?.textContent.trim() || '',
                CantidadVendida: fila.cells[3]?.textContent.trim() || '',
                TotalVendido: fila.cells[4]?.textContent.trim() || ''
            }));

    const columnas = ['Código', 'Calzado', 'Categoría', 'Cantidad', 'Total vendido'];
    const x = 14;
    const ancho = pdf.internal.pageSize.getWidth() - 28;
    const anchos = [30, 90, 55, 35, 53];
    const inicioTabla = y;
    const altoFila = 9;

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
    if (!datos.length || (datos.length === 1 && /no se encontraron/i.test(datos[0].codigoCalzado || ''))) {
        pdf.text('No se encontraron productos para el período seleccionado.', x + 3, y + 6);
        return;
    }

    datos.slice(0, 5).forEach((dato, indice) => {
        if (indice % 2 === 1) {
            pdf.setFillColor(248, 249, 250);
            pdf.rect(x, y, ancho, altoFila, 'F');
        }

        posicion = x;
        const valores = [
            `#${dato.codigoCalzado || ''}`,
            dato.Calzado || dato.Calzado || '',
            dato.Categoria || '',
            String(dato.CantidadVendida || ''),
            `Q ${dato.TotalVendido || ''}`
        ];
        valores.forEach((valor, valorIndice) => {
            pdf.text(String(valor).slice(0, valorIndice === 1 ? 42 : 24), posicion + 3, y + 6);
            posicion += anchos[valorIndice];
        });
        y += altoFila;
    });
    pdf.setDrawColor(210, 214, 218);
    pdf.rect(x, inicioTabla, ancho, y - inicioTabla);
}

async function generarPDFGeneral() {
    const boton = document.getElementById('btnExportarPDF');
    const textoOriginal = boton?.innerHTML;

    try {
        if (boton) {
            boton.disabled = true;
            boton.innerHTML = 'Generando PDF...';
        }

        const [imagenEvolucion, imagenCategoria, imagenTallas] = await Promise.all([
            obtenerImagenGrafico(obtenerGrafico('graficoEvolucionVentas')),
            obtenerImagenGrafico(obtenerGrafico('graficoVentasCategoria')),
            obtenerImagenGrafico(obtenerGrafico('graficoTallasMasVendidas'))
        ]);

        const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const contexto = obtenerTextoReporte();
        const indicadores = obtenerDatosKPI();

        agregarEncabezado(pdf, contexto, 1);
        agregarIndicadores(pdf, indicadores);
            await agregarGrafico(pdf, imagenEvolucion, 'Evolución de Ventas', 77, 100);

        pdf.addPage();
        agregarEncabezado(pdf, contexto, 2);
        await agregarGrafico(pdf, imagenCategoria, 'Ventas por Categoría', 40, 68);
        await agregarGrafico(pdf, imagenTallas, 'Ventas por Talla', 122, 68);

        pdf.addPage();
        agregarEncabezado(pdf, contexto, 3);
        agregarTituloSeccion(pdf, 'Productos Más Vendidos', 50);
        agregarTablaProductos(pdf, 58);

        pdf.save(`reporte-analisis-general-${new Date().toISOString().slice(0, 10)}.pdf`);
    } catch (error) {
        console.error('Error al generar el PDF:', error);
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

document.getElementById('btnExportarPDF')?.addEventListener('click', generarPDFGeneral);

