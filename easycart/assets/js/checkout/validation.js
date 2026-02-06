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

        return isValid;
    };

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
