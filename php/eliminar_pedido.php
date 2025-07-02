<?php
include "../inc/conexionbd.php";
$con = connection();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    
    // Iniciar transacción
    mysqli_begin_transaction($con);
    
    try {
        // Primero, obtener los detalles del pedido para devolver los productos al inventario
        $sql_pedido = "SELECT id_montura, id_cristal FROM pedidos WHERE id_pedido = $id_pedido";
        $result_pedido = mysqli_query($con, $sql_pedido);
        $pedido = mysqli_fetch_assoc($result_pedido);
        
        // Devolver la montura al inventario
        $sql_montura = "UPDATE monturas SET cantidad = cantidad + 1 WHERE id_montura = {$pedido['id_montura']}";
        mysqli_query($con, $sql_montura);
        
        // Devolver el cristal al inventario
        $sql_cristal = "UPDATE cristales SET cantidad = cantidad + 1 WHERE id_cristal = {$pedido['id_cristal']}";
        mysqli_query($con, $sql_cristal);
        
        // Eliminar el pedido
        $sql_eliminar = "DELETE FROM pedidos WHERE id_pedido = $id_pedido";
        mysqli_query($con, $sql_eliminar);
        
        // Confirmar transacción
        mysqli_commit($con);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($con);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error al eliminar el pedido: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Método no permitido'
    ]);
}
?>