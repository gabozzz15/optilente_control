<?php
    session_start();
    // Si ya hay una sesión iniciada, redirigir al home
    if(isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
        header('Location: home.php');
        exit();
    }
    
    include "./inc/conexionbd.php";
    $con = connection();
    
    $paso = isset($_GET['paso']) ? $_GET['paso'] : 1;
    $mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
    $error = isset($_GET['error']) ? $_GET['error'] : '';
    $usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Optilente 2020</title>
    
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
<body class="login-page-body">
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-glasses"></i>
        </div>
        
        <h2 class="text-center mb-4">Recuperar Contraseña</h2>
        <h5 class="text-center text-muted mb-4">Sistema de Gestión Optilente 2020</h5>
        
        <?php if($mensaje): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if($paso == 1): ?>
        <!-- Paso 1: Solicitar nombre de usuario -->
        <form action="php/procesar_recuperacion.php" method="POST" autocomplete="off">
            <input type="hidden" name="paso" value="1">
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="usuario" 
                           name="usuario" 
                           required 
                           placeholder="Ingrese su nombre de usuario">
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Buscar Usuario
                </button>
            </div>
        </form>
        
        <?php elseif($paso == 2): ?>
        <!-- Paso 2: Responder preguntas de seguridad -->
        <form action="php/procesar_recuperacion.php" method="POST" autocomplete="off">
            <input type="hidden" name="paso" value="2">
            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>">
            
            <?php
            // Verificar si el usuario tiene 4 preguntas de seguridad configuradas
            $sql = "SELECT COUNT(*) as total_preguntas 
                    FROM preguntas_seguridad p 
                    JOIN empleados e ON p.id_empleado = e.id_empleado 
                    WHERE e.usuario = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if($row['total_preguntas'] < 4) {
                echo '<div class="alert alert-danger">
                    <p class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No puedes recuperar tu contraseña porque no has configurado las 4 preguntas de seguridad.
                    </p>
                    <hr>
                    <p class="mb-0">
                        Por favor, configura tus 4 preguntas de seguridad en tu perfil antes de intentar recuperar la contraseña.
                    </p>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Inicio de Sesión
                    </a>
                    <a href="configurar_seguridad.php" class="btn btn-primary">
                        <i class="fas fa-shield-alt me-2"></i>Configurar Preguntas de Seguridad
                    </a>
                </div>';
                exit;
            }
            
            // Obtener las preguntas de seguridad del usuario
            $sql = "SELECT p.id_pregunta, p.pregunta, e.id_empleado 
                    FROM preguntas_seguridad p 
                    JOIN empleados e ON p.id_empleado = e.id_empleado 
                    WHERE e.usuario = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows >= 2) {
                // Obtener todas las preguntas
                $preguntas = [];
                while ($row = $result->fetch_assoc()) {
                    $preguntas[] = $row;
                }
                
                // Seleccionar 2 preguntas aleatorias
                $preguntas_seleccionadas = array_rand($preguntas, 2);
                $id_empleado = $preguntas[$preguntas_seleccionadas[0]]['id_empleado'];
            } else {
                echo '<div class="alert alert-danger">No hay suficientes preguntas de seguridad configuradas para este usuario.</div>';
                echo '<div class="text-center mt-3"><a href="recuperar_contrasena.php" class="btn btn-secondary">Volver</a></div>';
                exit;
            }
            ?>
            
            <input type="hidden" name="id_empleado" value="<?php echo $id_empleado; ?>">
            
            <?php foreach ($preguntas_seleccionadas as $index): 
                $pregunta = $preguntas[$index];
            ?>
            <div class="mb-3">
                <label class="form-label">Pregunta de Seguridad <?php echo $index + 1; ?>:</label>
                <p class="form-control-static"><strong><?php echo htmlspecialchars($pregunta['pregunta']); ?></strong></p>
                <input type="hidden" name="preguntas[]" value="<?php echo $pregunta['id_pregunta']; ?>">
            </div>
            
            <div class="mb-3">
                <label for="respuesta<?php echo $index; ?>" class="form-label">Respuesta</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="respuesta<?php echo $index; ?>" 
                           name="respuestas[]" 
                           required 
                           placeholder="Ingrese su respuesta">
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check me-2"></i>Verificar Respuestas
                </button>
            </div>
        </form>
        
        <?php elseif($paso == 3): ?>
        <!-- Paso 3: Establecer nueva contraseña -->
        <form action="php/procesar_recuperacion.php" method="POST" autocomplete="off">
            <input type="hidden" name="paso" value="3">
            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>">
            
            <div class="mb-3">
                <label for="nueva_clave" class="form-label">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           class="form-control" 
                           id="nueva_clave" 
                           name="nueva_clave" 
                           pattern="[a-zA-Z0-9$@.-]{7,100}" 
                           maxlength="100" 
                           required 
                           placeholder="Ingrese su nueva contraseña">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="confirmar_clave" class="form-label">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           class="form-control" 
                           id="confirmar_clave" 
                           name="confirmar_clave" 
                           pattern="[a-zA-Z0-9$@.-]{7,100}" 
                           maxlength="100" 
                           required 
                           placeholder="Confirme su nueva contraseña">
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Cambiar Contraseña
                </button>
            </div>
        </form>
        <?php endif; ?>
        
        <div class="text-center mt-3">
            <p class="text-muted">
                <a href="login.php" class="text-primary text-decoration-none">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mostrar la contraseña
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('nueva_clave');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle del icono
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            
            if (toggleConfirmPassword) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('confirmar_clave');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle del icono
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>