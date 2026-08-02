<?php
$port = "COM12"; // Change to the correct COM port

// Get number and message from Python
$number = $_POST['number'] ?? "";
$message = $_POST['message'] ?? "";

if (empty($number) || empty($message)) {
    echo "Error: Missing parameters.";
    exit;
}

// Open the serial port
exec("stty -F $port 115200 raw -echo"); // Set baud rate and raw mode

// Send AT command
$command = "AT+SMSEND=\"$number\",3,\"$message\"\r";
file_put_contents($port, $command);

echo "SMS Sent to $number with message: $message";
?>
