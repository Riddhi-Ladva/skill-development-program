/**
 * Checkout Validation & Submission
 * 
 * Goal: Ensure contact and shipping details are present.
 * Payment is now simplified (COD/UPI) with no extra inputs.
 */
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.querySelector('.checkout-form');
    if (!checkoutForm) return;

    const placeOrderBtn = checkoutForm.querySelector('.place-order-button');

    // Inputs to track
    const emailInput = document.getElementById('email');
    const firstNameInput = document.getElementById('first-name');
    const lastNameInput = document.getElementById('last-name');
    const addressInput = document.getElementById('address-line1');
    const cityInput = document.getElementById('city');
    const zipInput = document.getElementById('zip');
    const phoneInput = document.getElementById('phone');

    const showError = (input, message) => {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        formGroup.classList.add('form-group--error');
        let errorDisplay = formGroup.querySelector('.error-message');
        if (!errorDisplay) {
            errorDisplay = document.createElement('span');
            errorDisplay.className = 'error-message';
            formGroup.appendChild(errorDisplay);
        }
        errorDisplay.textContent = message;
    };

    const clearError = (input) => {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        formGroup.classList.remove('form-group--error');
        const errorDisplay = formGroup.querySelector('.error-message');
        if (errorDisplay) errorDisplay.remove();
    };

    const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const validateAll = () => {
        let isValid = true;

        // Contact Info
        if (!isValidEmail(emailInput.value.trim())) {
            showError(emailInput, 'Valid email is required');
            isValid = false;
        } else {
            clearError(emailInput);
        }

        // Shipping Info
        const requiredFields = [
            { el: firstNameInput, msg: 'First name is required' },
            { el: lastNameInput, msg: 'Last name is required' },
            { el: addressInput, msg: 'Address is required' },
            { el: cityInput, msg: 'City is required' },
            { el: zipInput, msg: 'ZIP code is required' },
            { el: phoneInput, msg: 'Phone is required' }
        ];

        requiredFields.forEach(field => {
            if (!field.el.value.trim()) {
                showError(field.el, field.msg);
                isValid = false;
            } else {
                clearError(field.el);
            }
        });

        // Payment Info: No extra validation needed for radio buttons (one is always checked)

        // Billing Info (Conditional)
        const sameAsShipping = document.querySelector('input[name="same-as-shipping"]');
        if (sameAsShipping && !sameAsShipping.checked) {
            const billingAddress = document.getElementById('billing-address');
            const billingCity = document.getElementById('billing-city');
            const billingState = document.getElementById('billing-state');
            const billingZip = document.getElementById('billing-zip');

            const billingFields = [
                { el: billingAddress, msg: 'Billing address is required' },
                { el: billingCity, msg: 'Billing city is required' },
                { el: billingState, msg: 'Billing state is required' },
                { el: billingZip, msg: 'Billing ZIP is required' }
            ];

            billingFields.forEach(field => {
                if (!field.el.value.trim()) {
                    showError(field.el, field.msg);
                    isValid = false;
                } else {
                    clearError(field.el);
                }
            });
        }

        return isValid;
    };

    // Toggle Billing Form logic & Auto-Detection
    const sameAsShippingCheckbox = document.querySelector('input[name="same-as-shipping"]');
    const billingForm = document.querySelector('.billing-form');

    if (sameAsShippingCheckbox && billingForm) {
        // Initial state
        billingForm.hidden = sameAsShippingCheckbox.checked;

        sameAsShippingCheckbox.addEventListener('change', () => {
            billingForm.hidden = sameAsShippingCheckbox.checked;
        });

        // Auto-detection: Compare billing inputs with shipping inputs
        const shippingFields = {
            address: document.getElementById('address-line1'),
            city: document.getElementById('city'),
            state: document.getElementById('state'),
            zip: document.getElementById('zip')
        };
        const billingFields = {
            address: document.getElementById('billing-address'),
            city: document.getElementById('billing-city'),
            state: document.getElementById('billing-state'),
            zip: document.getElementById('billing-zip')
        };

        const checkAutoMatch = () => {
            if (sameAsShippingCheckbox.checked) return;

            const isMatch =
                billingFields.address.value.trim().toLowerCase() === shippingFields.address.value.trim().toLowerCase() &&
                billingFields.city.value.trim().toLowerCase() === shippingFields.city.value.trim().toLowerCase() &&
                billingFields.state.value === shippingFields.state.value &&
                billingFields.zip.value.trim() === shippingFields.zip.value.trim() &&
                billingFields.address.value.trim() !== '';

            if (isMatch) {
                sameAsShippingCheckbox.checked = true;
                sameAsShippingCheckbox.dispatchEvent(new Event('change'));

                // Show localized message
                const msg = document.createElement('div');
                msg.className = 'billing-match-message';
                msg.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #4CAF50; color: white; padding: 15px; border-radius: 4px; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); animation: fadeIn 0.5s;';
                msg.textContent = 'Billing address matched shipping address. Automatically selected "Same as shipping".';
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 4000);
            }
        };

        // Attach listeners
        if (billingFields.address) billingFields.address.addEventListener('input', checkAutoMatch);
        if (billingFields.city) billingFields.city.addEventListener('input', checkAutoMatch);
        if (billingFields.state) billingFields.state.addEventListener('change', checkAutoMatch);
        if (billingFields.zip) billingFields.zip.addEventListener('input', checkAutoMatch);
    }

    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', (e) => {
            if (!validateAll()) {
                e.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.form-group--error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Real AJAX order placement
                const submitBtn = e.target;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                // Get selected payment method
                const selectedPayment = document.querySelector('input[name="payment-method"]:checked');
                const paymentMethod = selectedPayment ? selectedPayment.value : 'cod';

                // Get billing toggle
                const sameAsShippingCheckbox = document.querySelector('input[name="same-as-shipping"]');
                const isSameAsShipping = sameAsShippingCheckbox ? sameAsShippingCheckbox.checked : true;

                // GATHER FORM DATA
                const orderData = {
                    contact: {
                        email: emailInput.value.trim()
                    },
                    shipping: {
                        first_name: firstNameInput.value.trim(),
                        last_name: lastNameInput.value.trim(),
                        address: addressInput.value.trim(),
                        city: cityInput.value.trim(),
                        zip: zipInput.value.trim(),
                        phone: phoneInput.value.trim(),
                        state: document.getElementById('state').value,
                        country: document.getElementById('country').value,
                        company: document.getElementById('company').value.trim()
                    },
                    billing: {
                        same_as_shipping: isSameAsShipping,
                        address: isSameAsShipping ? '' : document.getElementById('billing-address').value.trim(),
                        city: isSameAsShipping ? '' : document.getElementById('billing-city').value.trim(),
                        state: isSameAsShipping ? '' : document.getElementById('billing-state').value.trim(),
                        zip: isSameAsShipping ? '' : document.getElementById('billing-zip').value.trim(),
                        country: 'US' // Default as per form limitation
                    },
                    payment: {
                        method: paymentMethod
                    }
                };

                fetch(window.EasyCart.baseUrl + '/ajax/checkout/place-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert('Error placing order: ' + data.error);
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Place Order';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Place Order';
                    });
            }
        });
    }

    // Live clearing of errors
    [emailInput, firstNameInput, lastNameInput, addressInput, cityInput, zipInput, phoneInput].forEach(input => {
        if (!input) return;
        input.addEventListener('input', () => {
            if (input.value.trim()) clearError(input);
        });
    });
});
