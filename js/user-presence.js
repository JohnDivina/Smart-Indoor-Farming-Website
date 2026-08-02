/**
 * User Presence Tracking System
 * 
 * Features:
 * - Heartbeat every 15 seconds
 * - Idle detection (60 seconds)
 * - Auto-stop heartbeat when idle
 * - Auto-resume when activity detected
 */

(function () {
    'use strict';

    // Configuration
    const HEARTBEAT_INTERVAL = 15000; // 15 seconds
    const IDLE_TIMEOUT = 60000; // 60 seconds

    let heartbeatTimer = null;
    let idleTimer = null;
    let isIdle = false;

    // Get current page name from document title
    function getCurrentPage() {
        // Extract page name from title (e.g., "Dashboard | Smart Farm" -> "Dashboard")
        const title = document.title;
        const pageName = title.split('|')[0].trim() || title.trim();
        return pageName || 'Unknown';
    }

    // Send heartbeat to server
    function sendHeartbeat() {
        if (isIdle) return; // Don't send if idle

        const currentPage = getCurrentPage();

        fetch('/smartfarm2/api/heartbeat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ page: currentPage })
        })
            .catch(error => {
                console.error('Heartbeat error:', error);
            });
    }

    // Start heartbeat
    function startHeartbeat() {
        if (heartbeatTimer) return; // Already running

        // Send immediately
        sendHeartbeat();

        // Then send every 15 seconds
        heartbeatTimer = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
    }

    // Stop heartbeat
    function stopHeartbeat() {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    }

    // Reset idle timer
    function resetIdleTimer() {
        // Clear existing timer
        if (idleTimer) {
            clearTimeout(idleTimer);
        }

        // If was idle, resume heartbeat
        if (isIdle) {
            isIdle = false;
            startHeartbeat();
        }

        // Set new idle timer
        idleTimer = setTimeout(() => {
            isIdle = true;
            stopHeartbeat();
        }, IDLE_TIMEOUT);
    }

    // Track user activity
    function initActivityTracking() {
        const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];

        events.forEach(event => {
            document.addEventListener(event, resetIdleTimer, { passive: true });
        });

        // Initial timer
        resetIdleTimer();
    }

    // Initialize on page load
    function init() {
        // Start heartbeat
        startHeartbeat();

        // Start activity tracking
        initActivityTracking();

        // Stop heartbeat when page is about to unload
        window.addEventListener('beforeunload', stopHeartbeat);
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
