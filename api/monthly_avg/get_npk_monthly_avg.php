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

// npksensoraverage stores hourly-averaged rows (one per hour, computed by ESP32)
$sql = "SELECT
            DATE_FORMAT(timestamp, '%Y-%m') AS month,
            ROUND(AVG(temp),  2) AS avg_temperature,
            ROUND(AVG(moist), 2) AS avg_moisture,
            ROUND(AVG(ec),    4) AS avg_ec,
            ROUND(AVG(ph),    2) AS avg_ph,
            ROUND(AVG(n),     2) AS avg_nitrogen,
            ROUND(AVG(p),     2) AS avg_phosphorus,
            ROUND(AVG(k),     2) AS avg_potassium
        FROM npksensoraverage
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

if (count($rows) > 1) {
    $fields = ['avg_temperature', 'avg_moisture', 'avg_ec', 'avg_ph',
               'avg_nitrogen', 'avg_phosphorus', 'avg_potassium'];
    $sums = array_fill_keys($fields, 0);
    $cnt  = count($rows);
    foreach ($rows as $r) {
        foreach ($fields as $f) {
            $sums[$f] += floatval($r[$f]);
        }
    }
    $overall = ['month' => 'Overall Average'];
    foreach ($fields as $f) {
        $overall[$f] = round($sums[$f] / $cnt, $f === 'avg_ec' ? 4 : 2);
    }
    $rows[] = $overall;
}

$conn->close();
echo json_encode($rows);
?>
