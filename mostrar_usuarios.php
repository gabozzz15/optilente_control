<?php
/**
 * Función para generar la tabla HTML de usuarios
 * 
 * @param array $usuarios Array de usuarios con sus datos
 * @return string HTML de la tabla de usuarios
 */
function generar_tabla_usuarios($usuarios) {
    $html = '
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
    
    foreach ($usuarios as $usuario) {
        // Determinar la clase de color para el estado
        $estadoClass = '';
        switch($usuario['estado_empleado']) {
            case 'activo':
                $estadoClass = 'success';
                break;
            case 'inactivo':
                $estadoClass = 'warning';
                break;
            case 'retirado':
                $estadoClass = 'danger';
                break;
            default:
                $estadoClass = 'secondary';
        }
        
        $html .= '
        <tr>
            <td>' . htmlspecialchars($usuario['id_empleado']) . '</td>
            <td>' . htmlspecialchars($usuario['nombre_completo']) . '</td>
            <td>' . htmlspecialchars($usuario['cedula_empleado']) . '</td>
            <td>' . htmlspecialchars($usuario['usuario']) . '</td>
            <td>' . ucfirst(htmlspecialchars($usuario['cargo'])) . '</td>
            <td>
                <select class="form-select form-select-sm estado-empleado" 
                        data-id="' . $usuario['id_empleado'] . '" 
                        data-original-estado="' . htmlspecialchars($usuario['estado_empleado']) . '">
                    <option value="activo" ' . ($usuario['estado_empleado'] == 'activo' ? 'selected' : '') . '>Activo</option>
                    <option value="inactivo" ' . ($usuario['estado_empleado'] == 'inactivo' ? 'selected' : '') . '>Inactivo</option>
                    <option value="retirado" ' . ($usuario['estado_empleado'] == 'retirado' ? 'selected' : '') . '>Retirado</option>
                </select>
            </td>
            <td>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-info editar-usuario" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalEditarUsuario"
                            data-id="' . $usuario['id_empleado'] . '"
                            data-nombre="' . htmlspecialchars($usuario['nombre_completo']) . '"
                            data-cedula="' . htmlspecialchars($usuario['cedula_empleado']) . '"
                            data-correo="' . htmlspecialchars($usuario['correo']) . '"
                            data-telefono="' . htmlspecialchars($usuario['num_telefono']) . '"
                            data-usuario="' . htmlspecialchars($usuario['usuario']) . '"
                            data-cargo="' . htmlspecialchars($usuario['cargo']) . '"
                            data-estado="' . htmlspecialchars($usuario['estado_empleado']) . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar-usuario" 
                            data-id="' . $usuario['id_empleado'] . '"
                            data-nombre="' . htmlspecialchars($usuario['nombre_completo']) . '"
                            ' . ($usuario['cargo'] == 'gerente' ? 'disabled title="No se puede eliminar un Gerente"' : '') . '>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
    
    return $html;
}

// Si este archivo se llama directamente, devolver un error
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso prohibido');
}
?>