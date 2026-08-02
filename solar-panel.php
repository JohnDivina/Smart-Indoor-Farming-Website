<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
if (!empty($_SESSION["is_guest"])) { header("Location: index.php?guest_restricted=1"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Solar Panel Monitor - Smart Farm</title>
    
    <style>
        .control-panel-wrapper {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .main-hero-icon {
            font-size: 100px;
            color: var(--text-secondary);
            margin: 32px 0;
            display: inline-block;
            transition: color var(--transition-normal), text-shadow var(--transition-normal);
        }
        
        .main-hero-icon.active {
            color: #f39c12;
            text-shadow: 0 0 40px rgba(243, 156, 18, 0.4);
        }

        .status-text {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .status-text span {
            color: var(--text-muted);
        }

        .status-text span.active {
            color: #f39c12;
        }

        .button-group-large {
            display: flex;
            gap: 16px;
            margin: 32px 0;
            justify-content: center;
        }

        .btn-large {
            padding: 16px 40px;
            font-size: 18px;
            border-radius: var(--border-radius-md);
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.03);
            color: var(--text-primary);
        }

        .btn-large:hover:not(:disabled) {
            background: rgba(255,255,255,0.08);
            border-color: var(--glass-border-hover);
            transform: translateY(-2px);
        }

        .btn-large.btn-on {
            border-color: rgba(243, 156, 18, 0.3);
            color: #f39c12;
        }

        .btn-large.btn-on:hover:not(:disabled) {
            background: rgba(243, 156, 18, 0.1);
            box-shadow: 0 0 20px rgba(243, 156, 18, 0.2);
        }

        .btn-large.btn-off {
            border-color: rgba(234, 76, 76, 0.3);
            color: var(--accent-danger);
        }

        .btn-large.btn-off:hover:not(:disabled) {
            background: rgba(234, 76, 76, 0.1);
            box-shadow: 0 0 20px rgba(234, 76, 76, 0.2);
        }

        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Power Stats Grid */
        .power-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .power-stat-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: var(--border-radius-md);
            text-align: center;
        }

        .power-stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 8px 0;
            font-variant-numeric: tabular-nums;
        }

        .power-stat-label {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
        }

        /* Toasts */
        .toast-container {
            position: fixed; top: 24px; right: 24px;
            display: flex; flex-direction: column; gap: 12px; z-index: 1000;
        }
        .toast {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            padding: 16px 24px;
            border-radius: var(--border-radius-sm);
            display: flex; align-items: center; gap: 12px;
            transform: translateX(120%);
            transition: transform var(--transition-normal);
            box-shadow: var(--glass-shadow);
        }
        .toast.show { transform: translateX(0); }
        .toast-success { border-left: 4px solid var(--accent-primary); }
        .toast-error { border-left: 4px solid var(--accent-danger); }
        .toast-info { border-left: 4px solid var(--accent-warning); }

        /* Animation for Motor Gear */
        @keyframes spinGear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .gear-icon {
            display: inline-block;
            transition: color 0.3s ease;
        }
        .gear-icon.spinning {
            animation: spinGear 2s linear infinite;
            color: var(--accent-primary);
        }

        /* Connection Status Dot */
        .conn-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--text-muted);
            margin-right: 6px;
            vertical-align: middle;
            transition: background 0.3s, box-shadow 0.3s;
        }
        .conn-dot.online  { background: #28a745; box-shadow: 0 0 8px rgba(40,167,69,0.6); }
        .conn-dot.offline { background: #dc3545; box-shadow: 0 0 8px rgba(220,53,69,0.4); }

    </style>
</head>
<body>

<div class="app-container">
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header">
            <div class="page-title">
                <p><a href="index.php" style="color:var(--accent-primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a></p>
                <h1 style="margin-top: 12px;">Solar Panel Control</h1>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-button">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </header>

        <div class="control-panel-wrapper">
            <!-- Main Control Card -->
            <div class="glass-panel" style="padding: 40px; text-align: center;">
                <h2 style="font-size: 20px; font-weight: 500; color: var(--text-secondary);">Solar Relay Status</h2>
                
                <i id="heroIcon" class="fa-solid fa-solar-panel main-hero-icon"></i>
                
                <div class="status-text">
                    Panel is <span id="statusDisplay">Unknown</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">
                    Motor: <i id="motorGearIcon" class="fa-solid fa-gear gear-icon" style="margin-right: 4px;"></i><span id="motorDisplay">Idle</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">
                    Controller: <span class="conn-dot" id="espConnDot"></span><span id="espStatusDisplay">Checking...</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px; display: none;" id="controllerMessageDiv">
                    Message: <span id="controllerMessageDisplay">-</span>
                </div>

                <div class="button-group-large">
                    <button id="btnOpen" class="btn btn-large btn-on">OPEN</button>
                    <button id="btnFold" class="btn btn-large btn-off">FOLD</button>
                </div>
                
                <div id="cooldownLabel" style="color: var(--accent-warning); font-size: 14px; min-height: 20px;"></div>
            </div>

            <!-- Schedule Mode Card -->
            <div class="glass-panel" style="padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--text-primary); margin: 0;">Schedule Mode</h3>
                        <p style="font-size: 14px; color: var(--text-muted); margin: 4px 0 0 0;">Automatically open and fold panels at set times.</p>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 28px;">
                        <input type="checkbox" id="scheduleToggle" style="opacity: 0; width: 0; height: 0;">
                        <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid var(--glass-border);"></span>
                        <style>
                            .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: var(--text-secondary); transition: .4s; border-radius: 50%; }
                            input:checked + .slider { background-color: rgba(243, 156, 18, 0.3); border-color: rgba(243, 156, 18, 0.5); }
                            input:checked + .slider:before { transform: translateX(22px); background-color: #f39c12; }
                            input:disabled + .slider { opacity: 0.5; cursor: not-allowed; }
                        </style>
                    </label>
                </div>

                <div id="scheduleSettings" style="display: none; transition: opacity 0.3s;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 8px;">Opening Time</label>
                            <input type="time" id="openTimeInput" style="width: 100%; padding: 12px; background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-primary); border-radius: var(--border-radius-sm); font-size: 16px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 8px;">Folding Time</label>
                            <input type="time" id="foldTimeInput" style="width: 100%; padding: 12px; background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-primary); border-radius: var(--border-radius-sm); font-size: 16px;">
                        </div>
                    </div>
                    
                    <button id="btnSaveSchedule" class="btn" style="width: 100%; padding: 14px; background: rgba(243, 156, 18, 0.1); border: 1px solid rgba(243, 156, 18, 0.3); color: #f39c12; font-size: 16px; font-weight: 500;">
                        <i class="fa-solid fa-save"></i> Save Schedule
                    </button>
                    <div id="scheduleStatusText" style="text-align: center; font-size: 13px; color: var(--accent-warning); margin-top: 12px; min-height: 18px;"></div>
                </div>
            </div>

        </div>
    </main>

</div>

<script>
// UI Elements
const heroIcon = document.getElementById('heroIcon');
const statusDisplay = document.getElementById('statusDisplay');
const motorDisplay = document.getElementById('motorDisplay');
const espStatusDisplay = document.getElementById('espStatusDisplay');
const controllerMessageDiv = document.getElementById('controllerMessageDiv');
const controllerMessageDisplay = document.getElementById('controllerMessageDisplay');

const btnOpen = document.getElementById('btnOpen');
const btnFold = document.getElementById('btnFold');
const cooldownLabel = document.getElementById('cooldownLabel');

const scheduleToggle = document.getElementById('scheduleToggle');
const scheduleSettings = document.getElementById('scheduleSettings');
const openTimeInput = document.getElementById('openTimeInput');
const foldTimeInput = document.getElementById('foldTimeInput');
const btnSaveSchedule = document.getElementById('btnSaveSchedule');
const scheduleStatusText = document.getElementById('scheduleStatusText');

let cooldownTimer = null;
let remainingCooldown = 0;
let currentMode = 'manual';
let waitingForConfigVersion = null;
let isScheduleEditing = false;

// Stop polling from overriding values when user is editing
openTimeInput.addEventListener('input', () => isScheduleEditing = true);
foldTimeInput.addEventListener('input', () => isScheduleEditing = true);
openTimeInput.addEventListener('focus', () => isScheduleEditing = true);
foldTimeInput.addEventListener('focus', () => isScheduleEditing = true);

// Toasts
function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? 'check' : type === 'error' ? 'xmark' : 'circle-info';
    toast.innerHTML = `<i class="fa-solid fa-${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Check device latency
function checkDeviceOffline(lastSeenAt) {
    if (!lastSeenAt) return true;
    const lastSeenDate = new Date(lastSeenAt);
    const now = new Date();
    // Consider offline if older than 15 seconds
    const diffMs = now - lastSeenDate;
    return diffMs > 15000;
}

// Server Polling
async function syncStatus() {
    try {
        const res = await fetch('api/solar/get_panel_ui_status.php');
        const data = await res.json();
        
        if (!data.success) return;
        
        // Mode logic
        currentMode = data.mode;
        
        // Handle Toggle Sync without interrupting user mid-interaction if possible
        if (currentMode === 'scheduled' && !scheduleToggle.checked) {
            scheduleToggle.checked = true;
            scheduleSettings.style.display = 'block';
        } else if (currentMode === 'manual' && scheduleToggle.checked) {
            scheduleToggle.checked = false;
            scheduleSettings.style.display = 'none';
        }
        
        // Only update inputs if the user is not actively editing them
        if (!isScheduleEditing) {
            openTimeInput.value = data.open_time;
            foldTimeInput.value = data.fold_time;
        }
        
        // Controller configuration sync logic
        if (waitingForConfigVersion !== null) {
            if (data.ack_config_version >= waitingForConfigVersion) {
                scheduleStatusText.textContent = "Schedule saved and confirmed by controller.";
                scheduleStatusText.style.color = "var(--accent-primary)";
                waitingForConfigVersion = null;
                setTimeout(() => { scheduleStatusText.textContent = ""; }, 5000);
            } else {
                scheduleStatusText.textContent = "Waiting for ESP32 confirmation...";
                scheduleStatusText.style.color = "var(--accent-warning)";
            }
        }

        const espConnDot = document.getElementById('espConnDot');
        // Offline logic
        const isOffline = checkDeviceOffline(data.last_seen_at) || data.wifi_status !== 'connected';
        const opacity = isOffline ? '0.5' : '1';
        heroIcon.style.opacity = opacity;
        statusDisplay.style.opacity = opacity;

        if (isOffline) {
            espConnDot.className = 'conn-dot offline';
            espStatusDisplay.textContent = 'Disconnected (Stale Data)';
            espStatusDisplay.style.color = '#dc3545';
            controllerMessageDiv.style.display = 'none';
        } else {
            espConnDot.className = 'conn-dot online';
            espStatusDisplay.textContent = 'Connected';
            espStatusDisplay.style.color = 'var(--accent-primary)';
            if (data.last_message) {
                controllerMessageDiv.style.display = 'block';
                controllerMessageDisplay.textContent = data.last_message;
            } else {
                controllerMessageDiv.style.display = 'none';
            }
        }

        // Panel state
        const state = data.actual_state;
        if (state === 1) {
            heroIcon.classList.add('active');
            statusDisplay.textContent = 'OPEN';
            statusDisplay.className = 'active';
            statusDisplay.style.color = '#f39c12';
        } else if (state === 0) {
            heroIcon.classList.remove('active');
            statusDisplay.textContent = 'FOLDED';
            statusDisplay.className = '';
            statusDisplay.style.color = '';
        } else {
            heroIcon.classList.remove('active');
            statusDisplay.textContent = 'Unknown';
            statusDisplay.className = '';
            statusDisplay.style.color = '';
        }

        // Motor State
        const isMotorRunning = data.motor_running === 1;
        motorDisplay.textContent = isMotorRunning ? 'Running (' + data.direction + ')' : 'Idle';
        motorDisplay.style.color = isMotorRunning ? 'var(--accent-primary)' : 'var(--text-muted)';
        
        const motorGear = document.getElementById('motorGearIcon');
        if (isMotorRunning) {
            motorGear.classList.add('spinning');
        } else {
            motorGear.classList.remove('spinning');
        }

        updateButtonStates(state, currentMode);
        
    } catch (error) {
        console.error('Sync failed', error);
    }
}

function updateButtonStates(actualState, mode) {
    const isCooldown = remainingCooldown > 0;
    
    if (mode === 'scheduled') {
        // Disable buttons visually in scheduled mode
        btnOpen.disabled = true;
        btnFold.disabled = true;
        btnOpen.style.opacity = '0.3';
        btnFold.style.opacity = '0.3';
        btnOpen.title = "Switch to Manual Mode to control";
        btnFold.title = "Switch to Manual Mode to control";
    } else {
        btnOpen.style.opacity = '1';
        btnFold.style.opacity = '1';
        btnOpen.title = "";
        btnFold.title = "";
        // In manual mode, only disable the button that corresponds to the CURRENT actual state or cooldown
        btnOpen.disabled = isCooldown || actualState === 1;
        btnFold.disabled = isCooldown || actualState === 0;
    }
}

// Cooldown
function startCooldown(seconds) {
    remainingCooldown = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);
    btnOpen.disabled = true;
    btnFold.disabled = true;

    cooldownTimer = setInterval(() => {
        if (remainingCooldown <= 0) {
            clearInterval(cooldownTimer);
            cooldownLabel.textContent = '';
            syncStatus();
            return;
        }
        cooldownLabel.textContent = `Please wait ${remainingCooldown}s...`;
        remainingCooldown--;
    }, 1000);
}

// Controls
btnOpen.addEventListener('click', async () => {
    try {
        const res = await fetch('api/solar/set_panel_command.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ desired_state: 1 })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Panel opening...', 'success');
            startCooldown(5); // Prevent spamming
        } else {
            showToast(data.message, 'error');
        }
    } catch { showToast('Network error', 'error'); }
});

btnFold.addEventListener('click', async () => {
    try {
        const res = await fetch('api/solar/set_panel_command.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ desired_state: 0 })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Panel folding...', 'success');
            startCooldown(5);
        } else {
            showToast(data.message, 'error');
        }
    } catch { showToast('Network error', 'error'); }
});

// Scheduling
scheduleToggle.addEventListener('change', async (e) => {
    const isChecked = e.target.checked;
    scheduleSettings.style.display = isChecked ? 'block' : 'none';
    
    // Switch Mode
    try {
        const mode = isChecked ? 'scheduled' : 'manual';
        const res = await fetch('api/solar/set_panel_mode.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ mode: mode })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Switched to ${mode} mode`, 'success');
            currentMode = mode;
            // Force quick sync to update button visual states
            syncStatus();
        } else {
            showToast(data.message, 'error');
            e.target.checked = !isChecked; // Revert switch if failed
            scheduleSettings.style.display = !isChecked ? 'block' : 'none';
        }
    } catch { 
        showToast('Network error', 'error'); 
        e.target.checked = !isChecked; 
        scheduleSettings.style.display = !isChecked ? 'block' : 'none';
    }
});

btnSaveSchedule.addEventListener('click', async () => {
    const openTime = openTimeInput.value;
    const foldTime = foldTimeInput.value;
    
    if (!openTime || !foldTime) {
        showToast('Please set both opening and folding times', 'error');
        return;
    }
    
    try {
        btnSaveSchedule.disabled = true;
        const res = await fetch('api/solar/save_panel_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ 
                open_time: openTime,
                fold_time: foldTime
            })
        });
        const data = await res.json();
        
        if (data.success) {
            isScheduleEditing = false; // Successfully saved, resume polling overrides
            showToast('Schedule applied. Waiting for controller...', 'success');
            waitingForConfigVersion = data.config_version;
            scheduleStatusText.textContent = "Waiting for ESP32 confirmation...";
            scheduleStatusText.style.color = "var(--accent-warning)";
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) { 
        showToast('Network error', 'error'); 
    } finally {
        btnSaveSchedule.disabled = false;
    }
});




// Init
syncStatus();
// Poll every 2 seconds
setInterval(syncStatus, 2000);
</script>

</body>
</html>
