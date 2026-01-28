/**
 * Global confirmation and notification logic for EasyCart
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Destructive Actions Confirmation (Using capture phase to intercept before bubbling)
    document.addEventListener('click', (e) => {
        const target = e.target;

        // Check for specific buttons or their parents
        const removeBtn = target.classList.contains('remove-item') ? target : target.closest('.remove-item');
        const placeOrderBtn = target.classList.contains('place-order-button') ? target : target.closest('.place-order-button');

        // Cart: Remove item
        if (removeBtn) {
            if (!confirm('Are you sure you want to remove this item?')) {
                e.preventDefault();
                e.stopPropagation(); // Stop event from reaching cart.js listeners
            }
        }

        // Checkout: Place Order
        else if (placeOrderBtn) {
            if (!confirm('Ready to place your order? This will complete your purchase.')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    }, true); // The 'true' enables the capture phase

    // 2. Success Messages (Non-blocking)
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

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 5000);

        // Close button click
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    // Check for "added" status in URL
    if (urlParams.has('added')) {
        showNotification('Item successfully added to your cart!');
        // Clean up URL without refresh
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]added=1/, '').replace(/^&/, '?');
        window.history.replaceState({}, '', newUrl);
    }

    // 3. Page Refresh Confirmation (Cart Preserved)
    // Check if we just added an item and then refreshed
    const navigationType = performance.getEntriesByType("navigation")[0]?.type;
    if (navigationType === 'reload') {
        // We only show this if the user was on the cart or product detail page
        const isCartRelated = window.location.pathname.includes('cart.php') || window.location.pathname.includes('product-detail.php');
        if (isCartRelated) {
            showNotification('Your cart is safe! All items have been preserved.', 'info');
        }
    }

    /**
     * 4. Global Wishlist Header Update
     * Keeps the wishlist count and heart icon in sync across the site.
     */
    const updateHeaderWishlist = () => {
        const wishlistLink = document.querySelector('.wishlist-link');
        const wishlistCount = document.getElementById('header-wishlist-count');
        if (!wishlistLink || !wishlistCount) return;

        const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
        const count = wishlist.length;

        wishlistCount.textContent = count;

        if (count > 0) {
            wishlistLink.classList.add('has-items');
        } else {
            wishlistLink.classList.remove('has-items');
        }
    };

    // Sync across tabs
    window.addEventListener('storage', (e) => {
        if (e.key === 'wishlist') updateHeaderWishlist();
    });

    // Sync on local clicks (since storage event doesn't fire for self)
    document.addEventListener('click', () => {
        // Small delay to ensure localStorage has been updated by other scripts (wishlist.js / cart.js)
        setTimeout(updateHeaderWishlist, 50);
    });

    window.addEventListener('wishlistUpdated', updateHeaderWishlist);

    // Initial load
    updateHeaderWishlist();
});
