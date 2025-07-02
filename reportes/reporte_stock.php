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
        $this->Cell(0, 10, 'Reporte de Stock - ' . date('d/m/Y'), 0, 1, 'C');
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

// Obtener datos de cristales
$sql_cristales = "SELECT id_cristal, marca, tipo_cristal, material_cristal, precio, cantidad FROM cristales WHERE marca != 'CRISTAL PROPIO' ORDER BY marca, tipo_cristal";
$query_cristales = mysqli_query($conn, $sql_cristales);

// Obtener datos de monturas
$sql_monturas = "SELECT id_montura, marca, material, precio, cantidad FROM monturas WHERE marca != 'MONTURA PROPIA' ORDER BY marca, material";
$query_monturas = mysqli_query($conn, $sql_monturas);

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Sección de Cristales
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'INVENTARIO DE CRISTALES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Material', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Precio ($)', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Estado', 1, 1, 'C', true);

// Datos de cristales
$pdf->SetFont('Arial', '', 10);
$rowColor = false;

while ($row = mysqli_fetch_assoc($query_cristales)) {
    // Determinar estado del stock
    $estado = 'Normal';
    $colorEstado = array(0, 0, 0); // Negro por defecto
    
    if ($row['cantidad'] <= 0) {
        $estado = 'Agotado';
        $colorEstado = array(255, 0, 0); // Rojo
    } elseif ($row['cantidad'] <= 5) {
        $estado = 'Bajo';
        $colorEstado = array(255, 165, 0); // Naranja
    }
    
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(10, 6, $row['id_cristal'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['marca'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, $row['tipo_cristal'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['material_cristal'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(20, 6, $row['cantidad'], 1, 0, 'C', true);
    
    // Cambiar color para el estado
    $pdf->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
    $pdf->Cell(40, 6, $estado, 1, 1, 'C', true);
    $pdf->SetTextColor(0); // Restaurar color negro
}

$pdf->Ln(10);

// Sección de Monturas
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'INVENTARIO DE MONTURAS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Marca', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Material', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Precio ($)', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Estado', 1, 1, 'C', true);

// Datos de monturas
$rowColor = false;

while ($row = mysqli_fetch_assoc($query_monturas)) {
    // Determinar estado del stock
    $estado = 'Normal';
    $colorEstado = array(0, 0, 0); // Negro por defecto
    
    if ($row['cantidad'] <= 0) {
        $estado = 'Agotado';
        $colorEstado = array(255, 0, 0); // Rojo
    } elseif ($row['cantidad'] <= 5) {
        $estado = 'Bajo';
        $colorEstado = array(255, 165, 0); // Naranja
    }
    
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(10, 6, $row['id_montura'], 1, 0, 'C', true);
    $pdf->Cell(50, 6, $row['marca'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, $row['material'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, number_format($row['precio'], 2), 1, 0, 'R', true);
    $pdf->Cell(30, 6, $row['cantidad'], 1, 0, 'C', true);
    
    // Cambiar color para el estado
    $pdf->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
    $pdf->Cell(40, 6, $estado, 1, 1, 'C', true);
    $pdf->SetTextColor(0); // Restaurar color negro
}

// Resumen de inventario
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE INVENTARIO', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Calcular totales
mysqli_data_seek($query_cristales, 0);
mysqli_data_seek($query_monturas, 0);

$total_cristales = 0;
$valor_cristales = 0;
$cristales_agotados = 0;
$cristales_bajos = 0;

while ($row = mysqli_fetch_assoc($query_cristales)) {
    $total_cristales += $row['cantidad'];
    $valor_cristales += $row['cantidad'] * $row['precio'];
    
    if ($row['cantidad'] <= 0) {
        $cristales_agotados++;
    } elseif ($row['cantidad'] <= 5) {
        $cristales_bajos++;
    }
}

$total_monturas = 0;
$valor_monturas = 0;
$monturas_agotadas = 0;
$monturas_bajas = 0;

while ($row = mysqli_fetch_assoc($query_monturas)) {
    $total_monturas += $row['cantidad'];
    $valor_monturas += $row['cantidad'] * $row['precio'];
    
    if ($row['cantidad'] <= 0) {
        $monturas_agotadas++;
    } elseif ($row['cantidad'] <= 5) {
        $monturas_bajas++;
    }
}

// Mostrar resumen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de cristales en inventario:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_cristales . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Valor total de cristales:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($valor_cristales, 2), 1, 1, 'R');

$pdf->Cell(100, 7, 'Cristales agotados:', 1, 0, 'L');
$pdf->Cell(90, 7, $cristales_agotados . ' tipos', 1, 1, 'R');

$pdf->Cell(100, 7, 'Cristales con stock bajo:', 1, 0, 'L');
$pdf->Cell(90, 7, $cristales_bajos . ' tipos', 1, 1, 'R');

$pdf->Ln(5);

$pdf->Cell(100, 7, 'Total de monturas en inventario:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_monturas . ' unidades', 1, 1, 'R');

$pdf->Cell(100, 7, 'Valor total de monturas:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($valor_monturas, 2), 1, 1, 'R');

$pdf->Cell(100, 7, 'Monturas agotadas:', 1, 0, 'L');
$pdf->Cell(90, 7, $monturas_agotadas . ' tipos', 1, 1, 'R');

$pdf->Cell(100, 7, 'Monturas con stock bajo:', 1, 0, 'L');
$pdf->Cell(90, 7, $monturas_bajas . ' tipos', 1, 1, 'R');

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 7, 'VALOR TOTAL DEL INVENTARIO:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($valor_cristales + $valor_monturas, 2), 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Stock_' . date('Y-m-d') . '.pdf', 'I');
?>