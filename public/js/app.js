// app.js
// Main JavaScript file for Flick Fusion application

document.addEventListener('DOMContentLoaded', () => {
    // Mobile nav toggle
    const navToggle = document.querySelector('.nav-toggle');
    const header = document.querySelector('.main-header');
    const mainNav = document.getElementById('mainNav');

    if (navToggle && header && mainNav) {
        navToggle.addEventListener('click', (e) => {
            e.preventDefault();
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!expanded));
            header.classList.toggle('nav-open');
            mainNav.classList.toggle('open');
            // morph hamburger to X
            navToggle.classList.toggle('open');

            // Make the mobile nav full-width and positioned below the header without pushing content
            if (mainNav.classList.contains('open')) {
                // compute header bottom relative to viewport and position nav fixed below it
                const rect = header.getBoundingClientRect();
                mainNav.style.position = 'fixed';
                mainNav.style.left = '0';
                mainNav.style.right = '0';
                mainNav.style.top = (rect.bottom) + 'px';
                mainNav.style.width = '100%';
                mainNav.style.zIndex = '995';
            } else {
                // clean up inline styles
                mainNav.style.position = '';
                mainNav.style.left = '';
                mainNav.style.right = '';
                mainNav.style.top = '';
                mainNav.style.width = '';
                mainNav.style.zIndex = '';
            }
        });

        // Close nav when clicking outside
        document.addEventListener('click', (ev) => {
            if (!ev.target.closest('.header-content') && header.classList.contains('nav-open')) {
                header.classList.remove('nav-open');
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
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
