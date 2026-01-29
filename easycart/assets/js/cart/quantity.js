/**
 * EasyCart - Cart Quantity Component
 * 
 * Responsibility: Manages [+] and [-] button clicks and manual quantity changes.
 */

document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.querySelector('.cart-items');
    if (!cartContainer) return;

    const updateSessionQuantity = async (productId, quantity) => {
        try {
            const response = await fetch(`${EasyCart.ajaxUrl}/cart/update-qty.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            });

            const data = await response.json();
            if (data.success && data.totals) {
                window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions);
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
        }
    };

    const handleRemoveItem = (btn) => {
        const item = btn.closest('.cart-item');
        const productId = item.querySelector('input[name="quantity"]').id.replace('quantity-', '');

        item.style.opacity = '0.5';

        fetch(`${EasyCart.ajaxUrl}/cart/remove.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                    window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions);
                    window.EasyCart.UI.refreshSummaryOptimistically();
                }
            });
    };

    cartContainer.addEventListener('click', (e) => {
        const btn = e.target;

        if (btn.classList.contains('qty-btn')) {
            const input = btn.closest('.quantity-controls').querySelector('input');
            let val = parseInt(input.value);
            if (btn.classList.contains('plus') && val < 10) val++;
            else if (btn.classList.contains('minus') && val > 1) val--;

            if (input.value != val) {
                input.value = val;
                window.EasyCart.UI.refreshSummaryOptimistically();
                updateSessionQuantity(input.id.replace('quantity-', ''), val);
            }
        }

        if (btn.classList.contains('remove-item')) {
            handleRemoveItem(btn);
        }
    });

    cartContainer.addEventListener('change', (e) => {
        if (e.target.name === 'quantity') {
            window.EasyCart.UI.refreshSummaryOptimistically();
            updateSessionQuantity(e.target.id.replace('quantity-', ''), parseInt(e.target.value));
        }
    });
});
