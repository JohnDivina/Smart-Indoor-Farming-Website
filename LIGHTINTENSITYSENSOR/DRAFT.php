<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm2";

date_default_timezone_set("UTC"); 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lux1 = isset($_POST['lux1']) ? (float) $_POST['lux1'] : 0.0;
$lux2 = isset($_POST['lux2']) ? (float) $_POST['lux2'] : 0.0;
$lux3 = isset($_POST['lux3']) ? (float) $_POST['lux3'] : 0.0;
$lux4 = isset($_POST['lux4']) ? (float) $_POST['lux4'] : 0.0;
$hourlyAverage = isset($_POST['hourlyAverage']) ? (float) $_POST['hourlyAverage'] : 0.0;

// Insert sensor data
$sql_sensor_data = "INSERT INTO sensor_data (lux1, lux2, lux3, lux4, timestamp) VALUES (?, ?, ?, ?, NOW())";
$stmt_sensor_data = $conn->prepare($sql_sensor_data);
$stmt_sensor_data->bind_param("dddd", $lux1, $lux2, $lux3, $lux4);

if ($stmt_sensor_data->execute()) {
    echo "Sensor data saved successfully.";
} else {
    echo "Error: " . $sql_sensor_data . "<br>" . $conn->error;
}

$stmt_sensor_data->close();

// Check if hourlyAverage is set and greater than 0
if ($hourlyAverage > 0) {
    // Retrieve the last insertion time from lis_avgperhr
    $sql_last_insert = "SELECT UNIX_TIMESTAMP(MAX(avg_hourtime)) AS last_insert_time FROM lis_avgperhr";
    $result_last_insert = $conn->query($sql_last_insert);

    $last_insert_time = 0; // Default to 0 if no record found
    if ($result_last_insert->num_rows > 0) {
        $row = $result_last_insert->fetch_assoc();
        if (!is_null($row['last_insert_time'])) {
            $last_insert_time = (int) $row['last_insert_time'];
        }
    }

    $current_time = time();
    if ($last_insert_time == 0 || ($current_time - $last_insert_time) >= 3600) {
        $sql_hourly_avg = "INSERT INTO lis_avgperhr (avg_lux, avg_hourtime) VALUES (?, NOW())";
        $stmt_hourly_avg = $conn->prepare($sql_hourly_avg);
        $stmt_hourly_avg->bind_param("d", $hourlyAverage);

        if ($stmt_hourly_avg->execute()) {
            echo "<br>Hour average saved successfully.";
        } else {
            echo "<br>Error saving hour average: " . $sql_hourly_avg . "<br>" . $conn->error;
        }

        $stmt_hourly_avg->close();
    } else {
        echo "<br>Hour average not saved. Less than 3600 seconds (1 hour) has passed since the last insertion.";
    }
}

$conn->close();
?>