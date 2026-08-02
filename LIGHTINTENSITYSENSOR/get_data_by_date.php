<?php
require_once '../database.php';

// Get the selected date from the request
$date = $_GET['date'] ?? null;

if ($date) {
    // Fetch data for the selected date
    $query = "SELECT id, hourlyAverage, timestamp FROM lightintensitysensor 
              WHERE hourlyAverage != 0 AND DATE(timestamp) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} else {
    echo json_encode(["error" => "No date provided"]);
}

$conn->close();
?>