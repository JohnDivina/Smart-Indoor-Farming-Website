<?php
header('Content-Type: application/json');
// filepath: c:\xampp\htdocs\smartfarmings\NPKSENSOR\get_data_by_date.php

require_once '../database.php';

// Get the selected date from the request
$date = $_GET['npkdate'] ?? null;

if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    // Fetch data for the selected date
    $query = "SELECT id, timestamp, temp, ph, moist, ec, n, p, k 
              FROM npksensoraverage 
              WHERE DATE(timestamp) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "No data found for the selected date."]);
    }
} else {
    echo json_encode(["error" => "Invalid or missing date parameter."]);
}

$conn->close();
?>