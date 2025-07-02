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

if (isset($_SESSION['id_empleado']) && isset($_SESSION['usuario'])) {
    if ($role == 'gerente' || $role == 'empleado') {
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Pedidos</title>
    
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
        if ($role == 'gerente') {
            include "./inc/navbarLogginGerente.php";
        } elseif ($role == 'empleado') {
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
                    <h1 class="display-5 fw-bold">GESTIÓN DE PEDIDOS</h1>
                    <p class="lead">Administra los pedidos del sistema</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light btn-lg" onclick="document.getElementById('pedido-form').style.display='block'">
                        <i class="fas fa-plus-circle me-2"></i>Generar Nuevo Pedido
                    </button>
                </div>
            </div>
        </div>
    </section>

    <?php
    // Mostrar notificaciones si existen
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        echo "<script>
            Swal.fire({
                icon: '{$notification['type']}',
                title: '{$notification['title']}',
                text: '{$notification['message']}',
                confirmButtonColor: '#3085d6'
            });
        </script>";
    }

    // Obtener lista de monturas disponibles
    $query_monturas = "SELECT * FROM monturas WHERE cantidad > 0";
    $resultado_monturas = mysqli_query($con, $query_monturas);

    // Obtener lista de cristales disponibles
    $query_cristales = "SELECT * FROM cristales WHERE cantidad > 0";
    $resultado_cristales = mysqli_query($con, $query_cristales);
    ?>

    <!-- Formulario para generar nuevo pedido -->
    <div id="pedido-form" style="display: none;">
        <div class="card shadow-sm mx-auto my-4" style="max-width: 800px;">
            <div class="card-body p-4">
                <h2 class="card-title h4 mb-4">Generar Nuevo Pedido</h2>
                <form id="formPedido" action="./php/procesar_pedido.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Datos del Cliente -->
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h4 class="h5 mb-3">Datos del Cliente</h4>
                            <div class="mb-3">
                                <label class="form-label">Cédula *</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="cedula_cliente" name="cedula_cliente" required>
                                    <button class="btn btn-outline-primary" type="button" id="buscarCliente">Buscar</button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingrese la cédula del cliente.
                                </div>
                            </div>
                            <div id="datosClienteForm">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input class="form-control" type="text" id="nombre_cliente" name="nombre_cliente" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese el nombre del cliente.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Apellido *</label>
                                    <input class="form-control" type="text" id="apellido_cliente" name="apellido_cliente" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese el apellido del cliente.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Teléfono *</label>
                                    <input class="form-control" type="tel" id="telefono_cliente" name="telefono_cliente" pattern="[0-9]{11}" maxlength="11" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un número de teléfono válido de 11 dígitos.
                                    </div>
                                    <small class="form-text text-muted">El número debe tener 11 dígitos.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo (opcional)</label>
                                    <input class="form-control" type="email" id="correo_cliente" name="correo_cliente">
                                </div>
                            </div>
                            
                            <!-- Sección para prescripciones guardadas -->
                            <div id="prescripcionesGuardadas" style="display: none;" class="mb-4">
                                <h4 class="h5 mb-3">Prescripciones Guardadas</h4>
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar Prescripción</label>
                                    <select class="form-select" id="prescripcionesSelect">
                                        <option value="">Seleccione una prescripción guardada</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="usarPrescripcion">
                                        <i class="fas fa-eye me-1"></i> Usar esta prescripción
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="nuevaPrescripcion">
                                        <i class="fas fa-plus me-1"></i> Crear nueva prescripción
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de Productos -->
                        <div class="col-md-6">
                            <h4 class="h5 mb-3">Selección de Productos</h4>
                            <div class="mb-3">
                                <label class="form-label">Montura *</label>
                                <select class="form-select" name="montura" required>
                                    <option value="">Seleccione una montura</option>
                                    <?php while($montura = mysqli_fetch_assoc($resultado_monturas)): ?>
                                        <option value="<?php echo $montura['id_montura']; ?>" 
                                                data-precio="<?php echo $montura['precio']; ?>">
                                            <?php echo $montura['marca'] . ' - ' . $montura['material'] . 
                                                     ' ($' . number_format($montura['precio'], 2) . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione una montura.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cristal Derecho *</label>
                                <select class="form-select" name="cristal1" required>
                                    <option value="">Seleccione el cristal derecho</option>
                                    <?php 
                                    mysqli_data_seek($resultado_cristales, 0);
                                    while($cristal = mysqli_fetch_assoc($resultado_cristales)): 
                                    ?>
                                        <option value="<?php echo $cristal['id_cristal']; ?>"
                                                data-precio="<?php echo $cristal['precio']; ?>">
                                            <?php echo $cristal['marca'] . ' - ' . 
                                                     ' ($' . number_format($cristal['precio'], 2) . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione el cristal derecho.
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">Fórmula Ojo Derecho *</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Esfera</label>
                                            <input class="form-control" type="text" name="od_esfera" required placeholder="+2.00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Cilindro</label>
                                            <input class="form-control" type="text" name="od_cilindro" required placeholder="-0.50">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Eje</label>
                                            <input class="form-control" type="text" name="od_eje" required placeholder="180">
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor ingrese la fórmula completa para el ojo derecho.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cristal Izquierdo *</label>
                                <select class="form-select" name="cristal2" required>
                                    <option value="">Seleccione el cristal izquierdo</option>
                                    <?php 
                                    mysqli_data_seek($resultado_cristales, 0);
                                    while($cristal = mysqli_fetch_assoc($resultado_cristales)): 
                                    ?>
                                        <option value="<?php echo $cristal['id_cristal']; ?>"
                                                data-precio="<?php echo $cristal['precio']; ?>">
                                            <?php echo $cristal['marca'] . ' - ' . 
                                                     ' ($' . number_format($cristal['precio'], 2) . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione el cristal izquierdo.
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">Fórmula Ojo Izquierdo *</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Esfera</label>
                                            <input class="form-control" type="text" name="oi_esfera" required placeholder="+2.00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Cilindro</label>
                                            <input class="form-control" type="text" name="oi_cilindro" required placeholder="-0.50">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Eje</label>
                                            <input class="form-control" type="text" name="oi_eje" required placeholder="180">
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor ingrese la fórmula completa para el ojo izquierdo.
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Datos Adicionales (Opcionales)</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Adición</label>
                                            <input class="form-control" type="text" name="adicion" placeholder="+2.50">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Altura Pupilar</label>
                                            <input class="form-control" type="text" name="altura_pupilar" placeholder="30mm">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Distancia Pupilar</label>
                                            <input class="form-control" type="text" name="distancia_pupilar" placeholder="62mm">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control" name="observacion" rows="2" placeholder="Observaciones adicionales sobre la prescripción"></textarea>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Precio Total:</strong> $<span id="precioTotal">0.00</span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Cantidad de lentes con estas especificaciones</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="decrementarCantidad">-</button>
                                    <input type="number" class="form-control text-center" id="cantidadLentes" name="cantidad_lentes" value="1" min="1" max="10">
                                    <button type="button" class="btn btn-outline-secondary" id="incrementarCantidad">+</button>
                                </div>
                                <small class="form-text text-muted">Indique cuántos lentes con estas mismas especificaciones desea el cliente.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" name="generar_pedido" class="btn btn-primary me-2">
                            <i class="fas fa-check me-2"></i>Generar Pedido
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('pedido-form').style.display='none'">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <section class="container py-5">
        <div class="card info-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Registro de Pedidos</h3>
            </div>
            <div class="card-body p-0">
                <?php include "./php/mostrar_pedidos.php"; ?>
            </div>
        </div>
    </section>

    <?php include "./inc/footer.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para buscar cliente por cédula
        document.getElementById('buscarCliente').addEventListener('click', function() {
            const cedula = document.getElementById('cedula_cliente').value.trim();
            
            if (cedula === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo vacío',
                    text: 'Por favor ingrese una cédula para buscar',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            // Realizar petición AJAX para buscar cliente
            fetch(`./php/buscar_cliente.php?cedula=${cedula}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cliente encontrado, llenar formulario con datos
                        const cliente = data.cliente;
                        document.getElementById('nombre_cliente').value = cliente.nombre;
                        document.getElementById('apellido_cliente').value = cliente.apellido;
                        document.getElementById('telefono_cliente').value = cliente.num_telefono;
                        document.getElementById('correo_cliente').value = cliente.correo || '';
                        
                        // Deshabilitar campos para que no se puedan modificar
                        document.getElementById('nombre_cliente').readOnly = true;
                        document.getElementById('apellido_cliente').readOnly = true;
                        document.getElementById('telefono_cliente').readOnly = true;
                        document.getElementById('correo_cliente').readOnly = true;
                        
                        // Mostrar y llenar el selector de prescripciones si hay prescripciones guardadas
                        if (data.prescripciones && data.prescripciones.length > 0) {
                            const prescripcionesSelect = document.getElementById('prescripcionesSelect');
                            // Limpiar opciones anteriores
                            prescripcionesSelect.innerHTML = '<option value="">Seleccione una prescripción guardada</option>';
                            
                            // Agregar las prescripciones al selector
                            data.prescripciones.forEach(prescripcion => {
                                const option = document.createElement('option');
                                option.value = prescripcion.id_prescripcion;
                                option.textContent = `Prescripción del ${formatDate(prescripcion.fecha_emision)}`;
                                option.dataset.prescripcion = JSON.stringify(prescripcion);
                                prescripcionesSelect.appendChild(option);
                            });
                            
                            // Mostrar la sección de prescripciones guardadas
                            document.getElementById('prescripcionesGuardadas').style.display = 'block';
                        } else {
                            document.getElementById('prescripcionesGuardadas').style.display = 'none';
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Cliente encontrado',
                            text: 'Los datos del cliente han sido cargados',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        // Cliente no encontrado, habilitar campos para ingreso de datos
                        document.getElementById('nombre_cliente').value = '';
                        document.getElementById('apellido_cliente').value = '';
                        document.getElementById('telefono_cliente').value = '';
                        document.getElementById('correo_cliente').value = '';
                        
                        // Habilitar campos para que se puedan modificar
                        document.getElementById('nombre_cliente').readOnly = false;
                        document.getElementById('apellido_cliente').readOnly = false;
                        document.getElementById('telefono_cliente').readOnly = false;
                        document.getElementById('correo_cliente').readOnly = false;
                        
                        // Ocultar sección de prescripciones guardadas
                        document.getElementById('prescripcionesGuardadas').style.display = 'none';
                        
                        Swal.fire({
                            icon: 'info',
                            title: 'Cliente no encontrado',
                            text: 'Por favor ingrese los datos del nuevo cliente',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al buscar el cliente',
                        confirmButtonColor: '#3085d6'
                    });
                });
        });
        
        // Función para formatear fecha
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        }
        
        // Evento para usar una prescripción guardada
        document.getElementById('usarPrescripcion').addEventListener('click', function() {
            const prescripcionesSelect = document.getElementById('prescripcionesSelect');
            if (prescripcionesSelect.value === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección requerida',
                    text: 'Por favor seleccione una prescripción guardada',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            // Obtener datos de la prescripción seleccionada
            const prescripcionData = JSON.parse(prescripcionesSelect.options[prescripcionesSelect.selectedIndex].dataset.prescripcion);
            
            // Llenar los campos de la fórmula con los datos de la prescripción
            document.querySelector('input[name="od_esfera"]').value = prescripcionData.OD_esfera;
            document.querySelector('input[name="od_cilindro"]').value = prescripcionData.OD_cilindro;
            document.querySelector('input[name="od_eje"]').value = prescripcionData.OD_eje;
            document.querySelector('input[name="oi_esfera"]').value = prescripcionData.OI_esfera;
            document.querySelector('input[name="oi_cilindro"]').value = prescripcionData.OI_cilindro;
            document.querySelector('input[name="oi_eje"]').value = prescripcionData.OI_eje;
            
            // Llenar campos adicionales si existen
            if (prescripcionData.adicion) {
                document.querySelector('input[name="adicion"]').value = prescripcionData.adicion;
            }
            if (prescripcionData.altura_pupilar) {
                document.querySelector('input[name="altura_pupilar"]').value = prescripcionData.altura_pupilar;
            }
            if (prescripcionData.distancia_pupilar) {
                document.querySelector('input[name="distancia_pupilar"]').value = prescripcionData.distancia_pupilar;
            }
            if (prescripcionData.observacion) {
                document.querySelector('textarea[name="observacion"]').value = prescripcionData.observacion;
            }
            
            // Agregar campo oculto para el ID de la prescripción
            let idPrescripcionInput = document.getElementById('id_prescripcion_existente');
            if (!idPrescripcionInput) {
                idPrescripcionInput = document.createElement('input');
                idPrescripcionInput.type = 'hidden';
                idPrescripcionInput.id = 'id_prescripcion_existente';
                idPrescripcionInput.name = 'id_prescripcion_existente';
                document.getElementById('formPedido').appendChild(idPrescripcionInput);
            }
            idPrescripcionInput.value = prescripcionData.id_prescripcion;
            
            Swal.fire({
                icon: 'success',
                title: 'Prescripción cargada',
                text: 'Los datos de la prescripción han sido cargados correctamente',
                confirmButtonColor: '#3085d6'
            });
        });
        
        // Evento para crear una nueva prescripción
        document.getElementById('nuevaPrescripcion').addEventListener('click', function() {
            // Limpiar los campos de la fórmula
            document.querySelector('input[name="od_esfera"]').value = '';
            document.querySelector('input[name="od_cilindro"]').value = '';
            document.querySelector('input[name="od_eje"]').value = '';
            document.querySelector('input[name="oi_esfera"]').value = '';
            document.querySelector('input[name="oi_cilindro"]').value = '';
            document.querySelector('input[name="oi_eje"]').value = '';
            document.querySelector('input[name="adicion"]').value = '';
            document.querySelector('input[name="altura_pupilar"]').value = '';
            document.querySelector('input[name="distancia_pupilar"]').value = '';
            document.querySelector('textarea[name="observacion"]').value = '';
            
            // Eliminar el campo oculto de ID de prescripción si existe
            const idPrescripcionInput = document.getElementById('id_prescripcion_existente');
            if (idPrescripcionInput) {
                idPrescripcionInput.remove();
            }
            
            Swal.fire({
                icon: 'info',
                title: 'Nueva prescripción',
                text: 'Puede ingresar los datos de la nueva prescripción',
                confirmButtonColor: '#3085d6'
            });
        });
        
        // Validación del teléfono para que tenga 11 dígitos
        document.getElementById('telefono_cliente').addEventListener('input', function(e) {
            // Permitir solo números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 11 dígitos
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });
        
        // También buscar al presionar Enter en el campo de cédula
        document.getElementById('cedula_cliente').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('buscarCliente').click();
            }
        });

        // Función para actualizar el estado del pedido
        function actualizarEstado(idPedido, nuevoEstado) {
            // Crear un formulario oculto para enviar los datos
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = './php/acciones_pedidos.php';
            form.style.display = 'none';

            // Agregar los campos necesarios
            const idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'id_pedido';
            idField.value = idPedido;

            const estadoField = document.createElement('input');
            estadoField.type = 'hidden';
            estadoField.name = 'nuevo_estado';
            estadoField.value = nuevoEstado;

            const submitField = document.createElement('input');
            submitField.type = 'hidden';
            submitField.name = 'actualizar_estado';
            submitField.value = '1';

            // Agregar los campos al formulario
            form.appendChild(idField);
            form.appendChild(estadoField);
            form.appendChild(submitField);

            // Agregar el formulario al documento y enviarlo
            document.body.appendChild(form);
            form.submit();
        }

        // Función para eliminar pedido
        function eliminarPedido(idPedido) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir al script de eliminación
                    window.location.href = './php/acciones_pedidos.php?eliminar_pedido=1&id_pedido=' + idPedido;
                }
            });
        }

        // Función para calcular el precio total
        function calcularPrecioTotal() {
            let precioTotal = 0;
            
            // Obtener precio de la montura
            const montura = document.querySelector('select[name="montura"]');
            if (montura.selectedIndex > 0) {
                precioTotal += parseFloat(montura.options[montura.selectedIndex].dataset.precio);
            }
            
            // Obtener precio del cristal derecho
            const cristalDerecho = document.querySelector('select[name="cristal1"]');
            if (cristalDerecho.selectedIndex > 0) {
                precioTotal += parseFloat(cristalDerecho.options[cristalDerecho.selectedIndex].dataset.precio);
            }
            
            // Obtener precio del cristal izquierdo
            const cristalIzquierdo = document.querySelector('select[name="cristal2"]');
            if (cristalIzquierdo.selectedIndex > 0) {
                precioTotal += parseFloat(cristalIzquierdo.options[cristalIzquierdo.selectedIndex].dataset.precio);
            }
            
            // Multiplicar por la cantidad de lentes
            const cantidad = parseInt(document.getElementById('cantidadLentes').value) || 1;
            precioTotal = precioTotal * cantidad;
            
            // Actualizar el precio total en la página
            document.getElementById('precioTotal').textContent = precioTotal.toFixed(2);
        }

        // Agregar event listeners para los cambios en las selecciones
        document.querySelector('select[name="montura"]').addEventListener('change', calcularPrecioTotal);
        document.querySelector('select[name="cristal1"]').addEventListener('change', calcularPrecioTotal);
        document.querySelector('select[name="cristal2"]').addEventListener('change', calcularPrecioTotal);
        document.getElementById('cantidadLentes').addEventListener('change', calcularPrecioTotal);

        // Validación del formulario
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Funcionalidad para los botones de incrementar/decrementar cantidad
        document.getElementById('incrementarCantidad').addEventListener('click', function() {
            const cantidadInput = document.getElementById('cantidadLentes');
            const montura = document.querySelector('select[name="montura"]');
            const cristal1 = document.querySelector('select[name="cristal1"]');
            const cristal2 = document.querySelector('select[name="cristal2"]');
            
            // Verificar que se hayan seleccionado productos
            if (montura.value === '' || cristal1.value === '' || cristal2.value === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección incompleta',
                    text: 'Debe seleccionar montura y cristales antes de modificar la cantidad',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            // Incrementar cantidad
            let cantidad = parseInt(cantidadInput.value) || 1;
            if (cantidad < 10) {
                cantidad++;
                cantidadInput.value = cantidad;
                
                // Verificar disponibilidad de stock
                verificarStock(montura.value, cristal1.value, cristal2.value, cantidad);
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Límite alcanzado',
                    text: 'No se pueden solicitar más de 10 lentes iguales en un pedido',
                    confirmButtonColor: '#3085d6'
                });
            }
            
            // Actualizar precio total
            calcularPrecioTotal();
        });
        
        document.getElementById('decrementarCantidad').addEventListener('click', function() {
            const cantidadInput = document.getElementById('cantidadLentes');
            let cantidad = parseInt(cantidadInput.value) || 1;
            
            if (cantidad > 1) {
                cantidad--;
                cantidadInput.value = cantidad;
                
                // Actualizar precio total
                calcularPrecioTotal();
            }
        });
        
        // Función para verificar disponibilidad de stock
        function verificarStock(idMontura, idCristal1, idCristal2, cantidad) {
            // Crear un objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('verificar_stock', '1');
            formData.append('id_montura', idMontura);
            formData.append('id_cristal1', idCristal1);
            formData.append('id_cristal2', idCristal2);
            formData.append('cantidad', cantidad);
            
            // Realizar petición AJAX para verificar stock
            fetch('./php/procesar_pedido.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Si no hay suficiente stock, mostrar alerta y resetear cantidad a 1
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock insuficiente',
                        text: data.message,
                        confirmButtonColor: '#3085d6'
                    });
                    
                    document.getElementById('cantidadLentes').value = 1;
                    calcularPrecioTotal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al verificar el stock disponible',
                    confirmButtonColor: '#3085d6'
                });
            });
        }
        
        // Verificar stock cuando se cambia manualmente la cantidad
        document.getElementById('cantidadLentes').addEventListener('change', function() {
            const cantidadInput = this;
            const montura = document.querySelector('select[name="montura"]');
            const cristal1 = document.querySelector('select[name="cristal1"]');
            const cristal2 = document.querySelector('select[name="cristal2"]');
            
            // Validar que sea un número entre 1 y 10
            let cantidad = parseInt(cantidadInput.value) || 1;
            if (cantidad < 1) cantidad = 1;
            if (cantidad > 10) cantidad = 10;
            cantidadInput.value = cantidad;
            
            // Verificar que se hayan seleccionado productos
            if (montura.value !== '' && cristal1.value !== '' && cristal2.value !== '') {
                verificarStock(montura.value, cristal1.value, cristal2.value, cantidad);
            }
        });
        
        // Verificar stock cuando se seleccionan productos
        document.querySelector('select[name="montura"]').addEventListener('change', function() {
            const cantidad = parseInt(document.getElementById('cantidadLentes').value) || 1;
            const montura = this.value;
            const cristal1 = document.querySelector('select[name="cristal1"]').value;
            const cristal2 = document.querySelector('select[name="cristal2"]').value;
            
            if (montura !== '' && cristal1 !== '' && cristal2 !== '' && cantidad > 1) {
                verificarStock(montura, cristal1, cristal2, cantidad);
            }
        });
        
        document.querySelector('select[name="cristal1"]').addEventListener('change', function() {
            const cantidad = parseInt(document.getElementById('cantidadLentes').value) || 1;
            const montura = document.querySelector('select[name="montura"]').value;
            const cristal1 = this.value;
            const cristal2 = document.querySelector('select[name="cristal2"]').value;
            
            if (montura !== '' && cristal1 !== '' && cristal2 !== '' && cantidad > 1) {
                verificarStock(montura, cristal1, cristal2, cantidad);
            }
        });
        
        document.querySelector('select[name="cristal2"]').addEventListener('change', function() {
            const cantidad = parseInt(document.getElementById('cantidadLentes').value) || 1;
            const montura = document.querySelector('select[name="montura"]').value;
            const cristal1 = document.querySelector('select[name="cristal1"]').value;
            const cristal2 = this.value;
            
            if (montura !== '' && cristal1 !== '' && cristal2 !== '' && cantidad > 1) {
                verificarStock(montura, cristal1, cristal2, cantidad);
            }
        });
    </script>
</body>
</html>

<?php 
    }
} else {
    echo "<script>
        alert('No puede ingresar a esta página sin loguearse');
        location.href = 'index.php';
    </script>";
}
?>