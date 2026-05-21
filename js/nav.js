// ============================================================
// nav.js — Hamburger menu + Avatar dropdown
// Used by: all pages via header.php
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');
    const navAvatar   = document.getElementById('navAvatar');
    const navDropdown = document.getElementById('navDropdown');

    // ---- Hamburger ----
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', navLinks.classList.contains('open'));
            // Close avatar dropdown when closing nav
            if (!navLinks.classList.contains('open') && navDropdown) {
                navDropdown.classList.remove('open');
            }
        });
    }

    // ---- Avatar dropdown ----
    if (navAvatar && navDropdown) {
        navAvatar.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMobile = window.innerWidth <= 640;
            if (isMobile) {
                // On mobile: toggle inline profile menu inside nav panel
                navDropdown.classList.toggle('open');
                navAvatar.setAttribute('aria-expanded', navDropdown.classList.contains('open'));
            } else {
                const isOpen = navDropdown.classList.toggle('open');
                navAvatar.setAttribute('aria-expanded', isOpen);
            }
        });
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function (e) {
        if (hamburger && navLinks && !hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('open');
        }
        if (navAvatar && navDropdown && !navAvatar.contains(e.target) && !navDropdown.contains(e.target)) {
            navDropdown.classList.remove('open');
            if (navAvatar) navAvatar.setAttribute('aria-expanded', 'false');
        }
    });
});
