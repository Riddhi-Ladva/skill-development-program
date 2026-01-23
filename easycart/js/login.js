/**
 * Login Form Validation
 * Handles client-side validation for the login form.
 */
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('form[action="orders.php"]');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (!loginForm) return;

    /**
     * Shows error message for a specific input field
     * @param {HTMLInputElement} input 
     * @param {string} message 
     */
    const showError = (input, message) => {
        const formGroup = input.closest('.form-group');
        formGroup.classList.add('form-group--error');
        
        let errorDisplay = formGroup.querySelector('.error-message');
        if (!errorDisplay) {
            errorDisplay = document.createElement('span');
            errorDisplay.className = 'error-message';
            formGroup.appendChild(errorDisplay);
        }
        errorDisplay.textContent = message;
    };

    /**
     * Removes error message for a specific input field
     * @param {HTMLInputElement} input 
     */
    const clearError = (input) => {
        const formGroup = input.closest('.form-group');
        formGroup.classList.remove('form-group--error');
        const errorDisplay = formGroup.querySelector('.error-message');
        if (errorDisplay) {
            errorDisplay.remove();
        }
    };

    /**
     * Validates an email address
     * @param {string} email 
     * @returns {boolean}
     */
    const isValidEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };

    /**
     * Handles the form validation logic
     * @returns {boolean} True if all fields are valid
     */
    const validateForm = () => {
        let isValid = true;

        // Email validation
        if (!emailInput.value.trim()) {
            showError(emailInput, 'Email address is required');
            isValid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            showError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearError(emailInput);
        }

        // Password validation
        if (!passwordInput.value.trim()) {
            showError(passwordInput, 'Password is required');
            isValid = false;
        } else if (passwordInput.value.length < 6) {
            showError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearError(passwordInput);
        }

        return isValid;
    };

    // Form submission event listener
    loginForm.addEventListener('submit', (event) => {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    // Real-time clearing of errors as user types
    emailInput.addEventListener('input', () => {
        if (emailInput.value.trim() && isValidEmail(emailInput.value.trim())) {
            clearError(emailInput);
        }
    });

    passwordInput.addEventListener('input', () => {
        if (passwordInput.value.trim() && passwordInput.value.length >= 6) {
            clearError(passwordInput);
        }
    });
});
