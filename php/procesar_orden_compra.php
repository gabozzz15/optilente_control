<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Iniciar transacción
    mysqli_begin_transaction($con);
    
    $id_proveedor = $_POST['id_proveedor'];
    $fecha_orden = $_POST['fecha_orden'];
    $num_orden_base = $_POST['num_orden'];
    $tipos = $_POST['tipo'];
    $items = $_POST['item'];
    $cantidades = $_POST['cantidad'];
    
    // Verificar que todos los arrays tengan la misma longitud
    if (count($tipos) !== count($items) || count($items) !== count($cantidades)) {
        throw new Exception('Datos de formulario inválidos');
    }
    
    // Verificar que se haya seleccionado un proveedor
    if (empty($id_proveedor)) {
        throw new Exception('Debe seleccionar un proveedor');
    }
    
    // Procesar cada item
    for ($i = 0; $i < count($tipos); $i++) {
        $tipo = $tipos[$i];
        $item_id = $items[$i];
        $cantidad = (int)$cantidades[$i];
        
        if ($cantidad <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }
        
        // Generar número de orden único para cada item
        $num_orden = $num_orden_base + $i;
        
        if ($tipo === 'montura') {
            // Actualizar stock de monturas
            $sql_update = "UPDATE monturas SET cantidad = cantidad + ? WHERE id_montura = ?";
            $stmt_update = mysqli_prepare($con, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ii", $cantidad, $item_id);
            
            if (!mysqli_stmt_execute($stmt_update)) {
                throw new Exception('Error al actualizar el inventario de monturas');
            }
            
            // Registrar en monturas_proveedores
            $sql_registro = "INSERT INTO monturas_proveedores (id_montura, id_proveedor, cantidad, fecha_orden, num_orden_compra) 
                            VALUES (?, ?, ?, ?, ?)";
            $stmt_registro = mysqli_prepare($con, $sql_registro);
            mysqli_stmt_bind_param($stmt_registro, "iiisi", $item_id, $id_proveedor, $cantidad, $fecha_orden, $num_orden);
            
            if (!mysqli_stmt_execute($stmt_registro)) {
                throw new Exception('Error al registrar la orden de compra de monturas');
            }
            
        } elseif ($tipo === 'cristal') {
            // Actualizar stock de cristales
            $sql_update = "UPDATE cristales SET cantidad = cantidad + ? WHERE id_cristal = ?";
            $stmt_update = mysqli_prepare($con, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ii", $cantidad, $item_id);
            
            if (!mysqli_stmt_execute($stmt_update)) {
                throw new Exception('Error al actualizar el inventario de cristales');
            }
            
            // Registrar en cristales_proveedores
            $sql_registro = "INSERT INTO cristales_proveedores (id_cristal, id_proveedor, cantidad, fecha_orden, num_orden_compra) 
                            VALUES (?, ?, ?, ?, ?)";
            $stmt_registro = mysqli_prepare($con, $sql_registro);
            mysqli_stmt_bind_param($stmt_registro, "iiisi", $item_id, $id_proveedor, $cantidad, $fecha_orden, $num_orden);
            
            if (!mysqli_stmt_execute($stmt_registro)) {
                throw new Exception('Error al registrar la orden de compra de cristales');
            }
        } else {
            throw new Exception('Tipo de item inválido');
        }
    }
    
    // Confirmar transacción
    mysqli_commit($con);
    
    echo json_encode([
        'success' => true,
        'message' => 'Orden de compra procesada exitosamente'
    ]);
    
} catch (Exception $e) {
    // Revertir cambios si hay error
    mysqli_rollback($con);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>