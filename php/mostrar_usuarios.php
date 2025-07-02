<?php
// Si este archivo se llama directamente a través de HTTP, configurar el encabezado
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
}

// Incluir conexión a la base de datos si aún no está incluida
if (!function_exists('connection')) {
    require_once __DIR__ . "/../inc/conexionbd.php";
}
$con = connection();

// Parámetros de búsqueda opcionales
$busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($con, $_GET['busqueda']) : '';

// Consulta base de usuarios
$query_usuarios = "SELECT id_empleado, CONCAT(nombre_empleado, ' ', apellido_empleado) as nombre_completo, cedula_empleado, usuario, correo, num_telefono, cargo, estado_empleado FROM empleados WHERE 1=1";

// Agregar filtro de búsqueda si existe
if (!empty($busqueda)) {
    $query_usuarios .= " AND (CONCAT(nombre_empleado, ' ', apellido_empleado) LIKE '%$busqueda%' 
                            OR cedula_empleado LIKE '%$busqueda%' 
                            OR usuario LIKE '%$busqueda%')";
}

// Ejecutar consulta
$result_usuarios = mysqli_query($con, $query_usuarios);

if (!$result_usuarios) {
    $error_message = 'Error en la consulta: ' . mysqli_error($con);
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        echo json_encode([
            'success' => false,
            'message' => $error_message,
            'usuarios' => []
        ]);
    } else {
        $usuarios = [];
    }
    
    // No cerramos la conexión si este archivo se incluye en otro
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        mysqli_close($con);
    }
    
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        exit();
    }
}

// Preparar array de usuarios
$usuarios = [];
while ($row = mysqli_fetch_assoc($result_usuarios)) {
    // Formatear datos si es necesario
    $row['cargo'] = ucfirst($row['cargo']);
    $usuarios[] = $row;
}

// Si este archivo se llama directamente, devolver JSON
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios,
        'total' => count($usuarios)
    ]);
    
    mysqli_close($con);
} else {
    // Si se incluye, generar la tabla HTML
    function generar_tabla_usuarios($usuarios) {
        $html = '
        <!-- Tabla de usuarios -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Cédula</th>
                                <th>Usuario</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        if (count($usuarios) > 0) {
            foreach ($usuarios as $usuario) {
                $html .= '
                <tr>
                    <td>' . htmlspecialchars($usuario['id_empleado']) . '</td>
                    <td>' . htmlspecialchars($usuario['nombre_completo']) . '</td>
                    <td>' . htmlspecialchars($usuario['cedula_empleado']) . '</td>
                    <td>' . htmlspecialchars($usuario['usuario']) . '</td>
                    <td>' . htmlspecialchars($usuario['cargo']) . '</td>
                    <td>' . htmlspecialchars($usuario['estado_empleado']) . '</td>
                    <td>
                        <select class="form-select form-select-sm estado-empleado" 
                                data-id="' . $usuario['id_empleado'] . '">
                            <option value="activo" ' . ($usuario['estado_empleado'] == 'activo' ? 'selected' : '') . '>Activo</option>
                            <option value="inactivo" ' . ($usuario['estado_empleado'] == 'inactivo' ? 'selected' : '') . '>Inactivo</option>
                        </select>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info editar-usuario" 
                                    data-id="' . $usuario['id_empleado'] . '"
                                    data-nombre="' . htmlspecialchars($usuario['nombre_completo']) . '"
                                    data-cedula="' . htmlspecialchars($usuario['cedula_empleado']) . '"
                                    data-correo="' . htmlspecialchars($usuario['correo']) . '"
                                    data-telefono="' . htmlspecialchars($usuario['num_telefono']) . '"
                                    data-usuario="' . htmlspecialchars($usuario['usuario']) . '"
                                    data-cargo="' . htmlspecialchars($usuario['cargo']) . '">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger eliminar-usuario" 
                                    data-id="' . $usuario['id_empleado'] . '"
                                    data-nombre="' . htmlspecialchars($usuario['nombre_completo']) . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" class="text-center">No se encontraron usuarios</td></tr>';
        }
        
        $html .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>';
        
        return $html;
    }
}
?>