// app.js
// Main JavaScript file for Flick Fusion application

document.addEventListener('DOMContentLoaded', () => {
    // Future JavaScript functionality can be added here
});

// Toggle friend dropdown menu
function toggleFriendMenu(event, friendId) {
    event.preventDefault();
    event.stopPropagation();
    
    const menu = document.getElementById('friendMenu' + friendId);
    const dropdown = menu.closest('.friend-dropdown');
    const isCurrentlyOpen = menu.classList.contains('show');
    
    // Close all other dropdowns
    document.querySelectorAll('.friend-dropdown-menu.show').forEach(m => {
        m.classList.remove('show');
        m.closest('.friend-dropdown').classList.remove('active');
    });
    
    // Toggle current dropdown
    if (!isCurrentlyOpen) {
        menu.classList.add('show');
        dropdown.classList.add('active');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', (event) => {
    if (!event.target.closest('.friend-dropdown')) {
        document.querySelectorAll('.friend-dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
            menu.closest('.friend-dropdown').classList.remove('active');
        });
    }
});
