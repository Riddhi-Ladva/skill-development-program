/**
 * MY STUDY NOTES: Checkout Validation
 * 
 * Goal: Ensure all shipping and payment details are perfect 
 * before the user places their order.
 * 
 * Logic:
 * 1. validateEmail: Standard regex check.
 * 2. validateCard: Simple length checks for number, expiry, and cvv.
 * 3. showError / clearError: Consistent UI feedback.
 */
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.querySelector('.checkout-form');
    if (!checkoutForm) return;

    // We can't use a single form.submit because the checkout has 
    // multiple <form> segments in the HTML structure.
    // Instead, we'll target the "Place Order" button directly.
    const placeOrderBtn = checkoutForm.querySelector('.place-order-button');

    // Inputs to track
    const emailInput = document.getElementById('email');
    const firstNameInput = document.getElementById('first-name');
    const lastNameInput = document.getElementById('last-name');
    const addressInput = document.getElementById('address-line1');
    const cityInput = document.getElementById('city');
    const zipInput = document.getElementById('zip');
    const phoneInput = document.getElementById('phone');

    const cardNumberInput = document.getElementById('card-number');
    const cardNameInput = document.getElementById('cardholder-name');
    const expiryInput = document.getElementById('expiry-date');
    const cvvInput = document.getElementById('cvv');

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

        // Payment Info (Simplified validation for Phase-3)
        if (cardNumberInput.value.replace(/\s/g, '').length < 13) {
            showError(cardNumberInput, 'Valid card number required');
            isValid = false;
        } else {
            clearError(cardNumberInput);
        }

        if (!cardNameInput.value.trim()) {
            showError(cardNameInput, 'Cardholder name is required');
            isValid = false;
        } else {
            clearError(cardNameInput);
        }

        if (!/^\d{2}\/\d{2}$/.test(expiryInput.value)) {
            showError(expiryInput, 'Use MM/YY format');
            isValid = false;
        } else {
            clearError(expiryInput);
        }

        if (cvvInput.value.length < 3) {
            showError(cvvInput, 'Valid CVV required');
            isValid = false;
        } else {
            clearError(cvvInput);
        }

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
                // Simulate success
                e.preventDefault();
                alert('Order placed successfully! (Simulation)');
                window.location.href = 'orders.php';
            }
        });
    }

    // Live clearing of errors
    [emailInput, firstNameInput, lastNameInput, addressInput, cityInput, zipInput, phoneInput, cardNumberInput, cardNameInput, expiryInput, cvvInput].forEach(input => {
        input.addEventListener('input', () => {
            if (input.value.trim()) clearError(input);
        });
    });
});
