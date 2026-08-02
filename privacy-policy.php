<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/header.php'; ?>
<title>Privacy Policy - Smart Indoor Farming Dashboard</title>
<link rel="stylesheet" href="css/drts-layout.css">
  
  <style>
  .policy-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
  }
  
  .policy-header {
    background: linear-gradient(90deg, #009639 60%, #87b237 100%);
    border-radius: 18px;
    padding: 24px 32px;
    margin-bottom: 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .policy-icon {
    font-size: 2.5rem;
    color: #fff;
  }
  
  .policy-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
  }
  
  .policy-subtitle {
    font-size: 1rem;
    color: #e0ffe0;
    margin: 0;
  }
  
  .policy-card {
    background: var(--card-bg, #fff);
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid var(--border-color, #e0e0e0);
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
  }
  
  .policy-content {
    padding: 0;
  }
  
  .policy-section {
    margin-bottom: 40px;
  }
  
  .policy-section:last-child {
    margin-bottom: 0;
  }
  
  .policy-section h3 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #009639;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .policy-section h3 i {
    font-size: 1.3rem;
  }
  
  .policy-section h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #333);
    margin-top: 24px;
    margin-bottom: 12px;
  }
  
  .policy-section p {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-secondary, #555);
    margin-bottom: 16px;
  }
  
  .policy-section ul {
    margin-left: 24px;
    margin-bottom: 16px;
  }
  
  .policy-section ul li {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-secondary, #555);
    margin-bottom: 8px;
  }
  
  .policy-section strong {
    color: var(--text-primary, #333);
    font-weight: 600;
  }
  
  .contact-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #009639;
    margin-top: 16px;
  }
  
  .contact-info p {
    margin: 0;
    line-height: 1.8;
  }
  
  @media (max-width: 768px) {
    .policy-container {
      margin: 1rem auto;
      padding: 0;
    }
    
    .policy-header {
      padding: 20px;
    }
    
    .policy-title {
      font-size: 1.3rem;
    }
    
    .policy-card {
      padding: 24px;
    }
    
    .placeholder-icon {
      font-size: 3rem;
    }
    
    .placeholder-title {
      font-size: 1.2rem;
    }
  }
  </style>
</head>
<body>
  <!-- DRTS-Style Layout -->
  <div class="dashboard-layout">
    
    <!-- Left Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Backdrop Overlay for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <!-- Top Header -->
    <header class="top-header">
      <div class="header-left">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="header-title">PRIVACY POLICY</h1>
      </div>
      
      <div class="header-right">
        <button class="btn btn-link p-0" 
                id="themeToggle" 
                title="Toggle Dark Mode" 
                aria-label="Toggle between light and dark mode"
                onclick="toggleTheme()">
          <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        
        <button class="btn btn-link p-0" 
                id="logoutBtn" 
                title="Logout"
                aria-label="Logout from dashboard">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </header>
    
    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
      <main class="main-content">
        
        <div class="policy-container">
          <!-- Policy Header -->
          <div class="policy-header">
            <i class="bi bi-shield-lock-fill policy-icon"></i>
            <div>
              <h2 class="policy-title">Privacy Policy</h2>
              <p class="policy-subtitle">How we collect, use, and protect your information</p>
            </div>
          </div>
          
          <!-- Policy Content Card -->
          <div class="policy-card" style="text-align: left; display: block;">
            <div class="policy-content">
              
              <section class="policy-section">
                <h3><i class="bi bi-1-circle-fill"></i> Introduction</h3>
                <p>CLSU Smart Farm ("we", "our", or "the System") respects your privacy and is committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your information when you use our website and monitoring platform.</p>
                <p><strong>By accessing or using the system, you agree to the terms of this Privacy Policy.</strong></p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-2-circle-fill"></i> Information We Collect</h3>
                <p>We may collect the following types of information:</p>
                
                <h4>A. Account Information</h4>
                <p>When you register or log in:</p>
                <ul>
                  <li>Full name</li>
                  <li>Username</li>
                  <li>Email address</li>
                  <li>Encrypted password</li>
                </ul>

                <h4>B. System Usage Information</h4>
                <p>When you use the platform:</p>
                <ul>
                  <li>Pages visited (e.g., Dashboard, Fertigation, NPK, Light, Temperature)</li>
                  <li>Login timestamps</li>
                  <li>Activity status (for active user display)</li>
                  <li>IP address (if logged by server)</li>
                </ul>

                <h4>C. Sensor & Farm Data</h4>
                <p>The system may collect:</p>
                <ul>
                  <li>Temperature readings</li>
                  <li>Soil moisture levels</li>
                  <li>NPK values</li>
                  <li>Light intensity</li>
                  <li>Fertigation data</li>
                </ul>
                <p>This data is used strictly for monitoring and farm management purposes.</p>

                <h4>D. External API Data</h4>
                <p>We use third-party services such as OpenWeatherMap to display external weather information. No personal user data is sent to these services.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-3-circle-fill"></i> How We Use Your Information</h3>
                <p>We use collected information to:</p>
                <ul>
                  <li>Authenticate users securely</li>
                  <li>Display active user presence within the system</li>
                  <li>Monitor farm environmental conditions</li>
                  <li>Improve system functionality</li>
                  <li>Ensure system security</li>
                  <li>Diagnose technical issues</li>
                </ul>
                <p><strong>We do NOT sell, rent, or trade your personal information.</strong></p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-4-circle-fill"></i> Active User Visibility</h3>
                <p>The system includes a real-time active user feature that may display:</p>
                <ul>
                  <li>User initials</li>
                  <li>Full name (on hover)</li>
                  <li>Current page being viewed</li>
                </ul>
                <p>This feature is visible only to authenticated users within the platform.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-5-circle-fill"></i> Data Security</h3>
                <p>We implement reasonable security measures including:</p>
                <ul>
                  <li>Password hashing and encryption</li>
                  <li>Server-side authentication</li>
                  <li>Secure API key storage</li>
                  <li>Controlled database access</li>
                  <li>Session management controls</li>
                </ul>
                <p>However, no internet-based system can be 100% secure. Users are encouraged to protect their login credentials.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-6-circle-fill"></i> Data Retention</h3>
                <p>We retain:</p>
                <ul>
                  <li>Account information for as long as the account remains active</li>
                  <li>System logs as required for maintenance and security</li>
                  <li>Sensor data for monitoring and historical analysis</li>
                </ul>
                <p>Inactive or deleted accounts may have their data removed according to administrative policy.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-7-circle-fill"></i> Third-Party Services</h3>
                <p>We may use third-party services including:</p>
                <ul>
                  <li>OpenWeatherMap (for external weather reference)</li>
                  <li>Hosting provider services</li>
                </ul>
                <p>These providers have their own privacy policies governing their data handling practices.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-8-circle-fill"></i> Cookies and Sessions</h3>
                <p>The system uses session cookies to:</p>
                <ul>
                  <li>Maintain login state</li>
                  <li>Secure authenticated access</li>
                  <li>Improve user experience</li>
                </ul>
                <p>These cookies do not track users outside the platform.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-9-circle-fill"></i> User Rights</h3>
                <p>Users may request to:</p>
                <ul>
                  <li>Update account information</li>
                  <li>Change passwords</li>
                  <li>Request account deletion (subject to administrative approval)</li>
                </ul>
                <p>Requests may be submitted to: <strong>[Insert contact email]</strong></p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-clipboard-check-fill"></i> Changes to This Policy</h3>
                <p>We may update this Privacy Policy periodically. Updates will be posted on this page with a revised effective date.</p>
                <p>Continued use of the system constitutes acceptance of the updated policy.</p>
              </section>

              <section class="policy-section">
                <h3><i class="bi bi-envelope-fill"></i> Contact Information</h3>
                <p>If you have questions regarding this Privacy Policy, please contact:</p>
                <div class="contact-info">
                  <p><strong>Administrator:</strong> Ivan Christian L. Salinas<br>
                  <strong>Email:</strong> banbansalinas@clsu.edu.ph<br>
                  <strong>Institution:</strong> Central Luzon State University</p>
                </div>
              </section>

              <div style="margin-top: 32px; padding-top: 24px; border-top: 2px solid var(--border-color, #e0e0e0);">
                <p style="text-align: center;">
                  <a href="settings.php" style="color: #009639; text-decoration: none; font-weight: 600;">
                    <i class="bi bi-arrow-left"></i> Back to Settings
                  </a>
                </p>
              </div>
            </div>
          </div>
          
        </div>
        
      </main>
    </div>
    
    <!-- Footer -->
    <footer class="dashboard-footer">
      <p>&copy; Crops and Resources Research and Development Center · Central Luzon State University</p>
    </footer>
  </div>

  <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to logout?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="user_logout.php" class="btn btn-danger">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/sidebar.js"></script>
  <script>
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', () => {
      const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
      modal.show();
    });
  </script>
</body>
</html>
