/**
 * EasyCart - Wishlist Component
 * 
 * Responsibility: Manages the wishlist (localStorage) and the carousel UI.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. WISHLIST BUTTON (PDP/PLP)
    const wishlistBtn = document.querySelector('.wishlist-button');
    if (wishlistBtn) {
        const productId = String(wishlistBtn.dataset.productId);
        const btnText = wishlistBtn.querySelector('.button-text');

        const getWishlist = () => JSON.parse(localStorage.getItem('wishlist') || '[]').map(String);

        const updateUI = (isIn) => {
            wishlistBtn.classList.toggle('active', isIn);
            if (btnText) btnText.textContent = isIn ? 'Remove from Wishlist' : 'Add to Wishlist';
        };

        updateUI(getWishlist().includes(productId));

        wishlistBtn.addEventListener('click', (e) => {
            e.preventDefault();
            let wishlist = getWishlist();
            const isIn = wishlist.includes(productId);
            wishlist = isIn ? wishlist.filter(id => id !== productId) : [...wishlist, productId];
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            updateUI(!isIn);
            window.dispatchEvent(new CustomEvent('wishlistUpdated'));
        });
    }

    // 2. WISHLIST CAROUSEL (Cart Page)
    const container = document.getElementById('wishlist-items-container');
    if (container) {
        const renderWishlist = () => {
            const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            if (wishlist.length === 0) {
                container.innerHTML = '<p>Your wishlist is empty.</p>';
                return;
            }

            container.innerHTML = '';
            wishlist.forEach(id => {
                const product = window.allProducts?.[id];
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

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-wishlist')) {
                const id = e.target.dataset.id;
                let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                wishlist = wishlist.filter(i => i !== id);
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                renderWishlist();
                window.dispatchEvent(new CustomEvent('wishlistUpdated'));
            }
        });

        renderWishlist();
        window.addEventListener('wishlistUpdated', renderWishlist);
    }
});
