/**
 * EasyCart - Wishlist Component
 * 
 * Responsibility: Manages the wishlist.
 * Supports:
 * - Logged-in: DB Sync
 * - Guest: LocalStorage
 */

document.addEventListener('DOMContentLoaded', () => {
    const EC = window.EasyCart;
    const GUEST_KEY = 'wishlist';

    console.log('[Wishlist] Init State:', { userId: EC.userId, wishlist: EC.wishlist });

    // --- UTILS ---

    const getGuestWishlist = () => {
        try {
            return JSON.parse(localStorage.getItem(GUEST_KEY)) || [];
        } catch {
            return [];
        }
    };

    const saveGuestWishlist = (list) => {
        localStorage.setItem(GUEST_KEY, JSON.stringify(list));
    };

    const updateWishlistBadge = (count) => {
        const badge = document.getElementById('header-wishlist-count');
        if (badge) {
            badge.textContent = count;
            const container = badge.closest('.wishlist-link');
            if (container) {
                container.classList.toggle('has-items', count > 0);
            }
        }
    };

    const updateCartBadge = (count) => {
        const badge = document.getElementById('header-total-items');
        if (badge) {
            badge.textContent = count;
            const container = badge.closest('.cart-link');
            if (container) {
                container.classList.toggle('has-items', parseInt(count) > 0);
            }
        }
    };

    // Initialize Badge and State
    if (!EC.userId) {
        // Guests: Sync from LocalStorage
        const guestList = getGuestWishlist();
        EC.wishlist = guestList.map(String);
    } else {
        // Logged-in: PHP already set EC.wishlist from DB, ensure they are strings
        if (Array.isArray(EC.wishlist)) {
            EC.wishlist = EC.wishlist.map(String);
        } else {
            EC.wishlist = [];
        }
    }

    // Always update badge on load to ensure UI sync
    updateWishlistBadge(EC.wishlist.length);

    /**
     * Helper to init listeners for wishlist buttons (PDP or PLP)
     */
    const initWishlistButtons = () => {
        const wishlistBtns = document.querySelectorAll('.wishlist-button');

        wishlistBtns.forEach(btn => {
            // Prevent multiple listeners if called multiple times
            if (btn.dataset.init) return;
            btn.dataset.init = 'true';

            const productId = String(btn.dataset.productId);
            const btnText = btn.querySelector('.button-text');

            const updateBtnState = (isIn) => {
                btn.classList.toggle('active', isIn);
                if (btnText) btnText.textContent = isIn ? 'Remove from Wishlist' : 'Add to Wishlist';
            };

            // Check initial state
            updateBtnState(EC.wishlist.includes(productId));

            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isIn = EC.wishlist.includes(productId);

                // GUEST LOGIC
                if (!EC.userId) {
                    let list = getGuestWishlist().map(String);
                    if (isIn) {
                        list = list.filter(id => id !== productId);
                        updateBtnState(false);
                    } else {
                        if (!list.includes(productId)) list.push(productId);
                        updateBtnState(true);
                    }
                    saveGuestWishlist(list);
                    EC.wishlist = list;
                    updateWishlistBadge(list.length);
                    return;
                }

                // USER LOGIC
                const endpoint = isIn ? 'remove.php' : 'add.php';
                btn.classList.add('loading-pulse');

                try {
                    const response = await fetch(`${EC.ajaxUrl}/wishlist/${endpoint}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId }),
                    });
                    const data = await response.json();

                    if (data.success) {
                        EC.wishlist = data.wishlist.map(String);
                        updateWishlistBadge(data.count);
                        updateBtnState(EC.wishlist.includes(productId));
                    } else {
                        throw new Error(data.error || 'Server error');
                    }
                } catch (err) {
                    console.error('Wishlist DB error:', err);
                } finally {
                    btn.classList.remove('loading-pulse');
                }
            });
        });
    };

    initWishlistButtons();

    // --- WISHLIST SECTION (Cart Page) ---
    const container = document.getElementById('wishlist-items-container');

    // RENDER GUEST WISHLIST IF ON CART PAGE
    if (container && !EC.userId) {
        const renderGuestWishlist = async () => {
            const list = getGuestWishlist();
            if (list.length === 0) return;

            try {
                container.innerHTML = '<p>Loading wishlist...</p>';
                const response = await fetch(`${EC.ajaxUrl}/wishlist/get-products.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: list })
                });
                const data = await response.json();

                if (data.success && data.products.length > 0) {
                    container.innerHTML = data.products.map(item => `
                        <article class="wishlist-card" data-product-id="${item.id}">
                            <div class="item-image"><img src="${item.image}" alt="${item.name}"></div>
                            <div class="item-info">
                                <h3><a href="${EC.baseUrl}/product-detail?id=${item.id}">${item.name}</a></h3>
                                <p class="item-price">$${parseFloat(item.price).toFixed(2)}</p>
                            </div>
                            <div class="item-actions">
                                <button type="button" class="action-btn add-to-cart-from-wishlist" data-id="${item.id}" ${item.is_in_stock ? '' : 'disabled'}>Add to Cart</button>
                                <button type="button" class="remove-wishlist" data-id="${item.id}">Remove</button>
                            </div>
                        </article>
                    `).join('');
                }
            } catch (e) {
                console.error("Failed to load guest wishlist", e);
            }
        };
        renderGuestWishlist();
    }

    if (container) {
        container.addEventListener('click', async (e) => {
            const target = e.target;
            const id = String(target.dataset.id);
            if (!id) return;

            // REMOVE ACTION
            if (target.classList.contains('remove-wishlist')) {
                target.disabled = true;
                if (!EC.userId) {
                    let list = getGuestWishlist().map(String);
                    list = list.filter(item => item !== id);
                    saveGuestWishlist(list);
                    EC.wishlist = list;
                    updateWishlistBadge(list.length);
                    target.closest('.wishlist-card')?.remove();
                } else {
                    try {
                        const response = await fetch(`${EC.ajaxUrl}/wishlist/remove.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ product_id: id })
                        });
                        const data = await response.json();
                        if (data.success) {
                            EC.wishlist = data.wishlist.map(String);
                            updateWishlistBadge(data.count);
                            target.closest('.wishlist-card')?.remove();
                        }
                    } catch (err) { console.error(err); }
                }
                return;
            }

            // ADD TO CART ACTION (Move)
            if (target.classList.contains('add-to-cart-from-wishlist')) {
                target.disabled = true;
                const originalText = target.textContent;
                target.textContent = 'Moving...';

                try {
                    let success = false;
                    let cartCount = 0;

                    if (!EC.userId) {
                        const res = await fetch(`${EC.ajaxUrl}/cart/add.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ product_id: id, quantity: 1 })
                        });
                        const data = await res.json();
                        if (data.success) {
                            success = true;
                            cartCount = data.totalItems;
                            let list = getGuestWishlist().map(String);
                            list = list.filter(item => item !== id);
                            saveGuestWishlist(list);
                            EC.wishlist = list;
                            updateWishlistBadge(list.length);
                        }
                    } else {
                        const res = await fetch(`${EC.ajaxUrl}/wishlist/move-to-cart.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ product_id: id })
                        });
                        const data = await res.json();
                        if (data.success) {
                            success = true;
                            cartCount = data.cartCount;
                            EC.wishlist = data.wishlist.map(String);
                            updateWishlistBadge(EC.wishlist.length);
                        }
                    }

                    if (success) {
                        updateCartBadge(cartCount);
                        target.closest('.wishlist-card')?.remove();
                        if (container.querySelectorAll('.wishlist-card').length === 0) {
                            container.innerHTML = '<div class="wishlist-empty">Your wishlist is empty.</div>';
                        }

                        // REFRESH CART UI (If on cart page)
                        if (window.EasyCart && window.EasyCart.UI && window.EasyCart.UI.refreshCartHTML) {
                            window.EasyCart.UI.refreshCartHTML();
                        }
                    } else {
                        target.disabled = false;
                        target.textContent = originalText;
                    }
                } catch (err) {
                    console.error(err);
                    target.disabled = false;
                    target.textContent = originalText;
                }
            }
        });
    }
});
