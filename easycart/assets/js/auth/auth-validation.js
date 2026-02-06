/**
 * EasyCart - Authentication Validation
 * 
 * Responsibility: Handles client-side validation for login and signup forms.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. SELECTORS
    const loginForm = document.querySelector('form[action*="login.php"], form[action*="orders.php"]');
    const signupForm = document.querySelector('form[action*="signup.php"]');

    // 2. SHARED HELPERS
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
        if (errorDisplay) {
            errorDisplay.remove();
        }
    };

    const isValidEmail = (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };

    // 3. LOGIN VALIDATION
    if (loginForm && !signupForm) {
        const emailInput = loginForm.querySelector('#email');
        const passwordInput = loginForm.querySelector('#password');

        loginForm.addEventListener('submit', (e) => {
            let isValid = true;
            if (!emailInput.value.trim() || !isValidEmail(emailInput.value.trim())) {
                showError(emailInput, 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError(emailInput);
            }

            if (passwordInput.value.length < 8) {
                showError(passwordInput, 'Password must be at least 8 characters');
                isValid = false;
            } else {
                clearError(passwordInput);
            }

            if (!isValid) e.preventDefault();
        });
    }

    // 4. SIGNUP VALIDATION
    if (signupForm) {
        const firstName = signupForm.querySelector('#first-name');
        const lastName = signupForm.querySelector('#last-name');
        const email = signupForm.querySelector('#email');
        const password = signupForm.querySelector('#password');
        const confirm = signupForm.querySelector('#confirm-password');
        const terms = signupForm.querySelector('input[name="terms"]');

        signupForm.addEventListener('submit', (e) => {
            let isValid = true;

            if (firstName.value.trim().length < 2) {
                showError(firstName, 'First name is required');
                isValid = false;
            } else clearError(firstName);

            if (lastName.value.trim().length < 2) {
                showError(lastName, 'Last name is required');
                isValid = false;
            } else clearError(lastName);

            if (!isValidEmail(email.value.trim())) {
                showError(email, 'Valid email is required');
                isValid = false;
            } else clearError(email);

            if (password.value.length < 8) {
                showError(password, 'Min 8 characters');
                isValid = false;
            } else clearError(password);

            if (confirm.value !== password.value) {
                showError(confirm, 'Passwords do not match');
                isValid = false;
            } else clearError(confirm);

            if (!isValid) e.preventDefault();
        });
    }
});
