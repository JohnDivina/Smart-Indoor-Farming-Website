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

$start_date = $start_month . '-01';
$end_bound  = date('Y-m-01', strtotime($end_month . '-01 +1 month'));

if ($start_date > $end_bound) {
    echo json_encode(['error' => 'start_month cannot be after end_month.']);
    exit();
}

// NOTE: lightintensitysensor stores one pre-aggregated hourlyAverage per hour
// (sent by the ESP32 firmware). AVG(hourlyAverage) is the correct and only
// available granularity for monthly aggregation.
$sql = "SELECT
            DATE_FORMAT(timestamp, '%Y-%m') AS month,
            ROUND(AVG(hourlyAverage), 2)    AS avg_lux
        FROM lightintensitysensor
        WHERE hourlyAverage > 0
          AND timestamp >= ? AND timestamp < ?
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

if (count($rows) > 1) {
    $sumLux = 0; $cnt = count($rows);
    foreach ($rows as $r) {
        $sumLux += floatval($r['avg_lux']);
    }
    $rows[] = [
        'month'   => 'Overall Average',
        'avg_lux' => round($sumLux / $cnt, 2),
    ];
}

$conn->close();
echo json_encode($rows);
?>
