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
        header("Location: ../optilente_control/gestionproveedores.php");
        exit();
    }
}

// Recoger y escapar datos
$rif = mysqli_real_escape_string($con, $_POST['rif']);
$nombre = mysqli_real_escape_string($con, $_POST['nombre']);
$direccion = mysqli_real_escape_string($con, $_POST['direccion']);
$telefono = mysqli_real_escape_string($con, $_POST['telefono']);
$correo = mysqli_real_escape_string($con, $_POST['correo']);

// Verificar si el RIF ya existe
$check_rif = "SELECT * FROM proveedor WHERE rif_proveedor = '$rif'";
$result_check_rif = mysqli_query($con, $check_rif);

if (mysqli_num_rows($result_check_rif) > 0) {
    $response = [
        'success' => false,
        'message' => 'El RIF ya está registrado para otro proveedor.'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionproveedores.php");
    }
    exit();
}

// Preparar consulta de inserción
$sql_crear = "INSERT INTO proveedor (
    rif_proveedor, 
    nombre, 
    direccion, 
    telefono, 
    correo
) VALUES (
    '$rif', 
    '$nombre', 
    '$direccion', 
    '$telefono', 
    '$correo'
)";

// Ejecutar consulta
if (mysqli_query($con, $sql_crear)) {
    // Obtener el ID del proveedor recién creado
    $id_proveedor = mysqli_insert_id($con);
    
    $response = [
        'success' => true,
        'message' => 'Proveedor creado correctamente.',
        'proveedor' => [
            'id' => $id_proveedor,
            'nombre' => $nombre,
            'rif' => $rif
        ]
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Éxito!',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionproveedores.php");
    }
} else {
    $response = [
        'success' => false,
        'message' => 'No se pudo crear el proveedor: ' . mysqli_error($con)
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionproveedores.php");
    }
}

// No cerramos la conexión si este archivo se incluye en otro
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    mysqli_close($con);
}
?>