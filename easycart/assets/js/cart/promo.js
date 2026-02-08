/**
 * EasyCart - Promo Code Component
 * 
 * Responsibility: Handles checking, applying, and removing promo codes.
 * Interacts with: summary.js (to update totals)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Helper to find elements dynamically since they might be replaced
    const getElements = () => ({
        input: document.getElementById('checkout-promo'),
        applyBtn: document.getElementById('apply-promo-btn'),
        removeBtn: document.getElementById('remove-promo-btn'),
        messageEl: document.getElementById('promo-message')
    });

    const updatePromoUI = (isActive, code = '') => {
        const { input, applyBtn, removeBtn, messageEl } = getElements();
        if (!input || !messageEl) return;

        if (isActive) {
            input.value = code;
            input.disabled = true;
            if (applyBtn) applyBtn.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'inline-block';
            messageEl.textContent = 'Promo code applied!';
            messageEl.className = 'message success';
        } else {
            input.value = '';
            input.disabled = false;
            if (applyBtn) applyBtn.style.display = 'inline-block';
            if (removeBtn) removeBtn.style.display = 'none';
            messageEl.textContent = '';
            messageEl.className = 'message';
        }
    };

    // Event Delegation for Promo Actions
    document.addEventListener('click', async (e) => {
        const target = e.target;

        // Apply Promo
        if (target && target.id === 'apply-promo-btn') {
            e.preventDefault();
            const { input, messageEl } = getElements();
            if (!input) return;

            const code = input.value.trim();
            if (!code) {
                if (messageEl) {
                    messageEl.textContent = 'Please enter a code';
                    messageEl.className = 'message error';
                }
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
                    if (window.EasyCart && window.EasyCart.UI) {
                        window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions, data.cartItems, data.shippingConstraints);
                    }
                } else {
                    if (messageEl) {
                        messageEl.textContent = data.message;
                        messageEl.className = 'message error';
                    }
                }
            } catch (error) {
                console.error('Error applying promo:', error);
                if (messageEl) {
                    messageEl.textContent = 'Connection error. Please try again.';
                    messageEl.className = 'message error';
                }
            }
        }

        // Remove Promo
        if (target && target.id === 'remove-promo-btn') {
            e.preventDefault();
            // CONFIRMATION DIALOG (Business Rule)
            if (!confirm('Are you sure you want to remove this promo code? Quantity-based discounts will be restored.')) {
                return;
            }

            try {
                const response = await fetch(`${EasyCart.ajaxUrl}/cart/remove-promo.php`, { method: 'POST' });
                const data = await response.json();

                if (data.success) {
                    updatePromoUI(false);
                    if (window.EasyCart && window.EasyCart.UI) {
                        window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions, data.cartItems, data.shippingConstraints);
                    }
                }
            } catch (error) {
                console.error('Error removing promo:', error);
            }
        }
    });
});
