<?php 
require_once __DIR__ . '/../../../../../configuracion/db_config.php'; // Ruta al archivo de configuración

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    //echo "Conexión exitosa a la base de datos!";
}

// Consulta para obtener los códigos de productos
$sql = "SELECT IDCanalVenta, canalVenta FROM canalventa";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Mostrar los códigos en el select
    while($row = $result->fetch_assoc()) {
        echo "<option value='" . $row["IDCanalVenta"] . "' data-descripcion='" . $row["canalVenta"] . "'>" . $row["canalVenta"] . "</option>";
    }
} else {
    echo "<option value=''>No hay registros de canal de venta</option>";
}

$conn->close(); // Cerrar la conexión a la base de datos