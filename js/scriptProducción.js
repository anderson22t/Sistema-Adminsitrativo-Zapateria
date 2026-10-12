/*--------Cambiar de formulario de calzado existente a nuevo--------*/
const btnCalzadoNuevo = document.getElementById('calzadoNuevo');
const btnCalzadoExistente = document.getElementById('calzadoExistente');
const inputCodigoCalzado = document.getElementById('buscarCodigo');
const btnNuevoCalzado = document.getElementById('btnNuevoCalzado');
const modalCatalogo = document.getElementById('modalCatalogo');
const catalogoPorCodigo = new Map();
const produccionesPorCodigo = new Map();

btnCalzadoExistente.addEventListener('click', function() {
    const catalogo = document.querySelector('.catalogoCalzado');
    const panelProduccion = document.getElementById('panelRegistrarProduccion');
    if (catalogo) {
        catalogo.style.display = 'none';
    }
    if (panelProduccion) {
        panelProduccion.hidden = false;
    }
    document.querySelector('.formularioCalzado.Existente').style.display = 'grid';
    document.querySelector('.formularioCalzado.Nuevo').style.display = 'none';
    btnCalzadoExistente.style.backgroundColor = '#2C3E50';
    btnCalzadoExistente.style.color = '#F8F7F7';
    btnCalzadoNuevo.style.border = '1px solid #000000';
    btnCalzadoNuevo.style.backgroundColor = 'transparent';
    btnCalzadoNuevo.style.color = '#000000';
});

btnCalzadoNuevo.addEventListener('click', function() {
    const catalogo = document.querySelector('.catalogoCalzado');
    const panelProduccion = document.getElementById('panelRegistrarProduccion');
    if (catalogo) {
        catalogo.style.display = 'flex';
    }
    if (panelProduccion) {
        panelProduccion.hidden = true;
    }
    btnCalzadoNuevo.style.backgroundColor = '#2C3E50';
    btnCalzadoNuevo.style.color = '#F8F7F7';
    btnCalzadoExistente.style.border = '1px solid #000000';
    btnCalzadoExistente.style.backgroundColor = 'transparent';
    btnCalzadoExistente.style.color = '#000000';

    if (modalCatalogo) {
        return;
    }

    document.querySelector('.formularioCalzado.Nuevo').style.display = 'grid';
});



const formularioCalzadoNuevo = document.getElementById('formCalzadoNuevo'); 
const formularioCalzadoExistente = document.getElementById('formCalzadoExistente');


formularioCalzadoNuevo.addEventListener('submit', function(e){
    e.preventDefault();
    const datos =  new FormData(formularioCalzadoNuevo);
    const modoEdicion = formularioCalzadoNuevo.dataset.mode === 'edit';
    const ruta = modoEdicion
        ? '../../php/configuraciones/actualizarDatos/actualizarCatalogo.php'
        : '../../php/configuraciones/insertarDatos/ingresoNuevoCalzado.php';

    fetch(ruta, {
        method: 'POST',
        body: datos
    })
        .then(r => r.json())
        .then(data=> {
            if (data.estado) {
                mostrarToast(data.mensaje, "success");
                if (modalCatalogo) {
                    if (!modoEdicion) {
                        agregarCalzadoAlSelector(datos);
                    }
                    cerrarModalCalzado();
                    obtenerCatalogo(inputCodigoCalzado.value.trim());
                } else {
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }
            } else {
                mostrarToast(data.mensaje, "error");
            }
        })
        .catch(error => {
            console.error(error);
            mostrarToast("No fue posible guardar el calzado.", "error");
        });

})

formularioCalzadoExistente.addEventListener('submit', function(e){
    e.preventDefault();
    const datos =  new FormData(formularioCalzadoExistente);
    const modoEdicion = formularioCalzadoExistente.dataset.mode === 'edit';
    const ruta = modoEdicion
        ? '../../php/configuraciones/actualizarDatos/actualizarProduccion.php'
        : '../../php/configuraciones/insertarDatos/ingresoProduccionExistente.php';

    fetch(ruta, {
        method: 'POST',
        body: datos
    })
        .then(r => r.json())
        .then(data=> {
            if (data.estado) {
                mostrarToast(data.mensaje, "success");
                formularioCalzadoExistente.reset();
                if (document.getElementById('modalProduccion')) {
                    cerrarModalProduccion();
                    obtenerHistorialProduccion(document.getElementById('buscarCodigoProduccion').value.trim());
                }
            } else {
                mostrarToast(data.mensaje, "error");
            }
        })
        .catch(error => {
            console.error(error);
            mostrarToast("No fue posible guardar la producción.", "error");
        });
})

/* --------------------- Funciones para mostrar el catálogo de calzado --------------------- */
async function obtenerCatalogo (codigoCalzadoIngresado) {    
    try {
        const respuesta = await fetch(`../../php/configuraciones/consultarDatos/seleccionCatalogo.php?codigoCalzado=${codigoCalzadoIngresado}`);
        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || 'No fue posible cargar el catálogo.');
        }

        if (!Array.isArray(datos)) {
            throw new Error('La respuesta del catálogo tiene un formato inesperado.');
        }

        mostrarCatalogo(datos);
    } catch (error) {
        console.error(error);
        mostrarErrorCatalogo();
    }

}

function mostrarCatalogo (datos) {
    const datosTablaCatalogo = document.getElementById('datosTablaCatalogo');
    datosTablaCatalogo.innerHTML = '';
    catalogoPorCodigo.clear();

    if (datos.length === 0) {
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = 7;
        celda.textContent = 'No se encontraron modelos.';
        fila.appendChild(celda);
        datosTablaCatalogo.appendChild(fila);
        return;
    }

    datos.forEach(element => {
        catalogoPorCodigo.set(String(element.codigoCalzado), element);
        const fila = document.createElement('tr');

        [
            element.codigoCalzado,
            element.Tipo,
            element.Linea,
            element.Descripcion,
            'Q ' + element.PrecioCosto
        ].forEach(valor => {
            const celda = document.createElement('td');
            celda.textContent = valor ?? '';
            fila.appendChild(celda);
        });

        const celdaEstado = document.createElement('td');
        const etiquetaEstado = document.createElement('span');
        const estado = String(element.Estado ?? '').trim().toLowerCase();
        etiquetaEstado.textContent = element.Estado ?? '';

        if (estado === 'activo') {
            etiquetaEstado.classList.add('estadoActivo');
        } else if (estado === 'inactivo') {
            etiquetaEstado.classList.add('estadoInactivo');
        }

        celdaEstado.appendChild(etiquetaEstado);
        fila.appendChild(celdaEstado);

        const celdaOpciones = document.createElement('td');
        const botonEditar = document.createElement('button');
        botonEditar.type = 'button';
        botonEditar.className = 'btnEditar btn-action-icon';
        botonEditar.title = 'Editar';
        botonEditar.dataset.id = element.codigoCalzado;
        botonEditar.setAttribute('aria-label', `Editar modelo ${element.codigoCalzado}`);

        const iconoEditar = document.createElement('ion-icon');
        iconoEditar.setAttribute('name', 'create-outline');
        botonEditar.appendChild(iconoEditar);
        celdaOpciones.appendChild(botonEditar);
        fila.appendChild(celdaOpciones);

        datosTablaCatalogo.appendChild(fila);
    });
}

function mostrarErrorCatalogo () {
    const datosTablaCatalogo = document.getElementById('datosTablaCatalogo');
    datosTablaCatalogo.innerHTML = '';
    const fila = document.createElement('tr');
    const celda = document.createElement('td');
    celda.colSpan = 7;
    celda.textContent = 'Ocurrió un error al cargar el catálogo. Intente nuevamente.';
    fila.appendChild(celda);
    datosTablaCatalogo.appendChild(fila);
}

function abrirModalCalzado(modo, modelo = null) {
    const formulario = document.getElementById('formCalzadoNuevo');
    const codigo = document.getElementById('codigoCalzadoNuevo');
    const campoEstado = document.getElementById('campoEstadoCalzado');
    const estado = document.getElementById('estadoCalzado');
    const titulo = document.getElementById('tituloModalCalzado');
    const botonGuardar = document.getElementById('btnGuardarCalzado');

    formulario.reset();
    formulario.style.display = 'grid';
    formulario.dataset.mode = modo;
    codigo.readOnly = modo === 'edit';
    campoEstado.hidden = modo !== 'edit';
    estado.disabled = modo !== 'edit';

    if (modo === 'edit' && modelo) {
        codigo.value = modelo.codigoCalzado;
        document.getElementById('tipo').value = modelo.Tipo;
        document.getElementById('descripcionCalzadoNuevo').value = modelo.Descripcion;
        document.getElementById('linea').value = modelo.Linea;
        document.getElementById('precioCosto').value = modelo.PrecioCosto;
        estado.value = String(modelo.Estado).toLowerCase() === 'activo' ? '1' : '0';
        titulo.textContent = 'Editar calzado';
        botonGuardar.value = 'Guardar cambios';
    } else {
        titulo.textContent = 'Registrar nuevo calzado';
        botonGuardar.value = 'Añadir Nuevo Calzado';
    }

    modalCatalogo.hidden = false;
    codigo.focus();
}

function cerrarModalCalzado() {
    if (!modalCatalogo) {
        return;
    }
    modalCatalogo.hidden = true;
    document.getElementById('formCalzadoNuevo').reset();
    btnNuevoCalzado.focus();
}

function agregarCalzadoAlSelector(datos) {
    const selector = document.getElementById('codigoCalzadoExistente');
    const codigo = datos.get('codigoCalzadoNuevo');
    const descripcion = datos.get('descripcionCalzadoNuevo');

    if (!selector || Array.from(selector.options).some(opcion => opcion.value === String(codigo))) {
        return;
    }

    const opcion = new Option(`${codigo} - ${descripcion}`, codigo);
    selector.appendChild(opcion);
}

/*-------------------------------- Funciones para mostrar el historial de producción --------------------------------*/
async function obtenerHistorialProduccion(codigoCalzado = '') {
    try {
        const parametros = new URLSearchParams({ codigoCalzado });
        const respuesta = await fetch(`../../php/configuraciones/consultarDatos/seleccionHistorialProduccion.php?${parametros}`);
        const datos = await respuesta.json();

        if (!respuesta.ok || !Array.isArray(datos)) {
            throw new Error(datos.error || 'No fue posible cargar el historial de producción.');
        }

        mostrarHistorialProduccion(datos);
    } catch (error) {
        console.error(error);
        mostrarErrorHistorialProduccion();
    }
}

function mostrarHistorialProduccion(datos) {
    const tbody = document.getElementById('datosTablaProduccion');
    tbody.replaceChildren();
    produccionesPorCodigo.clear();

    if (datos.length === 0) {
        agregarMensajeTabla(tbody, 6, 'No se encontraron registros de producción.');
        return;
    }

    datos.forEach(produccion => {
        const identificador = String(produccion.codigoIngresoProduccion);
        produccionesPorCodigo.set(identificador, produccion);

        const fila = document.createElement('tr');
        fila.dataset.id = identificador;
        [
            formatearFechaProduccion(produccion.FechaProduccion),
            produccion.codigoCalzado,
            produccion.Color,
            produccion.Talla,
            produccion.Cantidad
        ].forEach(valor => {
            const celda = document.createElement('td');
            celda.textContent = valor ?? '';
            fila.appendChild(celda);
        });

        const celdaAcciones = document.createElement('td');
        celdaAcciones.className = 'accionesTablaProduccion';
        celdaAcciones.appendChild(crearBotonAccion('Editar producción', 'create-outline', 'btnEditarProduccion'));
        celdaAcciones.appendChild(crearBotonAccion('Eliminar producción', 'trash-outline', 'btnEliminarProduccion'));
        fila.appendChild(celdaAcciones);
        tbody.appendChild(fila);
    });
}

function crearBotonAccion(titulo, iconoNombre, clase) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = `btn-action-icon ${clase}`;
    boton.title = titulo;
    boton.setAttribute('aria-label', titulo);
    const icono = document.createElement('ion-icon');
    icono.name = iconoNombre;
    boton.appendChild(icono);
    return boton;
}

function formatearFechaProduccion(fecha) {
    const partes = String(fecha ?? '').split('-');
    return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : fecha;
}

function agregarMensajeTabla(tbody, columnas, mensaje) {
    const fila = document.createElement('tr');
    const celda = document.createElement('td');
    celda.colSpan = columnas;
    celda.textContent = mensaje;
    fila.appendChild(celda);
    tbody.appendChild(fila);
}

function mostrarErrorHistorialProduccion() {
    const tbody = document.getElementById('datosTablaProduccion');
    if (tbody) {
        tbody.replaceChildren();
        agregarMensajeTabla(tbody, 6, 'Ocurrió un error al cargar el historial de producción.');
    }
}

function abrirModalProduccion(modo, produccion = null) {
    const formulario = document.getElementById('formCalzadoExistente');
    const modal = document.getElementById('modalProduccion');
    formulario.reset();
    formulario.dataset.mode = modo;
    formulario.style.display = 'grid';

    if (modo === 'edit' && produccion) {
        document.getElementById('codigoIngresoProduccion').value = produccion.codigoIngresoProduccion;
        document.getElementById('codigoCalzadoExistente').value = produccion.codigoCalzado;
        document.getElementById('colorCalzado').value = produccion.Color;
        document.getElementById('tallaCalzado').value = produccion.Talla;
        document.getElementById('cantidadCalzado').value = produccion.Cantidad;
        document.getElementById('fechaProduccion').value = produccion.FechaProduccion;
        document.getElementById('tituloModalProduccion').textContent = 'Editar Producción';
        document.getElementById('btnGuardarProduccion').value = 'Guardar Cambios';
    } else {
        document.getElementById('tituloModalProduccion').textContent = 'Registrar Producción';
        document.getElementById('btnGuardarProduccion').value = 'Añadir a Inventario';
    }

    modal.hidden = false;
    document.getElementById('codigoCalzadoExistente').focus();
}

function cerrarModalProduccion() {
    const modal = document.getElementById('modalProduccion');
    if (!modal) {
        return;
    }
    modal.hidden = true;
    document.getElementById('formCalzadoExistente').reset();
    const botonNuevaProduccion = document.getElementById('btnNuevaProduccion');
    if (botonNuevaProduccion) {
        botonNuevaProduccion.focus();
    }
}

async function eliminarProduccion(codigoIngresoProduccion) {
    const confirmacion = await Swal.fire({
        title: '¿Eliminar este registro?',
        text: 'La cantidad registrada también se descontará del inventario.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) {
        return;
    }

    try {
        const datos = new FormData();
        datos.set('codigoIngresoProduccion', codigoIngresoProduccion);
        const respuesta = await fetch('../../php/configuraciones/eliminarDatos/eliminarProduccion.php', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.estado) {
            throw new Error(resultado.mensaje || 'No fue posible eliminar el registro.');
        }

        mostrarToast(resultado.mensaje, 'success');
        obtenerHistorialProduccion(document.getElementById('buscarCodigoProduccion').value.trim());
    } catch (error) {
        console.error(error);
        mostrarToast(error.message || 'No fue posible eliminar el registro.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const tbodyCatalogo = document.getElementById('datosTablaCatalogo');
    if (inputCodigoCalzado && tbodyCatalogo) {
        let temporizadorCatalogo;
        obtenerCatalogo(inputCodigoCalzado.value.trim());

        inputCodigoCalzado.addEventListener('input', () => {
            window.clearTimeout(temporizadorCatalogo);
            temporizadorCatalogo = window.setTimeout(() => obtenerCatalogo(inputCodigoCalzado.value.trim()), 300);
        });
        inputCodigoCalzado.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                window.clearTimeout(temporizadorCatalogo);
                obtenerCatalogo(inputCodigoCalzado.value.trim());
            }
        });

        if (modalCatalogo) {
            btnNuevoCalzado.addEventListener('click', () => abrirModalCalzado('create'));
            document.getElementById('btnCerrarModalCatalogo').addEventListener('click', cerrarModalCalzado);
            modalCatalogo.addEventListener('click', event => {
                if (event.target === modalCatalogo) cerrarModalCalzado();
            });
            tbodyCatalogo.addEventListener('click', event => {
                const botonEditar = event.target.closest('.btnEditar');
                const modelo = botonEditar && catalogoPorCodigo.get(botonEditar.dataset.id);
                if (modelo) abrirModalCalzado('edit', modelo);
            });
        }
    }

    const tbodyProduccion = document.getElementById('datosTablaProduccion');
    if (!tbodyProduccion) {
        return;
    }

    const filtroProduccion = document.getElementById('buscarCodigoProduccion');
    const modalProduccion = document.getElementById('modalProduccion');
    const botonNuevaProduccion = document.getElementById('btnNuevaProduccion');
    let temporizadorProduccion;

    obtenerHistorialProduccion(filtroProduccion.value.trim());
    filtroProduccion.addEventListener('input', () => {
        window.clearTimeout(temporizadorProduccion);
        temporizadorProduccion = window.setTimeout(() => obtenerHistorialProduccion(filtroProduccion.value.trim()), 300);
    });
    filtroProduccion.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            window.clearTimeout(temporizadorProduccion);
            obtenerHistorialProduccion(filtroProduccion.value.trim());
        }
    });

    botonNuevaProduccion.addEventListener('click', () => abrirModalProduccion('create'));
    document.getElementById('btnCerrarModalProduccion').addEventListener('click', cerrarModalProduccion);
    modalProduccion.addEventListener('click', event => {
        if (event.target === modalProduccion) cerrarModalProduccion();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modalProduccion.hidden) cerrarModalProduccion();
    });

    tbodyProduccion.addEventListener('click', event => {
        const fila = event.target.closest('tr[data-id]');
        if (!fila) return;
        const codigo = fila.dataset.id;
        if (event.target.closest('.btnEditarProduccion')) {
            abrirModalProduccion('edit', produccionesPorCodigo.get(codigo));
        } else if (event.target.closest('.btnEliminarProduccion')) {
            eliminarProduccion(codigo);
        }
    });
});