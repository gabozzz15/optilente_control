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
        header("Location: ../optilente_control/gestionusuario.php");
        exit();
    }
}

// Recoger y escapar datos
$id_empleado = mysqli_real_escape_string($con, $_POST['id_empleado']);

// Verificar si el usuario existe antes de eliminarlo
$check_query = "SELECT * FROM empleados WHERE id_empleado = '$id_empleado'";
$check_result = mysqli_query($con, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $response = [
        'success' => false,
        'message' => 'El usuario no existe.'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionusuario.php");
    }
    exit();
}

// Obtener información del usuario antes de eliminarlo
$usuario_info = mysqli_fetch_assoc($check_result);

// Verificar si el usuario es un gerente
if ($usuario_info['cargo'] == 'gerente') {
    $response = [
        'success' => false,
        'message' => 'No se puede eliminar un usuario con cargo de Gerente.'
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Operación no permitida',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionusuario.php");
    }
    exit();
}

// En lugar de eliminar, cambiar el estado a "retirado"
$sql_eliminar = "UPDATE empleados SET estado_empleado = 'retirado' WHERE id_empleado = '$id_empleado'";

// Ejecutar consulta
if (mysqli_query($con, $sql_eliminar)) {
    $response = [
        'success' => true,
        'message' => 'Usuario marcado como retirado correctamente.',
        'usuario_retirado' => [
            'id' => $id_empleado,
            'nombre_completo' => $usuario_info['nombre_empleado'] . ' ' . $usuario_info['apellido_empleado'],
            'usuario' => $usuario_info['usuario']
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
        header("Location: ../optilente_control/gestionusuario.php");
    }
} else {
    $response = [
        'success' => false,
        'message' => 'No se pudo eliminar el usuario: ' . mysqli_error($con)
    ];
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode($response);
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => $response['message']
        ];
        header("Location: ../optilente_control/gestionusuario.php");
    }
}

// No cerramos la conexión si este archivo se incluye en otro
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    mysqli_close($con);
}
?>