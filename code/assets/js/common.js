// assets/js/common.js

// DOM Ready
document.addEventListener('DOMContentLoaded', function () {
    // Initialize all common functionality
    initializeAlerts();
    initializeModals();
    initializeForms();
    initializeTooltips();
});

// Alert System
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            });
        }

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
}

// Modal System
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => modal.style.display = 'none');
        }
    });
}

// Show modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Form Validation
function initializeForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormValidation);
    });
}

function handleFormValidation(e) {
    const form = e.target;
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
}

function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    let isValid = true;
    let errorMessage = '';

    // Remove previous error
    removeError(input);

    // Check if empty
    if (!value && input.hasAttribute('required')) {
        errorMessage = 'This field is required';
        isValid = false;
    }
    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email';
            isValid = false;
        }
    }
    // Password validation
    else if (type === 'password' && value && input.dataset.minLength) {
        if (value.length < parseInt(input.dataset.minLength)) {
            errorMessage = `Password must be at least ${input.dataset.minLength} characters`;
            isValid = false;
        }
    }
    // Number validation
    else if (type === 'number' && value) {
        const min = parseFloat(input.min);
        const max = parseFloat(input.max);
        const numValue = parseFloat(value);

        if (min && numValue < min) {
            errorMessage = `Value must be at least ${min}`;
            isValid = false;
        } else if (max && numValue > max) {
            errorMessage = `Value must not exceed ${max}`;
            isValid = false;
        }
    }

    if (!isValid) {
        showError(input, errorMessage);
    }

    return isValid;
}

function showError(input, message) {
    input.classList.add('is-invalid');
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.textContent = message;
    input.parentNode.appendChild(feedback);
}

function removeError(input) {
    input.classList.remove('is-invalid');
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.remove();
    }
}

// AJAX Request Helper
function ajaxRequest(url, method = 'GET', data = null, onSuccess = null, onError = null) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);

    if (method === 'POST') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    xhr.onload = function () {
        if (xhr.status === 200) {
            if (onSuccess) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    onSuccess(response);
                } catch (e) {
                    onSuccess(xhr.responseText);
                }
            }
        } else {
            if (onError) {
                onError(xhr.statusText);
            }
        }
    };

    xhr.onerror = function () {
        if (onError) {
            onError('Network error');
        }
    };

    if (method === 'POST' && data) {
        const params = new URLSearchParams(data).toString();
        xhr.send(params);
    } else {
        xhr.send();
    }
}

// Loading Spinner
function showLoading(element) {
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    element.appendChild(spinner);
}

function hideLoading(element) {
    const spinner = element.querySelector('.spinner');
    if (spinner) {
        spinner.remove();
    }
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format date
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

// Tooltip initialization
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const text = e.target.dataset.tooltip;
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;

    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Smooth scroll
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Cookie functions
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
}

function getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Export functions for use in other scripts
window.commonFunctions = {
    showModal,
    closeModal,
    ajaxRequest,
    showLoading,
    hideLoading,
    debounce,
    formatDate,
    smoothScroll,
    setCookie,
    getCookie,
    validateInput,
    showError,
    removeError
};