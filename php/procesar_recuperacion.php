<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Función para validar datos
function validar($data) {
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Verificar qué paso del proceso estamos manejando
$paso = isset($_POST['paso']) ? $_POST['paso'] : '';

// Paso 1: Verificar si el usuario existe
if ($paso == '1') {
    $usuario = validar($_POST['usuario']);
    
    if (empty($usuario)) {
        header("Location: ../recuperar_contrasena.php?error=El nombre de usuario es requerido");
        exit();
    }
    
    // Verificar si el usuario existe
    $sql = "SELECT id_empleado FROM empleados WHERE usuario = ? AND estado_empleado = 'activo'";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // Verificar si tiene preguntas de seguridad configuradas
        $row = $result->fetch_assoc();
        $id_empleado = $row['id_empleado'];
        
        $sql_pregunta = "SELECT id_pregunta FROM preguntas_seguridad WHERE id_empleado = ?";
        $stmt_pregunta = $con->prepare($sql_pregunta);
        $stmt_pregunta->bind_param("i", $id_empleado);
        $stmt_pregunta->execute();
        $result_pregunta = $stmt_pregunta->get_result();
        
        if ($result_pregunta->num_rows > 0) {
            // Redirigir al paso 2 (responder pregunta de seguridad)
            header("Location: ../recuperar_contrasena.php?paso=2&usuario=" . urlencode($usuario));
            exit();
        } else {
            header("Location: ../recuperar_contrasena.php?error=Este usuario no tiene preguntas de seguridad configuradas. Contacte al administrador.");
            exit();
        }
    } else {
        header("Location: ../recuperar_contrasena.php?error=Usuario no encontrado o inactivo");
        exit();
    }
}

// Paso 2: Verificar la respuesta a las preguntas de seguridad
elseif ($paso == '2') {
    $usuario = validar($_POST['usuario']);
    $id_empleado = validar($_POST['id_empleado']);
    $preguntas = $_POST['preguntas'];
    $respuestas = $_POST['respuestas'];
    
    // Validar que se hayan proporcionado todas las respuestas
    $respuestas_validas = true;
    foreach ($respuestas as $respuesta) {
        if (empty(trim($respuesta))) {
            $respuestas_validas = false;
            break;
        }
    }
    
    if (!$respuestas_validas) {
        header("Location: ../recuperar_contrasena.php?paso=2&usuario=" . urlencode($usuario) . "&error=Todas las respuestas son requeridas");
        exit();
    }
    
    // Verificar si las respuestas son correctas
    $respuestas_correctas = 0;
    
    foreach ($preguntas as $index => $id_pregunta) {
        $respuesta = trim(strtolower($respuestas[$index]));
        
        // Verificar la respuesta para cada pregunta
        $sql = "SELECT * FROM preguntas_seguridad WHERE id_pregunta = ? AND id_empleado = ? AND LOWER(respuesta) = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("iis", $id_pregunta, $id_empleado, $respuesta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $respuestas_correctas++;
        }
    }
    
    // Se requiere que al menos 2 respuestas sean correctas
    if ($respuestas_correctas >= 2) {
        // Respuestas correctas, redirigir al paso 3 (establecer nueva contraseña)
        header("Location: ../recuperar_contrasena.php?paso=3&usuario=" . urlencode($usuario));
        exit();
    } else {
        header("Location: ../recuperar_contrasena.php?paso=2&usuario=" . urlencode($usuario) . "&error=Las respuestas de seguridad son incorrectas");
        exit();
    }
}

// Paso 3: Cambiar la contraseña
elseif ($paso == '3') {
    $usuario = validar($_POST['usuario']);
    $nueva_clave = validar($_POST['nueva_clave']);
    $confirmar_clave = validar($_POST['confirmar_clave']);
    
    if (empty($nueva_clave) || empty($confirmar_clave)) {
        header("Location: ../recuperar_contrasena.php?paso=3&usuario=" . urlencode($usuario) . "&error=Todos los campos son requeridos");
        exit();
    }
    
    if ($nueva_clave !== $confirmar_clave) {
        header("Location: ../recuperar_contrasena.php?paso=3&usuario=" . urlencode($usuario) . "&error=Las contraseñas no coinciden");
        exit();
    }
    
    // Actualizar la contraseña
    $sql = "UPDATE empleados SET clave = ? WHERE usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $nueva_clave, $usuario);
    $result = $stmt->execute();
    
    if ($result) {
        header("Location: ../login.php?mensaje=Contraseña actualizada correctamente. Ya puede iniciar sesión.");
        exit();
    } else {
        header("Location: ../recuperar_contrasena.php?paso=3&usuario=" . urlencode($usuario) . "&error=Error al actualizar la contraseña: " . $con->error);
        exit();
    }
}

// Si no se especificó un paso válido
else {
    header("Location: ../recuperar_contrasena.php?error=Solicitud inválida");
    exit();
}
?>