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

// Procesar acciones de proveedores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear_proveedor':
                include_once "./php/crear_proveedor.php";
                break;

            case 'actualizar_proveedor':
                include_once "./php/actualizar_proveedor.php";
                break;

            case 'eliminar_proveedor':
                include_once "./php/eliminar_proveedor.php";
                break;
        }
    }
}

// Obtener lista de proveedores
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Consulta base de proveedores
$query_proveedores = "SELECT * FROM proveedor WHERE 1=1";

// Agregar filtro de búsqueda si existe
if (!empty($busqueda)) {
    $busqueda_escaped = mysqli_real_escape_string($con, $busqueda);
    $query_proveedores .= " AND (nombre LIKE '%$busqueda_escaped%' 
                            OR rif_proveedor LIKE '%$busqueda_escaped%' 
                            OR correo LIKE '%$busqueda_escaped%')";
}

// Ejecutar consulta
$result_proveedores = mysqli_query($con, $query_proveedores);

// Preparar array de proveedores
$proveedores = [];
if ($result_proveedores) {
    while ($row = mysqli_fetch_assoc($result_proveedores)) {
        $proveedores[] = $row;
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
    <title>OPTILENTE 2020 - Gestión de Proveedores</title>
    
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
                    <h1 class="display-5 fw-bold">GESTIÓN DE PROVEEDORES</h1>
                    <p class="lead">Administra los Proveedores del Sistema</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor">
                        <i class="fas fa-plus-circle me-2"></i>Nuevo Proveedor
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
                    <form method="GET" action="gestionproveedores.php">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar proveedor por nombre, RIF o correo" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pestañas para alternar entre vista de tabla y vista detallada -->
            <ul class="nav nav-tabs mb-4" id="proveedoresTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla" type="button" role="tab" aria-controls="tabla" aria-selected="true">
                        <i class="fas fa-table me-2"></i>Vista de Tabla
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="detalle-tab" data-bs-toggle="tab" data-bs-target="#detalle" type="button" role="tab" aria-controls="detalle" aria-selected="false">
                        <i class="fas fa-th-large me-2"></i>Vista Detallada
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="proveedoresTabsContent">
                <!-- Vista de Tabla -->
                <div class="tab-pane fade show active" id="tabla" role="tabpanel" aria-labelledby="tabla-tab">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>RIF</th>
                                            <th>Nombre</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Correo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($proveedores as $proveedor): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($proveedor['id_proveedor']); ?></td>
                                            <td><?php echo htmlspecialchars($proveedor['rif_proveedor']); ?></td>
                                            <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($proveedor['direccion']); ?></td>
                                            <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($proveedor['correo']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info editar-proveedor" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalEditarProveedor"
                                                            data-id="<?php echo $proveedor['id_proveedor']; ?>"
                                                            data-rif="<?php echo htmlspecialchars($proveedor['rif_proveedor']); ?>"
                                                            data-nombre="<?php echo htmlspecialchars($proveedor['nombre']); ?>"
                                                            data-direccion="<?php echo htmlspecialchars($proveedor['direccion']); ?>"
                                                            data-telefono="<?php echo htmlspecialchars($proveedor['telefono']); ?>"
                                                            data-correo="<?php echo htmlspecialchars($proveedor['correo']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger eliminar-proveedor" 
                                                            data-id="<?php echo $proveedor['id_proveedor']; ?>"
                                                            data-nombre="<?php echo htmlspecialchars($proveedor['nombre']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Detallada -->
                <div class="tab-pane fade" id="detalle" role="tabpanel" aria-labelledby="detalle-tab">
                    <?php include "./php/mostrar_proveedores.php"; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Nuevo Proveedor -->
    <div class="modal fade" id="modalNuevoProveedor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Crear Nuevo Proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestionproveedores.php" id="formNuevoProveedor">
                    <input type="hidden" name="accion" value="crear_proveedor">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">RIF</label>
                            <input type="text" class="form-control" name="rif" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div class="modal fade" id="modalEditarProveedor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Editar Proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestionproveedores.php" id="formEditarProveedor">
                    <input type="hidden" name="accion" value="actualizar_proveedor">
                    <input type="hidden" name="id_proveedor" id="editIdProveedor">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">RIF</label>
                            <input type="text" class="form-control" name="rif" id="editRif" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="editNombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="editDireccion" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="editTelefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" id="editCorreo" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">Actualizar Proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "./inc/footer.php";?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalEditarProveedor = document.getElementById('modalEditarProveedor');

        // Configurar modal de edición
        modalEditarProveedor.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const rif = button.getAttribute('data-rif');
            const nombre = button.getAttribute('data-nombre');
            const direccion = button.getAttribute('data-direccion');
            const telefono = button.getAttribute('data-telefono');
            const correo = button.getAttribute('data-correo');

            document.getElementById('editIdProveedor').value = id;
            document.getElementById('editRif').value = rif;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editDireccion').value = direccion;
            document.getElementById('editTelefono').value = telefono;
            document.getElementById('editCorreo').value = correo;
        });

        // Configurar botones de eliminación con SweetAlert
        document.querySelectorAll('.eliminar-proveedor').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                Swal.fire({
                    title: '¿Eliminar proveedor?',
                    html: `¿Está seguro que desea eliminar al proveedor <strong>${nombre}</strong>?<br><br>
                           <span class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Crear y enviar formulario para eliminar proveedor
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'gestionproveedores.php';
                        
                        const accionInput = document.createElement('input');
                        accionInput.type = 'hidden';
                        accionInput.name = 'accion';
                        accionInput.value = 'eliminar_proveedor';
                        
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_proveedor';
                        idInput.value = id;
                        
                        form.appendChild(accionInput);
                        form.appendChild(idInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
    </script>
</body>
</html>

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