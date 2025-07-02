<?php
session_start();
include "../inc/conexionbd.php";
$con = connection();

// Verificar stock disponible
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verificar_stock'])) {
    $id_montura = $_POST['id_montura'];
    $id_cristal1 = $_POST['id_cristal1'];
    $id_cristal2 = $_POST['id_cristal2'];
    $cantidad = intval($_POST['cantidad']);
    
    // Verificar stock de montura
    $sql = "SELECT cantidad FROM monturas WHERE id_montura = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_montura);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $montura = mysqli_fetch_assoc($result);
    
    // Verificar stock de cristal 1
    $sql = "SELECT cantidad FROM cristales WHERE id_cristal = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_cristal1);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cristal1 = mysqli_fetch_assoc($result);
    
    // Verificar stock de cristal 2
    $sql = "SELECT cantidad FROM cristales WHERE id_cristal = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_cristal2);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cristal2 = mysqli_fetch_assoc($result);
    
    $response = array();
    
    // Verificar si hay suficiente stock para la cantidad solicitada
    if ($montura['cantidad'] < $cantidad) {
        $response['success'] = false;
        $response['message'] = "No hay suficientes monturas en stock. Disponibles: " . $montura['cantidad'];
    } elseif ($cristal1['cantidad'] < $cantidad) {
        $response['success'] = false;
        $response['message'] = "No hay suficientes cristales derechos en stock. Disponibles: " . $cristal1['cantidad'];
    } elseif ($cristal2['cantidad'] < $cantidad) {
        $response['success'] = false;
        $response['message'] = "No hay suficientes cristales izquierdos en stock. Disponibles: " . $cristal2['cantidad'];
    } else {
        $response['success'] = true;
        $response['message'] = "Stock disponible";
    }
    
    // Devolver respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Función para crear una nueva prescripción
function crearNuevaPrescripcion($con, $id_cliente, $od_esfera, $od_cilindro, $od_eje, 
                               $oi_esfera, $oi_cilindro, $oi_eje, $adicion, $altura_pupilar, 
                               $distancia_pupilar, $observacion) {
    $fecha_actual = date('Y-m-d');
    $sql = "INSERT INTO prescripcion (id_cliente, fecha_emision, OD_esfera, OD_cilindro, OD_eje, 
                                     OI_esfera, OI_cilindro, OI_eje, adicion, altura_pupilar, 
                                     distancia_pupilar, observacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isssssssssss", 
        $id_cliente, $fecha_actual, $od_esfera, $od_cilindro, $od_eje, 
        $oi_esfera, $oi_cilindro, $oi_eje, $adicion, $altura_pupilar, 
        $distancia_pupilar, $observacion
    );
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($con);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generar_pedido'])) {
    $idUser = $_SESSION['id_empleado'];
    
    // Obtener datos del formulario
    $cedula_cliente = $_POST['cedula_cliente'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $apellido_cliente = isset($_POST['apellido_cliente']) ? $_POST['apellido_cliente'] : '';
    $telefono_cliente = $_POST['telefono_cliente'];
    $correo_cliente = $_POST['correo_cliente'];
    $id_montura = $_POST['montura'];
    $id_cristal1 = $_POST['cristal1'];
    $id_cristal2 = $_POST['cristal2'];
    $cantidad_lentes = isset($_POST['cantidad_lentes']) ? intval($_POST['cantidad_lentes']) : 1;
    
    // Validar cantidad
    if ($cantidad_lentes < 1) {
        $cantidad_lentes = 1;
    } elseif ($cantidad_lentes > 10) {
        $cantidad_lentes = 10;
    }
    
    // Datos de prescripción
    $od_esfera = $_POST['od_esfera'];
    $od_cilindro = $_POST['od_cilindro'];
    $od_eje = $_POST['od_eje'];
    $oi_esfera = $_POST['oi_esfera'];
    $oi_cilindro = $_POST['oi_cilindro'];
    $oi_eje = $_POST['oi_eje'];
    $adicion = isset($_POST['adicion']) ? $_POST['adicion'] : null;
    $altura_pupilar = isset($_POST['altura_pupilar']) ? $_POST['altura_pupilar'] : null;
    $distancia_pupilar = isset($_POST['distancia_pupilar']) ? $_POST['distancia_pupilar'] : null;
    $observacion = isset($_POST['observacion']) ? $_POST['observacion'] : null;
    
    try {
        // Iniciar transacción
        mysqli_begin_transaction($con);
        
        // Verificar stock disponible para la cantidad solicitada
        $sql = "SELECT cantidad FROM monturas WHERE id_montura = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_montura);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $montura = mysqli_fetch_assoc($result);
        
        $sql = "SELECT cantidad FROM cristales WHERE id_cristal = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_cristal1);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cristal1 = mysqli_fetch_assoc($result);
        
        $sql = "SELECT cantidad FROM cristales WHERE id_cristal = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_cristal2);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cristal2 = mysqli_fetch_assoc($result);
        
        // Verificar si hay suficiente stock
        if ($montura['cantidad'] < $cantidad_lentes) {
            throw new Exception("No hay suficientes monturas en stock. Disponibles: " . $montura['cantidad']);
        }
        
        if ($cristal1['cantidad'] < $cantidad_lentes) {
            throw new Exception("No hay suficientes cristales derechos en stock. Disponibles: " . $cristal1['cantidad']);
        }
        
        if ($cristal2['cantidad'] < $cantidad_lentes) {
            throw new Exception("No hay suficientes cristales izquierdos en stock. Disponibles: " . $cristal2['cantidad']);
        }
        
        // Verificar si el cliente ya existe
        $sql = "SELECT id_cliente FROM datos_clientes WHERE cedula_cliente = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $cedula_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Manejar correo vacío
        $correo_cliente = !empty($correo_cliente) ? $correo_cliente : '';
        
        if (mysqli_num_rows($result) == 0) {
            // Insertar nuevo cliente
            $sql = "INSERT INTO datos_clientes (cedula_cliente, nombre, apellido, num_telefono, correo) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", 
                $cedula_cliente, 
                $nombre_cliente,
                $apellido_cliente,
                $telefono_cliente, 
                $correo_cliente
            );
            mysqli_stmt_execute($stmt);
            $id_cliente = mysqli_insert_id($con);
        } else {
            // Obtener cliente existente
            $row = mysqli_fetch_assoc($result);
            $id_cliente = $row['id_cliente'];
        }
        
        // Verificar si se está utilizando una prescripción existente
        if (isset($_POST['id_prescripcion_existente']) && !empty($_POST['id_prescripcion_existente'])) {
            $id_prescripcion = $_POST['id_prescripcion_existente'];
            
            // Verificar que la prescripción pertenezca al cliente
            $sql = "SELECT id_prescripcion FROM prescripcion WHERE id_prescripcion = ? AND id_cliente = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $id_prescripcion, $id_cliente);
            mysqli_stmt_execute($stmt);
            $result_prescripcion = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result_prescripcion) == 0) {
                // La prescripción no pertenece al cliente, crear una nueva
                $id_prescripcion = crearNuevaPrescripcion($con, $id_cliente, $od_esfera, $od_cilindro, $od_eje, 
                                                         $oi_esfera, $oi_cilindro, $oi_eje, $adicion, $altura_pupilar, 
                                                         $distancia_pupilar, $observacion);
            }
        } else {
            // Crear una nueva prescripción
            $id_prescripcion = crearNuevaPrescripcion($con, $id_cliente, $od_esfera, $od_cilindro, $od_eje, 
                                                     $oi_esfera, $oi_cilindro, $oi_eje, $adicion, $altura_pupilar, 
                                                     $distancia_pupilar, $observacion);
        }
        
        // Crear el pedido
        $fecha_actual = date('Y-m-d');
        $estado = 'no disponible';
        
        $sql = "INSERT INTO pedidos (id_empleado, id_montura, id_cristal1, id_cristal2, 
                                   cedula_cliente, id_prescripcion, fecha_pedido, estado_pedido, cantidad) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iiisisssi", 
            $idUser, $id_montura, $id_cristal1, $id_cristal2, 
            $cedula_cliente, $id_prescripcion, $fecha_actual, $estado, $cantidad_lentes
        );
        mysqli_stmt_execute($stmt);
        
        // Actualizar inventario y contador de ventas para montura
        $sql = "UPDATE monturas SET cantidad = cantidad - ?, contador_venta = contador_venta + ? WHERE id_montura = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $cantidad_lentes, $cantidad_lentes, $id_montura);
        mysqli_stmt_execute($stmt);
        
        // Actualizar inventario y contador de ventas para cristal derecho
        $sql = "UPDATE cristales SET cantidad = cantidad - ?, contador_venta = contador_venta + ? WHERE id_cristal = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $cantidad_lentes, $cantidad_lentes, $id_cristal1);
        mysqli_stmt_execute($stmt);
        
        // Actualizar inventario y contador de ventas para cristal izquierdo
        $sql = "UPDATE cristales SET cantidad = cantidad - ?, contador_venta = contador_venta + ? WHERE id_cristal = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $cantidad_lentes, $cantidad_lentes, $id_cristal2);
        mysqli_stmt_execute($stmt);
        
        // Confirmar transacción
        mysqli_commit($con);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Éxito!',
            'message' => 'Pedido generado exitosamente'
        ];
        
    } catch (Exception $e) {
        // Revertir cambios si hay error
        mysqli_rollback($con);
        
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Error al generar el pedido: ' . $e->getMessage()
        ];
    }
    
    // Redireccionar de vuelta a la página de pedidos
    header("Location: ../pedidos.php");
    exit();
}
?>