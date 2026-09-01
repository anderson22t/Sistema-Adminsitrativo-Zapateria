const contenedorUsuario = document.querySelector(".contenedorUsuario");
const configuracionUsuario = document.querySelector(".configuracionUsuario");
const menuLateral = document.querySelector(".menuLateral");
const menuModulos = document.getElementById("menuModulos");
const sidebarModulos = document.querySelector(".sidebar");



contenedorUsuario.addEventListener("click", function (){
    configuracionUsuario.style.display = "block";
});

document.addEventListener("click", (e) => {
    if (!contenedorUsuario.contains(e.target)){
        configuracionUsuario.style.display = "none";
    }
});

function cerrarSesion() {
    window.location.href = '../configuraciones/cerrarSesion.php';
}

if (window.matchMedia("(max-width:1023px)").matches){
    contenedorUsuario.addEventListener("click", function (){
    configuracionUsuario.style.display = "block";
    menuLateral.style.display = 'block';
    });

    menuModulos.addEventListener("click", function (){
        sidebarModulos.style.display = "block";
        sidebarModulos.style.zIndex = "1000";
        menuLateral.style.display = 'block';
    });
}

menuLateral.addEventListener("click", function (){
    menuLateral.style.display = "none";
    sidebarModulos.style.display = "none";
});

// Manejo de submenús colapsables y resaltado del ítem activo en páginas de módulos
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
    const currentPage = window.location.pathname.split('/').pop(); // ej: "pedidos.php"
    const sidebarLinks = document.querySelectorAll(".sidebar a[href]");

    sidebarLinks.forEach(link => {
        const linkHref = link.getAttribute("href");
        if (!linkHref || linkHref === '#') return;

        const linkPage = linkHref.split('/').pop(); // ej: "pedidos.php"

        if (currentPage === linkPage) {
            // Resaltar la sección correspondiente
            const parentSection = link.closest(".seccion");
            if (parentSection) {
                parentSection.classList.add("activo");
            }

            // Si el ítem está dentro de un submenú: abrirlo automáticamente y resaltar el toggle
            const parentSubmenu = link.closest(".submenu");
            if (parentSubmenu) {
                parentSubmenu.classList.add("open");
                parentSubmenu.classList.add("activo-padre");
            }
        }
    });
});
