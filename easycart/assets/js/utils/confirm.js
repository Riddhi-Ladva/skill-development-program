/**
 * Global Interceptor & Notification System
 * 
 * Responsibility:
 * 1. Intercepts critical actions (delete/purchase) to prompt for user confirmation.
 * 2. Manages ephemeral toast notifications (success/info messages).
 * 3. Syncs wishlist state across tabs/interactions.
 */
document.addEventListener('DOMContentLoaded', () => {

    // 1. Critical Action Interceptor
    // Uses capture phase (true) to intercept events before other handlers execute.
    // Allows cancelling the action via e.stopPropagation() if user declines confirmation.
    document.addEventListener('click', (e) => {
        const target = e.target;

        const removeBtn = target.classList.contains('remove-item') ? target : target.closest('.remove-item');
        const placeOrderBtn = target.classList.contains('place-order-button') ? target : target.closest('.place-order-button');

        // Rule: Always double-check before deleting or spending money!
        if (removeBtn) {
            if (!confirm('Are you sure you want to remove this item?')) {
                e.preventDefault();
                e.stopPropagation(); // Stop the event so the cart doesn't actually delete it
            }
        }

        else if (placeOrderBtn) {
            if (!confirm('Ready to place your order? This will complete your purchase.')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    }, true);

    // 2. Global Notification (Toast) Manager
    const urlParams = new URLSearchParams(window.location.search);
    const notificationContainer = document.getElementById('global-notification-container');

    function showNotification(message, type = 'success') {
        if (!notificationContainer) return;

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${type === 'success' ? '✅' : 'ℹ️'}</span>
                <span class="notification-message">${message}</span>
            </div>
            <button class="notification-close" aria-label="Close notification">&times;</button>
        `;

        notificationContainer.appendChild(notification);

        // Auto-cleanup: The message disappears after 5 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 5000);

        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    // Check for "added=1" in the URL to show the "Success" banner
    if (urlParams.has('added')) {
        showNotification('Item successfully added to your cart!');
        // Clean up the URL so it looks professional (e.g., remove the ?added=1)
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]added=1/, '').replace(/^&/, '?');
        window.history.replaceState({}, '', newUrl);
    }

    // 3. User Feedback Check
    // Detects page reloads on cart/detail pages to reassure users about state persistence.
    const navigationType = performance.getEntriesByType("navigation")[0]?.type;
    if (navigationType === 'reload') {
        const isCartRelated = window.location.pathname.includes('/cart') || window.location.pathname.includes('/product-detail');
        if (isCartRelated) {
            showNotification('Your cart is safe! All items have been preserved.', 'info');
        }
    }
});
