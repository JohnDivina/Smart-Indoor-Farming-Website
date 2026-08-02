<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phonenumber = trim($_POST["phonenumber"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Basic validation
    if (empty($username) || empty($email) || empty($phonenumber) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!preg_match('/^\d+$/', $phonenumber)) {
        $error_message = "Please enter a valid phone number (digits only).";
    } elseif (!preg_match('/^(?=.*[0-9!@#$%^&*(),.?":{}|<>]).{6,}$/', $password)) {
        $error_message = "Password must be at least 6 characters and contain at least one number or special symbol.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if username, email, or phone number already exists
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? OR phonenumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $phonenumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username, email, or phone number already exists.";
        } else {
            // Insert new user with email_verified = 0
            $hashed_password = md5($password);  // consider using password_hash() for better security!
            $insert_sql = "INSERT INTO users (username, email, phonenumber, password, email_verified) VALUES (?, ?, ?, ?, 0)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $username, $email, $phonenumber, $hashed_password);
            
            if ($insert_stmt->execute()) {
                // Get the new user ID
                $newUserId = $insert_stmt->insert_id;
                
                // Send verification email
                require_once 'send_otp_email.php';
                $otpResult = sendOTPEmail($newUserId, $email, $username, $conn);
                
                if ($otpResult['success']) {
                    // Store user info in session for verification
                    $_SESSION['otp_user_id'] = $newUserId;
                    $_SESSION['otp_username'] = $username;
                    $_SESSION['otp_reason'] = 'account_creation';
                    
                    // Redirect to email verification page
                    header("Location: verify_email.php");
                    exit();
                } else {
                    $error_message = "Account created but failed to send verification email. " . $otpResult['message'];
                }
            } else {
                $error_message = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CLSU CRRDC | SMARTFARM CREATE ACCOUNT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet" />

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

    .left-panel h1 {
        color: #fff;
        font-weight: 700;
        font-size: 3rem;
        margin-top: 1rem;
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

      <!-- Left Logo Panel (only on md+ screens) -->
      <div class="col-md-6 left-panel d-none d-md-flex">
        <div class="text-center">
            <img src="assets/clsu-official-logo.png" alt="CLSU Logo" />
            <h1>SMARTFARM</h1>
        </div>
      </div>

      <!-- Registration Form Panel -->
      <div class="col-md-6 right-panel">
        <div class="login-box">
          <!-- Show logo on small screens only -->
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;" />

          <div class="login-title">CREATE ACCOUNT</div>

          <!-- Display error message -->
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php endif; ?>

          <!-- Display success message -->
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="" id="createaccountForm" novalidate>
            <div class="mb-3 text-start">
              <label for="username" class="form-label">Username</label>
              <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                placeholder="Enter username"
                required
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
              />
            </div>

            <div class="mb-3 text-start">
              <label for="email" class="form-label">Email Address</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
              />
            </div>

            <div class="mb-3 text-start">
              <label for="phonenumber" class="form-label">Phone Number</label>
              <input
                type="text"
                class="form-control"
                id="phonenumber"
                name="phonenumber"
                placeholder="Enter your phone number"
                required
                value="<?php echo isset($_POST['phonenumber']) ? htmlspecialchars($_POST['phonenumber']) : ''; ?>"
              />
            </div>

            <div class="mb-3 text-start">
              <label for="password" class="form-label">Password</label>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Enter password"
                required
                minlength="6"
                autocomplete="new-password"
              />
              <div id="passwordHelp" class="form-text text-danger"></div>
            </div>

            <div class="mb-3 text-start">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm password"
                required
                minlength="6"
                autocomplete="new-password"
              />
              <div id="confirmHelp" class="form-text text-danger"></div>
            </div>

            <button type="submit" class="btn btn-login" id="submitBtn">Create Account</button>
          </form>

          <div class="have-account mt-4 d-flex justify-content-center">
            <span>Already have an account? <a href="login.php">Log In</a></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password validation
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const passwordHelp = document.getElementById('passwordHelp');
    const confirmHelp = document.getElementById('confirmHelp');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('createaccountForm');

    function validatePassword() {
      const password = passwordInput.value;
      let valid = true;
      let message = "";

      // Minimum 6 characters, at least one number or special character
      if (password.length < 6) {
        message = "Password must be at least 6 characters.";
        valid = false;
      } else if (!(/[0-9!@#$%^&*(),.?":{}|<>]/.test(password))) {
        message = "Password must contain at least one number or special symbol.";
        valid = false;
      }

      passwordHelp.textContent = message;
      return valid;
    }

    function validateConfirm() {
      const password = passwordInput.value;
      const confirm = confirmInput.value;
      let valid = true;
      let message = "";

      if (confirm && password !== confirm) {
        message = "Passwords do not match.";
        valid = false;
      }

      confirmHelp.textContent = message;
      return valid;
    }

    passwordInput.addEventListener('input', function() {
      validatePassword();
      validateConfirm();
    });

    confirmInput.addEventListener('input', validateConfirm);

    form.addEventListener('submit', function(e) {
      let validPassword = validatePassword();
      let validConfirm = validateConfirm();
      if (!validPassword || !validConfirm) {
        e.preventDefault();
      }
    });

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
</body>
</html>