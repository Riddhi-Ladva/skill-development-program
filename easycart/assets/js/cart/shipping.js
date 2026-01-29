/**
 * EasyCart - Cart Shipping Component
 * 
 * Responsibility: Manages shipping method selection and updates totals.
 */

document.addEventListener('DOMContentLoaded', () => {
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    if (shippingRadios.length === 0) return;

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', async (e) => {
            const method = e.target.value;
            try {
                const response = await fetch(`${EasyCart.ajaxUrl}/shipping/update.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: method })
                });

                const data = await response.json();
                if (data.success) {
                    window.EasyCart.UI.updateTotalsFromResponse(data.totals);
                }
            } catch (error) {
                console.error('Error updating shipping:', error);
            }
        });
    });
});
