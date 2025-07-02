<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo incluir la conexión si no está ya definida
if (!isset($con)) {
    require_once __DIR__ . "/../inc/conexionbd.php";
    $con = connection();
}

// Procesar la eliminación directa
if (isset($_GET['tipo']) && isset($_GET['id'])) {
    $tipo = $_GET['tipo'];
    $id = $_GET['id'];
    
    $tabla = ($tipo === "montura") ? "monturas" : "cristales";
    
    // Eliminar el item directamente
    $query = "DELETE FROM $tabla WHERE id_{$tipo} = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Eliminado!',
            'message' => 'Item eliminado exitosamente'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Error al eliminar el item'
        ];
    }
    
    // Redireccionar de vuelta a inventario
    header("Location: ../optilente_control/inventario");
    exit();
} else {
    $_SESSION['notification'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Parámetros inválidos para la eliminación'
    ];
    header("Location: ../optilente_control/inventario");
    exit();
}
?>
