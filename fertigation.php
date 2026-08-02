<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
if (!empty($_SESSION["is_guest"])) { header("Location: index.php?guest_restricted=1"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Fertigation Control - Smart Farm</title>
    
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
            color: #0088cc;
            text-shadow: 0 0 40px rgba(0, 136, 204, 0.4);
            animation: dripFast 1s ease-in-out infinite;
        }

        @keyframes dripFast {
            0% { transform: translateY(0); opacity: 1; }
            50% { transform: translateY(10px); opacity: 0.8; }
            100% { transform: translateY(0); opacity: 1; }
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
            color: #0088cc;
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
            border-color: rgba(0, 136, 204, 0.3);
            color: #0088cc;
        }

        .btn-large.btn-on:hover:not(:disabled) {
            background: rgba(0, 136, 204, 0.1);
            box-shadow: 0 0 20px rgba(0, 136, 204, 0.2);
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

        /* Mode Card Tabs */
        .mode-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }
        .mode-tab {
            padding: 14px 10px;
            border-radius: var(--border-radius-sm);
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.02);
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .mode-tab:hover { background: rgba(255,255,255,0.06); }
        .mode-tab.active-manual    { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(25,201,104,0.08); }
        .mode-tab.active-scheduled { border-color: #f39c12; color: #f39c12; background: rgba(243,156,18,0.08); }
        .mode-tab.active-sensor_auto { border-color: #9b59b6; color: #9b59b6; background: rgba(155,89,182,0.08); }

        /* Mode badge */
        .mode-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-manual    { background: rgba(25,201,104,0.15); color: var(--accent-primary); }
        .badge-scheduled { background: rgba(243,156,18,0.15); color: #f39c12; }
        .badge-sensor_auto { background: rgba(155,89,182,0.15); color: #9b59b6; }

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
        .toast-info { border-left: 4px solid var(--accent-secondary); }
        
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
                <h1 style="margin-top: 12px;">Fertigation Control</h1>
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
                <h2 style="font-size: 20px; font-weight: 500; color: var(--text-secondary);">Pump Status</h2>
                
                <i id="heroIcon" class="fa-solid fa-faucet-drip main-hero-icon"></i>
                
                <div class="status-text">
                    Pump is <span id="statusDisplay">OFF</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">
                    Controller: <span class="conn-dot" id="espConnDot"></span><span id="espStatusDisplay">Checking...</span>
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    Mode: <span id="modeBadge" class="mode-badge badge-manual">Manual</span>
                </div>

                <div class="button-group-large">
                    <button id="btnOn" class="btn btn-large btn-on">START</button>
                    <button id="btnOff" class="btn btn-large btn-off">STOP</button>
                </div>
                
                <div id="cooldownLabel" style="color: var(--accent-warning); font-size: 14px; min-height: 20px;"></div>
            </div>

            <!-- Mode Selection Card -->
            <div class="glass-panel" style="padding: 24px;">
                <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary);">Operating Mode</h3>
                <div class="mode-tabs">
                    <button class="mode-tab" id="tabManual"    onclick="switchMode('manual')">
                        <i class="fa-solid fa-hand-pointer" style="display:block; font-size:20px; margin-bottom:6px;"></i>
                        Manual
                    </button>
                    <button class="mode-tab" id="tabScheduled" onclick="switchMode('scheduled')">
                        <i class="fa-solid fa-clock" style="display:block; font-size:20px; margin-bottom:6px;"></i>
                        Scheduled
                    </button>
                    <button class="mode-tab" id="tabSensorAuto" onclick="switchMode('sensor_auto')">
                        <i class="fa-solid fa-microchip" style="display:block; font-size:20px; margin-bottom:6px;"></i>
                        Sensor Auto
                    </button>
                </div>
            </div>

            <!-- Schedule Time Settings -->
            <div id="scheduleTimeContainer" class="glass-panel" style="padding: 24px; display: none;">
                <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--accent-primary);">
                    <i class="fa-solid fa-calendar-check"></i> Set Schedule
                </h3>
                <p id="scheduleHelperText" style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px; margin-top: -8px;">
                    Set and save Start and Stop time to activate scheduled operation.
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display:block; font-size:13px; color:var(--text-secondary); margin-bottom:6px;">Start Time</label>
        <input type="time" id="scheduleTime" style="width:100%; background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-primary); padding: 12px 16px; border-radius: var(--border-radius-sm); font-family: inherit; font-size: 15px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; color:var(--text-secondary); margin-bottom:6px;">Stop Time</label>
        <input type="time" id="scheduleStopTime" style="width:100%; background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-primary); padding: 12px 16px; border-radius: var(--border-radius-sm); font-family: inherit; font-size: 15px;">
                    </div>
                </div>
                <button id="saveScheduleBtn" class="btn btn-primary" style="width:100%; padding: 14px; border-radius: var(--border-radius-sm); border:none; background:var(--accent-primary); color:#fff; font-weight:600; cursor:pointer; font-size:15px; margin-bottom: 12px;">
                    <i class="fa-solid fa-save"></i> Save Schedule
                </button>
                <button id="resetScheduleBtn" class="btn btn-secondary" style="width:100%; padding: 12px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor:pointer; font-size: 14px;">
                    <i class="fa-solid fa-undo"></i> Reset Schedule
                </button>
                <div id="schedStatusText" style="text-align:center; font-size:13px; color:var(--accent-primary); margin-top:10px; min-height:16px;"></div>
                <div id="configSyncStatus" style="text-align:center; font-size:12px; margin-top:8px; display:none;"></div>
            </div>

            <!-- Stats -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                <div class="glass-panel" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="color: var(--text-secondary);">Last Irrigation Operation</div>
                    <div id="lastRunDisplay" style="font-weight: 600; font-size: 18px; color: var(--text-primary);">—</div>
                </div>
            </div>

        </div>
    </main>

</div>

<script>
// UI Elements
const heroIcon            = document.getElementById('heroIcon');
const statusDisplay       = document.getElementById('statusDisplay');
const espStatusDisplay    = document.getElementById('espStatusDisplay');
const btnOn               = document.getElementById('btnOn');
const btnOff              = document.getElementById('btnOff');
const modeBadge           = document.getElementById('modeBadge');
const scheduleTimeContainer = document.getElementById('scheduleTimeContainer');
const scheduleTime        = document.getElementById('scheduleTime');
const scheduleStopTime    = document.getElementById('scheduleStopTime');
const saveScheduleBtn     = document.getElementById('saveScheduleBtn');
const resetScheduleBtn    = document.getElementById('resetScheduleBtn');
const schedStatusText     = document.getElementById('schedStatusText');
const cooldownLabel       = document.getElementById('cooldownLabel');
const lastRunDisplay      = document.getElementById('lastRunDisplay');

let cooldownTimer     = null;
let remainingCooldown = 0;
let waitingForCfgVer  = null;
let currentMode       = 'manual';

// ─── Mode Tab UI ────────────────────────────────────────────────────────────
function updateModeUI(mode) {
    currentMode = mode;
    document.getElementById('tabManual').className = 'mode-tab' + (mode === 'manual' ? ' active-manual' : '');
    document.getElementById('tabScheduled').className = 'mode-tab' + (mode === 'scheduled' ? ' active-scheduled' : '');
    document.getElementById('tabSensorAuto').className = 'mode-tab' + (mode === 'sensor_auto' ? ' active-sensor_auto' : '');

    scheduleTimeContainer.style.display = (mode === 'scheduled') ? 'block' : 'none';

    const labels = { manual: 'Manual', scheduled: 'Scheduled', sensor_auto: 'Sensor Auto' };
    const klasses = { manual: 'badge-manual', scheduled: 'badge-scheduled', sensor_auto: 'badge-sensor_auto' };
    modeBadge.textContent = labels[mode] || mode;
    modeBadge.className = 'mode-badge ' + (klasses[mode] || 'badge-manual');

    updateButtonStates(heroIcon.classList.contains('active'), mode);
}

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

// Stats Polling
async function updateLastRun() {
    try {
        const res = await fetch('api/fertigation/get_last_irrigation.php');
        const data = await res.json();
        if (data.success && data.minutes_ago !== null) {
            updateLastRunText(data.minutes_ago);
        } else {
            updateLastRunText(null);
        }
    } catch {}
}

function updateLastRunText(minutesAgo) {
    const el = document.getElementById('lastRunDisplay');
    if (minutesAgo === null) {
        el.textContent = 'Never irrigated';
        return;
    }

    const mins = parseInt(minutesAgo);
    if (mins < 60) {
        el.textContent = `${mins} ${mins === 1 ? 'min' : 'mins'} ago`;
    } else if (mins < 1440) {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        let text = `${h} ${h === 1 ? 'hour' : 'hours'}`;
        if (m > 0) text += ` ${m} ${m === 1 ? 'min' : 'mins'}`;
        el.textContent = `${text} ago`;
    } else {
        const d = Math.floor(mins / 1440);
        const h = Math.floor((mins % 1440) / 60);
        let text = `${d} ${d === 1 ? 'day' : 'days'}`;
        if (h > 0) text += ` ${h} ${h === 1 ? 'hour' : 'hours'}`;
        el.textContent = `${text} ago`;
    }
}

let syncInProgress = false;

// Server Polling — DB-driven, no curl to ESP32
async function syncStatus() {
    if (syncInProgress) return;
    syncInProgress = true;
    try {
        const res  = await fetch('api/fertigation/get_status.php');
        const data = await res.json();
        if (!data.success) { syncInProgress = false; return; }

        const espConnDot = document.getElementById('espConnDot');
        const mode       = data.mode; // 'manual' | 'scheduled' | 'sensor_auto'

        // Connection from heartbeat staleness
        if (data.esp_online) {
            espConnDot.className         = 'conn-dot online';
            espStatusDisplay.textContent = 'Connected';
            espStatusDisplay.style.color = 'var(--accent-primary)';
            heroIcon.style.opacity       = '1';
            statusDisplay.style.opacity  = '1';
        } else {
            espConnDot.className         = 'conn-dot offline';
            espStatusDisplay.textContent = 'Disconnected (Stale Data)';
            espStatusDisplay.style.color = '#dc3545';
            heroIcon.style.opacity       = '0.5';
            statusDisplay.style.opacity  = '0.5';
        }

        // Pump state from actual_pump_state (ESP32-reported)
        const isOn = (data.actual_pump_state || data.esp_pump_state) === 'on';
        heroIcon.classList.toggle('active', isOn);
        statusDisplay.textContent = isOn ? 'ON' : 'OFF';
        statusDisplay.className   = isOn ? 'active' : '';

        updateButtonStates(isOn, mode);

        // Mode switch sync
        const schedActive = scheduleTimeContainer.contains(document.activeElement);
        if (!schedActive && document.activeElement.tagName !== 'BUTTON' && mode !== currentMode) {
            updateModeUI(mode);
        }

        // Time inputs — only update if schedule card is not focused
        if (!schedActive) {
            if (data.schedule_time      && document.activeElement !== scheduleTime)     scheduleTime.value     = data.schedule_time.substring(0, 5);
            if (data.schedule_stop_time && document.activeElement !== scheduleStopTime) scheduleStopTime.value = data.schedule_stop_time.substring(0, 5);
        }

        if (data.schedule_time && data.schedule_stop_time) {
            schedStatusText.textContent = `Schedule: ${data.schedule_time.substring(0,5)} → ${data.schedule_stop_time.substring(0,5)} daily`;
        } else {
            schedStatusText.textContent = 'No schedule saved yet.';
        }

        // Config ACK indicator
        const syncDiv = document.getElementById('configSyncStatus');
        if (syncDiv && waitingForCfgVer !== null) {
            if (data.ack_config_version >= waitingForCfgVer) {
                syncDiv.style.display = 'block';
                syncDiv.innerHTML = `<span style="color:var(--accent-primary)"><i class="fa-solid fa-circle-check"></i> Controller confirmed — config applied.</span>`;
                waitingForCfgVer = null;
                setTimeout(() => { syncDiv.style.display = 'none'; syncDiv.innerHTML = ''; }, 5000);
            } else {
                syncDiv.style.display = 'block';
                syncDiv.innerHTML = `<span style="color:var(--accent-warning)"><i class="fa-solid fa-spinner fa-spin"></i> Waiting for controller to apply config...</span>`;
            }
        }
    } catch (err) {
        console.error('Sync error:', err);
    } finally {
        syncInProgress = false;
    }
}

function updateButtonStates(isOn, mode) {
    const isCooldown = remainingCooldown > 0;
    if (mode === 'scheduled' || mode === 'sensor_auto') {
        btnOn.disabled  = true;
        btnOff.disabled = true;
    } else {
        btnOn.disabled  = isCooldown || isOn;
        btnOff.disabled = isCooldown || !isOn;
    }
}

// Cooldown
function startCooldown(seconds) {
    remainingCooldown = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);
    btnOn.disabled = true;
    btnOff.disabled = true;

    cooldownTimer = setInterval(() => {
        if (remainingCooldown <= 0) {
            clearInterval(cooldownTimer);
            cooldownLabel.textContent = '';
            syncStatus();
            return;
        }
        cooldownLabel.textContent = `Command sent — updating in ${remainingCooldown}s...`;
        remainingCooldown--;
    }, 1000);
}

async function logOp(action) {
    await fetch('api/fertigation/log_irrigation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({ action })
    });
    updateLastRun();
}

// Controls
btnOn.addEventListener('click', async () => {
    try {
        const res  = await fetch('api/fertigation/manual_control.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body:    JSON.stringify({ action: 'start' })
        });
        const data = await res.json();
        if (data.success) {
            showToast('START queued — controller will activate pump shortly.', 'success');
            logOp('START');
            updateModeUI('manual');
            startCooldown(5);
        } else { showToast(data.message, 'error'); }
    } catch { showToast('Network error', 'error'); }
});

btnOff.addEventListener('click', async () => {
    try {
        const res  = await fetch('api/fertigation/manual_control.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body:    JSON.stringify({ action: 'stop' })
        });
        const data = await res.json();
        if (data.success) {
            showToast('STOP queued — controller will deactivate pump shortly.', 'success');
            logOp('STOP');
            updateModeUI('manual');
            startCooldown(5);
        } else { showToast(data.message, 'error'); }
    } catch { showToast('Network error', 'error'); }
});

// ─── Mode Switch ─────────────────────────────────────────────────────────────
async function switchMode(mode) {
    if (mode === currentMode) return;
    try {
        const res  = await fetch('api/fertigation/set_mode.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body:    JSON.stringify({ mode })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || `Switched to ${mode} mode`, 'success');
            updateModeUI(mode);
            await syncStatus();
        } else {
            showToast(data.message || 'Mode switch failed', 'error');
        }
    } catch { showToast('Network error', 'error'); }
}
saveScheduleBtn.addEventListener('click', async () => {
    const time = scheduleTime.value, stopTime = scheduleStopTime.value;
    if (!time || !stopTime) return showToast('Set both Start and Stop times', 'error');
    if (time === stopTime)   return showToast('Start and Stop times cannot be the same', 'error');
    try {
        saveScheduleBtn.disabled = true;
        const res  = await fetch('api/fertigation/save_schedule.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body:    JSON.stringify({ time, stop_time: stopTime })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Schedule saved. Enable Scheduled Mode to activate.', 'success');
            if (schedStatusText) schedStatusText.textContent = `Schedule: ${time} → ${stopTime} daily`;
            if (data.config_version) {
                waitingForCfgVer = data.config_version;
                const syncDiv = document.getElementById('configSyncStatus');
                if (syncDiv) { syncDiv.style.display = 'block'; syncDiv.innerHTML = `<span style="color:var(--accent-warning)"><i class="fa-solid fa-spinner fa-spin"></i> Waiting for controller to apply config...</span>`; }
            }
            syncStatus();
        } else { showToast(data.message || 'Failed to save schedule', 'error'); }
    } catch { showToast('Network error', 'error'); }
    finally   { saveScheduleBtn.disabled = false; }
});

// Reset Schedule
resetScheduleBtn.addEventListener('click', async () => {
    const confirmed = await window.showConfirm({
        title: 'Reset Fertigation',
        message: 'Are you sure you want to clear the fertigation schedule and return to manual control?',
        confirmText: 'Reset Now',
        type: 'warning'
    });
    if (!confirmed) return;

    try {
        resetScheduleBtn.disabled = true;
        const res = await fetch('api/fertigation/save_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ reset: true })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Schedule reset. Returned to Manual Mode.', 'success');
            scheduleTime.value       = '';
            scheduleStopTime.value   = '';
            updateModeUI('manual');
            if (schedStatusText) schedStatusText.textContent = 'Schedule cleared.';
            syncStatus();
        } else { showToast(data.message || 'Failed to reset', 'error'); }
    } catch { showToast('Network error', 'error'); }
    finally { resetScheduleBtn.disabled = false; }
});

// ─── Init ──────────────────────────────────────────────────────────────────────
syncStatus();
updateLastRun();
setInterval(syncStatus,    2000);
setInterval(updateLastRun, 60000);
</script>

</body>
</html>
