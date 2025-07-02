<?php
// Incluir conexión a la base de datos si aún no está incluida
if (!function_exists('connection')) {
    require_once __DIR__ . "/../inc/conexionbd.php";
}
$con = connection();

// Obtener todos los proveedores
$query_proveedores = "SELECT * FROM proveedor ORDER BY nombre";
$result_proveedores = mysqli_query($con, $query_proveedores);

if ($result_proveedores && mysqli_num_rows($result_proveedores) > 0) {
    while ($proveedor = mysqli_fetch_assoc($result_proveedores)) {
        echo '<div class="card mb-4">';
        echo '<div class="card-header bg-primary text-white">';
        echo '<h5 class="mb-0">' . htmlspecialchars($proveedor['nombre']) . ' (RIF: ' . htmlspecialchars($proveedor['rif_proveedor']) . ')</h5>';
        echo '</div>';
        echo '<div class="card-body">';
        
        // Información de contacto
        echo '<div class="mb-3">';
        echo '<p><strong>Dirección:</strong> ' . htmlspecialchars($proveedor['direccion']) . '</p>';
        echo '<p><strong>Teléfono:</strong> ' . htmlspecialchars($proveedor['telefono']) . '</p>';
        echo '<p><strong>Correo:</strong> ' . htmlspecialchars($proveedor['correo']) . '</p>';
        echo '</div>';
        
        // Obtener monturas asociadas a este proveedor
        $query_monturas = "SELECT m.* FROM monturas m 
                          INNER JOIN monturas_proveedores mp ON m.id_montura = mp.id_montura 
                          WHERE mp.id_proveedor = " . $proveedor['id_proveedor'];
        $result_monturas = mysqli_query($con, $query_monturas);
        
        if ($result_monturas && mysqli_num_rows($result_monturas) > 0) {
            echo '<h6 class="mt-3">Monturas suministradas:</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-striped">';
            echo '<thead><tr><th>ID</th><th>Marca</th><th>Material</th><th>Precio</th><th>Stock</th></tr></thead>';
            echo '<tbody>';
            
            while ($montura = mysqli_fetch_assoc($result_monturas)) {
                echo '<tr>';
                echo '<td>' . $montura['id_montura'] . '</td>';
                echo '<td>' . htmlspecialchars($montura['marca']) . '</td>';
                echo '<td>' . htmlspecialchars($montura['material']) . '</td>';
                echo '<td>$' . number_format($montura['precio'], 2) . '</td>';
                echo '<td>' . $montura['cantidad'] . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<p class="text-muted">Este proveedor no suministra monturas.</p>';
        }
        
        // Obtener cristales asociados a este proveedor
        $query_cristales = "SELECT c.* FROM cristales c 
                           INNER JOIN cristales_proveedores cp ON c.id_cristal = cp.id_cristal 
                           WHERE cp.id_proveedor = " . $proveedor['id_proveedor'];
        $result_cristales = mysqli_query($con, $query_cristales);
        
        if ($result_cristales && mysqli_num_rows($result_cristales) > 0) {
            echo '<h6 class="mt-4">Cristales suministrados:</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-striped">';
            echo '<thead><tr><th>ID</th><th>Marca</th><th>Tipo</th><th>Material</th><th>Precio</th><th>Stock</th></tr></thead>';
            echo '<tbody>';
            
            while ($cristal = mysqli_fetch_assoc($result_cristales)) {
                echo '<tr>';
                echo '<td>' . $cristal['id_cristal'] . '</td>';
                echo '<td>' . htmlspecialchars($cristal['marca']) . '</td>';
                echo '<td>' . htmlspecialchars($cristal['tipo_cristal']) . '</td>';
                echo '<td>' . htmlspecialchars($cristal['material_cristal']) . '</td>';
                echo '<td>$' . number_format($cristal['precio'], 2) . '</td>';
                echo '<td>' . $cristal['cantidad'] . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<p class="text-muted">Este proveedor no suministra cristales.</p>';
        }
        
        echo '</div>'; // card-body
        echo '</div>'; // card
    }
} else {
    echo '<div class="alert alert-info">No hay proveedores registrados en el sistema.</div>';
}
?>