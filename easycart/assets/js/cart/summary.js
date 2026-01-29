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
    window.EasyCart.UI.updateTotalsFromResponse = (totals, shippingOptions) => {
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
    };

    /**
     * Optimistic UI update: Calculates subtotal based on current DOM state.
     */
    window.EasyCart.UI.refreshSummaryOptimistically = () => {
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
            if (container) container.innerHTML = '<p>Your cart is empty. <a href="products.php">Start shopping!</a></p>';
            document.querySelector('.cart-summary')?.remove();
        }
    };
})();
