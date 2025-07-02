<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Función para actualizar el estado del pedido
if (isset($_POST['actualizar_estado']) || isset($_GET['actualizar_estado'])) {
    // Obtener los parámetros del pedido (ya sea por POST o GET)
    $id_pedido = isset($_POST['id_pedido']) ? $_POST['id_pedido'] : $_GET['id_pedido'];
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : $_GET['nuevo_estado'];

    try {
        // Iniciar transacción
        mysqli_begin_transaction($con);
        
        // Obtener el estado actual y los IDs de montura y cristales
        $sql_actual = "SELECT estado_pedido, id_montura, id_cristal1, id_cristal2, cantidad FROM pedidos WHERE id_pedido = ?";
        $stmt_actual = mysqli_prepare($con, $sql_actual);
        mysqli_stmt_bind_param($stmt_actual, "i", $id_pedido);
        mysqli_stmt_execute($stmt_actual);
        $resultado_actual = mysqli_stmt_get_result($stmt_actual);
        $pedido_actual = mysqli_fetch_assoc($resultado_actual);
        
        // Obtener la cantidad (si no existe, usar 1 como valor predeterminado)
        $cantidad = isset($pedido_actual['cantidad']) ? $pedido_actual['cantidad'] : 1;
        
        // Preparar la consulta base para actualizar el pedido
        $sql = "UPDATE pedidos SET estado_pedido = ?";
        
        // Si el estado es 'entregado', agregar la fecha de entrega
        if ($nuevo_estado === 'entregado') {
            $sql .= ", fecha_entrega = CURDATE()";
            
            // Si el estado anterior no era 'entregado', actualizar contadores de venta
            if ($pedido_actual['estado_pedido'] !== 'entregado') {
                // Actualizar contador de venta para la montura
                $sql_montura = "UPDATE monturas SET contador_venta = IFNULL(contador_venta, 0) + ? WHERE id_montura = ?";
                $stmt_montura = mysqli_prepare($con, $sql_montura);
                mysqli_stmt_bind_param($stmt_montura, "ii", $cantidad, $pedido_actual['id_montura']);
                mysqli_stmt_execute($stmt_montura);
                
                // Actualizar contador de venta para el cristal 1
                $sql_cristal1 = "UPDATE cristales SET contador_venta = IFNULL(contador_venta, 0) + ? WHERE id_cristal = ?";
                $stmt_cristal1 = mysqli_prepare($con, $sql_cristal1);
                mysqli_stmt_bind_param($stmt_cristal1, "ii", $cantidad, $pedido_actual['id_cristal1']);
                mysqli_stmt_execute($stmt_cristal1);
                
                // Actualizar contador de venta para el cristal 2
                $sql_cristal2 = "UPDATE cristales SET contador_venta = IFNULL(contador_venta, 0) + ? WHERE id_cristal = ?";
                $stmt_cristal2 = mysqli_prepare($con, $sql_cristal2);
                mysqli_stmt_bind_param($stmt_cristal2, "ii", $cantidad, $pedido_actual['id_cristal2']);
                mysqli_stmt_execute($stmt_cristal2);
            }
        } else if ($pedido_actual['estado_pedido'] === 'entregado' && $nuevo_estado !== 'entregado') {
            // Si estamos cambiando de 'entregado' a otro estado, restar de los contadores
            $sql .= ", fecha_entrega = NULL";
            
            // Actualizar contador de venta para la montura (restar)
            $sql_montura = "UPDATE monturas SET contador_venta = GREATEST(IFNULL(contador_venta, 0) - ?, 0) WHERE id_montura = ?";
            $stmt_montura = mysqli_prepare($con, $sql_montura);
            mysqli_stmt_bind_param($stmt_montura, "ii", $cantidad, $pedido_actual['id_montura']);
            mysqli_stmt_execute($stmt_montura);
            
            // Actualizar contador de venta para el cristal 1 (restar)
            $sql_cristal1 = "UPDATE cristales SET contador_venta = GREATEST(IFNULL(contador_venta, 0) - ?, 0) WHERE id_cristal = ?";
            $stmt_cristal1 = mysqli_prepare($con, $sql_cristal1);
            mysqli_stmt_bind_param($stmt_cristal1, "ii", $cantidad, $pedido_actual['id_cristal1']);
            mysqli_stmt_execute($stmt_cristal1);
            
            // Actualizar contador de venta para el cristal 2 (restar)
            $sql_cristal2 = "UPDATE cristales SET contador_venta = GREATEST(IFNULL(contador_venta, 0) - ?, 0) WHERE id_cristal = ?";
            $stmt_cristal2 = mysqli_prepare($con, $sql_cristal2);
            mysqli_stmt_bind_param($stmt_cristal2, "ii", $cantidad, $pedido_actual['id_cristal2']);
            mysqli_stmt_execute($stmt_cristal2);
        } else {
            // Para otros cambios de estado que no involucran 'entregado'
            $sql .= ", fecha_entrega = NULL";
        }
        
        // Completar la consulta con la condición
        $sql .= " WHERE id_pedido = ?";
        
        // Preparar y ejecutar la consulta
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id_pedido);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Confirmar transacción
            mysqli_commit($con);
            
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Actualizado!',
                'message' => $nuevo_estado === 'entregado' 
                    ? 'Pedido marcado como entregado con fecha actual y contadores actualizados' 
                    : 'Estado del pedido actualizado correctamente'
            ];
        } else {
            // Revertir transacción
            mysqli_rollback($con);
            
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo actualizar el estado del pedido'
            ];
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($con);
        
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Ocurrió un error: ' . $e->getMessage()
        ];
    }

    // Redireccionar de vuelta a la página de pedidos
    header("Location: ../pedidos.php");
    exit();
}

// Función para eliminar pedido
if (isset($_GET['eliminar_pedido'])) {
    $id_pedido = $_GET['id_pedido'];

    try {
        // Iniciar transacción
        mysqli_begin_transaction($con);

        // Obtener detalles del pedido antes de eliminarlo
        $sql_detalles = "SELECT id_montura, id_cristal1, id_cristal2, cantidad FROM pedidos WHERE id_pedido = ?";
        $stmt_detalles = mysqli_prepare($con, $sql_detalles);
        mysqli_stmt_bind_param($stmt_detalles, "i", $id_pedido);
        mysqli_stmt_execute($stmt_detalles);
        $resultado_detalles = mysqli_stmt_get_result($stmt_detalles);
        $detalles_pedido = mysqli_fetch_assoc($resultado_detalles);
        
        // Obtener la cantidad (si no existe, usar 1 como valor predeterminado)
        $cantidad = isset($detalles_pedido['cantidad']) ? $detalles_pedido['cantidad'] : 1;

        // Restaurar inventario
        $sql_restaurar_montura = "UPDATE monturas SET cantidad = cantidad + ? WHERE id_montura = ?";
        $stmt_restaurar_montura = mysqli_prepare($con, $sql_restaurar_montura);
        mysqli_stmt_bind_param($stmt_restaurar_montura, "ii", $cantidad, $detalles_pedido['id_montura']);
        mysqli_stmt_execute($stmt_restaurar_montura);

        $sql_restaurar_cristal1 = "UPDATE cristales SET cantidad = cantidad + ? WHERE id_cristal = ?";
        $stmt_restaurar_cristal1 = mysqli_prepare($con, $sql_restaurar_cristal1);
        mysqli_stmt_bind_param($stmt_restaurar_cristal1, "ii", $cantidad, $detalles_pedido['id_cristal1']);
        mysqli_stmt_execute($stmt_restaurar_cristal1);

        $sql_restaurar_cristal2 = "UPDATE cristales SET cantidad = cantidad + ? WHERE id_cristal = ?";
        $stmt_restaurar_cristal2 = mysqli_prepare($con, $sql_restaurar_cristal2);
        mysqli_stmt_bind_param($stmt_restaurar_cristal2, "ii", $cantidad, $detalles_pedido['id_cristal2']);
        mysqli_stmt_execute($stmt_restaurar_cristal2);

        // Eliminar el pedido
        $sql_eliminar = "DELETE FROM pedidos WHERE id_pedido = ?";
        $stmt_eliminar = mysqli_prepare($con, $sql_eliminar);
        mysqli_stmt_bind_param($stmt_eliminar, "i", $id_pedido);
        
        // Ejecutar eliminación
        if (mysqli_stmt_execute($stmt_eliminar)) {
            // Confirmar transacción
            mysqli_commit($con);
            
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Eliminado!',
                'message' => 'Pedido eliminado correctamente y stock restaurado'
            ];
        } else {
            // Revertir transacción
            mysqli_rollback($con);
            
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo eliminar el pedido'
            ];
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($con);
        
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Ocurrió un error: ' . $e->getMessage()
        ];
    }

    // Redireccionar de vuelta a la página de pedidos
    header("Location: ../pedidos.php");
    exit();
}
?>