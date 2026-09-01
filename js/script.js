//Objeto para almacenar los datos de los formularios
let formulario = {};
let arregloProductos = [];
let producto = {};
var precioTotal = 0;

const btnListaProductos = document.getElementById("btnListaProductos");
const btnCerrarLista = document.getElementById("btnCerrarLista");
const contenedorListaProductos = document.querySelector(".contenedorListaProductos");
const contenedorFondo = document.querySelector(".fondo");
const btnEditar = document.getElementById("btnEditar");
const btnGuardarCambios = document.getElementById("btnGuardarCambios");
const formularioProductos = document.querySelector(".formularioDatosProducto");

function guardarDatosFormulario1() {
  //Obtener Datos del formulario de pedido
  formulario.noPeriodo = document.getElementById("noPeriodo").value;
  formulario.fechaRegistro = document.getElementById("fechaRegistro").value;
  formulario.fechaEntrega = document.getElementById("fechaEntrega").value;
  formulario.tipoVenta = document.getElementById("tipoVenta").value;
  formulario.canalVenta = document.getElementById("canalVenta").value;
  formulario.vendedor = document.getElementById("vendedor").value;

  console.log(formulario);
}

function guardarDatosProducto() {
  //Obtener Datos del formulario de producto
  let selectedOption = document.getElementById("codigoProducto").selectedOptions[0];
  let descripcion = selectedOption.getAttribute("data-descripcion") || "";
  producto = {
    codigoProducto: document.getElementById("codigoProducto").value,
    descripcion: descripcion,
    color: document.getElementById("color").value,
    talla: document.getElementById("talla").value,
    cantidad: document.getElementById("cantidad").value,
    precio: document.getElementById("precio").value,
  };
  arregloProductos.push(producto);
  console.log(arregloProductos);
}

function guardarDatosFormulario2() {
  //Obtener datos del formulario de cliente
  formulario.nombreCliente = document.getElementById("nombreCliente").value;
  formulario.correoCliente = document.getElementById("correoCliente").value;
  formulario.telefonoCliente = document.getElementById("telefonoCliente").value;
  formulario.perfilCliente = document.getElementById("perfilCliente").value;
  formulario.nitCliente = document.getElementById("nitCliente").value;

  //Obtener datos del formulario de envío
  formulario.direccion = document.getElementById("direccion").value;
  formulario.departamento = document.getElementById("departamento").value;
  formulario.indicaciones = document.getElementById("indicaciones").value;
  formulario.mensajeria = document.getElementById("mensajeria").value;
  formulario.noGuia = document.getElementById("noGuia").value;
  formulario.costoEnvio = document.getElementById("costoEnvio").value;
  formulario.formaPago = document.getElementById("formaPago").value;
}

function tablaPedidos() {
  //Crear tabla para mostrar los pedidos
  let tabla = document.getElementById("resumenDatosPedidos");
  let tbody = document.getElementById("datosPedido");
  tbody.innerHTML = ""; // Limpiar el contenido previo

  let tr = document.createElement("tr");
  tr.innerHTML = `
        <td>${formulario.noPeriodo}</td>
        <td>${formulario.tipoVenta}</td>
        <td>${formulario.canalVenta}</td>
        <td>${formulario.vendedor}</td>
        <td>${formulario.fechaRegistro}</td>
        <td>${formulario.fechaEntrega}</td>
    `;
  tbody.appendChild(tr);
}

function tablaProductos() {
  precioTotal = 0;
  //Crear tabla para mostrar los productos
  let tabla = document.getElementById("resumenDatosProducto");
  let tbody = document.getElementById("datosProducto");
  let filaTotal = document.getElementById("totalPedido");
  let subtotal = 0;

  tbody.innerHTML = ""; // Limpiar el contenido previo
  filaTotal.innerText = "";

  arregloProductos.forEach((producto, index) => {
    let tr = document.createElement("tr");
    subtotal = Number(producto.precio) * Number(producto.cantidad);
    precioTotal += subtotal;
    tr.innerHTML = `
            <td>${producto.codigoProducto}</td>
            <td>${producto.descripcion}</td>
            <td>${producto.color}</td>
            <td>${producto.talla}</td>
            <td>${producto.cantidad}</td>
            <td>${producto.precio}</td>
            <td>${subtotal.toFixed(2)}</td> 
        `;
    tbody.appendChild(tr);
  });
  precioTotal = precioTotal + Number(formulario.costoEnvio);
  filaTotal.innerText = precioTotal.toFixed(2);
  console.log(precioTotal);
}

function actualizarTablaCliente() {
  // Actualizar las celdas de la tabla con los datos del formulario
  document.querySelector("#resumenDatosCliente td:nth-child(2)").textContent =
    formulario.nombreCliente;
  document.querySelector("#resumenDatosCliente td:nth-child(4)").textContent =
    formulario.correoCliente;

  document.querySelector(
    "#resumenDatosCliente tr:nth-child(2) td:nth-child(2)",
  ).textContent = formulario.telefonoCliente;
  document.querySelector(
    "#resumenDatosCliente tr:nth-child(2) td:nth-child(4)",
  ).textContent = formulario.perfilCliente;

  document.querySelector(
    "#resumenDatosCliente tr:nth-child(3) td:nth-child(2)",
  ).textContent = `${formulario.direccion} - ${formulario.departamento}`;
  document.querySelector(
    "#resumenDatosCliente tr:nth-child(3) td:nth-child(4)",
  ).textContent = formulario.indicaciones;

  document.querySelector(
    "#resumenDatosCliente tr:nth-child(4) td:nth-child(2)",
  ).textContent = formulario.nitCliente;
  document.querySelector(
    "#resumenDatosCliente tr:nth-child(4) td:nth-child(4)",
  ).textContent = `${formulario.mensajeria}, No.Guia ${formulario.noGuia}`;

  document.querySelector(
    "#resumenDatosCliente tr:nth-child(5) td:nth-child(2)",
  ).textContent = formulario.costoEnvio;
  document.querySelector(
    "#resumenDatosCliente tr:nth-child(5) td:nth-child(4)",
  ).textContent = formulario.formaPago;
}

//Acción para guardar los datos del formulario de producto
document.getElementById("btnAgregarProducto").addEventListener("click", function () {
  if (!validarFormularioVisible(formularioProductos)) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Debe completar todos los campos requeridos',
    });
    return;
  }
  guardarDatosProducto();
  document.getElementById("cantidadProductos").textContent =
    arregloProductos.length;
  document.getElementById("codigoProducto").value = "";
  document.getElementById("descripcionProducto").textContent =
    "Selecciona un código";
  document.getElementById("color").value = "";
  document.getElementById("talla").value = "";
  document.getElementById("cantidad").value = "";
  document.getElementById("precio").value = "";
});

function validarFormularioVisible(formulario) {
  const camposRequeridos = formulario.querySelectorAll("[required]");
  let formularioValido = true;

  // Verificar cada campo requerido
  camposRequeridos.forEach((campo) => {
    if (campo.type !== "hidden" && !campo.value.trim()) {
      // Verifica solo campos visibles
      formularioValido = false;
      campo.style.borderColor = "red"; // Resalta los campos inválidos
    } else {
      campo.style.borderColor = ""; // Limpia el borde si es válido
    }
  });

  return formularioValido;
}

function listadoProductos() {
  const tbody = document.getElementById("datosLista");
  tbody.innerHTML = "";
  arregloProductos.forEach((producto, index) => {
    let tr = document.createElement("tr");
    tr.innerHTML = `
            <td>${producto.codigoProducto}</td>
            <td>${producto.descripcion}</td>
            <td>${producto.color}</td>
            <td>${producto.talla}</td>
            <td>${producto.cantidad}</td>
            <td>${producto.precio}</td>
            <td><ion-icon name="trash-outline" id="eliminar" onclick="eliminarProducto(this.closest('tr'), 1)" ></ion-icon></td>            
        `;
    tbody.appendChild(tr);
  });
}

btnListaProductos.addEventListener("click", function () {
  contenedorFondo.style.display = "block";
  contenedorListaProductos.style.display = "block";
  listadoProductos();
});

btnCerrarLista.addEventListener("click", function () {
  contenedorFondo.style.display = "none";
  contenedorListaProductos.style.display = "none";
});

btnEditar.addEventListener("click", function () {
  const filas = document.querySelectorAll("#datosLista tr");
  filas.forEach((fila) => {
    // Solo las columnas color, talla, cantidad y precio (índices 2, 3, 4, 5)
    [2, 3, 4, 5].forEach((i) => {
      if (fila.cells[i]) {
        fila.cells[i].setAttribute("contenteditable", "true");
        fila.cells[i].style.backgroundColor = "#fffbe6"; // Opcional: resalta la celda editable
      }
    });
  });
  btnGuardarCambios.style.backgroundColor = "#FA660A";
  btnGuardarCambios.style.color = "#F8F7F7";
  document.querySelector(".botonesEdicion.uno").classList.add("activo");
});

btnGuardarCambios.addEventListener("click", function () {
  const filas = document.querySelectorAll("#datosLista tr");
  filas.forEach((fila) => {
    [2, 3, 4, 5].forEach((i) => {
      if (fila.cells[i]) {
        fila.cells[i].removeAttribute("contenteditable");
        fila.cells[i].style.backgroundColor = "";
      }
    });
  });

  arregloProductos = []; // Limpiar el arreglo para evitar duplicados
  const tbody = document.getElementById("datosLista");
  filas.forEach((fila) => {
    let producto = {
      codigoProducto: fila.cells[0].textContent,
      descripcion: fila.cells[1].textContent,
      color: fila.cells[2].textContent,
      talla: fila.cells[3].textContent,
      cantidad: fila.cells[4].textContent,
      precio: fila.cells[5].textContent,
    };
    arregloProductos.push(producto);
  });
  console.log(arregloProductos);
});

/* ------------------------  Acción para mostrar los formularios de datos de cliente y de envío-----------------*/
document.getElementById("btnSiguiente").addEventListener("click", function () {
  // Validar los formularios visibles
  const formularioPedido = document.querySelector(".formularioDatosPedido");

  // Validación paso por paso
  if (!validarFormularioVisible(formularioPedido)) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Por favor complete todos los campos requeridos en el formulario de pedido',
    });
    return; // Detener si algún formulario es inválido
  }

  if(arregloProductos.length === 0){
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Por favor agregue al menos un producto',
    });
    return; // Detener si algún formulario es inválido
  }

  guardarDatosFormulario1();
  document.querySelector(".formularioDatosPedido").style.display = "none";
  document.querySelector(".formularioDatosProducto").style.display = "none";
  document.querySelector(".formularioDatosCliente").style.display = "block";
  document.querySelector(".formularioDatosEnvio").style.display = "block";
  document.querySelector(".btnFormulario.uno").style.display = "none";
  document.querySelector(".botones").style.display = "flex";
  window.scrollTo({ top: 0, behavior: "smooth" });
});

/*------------------------ Acción para regresar a los formularios de pedido y producto------------------------------*/
document.getElementById("btnAnterior").addEventListener("click", function () {
  document.querySelector(".formularioDatosPedido").style.display = "block";
  document.querySelector(".formularioDatosProducto").style.display = "block";
  document.querySelector(".formularioDatosCliente").style.display = "none";
  document.querySelector(".formularioDatosEnvio").style.display = "none";
  document.querySelector(".btnFormulario.uno").style.display = "flex";
  document.querySelector(".botones").style.display = "none";
  window.scrollTo({ top: 0, behavior: "smooth" });
});

/*------------------------ Acción para mostrar el resumen del pedido ---------------------------------------------*/
document
  .getElementById("btnSiguienteDos")
  .addEventListener("click", function () {
    // Validar los formularios visibles
    const formularioCliente = document.querySelector(".formularioDatosCliente");
    const formularioEnvio = document.querySelector(".formularioDatosEnvio");

    // Validación paso por paso
    if (!validarFormularioVisible(formularioCliente) || !validarFormularioVisible(formularioEnvio)) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete todos los campos de los formularios',
      });
      return; // Detener si algún formulario es inválido
    }

    guardarDatosFormulario2();
    document.querySelector(".formularioDatosCliente").style.display = "none";
    document.querySelector(".formularioDatosEnvio").style.display = "none";
    document.querySelector(".tablaResumen").style.display = "block";
    document.querySelector(".botones.dos").style.display = "flex";
    document.querySelector(".botones").style.display = "none";
    tablaPedidos();
    tablaProductos();
    actualizarTablaCliente();
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

/*----------------------------  Acción para regresar a los formularios de cliente y envío --------------------------*/
document
  .getElementById("btnAnteriorDos")
  .addEventListener("click", function () {
    document.querySelector(".formularioDatosCliente").style.display = "block";
    document.querySelector(".formularioDatosEnvio").style.display = "block";
    document.querySelector(".tablaResumen").style.display = "none";
    document.querySelector(".botones.dos").style.display = "none";
    document.querySelector(".botones").style.display = "flex";
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

/* --------------------  Actualizar la descripción del producto al seleccionar un código de producto --------------------*/
document
  .getElementById("codigoProducto")
  .addEventListener("change", function () {
    var selectedOption = this.options[this.selectedIndex];
    var descripcion = selectedOption.getAttribute("data-descripcion");
    var descripcionCalzado = document.getElementById("descripcionProducto");

    if (descripcion) {
      descripcionCalzado.textContent = descripcion;
    } else {
      descripcionCalzado.textContent = "Selecciona un código"; // Limpiar si no hay descripción
    }
  });

/* ----------------------------------------- Función para enviar datos ------------------------------------ --------------*/
document
  .getElementById("btnEnviarPedido")
  .addEventListener("click", function () {
    // Datos a enviar
    const datos = {
      formulario: formulario,
      productos: arregloProductos,
      total: precioTotal,
    };

    // Enviar los datos al servidor
    fetch("../configuraciones/ingresoPedidos.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(datos),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.estado) {
          mostrarToast(data.mensaje, "success");
          setTimeout(() => {
              location.reload();
          }, 3500);
        } else {
          mostrarToast("Error al ingresar el pedido", "error");
        }
      });
  });

// Función para eliminar el producto
function eliminarProducto(fila, num) {
  // Obtener el código del producto de la primera celda de la fila
  const codigo = fila.cells[0].textContent;
  // Eliminar el producto del arreglo usando el código
  arregloProductos = arregloProductos.filter(
    (producto) => producto.codigoProducto !== codigo,
  );
  // Volver a renderizar la tabla para reflejar el cambio
  listadoProductos();
  document.getElementById("cantidadProductos").textContent =
    arregloProductos.length;
}


