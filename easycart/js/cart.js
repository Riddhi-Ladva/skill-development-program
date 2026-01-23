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
    const SHIPPING_THRESHOLD = 50;
    const SHIPPING_COST = 9.99;

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
        const shipping = (subtotal > SHIPPING_THRESHOLD || subtotal === 0) ? 0 : SHIPPING_COST;
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
            return;
        }

        // Remove Item Button
        if (btn.classList.contains('remove-item')) {
            const item = btn.closest('.cart-item');
            if (item) {
                item.remove();
                updateCartSummary();
            }
        }
    });
});
