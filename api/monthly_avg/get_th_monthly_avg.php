<?php
header('Content-Type: application/json');
require_once '../../database.php';

date_default_timezone_set('Asia/Manila');

$start_month = $_GET['start_month'] ?? null; // YYYY-MM
$end_month   = $_GET['end_month']   ?? null; // YYYY-MM

if (!$start_month || !preg_match('/^\d{4}-\d{2}$/', $start_month) ||
    !$end_month   || !preg_match('/^\d{4}-\d{2}$/', $end_month)) {
    echo json_encode(['error' => 'Invalid or missing month parameters (expected YYYY-MM).']);
    exit();
}

// Build date bounds: first day of start_month → first day after end_month
$start_date = $start_month . '-01';
$end_bound  = date('Y-m-01', strtotime($end_month . '-01 +1 month'));

if ($start_date > $end_bound) {
    echo json_encode(['error' => 'start_month cannot be after end_month.']);
    exit();
}

$sql = "SELECT
            DATE_FORMAT(timestamp, '%Y-%m') AS month,
            ROUND(AVG(temperature), 2)      AS avg_temperature,
            ROUND(AVG(humidity), 2)         AS avg_humidity
        FROM temphumiditysensor
        WHERE timestamp >= ? AND timestamp < ?
        GROUP BY DATE_FORMAT(timestamp, '%Y-%m')
        ORDER BY month ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Query prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param('ss', $start_date, $end_bound);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

// Append overall average row when more than one month
if (count($rows) > 1) {
    $sumTemp = 0; $sumHum = 0; $cnt = count($rows);
    foreach ($rows as $r) {
        $sumTemp += floatval($r['avg_temperature']);
        $sumHum  += floatval($r['avg_humidity']);
    }
    $rows[] = [
        'month'           => 'Overall Average',
        'avg_temperature' => round($sumTemp / $cnt, 2),
        'avg_humidity'    => round($sumHum  / $cnt, 2),
    ];
}

$conn->close();
echo json_encode($rows);
?>
