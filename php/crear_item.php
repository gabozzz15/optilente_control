<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo incluir la conexión si no está ya definida
if (!isset($con)) {
    require_once __DIR__ . "/../inc/conexionbd.php";
    $con = connection();
}

// Verificar si la función ya está definida antes de declararla
if (!function_exists('createItem')) {
    function createItem($con, $tipo, $marca, $materialOAumento, $precio, $cantidad, $tipoCristal = null, $materialCristal = null, $proveedores = []) {
        $tabla = ($tipo === "montura") ? "monturas" : "cristales";
        
        // Definir columnas y valores según el tipo de item
        if ($tipo === "montura") {
            $columna = "material";
            $query = "SELECT * FROM $tabla WHERE marca = ? AND $columna = ?";
            $insertQuery = "INSERT INTO $tabla (marca, $columna, precio, cantidad) VALUES (?, ?, ?, ?)";
            $updateQuery = "UPDATE $tabla SET cantidad = ? WHERE id_montura = ?";
        } else {
            $query = "SELECT * FROM $tabla WHERE marca = ? AND tipo_cristal = ? AND material_cristal = ?";
            $insertQuery = "INSERT INTO $tabla (marca, tipo_cristal, material_cristal, precio, cantidad) VALUES (?, ?, ?, ?, ?)";
            $updateQuery = "UPDATE $tabla SET cantidad = ? WHERE id_cristal = ?";
        }

        // Preparar y ejecutar consulta de verificación
        $stmt = $con->prepare($query);
        if ($tipo === "montura") {
            $stmt->bind_param("ss", $marca, $materialOAumento);
        } else {
            $stmt->bind_param("sss", $marca, $tipoCristal, $materialCristal);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row['precio'] != $precio) {
                // Guardar los datos en sesión para el formulario de confirmación
                $_SESSION['temp_data'] = [
                    'tipo' => $tipo,
                    'marca' => $marca,
                    'materialOAumento' => $materialOAumento,
                    'precio_nuevo' => $precio,
                    'precio_actual' => $row['precio'],
                    'cantidad' => $cantidad,
                    'id_item' => $tipo === 'montura' ? $row['id_montura'] : $row['id_cristal'],
                    'tipo_cristal' => $tipoCristal,
                    'material_cristal' => $materialCristal,
                    'proveedores' => $proveedores
                ];
                
                // Redirigir a página de confirmación
                header("Location: ../optilente_control/confirmar_precio.php");
                exit();
            }

            // Sumar cantidad si el registro ya existe y tiene el mismo precio
            $newCantidad = $row['cantidad'] + $cantidad;
            $updateStmt = $con->prepare($updateQuery);
            $updateStmt->bind_param("ii", $newCantidad, $row[$tipo === 'montura' ? 'id_montura' : 'id_cristal']);
            $updateStmt->execute();

            // Asociar proveedores si se proporcionan
            if (!empty($proveedores)) {
                $tabla_intermedia = ($tipo === 'montura') ? 'monturas_proveedores' : 'cristales_proveedores';
                $id_campo = ($tipo === 'montura') ? 'id_montura' : 'id_cristal';
                $stmt_proveedor = $con->prepare("INSERT IGNORE INTO $tabla_intermedia ($id_campo, id_proveedor) VALUES (?, ?)");
                
                foreach ($proveedores as $id_proveedor) {
                    $stmt_proveedor->bind_param("ii", $row[$id_campo], $id_proveedor);
                    $stmt_proveedor->execute();
                }
            }

            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Actualizado!',
                'message' => 'Registro actualizado: cantidades sumadas correctamente'
            ];
        } else {
            // Insertar nuevo registro si no existe
            $insertStmt = $con->prepare($insertQuery);
            if ($tipo === "montura") {
                $insertStmt->bind_param("ssdi", $marca, $materialOAumento, $precio, $cantidad);
            } else {
                $insertStmt->bind_param("sssdi", $marca, $tipoCristal, $materialCristal, $precio, $cantidad);
            }
            $insertStmt->execute();
            
            // Obtener el ID del item recién creado
            $id_item = $con->insert_id;

            // Asociar proveedores si se proporcionan
            if (!empty($proveedores)) {
                $tabla_intermedia = ($tipo === 'montura') ? 'monturas_proveedores' : 'cristales_proveedores';
                $id_campo = ($tipo === 'montura') ? 'id_montura' : 'id_cristal';
                $stmt_proveedor = $con->prepare("INSERT INTO $tabla_intermedia ($id_campo, id_proveedor) VALUES (?, ?)");
                
                foreach ($proveedores as $id_proveedor) {
                    $stmt_proveedor->bind_param("ii", $id_item, $id_proveedor);
                    $stmt_proveedor->execute();
                }
            }

            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Creado!',
                'message' => 'Nuevo registro creado exitosamente'
            ];
        }
    }
}

// Procesar la creación de un nuevo item
if (isset($_POST['submit'])) {
    $tipo = $_POST['tipo'];
    $marca = $_POST['marca'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $proveedores = isset($_POST['proveedores']) ? $_POST['proveedores'] : [];

    if ($tipo === 'montura') {
        $materialOAumento = $_POST['materialOAumento'];
        createItem($con, $tipo, $marca, $materialOAumento, $precio, $cantidad, $proveedores);
    } else {
        $tipoCristal = $_POST['tipo_cristal'];
        $materialCristal = $_POST['material_cristal'];
        createItem($con, $tipo, $marca, $precio, $cantidad, $tipoCristal, $materialCristal, $proveedores);
    }

    // Redireccionar de vuelta a inventario.php si no hubo conflicto de precio
    if (!isset($_SESSION['temp_data'])) {
        header("Location: ../optilente_control/inventario.php");
        exit();
    }
}

// Procesar la respuesta de confirmación
if (isset($_POST['confirm_action'])) {
    if (!isset($_SESSION['temp_data'])) {
        header("Location: ../optilente_control/inventario.php");
        exit();
    }

    $data = $_SESSION['temp_data'];
    $tabla = ($data['tipo'] === "montura") ? "monturas" : "cristales";
    
    if ($_POST['confirm_action'] === 'update') {
        // Actualizar precio y sumar cantidad
        if ($data['tipo'] === 'montura') {
            $updateQuery = "UPDATE $tabla SET precio = ?, cantidad = cantidad + ? WHERE id_montura = ?";
            $stmt = $con->prepare($updateQuery);
            $stmt->bind_param("dii", $data['precio_nuevo'], $data['cantidad'], $data['id_item']);
        } else {
            $updateQuery = "UPDATE $tabla SET precio = ?, cantidad = cantidad + ? WHERE id_cristal = ?";
            $stmt = $con->prepare($updateQuery);
            $stmt->bind_param("dii", $data['precio_nuevo'], $data['cantidad'], $data['id_item']);
        }
        $stmt->execute();

        // Asociar proveedores si se proporcionan
        if (!empty($data['proveedores'])) {
            $tabla_intermedia = ($data['tipo'] === 'montura') ? 'monturas_proveedores' : 'cristales_proveedores';
            $id_campo = ($data['tipo'] === 'montura') ? 'id_montura' : 'id_cristal';
            $stmt_proveedor = $con->prepare("INSERT IGNORE INTO $tabla_intermedia ($id_campo, id_proveedor) VALUES (?, ?)");
            
            foreach ($data['proveedores'] as $id_proveedor) {
                $stmt_proveedor->bind_param("ii", $data['id_item'], $id_proveedor);
                $stmt_proveedor->execute();
            }
        }

        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Actualizado!',
            'message' => 'Precio actualizado y cantidad sumada exitosamente.'
        ];
    } else {
        // Solo sumar cantidad manteniendo el precio original
        if ($data['tipo'] === 'montura') {
            $updateQuery = "UPDATE $tabla SET cantidad = cantidad + ? WHERE id_montura = ?";
            $stmt = $con->prepare($updateQuery);
            $stmt->bind_param("ii", $data['cantidad'], $data['id_item']);
        } else {
            $updateQuery = "UPDATE $tabla SET cantidad = cantidad + ? WHERE id_cristal = ?";
            $stmt = $con->prepare($updateQuery);
            $stmt->bind_param("ii", $data['cantidad'], $data['id_item']);
        }
        $stmt->execute();

        // Asociar proveedores si se proporcionan
        if (!empty($data['proveedores'])) {
            $tabla_intermedia = ($data['tipo'] === 'montura') ? 'monturas_proveedores' : 'cristales_proveedores';
            $id_campo = ($data['tipo'] === 'montura') ? 'id_montura' : 'id_cristal';
            $stmt_proveedor = $con->prepare("INSERT IGNORE INTO $tabla_intermedia ($id_campo, id_proveedor) VALUES (?, ?)");
            
            foreach ($data['proveedores'] as $id_proveedor) {
                $stmt_proveedor->bind_param("ii", $data['id_item'], $id_proveedor);
                $stmt_proveedor->execute();
            }
        }

        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Actualizado!',
            'message' => 'Cantidad sumada exitosamente manteniendo el precio original.'
        ];
    }

    // Limpiar datos temporales
    unset($_SESSION['temp_data']);
    
    // Redireccionar de vuelta a inventario
    header("Location: ../optilente_control/inventario.php");
    exit();
}
?>