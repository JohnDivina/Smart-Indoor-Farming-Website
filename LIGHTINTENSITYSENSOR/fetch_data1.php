<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the hourly average data from the lis_avgperhr table
$sql_hourly_avg = "SELECT AVG(avg_lux) AS avg_lux, DATE_FORMAT(avg_hourtime, '%Y-%m-%d %H:00:00') AS avg_time FROM lis_avgperhr GROUP BY DATE_FORMAT(avg_hourtime, '%Y-%m-%d %H')";
$result_hourly_avg = $conn->query($sql_hourly_avg);

// Prepare the data for the line chart
$hourly_data = array();
$daily_data = array();
$weekly_data = array();
$monthly_data = array();

while ($row = $result_hourly_avg->fetch_assoc()) {
    $avg_lux = $row['avg_lux'];
    $avg_time = $row['avg_time'];

    // Hourly data
    $hourly_data[] = array(
        'x' => $avg_time,
        'y' => $avg_lux
    );

    // Daily data
    $daily_date = date('Y-m-d', strtotime($avg_time));
    if (!isset($daily_data[$daily_date])) {
        $daily_data[$daily_date] = array(
            'x' => $daily_date,
            'y' => $avg_lux
        );
    } else {
        $daily_data[$daily_date]['y'] += $avg_lux;
    }

    // Weekly data
    $weekly_date = date('Y-W', strtotime($avg_time));
    if (!isset($weekly_data[$weekly_date])) {
        $weekly_data[$weekly_date] = array(
            'x' => $weekly_date,
            'y' => $avg_lux
        );
    } else {
        $weekly_data[$weekly_date]['y'] += $avg_lux;
    }

    // Monthly data
    $monthly_date = date('Y-m', strtotime($avg_time));
    if (!isset($monthly_data[$monthly_date])) {
        $monthly_data[$monthly_date] = array(
            'x' => $monthly_date,
            'y' => $avg_lux
        );
    } else {
        $monthly_data[$monthly_date]['y'] += $avg_lux;
    }
}

// Calculate the average values for daily, weekly, and monthly data
foreach ($daily_data as &$data) {
    $data['y'] /= 24; // Average per hour
}

foreach ($weekly_data as &$data) {
    $data['y'] /= (24 * 7); // Average per hour
}

foreach ($monthly_data as &$data) {
    $data['y'] /= (24 * date('t', strtotime($data['x']))); // Average per hour
}

// Convert the data arrays to JSON format
$hourly_data_json = json_encode(array_values($hourly_data));
$daily_data_json = json_encode(array_values($daily_data));
$weekly_data_json = json_encode(array_values($weekly_data));
$monthly_data_json = json_encode(array_values($monthly_data));
?>