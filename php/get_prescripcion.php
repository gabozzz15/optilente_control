<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_empleado']) || !isset($_SESSION['usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de prescripción no proporcionado'
    ]);
    exit;
}

$id_prescripcion = $_GET['id'];

// Consultar la prescripción con datos del cliente
$query = "SELECT p.*, c.nombre, c.apellido, c.cedula_cliente 
          FROM prescripcion p 
          JOIN datos_clientes c ON p.id_cliente = c.id_cliente 
          WHERE p.id_prescripcion = '$id_prescripcion'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $prescripcion = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'prescripcion' => $prescripcion
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Prescripción no encontrada'
    ]);
}
?>