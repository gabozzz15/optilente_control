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

    // Función para obtener el precio del dólar del BCV usando DolarAPI
    function obtenerPrecioDolarBCV() {
        $apiUrl = "https://ve.dolarapi.com/v1/dolares/oficial";
        $json = file_get_contents($apiUrl);
        
        if ($json !== false) {
            $data = json_decode($json, true);
            if (isset($data['promedio'])) {
                return $data['promedio']; // Retorna el valor promedio del dólar oficial
            }
        }
        return "N/A"; // Valor predeterminado si no se puede obtener el precio
    }

    $precioDolarBCV = obtenerPrecioDolarBCV();
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
    <title>OPTILENTE 2020 - Inventario</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/styles.css">

    <script>
        // Actualizar la hora cada segundo
        function actualizarHora() {
            const hora = new Date();
            document.getElementById('horaActual').innerText = hora.toLocaleTimeString();
        }
        setInterval(actualizarHora, 1000);

        // Función para calcular bolívares a partir del monto en dólares
        function calcularBolivares() {
            const precioDolar = <?php echo json_encode($precioDolarBCV); ?>;
            const montoDolares = parseFloat(document.getElementById('inputDolares').value);
            const resultado = montoDolares * precioDolar;

            if (!isNaN(resultado)) {
                document.getElementById('resultadoBolivares').innerText = resultado.toLocaleString('es-VE', {
                    style: 'currency',
                    currency: 'VES'
                });
            }
        }
    </script>

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
        <div class="container text-center">
            <h1 class="display-5 fw-bold">
                BIENVENIDO
                <?php 
                    $rolMayuscula= strtoupper($role);
                    echo $rolMayuscula."!";
                ?>
            </h1>
            <p class="lead">
                ACCEDE A LAS FUNCIONES EN LA BARRA DE NAVEGACIÓN
            </p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h4 class="card-title">Precio del Dólar (BCV)</h4>
                            <h3 class="card-text">$<?php echo is_numeric($precioDolarBCV) ? number_format($precioDolarBCV, 2) : $precioDolarBCV; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h4 class="card-title">Hora Actual</h4>
                            <h3 id="horaActual" class="card-text"><?php echo date('H:i:s'); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h4 class="card-title">Calculadora de Conversión</h4>
                            <div class="mb-3">
                                <label for="inputDolares" class="form-label">Monto en Dólares (USD)</label>
                                <input id="inputDolares" class="form-control" type="number" step="0.01" placeholder="Ej. 100" oninput="calcularBolivares()">
                            </div>
                            <h5 class="mt-4">Equivalente en Bolívares:</h5>
                            <h3 id="resultadoBolivares" class="mt-2">Bs 0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "./inc/footer.php";?>

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