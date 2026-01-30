/**
 * EasyCart - Cart Shipping Component
 * 
 * Responsibility: Manages shipping method selection and updates totals.
 */

document.addEventListener('DOMContentLoaded', () => {
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    if (shippingRadios.length === 0) return; // Exit if not on a page with shipping options

    // Shared update function
    const updateShipping = async (method) => {
        try {
            const response = await fetch(`${EasyCart.ajaxUrl}/shipping/update.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: method })
            });

            const data = await response.json();
            if (data.success) {
                if (window.EasyCart && window.EasyCart.UI) {
                    window.EasyCart.UI.updateTotalsFromResponse(data.totals, data.shippingOptions, data.cartItems, data.shippingConstraints);
                }
            }
        } catch (error) {
            console.error('Error updating shipping:', error);
        }
    };

    // Attach listeners
    shippingRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.checked) {
                updateShipping(e.target.value);
            }
        });
    });

    /**
     * Validates and auto-corrects the selected shipping method.
     * Ensures we never stay selected on a disabled option.
     */
    window.EasyCart = window.EasyCart || {};
    window.EasyCart.Shipping = window.EasyCart.Shipping || {};

    window.EasyCart.Shipping.validateRules = () => {
        const currentChecked = document.querySelector('input[name="shipping"]:checked');

        // If current selection is valid, do nothing
        if (currentChecked && !currentChecked.disabled) {
            return;
        }

        // If invalid or none selected, find the first valid option
        const firstValid = document.querySelector('input[name="shipping"]:not(:disabled)');

        if (firstValid) {
            // Uncheck invalid manually just in case to clear visual state immediately
            if (currentChecked) currentChecked.checked = false;

            // Check new valid option
            firstValid.checked = true;

            // Trigger update immediately so server & UI sync up
            updateShipping(firstValid.value);
        }
    };

    // Run validation on load to fix any server-side mismatches (e.g. session says Standard, but item is Freight)
    window.EasyCart.Shipping.validateRules();
});
