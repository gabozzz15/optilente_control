<?php
    session_start();
    include "./inc/conexionbd.php";
    $con = connection();

    $role = '';
    $userData = null;
    $passwordError = '';
    $passwordSuccess = '';

    if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])) {
        $idUser = $_SESSION['id_empleado'];
        $sql = "SELECT * FROM empleados WHERE id_empleado = '$idUser' AND estado_empleado != 'retirado'";
        $query = mysqli_query($con, $sql);

        if ($query && mysqli_num_rows($query) > 0) {
            $userData = mysqli_fetch_assoc($query);
            $role = $userData['cargo'];
        } else {
            // Si el usuario está retirado o no existe, redirigir al logout
            header("Location: logout.php");
            exit();
        }
    }

    // Procesar actualización de datos personales
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar'])) {
        $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($con, $_POST['apellido']);
        $cedula = mysqli_real_escape_string($con, $_POST['cedula_empleado']);
        $correo = mysqli_real_escape_string($con, $_POST['correo']);
        $telefono = mysqli_real_escape_string($con, $_POST['num_telefono']);
        
        $sql_update = "UPDATE empleados SET 
            nombre_empleado = '$nombre',
            apellido_empleado = '$apellido',
            cedula_empleado = '$cedula',
            correo = '$correo',
            num_telefono = '$telefono'
            WHERE id_empleado = '$idUser'";
        
        if (mysqli_query($con, $sql_update)) {
            header("Location: perfil.php?success=1");
            exit();
        }
    }

    // Procesar cambio de contraseña
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $password_nuevo = $_POST['password_nuevo'];
        $password_confirmar = $_POST['password_confirmar'];

        // Verificar que la contraseña actual sea correcta
        if ($password_actual === $userData['clave']) {
            // Verificar que las nuevas contraseñas coincidan
            if ($password_nuevo === $password_confirmar) {
                $sql_update_password = "UPDATE empleados SET 
                    clave = '$password_nuevo'
                    WHERE id_empleado = '$idUser'";
                
                if (mysqli_query($con, $sql_update_password)) {
                    $passwordSuccess = "La contraseña ha sido actualizada exitosamente.";
                }
            } else {
                $passwordError = "Las nuevas contraseñas no coinciden.";
            }
        } else {
            $passwordError = "La contraseña actual es incorrecta.";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Perfil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <header>
        <?php 
            if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
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

    <section class="py-5">
        <div class="container">
            <h1 class="display-5 fw-bold text-center mb-4">Mi Perfil</h1>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Los datos han sido actualizados exitosamente.
            </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-12">
                    <div class="row g-4">
                        <!-- Formulario de datos personales -->
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h2 class="card-title h4 mb-4">Datos Personales</h2>
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input class="form-control" type="text" name="nombre" value="<?php echo htmlspecialchars($userData['nombre_empleado']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Apellido</label>
                                            <input class="form-control" type="text" name="apellido" value="<?php echo htmlspecialchars($userData['apellido_empleado']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cédula</label>
                                            <input class="form-control" type="text" name="cedula_empleado" value="<?php echo htmlspecialchars($userData['cedula_empleado']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Correo Electrónico</label>
                                            <input class="form-control" type="email" name="correo" value="<?php echo htmlspecialchars($userData['correo']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input class="form-control" type="tel" name="num_telefono" value="<?php echo htmlspecialchars($userData['num_telefono']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Usuario</label>
                                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($userData['usuario']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cargo</label>
                                            <input class="form-control" type="text" value="<?php echo ucfirst(htmlspecialchars($userData['cargo'])); ?>" disabled>
                                        </div>

                                        <div class="d-grid mb-3">
                                            <button type="submit" name="actualizar" class="btn btn-primary">
                                                Actualizar Datos
                                            </button>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <a href="configurar_seguridad.php" class="btn btn-outline-primary">
                                                <i class="fas fa-shield-alt me-2"></i>Configurar Pregunta de Seguridad
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de cambio de contraseña -->
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h2 class="card-title h4 mb-4">Cambiar Contraseña</h2>
                                    
                                    <?php if ($passwordError): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $passwordError; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($passwordSuccess): ?>
                                    <div class="alert alert-success">
                                        <?php echo $passwordSuccess; ?>
                                    </div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña Actual</label>
                                            <input class="form-control" type="password" name="password_actual" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <input class="form-control" type="password" name="password_nuevo" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirmar Nueva Contraseña</label>
                                            <input class="form-control" type="password" name="password_confirmar" required>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" name="cambiar_password" class="btn btn-info">
                                                Cambiar Contraseña
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "./inc/footer.php"; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>