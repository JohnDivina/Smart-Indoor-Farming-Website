<?php
session_start();

session_unset();
session_destroy();

// Check if logout is due to inactivity
$inactive = isset($_GET['inactive']) && $_GET['inactive'] == '1';

if (!$inactive) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logged Out</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 400px;">
      <div class="card-body text-center">
        <h3 class="card-title mb-3">You have been logged out</h3>
        <?php if ($inactive): ?>
          <div class="alert alert-warning" role="alert">
            You have been logged out due to inactivity.<br>
            Please log in again to continue.
          </div>
        <?php else: ?>
          <div class="alert alert-info" role="alert">
            You have successfully logged out.<br>
            Please log in again to continue.
          </div>
        <?php endif; ?>
        <a href="login.php" class="btn btn-success mt-3">Go to Login</a>
      </div>
    </div>
  </div>
</body>
</html>
