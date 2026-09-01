# Sistema Administrativo - Zapatería

El Sistema Administrativo es una aplicación web integral de gestión administrativa y operativa diseñada para una empresa de fabricación y comercialización de calzado. Permite la administración eficiente de todo el ciclo de negocio: desde el registro de pedidos de clientes y gestión de inventarios, hasta el control de compras de materias primas y visualización de KPIs mediante un dashboard interactivo.

## 🚀 Características Principales (Módulos)

El sistema está dividido en los siguientes módulos principales:

* **Registro de Pedidos**: Gestión de ventas (al por mayor y detalle), registro de clientes, información de envíos y cálculos de totales.
* **Ingreso de Producción**: Registro de nuevos lotes de calzado fabricado para aumentar el inventario de producto terminado.
* **Inventario de Producción**: Control de existencias de calzado disponible por modelo, talla y color.
* **Compra de Materiales**: Registro de abastecimiento de insumos y materia prima.
* **Inventario de Materiales**: Control de existencias de insumos necesarios para la fabricación.
* **Análisis y Reportes (Dashboard)**: Gráficos interactivos, KPIs (Ticket Promedio, Ventas Totales, Pares Vendidos) y reportes tabulares con filtros avanzados por fecha, canal de venta, etc.
* **Análisis General**: Panel global de métricas *(Módulo actualmente en desarrollo y siendo añadido al sistema)*.
* **Análisis de Productos**: Detalle de ventas por producto y categoría *(Módulo actualmente en desarrollo y siendo añadido al sistema)*.

## 🛠 Tecnologías Utilizadas

* **Frontend**: HTML5, CSS3 (Custom Properties, Flexbox, CSS Grid), JavaScript (Vanilla ES6, API Fetch).
* **Backend**: PHP (Orientado a objetos, preparación de sentencias PDO/mysqli).
* **Base de Datos**: MySQL / MariaDB (con manejo transaccional).
* **Librerías/Complementos**: 
  * [SweetAlert2](https://sweetalert2.github.io/) (Para alertas y modales).
  * [Ionicons](https://ionic.io/ionicons) (Para iconografía y diseño visual).

## 📋 Requisitos Previos

Para poder ejecutar el proyecto en un entorno local, necesitas:
* Un servidor web local como **XAMPP**, **WAMP**, o **MAMP** (que incluya Apache y MySQL).
* PHP 7.4 o superior.

## ⚙️ Instalación y Configuración

1. **Clonar el repositorio**:
   ```bash
   git clone <url-del-repositorio>
   ```

2. **Ubicación del Proyecto**:
   Mueve los archivos del proyecto a la carpeta raíz de tu servidor web (por ejemplo, `C:\xampp\htdocs\Sistema-Adminsitrativo-Zapateria`).

3. **Base de Datos**:
   * Abre phpMyAdmin (o tu gestor de MySQL preferido).
   * Crea una base de datos llamada `sistema_zapateria`.
   * Importa el archivo SQL con la estructura de las tablas (si cuentas con un `.sql` de respaldo).

4. **Configuración de Credenciales**:
   * Asegúrate de tener configurado el archivo de conexión a la base de datos (generalmente fuera de la carpeta pública por seguridad o en el directorio `configuracion/db_config.php`).
   * Configura las variables: `$host`, `$usuario`, `$contraseña` y `$baseDeDatos`.

5. **Primer Usuario (Admin)**:
   * Por seguridad, el sistema utiliza contraseñas encriptadas con BCRYPT. Puedes usar el script `php/configuraciones/usuario.php` (revisar primero el código) para generar tu primer usuario en la base de datos.
   * *Nota: Asegúrate de eliminar o proteger el archivo `usuario.php` en producción.*

## 💻 Uso Básico

1. Inicia los servicios de Apache y MySQL en XAMPP.
2. Abre tu navegador y dirígete a `http://localhost/Sistema-Adminsitrativo-Zapateria/html/index.html`
3. Inicia sesión con tus credenciales.
4. Navega a través de los módulos en la barra lateral para gestionar los recursos de tu zapatería.

---
*Proyecto desarrollado para la administración y automatización de los procesos de la Zapatería.*
