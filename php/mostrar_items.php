<?php
// Obtener el rol del usuario desde la sesión si no está definido
if (!isset($role) && isset($_SESSION['id_empleado'])) {
    $idUser = $_SESSION['id_empleado'];
    $sql_role = "SELECT cargo FROM empleados WHERE id_empleado = '$idUser'";
    $query_role = mysqli_query($con, $sql_role);

    if ($query_role) {
        $row_role = mysqli_fetch_assoc($query_role);
        $role = $row_role['cargo'];
    }
}

// Configuración de paginación
$items_por_pagina = 10;

// Paginación para monturas
$pagina_monturas = isset($_GET['pagina_monturas']) ? (int)$_GET['pagina_monturas'] : 1;
$offset_monturas = ($pagina_monturas - 1) * $items_por_pagina;

// Obtener total de monturas (excluyendo MONTURA PROPIA)
$total_monturas_query = "SELECT COUNT(*) as total FROM monturas WHERE marca != 'MONTURA PROPIA'";
$total_monturas_result = mysqli_query($con, $total_monturas_query);
$total_monturas = mysqli_fetch_assoc($total_monturas_result)['total'];
$total_paginas_monturas = ceil($total_monturas / $items_por_pagina);

// Mostrar tabla de monturas con paginación (excluyendo MONTURA PROPIA)
$queryMonturas = "SELECT * FROM monturas WHERE marca != 'MONTURA PROPIA' LIMIT $items_por_pagina OFFSET $offset_monturas";
$resultMonturas = mysqli_query($con, $queryMonturas);

?>
<h2 class="h3 mb-4 text-center">Monturas</h2>
<div class="table-responsive">
    <table class="table table-striped table-hover table-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Marca</th>
                <th>Material</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <?php if ($role == 'gerente'): ?>
                <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($resultMonturas)): ?>
                <tr>
                    <td><?php echo $item['id_montura']; ?></td>
                    <td><?php echo htmlspecialchars($item['marca']); ?></td>
                    <td><?php echo htmlspecialchars($item['material']); ?></td>
                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                    <?php if ($role == 'gerente'): ?>
                    <td class="table-actions">
                        <!-- Botón de Editar -->
                        <button class="btn btn-sm btn-info" 
                                onclick="openEditModal('montura', <?php echo $item['id_montura']; ?>, 
                                '<?php echo htmlspecialchars($item['marca']); ?>', 
                                '<?php echo htmlspecialchars($item['material']); ?>', 
                                <?php echo $item['precio']; ?>, 
                                <?php echo $item['cantidad']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Botón de Eliminar -->
                        <button class="btn btn-sm btn-danger" 
                               onclick="confirmarEliminacion('montura', <?php echo $item['id_montura']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Paginación para monturas -->
<nav aria-label="Paginación de monturas">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($pagina_monturas <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="#" 
               onclick="cambiarPaginaMonturas(<?php echo ($pagina_monturas - 1); ?>); return false;"
               <?php echo ($pagina_monturas <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                Anterior
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $total_paginas_monturas; $i++): ?>
            <li class="page-item <?php echo ($i == $pagina_monturas) ? 'active' : ''; ?>">
                <a class="page-link" href="#" 
                   onclick="cambiarPaginaMonturas(<?php echo $i; ?>); return false;">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo ($pagina_monturas >= $total_paginas_monturas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="#" 
               onclick="cambiarPaginaMonturas(<?php echo ($pagina_monturas + 1); ?>); return false;"
               <?php echo ($pagina_monturas >= $total_paginas_monturas) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                Siguiente
            </a>
        </li>
    </ul>
</nav>

<?php
// Paginación para cristales
$pagina_cristales = isset($_GET['pagina_cristales']) ? (int)$_GET['pagina_cristales'] : 1;
$offset_cristales = ($pagina_cristales - 1) * $items_por_pagina;

// Obtener total de cristales (excluyendo CRISTAL PROPIO)
$total_cristales_query = "SELECT COUNT(*) as total FROM cristales WHERE marca != 'CRISTAL PROPIO'";
$total_cristales_result = mysqli_query($con, $total_cristales_query);
$total_cristales = mysqli_fetch_assoc($total_cristales_result)['total'];
$total_paginas_cristales = ceil($total_cristales / $items_por_pagina);

// Mostrar tabla de cristales con paginación (excluyendo CRISTAL PROPIO)
$queryCristales = "SELECT * FROM cristales WHERE marca != 'CRISTAL PROPIO' LIMIT $items_por_pagina OFFSET $offset_cristales";
$resultCristales = mysqli_query($con, $queryCristales);
?>

<h2 class="h3 mb-4 text-center mt-5">Cristales</h2>
<div class="table-responsive">
    <table class="table table-striped table-hover table-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Marca</th>
                <th>Tipo de Cristal</th>
                <th>Material</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <?php if ($role == 'gerente'): ?>
                <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($resultCristales)): ?>
                <tr>
                    <td><?php echo $item['id_cristal']; ?></td>
                    <td><?php echo htmlspecialchars($item['marca']); ?></td>
                    <td><?php echo htmlspecialchars($item['tipo_cristal']); ?></td>
                    <td><?php echo htmlspecialchars($item['material_cristal']); ?></td>
                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                    <?php if ($role == 'gerente'): ?>
                    <td class="table-actions">
                        <!-- Botón de Editar -->
                        <button class="btn btn-sm btn-info" 
                                onclick="openEditModal('cristal', <?php echo $item['id_cristal']; ?>, 
                                '<?php echo htmlspecialchars($item['marca']); ?>', 
                                '<?php echo htmlspecialchars($item['tipo_cristal'] . ' - ' . $item['material_cristal']); ?>', 
                                <?php echo $item['precio']; ?>, 
                                <?php echo $item['cantidad']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Botón de Eliminar -->
                        <button class="btn btn-sm btn-danger" 
                               onclick="confirmarEliminacion('cristal', <?php echo $item['id_cristal']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Paginación para cristales -->
<nav aria-label="Paginación de cristales">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($pagina_cristales <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="#" 
               onclick="cambiarPaginaCristales(<?php echo ($pagina_cristales - 1); ?>); return false;"
               <?php echo ($pagina_cristales <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                Anterior
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $total_paginas_cristales; $i++): ?>
            <li class="page-item <?php echo ($i == $pagina_cristales) ? 'active' : ''; ?>">
                <a class="page-link" href="#" 
                   onclick="cambiarPaginaCristales(<?php echo $i; ?>); return false;">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo ($pagina_cristales >= $total_paginas_cristales) ? 'disabled' : ''; ?>">
            <a class="page-link" href="#" 
               onclick="cambiarPaginaCristales(<?php echo ($pagina_cristales + 1); ?>); return false;"
               <?php echo ($pagina_cristales >= $total_paginas_cristales) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                Siguiente
            </a>
        </li>
    </ul>
</nav>