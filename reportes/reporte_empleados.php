<?php
// Desactivar la visualización de errores para evitar problemas con la generación del PDF
error_reporting(0);
ini_set('display_errors', 0);

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
        $this->Cell(0, 10, 'Reporte de Empleados - ' . date('d/m/Y'), 0, 1, 'C');
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

// Obtener datos de empleados
$sql_empleados = "SELECT 
                    id_empleado as id, 
                    CONCAT(nombre_empleado, ' ', apellido_empleado) as nombre_completo, 
                    cedula_empleado, 
                    correo, 
                    num_telefono, 
                    cargo, 
                    usuario
                 FROM empleados 
                 ORDER BY cargo, nombre_completo";
$query_empleados = mysqli_query($conn, $sql_empleados);

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Título del reporte
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE EMPLEADOS', 0, 1, 'C');
$pdf->Ln(5);

// Sección de Empleados
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'LISTA DE EMPLEADOS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(60, 7, 'Nombre Completo', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cedula', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Correo', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Telefono', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cargo', 1, 1, 'C', true);

// Datos de empleados
$pdf->SetFont('Arial', '', 9);
$rowColor = false;
$total_empleados = 0;
$cargos = array();

while ($row = mysqli_fetch_assoc($query_empleados)) {
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_empleados++;
    
    // Contar cargos
    if (!isset($cargos[$row['cargo']])) {
        $cargos[$row['cargo']] = 0;
    }
    $cargos[$row['cargo']]++;
    
    $pdf->Cell(10, 6, $row['id'], 1, 0, 'C', true);
    $pdf->Cell(60, 6, $row['nombre_completo'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['cedula_empleado'], 1, 0, 'C', true);
    $pdf->Cell(40, 6, $row['correo'], 1, 0, 'L', true);
    $pdf->Cell(30, 6, $row['num_telefono'], 1, 0, 'C', true);
    $pdf->Cell(25, 6, $row['cargo'], 1, 1, 'C', true);
}

// Resumen de empleados
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE PERSONAL', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Mostrar resumen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de empleados:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_empleados . ' personas', 1, 1, 'R');

// Salida del PDF
$pdf->Output('Reporte_Empleados_' . date('Y-m-d') . '.pdf', 'I');
?>