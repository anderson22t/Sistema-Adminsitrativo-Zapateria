/*--------Cambiar de formulario de calzado existente a nuevo--------*/
const btnCalzadoNuevo = document.getElementById('calzadoNuevo');
const btnCalzadoExistente = document.getElementById('calzadoExistente');


btnCalzadoExistente.addEventListener('click', function() {
    document.querySelector('.formularioCalzado.Existente').style.display = 'grid';
    document.querySelector('.formularioCalzado.Nuevo').style.display = 'none';
    btnCalzadoExistente.style.backgroundColor = '#2C3E50';
    btnCalzadoExistente.style.color = '#F8F7F7';
    btnCalzadoNuevo.style.border = '1px solid #000000';
    btnCalzadoNuevo.style.backgroundColor = 'transparent';
    btnCalzadoNuevo.style.color = '#000000';
});

btnCalzadoNuevo.addEventListener('click', function() {
    document.querySelector('.formularioCalzado.Nuevo').style.display = 'grid';
    document.querySelector('.formularioCalzado.Existente').style.display = 'none';
    btnCalzadoNuevo.style.backgroundColor = '#2C3E50';
    btnCalzadoNuevo.style.color = '#F8F7F7';
    btnCalzadoExistente.style.border = '1px solid #000000';
    btnCalzadoExistente.style.backgroundColor = 'transparent';
    btnCalzadoExistente.style.color = '#000000';
});

/* --------------------  Actualizar la descripción del producto al seleccionar un código de producto --------------------*/
document.getElementById('codigoCalzadoExistente').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var descripcion = selectedOption.getAttribute('data-descripcion');
    var descripcionCalzado = document.getElementById('descripcionProducto');

    if (descripcion) {
        descripcionCalzado.textContent = descripcion;
    }
    else {
        descripcionCalzado.textContent = 'Selecciona un código'; // Limpiar si no hay descripción
    }
}
);

const formularioCalzadoNuevo = document.getElementById('formCalzadoNuevo'); 
const formularioCalzadoExistente = document.getElementById('formCalzadoExistente');


formularioCalzadoNuevo.addEventListener('submit', function(e){
    e.preventDefault();
    const datos =  new FormData(formularioCalzadoNuevo);

    fetch('../../php/configuraciones/ingresoNuevoCalzado.php', {
        method: 'POST',
        body: datos })
        .then(r => r.json())
        .then(data=> {
            if (data.estado) {
                mostrarToast(data.mensaje, "success");
                setTimeout(() => {
                    location.reload();
                }, 3000);

            } else {
                mostrarToast(data.mensaje, "error");
            }
        })

})

formularioCalzadoExistente.addEventListener('submit', function(e){
    e.preventDefault();
    const datos =  new FormData(formularioCalzadoExistente);

    fetch('../../php/configuraciones/ingresoProduccionExistente.php', {
        method: 'POST',
        body: datos })
        .then(r => r.json())
        .then(data=> {
            if (data.estado) {
                mostrarToast(data.mensaje, "success");
                formularioCalzadoExistente.reset();

            } else {
                mostrarToast(data.mensaje, "error");
            }
        })
})
