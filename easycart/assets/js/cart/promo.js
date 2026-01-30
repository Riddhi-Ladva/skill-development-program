/**
 * EasyCart - Promo Code Component
 * 
 * Responsibility: Handles checking, applying, and removing promo codes.
 * Interacts with: summary.js (to update totals)
 */

document.addEventListener('DOMContentLoaded', () => {
    const promoForm = document.getElementById('promo-form');
    if (!promoForm) return;

    const input = document.getElementById('checkout-promo');
    const applyBtn = document.getElementById('apply-promo-btn');
    const removeBtn = document.getElementById('remove-promo-btn');
    const messageEl = document.getElementById('promo-message');

    const updatePromoUI = (isActive, code = '') => {
        if (isActive) {
            input.value = code;
            input.disabled = true;
            applyBtn.style.display = 'none';
            removeBtn.style.display = 'inline-block';
            messageEl.textContent = 'Promo code applied!';
            messageEl.className = 'message success';
        } else {
            input.value = '';
            input.disabled = false;
            applyBtn.style.display = 'inline-block';
            removeBtn.style.display = 'none';
            messageEl.textContent = '';
            messageEl.className = 'message';
        }
    };

    applyBtn.addEventListener('click', async () => {
        const code = input.value.trim();
        if (!code) {
            messageEl.textContent = 'Please enter a code';
            messageEl.className = 'message error';
            return;
        }

        // CONFIRMATION DIALOG (Business Rule)
        if (!confirm('Are you sure you want to apply this promo code? This will replace any quantity-based discounts.')) {
            return;
        }

        try {
            const response = await fetch(`${EasyCart.ajaxUrl}/cart/apply-promo.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            });

            const data = await response.json();

            if (data.success) {
                updatePromoUI(true, data.appliedCode);
                window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions, data.cartItems, data.shippingConstraints);
            } else {
                messageEl.textContent = data.message;
                messageEl.className = 'message error';
            }
        } catch (error) {
            console.error('Error applying promo:', error);
            messageEl.textContent = 'Connection error. Please try again.';
            messageEl.className = 'message error';
        }
    });

    removeBtn.addEventListener('click', async () => {
        // CONFIRMATION DIALOG (Business Rule)
        if (!confirm('Are you sure you want to remove this promo code? Quantity-based discounts will be restored.')) {
            return;
        }

        try {
            const response = await fetch(`${EasyCart.ajaxUrl}/cart/remove-promo.php`, { method: 'POST' });
            const data = await response.json();

            if (data.success) {
                updatePromoUI(false);
                window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions, data.cartItems, data.shippingConstraints);
            }
        } catch (error) {
            console.error('Error removing promo:', error);
        }
    });
});
