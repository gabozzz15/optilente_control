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
$nombre = mysqli_real_escape_string($con, $_POST['nombre']);
$apellido = mysqli_real_escape_string($con, $_POST['apellido']);
$cedula = mysqli_real_escape_string($con, $_POST['cedula']);
$correo = mysqli_real_escape_string($con, $_POST['correo']);
$telefono = mysqli_real_escape_string($con, $_POST['telefono']);
$usuario = mysqli_real_escape_string($con, $_POST['usuario']);
$clave = mysqli_real_escape_string($con, $_POST['clave']);
$cargo = mysqli_real_escape_string($con, $_POST['cargo']);
$estado = isset($_POST['estado']) ? mysqli_real_escape_string($con, $_POST['estado']) : 'activo';

// Validar formato de cédula (solo números y entre 6-10 dígitos)
if (!empty($cedula) && !preg_match('/^[0-9]{6,10}$/', $cedula)) {
    $response = [
        'success' => false,
        'message' => 'La cédula debe contener solo números y tener entre 6 y 10 dígitos.'
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

// Verificar si la cédula ya existe
$check_cedula = "SELECT * FROM empleados WHERE cedula_empleado = '$cedula' AND cedula_empleado != ''";
$result_check_cedula = mysqli_query($con, $check_cedula);

if (mysqli_num_rows($result_check_cedula) > 0) {
    $response = [
        'success' => false,
        'message' => 'La cédula ya está registrada para otro usuario.'
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

// Verificar si el usuario ya existe
$check_usuario = "SELECT * FROM empleados WHERE usuario = '$usuario'";
$result_check = mysqli_query($con, $check_usuario);

if (mysqli_num_rows($result_check) > 0) {
    $response = [
        'success' => false,
        'message' => 'El nombre de usuario ya existe.'
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

// Preparar consulta de inserción
$sql_crear = "INSERT INTO empleados (
    nombre_empleado, 
    apellido_empleado,
    cedula_empleado, 
    correo, 
    num_telefono, 
    usuario, 
    clave, 
    cargo,
    estado_empleado
) VALUES (
    '$nombre', 
    '$apellido',
    '$cedula', 
    '$correo', 
    '$telefono', 
    '$usuario', 
    '$clave', 
    '$cargo',
    '$estado'
)";

// Ejecutar consulta
if (mysqli_query($con, $sql_crear)) {
    // Obtener el ID del usuario recién creado
    $id_usuario = mysqli_insert_id($con);
    
    $response = [
        'success' => true,
        'message' => 'Usuario creado correctamente.',
        'usuario' => [
            'id' => $id_usuario,
            'nombre_completo' => $nombre . ' ' . $apellido,
            'usuario' => $usuario,
            'cargo' => $cargo,
            'estado' => $estado
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
        'message' => 'No se pudo crear el usuario: ' . mysqli_error($con)
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