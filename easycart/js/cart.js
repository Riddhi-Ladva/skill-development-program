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


    /**
     * Updates the cart session on the server and refreshes totals
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

            // Check if response is OK before parsing
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.totals) {
                updateTotalsFromResponse(data.totals, data.shippingOptions);
            } else {
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
     * Recalculates item totals and total item count (client-side only for display)
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

        // Update item count
        if (headerTotalItems) headerTotalItems.textContent = totalItems;
        if (summaryTotalItems) summaryTotalItems.textContent = totalItems;

        // Handle Empty Cart State
        if (items.length === 0) {
            cartContainer.innerHTML = '<h2 class="visually-hidden">Cart Items</h2><p>Your cart is empty. <a href="products.php">Start shopping!</a></p>';
            const cartSummary = document.querySelector('.cart-summary');
            if (cartSummary) cartSummary.style.display = 'none';
        }
    };

    // Event Delegation for Quantity Buttons and Removal
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

            if (input.value != value) {
                input.value = value;
                handleQuantityChange(input);
            }
            return;
        }

        // Remove Item Button
        if (btn.classList.contains('remove-item')) {
            handleRemoveItem(btn);
        }
    });

    // Handle manual input changes
    cartContainer.addEventListener('change', (event) => {
        if (event.target.name === 'quantity') {
            handleQuantityChange(event.target);
        }
    });

    // Centralized handler for quantity updates
    const handleQuantityChange = (input) => {
        updateCartSummary(); // Optimistic UI update for line totals

        const productId = input.id.replace('quantity-', '');
        const value = parseInt(input.value);

        updateSessionQuantity(productId, value);
    };

    // Centralized handler for item removal
    const handleRemoveItem = (btn) => {
        const item = btn.closest('.cart-item');
        if (item) {
            const qtyInput = item.querySelector('input[name="quantity"]');
            const productId = qtyInput.id.replace('quantity-', '');

            // Visual feedback
            item.style.opacity = '0.5';

            fetch('remove-from-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        // Note: remove-from-cart.php needs to be updated to return shippingOptions too if we want dynamic update there
                        // For now we assume remove just updates totals, but consistency would require updating options too.
                        // Let's assume remove-from-cart returns it or we treat it as optional.
                        updateTotalsFromResponse(data.totals, data.shippingOptions);
                        checkEmptyState();
                    } else {
                        console.error('Failed to remove item:', data.message);
                        item.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                    item.style.opacity = '1';
                });
        }
    };

    // Shared function to update Order Summary DOM and Shipping Options
    const updateTotalsFromResponse = (totals, shippingOptions) => {
        if (totals) {
            const headerTotalItems = document.getElementById('header-total-items');
            const summaryTotalItems = document.getElementById('summary-total-items');
            const summarySubtotal = document.getElementById('summary-subtotal');
            const summaryShipping = document.getElementById('summary-shipping');
            const summaryTax = document.getElementById('summary-tax');
            const summaryOrderTotal = document.getElementById('summary-order-total');

            if (headerTotalItems && totals.totalItems !== undefined) headerTotalItems.textContent = totals.totalItems;
            if (summaryTotalItems && totals.totalItems !== undefined) summaryTotalItems.textContent = totals.totalItems;
            if (summarySubtotal) summarySubtotal.textContent = totals.subtotal;
            if (summaryShipping) summaryShipping.textContent = totals.shipping;
            if (summaryTax) summaryTax.textContent = totals.tax;
            if (summaryOrderTotal) summaryOrderTotal.textContent = totals.grandTotal;
        }

        // Update Shipping Option Prices (Radio Buttons)
        if (shippingOptions) {
            const shippingContainer = document.querySelector('.shipping-method-section');
            if (shippingContainer) {
                // Map session keys to radio values if they differ, but here they are same (standard, express, white-glove, freight)

                // Use a helper to find the radio and update its sibling price element
                const updateOptionPrice = (method, price) => {
                    const radio = shippingContainer.querySelector(`input[value="${method}"]`);
                    if (radio) {
                        const label = radio.closest('.shipping-option');
                        const priceEl = label ? label.querySelector('.option-price') : null;
                        if (priceEl) {
                            priceEl.textContent = price;
                        }
                    }
                };

                updateOptionPrice('standard', shippingOptions.standard);
                updateOptionPrice('express', shippingOptions.express);
                updateOptionPrice('white-glove', shippingOptions['white-glove']);
                updateOptionPrice('freight', shippingOptions.freight);
            }
        }
    };

    const checkEmptyState = () => {
        const items = document.querySelectorAll('.cart-item');
        if (items.length === 0) {
            cartContainer.innerHTML = '<h2 class="visually-hidden">Cart Items</h2><p>Your cart is empty. <a href="products.php">Start shopping!</a></p>';
            const cartSummary = document.querySelector('.cart-summary');
            if (cartSummary) cartSummary.style.display = 'none';
        }
    };

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
