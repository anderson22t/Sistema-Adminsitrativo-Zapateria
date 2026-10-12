# Sistema Administrativo para Zapatería

Sistema web diseñado para gestionar las operaciones principales de una zapatería, incluyendo pedidos, producción, compras de materiales, inventarios, reportes y análisis de ventas. La plataforma centraliza la información operativa y ayuda a mejorar la trazabilidad de los procesos de fabricación y comercialización.

## Descripción general

El proyecto permite administrar de forma integral los procesos de negocio de la empresa, desde el registro de pedidos hasta la consulta de indicadores de rendimiento en ventas. Además, incorpora dashboards para analizar métricas clave y facilitar la toma de decisiones basada en datos.

## Módulos principales

### Registro de Pedidos
Gestiona la carga y seguimiento de pedidos, incluyendo datos del cliente, productos solicitados, tipo y canal de venta, información de envío y forma de pago.

### Producción
Permite registrar la producción de calzado, tanto para nuevos modelos como para productos existentes, con detalle de color, talla, cantidad y demás variables necesarias para el control operativo.

### Compra de Materiales
Registra las compras de materiales para la fabricación, con información de proveedores, cantidades, costos, compradores y comprobantes, además de consultar el historial de compras.

### Inventario de Producción
Controla las existencias de calzado producido, organizadas por código, modelo, línea, color y talla para facilitar la consulta y búsqueda de productos disponibles.

### Inventario de Materiales
Monitorea el stock de materiales utilizados en producción, permitiendo consultar cantidades disponibles y localizar materiales registrados dentro del sistema.

### Reportes
Genera reportes de ventas y pedidos con filtros para analizar estados, formas de pago, cantidades y montos de venta de manera más eficiente.

### Inteligencia de Negocios
Incluye dashboards de análisis general y por producto, con filtros por periodo, canal de venta, tipo de venta y modelo, además de indicadores clave para la toma de decisiones.

## Tecnologías utilizadas

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP

### Base de datos
- MySQL

### Librerías y recursos
- SweetAlert2
- Ionicons
- ApexCharts

## Estructura del proyecto

- `index.html` — pantalla de inicio de sesión
- `php/vistas/` — módulos principales del sistema
- `php/configuraciones/` — lógica de negocio, inserciones, filtros y configuración
- `js/` — scripts de la interfaz y funcionalidad del sistema
- `css/` — estilos y diseño responsivo
- `capturas/` — imágenes de referencia del sistema

## Capturas del sistema

### Inicio de sesión

![Inicio de sesión](capturas/inicioSesión.png)

### Página principal

![Página principal](capturas/paginaPrincipal.png)

### Registro de pedidos

![Registro de pedidos](capturas/registroPedidos.png)

### Ingreso de producción

![Ingreso de producción](capturas/ingresoProduccion.png)

### Inventario de producción

![Inventario de producción](capturas/inventario.png)

### Compra de materiales

![Compra de materiales](capturas/compras.png)

### Reportes

![Reportes](capturas/reportes.png)

## Nota

Este repositorio se presenta con fines de portafolio. Por motivos de privacidad y seguridad, no se incluyen la base de datos real, credenciales ni información sensible del entorno productivo.

