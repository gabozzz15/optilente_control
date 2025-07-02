<?php
// Configuración de búsqueda y paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Parámetros de búsqueda
$busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($con, trim($_GET['busqueda'])) : '';
$tipo_busqueda = isset($_GET['tipo_busqueda']) ? mysqli_real_escape_string($con, $_GET['tipo_busqueda']) : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? mysqli_real_escape_string($con, $_GET['fecha_inicio']) : '';
$fecha_fin = isset($_GET['fecha_fin']) ? mysqli_real_escape_string($con, $_GET['fecha_fin']) : '';

// condiciones de búsqueda
$condiciones = [];
if (!empty($busqueda)) {
    switch($tipo_busqueda) {
        case 'nombre':
            $condiciones[] = "CONCAT(dc.nombre, ' ', dc.apellido) LIKE '%$busqueda%'";
            break;
        case 'cedula':
            $condiciones[] = "dc.cedula_cliente LIKE '%$busqueda%'";
            break;
    }
}

// condiciones de fecha
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $condiciones[] = "p.fecha_pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
} elseif (!empty($fecha_inicio)) {
    $condiciones[] = "p.fecha_pedido >= '$fecha_inicio'";
} elseif (!empty($fecha_fin)) {
    $condiciones[] = "p.fecha_pedido <= '$fecha_fin'";
}

// consulta
$where_clause = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// consulta para contar total de registros
$sql_total = "SELECT COUNT(*) as total 
              FROM pedidos p 
              JOIN datos_clientes dc ON p.cedula_cliente = dc.cedula_cliente
              JOIN monturas m ON p.id_montura = m.id_montura
              JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
              JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
              JOIN prescripcion pr ON p.id_prescripcion = pr.id_prescripcion
              $where_clause";
$resultado_total = mysqli_query($con, $sql_total);
$total_registros = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// consulta para obtener los pedidos con información relacionada y paginación
$sql = "SELECT p.*, 
               CONCAT(dc.nombre, ' ', dc.apellido) as nombre_completo, 
               dc.cedula_cliente,
               m.marca as marca_montura, 
               c1.marca as marca_cristal1, 
               c1.tipo_cristal as tipo_cristal1,
               c1.material_cristal as material_cristal1,
               c2.marca as marca_cristal2, 
               c2.tipo_cristal as tipo_cristal2,
               c2.material_cristal as material_cristal2,
               pr.OD_esfera, pr.OD_cilindro, pr.OD_eje,
               pr.OI_esfera, pr.OI_cilindro, pr.OI_eje,
               pr.adicion, pr.altura_pupilar, pr.distancia_pupilar
        FROM pedidos p 
        JOIN datos_clientes dc ON p.cedula_cliente = dc.cedula_cliente
        JOIN monturas m ON p.id_montura = m.id_montura
        JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
        JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
        JOIN prescripcion pr ON p.id_prescripcion = pr.id_prescripcion
        $where_clause
        ORDER BY p.fecha_pedido DESC
        LIMIT $inicio, $registros_por_pagina";
$resultado_pedidos = mysqli_query($con, $sql);
?>

<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 mb-4 text-center"></h2>
        </div>
        
        <div class="col-12">
            <form method="GET" class="bg-light p-4 rounded shadow-sm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Búsqueda</label>
                        <select class="form-select" name="tipo_busqueda">
                            <option value="" <?php echo empty($tipo_busqueda) ? 'selected' : ''; ?>>Seleccionar</option>
                            <option value="nombre" <?php echo $tipo_busqueda == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                            <option value="cedula" <?php echo $tipo_busqueda == 'cedula' ? 'selected' : ''; ?>>Cédula</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Término de Búsqueda</label>
                        <input class="form-control" type="text" name="busqueda" 
                               placeholder="Buscar..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Fecha Inicio</label>
                        <input class="form-control" type="date" name="fecha_inicio" 
                               value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Fecha Fin</label>
                        <input class="form-control" type="date" name="fecha_fin" 
                               value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                        <a href="pedidos.php" class="btn btn-secondary btn-sm flex-grow-1">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Información de Resultados -->
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>
            <?php 
            echo "Mostrando " . ($resultado_pedidos ? mysqli_num_rows($resultado_pedidos) : 0) . " de $total_registros registros";
            if (!empty($busqueda) || !empty($fecha_inicio) || !empty($fecha_fin)) {
                echo " (Búsqueda aplicada)";
            }
            ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-custom">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Cédula</th>
                    <th>Montura</th>
                    <th>Cristales</th>
                    <th>Fórmula</th>
                    <th>Cantidad</th>
                    <th>Fecha Pedido</th>
                    <th>Fecha Entrega</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($resultado_pedidos && mysqli_num_rows($resultado_pedidos) > 0) {
                    while($pedido = mysqli_fetch_assoc($resultado_pedidos)): 
                        // Determinar las opciones de estado
                        $estados = [
                            'no disponible' => ['text' => 'No Disponible', 'badge' => 'bg-danger text-white'],
                            'disponible' => ['text' => 'Disponible', 'badge' => 'bg-warning text-dark'],
                            'entregado' => ['text' => 'Entregado', 'badge' => 'bg-success text-white']
                        ];
                        $estado_actual = $pedido['estado_pedido'];
                ?>
                        <tr class="text-center">
                            <td><?php echo $pedido['id_pedido']; ?></td>
                            <td><?php echo $pedido['nombre_completo']; ?></td>
                            <td><?php echo $pedido['cedula_cliente']; ?></td>
                            <td><?php echo $pedido['marca_montura']; ?></td>
                            <td><?php echo $pedido['marca_cristal1'] . ' (' . $pedido['tipo_cristal1'] . '/' . $pedido['material_cristal1'] . ') / ' . 
                                                    $pedido['marca_cristal2'] . ' (' . $pedido['tipo_cristal2'] . '/' . $pedido['material_cristal2'] . ')'; ?></td>
                            <td>
                                OD: ESF: <?php echo $pedido['OD_esfera']; ?> CIL: <?php echo $pedido['OD_cilindro']; ?> EJE: <?php echo $pedido['OD_eje']; ?><br>
                                OI: ESF: <?php echo $pedido['OI_esfera']; ?> CIL: <?php echo $pedido['OI_cilindro']; ?> EJE: <?php echo $pedido['OI_eje']; ?>
                                <?php if(!empty($pedido['adicion'])): ?>
                                <br>Adición: <?php echo $pedido['adicion']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo isset($pedido['cantidad']) ? $pedido['cantidad'] : 1; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td><?php 
                                // Formatear la fecha de entrega
                                if ($pedido['fecha_entrega']) {
                                    $fecha_entrega = date('d/m/Y', strtotime($pedido['fecha_entrega']));
                                    echo $fecha_entrega;
                                } else {
                                    echo '-';
                                }
                            ?></td>
                            <td>
                                <select class="form-select form-select-sm <?php echo $estados[$estado_actual]['badge']; ?>" 
                                        onchange="actualizarEstado(<?php echo $pedido['id_pedido']; ?>, this.value)"
                                        <?php echo $estado_actual == 'entregado' ? 'disabled' : ''; ?>>
                                    <?php foreach($estados as $valor => $info): ?>
                                        <option value="<?php echo $valor; ?>" 
                                                <?php echo $valor == $estado_actual ? 'selected' : ''; ?>>
                                            <?php echo $info['text']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="table-actions">
                                <button class="btn btn-sm btn-danger" 
                                        onclick="eliminarPedido(<?php echo $pedido['id_pedido']; ?>)"
                                        <?php echo $estado_actual == 'entregado' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; 
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No se encontraron resultados</td></tr>";
                }?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <nav aria-label="Paginación de pedidos">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo max(1, $pagina_actual - 1); ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo_busqueda=<?php echo urlencode($tipo_busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>" 
                   <?php echo $pagina_actual <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                    <i class="fas fa-chevron-left me-2"></i>Anterior
                </a>
            </li>
            
            <?php 
            // Mostrar primera página
            if ($pagina_actual > 2): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=1&busqueda=<?php echo urlencode($busqueda); ?>&tipo_busqueda=<?php echo urlencode($tipo_busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>">
                        1
                    </a>
                </li>
                <?php if ($pagina_actual > 3): ?>
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                <?php endif; 
            endif;

            // Páginas alrededor de la página actual
            $inicio_rango = max(1, $pagina_actual - 1);
            $fin_rango = min($total_paginas, $pagina_actual + 1);

            for ($i = $inicio_rango; $i <= $fin_rango; $i++):
                if ($i == $pagina_actual): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?php echo $i; ?></span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo_busqueda=<?php echo urlencode($tipo_busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endif; 
            endfor;

            // Mostrar última página
            if ($pagina_actual < $total_paginas - 1): ?>
                <?php if ($pagina_actual < $total_paginas - 2): ?>
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo_busqueda=<?php echo urlencode($tipo_busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>">
                        <?php echo $total_paginas; ?>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo min($total_paginas, $pagina_actual + 1); ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo_busqueda=<?php echo urlencode($tipo_busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>" 
                   <?php echo $pagina_actual >= $total_paginas ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                    Siguiente<i class="fas fa-chevron-right ms-2"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>

<script>
function actualizarEstado(idPedido, nuevoEstado) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Cambiar el estado del pedido a ${nuevoEstado}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
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
    });
}

function eliminarPedido(idPedido) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `./php/acciones_pedidos.php?eliminar_pedido=1&id_pedido=${idPedido}`;
        }
    });
}
</script>