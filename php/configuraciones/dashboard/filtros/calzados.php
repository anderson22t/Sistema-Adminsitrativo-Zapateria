<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php'; // Ruta al archivo de configuración

$conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    //echo "Conexión exitosa a la base de datos!";
}

$datos = json_decode(file_get_contents('php://input'), true);
$rango = $datos['fechas'];
$filtros = $datos['filtros'];

// Datos de filtros
$codigoCanalVenta = !empty($filtros['canalVenta']) ? (int)$filtros['canalVenta'] : null;
$codigoTipoVenta = !empty($filtros['tipoVenta']) ? (int)$filtros['tipoVenta'] : null;


$fechaInicialActual = $rango['fechaInicialActual'];
$fechaFinalActual = $rango['fechaFinalActual'];

// Consulta para obtener los códigos de productos
$consultaCalzadosVendidos = $conn->prepare(
    "SELECT 
        c.ID_Calzado AS codigoCalzado,
        c.descripcionCalzado,
        SUM(
            CASE 
                WHEN rp.No_Pedido IS NOT NULL THEN d.cantidad
                ELSE 0
            END
        ) AS CantidadVendida,
        SUM(
            CASE 
                WHEN rp.No_Pedido IS NOT NULL THEN d.cantidad * d.precio
                ELSE 0
            END
        ) AS TotalVendido,
        CASE
            WHEN MAX(
                CASE 
                    WHEN rp.No_Pedido IS NOT NULL THEN 1
                    ELSE 0
                END
            ) = 1
            THEN 'Vendido'
            ELSE 'No vendido'
        END AS ResultadoVenta
    FROM calzado c
    LEFT JOIN detallepedido d 
        ON c.ID_Calzado = d.idCalzado
    LEFT JOIN registropedido rp 
        ON d.No_Pedido = rp.No_Pedido
        AND rp.fechaPedido BETWEEN ? AND ?
        AND (? IS NULL OR rp.codigoCanalVenta = ?)
        AND (? IS NULL OR rp.codigoTipoVenta = ?)
    GROUP BY 
        c.ID_Calzado,
        c.descripcionCalzado
    ORDER BY 
        CantidadVendida DESC,
        TotalVendido DESC,
        c.ID_Calzado ASC"
);

$consultaCalzadosVendidos->bind_param("ssiiii", 
    $fechaInicialActual, $fechaFinalActual, 
    $codigoCanalVenta, $codigoCanalVenta, 
    $codigoTipoVenta, $codigoTipoVenta
);
$consultaCalzadosVendidos->execute();
$resultado = $consultaCalzadosVendidos->get_result();
$datos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$consultaCalzadosVendidos->close(); // Cerrar la conexión a la base de datos
$conn->close();

?>