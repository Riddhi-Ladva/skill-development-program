/**
 * Cart Page Quantity Interaction
 * Handles client-side quantity changes and price recalculations.
 */
document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.querySelector('.cart-items');
    if (!cartContainer) return;

    // UI Elements for Summary Recap
    const headerTotalItems = document.getElementById('header-total-items');
    const summaryTotalItems = document.getElementById('summary-total-items');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryTax = document.getElementById('summary-tax');
    const summaryOrderTotal = document.getElementById('summary-order-total');

    const TAX_RATE = 0.08;


    /**
     * Updates the cart session on the server
     * @param {string|number} productId 
     * @param {number} quantity 
     */
    const updateSessionQuantity = async (productId, quantity) => {
        try {
            const response = await fetch('update-cart-quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });
            const data = await response.json();
            if (!data.success) {
                console.error('Failed to update session:', data.message);
            }
        } catch (error) {
            console.error('Error updating cart session:', error);
        }
    };

    /**
     * Formats a number as currency
     * @param {number} amount 
     * @returns {string}
     */
    const formatCurrency = (amount) => {
        return '$' + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /**
     * Recalculates the entire cart summary
     */
    const updateCartSummary = () => {
        let totalItems = 0;
        let subtotal = 0;

        const items = document.querySelectorAll('.cart-item');
        items.forEach(item => {
            const qtyInput = item.querySelector('input[name="quantity"]');
            const unitPrice = parseFloat(item.querySelector('.unit-price').dataset.price);
            const quantity = parseInt(qtyInput.value);

            const itemTotal = unitPrice * quantity;
            item.querySelector('[data-item-total]').textContent = formatCurrency(itemTotal);

            totalItems += quantity;
            subtotal += itemTotal;
        });

        const tax = subtotal * TAX_RATE;
        const shipping = (subtotal === 0) ? 0 : (window.shippingPrice || 0);
        const orderTotal = subtotal + tax + shipping;

        // Update Summary UI
        if (headerTotalItems) headerTotalItems.textContent = totalItems;
        if (summaryTotalItems) summaryTotalItems.textContent = totalItems;
        if (summarySubtotal) summarySubtotal.textContent = formatCurrency(subtotal);
        if (summaryTax) summaryTax.textContent = formatCurrency(tax);
        if (summaryOrderTotal) summaryOrderTotal.textContent = formatCurrency(orderTotal);

        if (summaryShipping) {
            summaryShipping.textContent = shipping === 0 ? 'FREE' : formatCurrency(shipping);
        }

        // Handle Empty Cart State
        if (items.length === 0) {
            cartContainer.innerHTML = '<h2 class="visually-hidden">Cart Items</h2><p>Your cart is empty. <a href="products.php">Start shopping!</a></p>';
            const cartSummary = document.querySelector('.cart-summary');
            if (cartSummary) cartSummary.style.display = 'none';
        }
    };

    // Event Delegation for Quantity and Removal
    cartContainer.addEventListener('click', (event) => {
        const btn = event.target;

        // Quantity Buttons
        if (btn.classList.contains('qty-btn')) {
            const controls = btn.closest('.quantity-controls');
            const input = controls.querySelector('input');
            let value = parseInt(input.value);

            if (btn.classList.contains('plus')) {
                if (value < 10) value++;
            } else if (btn.classList.contains('minus')) {
                if (value > 1) value--;
            }

            input.value = value;
            updateCartSummary();

            // Extract ID from input id "quantity-{id}"
            const productId = input.id.replace('quantity-', '');
            updateSessionQuantity(productId, value);

            return;
        }

        // Remove Item Button
        if (btn.classList.contains('remove-item')) {
            const item = btn.closest('.cart-item');
            if (item) {
                const qtyInput = item.querySelector('input[name="quantity"]');
                const productId = qtyInput.id.replace('quantity-', '');

                // Call server to remove
                fetch('remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();

                            // Update UI with returned totals (avoids client-side recalc issues)
                            const headerTotalItems = document.getElementById('header-total-items');
                            const summaryTotalItems = document.getElementById('summary-total-items');
                            const summarySubtotal = document.getElementById('summary-subtotal');
                            const summaryShipping = document.getElementById('summary-shipping');
                            const summaryTax = document.getElementById('summary-tax');
                            const summaryOrderTotal = document.getElementById('summary-order-total');

                            if (headerTotalItems) headerTotalItems.textContent = data.totals.totalItems;
                            if (summaryTotalItems) summaryTotalItems.textContent = data.totals.totalItems;
                            if (summarySubtotal) summarySubtotal.textContent = data.totals.subtotal;
                            if (summaryShipping) summaryShipping.textContent = data.totals.shipping;
                            if (summaryTax) summaryTax.textContent = data.totals.tax;
                            if (summaryOrderTotal) summaryOrderTotal.textContent = data.totals.grandTotal;

                            // Check empty state
                            const remainingItems = document.querySelectorAll('.cart-item');
                            if (remainingItems.length === 0) {
                                cartContainer.innerHTML = '<h2 class="visually-hidden">Cart Items</h2><p>Your cart is empty. <a href="products.php">Start shopping!</a></p>';
                                const cartSummary = document.querySelector('.cart-summary');
                                if (cartSummary) cartSummary.style.display = 'none';
                            }
                        } else {
                            console.error('Failed to remove item:', data.message);
                        }
                    })
                    .catch(error => console.error('Error removing item:', error));
            }
        }
    });

    /**
     * Wishlist Section Logic
     */
    const initWishlistCarousel = () => {
        const container = document.getElementById('wishlist-items-container');
        const prevBtn = document.getElementById('wishlist-prev');
        const nextBtn = document.getElementById('wishlist-next');
        if (!container) return;

        // 1. Load data from localStorage
        const loadWishlist = () => {
            const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            renderWishlist(wishlist);
        };

        // 2. Render items
        const renderWishlist = (wishlist) => {
            if (wishlist.length === 0) {
                container.innerHTML = '<div class="wishlist-empty"><p>Your wishlist is empty. Explore our products to add your favorites!</p></div>';
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
                return;
            }

            container.innerHTML = '';
            wishlist.forEach(id => {
                const product = window.allProducts && window.allProducts[id];
                if (!product) return;

                const card = document.createElement('article');
                card.className = 'wishlist-card';
                card.dataset.id = id;
                card.innerHTML = `
                    <div class="item-image">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="item-info">
                        <h3><a href="product-detail.php?id=${id}">${product.name}</a></h3>
                        <p class="item-price">$${parseFloat(product.price).toFixed(2)}</p>
                    </div>
                    <div class="item-actions">
                        <a href="product-detail.php?id=${id}" class="view-link">View Details</a>
                        <button type="button" class="remove-wishlist" aria-label="Remove from wishlist">Remove</button>
                    </div>
                `;
                container.appendChild(card);
            });

            updateNavButtons();
        };

        // 3. Carousel Scroll Logic
        const scrollAmount = 300;
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                setTimeout(updateNavButtons, 300);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                setTimeout(updateNavButtons, 300);
            });
        }

        const updateNavButtons = () => {
            if (!prevBtn || !nextBtn) return;
            prevBtn.disabled = container.scrollLeft <= 0;
            nextBtn.disabled = container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
        };

        container.addEventListener('scroll', updateNavButtons);

        // 4. Remove Item Logic
        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-wishlist')) {
                const card = e.target.closest('.wishlist-card');
                const id = card.dataset.id;

                // Update localStorage
                let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                wishlist = wishlist.filter(item => item !== id);
                localStorage.setItem('wishlist', JSON.stringify(wishlist));

                // Visual feedback
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    card.remove();
                    if (container.children.length === 0) {
                        renderWishlist([]);
                    }
                    updateNavButtons();
                }, 300);
            }
        });

        // Initialize display
        loadWishlist();
    };

    // Run Wishlist logic
    initWishlistCarousel();

    /**
     * Shipping Selection Logic
     */
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    if (shippingRadios.length > 0) {
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', async (e) => {
                const type = e.target.value;

                try {
                    const response = await fetch('set-shipping.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ type: type })
                    });
                    const data = await response.json();

                    if (data.success) {
                        // Update DOM elements
                        const summaryShipping = document.getElementById('summary-shipping');
                        const summaryTax = document.getElementById('summary-tax');
                        const summaryOrderTotal = document.getElementById('summary-order-total');
                        const summarySubtotal = document.getElementById('summary-subtotal');

                        if (summaryShipping) summaryShipping.textContent = data.totals.shipping;
                        if (summaryTax) summaryTax.textContent = data.totals.tax;
                        if (summaryOrderTotal) summaryOrderTotal.textContent = data.totals.grandTotal;
                        if (summarySubtotal) summarySubtotal.textContent = data.totals.subtotal;
                    } else {
                        console.error('Failed to update shipping:', data.message);
                    }
                } catch (error) {
                    console.error('Error updating shipping:', error);
                }
            });
        });
    }
});
