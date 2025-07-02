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

// Obtener datos de prescripciones
$sql_prescripciones = "SELECT 
    pr.id_prescripcion,
    CONCAT(dc.nombre, ' ', dc.apellido) AS nombre_cliente,
    dc.cedula_cliente,
    pr.fecha_emision,
    pr.OD_esfera AS od_esfera,
    pr.OD_cilindro AS od_cilindro,
    pr.OD_eje AS od_eje,
    pr.OI_esfera AS oi_esfera,
    pr.OI_cilindro AS oi_cilindro,
    pr.OI_eje AS oi_eje,
    pr.adicion,
    pr.altura_pupilar,
    pr.distancia_pupilar,
    pr.observacion
FROM prescripcion pr
JOIN datos_clientes dc ON pr.id_cliente = dc.id_cliente
ORDER BY pr.fecha_emision DESC";
$query_prescripciones = mysqli_query($conn, $sql_prescripciones);

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
        $this->Cell(0, 10, 'Reporte de Prescripciones de Clientes - ' . date('d/m/Y'), 0, 1, 'C');
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
$pdf->Cell(0, 10, 'REPORTE DE PRESCRIPCIONES DE CLIENTES', 0, 1, 'C');
$pdf->Ln(5);

// Sección de Prescripciones
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'DETALLE DE PRESCRIPCIONES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cédula', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'OD Esfera', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'OD Cilindro', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'OD Eje', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'OI Esfera', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'OI Cilindro', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'OI Eje', 1, 1, 'C', true);

// Datos de prescripciones
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_prescripciones = 0;

while ($row = mysqli_fetch_assoc($query_prescripciones)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_prescripciones++;
    
    $pdf->Cell(15, 6, $row['id_prescripcion'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['nombre_cliente'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['cedula_cliente'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['fecha_emision'])), 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['od_esfera'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['od_cilindro'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['od_eje'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['oi_esfera'], 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['oi_cilindro'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['oi_eje'], 1, 1, 'C', true);
}

// Agregar página de detalles adicionales
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'INFORMACIÓN ADICIONAL DE PRESCRIPCIONES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Reiniciar consulta para detalles adicionales
mysqli_data_seek($query_prescripciones, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Adición', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Altura Pupilar', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Distancia Pupilar', 1, 0, 'C', true);
$pdf->Cell(60, 7, 'Observaciones', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$rowColor = false;

while ($row = mysqli_fetch_assoc($query_prescripciones)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(15, 6, $row['id_prescripcion'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['nombre_cliente'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['adicion'] ?: 'N/A', 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['altura_pupilar'] ?: 'N/A', 1, 0, 'C', true);
    $pdf->Cell(30, 6, $row['distancia_pupilar'] ?: 'N/A', 1, 0, 'C', true);
    $pdf->Cell(60, 6, $row['observacion'] ?: 'Sin observaciones', 1, 1, 'L', true);
}

// Resumen de prescripciones
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE PRESCRIPCIONES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de prescripciones:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_prescripciones . ' prescripciones', 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Prescripciones_' . date('Y-m-d') . '.pdf', 'I');
?>