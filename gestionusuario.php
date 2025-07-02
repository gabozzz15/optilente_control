<?php
session_start();
include "./inc/conexionbd.php";
$con = connection();

$role = '';

if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])) {
    $idUser = $_SESSION['id_empleado'];
    $sql_role = "SELECT cargo FROM empleados WHERE id_empleado = '$idUser'";
    $query_role = mysqli_query($con, $sql_role);

    if ($query_role) {
        $row_role = mysqli_fetch_assoc($query_role);
        $role = $row_role['cargo'];
    }
}

// Procesar acciones de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear_usuario':
                // archivo de creación de usuario
                include_once "./php/crear_usuario.php";
                break;

            case 'actualizar_usuario':
                // archivo de actualización de usuario
                include_once "./php/actualizar_usuario.php";
                break;

            case 'eliminar_usuario':
                // archivo de eliminación de usuario
                include_once "./php/eliminar_usuario.php";
                break;
                
            case 'actualizar_estado':
                // Actualizar estado del empleado
                $id_empleado = mysqli_real_escape_string($con, $_POST['id_empleado']);
                $nuevo_estado = mysqli_real_escape_string($con, $_POST['estado']);
                
                $sql_actualizar_estado = "UPDATE empleados SET estado_empleado = '$nuevo_estado' WHERE id_empleado = '$id_empleado'";
                
                if (mysqli_query($con, $sql_actualizar_estado)) {
                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'title' => '¡Éxito!',
                        'message' => "Estado del empleado actualizado a $nuevo_estado"
                    ];
                } else {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'title' => 'Error',
                        'message' => 'No se pudo actualizar el estado del empleado'
                    ];
                }
                
                header("Location: gestionusuario.php");
                exit();
                break;
        }
    }
}

// Obtener lista de usuarios
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Incluir conexión a la base de datos y función para generar tabla
include_once "./inc/conexionbd.php";
include_once "./mostrar_usuarios.php";
$con = connection();

// Consulta base de usuarios
$query_usuarios = "SELECT id_empleado, 
                          CONCAT(nombre_empleado, ' ', apellido_empleado) as nombre_completo, 
                          cedula_empleado, 
                          usuario, 
                          correo, 
                          num_telefono, 
                          cargo,
                          estado_empleado 
                   FROM empleados 
                   WHERE estado_empleado != 'retirado'";

// Agregar filtro de búsqueda si existe
if (!empty($busqueda)) {
    $busqueda_escaped = mysqli_real_escape_string($con, $busqueda);
    $query_usuarios .= " AND (CONCAT(nombre_empleado, ' ', apellido_empleado) LIKE '%$busqueda_escaped%' 
                        OR cedula_empleado LIKE '%$busqueda_escaped%' 
                        OR usuario LIKE '%$busqueda_escaped%')";
}

// Ejecutar consulta
$result_usuarios = mysqli_query($con, $query_usuarios);

// Preparar array de usuarios
$usuarios = [];
if ($result_usuarios) {
    while ($row = mysqli_fetch_assoc($result_usuarios)) {
        $usuarios[] = $row;
    }
}

if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])) {
    if ($role == 'gerente') {
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Gestión de Usuarios</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <header>
        <?php include "./inc/navbarLogginGerente.php"; ?>
    </header>

    <section class="py-4 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h1 class="display-5 fw-bold">GESTIÓN DE USUARIOS</h1>
                    <p class="lead">Crea, Edita y Elimina Usuarios del Sistema</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>
    </section>

    <?php 
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$notification['type']}',
                    title: '{$notification['title']}',
                    text: '{$notification['message']}',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>";
    }
    ?>

    <section class="py-5">
        <div class="container">
            <!-- Barra de búsqueda -->
            <div class="row mb-4">
                <div class="col-md-6 offset-md-3">
                    <form method="GET" action="gestionusuario.php">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar usuario por nombre, cédula o usuario" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <?php echo generar_tabla_usuarios($usuarios); ?>
        </div>
    </section>

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="modalNuevoUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestionusuario.php" id="formNuevoUsuario">
                    <input type="hidden" name="accion" value="crear_usuario">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="apellido" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cédula</label>
                            <input type="text" class="form-control" name="cedula" id="nuevaCedula" pattern="[0-9]{6,10}" title="La cédula debe contener solo números y tener entre 6 y 10 dígitos" required>
                            <div class="invalid-feedback">
                                La cédula debe contener solo números y tener entre 6 y 10 dígitos.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" name="usuario" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="clave" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo</label>
                            <select class="form-select" name="cargo" required>
                                <option value="empleado">Empleado</option>
                                <option value="gerente">Gerente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="retirado">Retirado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestionusuario.php" id="formEditarUsuario">
                    <input type="hidden" name="accion" value="actualizar_usuario">
                    <input type="hidden" name="id_empleado" id="editIdEmpleado">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="editNombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="apellido" id="editApellido" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cédula</label>
                            <input type="text" class="form-control" name="cedula" id="editCedula" pattern="[0-9]{6,10}" title="La cédula debe contener solo números y tener entre 6 y 10 dígitos" required>
                            <div class="invalid-feedback">
                                La cédula debe contener solo números y tener entre 6 y 10 dígitos.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" id="editCorreo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="editTelefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="editUsuario" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" class="form-control" name="clave">
                            <small class="form-text text-muted">Dejar en blanco si no desea cambiar la contraseña</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo</label>
                            <select class="form-select" name="cargo" id="editCargo" required>
                                <option value="empleado">Empleado</option>
                                <option value="gerente">Gerente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="editEstado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="retirado">Retirado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "./inc/footer.php";?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Script para rellenar modal de edición y manejar eliminación con SweetAlert
    document.addEventListener('DOMContentLoaded', function() {
        const modalEditar = document.getElementById('modalEditarUsuario');
        const formNuevoUsuario = document.getElementById('formNuevoUsuario');
        const formEditarUsuario = document.getElementById('formEditarUsuario');
        const nuevaCedulaInput = document.getElementById('nuevaCedula');
        const editCedulaInput = document.getElementById('editCedula');

        // Validación de cédula para nuevo usuario
        if (formNuevoUsuario) {
            formNuevoUsuario.addEventListener('submit', function(event) {
                const cedula = nuevaCedulaInput.value.trim();
                
                if (cedula && !/^[0-9]{6,10}$/.test(cedula)) {
                    event.preventDefault();
                    nuevaCedulaInput.classList.add('is-invalid');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'La cédula debe contener solo números y tener entre 6 y 10 dígitos.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
            
            nuevaCedulaInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }
        
        // Validación de cédula para editar usuario
        if (formEditarUsuario) {
            formEditarUsuario.addEventListener('submit', function(event) {
                const cedula = editCedulaInput.value.trim();
                
                if (cedula && !/^[0-9]{6,10}$/.test(cedula)) {
                    event.preventDefault();
                    editCedulaInput.classList.add('is-invalid');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'La cédula debe contener solo números y tener entre 6 y 10 dígitos.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
            
            editCedulaInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }

        // Configurar modal de edición
        modalEditar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombreCompleto = button.getAttribute('data-nombre');
            const partes = nombreCompleto.split(' ');
            const nombre = partes[0];
            const apellido = partes.slice(1).join(' ');
            const cedula = button.getAttribute('data-cedula');
            const correo = button.getAttribute('data-correo');
            const telefono = button.getAttribute('data-telefono');
            const usuario = button.getAttribute('data-usuario');
            const cargo = button.getAttribute('data-cargo');
            const estado = button.getAttribute('data-estado');

            document.getElementById('editIdEmpleado').value = id;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editApellido').value = apellido;
            document.getElementById('editCedula').value = cedula;
            document.getElementById('editCorreo').value = correo;
            document.getElementById('editTelefono').value = telefono;
            document.getElementById('editUsuario').value = usuario;
            document.getElementById('editCargo').value = cargo;
            if (document.getElementById('editEstado')) {
                document.getElementById('editEstado').value = estado;
            }
        });

        // Configurar botones de eliminación con SweetAlert
        document.querySelectorAll('.eliminar-usuario').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                Swal.fire({
                    title: '¿Retirar usuario?',
                    html: `¿Está seguro que desea marcar como retirado al usuario <strong>${nombre}</strong>?<br><br>
                           <span class="text-danger"><strong>Advertencia:</strong> El usuario ya no aparecerá en la lista pero sus datos se conservarán en el sistema.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, retirar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Crear y enviar formulario para eliminar usuario
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'gestionusuario.php';
                        
                        const accionInput = document.createElement('input');
                        accionInput.type = 'hidden';
                        accionInput.name = 'accion';
                        accionInput.value = 'eliminar_usuario';
                        
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_empleado';
                        idInput.value = id;
                        
                        form.appendChild(accionInput);
                        form.appendChild(idInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
        
        // Manejar cambio de estado de empleado
        const estadoEmpleadoSelects = document.querySelectorAll('.estado-empleado');
        estadoEmpleadoSelects.forEach(select => {
            select.addEventListener('change', function() {
                const idEmpleado = this.getAttribute('data-id');
                const nuevoEstado = this.value;
                const estadoOriginal = this.getAttribute('data-original-estado');

                Swal.fire({
                    title: '¿Cambiar estado del empleado?',
                    text: `¿Está seguro que desea cambiar el estado a ${nuevoEstado}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Crear formulario para enviar el cambio de estado
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'gestionusuario.php';

                        // Campos ocultos
                        const accionInput = document.createElement('input');
                        accionInput.type = 'hidden';
                        accionInput.name = 'accion';
                        accionInput.value = 'actualizar_estado';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_empleado';
                        idInput.value = idEmpleado;

                        const estadoInput = document.createElement('input');
                        estadoInput.type = 'hidden';
                        estadoInput.name = 'estado';
                        estadoInput.value = nuevoEstado;

                        // Agregar campos al formulario
                        form.appendChild(accionInput);
                        form.appendChild(idInput);
                        form.appendChild(estadoInput);

                        // Agregar y enviar formulario
                        document.body.appendChild(form);
                        form.submit();
                    } else {
                        // Restaurar el estado original si se cancela
                        this.value = estadoOriginal;
                    }
                });
            });
        });
    });
    </script>
<?php
    } else {
        // Si no es gerente, redirigir a la página principal
        header("Location: home.php");
        exit();
    }
} else {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}
?>

