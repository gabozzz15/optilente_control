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

// Obtener datos de órdenes de compra para monturas
$sql_monturas = "SELECT 
    mp.id_proveedor_montura AS id,
    p.nombre AS proveedor,
    m.marca AS producto,
    mp.cantidad,
    mp.fecha_orden,
    mp.num_orden_compra,
    m.precio,
    (mp.cantidad * m.precio) AS total_orden
FROM monturas_proveedores mp
JOIN proveedor p ON mp.id_proveedor = p.id_proveedor
JOIN monturas m ON mp.id_montura = m.id_montura
WHERE m.marca != 'MONTURA PROPIA'
ORDER BY mp.fecha_orden DESC";
$query_monturas = mysqli_query($conn, $sql_monturas);

// Obtener datos de órdenes de compra para cristales
$sql_cristales = "SELECT 
    cp.id_proveedor_cristal AS id,
    p.nombre AS proveedor,
    c.marca AS producto,
    cp.cantidad,
    cp.fecha_orden,
    cp.num_orden_compra,
    c.precio,
    (cp.cantidad * c.precio) AS total_orden
FROM cristales_proveedores cp
JOIN proveedor p ON cp.id_proveedor = p.id_proveedor
JOIN cristales c ON cp.id_cristal = c.id_cristal
WHERE c.marca != 'CRISTAL PROPIO'
ORDER BY cp.fecha_orden DESC";
$query_cristales = mysqli_query($conn, $sql_cristales);

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
        $this->Cell(0, 10, 'Reporte de Órdenes de Compra - ' . date('d/m/Y'), 0, 1, 'C');
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
$pdf = new PDF('L', 'mm', 'A4');  // Orientación horizontal
$pdf->AliasNbPages();
$pdf->AddPage();

// Título del reporte
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE ÓRDENES DE COMPRA', 0, 1, 'C');
$pdf->Ln(5);

// Sección de Órdenes de Compra de Monturas
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'ÓRDENES DE COMPRA - MONTURAS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla de monturas
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Proveedor', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Fecha Orden', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Número Orden', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Precio Unit.', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Total Orden', 1, 1, 'C', true);

// Datos de órdenes de monturas
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_monturas = 0;
$total_monto_monturas = 0;

while ($row = mysqli_fetch_assoc($query_monturas)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_monturas += $row['cantidad'];
    $total_monto_monturas += $row['total_orden'];
    
    $pdf->Cell(15, 6, $row['id'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['proveedor'], 1, 0, 'L', true);
    $pdf->Cell(40, 6, $row['producto'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['cantidad'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['fecha_orden'])), 1, 0, 'C', true);
    $pdf->Cell(35, 6, $row['num_orden_compra'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, '$' . number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(30, 6, '$' . number_format($row['total_orden'], 2), 1, 1, 'R', true);
}

// Sección de Órdenes de Compra de Cristales
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'ÓRDENES DE COMPRA - CRISTALES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla de cristales
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Proveedor', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Fecha Orden', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Número Orden', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Precio Unit.', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Total Orden', 1, 1, 'C', true);

// Datos de órdenes de cristales
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_cristales = 0;
$total_monto_cristales = 0;

while ($row = mysqli_fetch_assoc($query_cristales)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_cristales += $row['cantidad'];
    $total_monto_cristales += $row['total_orden'];
    
    $pdf->Cell(15, 6, $row['id'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['proveedor'], 1, 0, 'L', true);
    $pdf->Cell(40, 6, $row['producto'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['cantidad'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['fecha_orden'])), 1, 0, 'C', true);
    $pdf->Cell(35, 6, $row['num_orden_compra'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, '$' . number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(30, 6, '$' . number_format($row['total_orden'], 2), 1, 1, 'R', true);
}

// Resumen de órdenes de compra
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE ÓRDENES DE COMPRA', 1, 1, 'C', true);
$pdf->SetTextColor(0);

$pdf->SetFont('Arial', '', 10);
// Resumen de monturas
$pdf->Cell(100, 7, 'Total de monturas compradas:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_monturas . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Monto total de órdenes de monturas:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_monturas, 2), 1, 1, 'R');

// Resumen de cristales
$pdf->Cell(100, 7, 'Total de cristales comprados:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_cristales . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Monto total de órdenes de cristales:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_cristales, 2), 1, 1, 'R');

// Total general
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 7, 'TOTAL GENERAL DE ÓRDENES DE COMPRA:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_monturas + $total_monto_cristales, 2), 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Ordenes_Compra_' . date('Y-m-d') . '.pdf', 'I');
?>