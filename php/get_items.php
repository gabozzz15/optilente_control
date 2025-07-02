<?php
include "../inc/conexionbd.php";
$con = connection();

header('Content-Type: application/json');

if (!isset($_GET['tipo'])) {
    echo json_encode(['error' => 'Tipo no especificado']);
    exit;
}

$tipo = $_GET['tipo'];
$items = [];

if ($tipo === 'montura') {
    $query = "SELECT id_montura as id, marca, material FROM monturas WHERE marca != 'MONTURA PROPIA'";
    $result = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
} elseif ($tipo === 'cristal') {
    $query = "SELECT id_cristal as id, marca, CONCAT(tipo_cristal, ' - ', material_cristal) as material FROM cristales WHERE marca != 'CRISTAL PROPIO'";
    $result = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

echo json_encode($items);
?>