<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
if (!empty($_SESSION["is_guest"])) { header("Location: index.php?guest_restricted=1"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Auxiliary Fan Control - Smart Farm</title>
    
    <style>
        .control-panel-wrapper {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .main-fan-icon {
            font-size: 100px;
            color: var(--text-secondary);
            margin: 32px 0;
            display: inline-block;
            transition: color var(--transition-normal), text-shadow var(--transition-normal);
        }
        
        .main-fan-icon.active {
            color: var(--accent-primary);
            text-shadow: 0 0 40px var(--accent-primary-glow);
            animation: spinFast 1s linear infinite;
        }

        @keyframes spinFast { 100% { transform: rotate(360deg); } }

        .status-text {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .status-text span { color: var(--text-muted); }
        .status-text span.active { color: var(--accent-primary); }

        .button-group-large {
            display: flex;
            gap: 16px;
            margin: 32px 0;
            justify-content: center;
            flex-wrap: wrap;
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
        .btn-large.btn-on { border-color: rgba(25,201,104,0.3); color: var(--accent-primary); }
        .btn-large.btn-on:hover:not(:disabled) { background: rgba(25,201,104,0.1); box-shadow: 0 0 20px var(--accent-primary-glow); }
        .btn-large.btn-off { border-color: rgba(234,76,76,0.3); color: var(--accent-danger); }
        .btn-large.btn-off:hover:not(:disabled) { background: rgba(234,76,76,0.1); box-shadow: 0 0 20px rgba(234,76,76,0.2); }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

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
        .mode-tab.active-auto      { border-color: #9b59b6; color: #9b59b6; background: rgba(155,89,182,0.08); }

        /* Schedule input grid */
        .time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .time-label {
            display: block;
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .time-input {
            width: 100%;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 12px 16px;
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 15px;
        }

        /* Auto mode info box */
        .auto-info {
            background: rgba(155,89,182,0.08);
            border: 1px solid rgba(155,89,182,0.3);
            border-radius: var(--border-radius-md);
            padding: 20px 24px;
        }
        .auto-info h3 { color: #9b59b6; margin-bottom: 8px; }
        .auto-info p  { color: var(--text-secondary); font-size: 14px; line-height: 1.6; }

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
        .badge-auto      { background: rgba(155,89,182,0.15); color: #9b59b6; }

        /* Toasts */
        .toast-container { position: fixed; top: 24px; right: 24px; display: flex; flex-direction: column; gap: 12px; z-index: 1000; }
        .toast { background: var(--glass-bg); backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border); padding: 16px 24px; border-radius: var(--border-radius-sm); display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: transform var(--transition-normal); box-shadow: var(--glass-shadow); }
        .toast.show { transform: translateX(0); }
        .toast-success { border-left: 4px solid var(--accent-primary); }
        .toast-error   { border-left: 4px solid var(--accent-danger); }
        .toast-info    { border-left: 4px solid var(--accent-secondary); }
        
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
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div class="page-title">
                <p><a href="index.php" style="color:var(--accent-primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a></p>
                <h1 style="margin-top: 12px;">Auxiliary Fan</h1>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-button"><i class="fa-solid fa-moon"></i></button>
            </div>
        </header>

        <div class="control-panel-wrapper">
            <!-- Main Control Card -->
            <div class="glass-panel" style="padding: 40px; text-align: center;">
                <h2 style="font-size: 20px; font-weight: 500; color: var(--text-secondary);">System Status</h2>
                
                <i id="fanIcon" class="fa-solid fa-fan main-fan-icon"></i>
                
                <div class="status-text">
                    Fan is <span id="fanStatusDisplay">OFF</span>
                </div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">
                    Controller: <span class="conn-dot" id="espConnDot"></span><span id="espStatusDisplay">Checking...</span>
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    Mode: <span id="modeBadge" class="mode-badge badge-manual">Manual</span>
                </div>

                <div class="button-group-large">
                    <button id="btnOn"  class="btn btn-large btn-on" >START</button>
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
                    <button class="mode-tab" id="tabAuto"      onclick="switchMode('auto')">
                        <i class="fa-solid fa-microchip" style="display:block; font-size:20px; margin-bottom:6px;"></i>
                        Sensor Auto
                    </button>
                </div>
            </div>

            <!-- Schedule Settings (only shown in scheduled mode) -->
            <div id="scheduleCard" class="glass-panel" style="padding: 24px; display:none;">
                <h3 style="font-size: 16px; margin-bottom: 16px; color: #f39c12;">
                    <i class="fa-solid fa-clock"></i> Schedule Configuration
                </h3>
                <div class="time-grid">
                    <div>
                        <span class="time-label">Start Time</span>
                        <input type="time" id="scheduleStartTime" class="time-input">
                    </div>
                    <div>
                        <span class="time-label">Stop Time</span>
                        <input type="time" id="scheduleStopTime" class="time-input">
                    </div>
                </div>
                <button id="saveScheduleBtn" class="btn btn-primary" style="width:100%; padding:14px; font-size:15px; margin-bottom: 12px;">
                    <i class="fa-solid fa-save"></i> Save Schedule
                </button>
                <button id="resetScheduleBtn" class="btn btn-secondary" style="width:100%; padding:12px; font-size:14px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-secondary);">
                    <i class="fa-solid fa-undo"></i> Reset Schedule
                </button>
                <div id="scheduleStatusText" style="text-align:center; font-size:13px; color:var(--accent-warning); margin-top:10px; min-height:18px;"></div>
                <div id="configSyncStatus" style="text-align:center; font-size:12px; margin-top:8px; display:none;"></div>
            </div>

            <!-- Auto Mode Info Card (only shown in auto mode) -->
            <div id="autoCard" style="display:none;" class="auto-info">
                <h3><i class="fa-solid fa-microchip"></i> Sensor-Based Auto Mode Active</h3>
                <p>
                    The ESP32 is now in <strong>Sensor Auto mode</strong>. The fan is controlled automatically 
                    by the MC1 microcontroller based on sensor readings (temperature, humidity, CO₂, etc.).<br><br>
                    The dashboard will still display the fan's live status reported by the ESP32.
                    Switch back to <strong>Manual</strong> or <strong>Scheduled</strong> mode to take control from the server.
                </p>
            </div>

            <!-- Last Operation -->
            <div style="display: grid; gap: 16px;">
                <div class="glass-panel" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="color: var(--text-secondary);">Last Fan Operation</div>
                    <div id="lastRunDisplay" style="font-weight: 600; font-size: 18px;">—</div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// UI refs
const fanIcon           = document.getElementById('fanIcon');
const fanStatusDisplay  = document.getElementById('fanStatusDisplay');
const espStatusDisplay  = document.getElementById('espStatusDisplay');
const modeBadge         = document.getElementById('modeBadge');
const btnOn             = document.getElementById('btnOn');
const btnOff            = document.getElementById('btnOff');
const cooldownLabel     = document.getElementById('cooldownLabel');
const lastRunDisplay    = document.getElementById('lastRunDisplay');
const scheduleCard      = document.getElementById('scheduleCard');
const autoCard          = document.getElementById('autoCard');
const scheduleStartTime = document.getElementById('scheduleStartTime');
const scheduleStopTime  = document.getElementById('scheduleStopTime');
const saveScheduleBtn   = document.getElementById('saveScheduleBtn');
const resetScheduleBtn  = document.getElementById('resetScheduleBtn');
const scheduleStatusText = document.getElementById('scheduleStatusText');

let cooldownTimer    = null;
let remainingCooldown = 0;
let currentMode      = 'manual';

// ─── Toasts ────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    let c = document.querySelector('.toast-container');
    if (!c) { c = document.createElement('div'); c.className = 'toast-container'; document.body.appendChild(c); }
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icon = type === 'success' ? 'check' : type === 'error' ? 'xmark' : 'circle-info';
    t.innerHTML = `<i class="fa-solid fa-${icon}"></i> <span>${message}</span>`;
    c.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}

// ─── Mode Tab UI ────────────────────────────────────────────────────────────
function updateModeUI(mode) {
    currentMode = mode;

    // Tab highlights
    document.getElementById('tabManual').className    = 'mode-tab' + (mode === 'manual'    ? ' active-manual'    : '');
    document.getElementById('tabScheduled').className = 'mode-tab' + (mode === 'scheduled' ? ' active-scheduled' : '');
    document.getElementById('tabAuto').className      = 'mode-tab' + (mode === 'auto'      ? ' active-auto'      : '');

    // Cards
    scheduleCard.style.display = mode === 'scheduled' ? 'block' : 'none';
    autoCard.style.display     = mode === 'auto'      ? 'block' : 'none';

    // Badge
    const labels = { manual: 'Manual', scheduled: 'Scheduled', auto: 'Sensor Auto' };
    const klasses = { manual: 'badge-manual', scheduled: 'badge-scheduled', auto: 'badge-auto' };
    modeBadge.textContent = labels[mode] || mode;
    modeBadge.className   = 'mode-badge ' + (klasses[mode] || 'badge-manual');

    updateButtonStates(fanStatusDisplay.textContent === 'ON', mode);
}

// ─── Mode Switch ─────────────────────────────────────────────────────────────
async function switchMode(mode) {
    if (mode === currentMode) return;
    try {
        const res  = await fetch('AUXILIARYBLOWER/api/manual_control.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ action: 'set_mode', mode })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Switched to ${mode} mode`, 'success');
            updateModeUI(mode);
        } else {
            showToast(data.message || 'Failed to switch mode', 'error');
        }
    } catch { showToast('Network error', 'error'); }
}

// ─── Button States ──────────────────────────────────────────────────────────
function updateButtonStates(isFanOn, mode) {
    const isCooldown   = remainingCooldown > 0;
    const isScheduled  = mode === 'scheduled';
    const isAuto       = mode === 'auto';
    btnOn.disabled  = isScheduled || isAuto || isCooldown || isFanOn;
    btnOff.disabled = isScheduled || isAuto || isCooldown || !isFanOn;
}

// ─── Status Polling ─────────────────────────────────────────────────────────
async function syncStatus() {
    try {
        const res  = await fetch('AUXILIARYBLOWER/status.php');
        const data = await res.json();
        if (!data.success) return;

        const isFanOn = data.esp_fan_state === 'on';

        // Fan icon & text
        fanIcon.classList.toggle('active', isFanOn);
        fanStatusDisplay.textContent = isFanOn ? 'ON' : 'OFF';
        fanStatusDisplay.className   = isFanOn ? 'active' : '';

        // Connection
        const espConnDot = document.getElementById('espConnDot');
        const opacity = data.esp_online ? '1' : '0.5';
        fanIcon.style.opacity = opacity;
        fanStatusDisplay.style.opacity = opacity;

        if (data.esp_online) {
            espConnDot.className = 'conn-dot online';
            espStatusDisplay.textContent = 'Connected';
            espStatusDisplay.style.color = 'var(--accent-primary)';
        } else {
            espConnDot.className = 'conn-dot offline';
            espStatusDisplay.textContent = 'Disconnected (Stale Data)';
            espStatusDisplay.style.color = '#dc3545';
        }

        // Mode (only update if user is not interacting with a mode tab or schedule inputs)
        const schedCardActive = scheduleCard.contains(document.activeElement);
        const newMode = data.mode || 'manual';
        if (!schedCardActive && document.activeElement.tagName !== 'BUTTON' && newMode !== currentMode) {
            updateModeUI(newMode);
        }

        // Schedule time inputs — only update specific inputs not currently focused
        if (data.mode === 'scheduled') {
            if (data.schedule_time && document.activeElement !== scheduleStartTime) {
                scheduleStartTime.value = data.schedule_time.substring(0, 5);
            }
            if (data.schedule_stop_time && document.activeElement !== scheduleStopTime) {
                scheduleStopTime.value = data.schedule_stop_time.substring(0, 5);
            }
        }

        // Config Acknowledgment Sync
        const syncDiv = document.getElementById('configSyncStatus');
        if (data.config_version !== undefined && data.ack_config_version !== undefined) {
            syncDiv.style.display = 'block';
            if (data.ack_config_version >= data.config_version) {
                syncDiv.innerHTML = `<span style="color:var(--accent-primary)"><i class="fa-solid fa-circle-check"></i> ESP32 Sync: Confirmed</span>`;
            } else {
                syncDiv.innerHTML = `<span style="color:var(--accent-warning)"><i class="fa-solid fa-spinner fa-spin"></i> ESP32 Sync: Waiting for controller...</span>`;
            }
        }

        updateButtonStates(isFanOn, data.mode || 'manual');
    } catch (e) { console.error('Sync failed', e); }
}

// ─── Last Run ────────────────────────────────────────────────────────────────
async function updateLastRun() {
    try {
        const res  = await fetch('api/fan/get_last_fan_run.php');
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
        el.textContent = 'Never operated';
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

// ─── Cooldown ────────────────────────────────────────────────────────────────
function startCooldown(seconds) {
    remainingCooldown = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);
    btnOn.disabled = btnOff.disabled = true;
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

async function logFan(action) {
    await fetch('api/fan/log_fan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({ action })
    });
    updateLastRun();
}

// ─── Manual Controls ─────────────────────────────────────────────────────────
btnOn.addEventListener('click', async () => {
    try {
        const res  = await fetch('AUXILIARYBLOWER/api/manual_control.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ action: 'start' })
        });
        const data = await res.json();
        if (data.success) { showToast('Fan started', 'success'); logFan('START'); startCooldown(3); }
        else { showToast(data.message, 'error'); }
    } catch { showToast('Network error', 'error'); }
});

btnOff.addEventListener('click', async () => {
    try {
        const res  = await fetch('AUXILIARYBLOWER/api/manual_control.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ action: 'stop' })
        });
        const data = await res.json();
        if (data.success) { showToast('Fan stopped', 'success'); logFan('STOP'); startCooldown(3); }
        else { showToast(data.message, 'error'); }
    } catch { showToast('Network error', 'error'); }
});

// ─── Save Schedule ───────────────────────────────────────────────────────────
saveScheduleBtn.addEventListener('click', async () => {
    const startTime = scheduleStartTime.value;
    const stopTime  = scheduleStopTime.value;
    if (!startTime || !stopTime) return showToast('Set both Start and Stop times', 'error');
    if (startTime === stopTime)  return showToast('Start and Stop times cannot be the same', 'error');

    try {
        saveScheduleBtn.disabled = true;
        const res  = await fetch('AUXILIARYBLOWER/api/save_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ enabled: true, time: startTime, stop_time: stopTime })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Schedule saved successfully', 'success');
            scheduleStatusText.textContent = `Fan will run ${startTime} → ${stopTime} daily`;
            scheduleStatusText.style.color = 'var(--accent-primary)';
            syncStatus();
        } else {
            showToast(data.message, 'error');
        }
    } catch { showToast('Network error', 'error'); }
    finally   { saveScheduleBtn.disabled = false; }
});

// ─── Reset Schedule ──────────────────────────────────────────────────────────
resetScheduleBtn.addEventListener('click', async () => {
    const confirmed = await window.showConfirm({
        title: 'Reset Fan Schedule',
        message: 'Are you sure you want to clear the schedule and reset the system to manual mode?',
        confirmText: 'Reset Now',
        type: 'warning'
    });
    if (!confirmed) return;

    try {
        resetScheduleBtn.disabled = true;
        const res = await fetch('AUXILIARYBLOWER/api/save_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ reset: true })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Schedule reset successfully', 'success');
            scheduleStartTime.value = '';
            scheduleStopTime.value = '';
            scheduleStatusText.textContent = 'Schedule cleared';
            syncStatus();
        } else {
            showToast(data.message, 'error');
        }
    } catch { showToast('Network error', 'error'); }
    finally { resetScheduleBtn.disabled = false; }
});

// ─── Init ────────────────────────────────────────────────────────────────────
syncStatus();
updateLastRun();
setInterval(syncStatus, 2000);
</script>

</body>
</html>
