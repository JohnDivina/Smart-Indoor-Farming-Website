<?php
header('Content-Type: application/json');
require_once '../../database.php';

$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? date('Y-m-d');

try {
    $stmt = $conn->prepare("SELECT temp, moist, ph, ec, n, p, k, timestamp FROM npksensoraverage WHERE DATE(timestamp) BETWEEN ? AND ? ORDER BY timestamp ASC");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'temp' => floatval($row['temp']),
            'moist' => floatval($row['moist']),
            'ph' => floatval($row['ph']),
            'ec' => floatval($row['ec']),
            'n' => floatval($row['n']),
            'p' => floatval($row['p']),
            'k' => floatval($row['k']),
            'timestamp' => $row['timestamp']
        ];
    }
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
$conn->close();
?>
