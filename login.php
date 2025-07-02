<?php
    session_start();
    // Si ya hay una sesión iniciada, redirigir al home
    if(isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
        header('Location: home.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Optilente 2020</title>
    
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
        
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        <h5 class="text-center text-muted mb-4">Sistema de Gestión Optilente 2020</h5>
        
        <form action="procesar_login.php" method="POST" autocomplete="off">
            <?php
            if(isset($_GET['error'])){
            ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
            <?php
            }
            ?>
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="usuario" 
                           name="usuario" 
                           pattern="[a-zA-Z0-9]{4,20}" 
                           maxlength="20" 
                           required 
                           placeholder="Ingrese su nombre de usuario">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           class="form-control" 
                           id="clave" 
                           name="clave" 
                           pattern="[a-zA-Z0-9$@.-]{7,100}" 
                           maxlength="100" 
                           required 
                           placeholder="Ingrese su contraseña">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </div>
        </form>
        
        <div class="text-center mt-3">
            <p class="text-muted">
                <a href="recuperar_contrasena.php" class="text-primary text-decoration-none">¿Olvidaste tu contraseña?</a>
            </p>
        </div>
        
        <?php
        if(isset($_GET['mensaje'])){
        ?>
        <div class="alert alert-success mt-3">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
        <?php
        }
        ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mostrar la contrasena
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('clave');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle del icono
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>