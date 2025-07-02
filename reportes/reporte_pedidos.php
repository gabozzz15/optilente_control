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
        $this->Cell(0, 10, 'Reporte de Pedidos - ' . date('d/m/Y'), 0, 1, 'C');
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

// Obtener datos de pedidos
$sql_pedidos = "
SELECT
  p.id_pedido AS id,
  p.fecha_pedido,
  p.fecha_entrega,
  p.estado_pedido,
  CONCAT(dc.nombre, ' ', dc.apellido) AS nombre_cliente,
  CONCAT(e.nombre_empleado, ' ', e.apellido_empleado) AS nombre_empleado,
  m.marca AS marca_montura,
  m.precio AS precio_montura,
  c1.marca AS marca_cristal1,
  c1.precio AS precio_cristal1,
  c2.marca AS marca_cristal2,
  c2.precio AS precio_cristal2,
  (m.precio + c1.precio + c2.precio) AS total
FROM pedidos p
JOIN datos_clientes dc   ON p.cedula_cliente = dc.cedula_cliente
JOIN empleados e         ON p.id_empleado    = e.id_empleado
JOIN monturas m          ON p.id_montura     = m.id_montura
JOIN cristales c1        ON p.id_cristal1    = c1.id_cristal
JOIN cristales c2        ON p.id_cristal2    = c2.id_cristal
JOIN prescripcion pr     ON p.id_prescripcion= pr.id_prescripcion
WHERE m.marca != 'MONTURA PROPIA'
  AND c1.marca != 'CRISTAL PROPIO'
  AND c2.marca != 'CRISTAL PROPIO'
ORDER BY p.fecha_pedido DESC
";
$query_pedidos = mysqli_query($conn, $sql_pedidos);
// 1) Modifica la creación del objeto PDF para usar orientación horizontal A4
$pdf = new PDF('L','mm','A4');  
$pdf->AliasNbPages();
$pdf->AddPage();            

// 2) Ajusta los anchos de las columnas para que sumen aprox. 277mm netos (A4 = 297mm - márgenes)
$w = [
  'id'          => 10,
  'fecha_ped'   => 20,
  'fecha_ent'   => 20,
  'estado'      => 25,
  'cliente'     => 45,
  'empleado'    => 45,
  'montura'     => 30,
  'cristal1'    => 30,
  'cristal2'    => 30,
  'total'       => 22,
];

// Encabezados
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(240);
$pdf->Cell($w['id'],       7, 'ID',             1, 0, 'C', true);
$pdf->Cell($w['fecha_ped'],7, 'Fecha Pedido',   1, 0, 'C', true);
$pdf->Cell($w['fecha_ent'],7, 'Fecha Entrega',  1, 0, 'C', true);
$pdf->Cell($w['estado'],    7, 'Estado',         1, 0, 'C', true);
$pdf->Cell($w['cliente'],   7, 'Cliente',        1, 0, 'C', true);
$pdf->Cell($w['empleado'],  7, 'Empleado',       1, 0, 'C', true);
$pdf->Cell($w['montura'],   7, 'Montura',        1, 0, 'C', true);
$pdf->Cell($w['cristal1'],  7, 'Cristal 1',      1, 0, 'C', true);
$pdf->Cell($w['cristal2'],  7, 'Cristal 2',      1, 0, 'C', true);
$pdf->Cell($w['total'],     7, 'Total',          1, 1, 'C', true);

// Filas
$pdf->SetFont('Arial','',7);
$rowColor = false;
while($row = mysqli_fetch_assoc($query_pedidos)) {
    $pdf->SetFillColor($rowColor?255:245);
    $rowColor = !$rowColor;

    // ID, fechas, estado, nombres…
    $pdf->Cell($w['id'],       6, $row['id'],                   1, 0, 'C', true);
    $pdf->Cell($w['fecha_ped'],6, date('d/m/Y',strtotime($row['fecha_pedido'])),1, 0, 'C', true);
    $pdf->Cell($w['fecha_ent'],6, $row['fecha_entrega']?date('d/m/Y',strtotime($row['fecha_entrega'])):'Pendiente',1,0,'C',true);
    // color del estado
    list($r,$g,$b) = ['no disponible'=>[255,165, 0],'disponible'=>[0,128,0],'entregado'=>[0,0,255]][$row['estado_pedido']] ?? [0,0,0];
    $pdf->SetTextColor($r,$g,$b);
    $pdf->Cell($w['estado'],   6, $row['estado_pedido'],       1, 0, 'C', true);
    $pdf->SetTextColor(0);
    // resto de columnas
    $pdf->Cell($w['cliente'],   6, $row['nombre_cliente'],      1, 0, 'L', true);
    $pdf->Cell($w['empleado'],  6, $row['nombre_empleado'],     1, 0, 'L', true);
    $pdf->Cell($w['montura'],   6, $row['marca_montura'],       1, 0, 'L', true);
    $pdf->Cell($w['cristal1'],  6, $row['marca_cristal1'],      1, 0, 'L', true);
    $pdf->Cell($w['cristal2'],  6, $row['marca_cristal2'],      1, 0, 'L', true);
    $pdf->Cell($w['total'],     6, '$'.number_format($row['total'],2),1, 1, 'R', true);
}

// Datos de pedidos
$pdf->SetFont('Arial', '', 7);
$rowColor = false;
$total_pedidos = 0;
$total_monto = 0;
$pedidos_por_estado = array();
$pedidos_por_mes = array();

while ($row = mysqli_fetch_assoc($query_pedidos)) {
    // Alternar colores de fila
    $pdf->SetFillColor($rowColor ? 255 : 245, $rowColor ? 255 : 245, $rowColor ? 255 : 245);
    $rowColor = !$rowColor;
    
    $total_pedido = floatval($row['precio_montura']) + floatval($row['precio_cristal1']) + floatval($row['precio_cristal2']);

    $total_pedidos++;
    $total_monto += $total_pedido;
    
    // Contar por estado
    if (!isset($pedidos_por_estado[$row['estado_pedido']])) {
        $pedidos_por_estado[$row['estado_pedido']] = 0;
    }
    $pedidos_por_estado[$row['estado_pedido']]++;
    
    // Contar por mes
    $mes = date('Y-m', strtotime($row['fecha_pedido']));
    if (!isset($pedidos_por_mes[$mes])) {
        $pedidos_por_mes[$mes] = array('count' => 0, 'total' => 0);
    }
    $pedidos_por_mes[$mes]['count']++;
    $pedidos_por_mes[$mes]['total'] += $total_pedido;
    
    // Formatear fechas
    $fecha_pedido = date('d/m/Y', strtotime($row['fecha_pedido']));
    $fecha_entrega = $row['fecha_entrega'] ? date('d/m/Y', strtotime($row['fecha_entrega'])) : 'Pendiente';
    
    // Formatear nombres
    $cliente = $row['nombre_cliente'];
    $empleado = $row['nombre_empleado'];
    
    // Determinar color para el estado
    $color_estado = array(0, 0, 0); // Negro por defecto
    
    if ($row['estado_pedido'] == 'entregado') {
        $color_estado = array(0, 128, 0); // Verde
    } elseif ($row['estado_pedido'] == 'en proceso') {
        $color_estado = array(0, 0, 255); // Azul
    } elseif ($row['estado_pedido'] == 'pendiente') {
        $color_estado = array(255, 165, 0); // Naranja
    } elseif ($row['estado_pedido'] == 'cancelado') {
        $color_estado = array(255, 0, 0); // Rojo
    }
    
    $pdf->Cell(10, 6, $row['id'], 1, 0, 'C', true);
    $pdf->Cell(20, 6, $fecha_pedido, 1, 0, 'C', true);
    $pdf->Cell(20, 6, $fecha_entrega, 1, 0, 'C', true);
    
    // Cambiar color para el estado
    $pdf->SetTextColor($color_estado[0], $color_estado[1], $color_estado[2]);
    $pdf->Cell(20, 6, $row['estado_pedido'], 1, 0, 'C', true);
    $pdf->SetTextColor(0); // Restaurar color negro
    
    $pdf->Cell(35, 6, $cliente, 1, 0, 'L', true);
    $pdf->Cell(35, 6, $empleado, 1, 0, 'L', true);
    $pdf->Cell(25, 6, $row['marca_montura'], 1, 0, 'L', true);
    $pdf->Cell(25, 6, '$' . number_format($total_pedido, 2), 1, 1, 'R', true);
}

// Resumen de pedidos
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'RESUMEN DE PEDIDOS', 1, 1, 'C', true);
$pdf->SetTextColor(0);

// Mostrar resumen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Total de pedidos:', 1, 0, 'L');
$pdf->Cell(90, 7, $total_pedidos . ' pedidos', 1, 1, 'R');

$pdf->Cell(100, 7, 'Monto total de pedidos:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($total_monto, 2), 1, 1, 'R');

$promedio = $total_pedidos > 0 ? $total_monto / $total_pedidos : 0;
$pdf->Cell(100, 7, 'Valor promedio por pedido:', 1, 0, 'L');
$pdf->Cell(90, 7, '$' . number_format($promedio, 2), 1, 1, 'R');


// Salida del PDF
$pdf->Output('Reporte_Pedidos_' . date('Y-m-d') . '.pdf', 'I');
?>