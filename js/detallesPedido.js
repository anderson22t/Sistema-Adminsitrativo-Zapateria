const contenedorDetalles = document.querySelector(".contenedorDetalles");
const contenedorEliminar = document.querySelector(".contenedorEliminar");
const detallesPedido = document.querySelector(".detallesPedido");
const actualizarPedido = document.querySelector(".actualizarPedido");
const iconoCerrarLista = document.getElementById("iconoCerrarLista");
const iconoCerrarActualizar = document.getElementById("iconoCerrarActualizar");
const btnActualizarDatos = document.getElementById("actualizarDatos");

const confirmarEliminacion = document.getElementById("confirmarEliminacion");
const cancelarEliminacion = document.getElementById("cancelarEliminacion");

let textoNoPedido = document.getElementById("codigoPedido");


let datosPedidos = null;
let datosProductos = null;
var totalPedido = 0;

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnVerDetalles")) {
        contenedorDetalles.style.display = "block";
        detallesPedido.style.display = "block";
        mostrarDetallesPedido(e.target.closest('tr'));
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("iconoEditar")) {
        contenedorDetalles.style.display = "block";
        actualizarPedido.style.display = "block";
        mostrarDatosActualizar(e.target.closest('tr'));
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("iconoEliminar")) {
        contenedorDetalles.style.display = "block";
        contenedorEliminar.style.display = "flex";
        textoNoPedido.textContent = e.target.closest('tr').cells[0].innerText;
    }
});

iconoCerrarLista.addEventListener("click", function() {
    contenedorDetalles.style.display = "none";
    detallesPedido.style.display = "none";
});

iconoCerrarActualizar.addEventListener("click", function() {
    contenedorDetalles.style.display = "none";
    actualizarPedido.style.display = "none";
    location.reload();
});

confirmarEliminacion.addEventListener("click", function() {
    eliminarDatos(textoNoPedido.textContent.trim());
});

cancelarEliminacion.addEventListener("click", function() {
    contenedorDetalles.style.display = "none";
    contenedorEliminar.style.display = "none";
});

function formatearFecha(fecha) {
    if (!fecha) return '';
    const [año, mes, día] = fecha.split('-');
    return `${día}/${mes}/${año}`;
}

function mostrarDetallesPedido(fila) {
   const codigoPedido = fila.cells[0].innerText;

   fetch(`../../php/configuraciones/detallesPedidos.php?codigoPedido=${codigoPedido}`)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            // Datos generales
            const pedido = data.pedido;
            document.getElementById('datoNoPedido').textContent = 'Detalles del Pedido No ' + pedido.No_Pedido
            const datosGenerales = document.getElementById('datosGeneralesBody');
            datosGenerales.innerHTML = "";
            const filaDatosGenerales = document.createElement("tr");
            filaDatosGenerales.innerHTML = `
                <td>${pedido.periodo || ''}</td>
                <td>${formatearFecha(pedido.fechaPedido) || ''}</td>
                <td>${formatearFecha(pedido.fechaEntrega) || ''}</td>
                <td>${pedido.TipoVenta || ''}</td>
                <td>${pedido.canalVenta || ''}</td>
                <td>${pedido.vendedor || ''}</td>
            `;
            datosGenerales.appendChild(filaDatosGenerales);
            //Datos Clientes
            document.getElementById('datoNombre').textContent = pedido.nombreCliente;
            document.getElementById('datoCorreo').textContent = pedido.correoCliente;
            document.getElementById('datoTelefono').textContent = pedido.telefonoCliente;
            document.getElementById('datoPerfil').textContent = pedido.nombreUsuario;
            document.getElementById('datoNit').textContent = pedido.nitCliente;
            document.getElementById('datoDireccion').textContent = (pedido.direccion || '') + ' - ' + (pedido.departamento || '') + ', ' + (pedido.municipio || '');
            //Datos Envio
            document.getElementById('datoIndicaciones').textContent = pedido.indicaciones;
            document.getElementById('datoMensajeria').textContent = pedido.mensajeria;
            document.getElementById('datoNoGuia').textContent = pedido.NoGuia;
            document.getElementById('datoCostoEnvio').textContent = pedido.costoEnvio;
            document.getElementById('datoFormaPago').textContent = pedido.formaPago;

            // Ingresar datos de producto a la tabla
            const datosProducto = document.getElementById('datosProducto');
            let subtotal = 0;
            let filaTotal = document.getElementById('totalPedido');
            datosProducto.innerHTML = "";
            data.datosProductos.forEach(producto => {
                const filaProducto = document.createElement("tr");
                subtotal = Number(producto.precio) * Number(producto.cantidad);
                filaProducto.innerHTML = `
                    <td>${producto.codigoCalzado || ''}</td>
                    <td>${producto.descripcionCalzado || ''}</td>
                    <td>${producto.color || ''}</td>
                    <td>${producto.talla || ''}</td>
                    <td>${producto.cantidad || ''}</td>
                    <td>${producto.precio || ''}</td>
                    <td>${subtotal.toFixed(2)}</td>
                `;
                datosProducto.appendChild(filaProducto);
            });
            filaTotal.innerText = pedido.Total; 
        }
    })
}

function mostrarDatosActualizar(fila) {
    const codigoPedido = fila.cells[0].innerText;
   fetch(`../../php/configuraciones/detallesPedidos.php?codigoPedido=${codigoPedido}`)
   .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            const pedido = data.pedido;
            document.getElementById('tituloPedidoActualizar').textContent = 'Pedido No ' + pedido.No_Pedido;
            // Para los inputs de Pedido
            document.getElementById('datoPeriodo').value = pedido.periodo || '';
            document.getElementById('datoFechaPedido').value = pedido.fechaPedido || '';
            document.getElementById('datoFechaEntrega').value = pedido.fechaEntrega || '';
            document.getElementById('datoTipoVenta').value = pedido.TipoVenta || '';
            document.getElementById('datoCanalVenta').value = pedido.canalVenta || '';
            document.getElementById('datoVendedor').value = pedido.vendedor || '';
            document.getElementById('estadoPedido').value = pedido.estadoPedido || '';

            // Para los inputs de Cliente
            document.getElementById('datoNombreCliente').value = pedido.nombreCliente || '';
            document.getElementById('datoCorreoCliente').value = pedido.correoCliente || '';
            document.getElementById('datoPerfilUsuario').value = pedido.nombreUsuario || '';
            document.getElementById('datoTelefonoCliente').value = pedido.telefonoCliente || '';
            document.getElementById('datoNITCliente').value = pedido.nitCliente || '';  

            // Para los inputs de Envio
            document.getElementById('datoDireccionCompleta').value = pedido.direccion || '';
            document.getElementById('datoDepartamento').value = pedido.departamento || '';
            document.getElementById('datoIndicacion').value = pedido.indicaciones || '';
            document.getElementById('Mensajeria').value = pedido.mensajeria || '';
            document.getElementById('NoGuia').value = pedido.NoGuia || '';
            document.getElementById('CostoEnvio').value = pedido.costoEnvio || '';
            document.getElementById('FormaPago').value = pedido.formaPago || '';

            datosPedidos = { ...pedido};
            totalOriginal = parseFloat(pedido.Total) || 0;
            // Ingresar datos de producto a la tabla
            const datoProducto = document.getElementById('tablaDatosProductos');
            let subtotal = 0;
            let total = 0;
            let filaTotal = document.getElementById('totalPedidoRegistrado');
            datoProducto.innerHTML = "";

            data.datosProductos.forEach(producto => {
                const filaProducto = document.createElement("tr");
                subtotal = Number(producto.precio) * Number(producto.cantidad);
                total += subtotal;
                filaProducto.setAttribute('data-codigoDetallePedido', producto.codigoDetallePedido || '');
                filaProducto.innerHTML = `
                    <td>${producto.codigoCalzado || ''}</td>
                    <td>${producto.descripcionCalzado || ''}</td>
                    <td class="editableColor" contenteditable="true">${producto.color || ''}</td>
                    <td class="editableTalla" contenteditable="true">${producto.talla || ''}</td>
                    <td class="editableCantidad" contenteditable="true">${producto.cantidad || ''}</td>
                    <td class="editablePrecio" contenteditable="true">${producto.precio || ''}</td>
                    <td class="nuevoSubtotal">${subtotal.toFixed(2)}</td>
                `;
                datoProducto.appendChild(filaProducto);
            });
            filaTotal.setAttribute('contenteditable', 'true');
            filaTotal.classList.add('editableTotal');
            filaTotal.innerText = total.toFixed(2);
            datosProductos = data.datosProductos;
            
            // Función para recalcular subtotales y total
            function recalcularTotales() {
                totalPedido = 0;
                document.querySelectorAll('#tablaDatosProductos tr').forEach(tr => {
                    const cantidad = parseFloat(tr.querySelector('.editableCantidad').textContent) || 0;
                    const precio = parseFloat(tr.querySelector('.editablePrecio').textContent) || 0;
                    const subtotal = cantidad * precio;
                    tr.querySelector('.nuevoSubtotal').textContent = subtotal.toFixed(2);
                    totalPedido += subtotal;
                });
                const costoEnvio = parseFloat(pedido.costoEnvio) || 0;
                const totalConEnvio = totalPedido + costoEnvio;
                document.getElementById('totalPedidoRegistrado').textContent = totalConEnvio.toFixed(2);
            }

            // Agrega listeners a cantidad y precio para recalcular en cada cambio
            document.querySelectorAll('.editableCantidad, .editablePrecio').forEach(td => {
                td.addEventListener('input', recalcularTotales);
                td.addEventListener('blur', recalcularTotales);
            });

            // Inicializa el total al cargar
            recalcularTotales();
        }
    })
}

function actualizarDatos() {
    let datosNuevos = {};
    let datosNuevosProductos = [];

    if (document.getElementById('datoPeriodo').value != datosPedidos.periodo) {
        datosNuevos.No_Periodo = document.getElementById('datoPeriodo').value;
        console.log(datosNuevos.No_Periodo);
    }
    if (document.getElementById('datoFechaPedido').value !== datosPedidos.fechaPedido) {
        datosNuevos.fechaPedido = document.getElementById('datoFechaPedido').value;
    }
    if (document.getElementById('datoFechaEntrega').value !== datosPedidos.fechaEntrega) {
        datosNuevos.fechaEntrega = document.getElementById('datoFechaEntrega').value;
    }
    if (document.getElementById('datoTipoVenta').value !== datosPedidos.TipoVenta) {
        datosNuevos.TipoVenta = document.getElementById('datoTipoVenta').value;
    }
    if (document.getElementById('datoCanalVenta').value !== datosPedidos.canalVenta) {
        datosNuevos.canalVenta = document.getElementById('datoCanalVenta').value;
    }
    if (document.getElementById('datoVendedor').value !== datosPedidos.vendedor) {
        datosNuevos.vendedor = document.getElementById('datoVendedor').value;
    }
    if (document.getElementById('estadoPedido').value !== datosPedidos.estadoPedido) {
        datosNuevos.estadoPedido = document.getElementById('estadoPedido').value;
    }

    // Cliente
    // Si ha cambiado el nombre o NIT del cliente, se trata de un nuevo cliente
    if (document.getElementById('datoNombreCliente').value !== datosPedidos.nombreCliente 
        && document.getElementById('datoNITCliente').value !== datosPedidos.nitCliente) {
            datosNuevos.nombreCliente = document.getElementById('datoNombreCliente').value;
            datosNuevos.nitCliente = document.getElementById('datoNITCliente').value;
            datosNuevos.correoCliente = document.getElementById('datoCorreoCliente').value;
            datosNuevos.nombreUsuario = document.getElementById('datoPerfilUsuario').value;
            datosNuevos.telefonoCliente = document.getElementById('datoTelefonoCliente').value;
            datosNuevos.canalVenta = document.getElementById('datoCanalVenta').value;
    } 
    else {
        if (document.getElementById('datoNombreCliente').value !== datosPedidos.nombreCliente) {
        datosNuevos.nombreCliente = document.getElementById('datoNombreCliente').value;
        }
        if (document.getElementById('datoCorreoCliente').value !== datosPedidos.correoCliente) {
            datosNuevos.correoCliente = document.getElementById('datoCorreoCliente').value;
        }
        if (document.getElementById('datoPerfilUsuario').value !== datosPedidos.nombreUsuario) {
            datosNuevos.nombreUsuario = document.getElementById('datoPerfilUsuario').value;
        }
        if (document.getElementById('datoTelefonoCliente').value !== datosPedidos.telefonoCliente) {
            datosNuevos.telefonoCliente = document.getElementById('datoTelefonoCliente').value;
        }
        if (document.getElementById('datoNITCliente').value !== datosPedidos.nitCliente) {
            datosNuevos.nitCliente = document.getElementById('datoNITCliente').value;
        }  
    }
 
    
    // Envío
    if (document.getElementById('datoDireccionCompleta').value !== datosPedidos.direccion) {
        datosNuevos.direccion = document.getElementById('datoDireccionCompleta').value;
    }
    if (document.getElementById('datoDepartamento').value !== datosPedidos.departamento) {
        datosNuevos.departamento = document.getElementById('datoDepartamento').value;
    }

    if (document.getElementById('datoIndicacion').value !== datosPedidos.indicaciones) {
        datosNuevos.indicaciones = document.getElementById('datoIndicacion').value;
    }
    if (document.getElementById('Mensajeria').value !== datosPedidos.mensajeria) {
        datosNuevos.mensajeria = document.getElementById('Mensajeria').value;
    }
    if (document.getElementById('NoGuia').value !== datosPedidos.NoGuia) {
        datosNuevos.NoGuia = document.getElementById('NoGuia').value;
    }
    if (document.getElementById('CostoEnvio').value !== datosPedidos.costoEnvio) {
        datosNuevos.costoEnvio = document.getElementById('CostoEnvio').value;
    }
    if (document.getElementById('FormaPago').value !== datosPedidos.formaPago) {
        datosNuevos.formaPago = document.getElementById('FormaPago').value;
    }

    // Productos
    document.querySelectorAll('#tablaDatosProductos tr').forEach((tr, i) => {
        const codigoCalzado = tr.children[0].textContent.trim();
        const codigoDetallePedido = tr.getAttribute('data-codigoDetallePedido');
        // Obtén los valores actuales editados
        const color = tr.querySelector('.editableColor').textContent.trim();
        const talla = tr.querySelector('.editableTalla').textContent.trim();
        const cantidad = tr.querySelector('.editableCantidad').textContent.trim();
        const precio = tr.querySelector('.editablePrecio').textContent.trim();
        const totalFila = tr.querySelector('.nuevoSubtotal').textContent.trim();


        // Busca el producto original por código
        const original = datosProductos.find(p => String(p.codigoDetallePedido) === String(codigoDetallePedido));

        // Si el producto existe y alguno de los campos cambió, almacena el cambio
        if (original) {
            let cambios = {
                codigoDetallePedido,
                codigoCalzado
            };

            if (color !== String(original.color)) cambios.color = color;
            if (talla !== String(original.talla)) cambios.talla = talla;
            if (cantidad !== String(original.cantidad)) cambios.cantidad = cantidad;
            if (precio !== String(original.precio)) cambios.precio = precio;

            // Solo agrega si hay algún cambio (además de los códigos)
            if (Object.keys(cambios).length > 2) {
                datosNuevosProductos.push(cambios);
            }
        }
    });

    if (parseFloat(document.getElementById('totalPedidoRegistrado').textContent) !== parseFloat(datosPedidos.Total)) {
        datosNuevos.Total = document.getElementById('totalPedidoRegistrado').textContent;
    }

    // Ahora puedes enviar datosNuevos y productosEditados al backend si hay cambios
    if (Object.keys(datosNuevos).length > 0 || datosNuevosProductos.length > 0) {
        // Aquí tu fetch o AJAX para enviar los cambios
        console.log('Datos modificados:', datosNuevos);
        console.log('Productos modificados:', datosNuevosProductos);
        fetch('../../php/configuraciones/actualizarDatos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                No_Pedido: datosPedidos.No_Pedido,
                pedidoNuevo: datosNuevos,
                nuevoDatoProductos: datosNuevosProductos
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado == 1) {
                mostrarToast(data.mensaje, "success");
            }
            else {
                mostrarToast(data.mensaje, "error");
            }
        });
    } else {
        mostrarToast("No hay cambios para actualizar.", "info");
    }
}

function eliminarDatos(codigoPedido) {
    fetch(`../../php/configuraciones/eliminarDatos.php?codigoPedido=${codigoPedido}`)
    .then(response => response.json())
    .then(data => {
        if (data.estado) {
            contenedorDetalles.style.display = "none";
            contenedorEliminar.style.display = "none";
            mostrarToast(data.mensaje, "success");
            setTimeout(() => {
                location.reload();
            }, 3000); // Espera 3.5 segundos antes de recargar
        } else {
            mostrarToast(data.mensaje, "error");
        }
    });
}

btnActualizarDatos.addEventListener("click", function() {
    actualizarDatos();
});


function showToast(message) {
    var toast = document.getElementById("toast");
    toast.textContent = message;  // Asigna el mensaje dinámico
    toast.className = "show";     // Añade la clase para mostrar el toast
    setTimeout(function() {
        toast.className = toast.className.replace("show", "");  // Elimina la clase para ocultar el toast después de 3 segundos
    }, 3000);
}