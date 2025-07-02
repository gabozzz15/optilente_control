<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Verificar si el usuario está logueado y tiene permisos
if (!isset($_SESSION['id_empleado']) || !isset($_SESSION['usuario'])) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'title' => 'Error de Acceso',
        'message' => 'Debe iniciar sesión para realizar esta acción'
    ];
    header("Location: ../index.php");
    exit();
}

// Verificar rol del usuario
$idUser = $_SESSION['id_empleado'];
$sql_role = "SELECT cargo FROM empleados WHERE id_empleado = ?";
$stmt_role = $con->prepare($sql_role);
$stmt_role->bind_param("i", $idUser);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
$user_role = $result_role->fetch_assoc()['cargo'];

if ($user_role !== 'gerente') {
    $_SESSION['notification'] = [
        'type' => 'error',
        'title' => 'Acceso Denegado',
        'message' => 'No tiene permisos para realizar esta acción'
    ];
    header("Location: ../inventario.php");
    exit();
}

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar datos de entrada
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
    $marca = trim(filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING));
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

    // Validar que todos los campos sean válidos
    if ($id === false || $tipo === false || empty($marca) || 
        $precio === false || $cantidad === false) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error de Validación',
            'message' => 'Por favor, complete todos los campos correctamente'
        ];
        header("Location: ../inventario.php");
        exit();
    }

    // Validar rango de valores
    if ($precio <= 0 || $cantidad < 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error de Validación',
            'message' => 'El precio debe ser mayor a 0 y la cantidad no puede ser negativa'
        ];
        header("Location: ../inventario.php");
        exit();
    }

    // Preparar la consulta según el tipo de item
    try {
        $con->begin_transaction();

        if ($tipo === 'montura') {
            $material = trim(filter_input(INPUT_POST, 'detalle', FILTER_SANITIZE_STRING));
            $sql = "UPDATE monturas SET marca = ?, material = ?, precio = ?, cantidad = ? WHERE id_montura = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssdii", $marca, $material, $precio, $cantidad, $id);
        } else if ($tipo === 'cristal') {
            // Separar tipo y material de cristal
            $detalles = explode(' - ', filter_input(INPUT_POST, 'detalle', FILTER_SANITIZE_STRING));
            if (count($detalles) !== 2) {
                throw new Exception("Formato de detalle de cristal inválido");
            }
            $tipo_cristal = $detalles[0];
            $material_cristal = $detalles[1];

            $sql = "UPDATE cristales SET marca = ?, tipo_cristal = ?, material_cristal = ?, precio = ?, cantidad = ? WHERE id_cristal = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssdii", $marca, $tipo_cristal, $material_cristal, $precio, $cantidad, $id);
        } else {
            throw new Exception("Tipo de item no válido");
        }

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $con->commit();
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Actualizado!',
                'message' => 'Item actualizado correctamente'
            ];
        } else {
            $con->rollback();
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al actualizar el item: ' . $con->error
            ];
        }

        $stmt->close();
    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()
        ];
    }
} else {
    // Si se intenta acceder directamente sin POST
    $_SESSION['notification'] = [
        'type' => 'error',
        'title' => 'Error',
        'message' => 'Acceso no autorizado'
    ];
}

// Redireccionar de vuelta a inventario
header("Location: ../inventario.php");
exit();
?>