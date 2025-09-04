// assets/js/faculty-profile.js

document.addEventListener('DOMContentLoaded', function () {
    initializeProfile();
    initializeAvatarUpload();
    initializeFormValidation();
    initializeCharCounters();
    initializeTooltips();
});

// Initialize profile functionality
function initializeProfile() {
    // Smooth scroll for internal links
    const internalLinks = document.querySelectorAll('a[href^="#"]');
    internalLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Add animation to sections on scroll
    const sections = document.querySelectorAll('.profile-section, .sidebar-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(section);
    });
}

// Toggle between view and edit mode
function toggleEditMode() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const editBtn = document.querySelector('.edit-profile-btn');

    if (viewMode.style.display === 'none') {
        // Switch to view mode
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
        editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        // Switch to edit mode
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
        editBtn.innerHTML = '<i class="fas fa-eye"></i> View Profile';

        // Focus on first input
        const firstInput = editMode.querySelector('input[type="text"]');
        if (firstInput) {
            firstInput.focus();
        }
    }
}

// Initialize avatar upload
function initializeAvatarUpload() {
    const avatarInput = document.getElementById('avatarInput');
    const currentAvatar = document.getElementById('currentAvatar');
    const profileImageInput = document.getElementById('profile_image');

    // Handle avatar edit button click
    avatarInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            if (validateImage(file)) {
                previewImage(file, currentAvatar);

                // Also update the form input if in edit mode
                if (profileImageInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    profileImageInput.files = dataTransfer.files;
                }

                showNotification('Image selected. Save changes to update your profile picture.', 'info');
            }
        }
    });

    // Handle profile image input in form
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && validateImage(file)) {
                previewImage(file, currentAvatar);
            }
        });
    }
}

// Validate image file
function validateImage(file) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!validTypes.includes(file.type)) {
        showNotification('Please select a valid image file (JPG, PNG, or GIF)', 'error');
        return false;
    }

    if (file.size > maxSize) {
        showNotification('Image size must be less than 5MB', 'error');
        return false;
    }

    return true;
}

// Preview image
function previewImage(file, imgElement) {
    const reader = new FileReader();
    reader.onload = function (e) {
        imgElement.src = e.target.result;

        // Add loading animation
        imgElement.style.opacity = '0.5';
        setTimeout(() => {
            imgElement.style.opacity = '1';
        }, 300);
    };
    reader.readAsDataURL(file);
}

// Initialize form validation
function initializeFormValidation() {
    const form = document.querySelector('.profile-form');
    if (!form) return;

    // Real-time validation for required fields
    const requiredInputs = form.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function () {
            validateField(this);
        });

        input.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // URL validation for social links
    const urlInputs = form.querySelectorAll('input[type="url"]');
    urlInputs.forEach(input => {
        input.addEventListener('blur', function () {
            if (this.value && !isValidURL(this.value)) {
                showFieldError(this, 'Please enter a valid URL');
            } else {
                clearFieldError(this);
            }
        });
    });

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            // Remove non-numeric characters except + and spaces
            this.value = this.value.replace(/[^\d\s+]/g, '');
        });
    }

    // Form submission
    form.addEventListener('submit', function (e) {
        const invalidFields = form.querySelectorAll('.is-invalid');
        if (invalidFields.length > 0) {
            e.preventDefault();
            invalidFields[0].focus();
            showNotification('Please correct the errors before submitting', 'error');
        } else {
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';
        }
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();

    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }

    clearFieldError(field);
    return true;
}

// Check if URL is valid
function isValidURL(string) {
    try {
        const url = new URL(string);
        return url.protocol === "http:" || url.protocol === "https:";
    } catch (_) {
        return false;
    }
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('is-invalid');

    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

// Clear field error
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

// Initialize character counters for textareas
function initializeCharCounters() {
    const textareas = [
        { id: 'education', max: 500 },
        { id: 'research_interests', max: 500 },
        { id: 'courses_taught', max: 500 },
        { id: 'biography', max: 1000 },
        { id: 'about', max: 1000 }
    ];

    textareas.forEach(({ id, max }) => {
        const textarea = document.getElementById(id);
        if (!textarea) return;

        // Create counter element
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.textAlign = 'right';
        counter.style.fontSize = '0.75rem';
        counter.style.color = '#64748b';
        counter.style.marginTop = '0.25rem';

        textarea.parentNode.appendChild(counter);

        // Update counter on input
        const updateCounter = () => {
            const length = textarea.value.length;
            counter.textContent = `${length} / ${max}`;

            if (length > max * 0.9) {
                counter.style.color = '#ef4444';
            } else if (length > max * 0.7) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = '#64748b';
            }
        };

        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    });
}

// Initialize tooltips
function initializeTooltips() {
    const elements = document.querySelectorAll('[title]');
    elements.forEach(element => {
        const title = element.getAttribute('title');
        element.removeAttribute('title');
        element.setAttribute('data-tooltip', title);

        element.addEventListener('mouseenter', function (e) {
            showTooltip(e.target, title);
        });

        element.addEventListener('mouseleave', function () {
            hideTooltip();
        });
    });
}

// Show tooltip
function showTooltip(element, text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        z-index: 9999;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    `;

    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
}

// Hide tooltip
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    `;

    // Add border color based on type
    const borderColors = {
        info: '#3b82f6',
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b'
    };
    notification.style.borderLeft = `4px solid ${borderColors[type]}`;

    // Add icon
    const icons = {
        info: 'fa-info-circle',
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle'
    };

    notification.innerHTML = `
        <i class="fas ${icons[type]}" style="color: ${borderColors[type]}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: #6b7280; margin-left: 1rem;">&times;</button>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Add spinner styles
const style = document.createElement('style');
style.textContent = `
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .is-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
    }
`;
document.head.appendChild(style);

// Export functions
window.toggleEditMode = toggleEditMode;
window.showNotification = showNotification;