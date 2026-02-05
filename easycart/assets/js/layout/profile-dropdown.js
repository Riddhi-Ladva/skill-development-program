/**
 * Profile Dropdown Toggle
 * 
 * Responsibility: Handles the opening and closing of the profile dropdown menu.
 * Logic: Toggles the 'active' class on click and closes the menu when clicking outside.
 */
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('profile-menu-toggle');
    const dropdownMenu = document.getElementById('profile-dropdown-menu');

    if (!toggleButton || !dropdownMenu) return;

    // Mobile: Toggle on click for touch devices where hover is unreliable
    toggleButton.addEventListener('click', function (e) {
        if (window.innerWidth <= 640) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        }
    });

    // Close mobile dropdown on click outside
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 640 && !dropdownMenu.contains(e.target) && !toggleButton.contains(e.target)) {
            dropdownMenu.classList.remove('active');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdownMenu.classList.remove('active');
        }
    });
});
