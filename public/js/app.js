// app.js
// Main JavaScript file for Flick Fusion application

// ---- Navbar Functionality ----
document.addEventListener('DOMContentLoaded', () => {
    // User Dropdown Menu Logic
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (userMenuButton && userDropdown) {
        // When the user icon is clicked, show/hide the dropdown
        userMenuButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevents the window click event from firing
            userDropdown.classList.toggle('is-open');
        });

        // If the user clicks anywhere else on the page, close the dropdown
        window.addEventListener('click', function(event) {
            if (userDropdown.classList.contains('is-open')) {
                userDropdown.classList.remove('is-open');
            }
        });
    }
});