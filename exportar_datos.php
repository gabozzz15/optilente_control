<?php
    session_start();
    
    // Intentar múltiples rutas para autoload
    $autoload_paths = [
        'vendor/autoload.php',
        '../vendor/autoload.php',
        'libs/phpspreadsheet/vendor/autoload.php',
        '../libs/phpspreadsheet/vendor/autoload.php'
    ];

    $autoload_found = false;
    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            require $path;
            $autoload_found = true;
            break;
        }
    }

    if (!$autoload_found) {
        // Si no se encuentra el autoload, cargar manualmente las clases necesarias
        require_once 'libs/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';
        require_once 'libs/phpspreadsheet/src/PhpSpreadsheet/Writer/Xlsx.php';
    }

    include "./inc/conexionbd.php";
    
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $con = connection();

    // Función para exportar datos
    function exportarDatos($tipo, $con) {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            switch($tipo) {
                case 'stock':
                    $query = "
                        SELECT 'Montura' as tipo, marca, material as detalle, precio, cantidad, IFNULL(contador_venta, 0) as contador_venta 
                        FROM monturas
                        UNION ALL
                        SELECT 'Cristal', marca, tipo_cristal as detalle, precio, cantidad, IFNULL(contador_venta, 0) 
                        FROM cristales
                    ";
                    $sheet->setCellValue('A1', 'Tipo');
                    $sheet->setCellValue('B1', 'Marca');
                    $sheet->setCellValue('C1', 'Detalle');
                    $sheet->setCellValue('D1', 'Precio');
                    $sheet->setCellValue('E1', 'Cantidad');
                    $sheet->setCellValue('F1', 'Contador de Ventas');
                    break;

                case 'pedidos':
                    $query = "
                        SELECT p.id_pedido, 
                               CONCAT(dc.nombre, ' ', dc.apellido) as nombre_cliente, 
                               m.marca as montura, 
                               c1.marca as cristal_derecho, 
                               c2.marca as cristal_izquierdo, 
                               p.fecha_pedido, 
                               p.fecha_entrega, 
                               p.estado_pedido,
                               CONCAT(pr.OD_esfera, '/', pr.OD_cilindro, '/', pr.OD_eje, ' - ', 
                                     pr.OI_esfera, '/', pr.OI_cilindro, '/', pr.OI_eje) as formula
                        FROM pedidos p
                        JOIN datos_clientes dc ON p.cedula_cliente = dc.cedula_cliente
                        JOIN monturas m ON p.id_montura = m.id_montura
                        JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
                        JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
                        JOIN prescripcion pr ON p.id_prescripcion = pr.id_prescripcion
                    ";
                    $sheet->setCellValue('A1', 'ID Pedido');
                    $sheet->setCellValue('B1', 'Cliente');
                    $sheet->setCellValue('C1', 'Montura');
                    $sheet->setCellValue('D1', 'Cristal Derecho');
                    $sheet->setCellValue('E1', 'Cristal Izquierdo');
                    $sheet->setCellValue('F1', 'Fecha Pedido');
                    $sheet->setCellValue('G1', 'Fecha Entrega');
                    $sheet->setCellValue('H1', 'Estado');
                    $sheet->setCellValue('I1', 'Fórmula');
                    break;

                case 'empleados':
                    $query = "
                        SELECT cedula_empleado, 
                               CONCAT(nombre_empleado, ' ', apellido_empleado) as nombre_completo, 
                               cargo, 
                               correo, 
                               num_telefono, 
                               usuario
                        FROM empleados
                    ";
                    $sheet->setCellValue('A1', 'Cédula');
                    $sheet->setCellValue('B1', 'Nombre');
                    $sheet->setCellValue('C1', 'Cargo');
                    $sheet->setCellValue('D1', 'Correo');
                    $sheet->setCellValue('E1', 'Teléfono');
                    $sheet->setCellValue('F1', 'Usuario');
                    break;

                case 'productos_vendidos':
                    $query = "
                        SELECT 'Montura' as tipo, marca, material as detalle, 
                               precio, IFNULL(contador_venta, 0) as contador_venta 
                        FROM monturas
                        UNION ALL
                        SELECT 'Cristal', marca, tipo_cristal, precio, IFNULL(contador_venta, 0) 
                        FROM cristales
                        ORDER BY contador_venta DESC
                    ";
                    $sheet->setCellValue('A1', 'Tipo');
                    $sheet->setCellValue('B1', 'Marca');
                    $sheet->setCellValue('C1', 'Detalle');
                    $sheet->setCellValue('D1', 'Precio');
                    $sheet->setCellValue('E1', 'Contador de Ventas');
                    break;

                case 'rendimiento_empleados':
                    $query = "
                        SELECT e.cedula_empleado, e.nombre_completo, e.cargo,
                               COUNT(p.id_pedido) as total_pedidos,
                               SUM(m.precio + c1.precio + c2.precio) as monto_total
                        FROM empleados e
                        LEFT JOIN pedidos p ON e.id_empleado = p.id_empleado
                        LEFT JOIN monturas m ON p.id_montura = m.id_montura
                        LEFT JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
                        LEFT JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
                        GROUP BY e.id_empleado
                    ";
                    $sheet->setCellValue('A1', 'Cédula');
                    $sheet->setCellValue('B1', 'Nombre');
                    $sheet->setCellValue('C1', 'Cargo');
                    $sheet->setCellValue('D1', 'Total Pedidos');
                    $sheet->setCellValue('E1', 'Monto Total');
                    break;

                case 'ventas_mensuales':
                    $query = "
                        SELECT 
                            DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
                            COUNT(id_pedido) as total_pedidos,
                            SUM(m.precio + c1.precio + c2.precio) as monto_total
                        FROM pedidos p
                        JOIN monturas m ON p.id_montura = m.id_montura
                        JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
                        JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
                        GROUP BY mes
                        ORDER BY mes
                    ";
                    $sheet->setCellValue('A1', 'Mes');
                    $sheet->setCellValue('B1', 'Total Pedidos');
                    $sheet->setCellValue('C1', 'Monto Total');
                    break;

                case 'proveedores':
                    $query = "
                        SELECT id_proveedor, nombre, direccion, 
                               telefono, correo, rif_proveedor as tipo_producto
                        FROM proveedor
                    ";
                    $sheet->setCellValue('A1', 'ID');
                    $sheet->setCellValue('B1', 'Nombre');
                    $sheet->setCellValue('C1', 'Dirección');
                    $sheet->setCellValue('D1', 'Teléfono');
                    $sheet->setCellValue('E1', 'Correo');
                    $sheet->setCellValue('F1', 'RIF');
                    break;

                case 'prescripciones':
                    $query = "
                        SELECT p.id_prescripcion, 
                               CONCAT(dc.nombre, ' ', dc.apellido) as nombre_cliente, 
                               p.fecha_emision,
                               p.OD_esfera, p.OD_cilindro, p.OD_eje, p.adicion,
                               p.OI_esfera, p.OI_cilindro, p.OI_eje,
                               p.altura_pupilar, p.distancia_pupilar,
                               p.observacion
                        FROM prescripcion p
                        JOIN datos_clientes dc ON p.id_cliente = dc.id_cliente
                    ";
                    $sheet->setCellValue('A1', 'ID');
                    $sheet->setCellValue('B1', 'Cliente');
                    $sheet->setCellValue('C1', 'Fecha Examen');
                    $sheet->setCellValue('D1', 'Esfera OD');
                    $sheet->setCellValue('E1', 'Cilindro OD');
                    $sheet->setCellValue('F1', 'Eje OD');
                    $sheet->setCellValue('G1', 'Adición');
                    $sheet->setCellValue('H1', 'Esfera OI');
                    $sheet->setCellValue('I1', 'Cilindro OI');
                    $sheet->setCellValue('J1', 'Eje OI');
                    $sheet->setCellValue('K1', 'Altura Pupilar');
                    $sheet->setCellValue('L1', 'Distancia Pupilar');
                    $sheet->setCellValue('M1', 'Observaciones');
                    break;

                case 'ordenes_compra':
                    $query = "
                        SELECT oc.id_orden, p.nombre as nombre_proveedor, oc.fecha_orden,
                               oc.detalle_orden, oc.monto_total, oc.estado
                        FROM ordenes_compra oc
                        JOIN proveedor p ON oc.id_proveedor = p.id_proveedor
                    ";
                    $sheet->setCellValue('A1', 'ID Orden');
                    $sheet->setCellValue('B1', 'Proveedor');
                    $sheet->setCellValue('C1', 'Fecha');
                    $sheet->setCellValue('D1', 'Detalle');
                    $sheet->setCellValue('E1', 'Monto Total');
                    $sheet->setCellValue('F1', 'Estado');
                    break;

                default:
                    throw new Exception("Tipo de exportación no válido");
            }

            // Ejecutar consulta
            $result = mysqli_query($con, $query);
            
            if (!$result) {
                throw new Exception("Error en la consulta: " . mysqli_error($con));
            }

            // Llenar datos
            $row = 2;
            while ($data = mysqli_fetch_array($result)) {
                $col = 'A';
                // Iterar sobre los datos usando el índice numérico
                for ($i = 0; $i < count($data) / 2; $i++) {
                    // Verificar si el valor no es nulo
                    $cellValue = $data[$i] !== null ? $data[$i] : 'N/A';
                    $sheet->setCellValue($col . $row, $cellValue);
                    $col++;
                }
                $row++;
            }

            // Si no hay datos, mostrar un mensaje
            if ($row == 2) {
                $sheet->setCellValue('A2', 'No hay datos disponibles');
            }

            // Estilo de encabezado
            $sheet->getStyle('A1:L1')->getFont()->setBold(true);

            // Ajustar ancho de columnas
            $columnas = range('A', 'L');
            foreach ($columnas as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Preparar para descargar
            $writer = new Xlsx($spreadsheet);
            $filename = 'Reporte_' . ucfirst($tipo) . '_' . date('Y-m-d') . '.xlsx';

            // Encabezados para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Enviar archivo
            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            // Manejar errores
            error_log('Error en exportación de datos: ' . $e->getMessage());
            
            // Mostrar mensaje de error al usuario
            header('Content-Type: text/html; charset=utf-8');
            echo "Error al generar el reporte: " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "Por favor, contacte al administrador del sistema.<br>";
            echo "Detalles técnicos: " . htmlspecialchars($e->getTraceAsString());
            exit;
        }
    }

    // Manejar la exportación si se solicita
    if (isset($_GET['exportar'])) {
        $tipo = $_GET['exportar'];
        exportarDatos($tipo, $con);
    }

?>