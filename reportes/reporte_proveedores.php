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

// Obtener datos de proveedores con sus órdenes de compra
$sql_proveedores = "SELECT 
    p.id_proveedor,
    p.rif_proveedor,
    p.nombre,
    p.direccion,
    p.telefono,
    p.correo,
    (SELECT COUNT(*) FROM monturas_proveedores mp WHERE mp.id_proveedor = p.id_proveedor) AS total_ordenes_monturas,
    (SELECT COUNT(*) FROM cristales_proveedores cp WHERE cp.id_proveedor = p.id_proveedor) AS total_ordenes_cristales,
    (SELECT SUM(m.precio * mp.cantidad) FROM monturas_proveedores mp 
     JOIN monturas m ON mp.id_montura = m.id_montura 
     WHERE mp.id_proveedor = p.id_proveedor) AS total_monto_monturas,
    (SELECT SUM(c.precio * cp.cantidad) FROM cristales_proveedores cp 
     JOIN cristales c ON cp.id_cristal = c.id_cristal 
     WHERE cp.id_proveedor = p.id_proveedor) AS total_monto_cristales
FROM proveedor p
ORDER BY total_monto_monturas + total_monto_cristales DESC";
$query_proveedores = mysqli_query($conn, $sql_proveedores);

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
        $this->Cell(0, 10, 'Reporte de Proveedores - ' . date('d/m/Y'), 0, 1, 'C');
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
$pdf->Cell(0, 10, 'REPORTE DE PROVEEDORES', 0, 1, 'C');
$pdf->Ln(5);

// Sección de Proveedores
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'DETALLE DE PROVEEDORES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'RIF', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Teléfono', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Correo', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Órdenes Mont.', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Órdenes Crist.', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Total Monturas', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Total Cristales', 1, 1, 'C', true);

// Datos de proveedores
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_proveedores = 0;
$total_ordenes_monturas = 0;
$total_ordenes_cristales = 0;
$total_monto_monturas = 0;
$total_monto_cristales = 0;

while ($row = mysqli_fetch_assoc($query_proveedores)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_proveedores++;
    $total_ordenes_monturas += $row['total_ordenes_monturas'];
    $total_ordenes_cristales += $row['total_ordenes_cristales'];
    $total_monto_monturas += $row['total_monto_monturas'];
    $total_monto_cristales += $row['total_monto_cristales'];
    
    $pdf->Cell(15, 6, $row['id_proveedor'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['nombre'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, $row['rif_proveedor'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['telefono'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['correo'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['total_ordenes_monturas'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['total_ordenes_cristales'], 1, 0, 'C', true);
    $pdf->Cell(35, 6, '$' . number_format($row['total_monto_monturas'], 2), 1, 0, 'R', true);
    $pdf->Cell(35, 6, '$' . number_format($row['total_monto_cristales'], 2), 1, 1, 'R', true);
}

// Agregar página de detalles de contacto
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'INFORMACIÓN DE CONTACTO DE PROVEEDORES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Reiniciar consulta para detalles de contacto
mysqli_data_seek($query_proveedores, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Dirección', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Teléfono', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Correo', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$rowColor = false;

while ($row = mysqli_fetch_assoc($query_proveedores)) {
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(15, 6, $row['id_proveedor'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['nombre'], 1, 0, 'L', true);
    $pdf->Cell(50, 6, $row['direccion'], 1, 0, 'L', true);
    $pdf->Cell(40, 6, $row['telefono'], 1, 0, 'C', true);
    $pdf->Cell(50, 6, $row['correo'], 1, 1, 'L', true);
}

// Resumen de proveedores
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE PROVEEDORES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de proveedores:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_proveedores . ' proveedores', 1, 1, 'R');

$pdf->Cell(100, 7, 'Total de órdenes de monturas:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_ordenes_monturas . ' órdenes', 1, 1, 'R');

$pdf->Cell(100, 7, 'Total de órdenes de cristales:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_ordenes_cristales . ' órdenes', 1, 1, 'R');

$pdf->Cell(100, 7, 'Total invertido en monturas:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_monturas, 2), 1, 1, 'R');

$pdf->Cell(100, 7, 'Total invertido en cristales:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_cristales, 2), 1, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 7, 'TOTAL GENERAL INVERTIDO:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto_monturas + $total_monto_cristales, 2), 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Proveedores_' . date('Y-m-d') . '.pdf', 'I');
?>