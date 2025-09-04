// Admin Signup JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Form validation
    const form = document.getElementById('adminSignupForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const emailInput = document.getElementById('email');
    const passwordStrength = document.getElementById('passwordStrength');

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;

        // Length check
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;

        // Character variety checks
        if (/[a-z]/.test(password)) strength += 12.5;
        if (/[A-Z]/.test(password)) strength += 12.5;
        if (/[0-9]/.test(password)) strength += 12.5;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 12.5;

        return Math.min(strength, 100);
    }

    // Update password strength indicator
    passwordInput.addEventListener('input', function () {
        const strength = checkPasswordStrength(this.value);
        passwordStrength.style.setProperty('--strength', strength + '%');

        // Add strength message
        let message = '';
        if (strength < 30) message = 'Weak';
        else if (strength < 60) message = 'Fair';
        else if (strength < 80) message = 'Good';
        else message = 'Strong';

        passwordStrength.setAttribute('data-strength', message);
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Email validation
    emailInput.addEventListener('blur', function () {
        const email = this.value;
        const nsuPattern = /^[a-zA-Z0-9._%+-]+@northsouth\.edu$/;

        if (email && !nsuPattern.test(email)) {
            this.setCustomValidity('Please use a valid NSU email address');
            this.classList.add('error');
        } else {
            this.setCustomValidity('');
            this.classList.remove('error');
        }
    });

    // Form submission validation
    form.addEventListener('submit', function (e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Check password match
        if (password !== confirmPassword) {
            e.preventDefault();
            confirmPasswordInput.setCustomValidity('Passwords do not match');
            confirmPasswordInput.classList.add('error');
            confirmPasswordInput.focus();

            // Show error message
            showError('Passwords do not match!');
            return false;
        }

        // Check password strength
        if (password.length < 8) {
            e.preventDefault();
            passwordInput.setCustomValidity('Password must be at least 8 characters long');
            passwordInput.classList.add('error');
            passwordInput.focus();

            showError('Password must be at least 8 characters long!');
            return false;
        }

        // Clear any custom validity
        confirmPasswordInput.setCustomValidity('');
        passwordInput.setCustomValidity('');
    });

    // Real-time password match validation
    confirmPasswordInput.addEventListener('input', function () {
        if (this.value !== passwordInput.value) {
            this.setCustomValidity('Passwords do not match');
            this.classList.add('error');
        } else {
            this.setCustomValidity('');
            this.classList.remove('error');
        }
    });

    // Show error message function
    function showError(message) {
        // Remove existing alerts
        document.querySelectorAll('.alert').forEach(alert => alert.remove());

        // Create new error alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            ${message}
        `;

        // Insert after header
        const header = document.querySelector('.signup-header');
        header.parentNode.insertBefore(alertDiv, header.nextSibling);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Add input animations
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // Secret key field - show/hide functionality
    const secretKeyInput = document.getElementById('secret_key');
    const secretKeyToggle = document.createElement('button');
    secretKeyToggle.type = 'button';
    secretKeyToggle.className = 'toggle-password';
    secretKeyToggle.setAttribute('data-target', 'secret_key');
    secretKeyToggle.innerHTML = '<i class="fas fa-eye"></i>';

    // Wrap secret key input
    const secretKeyWrapper = document.createElement('div');
    secretKeyWrapper.className = 'password-input';
    secretKeyInput.parentNode.insertBefore(secretKeyWrapper, secretKeyInput);
    secretKeyWrapper.appendChild(secretKeyInput);
    secretKeyWrapper.appendChild(secretKeyToggle);

    // Add event listener for secret key toggle
    secretKeyToggle.addEventListener('click', function () {
        const icon = this.querySelector('i');

        if (secretKeyInput.type === 'password') {
            secretKeyInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            secretKeyInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});