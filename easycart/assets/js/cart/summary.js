/**
 * EasyCart - Cart Summary Component
 * 
 * Responsibility: Updates totals (subtotal, shipping, tax, grand total) across the UI.
 * This is used as a shared utility by quantity and shipping handlers.
 */

window.EasyCart = window.EasyCart || {};
window.EasyCart.UI = window.EasyCart.UI || {};

(function () {
    const headerTotalItems = document.getElementById('header-total-items');
    // New Element for Cart Page Header Count
    const cartPageCount = document.getElementById('cart-page-count');
    const cartPageText = document.getElementById('cart-page-text');

    const summaryTotalItems = document.getElementById('summary-total-items');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryTax = document.getElementById('summary-tax');
    const summaryOrderTotal = document.getElementById('summary-order-total');

    const formatCurrency = (amount) => {
        return '$' + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /**
     * Updates the summary UI with raw data returned from AJAX endpoints.
     */
    /**
     * Updates the summary UI with raw data returned from AJAX endpoints.
     */
    window.EasyCart.UI.updateTotalsFromResponse = (totals, shippingOptions, cartItems, shippingConstraints) => {
        if (totals) {
            if (headerTotalItems && totals.totalItems !== undefined) {
                headerTotalItems.textContent = totals.totalItems;
                const cartLink = headerTotalItems.closest('.icon-wrapper');
                if (cartLink) {
                    cartLink.classList.toggle('has-items', parseInt(totals.totalItems) > 0);
                }
            }
            // Update Cart Page Count
            if (cartPageCount && totals.totalItems !== undefined) {
                cartPageCount.innerText = totals.totalItems;
            }
            // Update Cart Page Text (item/items)
            if (cartPageText && totals.totalItems !== undefined) {
                const count = parseInt(totals.totalItems);
                cartPageText.innerText = count === 1 ? 'item' : 'items';
            }

            if (summaryTotalItems && totals.totalItems !== undefined) summaryTotalItems.textContent = totals.totalItems;
            if (summarySubtotal) summarySubtotal.textContent = totals.subtotal;
            if (summaryShipping) summaryShipping.textContent = totals.shipping;
            if (summaryTax) summaryTax.textContent = totals.tax;
            if (summaryOrderTotal) summaryOrderTotal.textContent = totals.grandTotal;

            // Handle Promo Row
            const promoRowLabel = document.getElementById('promo-row-label');
            const promoRowAmount = document.getElementById('promo-row-amount');
            if (promoRowLabel && promoRowAmount) {
                if (totals.promo_discount && totals.promo_discount !== '$0.00') {
                    promoRowLabel.style.display = 'block';
                    promoRowAmount.style.display = 'block';
                    promoRowAmount.textContent = totals.promo_discount; // Already formatted as -$X.XX
                } else {
                    promoRowLabel.style.display = 'none';
                    promoRowAmount.style.display = 'none';
                }
            }
        }

        // Update individual cart items (Discounts, Final Totals, Shipping Eligibility)
        if (cartItems) {
            Object.entries(cartItems).forEach(([id, item]) => {
                const row = document.querySelector(`.cart-item[data-product-id="${id}"]`);
                if (row) {
                    // Update Total Price
                    const totalEl = row.querySelector('[data-item-total]');
                    if (totalEl) totalEl.textContent = '$' + item.final_total.toFixed(2);

                    // Update Discount
                    const discountEl = row.querySelector('.item-discount');
                    if (discountEl) {
                        if (item.discount_amount > 0) {
                            discountEl.style.display = 'block';
                            discountEl.textContent = `Discount (${item.discount_percent}%): -$${item.discount_amount.toFixed(2)}`;
                        } else {
                            discountEl.style.display = 'none';
                        }
                    }

                    // NEW: Update Shipping Eligibility
                    const eligibilityEl = row.querySelector('.shipping-eligibility');
                    if (eligibilityEl && item.shipping_eligibility) {
                        eligibilityEl.innerHTML = `<span class="shipping-label ${item.shipping_eligibility.class}">${item.shipping_eligibility.icon} ${item.shipping_eligibility.label}</span>`;
                    }
                }
            });
        }

        if (shippingOptions) {
            Object.entries(shippingOptions).forEach(([method, price]) => {
                const radio = document.querySelector(`input[name="shipping"][value="${method}"]`);
                if (radio) {
                    const priceEl = radio.closest('.shipping-option')?.querySelector('.option-price');
                    if (priceEl) priceEl.textContent = price;
                }
            });
        }

        // NEW: Handle Shipping Constraints (Enable/Disable logic)
        if (shippingConstraints) {
            const requiresFreight = shippingConstraints.requires_freight;

            // Helper to toggle
            const toggleOption = (val, disabled) => {
                const input = document.querySelector(`input[name="shipping"][value="${val}"]`);
                if (input) {
                    input.disabled = disabled;
                    // Find the parent card and toggle the visual disabled state
                    const card = input.closest('.shipping-option');
                    if (card) {
                        if (disabled) {
                            card.classList.add('is-disabled');
                        } else {
                            card.classList.remove('is-disabled');
                        }
                    }
                }
            };

            // Rule 1: Custom Logic
            // If requiresFreight: Disable Standard/Express, Enable White Glove/Freight
            // If !requiresFreight: Enable Standard/Express, Disable White Glove/Freight
            toggleOption('standard', requiresFreight);
            toggleOption('express', requiresFreight);
            toggleOption('white-glove', !requiresFreight);
            toggleOption('freight', !requiresFreight);

            // Auto-correct selection using shared logic
            if (window.EasyCart.Shipping && window.EasyCart.Shipping.validateRules) {
                window.EasyCart.Shipping.validateRules();
            }
        }
    };

    /**
     * Optimistic UI update: Updates Quantity inputs and header counts immediately.
     * DOES NOT update prices (waiting for server to ensure discount logic is correct).
     */
    window.EasyCart.UI.refreshSummaryOptimistically = () => {
        let totalItems = 0;

        const items = document.querySelectorAll('.cart-item');
        items.forEach(item => {
            const qtyInput = item.querySelector('input[name="quantity"]');
            const quantity = parseInt(qtyInput.value);
            totalItems += quantity;

            // We do NOT calculate item totals here anymore.
            // That business logic lives strictly on the server.
        });

        if (headerTotalItems) {
            headerTotalItems.textContent = totalItems;
            const cartLink = headerTotalItems.closest('.icon-wrapper');
            if (cartLink) {
                cartLink.classList.toggle('has-items', totalItems > 0);
            }
        }

        // Update Cart Page Count Optimistically
        if (cartPageCount) {
            cartPageCount.innerText = totalItems;
        }
        if (cartPageText) {
            cartPageText.innerText = totalItems === 1 ? 'item' : 'items';
        }

        if (summaryTotalItems) summaryTotalItems.textContent = totalItems;

        if (items.length === 0) {
            const container = document.querySelector('.cart-items');
            if (container) container.innerHTML = `<p>Your cart is empty. <a href="${EasyCart.baseUrl}/products">Start shopping!</a></p>`;
            document.querySelector('.cart-summary')?.remove();
        }
    };
    /**
     * Refreshes the entire cart UI by fetching updated HTML from server.
     * Updates: .cart-items, .cart-summary, #cart-page-count
     */
    window.EasyCart.UI.refreshCartHTML = async () => {
        const cartItemsContainer = document.querySelector('.cart-items');
        if (!cartItemsContainer) return; // Not on cart page

        try {
            const htmlResponse = await fetch(`${EasyCart.ajaxUrl}/cart/get-cart-html.php`);
            const htmlData = await htmlResponse.json();

            if (htmlData.success) {
                // Replace Cart Items
                cartItemsContainer.innerHTML = htmlData.cartHtml;

                // Replace Cart Summary
                const cartContainer = document.querySelector('.cart-container');
                const existingSummary = document.querySelector('.cart-summary');

                if (existingSummary) {
                    existingSummary.outerHTML = htmlData.summaryHtml;
                } else if (htmlData.summaryHtml && cartContainer) {
                    cartContainer.insertAdjacentHTML('beforeend', htmlData.summaryHtml);
                }

                // Update Page Header Count (Specific to Cart Page)
                const cartPageCount = document.getElementById('cart-page-count');
                const cartPageText = document.getElementById('cart-page-text');
                if (cartPageCount) cartPageCount.textContent = htmlData.totalItems;
                if (cartPageText) cartPageText.textContent = parseInt(htmlData.totalItems) === 1 ? 'item' : 'items';
            }
        } catch (err) {
            console.error('Error refreshing cart UI:', err);
        }
    };
})();
