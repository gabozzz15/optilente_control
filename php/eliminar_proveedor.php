<?php
// Si este archivo se llama directamente a través de HTTP, configurar el encabezado
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
}

// Iniciar sesión para manejar notificaciones si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a la base de datos si aún no está incluida
if (!function_exists('connection')) {
    require_once __DIR__ . "/../inc/conexionbd.php";
}
$con = connection();

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'success' => false, 
        'message' => 'Método no permitido'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        http_response_code(405);
        echo json_encode($response);
        exit();
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../gestionproveedores.php");
        exit();
    }
}

// Recoger y escapar datos
$id_proveedor = mysqli_real_escape_string($con, $_POST['id_proveedor']);

// Verificar si el proveedor existe
$check_proveedor = "SELECT * FROM proveedor WHERE id_proveedor = '$id_proveedor'";
$result_check = mysqli_query($con, $check_proveedor);

if (mysqli_num_rows($result_check) == 0) {
    $response = [
        'success' => false,
        'message' => 'El proveedor no existe.'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../gestionproveedores.php");
    }
    exit();
}

// Iniciar transacción para asegurar la integridad de los datos
mysqli_begin_transaction($con);

try {
    // Eliminar registros de las tablas intermedias primero
    $sql_delete_monturas = "DELETE FROM monturas_proveedores WHERE id_proveedor = '$id_proveedor'";
    mysqli_query($con, $sql_delete_monturas);
    
    $sql_delete_cristales = "DELETE FROM cristales_proveedores WHERE id_proveedor = '$id_proveedor'";
    mysqli_query($con, $sql_delete_cristales);
    
    // Finalmente eliminar el proveedor
    $sql_delete_proveedor = "DELETE FROM proveedor WHERE id_proveedor = '$id_proveedor'";
    mysqli_query($con, $sql_delete_proveedor);
    
    // Confirmar transacción
    mysqli_commit($con);
    
    $response = [
        'success' => true,
        'message' => 'Proveedor eliminado correctamente.'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Éxito!',
            'message' => $response['message']
        ];
        header("Location: ../gestionproveedores.php");
    }
} catch (Exception $e) {
    // Revertir cambios en caso de error
    mysqli_rollback($con);
    
    $response = [
        'success' => false,
        'message' => 'Error al eliminar el proveedor: ' . $e->getMessage()
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../gestionproveedores.php");
    }
}

// No cerramos la conexión si este archivo se incluye en otro
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    mysqli_close($con);
}
?>