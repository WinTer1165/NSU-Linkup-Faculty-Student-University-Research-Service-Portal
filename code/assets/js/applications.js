// assets/js/applications.js

document.addEventListener('DOMContentLoaded', function () {
    initializeMobileMenu();
    initializeCardAnimations();
    initializeExpandableContent();
});

// Initialize mobile menu
function initializeMobileMenu() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }
}

// Initialize card animations
function initializeCardAnimations() {
    const cards = document.querySelectorAll('.application-card');

    cards.forEach((card, index) => {
        // Add staggered animation on load
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Initialize expandable content (cover letters)
function initializeExpandableContent() {
    const coverLetters = document.querySelectorAll('.cover-letter p');

    coverLetters.forEach(letter => {
        const fullText = letter.textContent;
        const maxLength = 200;

        if (fullText.length > maxLength) {
            const truncatedText = fullText.substring(0, maxLength) + '...';
            letter.textContent = truncatedText;
            letter.dataset.fullText = fullText;
            letter.dataset.truncated = truncatedText;

            // Add expand/collapse button
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'text-toggle-btn';
            toggleBtn.textContent = 'Read More';
            toggleBtn.onclick = function () {
                toggleText(letter, toggleBtn);
            };

            letter.parentNode.appendChild(toggleBtn);
        }
    });
}

// Toggle text expansion
function toggleText(element, button) {
    if (element.textContent === element.dataset.truncated) {
        element.textContent = element.dataset.fullText;
        button.textContent = 'Read Less';
    } else {
        element.textContent = element.dataset.truncated;
        button.textContent = 'Read More';
    }
}

// Confirm action (approve/reject)
function confirmAction(applicationId, action, studentName) {
    const modal = document.getElementById('actionModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const applicationIdInput = document.getElementById('applicationId');
    const actionTypeInput = document.getElementById('actionType');
    const confirmBtn = document.getElementById('confirmBtn');

    // Set modal content based on action
    if (action === 'approve') {
        modalTitle.textContent = 'Approve Application';
        modalMessage.textContent = `Are you sure you want to approve ${studentName}'s application?`;
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
    } else {
        modalTitle.textContent = 'Reject Application';
        modalMessage.textContent = `Are you sure you want to reject ${studentName}'s application?`;
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject';
    }

    // Set form values
    applicationIdInput.value = applicationId;
    actionTypeInput.value = action;

    // Show modal
    modal.classList.add('active');
    modal.style.display = 'block';
}

// Close modal
function closeModal() {
    const modal = document.getElementById('actionModal');
    modal.classList.remove('active');
    modal.style.display = 'none';
}

// View application details (can be expanded for more functionality)
function viewDetails(applicationId) {
    // For now, just expand the card
    const card = document.querySelector(`[data-application-id="${applicationId}"]`);
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.style.animation = 'highlight 1s ease';
    }

    // You can add more functionality here like opening a detailed view modal
    console.log('Viewing details for application:', applicationId);
}

// Export functions for global use
window.confirmAction = confirmAction;
window.closeModal = closeModal;
window.viewDetails = viewDetails;

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('actionModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Add highlight animation
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight {
        0% {
            box-shadow: var(--card-shadow);
        }
        50% {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        100% {
            box-shadow: var(--card-shadow);
        }
    }
    
    .text-toggle-btn {
        background: none;
        border: none;
        color: var(--dashboard-secondary);
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 0.5rem;
        transition: color 0.3s ease;
    }
    
    .text-toggle-btn:hover {
        color: var(--dashboard-primary);
        text-decoration: underline;
    }
`;
document.head.appendChild(style);

// Add filter functionality
document.addEventListener('DOMContentLoaded', function () {
    // Auto-submit filter form on change
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            // Form is already set to auto-submit in HTML, but we can add additional logic here
            console.log('Filter changed:', this.name, this.value);
        });
    });

    // Add search functionality if needed in the future
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search applications...';
    searchInput.className = 'search-input';
    searchInput.style.display = 'none'; // Hidden by default, can be enabled later

    searchInput.addEventListener('input', debounce(function () {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.application-card');

        cards.forEach(card => {
            const studentName = card.querySelector('.student-details h3').textContent.toLowerCase();
            const researchTitle = card.querySelector('.research-info h4').textContent.toLowerCase();

            if (studentName.includes(searchTerm) || researchTitle.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }, 300));
});

// Debounce helper function
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

// Add loading state for action buttons
document.querySelectorAll('.application-actions .btn').forEach(btn => {
    btn.addEventListener('click', function () {
        if (this.type === 'submit') {
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Processing...';
        }
    });
});

// Statistics animation
window.addEventListener('load', function () {
    const statValues = document.querySelectorAll('.stat-value');

    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 30);
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            stat.textContent = currentValue;
        }, 30);
    });
});