<?php
session_start();

// Set timezone
date_default_timezone_set('Asia/Manila');

include 'database.php';

$error_message = "";
$show_modal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Validate email format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists - use existing OTP system
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            $userName = $user['username'];
            
            $_SESSION["reset_email"] = $email;
            $_SESSION["reset_user_id"] = $userId;

            // Use the existing OTP email function
            require_once 'send_otp_email.php';
            $otpResult = sendOTPEmail($userId, $email, $userName, $conn);
            
            if ($otpResult['success']) {
                // Store session data for password reset flow
                $_SESSION['otp_user_id'] = $userId;
                $_SESSION['otp_username'] = $userName;
                $_SESSION['otp_reason'] = 'password_reset';
                
                // Redirect to password reset OTP verification page
                header("Location: verify_reset_otp.php");
                exit();
            } else {
                $error_message = "Failed to send verification code. Please try again.";
            }
        } else {
            // Email not found - show modal
            $show_modal = true;
        }
        $stmt->close();
    } else {
        $error_message = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CLSU | Faculty Portal Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root {
      --green-grad: linear-gradient(120deg, #009639, #87b237);
    }
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Libre Franklin', sans-serif;
      background-color: #f0f2f5;
    }
    .main-container {
      height: 100vh;
    }
    .left-panel {
      background: var(--green-grad);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .left-panel img {
      width: 80%;
      max-width: 380px;
      height: auto;
    }
    .right-panel {
      background-color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .login-box {
      width: 100%;
      max-width: 700px;
      padding: 2rem;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .login-box img {
      max-width: 160px;
      margin-bottom: 1rem;
    }
    .login-title {
      font-size: 28px;
      font-weight: 600;
      color: #333;
      margin-bottom: 2rem;
    }
    .form-label {
      font-weight: 500;
      text-align: left;
      display: block;
    }
    .form-control {
      border-radius: 8px;
      padding: 0.75rem;
    }
    .btn-login {
      background-color:#009639;
      border: none;
      font-weight: 600;
      color: #ffffff;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 1rem;
    }
    .btn-login:hover {
      background-color: #009639;
      color: #ffffff;
    }
    .forgot, .have-account {
      text-align: right;
      font-size: 0.875rem;
      margin-top: 0.5rem;
      color: #6c757d;
    }
    .have-account a {
      text-decoration: none;
      font-weight: 600;
      color: #009639;
    }
    .have-account a:hover {
      text-decoration: underline;
      color: #006622;
    }
    @media (max-width: 768px) {
      .left-panel {
        display: none !important;
      }
      .login-box {
        box-shadow: 0 12px 25px rgba(33, 106, 30, 0.577);
        padding: 1.5rem;
        height: auto;
      }
      .login-title {
        margin-top: -.5rem;
      }
      .right-panel {
        padding: 2rem 1rem;
      }
    }
  </style>
</head>
<body>

  <div class="container-fluid main-container">
    <div class="row h-100">
      <div class="col-md-6 left-panel d-none d-md-flex">
        <img src="assets/clsu-official-logo.png" alt="CLSU Logo" />
      </div>
      <div class="col-md-6 right-panel">
        <div class="login-box">
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;" />
          <div class="login-title">SMART FARM PASSWORD RESET</div>

          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $error_message; ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="mb-3 text-start">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter your registered email" required />
            </div>
            <button type="submit" class="btn btn-login">Send OTP</button>
          </form>

          <div class="have-account mt-4 d-flex justify-content-center">
            <span>Don't have an account? <a href="createaccount.php">Sign Up</a></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for email not registered -->
  <div class="modal fade" id="emailNotRegisteredModal" tabindex="-1" aria-labelledby="emailNotRegisteredLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="emailNotRegisteredLabel">Email Not Registered</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          The email address you entered is not registered. Please check and try again.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    <?php if ($show_modal): ?>
      var myModal = new bootstrap.Modal(document.getElementById('emailNotRegisteredModal'));
      window.onload = function() { myModal.show(); };
    <?php endif; ?>
  </script>

</body>
</html>
