<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['estado' => false, 'mensaje' => 'No autorizado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['estado' => false, 'mensaje' => 'Método no permitido.']);
    exit();
}

$camposTexto = [
    'codigoCalzadoNuevo',
    'tipo',
    'descripcionCalzadoNuevo',
    'linea',
    'precioCosto',
    'estado'
];
foreach ($camposTexto as $campo) {
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
        http_response_code(400);
        echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos del calzado.']);
        exit();
    }
}

$codigoCalzado = trim($_POST['codigoCalzadoNuevo']);
$tipoCalzado = trim($_POST['tipo']);
$descripcionCalzado = trim($_POST['descripcionCalzadoNuevo']);
$lineaCalzado = trim($_POST['linea']);
$precioCosto = trim($_POST['precioCosto']);
$estadoCalzado = $_POST['estado'] ?? null;

if (
    $codigoCalzado === '' || !ctype_digit($codigoCalzado) ||
    $tipoCalzado === '' || $descripcionCalzado === '' || $lineaCalzado === '' ||
    $precioCosto === '' || !is_numeric($precioCosto) || (float) $precioCosto < 0 ||
    !in_array($estadoCalzado, ['0', '1'], true)
) {
    http_response_code(400);
    echo json_encode(['estado' => false, 'mensaje' => 'Verifica los datos del calzado.']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuracion/db_config.php';

try {
    $conn = new mysqli($host, $usuario, $contraseña, $baseDeDatos);
    $conn->set_charset('utf8mb4');
    $conn->begin_transaction();

    $codigoCalzado = (int) $codigoCalzado;
    $estadoCalzado = (int) $estadoCalzado;
    $precioCosto = (float) $precioCosto;

    $consultaCalzado = $conn->prepare('SELECT ID_Calzado FROM calzado WHERE ID_Calzado = ? FOR UPDATE');
    $consultaCalzado->bind_param('i', $codigoCalzado);
    $consultaCalzado->execute();

    if ($consultaCalzado->get_result()->num_rows === 0) {
        throw new RuntimeException('No se encontró el calzado que intentas editar.');
    }
    $consultaCalzado->close();

    $idTipoCalzado = verificarExistenciaCatalogo(
        $conn,
        'tipocalzado',
        'ID_Tipo',
        'descripcionTipo',
        $tipoCalzado
    );
    $idLineaCalzado = verificarExistenciaCatalogo(
        $conn,
        'lineacalzado',
        'ID_linea',
        'Descripcion',
        $lineaCalzado
    );

    $actualizarCalzado = $conn->prepare(
        'UPDATE calzado
         SET ID_Linea = ?, ID_Tipo = ?, descripcionCalzado = ?, precioCosto = ?, estado = ?
         WHERE ID_Calzado = ?'
    );
    $actualizarCalzado->bind_param(
        'iisdii',
        $idLineaCalzado,
        $idTipoCalzado,
        $descripcionCalzado,
        $precioCosto,
        $estadoCalzado,
        $codigoCalzado
    );
    $actualizarCalzado->execute();
    $actualizarCalzado->close();

    $conn->commit();
    echo json_encode(['estado' => true, 'mensaje' => 'Calzado actualizado exitosamente.']);
    $conn->close();
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $errorConexion) {
            error_log('Error al revertir actualización de catálogo: ' . $errorConexion->getMessage());
        }
    }

    if ($error instanceof RuntimeException && $error->getMessage() === 'No se encontró el calzado que intentas editar.') {
        http_response_code(404);
        echo json_encode(['estado' => false, 'mensaje' => $error->getMessage()]);
        exit();
    }

    error_log('Error al actualizar el catálogo de calzados: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No fue posible actualizar el calzado.']);
}

function verificarExistenciaCatalogo(mysqli $conn, string $tabla, string $campoId, string $atributo, string $valor): int
{
    $tablasPermitidas = [
        'tipocalzado' => ['ID_Tipo', 'descripcionTipo'],
        'lineacalzado' => ['ID_linea', 'Descripcion']
    ];

    if (!isset($tablasPermitidas[$tabla]) || $tablasPermitidas[$tabla] !== [$campoId, $atributo]) {
        throw new LogicException('Tabla o campos no permitidos para verificar el catálogo.');
    }

    $consulta = $conn->prepare("SELECT $campoId FROM $tabla WHERE $atributo = ? LIMIT 1");
    $consulta->bind_param('s', $valor);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $consulta->close();
        return (int) $fila[$campoId];
    }
    $consulta->close();

    $insercion = $conn->prepare("INSERT INTO $tabla ($atributo) VALUES (?)");
    $insercion->bind_param('s', $valor);
    $insercion->execute();
    $id = (int) $conn->insert_id;
    $insercion->close();

    return $id;
}
