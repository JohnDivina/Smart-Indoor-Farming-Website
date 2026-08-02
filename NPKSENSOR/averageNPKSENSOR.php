<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

date_default_timezone_set("Asia/Manila"); // Set the correct timezone

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['temp']) && isset($_POST['moist']) && isset($_POST['ph']) && isset($_POST['ec']) && isset($_POST['n']) && isset($_POST['p']) && isset($_POST['k'])) {
        $temp = $_POST['temp'];
        $moist = $_POST['moist'];
        $ph = $_POST['ph'];
        $ec = $_POST['ec'];
        $n = $_POST['n'];
        $p = $_POST['p'];
        $k = $_POST['k'];
        $timestamp = date("Y-m-d H:i:s"); // Get the current timestamp

        // Check if data already exists
        $check_sql = "SELECT id FROM npksensor ORDER BY id DESC LIMIT 1";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            // Data exists, update it
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $update_sql = "UPDATE npksensor SET temp = $temp, moist = $moist, ph = $ph, ec = $ec, n = $n, p = $p, k = $k, timestamp = '$timestamp' WHERE id = $id";
            if ($conn->query($update_sql) === TRUE) {
                echo "NPK Sensor data updated.";
            } else {
                echo "Error: " . $update_sql . "<br>" . $conn->error;
            }
        } else {
            // No data exists, insert new data
            $insert_sql = "INSERT INTO npksensor (temp, moist, ph, ec, n, p, k, timestamp) VALUES ($temp, $moist, $ph, $ec, $n, $p, $k, '$timestamp')";
            if ($conn->query($insert_sql) === TRUE) {
                echo "NPK Sensor data inserted.";
            } else {
                echo "Error: " . $insert_sql . "<br>" . $conn->error;
            }
        }
    } elseif (isset($_POST['heartbeat'])) {
        $sql = "INSERT INTO npk_status (sensor_id, last_heartbeat) VALUES (1, NOW()) ON DUPLICATE KEY UPDATE last_heartbeat = NOW()";
        if ($conn->query($sql) === TRUE) {
            echo "Heartbeat received";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT last_heartbeat FROM npk_status WHERE sensor_id = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastHeartbeat = strtotime($row['last_heartbeat']);
        $formattedHeartbeat = date("F j, Y h:i:s A", $lastHeartbeat);
        $currentTime = time();
        $formattedCurrentTime = date("F j, Y h:i:s A", $currentTime); // Format current time

        // Debug output:
        error_log("Server Current Time: " . $formattedCurrentTime);
        error_log("Last Heartbeat Time: " . $formattedHeartbeat);

        if ($currentTime - $lastHeartbeat > 60) {
            $status = "disconnected";
        } else {
            $status = "connected";
        }
        echo json_encode(["status" => $status, "lastHeartbeat" => $formattedHeartbeat]);
    } else {
        $status = "disconnected";
        echo json_encode(["status" => $status, "lastHeartbeat" => "N/A"]);
    }
}

$conn->close();
?>