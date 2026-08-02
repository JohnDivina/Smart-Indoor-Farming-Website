<?php
header('Content-Type: application/json');
require_once '../database.php';

$sensor = $_GET['sensor'] ?? 'npk';
$tableMap = [
    'npk' => 'npksensoraverage',
    'light' => 'lightintensitysensor',
    'environment' => 'temphumiditysensor'
];

$table = $tableMap[$sensor] ?? 'npksensoraverage';

try {
    $res = $conn->query("SELECT MAX(DATE(timestamp)) as latest_date FROM `$table` WHERE timestamp IS NOT NULL");
    $row = $res->fetch_assoc();
    echo json_encode(['latest_date' => $row['latest_date'] ?? date('Y-m-d')]);
} catch (Exception $e) {
    echo json_encode(['latest_date' => date('Y-m-d')]);
}
$conn->close();
?>
