const formularioFiltroCompras = document.getElementById("formularioFiltroCompras");
const filtrosHistorialCompra = document.querySelector(".filtrosHistorialCompra");
const modalCompra = document.getElementById("modalCompra");
const formularioCompra = document.getElementById("formRegistroCompras");
const btnNuevaCompra = document.getElementById("btnNuevaCompra");
const btnCerrarModalCompra = document.getElementById("btnCerrarModalCompra");
const tbodyHistorialCompras = document.getElementById("historialCompras");

let currentPage = 1;
let totalPages = 1;

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "show";
    window.setTimeout(() => {
        toast.className = toast.className.replace("show", "");
    }, 3000);
}

function mostrarModalCompra() {
    formularioCompra.reset();
    document.getElementById("tituloModalCompra").textContent = "Registrar Compra de Materiales";
    document.querySelector("#formRegistroCompras .btnRegistrarCompra").value = "Añadir Registro de Compra";
    modalCompra.hidden = false;
    document.getElementById("descripcionMaterial").focus();
}

function cerrarModalCompra() {
    modalCompra.hidden = true;
    formularioCompra.reset();
    document.getElementById("tituloModalCompra").textContent = "Registrar Compra de Materiales";
    document.querySelector("#formRegistroCompras .btnRegistrarCompra").value = "Añadir Registro de Compra";
    btnNuevaCompra.focus();
}

function editarCompra(compra) {
    formularioCompra.reset();
    formularioCompra.elements.idCompra.value = compra.id;
    document.getElementById("descripcionMaterial").value = compra.descripcion;
    document.getElementById("cantidadMaterial").value = compra.cantidad;
    document.getElementById("precioMaterial").value = compra.precio;
    document.getElementById("comprador").value = compra.comprador;
    document.getElementById("proveedorMaterial").value = compra.proveedor;
    document.getElementById("fechaCompra").value = compra.fecha;
    document.getElementById("noComprobante").value = compra.comprobante;
    document.getElementById("tituloModalCompra").textContent = "Actualizar Compra de Materiales";
    document.querySelector("#formRegistroCompras .btnRegistrarCompra").value = "Guardar Cambios";
    modalCompra.hidden = false;
    document.getElementById("descripcionMaterial").focus();
}

function crearAccionCompra(titulo, iconoNombre, clase, idCompra) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.className = `btn-action-icon ${clase}`;
    boton.title = titulo;
    boton.setAttribute("aria-label", titulo);
    boton.dataset.id = idCompra;

    const icono = document.createElement("ion-icon");
    icono.name = iconoNombre;
    boton.appendChild(icono);
    return boton;
}

function formatearFechaCompra(fecha) {
    const partes = String(fecha ?? "").match(/^(\d{4})-(\d{2})-(\d{2})$/);
    return partes ? `${partes[3]}/${partes[2]}/${partes[1]}` : fecha ?? "";
}

function mostrarFilasCompras(compras) {
    tbodyHistorialCompras.replaceChildren();

    if (compras.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = 8;
        celda.className = "mensajeTablaCompras";
        celda.textContent = "No se encontraron compras con esos filtros.";
        fila.appendChild(celda);
        tbodyHistorialCompras.appendChild(fila);
        return;
    }

    compras.forEach(compra => {
        const fila = document.createElement("tr");
        fila.dataset.id = compra.idCompra;
        fila.dataset.fecha = compra.FechaCompra ?? "";
        fila.dataset.descripcion = compra.descripcionMaterial ?? "";
        fila.dataset.cantidad = compra.cantidad ?? "";
        fila.dataset.precio = compra.precio ?? "";
        fila.dataset.comprador = compra.comprador ?? "";
        fila.dataset.proveedor = compra.proveedor ?? "";
        fila.dataset.comprobante = compra.NO_Comprobante ?? "";

        [
            formatearFechaCompra(compra.FechaCompra),
            compra.descripcionMaterial,
            compra.cantidad,
            `Q ${Number(compra.precio).toFixed(2)}`,
            compra.proveedor,
            compra.NO_Comprobante,
            compra.comprador
        ].forEach(valor => {
            const celda = document.createElement("td");
            celda.textContent = valor ?? "";
            fila.appendChild(celda);
        });

        const acciones = document.createElement("td");
        acciones.className = "accionesTablaCompras";
        acciones.appendChild(crearAccionCompra("Editar compra", "create-outline", "btnEditarCompra", compra.idCompra));
        acciones.appendChild(crearAccionCompra("Eliminar compra", "trash-outline", "btnEliminarCompra", compra.idCompra));
        fila.appendChild(acciones);
        tbodyHistorialCompras.appendChild(fila);
    });
}

async function cargarHistorialCompras() {
    const parametros = new URLSearchParams({
        descripcion: document.getElementById("descripcionCompra").value.trim(),
        fecha: document.getElementById("filtroFechaCompra").value,
        proveedor: document.getElementById("proveedorCompra").value.trim(),
        comprador: document.getElementById("compradorCompra").value.trim(),
        page: currentPage
    });

    try {
        const respuesta = await fetch(`../configuraciones/consultarDatos/historialCompras.php?${parametros}`);
        const resultado = await respuesta.json();
        if (!respuesta.ok || !Array.isArray(resultado.data)) {
            throw new Error(resultado.error || "No fue posible cargar el historial de compras.");
        }

        totalPages = Math.max(1, Number(resultado.totalPages) || 1);
        if (currentPage > totalPages) {
            currentPage = totalPages;
            return cargarHistorialCompras();
        }
        mostrarFilasCompras(resultado.data);
        document.getElementById("contadorPaginas").textContent = `Página ${currentPage} de ${totalPages}`;
        document.getElementById("paginacionAnterior").disabled = currentPage <= 1;
        document.getElementById("paginacionSiguiente").disabled = currentPage >= totalPages;
    } catch (error) {
        console.error("Error al cargar el historial de compras:", error);
        tbodyHistorialCompras.replaceChildren();
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = 8;
        celda.className = "mensajeTablaCompras";
        celda.textContent = "Ocurrió un error al cargar el historial de compras.";
        fila.appendChild(celda);
        tbodyHistorialCompras.appendChild(fila);
    }
}

btnNuevaCompra.addEventListener("click", mostrarModalCompra);
btnCerrarModalCompra.addEventListener("click", cerrarModalCompra);

tbodyHistorialCompras.addEventListener("click", async event => {
    const boton = event.target.closest("button");
    if (!boton || !tbodyHistorialCompras.contains(boton)) {
        return;
    }

    const fila = boton.closest("tr");
    if (boton.classList.contains("btnEditarCompra")) {
        editarCompra({
            id: fila.dataset.id,
            fecha: fila.dataset.fecha,
            descripcion: fila.dataset.descripcion,
            cantidad: fila.dataset.cantidad,
            precio: fila.dataset.precio,
            comprador: fila.dataset.comprador,
            proveedor: fila.dataset.proveedor,
            comprobante: fila.dataset.comprobante
        });
        return;
    }

    if (!boton.classList.contains("btnEliminarCompra")) {
        return;
    }

    if (!window.confirm("¿Seguro que deseas eliminar esta compra? Se descontará su cantidad del inventario.")) {
        return;
    }

    boton.disabled = true;
    const datos = new FormData();
    datos.set("idCompra", fila.dataset.id);

    try {
        const respuesta = await fetch("../configuraciones/eliminarDatos/eliminarCompraMateriales.php", {
            method: "POST",
            body: datos
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.estado) {
            throw new Error(resultado.mensaje || "No fue posible eliminar la compra.");
        }

        await cargarHistorialCompras();
        showToast(resultado.mensaje);
    } catch (error) {
        console.error("Error al eliminar la compra:", error);
        showToast(error.message || "Ocurrió un error al eliminar la compra.");
        boton.disabled = false;
    }
});

formularioCompra.addEventListener("submit", async event => {
    if (!formularioCompra.elements.idCompra.value) {
        return;
    }

    event.preventDefault();
    const botonEnviar = formularioCompra.querySelector(".btnRegistrarCompra");
    botonEnviar.disabled = true;

    try {
        const respuesta = await fetch("../configuraciones/actualizarDatos/actualizarCompraMateriales.php", {
            method: "POST",
            body: new FormData(formularioCompra)
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.estado) {
            throw new Error(resultado.mensaje || "No fue posible actualizar la compra.");
        }

        cerrarModalCompra();
        await cargarHistorialCompras();
        showToast(resultado.mensaje);
    } catch (error) {
        console.error("Error al actualizar la compra:", error);
        showToast(error.message || "Ocurrió un error al actualizar la compra.");
    } finally {
        botonEnviar.disabled = false;
    }
});

modalCompra.addEventListener("click", event => {
    if (event.target === modalCompra) {
        cerrarModalCompra();
    }
});

document.addEventListener("keydown", event => {
    if (event.key === "Escape" && !modalCompra.hidden) {
        cerrarModalCompra();
    }
});

document.getElementById("mostrarFiltrosCompras").addEventListener("click", () => {
    filtrosHistorialCompra.classList.toggle("visible");
});

formularioFiltroCompras.addEventListener("submit", event => {
    event.preventDefault();
    currentPage = 1;
    cargarHistorialCompras();
});

document.getElementById("paginacionAnterior").addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        cargarHistorialCompras();
    }
});

document.getElementById("paginacionSiguiente").addEventListener("click", () => {
    if (currentPage < totalPages) {
        currentPage++;
        cargarHistorialCompras();
    }
});

window.addEventListener("DOMContentLoaded", () => {
    cargarHistorialCompras();

    const estadoRegistro = new URLSearchParams(window.location.search).get("status");
    if (estadoRegistro === "success") {
        showToast("La compra se registró exitosamente.");
    } else if (estadoRegistro === "error") {
        showToast("Hubo un error al registrar la compra.");
    }
});
