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
<title>Terms of Service - Smart Indoor Farming Dashboard</title>
<link rel="stylesheet" href="css/drts-layout.css">
  
  <style>
  .terms-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
  }
  
  .terms-header {
    background: linear-gradient(90deg, #009639 60%, #87b237 100%);
    border-radius: 18px;
    padding: 24px 32px;
    margin-bottom: 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .terms-icon {
    font-size: 2.5rem;
    color: #fff;
  }
  
  .terms-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
  }
  
  .terms-subtitle {
    font-size: 1rem;
    color: #e0ffe0;
    margin: 0;
  }
  
  .terms-card {
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
  
  .terms-content {
    padding: 0;
  }
  
  .terms-section {
    margin-bottom: 40px;
  }
  
  .terms-section:last-child {
    margin-bottom: 0;
  }
  
  .terms-section h3 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #009639;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .terms-section h3 i {
    font-size: 1.3rem;
  }
  
  .terms-section h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #333);
    margin-top: 24px;
    margin-bottom: 12px;
  }
  
  .terms-section p {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-secondary, #555);
    margin-bottom: 16px;
  }
  
  .terms-section ul {
    margin-left: 24px;
    margin-bottom: 16px;
  }
  
  .terms-section ul li {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-secondary, #555);
    margin-bottom: 8px;
  }
  
  .terms-section strong {
    color: var(--text-primary, #333);
    font-weight: 600;
  }
  
  .terms-section a {
    color: #009639;
    text-decoration: none;
    font-weight: 600;
  }
  
  .terms-section a:hover {
    text-decoration: underline;
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
    .terms-container {
      margin: 1rem auto;
      padding: 0;
    }
    
    .terms-header {
      padding: 20px;
    }
    
    .terms-title {
      font-size: 1.3rem;
    }
    
    .terms-card {
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
        <h1 class="header-title">TERMS OF SERVICE</h1>
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
        
        <div class="terms-container">
          <!-- Terms Header -->
          <div class="terms-header">
            <i class="bi bi-file-text-fill terms-icon"></i>
            <div>
              <h2 class="terms-title">Terms of Service</h2>
              <p class="terms-subtitle">Rules and guidelines for using our platform</p>
            </div>
          </div>
          
          <!-- Terms Content Card -->
          <div class="terms-card" style="text-align: left; display: block;">
            <div class="terms-content">
              
              <section class="terms-section">
                <h3><i class="bi bi-1-circle-fill"></i> Acceptance of Terms</h3>
                <p>By accessing or using the CLSU Smart Farm system ("the Platform"), you agree to be bound by these Terms of Service. If you do not agree, you must not use the Platform.</p>
                <p>These Terms apply to all users, including administrators, staff, researchers, and authorized users.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-2-circle-fill"></i> Description of the Service</h3>
                <p>The Platform provides:</p>
                <ul>
                  <li>Real-time farm monitoring (temperature, soil moisture, NPK, light, fertigation)</li>
                  <li>External weather reference data</li>
                  <li>User account authentication</li>
                  <li>Activity visibility within the system</li>
                  <li>Data visualization and monitoring dashboards</li>
                </ul>
                <p>The system is intended for agricultural monitoring, academic, research, or institutional use.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-3-circle-fill"></i> User Accounts</h3>
                <p>To access certain features, users must:</p>
                <ul>
                  <li>Provide accurate registration information</li>
                  <li>Maintain the confidentiality of their password</li>
                  <li>Notify administrators of unauthorized access</li>
                </ul>
                <p><strong>You are responsible for all activity under your account.</strong></p>
                <p>We reserve the right to suspend or terminate accounts that violate these Terms.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-4-circle-fill"></i> Acceptable Use</h3>
                <p>You agree NOT to:</p>
                <ul>
                  <li>Attempt unauthorized access to other accounts</li>
                  <li>Interfere with system performance</li>
                  <li>Upload malicious code or scripts</li>
                  <li>Manipulate sensor data without authorization</li>
                  <li>Use the system for unlawful purposes</li>
                </ul>
                <p><strong>Violation may result in account suspension or permanent termination.</strong></p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-5-circle-fill"></i> Active User Visibility</h3>
                <p>The Platform includes a real-time active user feature that may display:</p>
                <ul>
                  <li>User initials</li>
                  <li>User name</li>
                  <li>Current page being viewed</li>
                </ul>
                <p>This visibility is limited to authenticated users within the Platform and is intended to enhance collaboration and transparency.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-6-circle-fill"></i> Data Accuracy and System Availability</h3>
                <p>While we strive to maintain accurate sensor readings and system uptime:</p>
                <ul>
                  <li>We do not guarantee uninterrupted access.</li>
                  <li>Sensor readings may be subject to hardware or connectivity limitations.</li>
                  <li>External weather data is provided by third-party services and may not always be accurate.</li>
                </ul>
                <p>Users should not rely solely on the Platform for critical agricultural decisions without verification.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-7-circle-fill"></i> Intellectual Property</h3>
                <p>All content, design, software, and system architecture belong to CLSU Smart Farm unless otherwise stated.</p>
                <p>Users may not:</p>
                <ul>
                  <li>Copy</li>
                  <li>Modify</li>
                  <li>Distribute</li>
                  <li>Reverse engineer</li>
                </ul>
                <p>Any part of the Platform without written permission.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-8-circle-fill"></i> Third-Party Services</h3>
                <p>The Platform may integrate external services such as:</p>
                <ul>
                  <li>OpenWeatherMap (weather reference)</li>
                  <li>Hosting or infrastructure providers</li>
                </ul>
                <p>We are not responsible for third-party service outages or inaccuracies.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-9-circle-fill"></i> Limitation of Liability</h3>
                <p>To the fullest extent permitted by law:</p>
                <p>CLSU Smart Farm and its administrators shall not be liable for:</p>
                <ul>
                  <li>Data loss</li>
                  <li>Crop damage</li>
                  <li>System downtime</li>
                  <li>Indirect or consequential damages</li>
                  <li>Inaccurate sensor readings</li>
                </ul>
                <p><strong>Use of the Platform is at your own risk.</strong></p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-clipboard-check-fill"></i> Account Termination</h3>
                <p>We may suspend or terminate accounts if:</p>
                <ul>
                  <li>Terms are violated</li>
                  <li>Suspicious activity is detected</li>
                  <li>Misuse of system resources occurs</li>
                </ul>
                <p>Users may request account deletion by contacting the administrator.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-shield-lock-fill"></i> Privacy</h3>
                <p>Your use of the Platform is also governed by our <a href="privacy-policy.php" style="color: #009639; text-decoration: none; font-weight: 600;">Privacy Policy</a>.</p>
                <p>Please review it to understand how we collect and use information.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-gear-fill"></i> Modifications to the Service</h3>
                <p>We reserve the right to:</p>
                <ul>
                  <li>Update features</li>
                  <li>Modify system functionality</li>
                  <li>Improve UI/UX</li>
                  <li>Change technical infrastructure</li>
                </ul>
                <p>Without prior notice.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-pencil-square"></i> Changes to Terms</h3>
                <p>We may update these Terms of Service at any time. Changes become effective upon posting.</p>
                <p>Continued use of the Platform constitutes acceptance of updated terms.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-bank"></i> Governing Law</h3>
                <p>These Terms shall be governed by the laws applicable within the jurisdiction of the Philippines.</p>
              </section>

              <section class="terms-section">
                <h3><i class="bi bi-envelope-fill"></i> Contact Information</h3>
                <p>For questions regarding these Terms:</p>
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
