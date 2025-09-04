// assets/js/faculty-dashboard.js

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializeDashboard();
    initializeApplicationReview();
    initializeStats();
    loadDashboardUpdates();
});

// Initialize dashboard functionality
function initializeDashboard() {
    const postCards = document.querySelectorAll('.research-post-card');
    postCards.forEach(card => {
        card.addEventListener('click', function (e) {
            if (!e.target.closest('.btn')) {
                const link = this.querySelector('a[href*="view-research"]');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    });

    // Add hover effects to application items
    const applicationItems = document.querySelectorAll('.application-item');
    applicationItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.cursor = 'pointer';
        });
    });
}

// Initialize application review functionality
function initializeApplicationReview() {
}

// Review application function
function reviewApplication(applicationId) {
    // Show loading
    const modal = document.getElementById('applicationModal');
    const detailsContainer = document.getElementById('applicationDetails');

    detailsContainer.innerHTML = '<div class="loading">Loading application details...</div>';
    commonFunctions.showModal('applicationModal');

    // Fetch application details
    commonFunctions.ajaxRequest(
        '../api/get-application-details.php',
        'GET',
        { application_id: applicationId },
        (response) => {
            if (response.success) {
                displayApplicationDetails(response.data);
            } else {
                detailsContainer.innerHTML = '<p class="error">Failed to load application details.</p>';
            }
        },
        (error) => {
            detailsContainer.innerHTML = '<p class="error">Error loading application details.</p>';
        }
    );
}

// Display application details in modal
function displayApplicationDetails(data) {
    const detailsContainer = document.getElementById('applicationDetails');

    detailsContainer.innerHTML = `
        <div class="application-detail-section">
            <h3>Student Information</h3>
            <div class="student-profile">
                ${data.student.profile_image ?
            `<img src="${data.student.profile_image}" alt="Student" class="student-avatar">` :
            `<div class="student-avatar" style="background: var(--gradient-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700;">
                        ${data.student.full_name.charAt(0).toUpperCase()}
                    </div>`
        }
                <div class="student-info">
                    <h4>${escapeHtml(data.student.full_name)}</h4>
                    <p>${escapeHtml(data.student.email)}</p>
                    <div class="student-details">
                        <div class="detail-field">
                            <label>Degree</label>
                            <span>${escapeHtml(data.student.degree)}</span>
                        </div>
                        <div class="detail-field">
                            <label>CGPA</label>
                            <span>${data.student.cgpa ? data.student.cgpa.toFixed(2) : 'N/A'}</span>
                        </div>
                        <div class="detail-field">
                            <label>Phone</label>
                            <span>${escapeHtml(data.student.phone || 'Not provided')}</span>
                        </div>
                        <div class="detail-field">
                            <label>Research Interests</label>
                            <span>${escapeHtml(data.student.research_interest || 'Not specified')}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="application-detail-section">
            <h3>Applied For</h3>
            <p><strong>${escapeHtml(data.research.title)}</strong></p>
            <p>Applied on: ${formatDate(data.application.applied_at)}</p>
        </div>
        
        ${data.application.cover_letter ? `
        <div class="application-detail-section">
            <h3>Cover Letter</h3>
            <div class="cover-letter">
                ${escapeHtml(data.application.cover_letter).replace(/\n/g, '<br>')}
            </div>
        </div>
        ` : ''}
        
        ${data.student.linkedin || data.student.github ? `
        <div class="application-detail-section">
            <h3>Links</h3>
            <div class="student-links">
                ${data.student.linkedin ? `<a href="${escapeHtml(data.student.linkedin)}" target="_blank" class="btn btn-sm">LinkedIn Profile</a>` : ''}
                ${data.student.github ? `<a href="${escapeHtml(data.student.github)}" target="_blank" class="btn btn-sm">GitHub Profile</a>` : ''}
            </div>
        </div>
        ` : ''}
        
        <div class="application-actions-modal">
            <button class="btn btn-success" onclick="acceptApplication(${data.application.application_id})">Accept Application</button>
            <button class="btn btn-danger" onclick="rejectApplication(${data.application.application_id})">Reject Application</button>
            <button class="btn btn-outline" onclick="closeModal('applicationModal')">Close</button>
        </div>
    `;
}

// Accept application
function acceptApplication(applicationId) {
    if (confirm('Are you sure you want to accept this application?')) {
        updateApplicationStatus(applicationId, 'accepted');
    }
}

// Reject application
function rejectApplication(applicationId) {
    if (confirm('Are you sure you want to reject this application?')) {
        updateApplicationStatus(applicationId, 'rejected');
    }
}

// Update application status
function updateApplicationStatus(applicationId, status) {
    commonFunctions.ajaxRequest(
        '../api/update-application-status.php',
        'POST',
        {
            application_id: applicationId,
            status: status
        },
        (response) => {
            if (response.success) {
                showNotification(`Application ${status} successfully!`, 'success');
                commonFunctions.closeModal('applicationModal');

                // Remove from pending list
                const appItem = document.querySelector(`.application-item [onclick*="${applicationId}"]`);
                if (appItem) {
                    const container = appItem.closest('.application-item');
                    container.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => container.remove(), 300);
                }

                // Update pending count
                updatePendingCount(-1);

                // Reload page after 1 second
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Failed to update application status', 'error');
            }
        }
    );
}

// Initialize stats animations
function initializeStats() {
    const statValues = document.querySelectorAll('.stat-value');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateValue(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    statValues.forEach(value => observer.observe(value));
}

// Animate stat values
function animateValue(element) {
    const endValue = parseInt(element.textContent);
    const duration = 1000;
    const frameDuration = 1000 / 60;
    const totalFrames = Math.round(duration / frameDuration);
    const easeOutQuad = t => t * (2 - t);

    let frame = 0;
    const counter = setInterval(() => {
        frame++;
        const progress = easeOutQuad(frame / totalFrames);
        const currentValue = Math.round(endValue * progress);

        element.textContent = currentValue;

        if (frame === totalFrames) {
            clearInterval(counter);
        }
    }, frameDuration);
}

// Load dashboard updates
function loadDashboardUpdates() {
    // Check for new applications every 30 seconds
    setInterval(() => {
        checkNewApplications();
    }, 30000);
}

// Check for new applications
function checkNewApplications() {
    commonFunctions.ajaxRequest(
        '../api/check-new-applications.php',
        'GET',
        null,
        (response) => {
            if (response.hasNew) {
                showNotification(`You have ${response.count} new application(s)!`, 'info');
                updatePendingCount(response.count);
            }
        }
    );
}

// Update pending count
function updatePendingCount(change) {
    const pendingCard = document.querySelector('.stat-card:nth-child(4) .stat-value');
    if (pendingCard) {
        const currentCount = parseInt(pendingCard.textContent);
        const newCount = Math.max(0, currentCount + change);

        pendingCard.style.transform = 'scale(1.2)';
        pendingCard.style.color = 'var(--success-color)';

        setTimeout(() => {
            pendingCard.textContent = newCount;
            pendingCard.style.transform = 'scale(1)';

            setTimeout(() => {
                pendingCard.style.color = '';
            }, 1000);
        }, 300);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span>${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 10);

    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });

    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Add animations to elements
document.querySelectorAll('.stat-card').forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    card.classList.add('animate-in');
});

document.querySelectorAll('.research-post-card').forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    card.classList.add('animate-in');
});

// Add CSS for animations and notifications
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.5s ease forwards;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 0.5rem;
        box-shadow: var(--shadow-lg);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 9999;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        gap: 1rem;
    }
    
    .notification-info {
        border-left: 4px solid var(--info-color);
    }
    
    .notification-success {
        border-left: 4px solid var(--success-color);
    }
    
    .notification-error {
        border-left: 4px solid var(--danger-color);
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-secondary);
        padding: 0;
        line-height: 1;
    }
    
    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: scale(0.8);
        }
    }
    
    .loading {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
    }
    
    .error {
        color: var(--danger-color);
        text-align: center;
        padding: 2rem;
    }
`;
document.head.appendChild(style);

// Export functions
window.reviewApplication = reviewApplication;
window.acceptApplication = acceptApplication;
window.rejectApplication = rejectApplication;
window.closeModal = commonFunctions.closeModal;