const btnPedidos = document.querySelector(".btnPedidos");
const btnDatosPedido = document.getElementById("datosPedidos");
const btnDatosCliente = document.getElementById("datosCliente");
const btnDatosEnvio = document.getElementById("datosEnvio");
const btnDatosProductos = document.getElementById("datosProductos");

btnDatosPedido.addEventListener("click", () => {
    document.querySelector(".contenedorPrincipal.Pedido").style.display = "grid";
    document.querySelector(".contenedorPrincipal.Cliente").style.display = "none";
    document.querySelector(".contenedorPrincipal.Envio").style.display = "none";
    document.querySelector(".contenedorPrincipal.Productos").style.display = "none";
    btnDatosPedido.style.backgroundColor = "#2C3E50";
    btnDatosPedido.style.color = '#F8F7F7'; 
    btnDatosEnvio.style.backgroundColor = "transparent";
    btnDatosEnvio.style.color = '#000000';
    btnDatosEnvio.style.border = '1px solid #000000';
    btnDatosCliente.style.backgroundColor = "transparent";
    btnDatosCliente.style.color = '#000000';
    btnDatosCliente.style.border = '1px solid #000000';
    btnDatosProductos.style.backgroundColor = "transparent";
    btnDatosProductos.style.color = '#000000';
    btnDatosProductos.style.border = '1px solid #000000';
});

btnDatosCliente.addEventListener("click", () => {
    document.querySelector(".contenedorPrincipal.Pedido").style.display = "none";
    document.querySelector(".contenedorPrincipal.Cliente").style.display = "grid";
    document.querySelector(".contenedorPrincipal.Envio").style.display = "none";
    document.querySelector(".contenedorPrincipal.Productos").style.display = "none";
    btnDatosCliente.style.backgroundColor = "#2C3E50";
    btnDatosCliente.style.color = '#F8F7F7';
    btnDatosPedido.style.backgroundColor = "transparent";
    btnDatosPedido.style.color = '#000000';
    btnDatosPedido.style.border = '1px solid #000000';
    btnDatosEnvio.style.backgroundColor = "transparent";
    btnDatosEnvio.style.color = '#000000';
    btnDatosEnvio.style.border = '1px solid #000000';    
    btnDatosProductos.style.backgroundColor = "transparent";
    btnDatosProductos.style.color = '#000000'; 
    btnDatosProductos.style.border = '1px solid #000000';   
});

btnDatosEnvio.addEventListener("click", () => {
    document.querySelector(".contenedorPrincipal.Pedido").style.display = "none";
    document.querySelector(".contenedorPrincipal.Cliente").style.display = "none";
    document.querySelector(".contenedorPrincipal.Envio").style.display = "grid";
    document.querySelector(".contenedorPrincipal.Productos").style.display = "none";
    btnDatosEnvio.style.backgroundColor = "#2C3E50";
    btnDatosEnvio.style.color = '#F8F7F7';
    btnDatosPedido.style.backgroundColor = "transparent";
    btnDatosPedido.style.color = '#000000';
    btnDatosPedido.style.border = '1px solid #000000';
    btnDatosCliente.style.backgroundColor = "transparent";
    btnDatosCliente.style.color = '#000000';
    btnDatosCliente.style.border = '1px solid #000000';
    btnDatosProductos.style.backgroundColor = "transparent";
    btnDatosProductos.style.color = '#000000';    
    btnDatosProductos.style.border = '1px solid #000000';
});

btnDatosProductos.addEventListener("click", () => {
    document.querySelector(".contenedorPrincipal.Pedido").style.display = "none";
    document.querySelector(".contenedorPrincipal.Cliente").style.display = "none";
    document.querySelector(".contenedorPrincipal.Envio").style.display = "none";
    document.querySelector(".contenedorPrincipal.Productos").style.display = "grid";
    btnDatosProductos.style.backgroundColor = "#2C3E50";
    btnDatosProductos.style.color = '#F8F7F7';
    btnDatosPedido.style.backgroundColor = "transparent";
    btnDatosPedido.style.color = '#000000';
    btnDatosPedido.style.border = '1px solid #000000';
    btnDatosEnvio.style.backgroundColor = "transparent";
    btnDatosEnvio.style.color = '#000000';
    btnDatosEnvio.style.border = '1px solid #000000';
    btnDatosCliente.style.backgroundColor = "transparent";
    btnDatosCliente.style.color = '#000000';    
    btnDatosCliente.style.border = '1px solid #000000';
});