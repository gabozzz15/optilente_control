<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_empleado']) || !isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include "./inc/conexionbd.php";
$con = connection();

$id_empleado = $_SESSION['id_empleado'];
$usuario = $_SESSION['usuario'];

// Verificar si ya tiene preguntas de seguridad configuradas
$sql = "SELECT * FROM preguntas_seguridad WHERE id_empleado = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
$preguntas_actuales = [];
while ($row = $result->fetch_assoc()) {
    $preguntas_actuales[] = $row;
}
$tiene_preguntas = count($preguntas_actuales) > 0;
$num_preguntas = count($preguntas_actuales);

// Lista de preguntas disponibles
$preguntas_disponibles = [
    '¿Cuál es el nombre de tu primera mascota?',
    '¿En qué ciudad naciste?',
    '¿Cuál es tu color favorito?',
    '¿Cuál fue el nombre de tu primer colegio?',
    '¿Cuál es el segundo nombre de tu madre?',
    '¿Cuál es tu comida favorita?',
    '¿Cuál es el nombre de tu mejor amigo de la infancia?',
    '¿Cuál fue tu primer trabajo?',
    '¿Cuál es el nombre de la calle donde creciste?',
    '¿Cuál es tu película favorita?'
];

// Procesar el formulario si se envió
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una eliminación
    if (isset($_POST['eliminar_pregunta'])) {
        $id_pregunta = $_POST['id_pregunta'];
        
        $sql = "DELETE FROM preguntas_seguridad WHERE id_pregunta = ? AND id_empleado = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ii", $id_pregunta, $id_empleado);
        
        if ($stmt->execute()) {
            $mensaje = "Pregunta de seguridad eliminada correctamente";
            // Actualizar la lista de preguntas
            $sql = "SELECT * FROM preguntas_seguridad WHERE id_empleado = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $id_empleado);
            $stmt->execute();
            $result = $stmt->get_result();
            $preguntas_actuales = [];
            while ($row = $result->fetch_assoc()) {
                $preguntas_actuales[] = $row;
            }
            $tiene_preguntas = count($preguntas_actuales) > 0;
            $num_preguntas = count($preguntas_actuales);
        } else {
            $error = "Error al eliminar la pregunta de seguridad: " . $con->error;
        }
    }
    // Verificar si es un cambio de pregunta
    elseif (isset($_POST['cambiar_pregunta']) && isset($_POST['id_pregunta_cambiar']) && isset($_POST['pregunta_cambiar']) && isset($_POST['respuesta_cambiar'])) {
        $id_pregunta = $_POST['id_pregunta_cambiar'];
        $nueva_pregunta = trim($_POST['pregunta_cambiar']);
        $nueva_respuesta = trim(strtolower($_POST['respuesta_cambiar'])); // Guardar en minúsculas para comparación no sensible a mayúsculas
        
        if (empty($nueva_pregunta) || empty($nueva_respuesta)) {
            $error = "Todos los campos son obligatorios para cambiar la pregunta";
        } else {
            // Verificar si la nueva pregunta ya existe para este usuario (excepto la que estamos cambiando)
            $pregunta_existe = false;
            foreach ($preguntas_actuales as $p) {
                if ($p['pregunta'] === $nueva_pregunta && $p['id_pregunta'] != $id_pregunta) {
                    $pregunta_existe = true;
                    break;
                }
            }
            
            if ($pregunta_existe) {
                $error = "Ya has configurado esta pregunta. Por favor, elige otra diferente.";
            } else {
                // Actualizar la pregunta existente
                $sql = "UPDATE preguntas_seguridad SET pregunta = ?, respuesta = ? WHERE id_pregunta = ? AND id_empleado = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssii", $nueva_pregunta, $nueva_respuesta, $id_pregunta, $id_empleado);
                
                if ($stmt->execute()) {
                    $mensaje = "Pregunta de seguridad actualizada correctamente";
                    
                    // Actualizar la lista de preguntas
                    $sql = "SELECT * FROM preguntas_seguridad WHERE id_empleado = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("i", $id_empleado);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $preguntas_actuales = [];
                    while ($row = $result->fetch_assoc()) {
                        $preguntas_actuales[] = $row;
                    }
                    $tiene_preguntas = count($preguntas_actuales) > 0;
                    $num_preguntas = count($preguntas_actuales);
                } else {
                    $error = "Error al actualizar la pregunta de seguridad: " . $con->error;
                }
            }
        }
    }
    // Si es una adición de pregunta
    elseif (isset($_POST['pregunta']) && isset($_POST['respuesta'])) {
        $pregunta = trim($_POST['pregunta']);
        $respuesta = trim(strtolower($_POST['respuesta'])); // Guardar en minúsculas para comparación no sensible a mayúsculas
        
        if (empty($pregunta) || empty($respuesta)) {
            $error = "Todos los campos son obligatorios";
        } elseif ($num_preguntas >= 4) {
            $error = "Ya has configurado el máximo de 4 preguntas de seguridad. Elimina alguna si deseas cambiarla.";
        } else {
            // Verificar si la pregunta ya existe para este usuario
            $pregunta_existe = false;
            foreach ($preguntas_actuales as $p) {
                if ($p['pregunta'] === $pregunta) {
                    $pregunta_existe = true;
                    break;
                }
            }
            
            if ($pregunta_existe) {
                $error = "Ya has configurado esta pregunta. Por favor, elige otra diferente.";
            } else {
                // Insertar nueva pregunta
                $sql = "INSERT INTO preguntas_seguridad (id_empleado, pregunta, respuesta) VALUES (?, ?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("iss", $id_empleado, $pregunta, $respuesta);
                
                if ($stmt->execute()) {
                    $mensaje = "Pregunta de seguridad guardada correctamente";
                    
                    // Actualizar la lista de preguntas
                    $sql = "SELECT * FROM preguntas_seguridad WHERE id_empleado = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("i", $id_empleado);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $preguntas_actuales = [];
                    while ($row = $result->fetch_assoc()) {
                        $preguntas_actuales[] = $row;
                    }
                    $tiene_preguntas = count($preguntas_actuales) > 0;
                    $num_preguntas = count($preguntas_actuales);
                } else {
                    $error = "Error al guardar la pregunta de seguridad: " . $con->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Seguridad - Optilente 2020</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <header>
        <?php 
            if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
                // Obtener el rol del usuario
                $sql_rol = "SELECT cargo FROM empleados WHERE id_empleado = ?";
                $stmt_rol = $con->prepare($sql_rol);
                $stmt_rol->bind_param("i", $id_empleado);
                $stmt_rol->execute();
                $result_rol = $stmt_rol->get_result();
                $row_rol = $result_rol->fetch_assoc();
                $role = $row_rol['cargo'];
                
                if ($role == 'gerente'){
                    include "./inc/navbarLogginGerente.php";
                }
                elseif($role == 'empleado'){
                    include "./inc/navbarLoggin.php";
                }               
            }else{
                include "./inc/navbar.php";
            }  
        ?>
    </header>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if ($num_preguntas < 4): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Configuración Obligatoria</strong>
                    <p class="mt-2">Debes configurar 4 preguntas de seguridad para poder recuperar tu contraseña en caso de olvido. 
                    Cada pregunta debe ser única y la respuesta debe ser fácil de recordar para ti, pero difícil de adivinar para otros.</p>
                    <hr>
                    <p class="mb-0">Preguntas configuradas: <?php echo $num_preguntas; ?>/4</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Configurar Preguntas de Seguridad</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <p class="mb-0">
                                <strong><i class="fas fa-info-circle me-2"></i>Importante:</strong> 
                                Debes configurar 4 preguntas de seguridad. No podrás acceder a otras funciones del sistema hasta completar esta configuración.
                            </p>
                        </div>
                        
                        <!-- Mostrar preguntas actuales -->
                        <?php if ($tiene_preguntas): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Preguntas de seguridad configuradas (<?php echo $num_preguntas; ?>/4)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Pregunta</th>
                                                <th class="text-end">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preguntas_actuales as $pregunta): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($pregunta['pregunta']); ?></td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#cambiarPregunta<?php echo $pregunta['id_pregunta']; ?>">
                                                        <i class="fas fa-edit"></i> Cambiar
                                                    </button>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="id_pregunta" value="<?php echo $pregunta['id_pregunta']; ?>">
                                                        <button type="submit" name="eliminar_pregunta" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta pregunta de seguridad?');">
                                                            <i class="fas fa-trash-alt"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal para cambiar pregunta -->
                                            <div class="modal fade" id="cambiarPregunta<?php echo $pregunta['id_pregunta']; ?>" tabindex="-1" aria-labelledby="cambiarPreguntaLabel<?php echo $pregunta['id_pregunta']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title" id="cambiarPreguntaLabel<?php echo $pregunta['id_pregunta']; ?>">
                                                                <i class="fas fa-edit me-2"></i>Cambiar Pregunta de Seguridad
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_pregunta_cambiar" value="<?php echo $pregunta['id_pregunta']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label for="pregunta_cambiar<?php echo $pregunta['id_pregunta']; ?>" class="form-label">Nueva Pregunta de Seguridad</label>
                                                                    <select class="form-select" id="pregunta_cambiar<?php echo $pregunta['id_pregunta']; ?>" name="pregunta_cambiar" required>
                                                                        <option value="" disabled>Selecciona una nueva pregunta</option>
                                                                        <?php 
                                                                        // Mostrar todas las preguntas disponibles
                                                                        foreach ($preguntas_disponibles as $pregunta_disponible): 
                                                                            // Si es la pregunta actual o no está seleccionada por otro, mostrarla
                                                                            $es_actual = ($pregunta_disponible === $pregunta['pregunta']);
                                                                            $esta_seleccionada = in_array($pregunta_disponible, array_column($preguntas_actuales, 'pregunta'));
                                                                            
                                                                            // Mostrar si es la actual o si no está seleccionada por otra pregunta
                                                                            if ($es_actual || !$esta_seleccionada):
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($pregunta_disponible); ?>" 
                                                                            <?php echo ($es_actual ? 'selected' : ''); ?>>
                                                                            <?php echo htmlspecialchars($pregunta_disponible); ?>
                                                                        </option>
                                                                        <?php 
                                                                            endif;
                                                                        endforeach; 
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="respuesta_cambiar<?php echo $pregunta['id_pregunta']; ?>" class="form-label">Nueva Respuesta</label>
                                                                    <input type="text" class="form-control" id="respuesta_cambiar<?php echo $pregunta['id_pregunta']; ?>" name="respuesta_cambiar" placeholder="Ingrese la nueva respuesta" required>
                                                                    <div class="form-text">La respuesta no distingue entre mayúsculas y minúsculas.</div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="fas fa-times me-2"></i>Cancelar
                                                                </button>
                                                                <button type="submit" name="cambiar_pregunta" class="btn btn-warning" onclick="return confirm('¿Estás seguro de cambiar esta pregunta de seguridad?');">
                                                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Formulario para añadir nueva pregunta -->
                        <?php if ($num_preguntas < 4): ?>
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Añadir Pregunta de Seguridad <?php echo $num_preguntas + 1; ?>/4</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="pregunta" class="form-label">Pregunta de Seguridad</label>
                                        <select class="form-select" id="pregunta" name="pregunta" required>
                                            <option value="" disabled selected>Selecciona una pregunta</option>
                                            <?php 
                                            // Mostrar solo las preguntas que no han sido seleccionadas
                                            $preguntas_seleccionadas = array_column($preguntas_actuales, 'pregunta');
                                            foreach ($preguntas_disponibles as $pregunta_disponible): 
                                                if (!in_array($pregunta_disponible, $preguntas_seleccionadas)):
                                            ?>
                                            <option value="<?php echo htmlspecialchars($pregunta_disponible); ?>"><?php echo htmlspecialchars($pregunta_disponible); ?></option>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="respuesta" class="form-label">Respuesta</label>
                                        <input type="text" class="form-control" id="respuesta" name="respuesta" required>
                                        <div class="form-text">La respuesta no distingue entre mayúsculas y minúsculas.</div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus-circle me-2"></i>Añadir Pregunta de Seguridad
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        
                        <div class="d-grid mt-4">
                            <a href="perfil.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "./inc/footer.php"; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>