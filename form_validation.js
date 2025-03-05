// form_validation.js

document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            let isValid = true;

            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    showError(field, 'This field is required.');
                    isValid = false;
                } else {
                    clearError(field);
                }
            });

            // Validate email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(function (emailField) {
                if (emailField.value && !validateEmail(emailField.value)) {
                    showError(emailField, 'Please enter a valid email address.');
                    isValid = false;
                } else {
                    clearError(emailField);
                }
            });

            // Validate password fields
            const passwordFields = form.querySelectorAll('input[type="password"]');
            passwordFields.forEach(function (passwordField) {
                if (passwordField.value.length < 6) {
                    showError(passwordField, 'Password must be at least 6 characters long.');
                    isValid = false;
                } else {
                    clearError(passwordField);
                }
            });

            // Validate date fields
            const dateFields = form.querySelectorAll('input[type="date"]');
            dateFields.forEach(function (dateField) {
                if (!dateField.value) {
                    showError(dateField, 'Please enter a valid date.');
                    isValid = false;
                } else {
                    clearError(dateField);
                }
            });

            // Prevent form submission if validation fails
            if (!isValid) {
                event.preventDefault();
            }
        });
    });

    function showError(input, message) {
        let errorElement = input.parentElement.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.classList.add('error-message');
            input.parentElement.appendChild(errorElement);
        }
        errorElement.textContent = message;
        input.classList.add('input-error');
    }

    function clearError(input) {
        const errorElement = input.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
        input.classList.remove('input-error');
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
