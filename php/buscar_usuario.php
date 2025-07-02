<?php
header('Content-Type: application/json');

// Incluir conexión a la base de datos
require_once __DIR__ . "/../inc/conexionbd.php";
$con = connection();

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Obtener término de búsqueda
$termino = isset($_GET['termino']) ? mysqli_real_escape_string($con, $_GET['termino']) : '';

// Verificar si se proporcionó un término de búsqueda
if (empty($termino)) {
    echo json_encode([
        'success' => false,
        'message' => 'Debe proporcionar un término de búsqueda'
    ]);
    exit();
}

// Consulta de búsqueda
$query = "SELECT id_empleado, nombre_completo, cedula_empleado, usuario, cargo 
          FROM empleados 
          WHERE nombre_completo LIKE '%$termino%' 
             OR cedula_empleado LIKE '%$termino%' 
             OR usuario LIKE '%$termino%'";

$result = mysqli_query($con, $query);

// Preparar resultados
$usuarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['cargo'] = ucfirst($row['cargo']);
    $usuarios[] = $row;
}

// Devolver resultados
echo json_encode([
    'success' => true,
    'usuarios' => $usuarios,
    'total' => count($usuarios)
]);

mysqli_close($con);
?>