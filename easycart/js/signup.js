/**
 * Signup Form Validation
 * Handles client-side validation for the signup form.
 */
document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.querySelector('form[action="login.php"]');
    const firstNameInput = document.getElementById('first-name');
    const lastNameInput = document.getElementById('last-name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const termsCheckbox = document.querySelector('input[name="terms"]');

    if (!signupForm) return;

    /**
     * Shows error message for a specific input field
     * @param {HTMLElement} element 
     * @param {string} message 
     */
    const showError = (element, message) => {
        const formGroup = element.closest('.form-group');
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
     * @param {HTMLElement} element 
     */
    const clearError = (element) => {
        const formGroup = element.closest('.form-group');
        if (!formGroup) return;

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

        // First Name validation
        if (!firstNameInput.value.trim()) {
            showError(firstNameInput, 'First name is required');
            isValid = false;
        } else if (firstNameInput.value.trim().length < 2) {
            showError(firstNameInput, 'First name must be at least 2 characters');
            isValid = false;
        } else {
            clearError(firstNameInput);
        }

        // Last Name validation
        if (!lastNameInput.value.trim()) {
            showError(lastNameInput, 'Last name is required');
            isValid = false;
        } else if (lastNameInput.value.trim().length < 2) {
            showError(lastNameInput, 'Last name must be at least 2 characters');
            isValid = false;
        } else {
            clearError(lastNameInput);
        }

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

        // Confirm Password validation
        if (!confirmPasswordInput.value.trim()) {
            showError(confirmPasswordInput, 'Please confirm your password');
            isValid = false;
        } else if (confirmPasswordInput.value !== passwordInput.value) {
            showError(confirmPasswordInput, 'Passwords do not match');
            isValid = false;
        } else {
            clearError(confirmPasswordInput);
        }

        // Terms validation
        if (!termsCheckbox.checked) {
            showError(termsCheckbox, 'You must agree to the Terms of Service');
            isValid = false;
        } else {
            clearError(termsCheckbox);
        }

        return isValid;
    };

    // Form submission event listener
    signupForm.addEventListener('submit', (event) => {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    // Real-time clearing of errors as user types
    const inputs = [firstNameInput, lastNameInput, emailInput, passwordInput, confirmPasswordInput];
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            // Check specific conditions to clear errors early
            if (input === firstNameInput || input === lastNameInput) {
                if (input.value.trim().length >= 2) clearError(input);
            } else if (input === emailInput) {
                if (isValidEmail(input.value.trim())) clearError(input);
            } else if (input === passwordInput) {
                if (input.value.length >= 6) {
                    clearError(input);
                    // Also clear check if confirm password matches now
                    if (confirmPasswordInput.value === input.value) clearError(confirmPasswordInput);
                }
            } else if (input === confirmPasswordInput) {
                if (input.value === passwordInput.value) clearError(input);
            }
        });
    });

    termsCheckbox.addEventListener('change', () => {
        if (termsCheckbox.checked) clearError(termsCheckbox);
    });
});
