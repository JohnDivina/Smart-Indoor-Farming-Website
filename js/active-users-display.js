/**
 * Active Users Sidebar Display
 * 
 * Features:
 * - Polls every 15 seconds
 * - Displays user initials in circular avatars
 * - Shows tooltip on hover with full name and current page
 * - Smooth add/remove animations
 */

(function () {
    'use strict';

    const POLL_INTERVAL = 15000; // 15 seconds
    let pollTimer = null;

    // Create sidebar container if it doesn't exist
    function createSidebarContainer() {
        // Find the sidebar navigation
        const sidebar = document.querySelector('.sidebar') || document.querySelector('[class*="sidebar"]');
        if (!sidebar) {
            console.warn('Sidebar not found');
            return null;
        }

        // Check if container already exists
        let container = document.getElementById('active-users-container');
        if (container) return container;

        // Create container
        container = document.createElement('div');
        container.id = 'active-users-container';
        container.className = 'active-users-section';
        container.innerHTML = `
            <div class="active-users-header">Active Users</div>
            <div class="active-users-list" id="active-users-list"></div>
        `;

        // Insert after navigation links (try to find "About Us" link or append at end)
        const aboutLink = Array.from(sidebar.querySelectorAll('a')).find(a =>
            a.textContent.toLowerCase().includes('about')
        );

        if (aboutLink && aboutLink.parentElement) {
            aboutLink.parentElement.after(container);
        } else {
            sidebar.appendChild(container);
        }

        return container;
    }

    // Create user avatar element
    function createUserAvatar(user) {
        const avatar = document.createElement('div');
        avatar.className = 'user-avatar';
        avatar.setAttribute('data-name', user.name);
        avatar.setAttribute('data-page', user.page);
        avatar.textContent = user.initials;

        // Create tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'user-tooltip';
        tooltip.innerHTML = `
            <div class="tooltip-name">${escapeHtml(user.name)}</div>
          
        `;
        avatar.appendChild(tooltip);

        return avatar;
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Update active users display
    function updateActiveUsers(users) {
        const listElement = document.getElementById('active-users-list');
        if (!listElement) return;

        // Get existing avatars
        const existingAvatars = Array.from(listElement.querySelectorAll('.user-avatar'));
        const existingNames = existingAvatars.map(a => a.getAttribute('data-name'));
        const newNames = users.map(u => u.name);

        // Remove users who are no longer active
        existingAvatars.forEach(avatar => {
            const name = avatar.getAttribute('data-name');
            if (!newNames.includes(name)) {
                avatar.classList.add('removing');
                setTimeout(() => avatar.remove(), 300);
            }
        });

        // Add new users
        users.forEach(user => {
            if (!existingNames.includes(user.name)) {
                const avatar = createUserAvatar(user);
                avatar.classList.add('adding');
                listElement.appendChild(avatar);
                setTimeout(() => avatar.classList.remove('adding'), 10);
            }
        });

        // Show/hide container based on user count
        const container = document.getElementById('active-users-container');
        if (container) {
            if (users.length > 0) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    }

    // Fetch active users from API
    function fetchActiveUsers() {
        fetch('/smartfarm2/api/get_active_users.php')
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    updateActiveUsers(data);
                }
            })
            .catch(error => {
                console.error('Error fetching active users:', error);
            });
    }

    // Start polling
    function startPolling() {
        if (pollTimer) return; // Already running

        // Fetch immediately
        fetchActiveUsers();

        // Then poll every 15 seconds
        pollTimer = setInterval(fetchActiveUsers, POLL_INTERVAL);
    }

    // Stop polling
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    // Initialize
    function init() {
        // Create sidebar container
        createSidebarContainer();

        // Start polling
        startPolling();

        // Stop polling when page unloads
        window.addEventListener('beforeunload', stopPolling);
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
