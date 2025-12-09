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

// Unfriend confirm flow
let _pendingUnfriendForm = null;
let _pendingUnfriendName = '';

function showConfirmModal(name) {
    const modal = document.getElementById('confirmModal');
    const msg = document.getElementById('confirmMessage');
    if (name && name.trim() !== '') {
        msg.textContent = `Are you sure you want to remove ${name} from your friends?`;
    } else {
        msg.textContent = 'Are you sure you want to remove this friend?';
    }
    modal.style.display = 'flex';
}

function hideConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.style.display = 'none';
    _pendingUnfriendForm = null;
    _pendingUnfriendName = '';
}

document.addEventListener('click', (e) => {
    // Open confirm modal when clicking any .confirm-unfriend button
    const btn = e.target.closest('.confirm-unfriend');
    if (btn) {
        e.preventDefault();
        const form = btn.closest('form');
        if (!form) return;
        _pendingUnfriendForm = form;
        _pendingUnfriendName = btn.getAttribute('data-username') || '';
        showConfirmModal(_pendingUnfriendName);
    }
});

// Modal buttons
document.addEventListener('DOMContentLoaded', () => {
    const cancel = document.getElementById('confirmCancel');
    const ok = document.getElementById('confirmOK');

    if (cancel) cancel.addEventListener('click', (e) => { e.preventDefault(); hideConfirmModal(); });
    if (ok) ok.addEventListener('click', (e) => { e.preventDefault(); if (_pendingUnfriendForm) _pendingUnfriendForm.submit(); hideConfirmModal(); });

    const closeBtn = document.getElementById('confirmClose');
    if (closeBtn) closeBtn.addEventListener('click', (e) => { e.preventDefault(); hideConfirmModal(); });

    // Close with Escape
    document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape') hideConfirmModal();
    });
});
