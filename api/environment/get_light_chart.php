<?php
header('Content-Type: application/json');
require_once '../../database.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    // get from lightintensitysensor which stores the averages
    $start = $date . ' 00:00:00';
    $end = $date . ' 23:59:59';
    $stmt = $conn->prepare("SELECT hourlyAverage, timestamp FROM lightintensitysensor WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp ASC");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'lux' => floatval($row['hourlyAverage']),
            'timestamp' => $row['timestamp']
        ];
    }
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode([]);
}
$conn->close();
?>
