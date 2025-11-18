// app.js
// Main JavaScript file for Flick Fusion application

// ---- Navbar Functionality ----
document.addEventListener('DOMContentLoaded', () => {
    // User Dropdown Menu Logic (support both id naming variants)
    const userMenuButton = document.getElementById('user-menu-button') || document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown') || document.getElementById('user-menu-dropdown');

    if (userMenuButton && userDropdown) {
        userMenuButton.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdown.classList.toggle('is-open');
        });

        window.addEventListener('click', function() {
            if (userDropdown.classList.contains('is-open')) {
                userDropdown.classList.remove('is-open');
        }
    });
    }
});