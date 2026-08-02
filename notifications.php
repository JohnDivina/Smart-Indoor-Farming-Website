<?php
session_start();

include 'database.php';

// Fetch rows where hourlyAverage is not zero
$sql = "SELECT timestamp, hourlyAverage FROM lightintensitysensor WHERE hourlyAverage != 0";
$result = $conn->query($sql);

$lightConditions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timestamp = $row['timestamp'];
        $average = (float) $row['hourlyAverage']; // Explicit cast to float for decimals

        if ($average >= 5000 && $average < 10000) {
            $condition = "Low Light";
        } elseif ($average >= 10000 && $average < 30000) {
            $condition = "Medium Light";
        } elseif ($average >= 30000) {
            $condition = "High Light";
        } else {
            continue; // Ignore values below 5000
        }

        $lightConditions[] = "At $timestamp - Light Level: $average Lux ($condition)";
    }
}

// Store messages in session for index.php to show
$_SESSION['light_messages'] = $lightConditions;

// Redirect back to index.php to show notifications
header("Location: index.php");
exit;

?>

<script>
// Auto-logout after 5 minutes (300,000 ms) of inactivity
(function() {
  let logoutTimer;
  const logoutAfter = 5 * 60 * 1000; // 5 minutes in milliseconds

  function resetLogoutTimer() {
    clearTimeout(logoutTimer);
    logoutTimer = setTimeout(() => {
      window.location.href = 'user_logout.php?inactive=1';
    }, logoutAfter);
  }

  // Reset timer on user activity
  ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, resetLogoutTimer, true);
  });

  resetLogoutTimer(); // Start timer on page load
})();
</script>
