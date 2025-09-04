// assets/js/signup.js

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializeSignupForm();
    initializePasswordStrength();
    initializePasswordMatch();
    initializeEmailValidation();
});

// Initialize signup form
function initializeSignupForm() {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', handleSignupSubmit);

        // Real-time validation
        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                validateSignupInput(input);
            });

            input.addEventListener('input', () => {
                commonFunctions.removeError(input);

                // Special handling for password confirmation
                if (input.id === 'confirm_password' || input.id === 'password') {
                    checkPasswordMatch();
                }
            });
        });
    }
}

// Handle form submission
function handleSignupSubmit(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    // Validate all inputs
    inputs.forEach(input => {
        if (!validateSignupInput(input)) {
            isValid = false;
        }
    });

    // Check password match
    if (!checkPasswordMatch()) {
        isValid = false;
    }

    // Check terms acceptance
    const termsCheckbox = form.querySelector('input[name="terms"]');
    if (termsCheckbox && !termsCheckbox.checked) {
        alert('Please accept the Terms and Privacy Policy to continue.');
        isValid = false;
    }

    if (isValid && submitBtn) {
        // Add loading state
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
    } else {
        e.preventDefault();
    }
}

// Enhanced validation for signup
function validateSignupInput(input) {
    let isValid = commonFunctions.validateInput(input);

    // Additional validation for email
    if (input.type === 'email' && input.value) {
        if (!input.value.endsWith('@northsouth.edu')) {
            commonFunctions.showError(input, 'Please use your NSU email address (@northsouth.edu)');
            isValid = false;
        }
    }

    // Phone number validation
    if (input.type === 'tel' && input.value) {
        const phoneRegex = /^(\+?880)?1[3-9]\d{8}$/;
        if (!phoneRegex.test(input.value.replace(/\s/g, ''))) {
            commonFunctions.showError(input, 'Please enter a valid Bangladesh phone number');
            isValid = false;
        }
    }

    return isValid;
}

// Password strength checker
function initializePasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    // Create strength indicator
    const strengthContainer = document.createElement('div');
    strengthContainer.innerHTML = `
        <div class="password-strength" id="passwordStrength">
            <div class="password-strength-bar"></div>
        </div>
        <div class="password-strength-text" id="strengthText"></div>
    `;
    passwordInput.parentNode.appendChild(strengthContainer);

    // Check strength on input
    passwordInput.addEventListener('input', function () {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        updateStrengthIndicator(strength);
    });
}

// Check password strength
function checkPasswordStrength(password) {
    let strength = 0;

    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
}

// Update strength indicator
function updateStrengthIndicator(strength) {
    const indicator = document.getElementById('passwordStrength');
    const text = document.getElementById('strengthText');

    if (!indicator || !text) return;

    indicator.className = `password-strength ${strength}`;

    switch (strength) {
        case 'weak':
            text.textContent = 'Weak password';
            text.style.color = 'var(--danger-color)';
            break;
        case 'medium':
            text.textContent = 'Medium strength';
            text.style.color = 'var(--warning-color)';
            break;
        case 'strong':
            text.textContent = 'Strong password';
            text.style.color = 'var(--success-color)';
            break;
    }
}

// Initialize password match checking
function initializePasswordMatch() {
    const confirmInput = document.getElementById('confirm_password');
    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
}

// Check if passwords match
function checkPasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (!password || !confirmPassword || !confirmPassword.value) return true;

    commonFunctions.removeError(confirmPassword);

    if (password.value !== confirmPassword.value) {
        commonFunctions.showError(confirmPassword, 'Passwords do not match');
        return false;
    }

    return true;
}

// Email validation with domain check
function initializeEmailValidation() {
    const emailInput = document.getElementById('email');
    if (!emailInput) return;

    // Add helper text on focus
    emailInput.addEventListener('focus', function () {
        const existingHelper = this.parentNode.querySelector('.email-helper');
        if (!existingHelper) {
            const helper = document.createElement('div');
            helper.className = 'form-text email-helper';
            helper.textContent = 'Example: mubin.islam@northsouth.edu';
            helper.style.color = 'var(--info-color)';
            this.parentNode.appendChild(helper);
        }
    });

    // Remove helper on blur if valid
    emailInput.addEventListener('blur', function () {
        const helper = this.parentNode.querySelector('.email-helper');
        if (helper && this.value.endsWith('@northsouth.edu')) {
            helper.remove();
        }
    });
}

// Animate form elements on load
window.addEventListener('load', function () {
    const formGroups = document.querySelectorAll('.form-group, .form-row');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';

        setTimeout(() => {
            group.style.transition = 'all 0.5s ease';
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Auto-format phone number
document.addEventListener('input', function (e) {
    if (e.target.type === 'tel') {
        let value = e.target.value.replace(/\D/g, '');

        if (value.startsWith('880')) {
            value = value.substring(3);
        }

        if (value.length > 0) {
            if (value.length <= 4) {
                value = value;
            } else if (value.length <= 8) {
                value = value.slice(0, 4) + ' ' + value.slice(4);
            } else {
                value = value.slice(0, 4) + ' ' + value.slice(4, 8) + ' ' + value.slice(8, 11);
            }

            if (!value.startsWith('0')) {
                value = '+880 ' + value;
            }
        }

        e.target.value = value;
    }
});