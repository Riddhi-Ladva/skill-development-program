/**
 * EasyCart - Wishlist Component
 * 
 * Responsibility: Manages the wishlist (DB only).
 * strict: Guests cannot use wishlist.
 */

document.addEventListener('DOMContentLoaded', () => {
    const EC = window.EasyCart;

    // --- UTILS ---
    const updateWishlistBadge = (count) => {
        const badge = document.getElementById('header-wishlist-count');
        if (badge) {
            badge.textContent = count;
            badge.closest('.wishlist-link')?.classList.toggle('has-items', count > 0);
        }
    };

    // Initial Badge Update RE MOVED to rely on PHP SSR to prevent flicker.
    // The badge is already rendered by header.php.
    // We only update it when actions occur (Add/Remove).

    // --- WISHLIST BUTTON (PDP/PLP) ---
    const wishlistBtn = document.querySelector('.wishlist-button');
    if (wishlistBtn) {
        const productId = String(wishlistBtn.dataset.productId);
        const btnText = wishlistBtn.querySelector('.button-text');

        const updateBtnState = (isIn) => {
            wishlistBtn.classList.toggle('active', isIn);
            if (btnText) btnText.textContent = isIn ? 'Remove from Wishlist' : 'Add to Wishlist';
        };

        // Check initial state
        if (EC.userId) {
            const currentWishlist = (EC.wishlist || []).map(String);
            updateBtnState(currentWishlist.includes(productId));
        }

        wishlistBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            if (!EC.userId) {
                // Guests: Login required
                window.location.href = EC.baseUrl + '/pages/login.php?redirect=' + encodeURIComponent(window.location.href);
                return;
            }

            const currentWishlist = (EC.wishlist || []).map(String);
            const isIn = currentWishlist.includes(productId);
            const endpoint = isIn ? 'remove.php' : 'add.php';

            // STRICT: No Optimistic UI. Wait for server response.
            wishlistBtn.classList.add('loading-pulse');

            try {
                const response = await fetch(`${EC.ajaxUrl}/wishlist/${endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId }),
                    keepalive: true
                });
                const data = await response.json();

                if (data.success) {
                    // Sync Truth from Server
                    EC.wishlist = data.wishlist.map(String);

                    // Update Badge with count from DB response (matches Cart logic)
                    updateWishlistBadge(data.count);

                    // Update Button State based on new wishlist array
                    // This ensures button reflects REAL DB state
                    const newIsIn = EC.wishlist.includes(productId);
                    updateBtnState(newIsIn);
                } else {
                    // Handle logic error
                    throw new Error(data.error || 'Server error');
                }
            } catch (err) {
                console.error('Wishlist DB error:', err);
                // Error handling - notify user
                alert('Action failed. Please try again.');
            } finally {
                wishlistBtn.classList.remove('loading-pulse');
            }
        });
    }

    // --- WISHLIST SECTION (Cart Page) ---
    const container = document.getElementById('wishlist-items-container');
    if (container) {
        // Event Delegation
        container.addEventListener('click', async (e) => {
            const target = e.target;

            // REMOVE ACTION
            if (target.classList.contains('remove-wishlist')) {
                const id = String(target.dataset.id);
                target.disabled = true;

                if (!EC.userId) return; // Should not happen on cart page as section is hidden for guests usually, but safe guard

                try {
                    const response = await fetch(`${EC.ajaxUrl}/wishlist/remove.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: id }),
                        keepalive: true
                    });
                    const data = await response.json();

                    if (data.success) {
                        EC.wishlist = data.wishlist.map(String);
                        updateWishlistBadge(EC.wishlist.length);

                        // Remove Card
                        const card = target.closest('.wishlist-card');
                        if (card) {
                            card.remove();
                            // Check empty
                            if (container.children.length === 0) {
                                container.innerHTML = '<div class="wishlist-empty">Your wishlist is empty. Items you save will appear here.</div>';
                            }
                        }
                    }
                } catch (err) {
                    console.error('Wishlist remove error:', err);
                }
            }

            // ADD TO CART ACTION
            if (target.classList.contains('add-to-cart-from-wishlist')) {
                const id = String(target.dataset.id);
                target.disabled = true;
                target.textContent = 'Moving...';

                try {
                    // 1. Strict Move to Cart (Atomic backend op)
                    const response = await fetch(`${EC.ajaxUrl}/wishlist/move_to_cart.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: id })
                    });
                    const data = await response.json();

                    if (data.success) {
                        // Update global state if needed, but we are reloading mostly
                        EC.wishlist = data.wishlist.map(String);
                        updateWishlistBadge(EC.wishlist.length);

                        // Reload Page to update Cart Table interaction
                        window.location.reload();
                    } else {
                        alert('Could not move to cart: ' + (data.error || 'Unknown error'));
                        target.disabled = false;
                        target.textContent = 'Add to Cart';
                    }
                } catch (err) {
                    console.error('Move to cart error:', err);
                    target.disabled = false;
                    target.textContent = 'Add to Cart';
                }
            }
        });
    }
});
