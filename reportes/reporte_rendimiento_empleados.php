<?php
// Desactivar la visualización de errores para evitar problemas con la generación del PDF
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../inc/conexionbd.php';
require_once '../fpdf/FPDF_UTF8.php';

// Verificar si el usuario está logueado y tiene permisos
if (!isset($_SESSION['id_empleado'])) {
    header('Location: ../login.php');
    exit;
}

$conn = connection();

// Obtener datos de rendimiento de empleados (pedidos por empleado)
$sql_rendimiento = "SELECT 
                    e.id_empleado as id, 
                    CONCAT(e.nombre_empleado, ' ', e.apellido_empleado) as nombre,
                    e.cargo,
                    COUNT(p.id_pedido) as total_pedidos,
                    SUM(IFNULL(m.precio, 0) + IFNULL(c1.precio, 0) + IFNULL(c2.precio, 0)) as monto_total,
                    AVG(CASE WHEN m.precio IS NOT NULL AND c1.precio IS NOT NULL AND c2.precio IS NOT NULL 
                         THEN m.precio + c1.precio + c2.precio ELSE NULL END) as promedio_pedido
                FROM empleados e
                LEFT JOIN pedidos p ON e.id_empleado = p.id_empleado
                LEFT JOIN monturas m ON p.id_montura = m.id_montura
                LEFT JOIN cristales c1 ON p.id_cristal1 = c1.id_cristal
                LEFT JOIN cristales c2 ON p.id_cristal2 = c2.id_cristal
                GROUP BY e.id_empleado
                ORDER BY monto_total DESC";
$query_rendimiento = mysqli_query($conn, $sql_rendimiento);

// Definir la clase PDF que extiende FPDF_UTF8
class PDF extends FPDF_UTF8 {
    // Método Header para el encabezado de cada página
    function Header() {
        // Logo (si existe)
        if(file_exists('../img/logo.png')) {
            $this->Image('../img/logo.png', 10, 8, 30);
        }
        // Título
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'OPTILENTE 2020', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Reporte de Rendimiento de Empleados - ' . date('d/m/Y'), 0, 1, 'C');
        // Salto de línea
        $this->Ln(10);
    }

    // Método Footer para el pie de cada página
    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        // Fuente Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Título del reporte
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE RENDIMIENTO DE EMPLEADOS', 0, 1, 'C');
$pdf->Ln(5);

// Sección de Rendimiento
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RENDIMIENTO POR EMPLEADO', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(80, 7, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cargo', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Total Pedidos', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Monto Total', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Promedio', 1, 1, 'C', true);

// Datos de rendimiento
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_pedidos_global = 0;
$monto_total_global = 0;
$mejor_empleado = null;
$empleados_data = array();

while ($row = mysqli_fetch_assoc($query_rendimiento)) {
    // Asegurar que los valores numéricos no sean NULL
    $row['total_pedidos'] = intval($row['total_pedidos']);
    $row['monto_total'] = is_null($row['monto_total']) ? 0 : floatval($row['monto_total']);
    $row['promedio_pedido'] = is_null($row['promedio_pedido']) ? 0 : floatval($row['promedio_pedido']);
    
    // Guardar datos para análisis
    $empleados_data[] = $row;
    
    // Acumular totales
    $total_pedidos_global += $row['total_pedidos'];
    $monto_total_global += $row['monto_total'];
    
    // Identificar mejor empleado
    if ($mejor_empleado === null || $row['monto_total'] > $mejor_empleado['monto_total']) {
        $mejor_empleado = $row;
    }
    
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(10, 6, $row['id'], 1, 0, 'C', true);
    $pdf->Cell(80, 6, $row['nombre'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['cargo'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['total_pedidos'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, '$' . number_format($row['monto_total'], 2), 1, 0, 'R', true);
    $pdf->Cell(25, 6, '$' . number_format($row['promedio_pedido'], 2), 1, 1, 'R', true);
}

// Resumen de rendimiento
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE RENDIMIENTO', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Mostrar resumen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de pedidos realizados:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_pedidos_global . ' pedidos', 1, 1, 'R');

$pdf->Cell(100, 7, 'Monto total de pedidos:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($monto_total_global, 2), 1, 1, 'R');

$promedio_global = $total_pedidos_global > 0 ? $monto_total_global / $total_pedidos_global : 0;
$pdf->Cell(100, 7, 'Promedio por pedido:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($promedio_global, 2), 1, 1, 'R');

// Mostrar mejor empleado
if ($mejor_empleado) {
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, 'Empleado con mejor rendimiento:', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 7, 'Nombre:', 1, 0, 'L');
    $pdf->Cell(90, 7, $mejor_empleado['nombre'], 1, 1, 'R');
    
    $pdf->Cell(100, 7, 'Cargo:', 1, 0, 'L');
    $pdf->Cell(90, 7, $mejor_empleado['cargo'], 1, 1, 'R');
    
    $pdf->Cell(100, 7, 'Total de pedidos:', 1, 0, 'L');
    $pdf->Cell(90, 7, $mejor_empleado['total_pedidos'] . ' pedidos', 1, 1, 'R');
    
    $pdf->Cell(100, 7, 'Monto total:', 1, 0, 'L');
    $pdf->Cell(90, 7, '$' . number_format($mejor_empleado['monto_total'], 2), 1, 1, 'R');
    
    $porcentaje = $monto_total_global > 0 ? ($mejor_empleado['monto_total'] / $monto_total_global) * 100 : 0;
    $pdf->Cell(100, 7, 'Porcentaje del total de pedidos:', 1, 0, 'L');
    $pdf->Cell(90, 7, number_format($porcentaje, 2) . '%', 1, 1, 'R');
}

// Salida del PDF
$pdf->Output('Reporte_Rendimiento_Empleados_' . date('Y-m-d') . '.pdf', 'I');
?>