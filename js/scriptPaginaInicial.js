const iconoMenu = document.getElementById("menu");
const contenedorUsuario = document.querySelector(".contenedorUsuario");
const configuracionUsuario = document.querySelector(".configuracionUsuario");
const menuLateral = document.querySelector(".menuLateral");

iconoMenu.addEventListener("click", function () {
    menuLateral.style.display = "block";
    document.querySelector('.sidebar.activo').style.display = "block";
    document.getElementById("iconoCerrar").style.display = "block";
});

menuLateral.addEventListener("click", function (){
    menuLateral.style.display = "none";
    document.querySelector('.sidebar.activo').style.display = "none";
    document.getElementById("iconoCerrar").style.display = "none";
});

contenedorUsuario.addEventListener("click", function (){
    configuracionUsuario.style.display = "block";
});

document.addEventListener("click", (e) => {
    if (!contenedorUsuario.contains(e.target)){
        configuracionUsuario.style.display = "none";
    }
});

document.getElementById('cerrarSesion').addEventListener("click", function(){
    window.location.href = '../configuraciones/usuarios/cerrarSesion.php';
});


if (window.matchMedia("(max-width:1023px)").matches){
    contenedorUsuario.addEventListener("click", function (){
    configuracionUsuario.style.display = "block";
    menuLateral.style.display = 'block';
    });
}

// Manejo de submenús colapsables y resaltado del ítem activo en página inicial
document.addEventListener("DOMContentLoaded", () => {
    // --- Submenú colapsable (acordeón) ---
    const submenuToggles = document.querySelectorAll(".submenu-toggle");
    submenuToggles.forEach(toggle => {
        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            const parent = toggle.parentElement;
            
            // Cerrar otros submenús (acordeón)
            document.querySelectorAll(".submenu").forEach(item => {
                if (item !== parent) {
                    item.classList.remove("open");
                }
            });
            
            // Alternar el actual
            parent.classList.toggle("open");
        });
    });

    // --- Resaltar el ítem activo según la URL actual ---
    const currentPage = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll(".sidebar a[href]");

    sidebarLinks.forEach(link => {
        const linkHref = link.getAttribute("href");
        if (!linkHref || linkHref === '#') return;

        const linkPage = linkHref.split('/').pop();

        if (currentPage === linkPage) {
            const parentSection = link.closest(".seccion");
            if (parentSection) {
                parentSection.classList.add("activo");
            }

            const parentSubmenu = link.closest(".submenu");
            if (parentSubmenu) {
                parentSubmenu.classList.add("open");
                parentSubmenu.classList.add("activo-padre");
            }
        }
    });
});
