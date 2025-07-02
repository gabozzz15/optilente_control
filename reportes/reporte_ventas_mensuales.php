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
        $this->Cell(0, 10, 'Reporte de Ventas Mensuales - ' . date('d/m/Y'), 0, 1, 'C');
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

    // Método para rotar texto
    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($angle == 0) {
            $this->_out('Q');
        } else {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
}

// Verificar si el usuario está logueado y tiene permisos
if (!isset($_SESSION['id_empleado'])) {
    header('Location: ../login.php');
    exit;
}

$conn = connection();

// Obtener datos de ventas mensuales
$sql = "
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
$query = mysqli_query($conn, $sql);

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Título del reporte
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219); // Color azul para el encabezado
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'VENTAS MENSUALES', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(60, 7, 'Mes', 1, 0, 'C', true);
$pdf->Cell(60, 7, 'Total Pedidos', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'Monto Total ($)', 1, 1, 'C', true);

// Datos de ventas mensuales
$pdf->SetFont('Arial', '', 10);
$rowColor = false;
$total_general_pedidos = 0;
$total_general_monto = 0;

while ($row = mysqli_fetch_assoc($query)) {
    // Formatear mes para mostrar nombre del mes
    $fecha = DateTime::createFromFormat('Y-m', $row['mes']);
    $mes_formateado = $fecha->format('F Y');
    
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $pdf->Cell(60, 6, $mes_formateado, 1, 0, 'L', true);
    $pdf->Cell(60, 6, $row['total_pedidos'], 1, 0, 'C', true);
    $pdf->Cell(70, 6, '$' . number_format($row['monto_total'], 2), 1, 1, 'R', true);
    
    // Acumular totales
    $total_general_pedidos += $row['total_pedidos'];
    $total_general_monto += $row['monto_total'];
}

// Mostrar totales generales
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 7, 'TOTAL GENERAL', 1, 0, 'C', true);
$pdf->Cell(60, 7, $total_general_pedidos, 1, 0, 'C', true);
$pdf->Cell(70, 7, '$' . number_format($total_general_monto, 2), 1, 1, 'R', true);

// Gráfico de barras para visualizar las ventas mensuales
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'GRÁFICO DE VENTAS MENSUALES', 0, 1, 'C');
$pdf->Ln(5);

// Reiniciar la consulta para el gráfico
mysqli_data_seek($query, 0);

// Configuración del gráfico
$margen_izquierdo = 30;
$ancho_grafico = 150;
$alto_grafico = 100;
$y_base = $pdf->GetY() + $alto_grafico;
$max_valor = 0;

// Encontrar el valor máximo para escalar el gráfico
while ($row = mysqli_fetch_assoc($query)) {
    if ($row['monto_total'] > $max_valor) {
        $max_valor = $row['monto_total'];
    }
}

// Reiniciar la consulta nuevamente
mysqli_data_seek($query, 0);

// Dibujar ejes
$pdf->Line($margen_izquierdo, $y_base, $margen_izquierdo + $ancho_grafico, $y_base); // Eje X
$pdf->Line($margen_izquierdo, $y_base, $margen_izquierdo, $y_base - $alto_grafico); // Eje Y

// Dibujar barras
$x = $margen_izquierdo + 10;
$ancho_barra = 15;
$espacio = 10;
$contador = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $fecha = DateTime::createFromFormat('Y-m', $row['mes']);
    $mes_corto = $fecha->format('M y');
    
    $altura_barra = ($row['monto_total'] / $max_valor) * $alto_grafico;
    
    // Dibujar barra
    $pdf->SetFillColor(52, 152, 219);
    $pdf->Rect($x, $y_base - $altura_barra, $ancho_barra, $altura_barra, 'F');
    
    // Etiqueta del mes
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($x - 5, $y_base + 2);
    $pdf->Cell($ancho_barra + 10, 5, $mes_corto, 0, 0, 'C');
    
    // Valor sobre la barra
    $pdf->SetXY($x - 5, $y_base - $altura_barra - 7);
    $pdf->Cell($ancho_barra + 10, 5, '$' . number_format($row['monto_total'], 0), 0, 0, 'C');
    
    $x += $ancho_barra + $espacio;
    $contador++;
    
    // Si hay demasiadas barras, pasar a una nueva línea
    if ($contador > 8) {
        break;
    }
}

// Leyenda del eje Y
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($margen_izquierdo - 25, $y_base - $alto_grafico);
$pdf->Cell(20, 10, '$' . number_format($max_valor, 0), 0, 0, 'R');

$pdf->SetXY($margen_izquierdo - 25, $y_base - ($alto_grafico / 2));
$pdf->Cell(20, 10, '$' . number_format($max_valor / 2, 0), 0, 0, 'R');

$pdf->SetXY($margen_izquierdo - 25, $y_base);
$pdf->Cell(20, 10, '$0', 0, 0, 'R');

// Título de los ejes
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY($margen_izquierdo + ($ancho_grafico / 2) - 20, $y_base + 15);
$pdf->Cell(40, 10, 'Meses', 0, 0, 'C');

$pdf->SetXY($margen_izquierdo - 25, $y_base - ($alto_grafico / 2) - 20);
$pdf->Rotate(90);
$pdf->Cell(40, 10, 'Monto ($)', 0, 0, 'C');
$pdf->Rotate(0);

// Salida del PDF
$pdf->Output('Reporte_Ventas_Mensuales_' . date('Y-m-d') . '.pdf', 'I');
?>