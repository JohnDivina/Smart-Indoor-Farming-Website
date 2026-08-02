// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function () {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    // Ensure sidebar is closed on mobile by default
    if (window.innerWidth <= 768) {
        document.body.classList.remove('sidebar-mobile-open');
    }

    // Toggle sidebar on hamburger click
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-mobile-open');
        });
    }

    // Close sidebar when clicking backdrop
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            document.body.classList.remove('sidebar-mobile-open');
        });
    }

    // Close sidebar when clicking close button
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function () {
            document.body.classList.remove('sidebar-mobile-open');
        });
    }

    // Close sidebar on window resize if switching to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            document.body.classList.remove('sidebar-mobile-open');
        }
    });
});
