<?php
session_start();
include "./inc/conexionbd.php";
$con = connection();

// Procesar la creación o edición de prescripción
if (isset($_POST['submit_prescripcion'])) {
    $id_cliente = $_POST['id_cliente'];
    $fecha_emision = $_POST['fecha_emision'];
    $OD_esfera = $_POST['OD_esfera'];
    $OD_cilindro = $_POST['OD_cilindro'];
    $OD_eje = $_POST['OD_eje'];
    $OI_esfera = $_POST['OI_esfera'];
    $OI_cilindro = $_POST['OI_cilindro'];
    $OI_eje = $_POST['OI_eje'];
    $adicion = $_POST['adicion'] ?: null;
    $altura_pupilar = $_POST['altura_pupilar'] ?: null;
    $distancia_pupilar = $_POST['distancia_pupilar'] ?: null;
    $observacion = $_POST['observacion'] ?: null;
    
    // Si hay un ID de prescripción, es una edición
    if (isset($_POST['id_prescripcion']) && !empty($_POST['id_prescripcion'])) {
        $id_prescripcion = $_POST['id_prescripcion'];
        $sql = "UPDATE prescripcion SET 
                id_cliente = '$id_cliente',
                fecha_emision = '$fecha_emision',
                OD_esfera = '$OD_esfera',
                OD_cilindro = '$OD_cilindro',
                OD_eje = '$OD_eje',
                OI_esfera = '$OI_esfera',
                OI_cilindro = '$OI_cilindro',
                OI_eje = '$OI_eje',
                adicion = " . ($adicion ? "'$adicion'" : "NULL") . ",
                altura_pupilar = " . ($altura_pupilar ? "'$altura_pupilar'" : "NULL") . ",
                distancia_pupilar = " . ($distancia_pupilar ? "'$distancia_pupilar'" : "NULL") . ",
                observacion = " . ($observacion ? "'$observacion'" : "NULL") . "
                WHERE id_prescripcion = '$id_prescripcion'";
        
        $mensaje = "Prescripción actualizada correctamente";
    } else {
        // Es una nueva prescripción
        $sql = "INSERT INTO prescripcion (id_cliente, fecha_emision, OD_esfera, OD_cilindro, OD_eje, OI_esfera, OI_cilindro, OI_eje, adicion, altura_pupilar, distancia_pupilar, observacion) 
                VALUES ('$id_cliente', '$fecha_emision', '$OD_esfera', '$OD_cilindro', '$OD_eje', '$OI_esfera', '$OI_cilindro', '$OI_eje', " . 
                ($adicion ? "'$adicion'" : "NULL") . ", " . 
                ($altura_pupilar ? "'$altura_pupilar'" : "NULL") . ", " . 
                ($distancia_pupilar ? "'$distancia_pupilar'" : "NULL") . ", " . 
                ($observacion ? "'$observacion'" : "NULL") . ")";
        
        $mensaje = "Prescripción registrada correctamente";
    }
    
    $result = mysqli_query($con, $sql);
    
    if ($result) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => '¡Éxito!',
            'message' => $mensaje
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Hubo un problema al procesar la prescripción: ' . mysqli_error($con)
        ];
    }
    
    // Redireccionar para evitar reenvío del formulario
    header("Location: prescripciones.php");
    exit;
}

// Eliminar prescripción
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $id_prescripcion = $_GET['eliminar'];
    
    // Verificar si la prescripción está asociada a algún pedido
    $check_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE id_prescripcion = '$id_prescripcion'";
    $result_check = mysqli_query($con, $check_pedidos);
    $pedidos_asociados = mysqli_fetch_assoc($result_check)['total'];
    
    if ($pedidos_asociados > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'No se puede eliminar la prescripción porque está asociada a uno o más pedidos'
        ];
    } else {
        $sql = "DELETE FROM prescripcion WHERE id_prescripcion = '$id_prescripcion'";
        $result = mysqli_query($con, $sql);
        
        if ($result) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => '¡Éxito!',
                'message' => 'Prescripción eliminada correctamente'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Hubo un problema al eliminar la prescripción: ' . mysqli_error($con)
            ];
        }
    }
    
    // Redireccionar para evitar reenvío del formulario
    header("Location: prescripciones.php");
    exit;
}

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
?>

<?php 
if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
    if ($role == 'gerente' || $role == 'empleado'){ 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Prescripciones</title>
    
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
        <?php 
            if ($role == 'gerente'){
                include "./inc/navbarLogginGerente.php";
            } elseif ($role == 'empleado'){
                include "./inc/navbarLoggin.php";
            } else {
                include "./inc/navbar.php";
            }  
        ?>
    </header>

    <section class="py-4 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h1 class="display-5 fw-bold">PRESCRIPCIONES</h1>
                    <p class="lead">Gestiona las prescripciones médicas de los clientes</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light btn-lg" onclick="document.getElementById('crear-prescripcion-form').style.display='block'">
                        <i class="fas fa-plus-circle me-2"></i>Nueva Prescripción
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

    <!-- Formulario para crear/editar prescripción -->
    <div id="crear-prescripcion-form" style="display: none;">
        <div class="card shadow-sm mx-auto my-4" style="max-width: 800px;">
            <div class="card-body p-4">
                <h2 class="card-title h4 mb-4" id="form-title">Registrar Nueva Prescripción</h2>
                <form id="prescripcionForm" method="POST" action="prescripciones.php">
                    <input type="hidden" name="submit_prescripcion" value="1">
                    <input type="hidden" name="id_prescripcion" id="id_prescripcion" value="">

                    <!-- Cliente -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required>
                            <option value="">Seleccione un cliente</option>
                            <?php
                            $query_clientes = "SELECT id_cliente, cedula_cliente, nombre, apellido FROM datos_clientes ORDER BY nombre, apellido";
                            $result_clientes = mysqli_query($con, $query_clientes);
                            
                            while ($cliente = mysqli_fetch_assoc($result_clientes)) {
                                echo "<option value='" . $cliente['id_cliente'] . "'>" . 
                                     htmlspecialchars($cliente['nombre'] . " " . $cliente['apellido'] . " - CI: " . $cliente['cedula_cliente']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Fecha de emisión -->
                    <div class="mb-3">
                        <label class="form-label">Fecha de Emisión</label>
                        <input type="date" class="form-control" name="fecha_emision" id="fecha_emision" required>
                    </div>

                    <!-- Datos de la prescripción -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Ojo Derecho (OD)</h5>
                            <div class="mb-3">
                                <label class="form-label">Esfera</label>
                                <input type="text" class="form-control" name="OD_esfera" id="OD_esfera" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cilindro</label>
                                <input type="text" class="form-control" name="OD_cilindro" id="OD_cilindro" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Eje</label>
                                <input type="text" class="form-control" name="OD_eje" id="OD_eje" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Ojo Izquierdo (OI)</h5>
                            <div class="mb-3">
                                <label class="form-label">Esfera</label>
                                <input type="text" class="form-control" name="OI_esfera" id="OI_esfera" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cilindro</label>
                                <input type="text" class="form-control" name="OI_cilindro" id="OI_cilindro" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Eje</label>
                                <input type="text" class="form-control" name="OI_eje" id="OI_eje" required>
                            </div>
                        </div>
                    </div>

                    <!-- Datos adicionales -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Adición</label>
                                <input type="text" class="form-control" name="adicion" id="adicion">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Altura Pupilar</label>
                                <input type="text" class="form-control" name="altura_pupilar" id="altura_pupilar">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Distancia Pupilar</label>
                                <input type="text" class="form-control" name="distancia_pupilar" id="distancia_pupilar">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observacion" id="observacion" rows="3"></textarea>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i>Guardar Prescripción
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('crear-prescripcion-form').style.display='none'">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado de prescripciones -->
    <section class="container py-5">
        <div class="card info-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Prescripciones Registradas</h3>
            </div>
            <div class="card-body">
                <?php
                // Configuración de paginación
                $items_por_pagina = 10;
                $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $offset = ($pagina - 1) * $items_por_pagina;

                // Obtener total de prescripciones
                $total_query = "SELECT COUNT(*) as total FROM prescripcion";
                $total_result = mysqli_query($con, $total_query);
                $total_items = mysqli_fetch_assoc($total_result)['total'];
                $total_paginas = ceil($total_items / $items_por_pagina);

                // Consulta para obtener prescripciones con datos del cliente
                $query = "SELECT p.*, c.nombre, c.apellido, c.cedula_cliente 
                          FROM prescripcion p 
                          JOIN datos_clientes c ON p.id_cliente = c.id_cliente 
                          ORDER BY p.fecha_emision DESC 
                          LIMIT $items_por_pagina OFFSET $offset";
                $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>OD (Esf/Cil/Eje)</th>
                                <th>OI (Esf/Cil/Eje)</th>
                                <th>Adición</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id_prescripcion']; ?></td>
                                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_emision'])); ?></td>
                                <td><?php echo htmlspecialchars($row['OD_esfera'] . ' / ' . $row['OD_cilindro'] . ' / ' . $row['OD_eje']); ?></td>
                                <td><?php echo htmlspecialchars($row['OI_esfera'] . ' / ' . $row['OI_cilindro'] . ' / ' . $row['OI_eje']); ?></td>
                                <td><?php echo $row['adicion'] ? htmlspecialchars($row['adicion']) : '-'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verPrescripcion(<?php echo $row['id_prescripcion']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="editarPrescripcion(<?php echo $row['id_prescripcion']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?php echo $row['id_prescripcion']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav aria-label="Paginación de prescripciones">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo ($pagina - 1); ?>" <?php echo ($pagina <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                Anterior
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo ($pagina + 1); ?>" <?php echo ($pagina >= $total_paginas) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                Siguiente
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php
                } else {
                    echo '<div class="alert alert-info">No hay prescripciones registradas.</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Modal para ver detalles de prescripción -->
    <div class="modal fade" id="verPrescripcionModal" tabindex="-1" aria-labelledby="verPrescripcionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verPrescripcionModalLabel">Detalles de Prescripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="prescripcionDetalles">
                    <!-- Los detalles se cargarán dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Establecer la fecha actual por defecto en el formulario
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_emision').value = today;
    });

    // Función para ver detalles de una prescripción
    async function verPrescripcion(id) {
        try {
            const response = await fetch(`./php/get_prescripcion.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const prescripcion = data.prescripcion;
                
                // Formatear los datos para mostrarlos en el modal
                let detallesHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> ${prescripcion.nombre} ${prescripcion.apellido}</p>
                            <p><strong>Cédula:</strong> ${prescripcion.cedula_cliente}</p>
                            <p><strong>Fecha de Emisión:</strong> ${new Date(prescripcion.fecha_emision).toLocaleDateString()}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Ojo Derecho (OD)</h5>
                            <p><strong>Esfera:</strong> ${prescripcion.OD_esfera}</p>
                            <p><strong>Cilindro:</strong> ${prescripcion.OD_cilindro}</p>
                            <p><strong>Eje:</strong> ${prescripcion.OD_eje}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Ojo Izquierdo (OI)</h5>
                            <p><strong>Esfera:</strong> ${prescripcion.OI_esfera}</p>
                            <p><strong>Cilindro:</strong> ${prescripcion.OI_cilindro}</p>
                            <p><strong>Eje:</strong> ${prescripcion.OI_eje}</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <p><strong>Adición:</strong> ${prescripcion.adicion || '-'}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Altura Pupilar:</strong> ${prescripcion.altura_pupilar || '-'}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Distancia Pupilar:</strong> ${prescripcion.distancia_pupilar || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h5>Observaciones:</h5>
                        <p>${prescripcion.observacion || 'Sin observaciones'}</p>
                    </div>
                `;
                
                document.getElementById('prescripcionDetalles').innerHTML = detallesHTML;
                
                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('verPrescripcionModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo cargar la información de la prescripción'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al cargar la información de la prescripción'
            });
        }
    }

    // Función para editar una prescripción
    async function editarPrescripcion(id) {
        try {
            const response = await fetch(`./php/get_prescripcion.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const prescripcion = data.prescripcion;
                
                // Cambiar el título del formulario
                document.getElementById('form-title').textContent = 'Editar Prescripción';
                
                // Llenar el formulario con los datos de la prescripción
                document.getElementById('id_prescripcion').value = prescripcion.id_prescripcion;
                document.getElementById('id_cliente').value = prescripcion.id_cliente;
                document.getElementById('fecha_emision').value = prescripcion.fecha_emision;
                document.getElementById('OD_esfera').value = prescripcion.OD_esfera;
                document.getElementById('OD_cilindro').value = prescripcion.OD_cilindro;
                document.getElementById('OD_eje').value = prescripcion.OD_eje;
                document.getElementById('OI_esfera').value = prescripcion.OI_esfera;
                document.getElementById('OI_cilindro').value = prescripcion.OI_cilindro;
                document.getElementById('OI_eje').value = prescripcion.OI_eje;
                document.getElementById('adicion').value = prescripcion.adicion || '';
                document.getElementById('altura_pupilar').value = prescripcion.altura_pupilar || '';
                document.getElementById('distancia_pupilar').value = prescripcion.distancia_pupilar || '';
                document.getElementById('observacion').value = prescripcion.observacion || '';
                
                // Mostrar el formulario
                document.getElementById('crear-prescripcion-form').style.display = 'block';
                
                // Hacer scroll al formulario
                document.getElementById('crear-prescripcion-form').scrollIntoView({ behavior: 'smooth' });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo cargar la información de la prescripción'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al cargar la información de la prescripción'
            });
        }
    }

    // Función para confirmar eliminación
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer. Si la prescripción está asociada a pedidos, no podrá ser eliminada.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `prescripciones.php?eliminar=${id}`;
            }
        });
    }
    </script>

    <?php include "./inc/footer.php"; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
    }
} else {
    echo "<script>
                    alert('No puede ingresar a esa página sin loguearse');
                    location.href = 'index.php'
        </script>";
}           
?>