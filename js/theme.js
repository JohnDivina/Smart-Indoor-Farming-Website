/**
 * theme.js — Global Dark / Light mode toggle
 * Works on every page that includes this script and has a #themeToggle button.
 */

// Apply saved theme ASAP (header.php already does this inline, but this covers edge cases)
(function () {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
})();

// --- Global high-end confirmation modal ---
window.showConfirm = function (options = {}) {
    const {
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Proceed',
        cancelText = 'Cancel',
        type = 'primary' // primary, danger, warning
    } = options;

    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'glass-confirm-modal';

        const typeColor = type === 'danger' ? 'var(--accent-danger)' :
            type === 'warning' ? 'var(--accent-warning)' :
                'var(--accent-primary)';

        const icon = type === 'danger' ? 'fa-triangle-exclamation' :
            type === 'warning' ? 'fa-circle-exclamation' :
                'fa-circle-question';

        modal.style.cssText = `
            position: fixed; inset: 0; z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        `;

        modal.innerHTML = `
            <div class="modal-backdrop" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);"></div>
            <div class="modal-container glass-panel" style="
                position: relative; width: 90%; max-width: 400px;
                padding: 32px; text-align: center;
                transform: translateY(20px); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            ">
                <div style="font-size: 48px; color: ${typeColor}; margin-bottom: 20px;">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <h3 style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: var(--text-primary);">${title}</h3>
                <p style="margin: 0 0 32px; color: var(--text-secondary); line-height: 1.6; font-size: 15px;">${message}</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="modal-cancel-btn btn" style="
                        background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
                        color: var(--text-secondary); flex: 1; padding: 12px;
                    ">${cancelText}</button>
                    <button class="modal-confirm-btn btn" style="
                        background: ${typeColor}; color: #fff; flex: 1; padding: 12px;
                        box-shadow: 0 4px 12px ${typeColor}44;
                    ">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            modal.querySelector('.modal-container').style.transform = 'translateY(0)';
        });

        const close = (result) => {
            modal.style.opacity = '0';
            modal.querySelector('.modal-container').style.transform = 'translateY(20px)';
            setTimeout(() => {
                modal.remove();
                resolve(result);
            }, 300);
        };

        modal.querySelector('.modal-confirm-btn').onclick = () => close(true);
        modal.querySelector('.modal-cancel-btn').onclick = () => close(false);
        modal.querySelector('.modal-backdrop').onclick = () => close(false);
    });
};

// Wire up the toggle button after DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('themeToggle');
    if (!btn) return;

    // Sync icon to current theme
    function syncIcon() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const icon = btn.querySelector('i');
        if (icon) {
            // Font Awesome moon/sun
            // Light Mode = Sun, Dark Mode = Moon
            if (icon.className.includes('fa-')) {
                icon.className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            }
            // Bootstrap Icons
            if (icon.className.includes('bi-')) {
                icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }
    }

    // --- Global Chart.js Theme Application ---
    function applyChartTheme(theme) {
        if (typeof Chart === 'undefined') return;

        const currentTheme = theme || localStorage.getItem('theme') || 'light';
        const isDark = currentTheme === 'dark';

        // Colors from design system
        const labelColor = isDark ? '#94a3ab' : '#4a5568';
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const titleColor = isDark ? '#ffffff' : '#1a232c';

        // Global defaults for new charts
        Chart.defaults.color = labelColor;

        // Update all existing instances safely
        if (Chart.instances) {
            Object.values(Chart.instances).forEach(function (chart) {
                if (chart && chart.options && chart.options.scales) {
                    Object.values(chart.options.scales).forEach(function (scale) {
                        if (scale.ticks) scale.ticks.color = labelColor;
                        if (scale.grid) scale.grid.color = gridColor;
                        if (scale.title) scale.title.color = labelColor;
                    });
                }
                // Optional: Update legend text
                if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                    chart.options.plugins.legend.labels.color = labelColor;
                }
                chart.update('none'); // Update without animation for instant switch
            });
        }
    }

    // Apply on load
    applyChartTheme();
    syncIcon();

    btn.addEventListener('click', function () {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';

        if (newTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }

        localStorage.setItem('theme', newTheme);
        syncIcon();
        applyChartTheme(newTheme);
    });

    // --- Global Mobile Navigation Injection ---
    const topHeader = document.querySelector('.top-header');
    if (topHeader && !topHeader.querySelector('.mobile-nav-group') && window.innerWidth <= 900) {
        const navGroup = document.createElement('div');
        navGroup.className = 'mobile-nav-group';

        const path = window.location.pathname.toLowerCase();
        const isDashboard = path === '/' ||
            path === '' ||
            path.endsWith('index.php') ||
            path.endsWith('dashboard/');

        // --- Hamburger Logic ---
        const menuBtn = document.createElement('button');
        menuBtn.className = 'icon-button mobile-menu-btn';
        menuBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';

        navGroup.appendChild(menuBtn);

        // --- Back Button Logic ---
        // Specifically check for our added back links
        const hasBackLink = !!document.querySelector('.page-title a[href*="index.php"]') ||
            !!topHeader.textContent.toLowerCase().includes('back to dashboard');

        if (!isDashboard && !hasBackLink) {
            const backBtn = document.createElement('button');
            backBtn.className = 'icon-button mobile-back-btn';
            backBtn.innerHTML = '<i class="fa-solid fa-arrow-left"></i>';
            backBtn.onclick = () => window.location.href = 'index.php';
            navGroup.appendChild(backBtn);
        }

        topHeader.insertBefore(navGroup, topHeader.firstChild);

        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);

            let scrollPos = 0;
            const toggleSidebar = () => {
                const isOpen = sidebar.classList.contains('mobile-open');
                if (!isOpen) {
                    // Opening
                    scrollPos = window.pageYOffset;
                    document.body.style.top = `-${scrollPos}px`;
                    document.body.classList.add('sidebar-open');
                    sidebar.classList.add('mobile-open');
                    backdrop.classList.add('show');
                } else {
                    // Closing
                    document.body.classList.remove('sidebar-open');
                    document.body.style.top = '';
                    sidebar.classList.remove('mobile-open');
                    backdrop.classList.remove('show');
                    window.scrollTo(0, scrollPos);
                }
            };

            menuBtn.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', toggleSidebar);
        }
    }
});

// Expose toggleTheme for any onclick="toggleTheme()" usages
window.toggleTheme = function () {
    const btn = document.getElementById('themeToggle');
    if (btn) btn.click();
};
