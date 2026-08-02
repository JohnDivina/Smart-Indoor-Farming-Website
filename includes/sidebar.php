<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_guest = !empty($_SESSION['is_guest']);
$display_name = $is_guest ? 'Guest User' : (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'System Admin');
$status_label = $is_guest ? 'Guest Mode' : 'Online';
$profile_click = $is_guest ? "window.location.href='index.php?guest_restricted=1'" : "window.location.href='settings.php'";
?>
<aside class="sidebar">
    <div class="brand">
        <img src="assets/clsu-official-logo.png" alt="CLSU Logo" style="width: 46px; height: 46px; object-fit: contain;">
        <div class="brand-title" style="font-size: 16px; margin-left: 12px; white-space: nowrap; line-height: 1.2;">
            <span style="font-weight: 600; color: var(--text-primary);">CLSU Smart</span>
            <span style="font-weight: 800; color: var(--accent-primary);"> Farm</span>
        </div>
    </div>
    
    <ul class="nav-links" style="list-style:none; padding:0; margin:0;">
        <a href="index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-shapes"></i> Dashboard
        </a>

        <li class="nav-section-label">Sensors</li>
        <a href="temp-humidity.php" class="nav-item <?php echo $current_page == 'temp-humidity.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-temperature-half"></i> Temp &amp; Humidity
        </a>
        <a href="light-intensity.php" class="nav-item <?php echo $current_page == 'light-intensity.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-sun"></i> Light Intensity
        </a>
        <a href="npk.php" class="nav-item <?php echo $current_page == 'npk.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-flask"></i> Soil NPK
        </a>
        
        <li class="nav-section-label">Controls</li>
        <?php if ($is_guest): ?>
        <a href="index.php?guest_restricted=1" class="nav-item" style="opacity:0.75;" title="Locked in Guest Mode">
            <i class="fa-solid fa-fan"></i> Auxiliary Fan <i class="fa-solid fa-lock" style="margin-left:auto; font-size:11px; opacity:0.6;"></i>
        </a>
        <a href="index.php?guest_restricted=1" class="nav-item" style="opacity:0.75;" title="Locked in Guest Mode">
            <i class="fa-solid fa-droplet"></i> Fertigation <i class="fa-solid fa-lock" style="margin-left:auto; font-size:11px; opacity:0.6;"></i>
        </a>
        <a href="index.php?guest_restricted=1" class="nav-item" style="opacity:0.75;" title="Locked in Guest Mode">
            <i class="fa-solid fa-solar-panel"></i> Solar Panels <i class="fa-solid fa-lock" style="margin-left:auto; font-size:11px; opacity:0.6;"></i>
        </a>
        <?php else: ?>
        <a href="auxiliary-fan.php" class="nav-item <?php echo $current_page == 'auxiliary-fan.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-fan"></i> Auxiliary Fan
        </a>
        <a href="fertigation.php" class="nav-item <?php echo $current_page == 'fertigation.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-droplet"></i> Fertigation
        </a>
        <a href="solar-panel.php" class="nav-item <?php echo $current_page == 'solar-panel.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-solar-panel"></i> Solar Panels
        </a>
        <?php endif; ?>
        
        <li class="nav-section-label">System</li>
        <a href="about-us.php" class="nav-item <?php echo $current_page == 'about-us.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-info"></i> About Us
        </a>
        <?php if ($is_guest): ?>
        <a href="index.php?guest_restricted=1" class="nav-item" style="opacity:0.75;" title="Locked in Guest Mode">
            <i class="fa-solid fa-gear"></i> Settings <i class="fa-solid fa-lock" style="margin-left:auto; font-size:11px; opacity:0.6;"></i>
        </a>
        <?php else: ?>
        <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gear"></i> Settings
        </a>
        <?php endif; ?>
    </ul>
    
    <div class="user-profile" style="position: relative;">
        <div class="avatar" onclick="<?php echo $profile_click; ?>" style="cursor:pointer; <?php echo $is_guest ? 'background:rgba(234,179,8,0.2); color:#eab308;' : ''; ?>">
            <i class="fa-solid <?php echo $is_guest ? 'fa-user-clock' : 'fa-user'; ?>"></i>
        </div>
        <div class="user-info" onclick="<?php echo $profile_click; ?>" style="cursor:pointer;">
            <h4><?php echo $display_name; ?></h4>
            <span style="<?php echo $is_guest ? 'color:#eab308; font-weight:600;' : ''; ?>"><?php echo $status_label; ?></span>
        </div>
        <!-- Logout triggers confirm modal, not direct redirect -->
        <button class="logout-btn" id="sidebarLogoutBtn" title="<?php echo $is_guest ? 'Exit Guest Mode' : 'Logout'; ?>"
                style="margin-left:auto; background:none; border:none; cursor:pointer; color:var(--accent-danger); font-size:18px; transition:transform 0.2s; padding:0;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </button>
    </div>
</aside>

<!-- Logout Confirmation Modal (appended to body via JS to avoid stacking context issues) -->
<script>
(function () {
    // Build the modal once after DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        // Inject modal HTML at the end of <body>
        const modal = document.createElement('div');
        modal.id = 'globalLogoutModal';
        modal.style.cssText = [
            'display:none',
            'position:fixed',
            'inset:0',
            'z-index:9999',
            'align-items:center',
            'justify-content:center',
        ].join(';');
        modal.innerHTML = `
          <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);" id="logoutBackdrop"></div>
          <div style="position:relative;background:var(--glass-bg,#fff);border:1px solid var(--glass-border,#e2e8f0);
                      border-radius:16px;padding:36px 32px;max-width:380px;width:90%;text-align:center;
                      box-shadow:0 24px 64px rgba(0,0,0,0.18);">
            <div style="font-size:48px;color:var(--accent-danger,#e53e3e);margin-bottom:12px;">
              <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3 style="margin:0 0 8px;font-size:20px;font-weight:700;color:var(--text-primary,#1a202c);">Confirm Logout</h3>
            <p style="margin:0 0 24px;color:var(--text-secondary,#64748b);font-size:14px;">Are you sure you want to log out of your account?</p>
            <div style="display:flex;gap:12px;justify-content:center;">
              <button id="logoutCancelBtn" style="padding:10px 26px;border-radius:8px;border:1px solid var(--glass-border,#e2e8f0);
                      background:transparent;color:var(--text-secondary,#64748b);font-size:14px;
                      font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
              <a href="user_logout.php" style="padding:10px 26px;border-radius:8px;border:none;
                      background:var(--accent-danger,#e53e3e);color:#fff;font-size:14px;
                      font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;
                      align-items:center;gap:6px;font-family:inherit;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
              </a>
            </div>
          </div>`;
        document.body.appendChild(modal);

        // Open
        const logoutBtn = document.getElementById('sidebarLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function () {
                modal.style.display = 'flex';
            });
        }

        // Close on Cancel or backdrop click
        document.getElementById('logoutCancelBtn').addEventListener('click', function () {
            modal.style.display = 'none';
        });
        document.getElementById('logoutBackdrop').addEventListener('click', function () {
            modal.style.display = 'none';
        });
    });
})();
</script>
