// assets/js/login.js

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializeLoginForm();
    initializePasswordToggle();
    checkRememberMe();
});

// Initialize login form
function initializeLoginForm() {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', handleLoginSubmit);

        // Real-time validation
        const inputs = form.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                commonFunctions.validateInput(input);
            });

            input.addEventListener('input', () => {
                commonFunctions.removeError(input);
            });
        });
    }
}

// Handle form submission
function handleLoginSubmit(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;

    // Validate all inputs
    inputs.forEach(input => {
        if (!commonFunctions.validateInput(input)) {
            isValid = false;
            input.classList.add('shake');
            setTimeout(() => input.classList.remove('shake'), 500);
        }
    });

    if (isValid && submitBtn) {
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
    } else {
        e.preventDefault();
    }
}

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.password-toggle');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }
}

// Check remember me cookie
function checkRememberMe() {
    const rememberToken = commonFunctions.getCookie('remember_token');
    if (rememberToken) {
        const rememberCheckbox = document.querySelector('input[name="remember"]');
        if (rememberCheckbox) {
            rememberCheckbox.checked = true;
        }
    }
}

// Add enter key support for form submission
document.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        const form = document.querySelector('form');
        if (form && !e.target.matches('textarea')) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                form.requestSubmit();
            }
        }
    }
});

// Animate form elements on load
window.addEventListener('load', function () {
    const formGroups = document.querySelectorAll('.form-group');
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

// Export toggle function
window.togglePassword = togglePassword;