<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Verificar si se recibió una cédula
if (isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];
    
    // Preparar consulta para buscar cliente por cédula
    $sql = "SELECT * FROM datos_clientes WHERE cedula_cliente = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $cedula);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Verificar si se encontró el cliente
    if (mysqli_num_rows($result) > 0) {
        $cliente = mysqli_fetch_assoc($result);
        $id_cliente = $cliente['id_cliente'];
        
        // Buscar prescripciones del cliente
        $sql_prescripciones = "SELECT * FROM prescripcion WHERE id_cliente = ? ORDER BY fecha_emision DESC";
        $stmt_prescripciones = mysqli_prepare($con, $sql_prescripciones);
        mysqli_stmt_bind_param($stmt_prescripciones, "i", $id_cliente);
        mysqli_stmt_execute($stmt_prescripciones);
        $result_prescripciones = mysqli_stmt_get_result($stmt_prescripciones);
        
        $prescripciones = [];
        while ($prescripcion = mysqli_fetch_assoc($result_prescripciones)) {
            $prescripciones[] = $prescripcion;
        }
        
        // Devolver datos del cliente y sus prescripciones en formato JSON
        echo json_encode([
            'success' => true,
            'cliente' => $cliente,
            'prescripciones' => $prescripciones
        ]);
    } else {
        // Cliente no encontrado
        echo json_encode([
            'success' => false,
            'message' => 'Cliente no encontrado'
        ]);
    }
} else {
    // No se proporcionó cédula
    echo json_encode([
        'success' => false,
        'message' => 'No se proporcionó cédula'
    ]);
}
?>