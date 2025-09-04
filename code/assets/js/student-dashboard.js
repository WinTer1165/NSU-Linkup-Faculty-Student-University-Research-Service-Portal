// assets/js/student-dashboard.js

document.addEventListener('DOMContentLoaded', function () {
    initializeDashboard();
    initializeMobileMenu();
    loadDashboardData();
});

// Initialize dashboard functionality
function initializeDashboard() {
    // Animate stats on load
    animateStats();

    // Initialize smooth scrolling
    initializeSmoothScrolling();

    // Initialize tooltips
    initializeTooltips();

    // Auto-refresh data every 5 minutes
    setInterval(loadDashboardData, 300000);
}

// Animate statistics counters
function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');

    statValues.forEach(stat => {
        const target = parseInt(stat.textContent.replace(/[^\d]/g, ''));
        if (isNaN(target)) return;

        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target + (stat.textContent.includes('%') ? '%' : '');
                clearInterval(timer);
            } else {
                stat.textContent = Math.ceil(current) + (stat.textContent.includes('%') ? '%' : '');
            }
        }, 50);
    });
}

// Initialize mobile menu
function initializeMobileMenu() {
    const menuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function (e) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }
}

// Initialize smooth scrolling for internal links
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize tooltips
function initializeTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');

    elements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

// Show tooltip
function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    if (!text) return;

    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;

    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.position = 'absolute';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
    tooltip.style.background = '#1e3a8a';
    tooltip.style.color = 'white';
    tooltip.style.padding = '0.5rem 0.75rem';
    tooltip.style.borderRadius = '0.375rem';
    tooltip.style.fontSize = '0.875rem';
    tooltip.style.zIndex = '9999';
    tooltip.style.opacity = '0';
    tooltip.style.transition = 'opacity 0.3s ease';

    setTimeout(() => tooltip.style.opacity = '1', 10);
}

// Hide tooltip
function hideTooltip() {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Load dashboard data via AJAX
function loadDashboardData() {
    // This would typically fetch updated data from the server
    // For now, we'll just update timestamps and notification counts
    updateTimestamps();
    checkForNewNotifications();
}

// Update relative timestamps
function updateTimestamps() {
    const timestamps = document.querySelectorAll('[data-timestamp]');

    timestamps.forEach(timestamp => {
        const date = new Date(timestamp.getAttribute('data-timestamp'));
        const now = new Date();
        const diff = now - date;

        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        let relativeTime;
        if (minutes < 1) {
            relativeTime = 'Just now';
        } else if (minutes < 60) {
            relativeTime = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (hours < 24) {
            relativeTime = `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            relativeTime = `${days} day${days > 1 ? 's' : ''} ago`;
        }

        timestamp.textContent = relativeTime;
    });
}

// Check for new notifications
function checkForNewNotifications() {
    fetch('../api/check-notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                showNotificationBadge();
            }
        })
        .catch(error => {
            console.log('Notification check failed:', error);
        });
}

// Show notification badge
function showNotificationBadge() {
    const badge = document.createElement('span');
    badge.className = 'notification-badge';
    badge.textContent = '!';

    const bellIcon = document.querySelector('.fa-bell');
    if (bellIcon && !bellIcon.querySelector('.notification-badge')) {
        bellIcon.appendChild(badge);
    }
}

// View application details
function viewApplication(applicationId) {
    showApplicationModal(applicationId);
}

// Show application modal
function showApplicationModal(applicationId) {
    const modal = document.createElement('div');
    modal.className = 'application-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Application Details</h3>
                <button class="modal-close" onclick="closeApplicationModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading application details...</p>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Load application details
    fetch(`../api/get-application.php?id=${applicationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplicationDetails(data.application);
            } else {
                showError('Failed to load application details');
            }
        })
        .catch(error => {
            showError('Error loading application details');
        });
}

// Display application details in modal
function displayApplicationDetails(application) {
    const modalBody = document.querySelector('.application-modal .modal-body');

    modalBody.innerHTML = `
        <div class="application-details-content">
            <div class="application-header">
                <div class="faculty-info">
                    <img src="${application.faculty_image || '../assets/images/default-avatar.png'}" 
                         alt="Faculty" class="faculty-avatar">
                    <div>
                        <h4>${application.title}</h4>
                        <p>${application.faculty_name}</p>
                        <span class="department">${application.department}</span>
                    </div>
                </div>
                <span class="status-badge status-${application.status}">
                    ${application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                </span>
            </div>
            
            <div class="application-info">
                <div class="info-section">
                    <h5>Research Details</h5>
                    <p><strong>Duration:</strong> ${application.duration || 'Not specified'}</p>
                    <p><strong>Salary:</strong> ${application.salary || 'Not specified'}</p>
                    <p><strong>Deadline:</strong> ${formatDate(application.apply_deadline)}</p>
                    <p><strong>Positions:</strong> ${application.number_required || 'Not specified'}</p>
                </div>
                
                <div class="info-section">
                    <h5>Application Details</h5>
                    <p><strong>Applied on:</strong> ${formatDate(application.applied_at)}</p>
                    ${application.reviewed_at ? `<p><strong>Reviewed on:</strong> ${formatDate(application.reviewed_at)}</p>` : ''}
                </div>
                
                ${application.cover_letter ? `
                    <div class="info-section">
                        <h5>Cover Letter</h5>
                        <div class="cover-letter">
                            ${application.cover_letter.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                ` : ''}
            </div>
            
            ${application.status === 'pending' ? `
                <div class="application-actions">
                    <button class="btn btn-outline" onclick="withdrawApplication(${application.application_id})">
                        Withdraw Application
                    </button>
                </div>
            ` : ''}
        </div>
    `;
}

// Close application modal
function closeApplicationModal() {
    const modal = document.querySelector('.application-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

// Withdraw application
function withdrawApplication(applicationId) {
    if (!confirm('Are you sure you want to withdraw this application? This action cannot be undone.')) {
        return;
    }

    fetch('../api/withdraw-application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `application_id=${applicationId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Application withdrawn successfully');
                closeApplicationModal();
                setTimeout(() => location.reload(), 2000);
            } else {
                showError(data.message || 'Failed to withdraw application');
            }
        })
        .catch(error => {
            showError('Error withdrawing application');
        });
}

// Show success message
function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.innerHTML = `
        <i class="fas fa-check-circle"></i>
        ${message}
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.insertBefore(alert, document.body.firstChild);

    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// Show error message
function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        ${message}
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.insertBefore(alert, document.body.firstChild);

    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// Format date helper
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Search functionality for quick access
function initializeQuickSearch() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Quick search...';
    searchInput.className = 'quick-search';

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const searchableElements = document.querySelectorAll('[data-searchable]');

        searchableElements.forEach(element => {
            const text = element.textContent.toLowerCase();
            const isVisible = text.includes(query);
            element.style.display = isVisible ? 'block' : 'none';
        });
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function (e) {
    // Ctrl/Cmd + K for quick search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('.quick-search');
        if (searchInput) {
            searchInput.focus();
        }
    }

    // Escape to close modals
    if (e.key === 'Escape') {
        closeApplicationModal();
    }
});

// Export functions for global access
window.dashboardFunctions = {
    viewApplication,
    withdrawApplication,
    showSuccessMessage,
    showError,
    closeApplicationModal
};