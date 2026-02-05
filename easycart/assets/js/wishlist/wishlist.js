/**
 * EasyCart - Wishlist Component
 * 
 * Responsibility: Manages the wishlist (localStorage for guests, DB for users).
 */

document.addEventListener('DOMContentLoaded', () => {
    const EC = window.EasyCart;

    // --- UTILS ---
    const getGuestWishlist = () => JSON.parse(localStorage.getItem('wishlist') || '[]').map(String);
    const setGuestWishlist = (wishlist) => localStorage.setItem('wishlist', JSON.stringify([...new Set(wishlist.map(String))]));

    const updateWishlistBadge = (count) => {
        const badge = document.getElementById('header-wishlist-count');
        if (badge) {
            badge.textContent = count;
            badge.closest('.wishlist-link')?.classList.toggle('has-items', count > 0);
        }
    };

    // Initial badge sync (especially for guests)
    if (EC && !EC.userId) {
        updateWishlistBadge(getGuestWishlist().length);
    } else if (EC && EC.wishlist) {
        updateWishlistBadge(EC.wishlist.length);
    }

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
        const initialWishlist = EC.userId ? (EC.wishlist || []) : getGuestWishlist();
        updateBtnState(initialWishlist.map(String).includes(productId));

        wishlistBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            wishlistBtn.disabled = true;

            if (EC.userId) {
                // LOGGED IN - DB
                const currentWishlist = (EC.wishlist || []).map(String);
                const isIn = currentWishlist.includes(productId);
                const endpoint = isIn ? 'remove.php' : 'add.php';

                try {
                    const response = await fetch(`${EC.ajaxUrl}/wishlist/${endpoint}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `product_id=${productId}`
                    });
                    const data = await response.json();
                    if (data.success) {
                        EC.wishlist = data.wishlist.map(String);
                        updateBtnState(!isIn);
                        updateWishlistBadge(EC.wishlist.length);
                    }
                } catch (err) {
                    console.error('Wishlist DB error:', err);
                }
            } else {
                // GUEST - localStorage
                let wishlist = getGuestWishlist();
                const isIn = wishlist.includes(productId);
                wishlist = isIn ? wishlist.filter(id => id !== productId) : [...wishlist, productId];
                setGuestWishlist(wishlist);
                updateBtnState(!isIn);
                updateWishlistBadge(wishlist.length);
            }
            wishlistBtn.disabled = false;
            window.dispatchEvent(new CustomEvent('wishlistUpdated'));
        });
    }

    // --- WISHLIST CAROUSEL (Cart Page / Wishlist Section) ---
    const container = document.getElementById('wishlist-items-container');
    if (container) {
        const renderWishlist = () => {
            const wishlist = EC.userId ? (EC.wishlist || []) : getGuestWishlist();

            if (wishlist.length === 0) {
                container.innerHTML = '<div class="wishlist-empty">Your wishlist is empty. Items you save will appear here.</div>';
                return;
            }

            if (!window.allProducts) {
                container.innerHTML = '<div class="wishlist-empty">Loading products...</div>';
                return;
            }

            container.innerHTML = '';
            wishlist.forEach(id => {
                const product = window.allProducts[id];
                if (!product) return;

                const card = document.createElement('article');
                card.className = 'wishlist-card';
                card.innerHTML = `
                    <div class="item-image"><img src="${product.image}" alt="${product.name}"></div>
                    <div class="item-info">
                        <h3><a href="product-detail.php?id=${id}">${product.name}</a></h3>
                        <p class="item-price">$${parseFloat(product.price).toFixed(2)}</p>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="remove-wishlist" data-id="${id}">Remove</button>
                    </div>
                `;
                container.appendChild(card);
            });
        };

        container.addEventListener('click', async (e) => {
            if (e.target.classList.contains('remove-wishlist')) {
                const id = String(e.target.dataset.id);
                e.target.disabled = true;

                if (EC.userId) {
                    try {
                        const response = await fetch(`${EC.ajaxUrl}/wishlist/remove.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `product_id=${id}`
                        });
                        const data = await response.json();
                        if (data.success) {
                            EC.wishlist = data.wishlist.map(String);
                            renderWishlist();
                            updateWishlistBadge(EC.wishlist.length);
                            // Also update button if on same page
                            if (wishlistBtn && wishlistBtn.dataset.productId === id) {
                                wishlistBtn.classList.remove('active');
                                if (btnText) btnText.textContent = 'Add to Wishlist';
                            }
                        }
                    } catch (err) {
                        console.error('Wishlist remove error:', err);
                    }
                } else {
                    let wishlist = getGuestWishlist();
                    wishlist = wishlist.filter(i => i !== id);
                    setGuestWishlist(wishlist);
                    renderWishlist();
                    updateWishlistBadge(wishlist.length);
                }
                window.dispatchEvent(new CustomEvent('wishlistUpdated', { detail: { productId: id, removed: true } }));
            }
        });

        renderWishlist();
        window.addEventListener('wishlistUpdated', renderWishlist);
    }
});
