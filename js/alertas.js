function mostrarToast(mensaje, tipo) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: tipo,
        title: mensaje,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}