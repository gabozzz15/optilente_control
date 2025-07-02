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

<?php 
    if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])){
            
        if ($role == 'gerente' || $role == 'empleado'){ 
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Reportes</title>
    
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

    <section class="py-4 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h1 class="display-5 fw-bold">REPORTES</h1>
                    <p class="lead">Genera los reportes de métricas y puntos clave del sistema</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Reporte de Stock -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-boxes fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Reporte de Stock</h5>
                                    <p class="card-subtitle text-muted">Estado actual del Stock</p>
                                </div>
                            </div>
                            <p class="card-text">Genera un reporte detallado del stock actual, incluyendo productos disponibles, agotados y próximos a agotarse.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_stock.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=stock" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reporte de Pedidos -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-shopping-cart fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Reporte de Pedidos</h5>
                                    <p class="card-subtitle text-muted">Historial de pedidos</p>
                                </div>
                            </div>
                            <p class="card-text">Visualiza todos los pedidos realizados, su estado actual, fechas de entrega y detalles de los clientes.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_pedidos.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=pedidos" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reporte de Empleados -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-users fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Reporte de Empleados</h5>
                                    <p class="card-subtitle text-muted">Información del personal</p>
                                </div>
                            </div>
                            <p class="card-text">Accede a la información detallada de todos los empleados, incluyendo datos de contacto y roles asignados.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_empleados.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=empleados" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Producto más vendido -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-trophy fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Productos más vendidos</h5>
                                    <p class="card-subtitle text-muted">Análisis de ventas</p>
                                </div>
                            </div>
                            <p class="card-text">Identifica los productos más populares y analiza las tendencias de ventas para optimizar tu inventario.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_productos_vendidos.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=productos_vendidos" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empleado con más pedidos -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-award fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Rendimiento de empleados</h5>
                                    <p class="card-subtitle text-muted">Métricas de desempeño</p>
                                </div>
                            </div>
                            <p class="card-text">Analiza el rendimiento de los empleados según la cantidad de pedidos procesados y ventas realizadas.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_rendimiento_empleados.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=rendimiento_empleados" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Órdenes de Compra -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-truck-loading fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Órdenes de Compra</h5>
                                    <p class="card-subtitle text-muted">Detalle de compras</p>
                                </div>
                            </div>
                            <p class="card-text">Visualiza todas las órdenes de compra de monturas y cristales, con detalles de proveedores y montos.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_ordenes_compra.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=ordenes_compra" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Prescripciones de Clientes -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-notes-medical fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Prescripciones</h5>
                                    <p class="card-subtitle text-muted">Historial médico</p>
                                </div>
                            </div>
                            <p class="card-text">Consulta el historial de prescripciones de los clientes, con detalles de fórmulas ópticas y medidas.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_prescripciones.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=prescripciones" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Proveedores -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-industry fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Proveedores</h5>
                                    <p class="card-subtitle text-muted">Análisis de proveedores</p>
                                </div>
                            </div>
                            <p class="card-text">Obtén información detallada de tus proveedores, incluyendo órdenes de compra y montos invertidos.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_proveedores.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=proveedores" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ventas Mensuales -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="fa-stack fa-2x">
                                        <i class="fas fa-circle fa-stack-2x text-primary opacity-25"></i>
                                        <i class="fas fa-chart-line fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Ventas Mensuales</h5>
                                    <p class="card-subtitle text-muted">Análisis de ventas por mes</p>
                                </div>
                            </div>
                            <p class="card-text">Visualiza las estadísticas de ventas mensuales, incluyendo cantidad de pedidos y montos totales.</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="reportes/reporte_ventas_mensuales.php" class="btn btn-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </a>
                                <a href="exportar_datos.php?exportar=ventas_mensuales" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </a>
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

<?php 
            }
    }else{
        echo "<script>
                        alert('No puede ingresar a esa pagina sin loguearse');
                        location.href = 'index.php'
            </script>";
    }           
?>