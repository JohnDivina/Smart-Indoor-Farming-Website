<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Get the date parameter from the request
$date = isset($_GET['date']) ? $_GET['date'] : null;

$query = "SELECT hourlyAverage, timestamp FROM lightintensitysensor WHERE hourlyAverage > 0";

if ($date) {
    $query .= " AND DATE(timestamp) = ?";
}

$query .= " ORDER BY timestamp ASC";

$stmt = $conn->prepare($query);

if ($date) {
    $stmt->bind_param("s", $date);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'hourlyAverage' => $row['hourlyAverage'],
            'timestamp' => $row['timestamp']
        ];
    }
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>