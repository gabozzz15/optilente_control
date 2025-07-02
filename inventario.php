<?php
session_start();
include "./inc/conexionbd.php";
$con = connection();

// Procesar la creación de item antes de cualquier salida de HTML
if (isset($_POST['submit'])) {
    include "./php/crear_item.php";
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
    <title>OPTILENTE 2020 - Inventario</title>
    
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
                    <h1 class="display-5 fw-bold">GESTIÓN DE STOCK</h1>
                    <p class="lead">Administra el Stock de las Monturas y Cristales del Sistema</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light btn-lg me-2 mb-2 mb-md-0" onclick="document.getElementById('crear-form').style.display='block'">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nuevo Item
                    </button>
                    <button class="btn btn-light btn-lg" onclick="document.getElementById('orden-compra-form').style.display='block'">
                        <i class="fas fa-shopping-cart me-2"></i>Ingresar Orden de Compra
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

    <!-- Formulario para Orden de Compra -->
    <div id="orden-compra-form" style="display: none;">
        <div class="card shadow-sm mx-auto my-4" style="max-width: 800px;">
            <div class="card-body p-4">
                <h2 class="card-title h4 mb-4">Ingresar Orden de Compra</h2>
                <form id="formOrdenCompra" action="./php/procesar_orden_compra.php" method="POST">
                    <!-- Información general de la orden -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Proveedor</label>
                                <select name="id_proveedor" class="form-select" required>
                                    <option value="">Seleccione proveedor</option>
                                    <?php
                                    $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                    $result_proveedores = mysqli_query($con, $query_proveedores);
                                    
                                    while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                        echo "<option value='" . $proveedor['id_proveedor'] . "'>" . 
                                             htmlspecialchars($proveedor['nombre']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Fecha de Orden</label>
                                <input type="date" class="form-control" name="fecha_orden" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Número de Orden</label>
                                <input type="text" class="form-control" value="<?php echo 'OC-' . date('Ymd') . '-' . rand(1000, 9999); ?>" readonly>
                                <input type="hidden" name="num_orden" value="<?php echo time(); ?>">
                            </div>
                        </div>
                    </div>

                    <div id="items-container">
                        <!-- Primer item (siempre presente) -->
                        <div class="item-orden card mb-3 p-3">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Tipo de Item</label>
                                        <select name="tipo[]" class="form-select tipo-select" onchange="actualizarItems(this)" required>
                                            <option value="">Seleccione tipo</option>
                                            <option value="montura">Montura</option>
                                            <option value="cristal">Cristal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Item</label>
                                        <select name="item[]" class="form-select item-select" required>
                                            <option value="">Seleccione item</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Cantidad</label>
                                        <input class="form-control" type="number" name="cantidad[]" min="1" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-item" style="display: none;" onclick="removeItem(this)">
                                <i class="fas fa-minus me-1"></i>Eliminar Item
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-info" onclick="agregarItem()">
                            <i class="fas fa-plus me-1"></i>Agregar Otro Item
                        </button>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-check me-1"></i>Confirmar Orden
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('orden-compra-form').style.display='none'">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulario para crear un nuevo item -->
    <div id="crear-form" style="display: none;">
        <div class="card shadow-sm mx-auto my-4" style="max-width: 600px;">
            <div class="card-body p-4">
                <h2 class="card-title h4 mb-4">Registrar Nuevo Item</h2>
                <form method="POST" action="inventario.php">
                    <input type="hidden" name="submit" value="1">

                    <!-- Campo para seleccionar tipo -->
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" id="tipo" onchange="updateFieldLabel()">
                            <option value="montura">Montura</option>
                            <option value="cristal">Cristal</option>
                        </select>
                    </div>

                    <!-- Campo para marca -->
                    <div class="mb-3">
                        <label class="form-label">Marca</label>
                        <input class="form-control" type="text" name="marca" required>
                    </div>

                    <!-- Contenedor para campos específicos de montura/cristal -->
                    <div id="campos-especificos">
                        <!-- Campos para montura (por defecto) -->
                        <div id="campos-montura">
                            <div class="mb-3">
                                <label class="form-label">Material</label>
                                <input class="form-control" type="text" name="materialOAumento" required>
                            </div>
                        </div>

                        <!-- Campos para cristal (ocultos por defecto) -->
                        <div id="campos-cristal" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Cristal</label>
                                <select class="form-select" name="tipo_cristal">
                                    <option value="monofocal">Monofocal</option>
                                    <option value="bifocal">Bifocal</option>
                                    <option value="multifocal">Multifocal</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Material del Cristal</label>
                                <select class="form-select" name="material_cristal">
                                    <option value="policarbonato">Policarbonato</option>
                                    <option value="hi-index">Hi-Index</option>
                                    <option value="tallados">Tallados</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Campo para precio -->
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input class="form-control" type="number" step="0.01" name="precio" required>
                    </div>

                    <!-- Campo para cantidad -->
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input class="form-control" type="number" name="cantidad" required>
                    </div>

                    <!-- Selección de proveedores -->
                    <div class="mb-3">
                        <label class="form-label">Proveedores</label>
                        <select class="form-select" name="proveedores[]" multiple>
                            <?php
                            // Obtener lista de proveedores
                            $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                            $result_proveedores = mysqli_query($con, $query_proveedores);
                            
                            while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                echo "<option value='" . $proveedor['id_proveedor'] . "'>" . 
                                     htmlspecialchars($proveedor['nombre']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i>Guardar Item
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('crear-form').style.display='none'">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function updateFieldLabel() {
        const tipoSelect = document.getElementById("tipo");
        const camposMonturaDiv = document.getElementById("campos-montura");
        const camposCristalDiv = document.getElementById("campos-cristal");
        const materialLabel = document.querySelector("label[for='materialOAumento']");

        if (tipoSelect.value === "montura") {
            camposMonturaDiv.style.display = 'block';
            camposCristalDiv.style.display = 'none';
            document.querySelector("input[name='materialOAumento']").setAttribute('required', 'required');
            document.querySelector("select[name='tipo_cristal']").removeAttribute('required');
            document.querySelector("select[name='material_cristal']").removeAttribute('required');
        } else {
            camposMonturaDiv.style.display = 'none';
            camposCristalDiv.style.display = 'block';
            document.querySelector("input[name='materialOAumento']").removeAttribute('required');
            document.querySelector("select[name='tipo_cristal']").setAttribute('required', 'required');
            document.querySelector("select[name='material_cristal']").setAttribute('required', 'required');
        }
    }

    // Llamar a la función al cargar la página para establecer el estado inicial
    document.addEventListener('DOMContentLoaded', updateFieldLabel);

    // Funciones para la Orden de Compra
    function agregarItem() {
        const container = document.getElementById('items-container');
        const items = container.getElementsByClassName('item-orden');
        const newItem = items[0].cloneNode(true);
        
        // Limpiar valores
        newItem.querySelectorAll('select, input').forEach(element => {
            element.value = '';
        });
        
        // Mostrar botón de eliminar
        newItem.querySelector('.remove-item').style.display = 'block';
        
        container.appendChild(newItem);
    }

    function removeItem(button) {
        button.closest('.item-orden').remove();
    }

    async function actualizarItems(selectTipo) {
        const itemSelect = selectTipo.closest('.row').querySelector('.item-select');
        const tipo = selectTipo.value;
        
        // Limpiar select de items
        itemSelect.innerHTML = '<option value="">Seleccione item</option>';
        
        if (!tipo) return;

        try {
            const response = await fetch(`./php/get_items.php?tipo=${tipo}`);
            const items = await response.json();
            
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.marca} - ${item.material}`;
                itemSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar items:', error);
        }
    }

    // Manejar el envío del formulario de orden de compra
    document.getElementById('formOrdenCompra').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'La orden de compra se ha procesado correctamente',
                    confirmButtonColor: '#3085d6'
                });
                
                // Recargar la página para actualizar el inventario
                window.location.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Ha ocurrido un error al procesar la orden',
                    confirmButtonColor: '#3085d6'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ha ocurrido un error al procesar la orden',
                confirmButtonColor: '#3085d6'
            });
        }
    });
    </script>

    <!-- Incluir visualización de tablas -->
    <section class="container py-5">
        <div class="card info-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Registro de Stock</h3>
            </div>
            <div class="card-body p-0">
                <?php include "./php/mostrar_items.php"; ?>
            </div>
        </div>
    </section>

    <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" action="./php/editar_item.php" method="POST">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editTipo" name="tipo">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editMarca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="editMarca" name="marca" required>
                        </div>

                        <div class="mb-3" id="editDetalleContainer">
                            <label for="editDetalle" class="form-label" id="editDetalleLabel">Material</label>
                            <input type="text" class="form-control" id="editDetalle" name="detalle" required>
                        </div>

                        <div class="mb-3" id="editCristalContainer" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Cristal</label>
                                    <select class="form-select" id="editTipoCristal" name="tipo_cristal">
                                        <option value="monofocal">Monofocal</option>
                                        <option value="bifocal">Bifocal</option>
                                        <option value="multifocal">Multifocal</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Material del Cristal</label>
                                    <select class="form-select" id="editMaterialCristal" name="material_cristal">
                                        <option value="policarbonato">Policarbonato</option>
                                        <option value="hi-index">Hi-Index</option>
                                        <option value="tallados">Tallados</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editPrecio" class="form-label">Precio</label>
                            <input type="number" class="form-control" id="editPrecio" name="precio" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="editCantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="editCantidad" name="cantidad" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(tipo, id, marca, detalle, precio, cantidad) {
        // Obtener referencias a los elementos del modal
        const editId = document.getElementById('editId');
        const editTipo = document.getElementById('editTipo');
        const editMarca = document.getElementById('editMarca');
        const editDetalle = document.getElementById('editDetalle');
        const editDetalleLabel = document.getElementById('editDetalleLabel');
        const editDetalleContainer = document.getElementById('editDetalleContainer');
        const editCristalContainer = document.getElementById('editCristalContainer');
        const editPrecio = document.getElementById('editPrecio');
        const editCantidad = document.getElementById('editCantidad');
        const editTipoCristal = document.getElementById('editTipoCristal');
        const editMaterialCristal = document.getElementById('editMaterialCristal');

        // Verificar que todos los elementos existen
        if (!editId || !editTipo || !editMarca || !editDetalle || !editPrecio || !editCantidad) {
            console.error('Uno o más elementos del modal no se encontraron');
            return;
        }

        // Establecer valores
        editId.value = id;
        editTipo.value = tipo;
        editMarca.value = marca;
        editPrecio.value = parseFloat(precio).toFixed(2);
        editCantidad.value = cantidad;
        
        // Configurar campos según el tipo de item
        if (tipo === 'montura') {
            editDetalleLabel.textContent = 'Material';
            editDetalle.value = detalle;
            editDetalleContainer.style.display = 'block';
            editCristalContainer.style.display = 'none';
            editDetalle.setAttribute('required', 'required');
            editTipoCristal.removeAttribute('required');
            editMaterialCristal.removeAttribute('required');
        } else {
            // Separar tipo y material de cristal
            const [tipoCristal, materialCristal] = detalle.split(' - ');
            
            editDetalleContainer.style.display = 'none';
            editCristalContainer.style.display = 'block';
            
            editTipoCristal.value = tipoCristal;
            editMaterialCristal.value = materialCristal;
            
            editDetalle.removeAttribute('required');
            editTipoCristal.setAttribute('required', 'required');
            editMaterialCristal.setAttribute('required', 'required');
        }
        
        // Mostrar el modal usando Bootstrap
        const editModalElement = document.getElementById('editModal');
        if (editModalElement) {
            const editModal = new bootstrap.Modal(editModalElement, {
                keyboard: true,
                backdrop: 'static'
            });
            editModal.show();
        } else {
            console.error('Elemento del modal no encontrado');
        }
    }
    </script>

    <script>
    // Función para confirmar eliminación con SweetAlert
    function confirmarEliminacion(tipo, id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `./php/eliminar_item.php?tipo=${tipo}&id=${id}`;
            }
        });
    }

    // Funciones para paginación
    function cambiarPaginaMonturas(pagina) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('pagina_monturas', pagina);
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        
        fetch(newUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Actualizar solo las tablas y paginadores
                const tablaActual = document.querySelector('.table:nth-of-type(1)');
                const tablaNueva = doc.querySelector('.table:nth-of-type(1)');
                
                const paginadorActual = document.querySelector('nav[aria-label="Paginación de monturas"]');
                const paginadorNuevo = doc.querySelector('nav[aria-label="Paginación de monturas"]');
                
                if (tablaActual && tablaNueva && paginadorActual && paginadorNuevo) {
                    tablaActual.outerHTML = tablaNueva.outerHTML;
                    paginadorActual.outerHTML = paginadorNuevo.outerHTML;
                    history.pushState(null, '', newUrl);
                } else {
                    console.error('No se pudieron encontrar los elementos para actualizar');
                }
            })
            .catch(error => {
                console.error('Error al cambiar página de monturas:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cambiar de página. Intente nuevamente.'
                });
            });
    }

    function cambiarPaginaCristales(pagina) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('pagina_cristales', pagina);
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        
        fetch(newUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Actualizar solo las tablas y paginadores
                const tablaActual = document.querySelector('.table:nth-of-type(2)');
                const tablaNueva = doc.querySelector('.table:nth-of-type(2)');
                
                const paginadorActual = document.querySelector('nav[aria-label="Paginación de cristales"]');
                const paginadorNuevo = doc.querySelector('nav[aria-label="Paginación de cristales"]');
                
                if (tablaActual && tablaNueva && paginadorActual && paginadorNuevo) {
                    tablaActual.outerHTML = tablaNueva.outerHTML;
                    paginadorActual.outerHTML = paginadorNuevo.outerHTML;
                    history.pushState(null, '', newUrl);
                } else {
                    console.error('No se pudieron encontrar los elementos para actualizar');
                }
            })
            .catch(error => {
                console.error('Error al cambiar página de cristales:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cambiar de página. Intente nuevamente.'
                });
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