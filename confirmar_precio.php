<?php
session_start();
include "./inc/conexionbd.php";
$con = connection();

// Verificar si hay datos temporales
if (!isset($_SESSION['temp_data'])) {
    header("Location: inventario.php");
    exit();
}

$data = $_SESSION['temp_data'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPTILENTE 2020 - Confirmar Precio</title>
    
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
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title text-center mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Conflicto de Precio
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <p>Se ha detectado un registro existente con un precio diferente:</p>
                            <hr>
                            <p><strong>Marca:</strong> <?php echo htmlspecialchars($data['marca']); ?></p>
                            <p><strong><?php echo $data['tipo'] === 'montura' ? 'Material' : 'Aumento'; ?>:</strong> 
                                <?php echo htmlspecialchars($data['materialOAumento']); ?></p>
                            <p><strong>Precio Actual:</strong> $<?php echo number_format($data['precio_actual'], 2); ?></p>
                            <p><strong>Precio Nuevo:</strong> $<?php echo number_format($data['precio_nuevo'], 2); ?></p>
                            <p><strong>Cantidad a agregar:</strong> <?php echo $data['cantidad']; ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Proveedores</label>
                            <select class="form-select" name="proveedores[]" multiple>
                                <?php
                                // Obtener lista de proveedores
                                $query_proveedores = "SELECT id_proveedor, nombre FROM proveedor";
                                $result_proveedores = mysqli_query($con, $query_proveedores);
                                
                                while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
                                    $selected = isset($data['proveedores']) && 
                                                in_array($proveedor['id_proveedor'], $data['proveedores']) 
                                                ? 'selected' : '';
                                    echo "<option value='" . $proveedor['id_proveedor'] . "' $selected>" . 
                                         htmlspecialchars($proveedor['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <form method="POST" action="php/crear_item.php">
                                <input type="hidden" name="confirm_action" value="update">
                                <?php if (!empty($data['proveedores'])): ?>
                                    <?php foreach ($data['proveedores'] as $proveedor): ?>
                                        <input type="hidden" name="proveedores[]" value="<?php echo $proveedor; ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-sync me-2"></i>Actualizar precio y sumar cantidad
                                </button>
                            </form>

                            <form method="POST" action="php/crear_item.php">
                                <input type="hidden" name="confirm_action" value="keep">
                                <?php if (!empty($data['proveedores'])): ?>
                                    <?php foreach ($data['proveedores'] as $proveedor): ?>
                                        <input type="hidden" name="proveedores[]" value="<?php echo $proveedor; ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-plus me-2"></i>Solo sumar cantidad
                                </button>
                            </form>
                        </div>

                        <div class="text-center mt-3">
                            <a href="inventario.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>