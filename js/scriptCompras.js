const btnCompras = document.getElementById("btnCompra");
const btnHistorial = document.getElementById("btnHistorial");
const titulo =  document.querySelector(".titulo.RegistroCompras");
const btnFiltroCompras =  document.getElementById("btnFiltroCompras");
const btnMostrarFiltrosCompras = document.getElementById("mostrarFiltrosCompras");
const filtrosHistorialCompra = document.querySelector(".filtrosHistorialCompra");

if (btnMostrarFiltrosCompras && filtrosHistorialCompra) {
    btnMostrarFiltrosCompras.addEventListener("click", function() {
        if (filtrosHistorialCompra.style.display === "grid") {
            filtrosHistorialCompra.style.display = "none";
        } else {
            filtrosHistorialCompra.style.display = "grid";
        }
    });
}

let currentPage = 1;  // Página actual
let totalPages = 1;   // Total de páginas

btnFiltroCompras.addEventListener("click", function(){
    const fecha = document.getElementById("filtroFechaCompra").value;
    const descripcion = document.getElementById("descripcionCompra").value;
    console.log(fecha);
    console.log(descripcion);
})

btnHistorial.addEventListener("click", function() {
    titulo.textContent = 'Historial de Compras de Materiales';
    document.querySelector(".formularioCompras").style.display = "none";
    document.querySelector(".contenedorTabla").style.display = "block";
    btnHistorial.style.backgroundColor = "#2C3E50";
    btnHistorial.style.color = "#F8F7F7";
    btnCompras.style.backgroundColor = "transparent";
    btnCompras.style.color = "#000000";
    btnCompras.style.border = "1px solid #000000";

    cargarHistorialCompras(); 
});


btnCompras.addEventListener("click", function() {
    titulo.textContent = 'Registro de Compra de Materiales';
    document.querySelector(".formularioCompras").style.display = "grid";
    document.querySelector(".contenedorTabla").style.display = "none";   
    btnCompras.style.backgroundColor = "#2C3E50";
    btnCompras.style.color = "#F8F7F7";
    btnHistorial.style.border = "1px solid #000000";
    btnHistorial.style.backgroundColor = "transparent";
    btnHistorial.style.color = "#000000";
});

/* ------------------------------- Notificación Toast ------------------------------- */
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        if (urlParams.get('status') === 'success') {
            showToast("Se ha registrado exitosamente");
        } else if (urlParams.get('status') === 'error') {
            showToast("Hubo un error al insertar el producto.");
        }
    }
};

function showToast(message) {
    var toast = document.getElementById("toast");
    toast.textContent = message;  // Asigna el mensaje dinámico
    toast.className = "show";     // Añade la clase para mostrar el toast
    setTimeout(function() {
        toast.className = toast.className.replace("show", "");  // Elimina la clase para ocultar el toast después de 3 segundos
    }, 3000);
}


// Función para cargar el historial de compras con paginación
function cargarHistorialCompras() {
    const descripcion = document.getElementById("descripcionCompra").value;
    const fecha = document.getElementById("filtroFechaCompra").value;
    const proveedor = document.getElementById("proveedorCompra").value;
    const comprador = document.getElementById("compradorCompra").value;

    const params = new URLSearchParams({
        descripcion,
        fecha,
        proveedor,
        comprador,
        page: currentPage  // Pasar la página actual al backend
    });

    fetch(`../configuraciones/historialCompras.php?${params}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("historialCompras");
            tbody.innerHTML = "";

            if (data.data.length > 0) {
                data.data.forEach(row => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${row.FechaCompra}</td>
                        <td>${row.descripcionMaterial}</td>
                        <td>${row.cantidad}</td>
                        <td>Q${parseFloat(row.precio).toFixed(2)}</td>
                        <td>${row.comprador}</td>
                        <td>${row.proveedor}</td>
                        <td>${row.NO_Comprobante}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td colspan="7">No se encontraron resultados</td>`;
                tbody.appendChild(tr);
            }

            // Actualizar paginación
            totalPages = data.totalPages;
            document.getElementById("contadorPaginas").innerHTML = `Página ${currentPage} de ${totalPages}`;

            // Habilitar o deshabilitar los botones de paginación
            document.getElementById("paginacionAnterior").disabled = currentPage === 1;
            document.getElementById("paginacionSiguiente").disabled = currentPage === totalPages;
        })
        .catch(error => {
            console.error("Error al cargar el historial:", error);
        });
}

// Llamar cuando se aplique filtro
document.querySelector(".filtrosHistorialCompra").addEventListener("submit", function (e) {
    e.preventDefault(); // Prevenir envío
    currentPage = 1; // Resetear a la primera página cuando se apliquen los filtros
    cargarHistorialCompras(); // Cargar con filtros
});

// Función para manejar la paginación
function changePage(direction) {
    if (direction === 'previous' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    }
    cargarHistorialCompras(); // Cargar la página actual con los datos
}

// Eventos para los botones de paginación
document.getElementById("paginacionAnterior").addEventListener("click", function () {
    changePage('previous');
});

document.getElementById("paginacionSiguiente").addEventListener("click", function () {
    changePage('next');
});
