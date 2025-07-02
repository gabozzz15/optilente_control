<?php
session_start();
require_once '../inc/conexionbd.php';
require_once '../fpdf/FPDF_UTF8.php';

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
        $this->Cell(0, 10, 'Reporte de Productos Vendidos - ' . date('d/m/Y'), 0, 1, 'C');
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

// Verificar si el usuario está logueado y tiene permisos
if (!isset($_SESSION['id_empleado'])) {
    header('Location: ../login.php');
    exit;
}

$conn = connection();

// Obtener datos de cristales más vendidos
$sql_cristales = "SELECT id_cristal, marca, tipo_cristal, material_cristal, precio, cantidad, contador_venta 
                 FROM cristales 
                 WHERE marca != 'CRISTAL PROPIO'
                 ORDER BY contador_venta DESC, marca ASC";
$query_cristales = mysqli_query($conn, $sql_cristales);

// Obtener datos de monturas más vendidas
$sql_monturas = "SELECT id_montura, marca, material, precio, cantidad, contador_venta 
                FROM monturas 
                WHERE marca != 'MONTURA PROPIA'
                ORDER BY contador_venta DESC, marca ASC";
$query_monturas = mysqli_query($conn, $sql_monturas);

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Sección de Cristales más vendidos
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'CRISTALES MAS VENDIDOS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Material', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Precio ($)', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Stock', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Unidades Vendidas', 1, 1, 'C', true);

// Datos de cristales
$pdf->SetFont('Arial', '', 10);
$rowColor = false;
$total_cristales_vendidos = 0;
$ingresos_cristales = 0;

while ($row = mysqli_fetch_assoc($query_cristales)) {
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $ventas = $row['contador_venta'] ? $row['contador_venta'] : 0;
    $total_cristales_vendidos += $ventas;
    $ingresos_cristales += $ventas * $row['precio'];
    
    $pdf->Cell(10, 6, $row['id_cristal'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['marca'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['tipo_cristal'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['material_cristal'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(25, 6, $row['cantidad'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $ventas, 1, 1, 'C', true);
}

$pdf->Ln(10);

// Sección de Monturas más vendidas
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'MONTURAS MAS VENDIDAS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Material', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Precio ($)', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Stock Actual', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Unidades Vendidas', 1, 1, 'C', true);

// Datos de monturas
$rowColor = false;
$total_monturas_vendidas = 0;
$ingresos_monturas = 0;

while ($row = mysqli_fetch_assoc($query_monturas)) {
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $ventas = $row['contador_venta'] ? $row['contador_venta'] : 0;
    $total_monturas_vendidas += $ventas;
    $ingresos_monturas += $ventas * $row['precio'];
    
    $pdf->Cell(10, 6, $row['id_montura'], 1, 0, 'C', true);
    $pdf->Cell(50, 6, $row['marca'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, $row['material'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(30, 6, $row['cantidad'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $ventas, 1, 1, 'C', true);
}

// Resumen de ventas
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE VENTAS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Mostrar resumen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de cristales vendidos:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_cristales_vendidos . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Ingresos por cristales:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($ingresos_cristales, 2), 1, 1, 'R');

$pdf->Ln(5);

$pdf->Cell(100, 7, 'Total de monturas vendidas:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_monturas_vendidas . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Ingresos por monturas:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($ingresos_monturas, 2), 1, 1, 'R');

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 7, 'TOTAL DE PRODUCTOS VENDIDOS:', 1, 0, 'L');
$pdf->Cell(90, 7, ($total_cristales_vendidos + $total_monturas_vendidas) . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'INGRESOS TOTALES POR VENTAS:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($ingresos_cristales + $ingresos_monturas, 2), 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Productos_Vendidos_' . date('Y-m-d') . '.pdf', 'I');
?>