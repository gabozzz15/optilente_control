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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Visión de Calidad</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
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

    <!-- Hero Section -->
    <section class="hero-section" id="quienes_somos">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="hero-title">Visión clara, futuro brillante</h1>
                    <p class="hero-subtitle">En OPTILENTE 2020 combinamos tecnología avanzada y atención personalizada para cuidar lo más valioso: tu visión.</p>
                    <div class="d-flex flex-wrap">
                        <a href="#servicios" class="btn btn-primary">Nuestros Servicios</a>
                        <a href="login.php" class="btn btn-outline-primary">Iniciar Sesión</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                    <div class="model-container">
                        <!-- Lente 3D (Conservado del diseño original) -->
                        <div class="sketchfab-embed-wrapper w-100 h-100 position-absolute top-0 start-0"> 
                            <iframe title="Reading Glasses" 
                                    frameborder="0" 
                                    allowfullscreen 
                                    mozallowfullscreen="true" 
                                    webkitallowfullscreen="true" 
                                    allow="autoplay; fullscreen; xr-spatial-tracking" 
                                    xr-spatial-tracking 
                                    execution-while-out-of-viewport 
                                    execution-while-not-rendered 
                                    web-share 
                                    class="w-100 h-100 object-fit-cover" 
                                    src="https://sketchfab.com/models/cb92f0ac50ea46d5ab17036f279c3aa4/embed?autospin=1&autostart=1&camera=0&transparent=1&ui_animations=0&ui_infos=0&ui_stop=0&ui_inspector=0&ui_watermark_link=0&ui_watermark=0&ui_hint=0&ui_ar=0&ui_help=0&ui_settings=0&ui_vr=0&ui_fullscreen=0&ui_annotations=0">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5 my-5" id="servicios">
        <div class="container">
            <div class="section-title" data-aos="fade-up" data-aos-duration="800">
                <h2>Nuestros Servicios</h2>
                <p>Ofrecemos soluciones completas para el cuidado de tu visión con la más alta calidad y tecnología</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">
                    <div class="service-card">
                        <img src="./css/img/tipo.jpg" class="card-img-top" alt="Examen visual">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h5 class="card-title">Exámenes Visuales Completos</h5>
                            <p class="card-text">Evaluaciones exhaustivas realizadas por profesionales certificados utilizando tecnología de vanguardia para diagnosticar con precisión cualquier problema visual.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
                    <div class="service-card">
                        <img src="./css/img/chama.jpg" class="card-img-top" alt="Monturas de calidad">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-glasses"></i>
                            </div>
                            <h5 class="card-title">Monturas de Diseñador</h5>
                            <p class="card-text">Amplia selección de monturas de las mejores marcas internacionales, combinando estilo, durabilidad y confort para satisfacer todos los gustos y necesidades.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300" data-aos-duration="800">
                    <div class="service-card">
                        <img src="./css/img/gafas.jpg" class="card-img-top" alt="Lentes especializados">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-low-vision"></i>
                            </div>
                            <h5 class="card-title">Lentes Especializados</h5>
                            <p class="card-text">Cristales de alta tecnología adaptados a cada necesidad: progresivos, fotocromáticos, con filtro de luz azul y más, todos con tratamientos antirreflejo y antirayones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include "./inc/footer.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS animation
        AOS.init();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>