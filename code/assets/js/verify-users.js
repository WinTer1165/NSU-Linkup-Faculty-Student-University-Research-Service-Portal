// assets/js/verify-users.js

document.addEventListener('DOMContentLoaded', function () {
    initializeRejectForm();
    initializeModalHandlers();
    initializeAutoRefresh();
    initializeKeyboardShortcuts();
});

// Initialize reject form
function initializeRejectForm() {
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const userId = document.getElementById('rejectUserId').value;
            const reason = document.getElementById('rejectReason').value;

            performReject(userId, reason);
        });
    }
}

// Initialize modal handlers
function initializeModalHandlers() {
    // Close modals on background click
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // ESC key to close modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

// View user details
function viewDetails(userId) {
    const modal = document.getElementById('userDetailsModal');
    const content = document.getElementById('userDetailsContent');

    // Show loading
    content.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    modal.style.display = 'block';

    fetch(`verify-users.php?ajax=1&action=get_user_details&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = buildDetailsHTML(data.data);
            } else {
                content.innerHTML = '<p class="error">Failed to load user details</p>';
                showNotification(data.message || 'Failed to load details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.reload();
            // content.innerHTML = '<p class="error">An error occurred</p>';
            // showNotification('An error occurred', 'error');
        });
}

// Build user details HTML
function buildDetailsHTML(user) {
    let html = `
        <div class="details-section">
            <h3>Basic Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Full Name</div>
                    <div class="detail-item-value">${escapeHtml(user.full_name)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Email</div>
                    <div class="detail-item-value">${escapeHtml(user.email)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">User Type</div>
                    <div class="detail-item-value">
                        <span class="user-type-badge ${user.user_type}">
                            ${user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Registration Date</div>
                    <div class="detail-item-value">${formatDateTime(user.created_at)}</div>
                </div>
            </div>
        </div>
    `;

    if (user.user_type === 'faculty') {
        html += `
            <div class="details-section">
                <h3>Faculty Information</h3>
                <div class="details-grid">
                    ${user.title ? `
                        <div class="detail-item">
                            <div class="detail-item-label">Title</div>
                            <div class="detail-item-value">${escapeHtml(user.title)}</div>
                        </div>
                    ` : ''}
                    ${user.office ? `
                        <div class="detail-item">
                            <div class="detail-item-label">Office</div>
                            <div class="detail-item-value">${escapeHtml(user.office)}</div>
                        </div>
                    ` : ''}
                </div>
                ${user.education ? `
                    <div class="detail-item" style="margin-top: 1rem;">
                        <div class="detail-item-label">Education</div>
                        <div class="detail-item-value">${escapeHtml(user.education)}</div>
                    </div>
                ` : ''}
                ${user.research_interests ? `
                    <div class="detail-item" style="margin-top: 1rem;">
                        <div class="detail-item-label">Research Interests</div>
                        <div class="detail-item-value">${escapeHtml(user.research_interests)}</div>
                    </div>
                ` : ''}
            </div>
        `;
    } else if (user.user_type === 'organizer') {
        html += `
            <div class="details-section">
                <h3>Organizer Information</h3>
                <div class="details-grid">
                    ${user.organization ? `
                        <div class="detail-item">
                            <div class="detail-item-label">Organization</div>
                            <div class="detail-item-value">${escapeHtml(user.organization)}</div>
                        </div>
                    ` : ''}
                    ${user.organizer_phone ? `
                        <div class="detail-item">
                            <div class="detail-item-label">Phone</div>
                            <div class="detail-item-value">${escapeHtml(user.organizer_phone)}</div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    html += `
        <div class="modal-actions">
            <button class="btn btn-success" onclick="verifyUserFromModal(${user.user_id}, '${escapeHtml(user.full_name)}')">
                Approve User
            </button>
            <button class="btn btn-danger" onclick="rejectUserFromModal(${user.user_id}, '${escapeHtml(user.full_name)}')">
                Reject User
            </button>
            <button class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
        </div>
    `;

    return html;
}

// Verify user
function verifyUser(userId, userName) {
    if (!confirm(`Are you sure you want to approve ${userName}?`)) {
        return;
    }

    const card = document.querySelector(`[data-user-id="${userId}"]`);
    if (card) {
        card.classList.add('loading');
    }

    const formData = new FormData();
    formData.append('user_id', userId);

    fetch('verify-users.php?ajax=1&action=verify_user', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animate and remove card
                if (card) {
                    card.classList.add('success-animation');
                    setTimeout(() => {
                        card.classList.add('card-removing');
                        setTimeout(() => {
                            card.remove();
                            checkEmptyState();
                            updateStatistics();
                        }, 300);
                    }, 500);
                }

                showNotification(`${userName} has been approved successfully!`, 'success');
            } else {
                if (card) {
                    card.classList.remove('loading');
                }
                //showNotification(data.message || 'Failed to verify user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (card) {
                card.classList.remove('loading');
            }
            window.location.reload();
            // showNotification('An error occurred', 'error');
        });
}

// Verify user from modal
function verifyUserFromModal(userId, userName) {
    closeDetailsModal();
    verifyUser(userId, userName);
}

// Reject user
function rejectUser(userId, userName) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectModal').style.display = 'block';

    // Store user name for later use
    document.getElementById('rejectForm').dataset.userName = userName;
}

// Reject user from modal
function rejectUserFromModal(userId, userName) {
    closeDetailsModal();
    rejectUser(userId, userName);
}

// Perform reject
function performReject(userId, reason) {
    const userName = document.getElementById('rejectForm').dataset.userName;
    const card = document.querySelector(`[data-user-id="${userId}"]`);

    if (card) {
        card.classList.add('loading');
    }

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('reason', reason);

    fetch('verify-users.php?ajax=1&action=reject_user', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                closeRejectModal();

                // Animate and remove card
                if (card) {
                    card.style.backgroundColor = '#fee2e2';
                    setTimeout(() => {
                        card.classList.add('card-removing');
                        setTimeout(() => {
                            card.remove();
                            checkEmptyState();
                            updateStatistics();
                        }, 300);
                    }, 300);
                }

                showNotification(`${userName} has been rejected.`, 'info');
            } else {
                if (card) {
                    card.classList.remove('loading');
                }
                //showNotification(data.message || 'Failed to reject user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (card) {
                card.classList.remove('loading');
            }
            window.location.reload();
            // showNotification('An error occurred', 'error');
        });
}

// Close details modal
function closeDetailsModal() {
    document.getElementById('userDetailsModal').style.display = 'none';
}

// Close reject modal
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejectForm').reset();
}

// Close all modals
function closeAllModals() {
    closeDetailsModal();
    closeRejectModal();
}

// Check empty state
function checkEmptyState() {
    const list = document.querySelector('.verification-list');
    if (list && list.children.length === 0) {
        const section = document.querySelector('.pending-users-section');
        section.innerHTML = `
            <h2 class="section-title">Pending Verifications</h2>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>All caught up!</h3>
                <p>No pending verifications at the moment.</p>
            </div>
        `;
    }
}

// Update statistics
function updateStatistics() {
    // Count remaining cards by type
    const pendingFaculty = document.querySelectorAll('.user-type-badge.faculty').length;
    const pendingOrganizers = document.querySelectorAll('.user-type-badge.organizer').length;

    // Update stat values
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues[0]) animateNumber(statValues[0], parseInt(statValues[0].textContent), pendingFaculty);
    if (statValues[1]) animateNumber(statValues[1], parseInt(statValues[1].textContent), pendingOrganizers);
}

// Initialize auto-refresh
function initializeAutoRefresh() {
    // Check for new pending users every 60 seconds
    setInterval(checkForNewUsers, 60000);
}

// Check for new users
function checkForNewUsers() {
    fetch('get-pending-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > document.querySelectorAll('.verification-card').length) {
                showNotification('New users pending verification!', 'info');
            }
        })
        .catch(error => console.error('Error checking for new users:', error));
}

// Initialize keyboard shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // A to approve first user
        if (e.key === 'a' && !e.ctrlKey && !e.target.matches('input, textarea')) {
            const firstCard = document.querySelector('.verification-card');
            if (firstCard) {
                const approveBtn = firstCard.querySelector('.btn-success');
                if (approveBtn) approveBtn.click();
            }
        }

        // R to reject first user
        if (e.key === 'r' && !e.ctrlKey && !e.target.matches('input, textarea')) {
            const firstCard = document.querySelector('.verification-card');
            if (firstCard) {
                const rejectBtn = firstCard.querySelector('.btn-danger');
                if (rejectBtn) rejectBtn.click();
            }
        }
    });
}

// Animate number
function animateNumber(element, start, end) {
    const duration = 300;
    const increment = (end - start) / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Format date time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Escape HTML
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${getNotificationIcon(type)}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Get notification icon
function getNotificationIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}

// Export functions for global use
window.viewDetails = viewDetails;
window.verifyUser = verifyUser;
window.verifyUserFromModal = verifyUserFromModal;
window.rejectUser = rejectUser;
window.rejectUserFromModal = rejectUserFromModal;
window.closeDetailsModal = closeDetailsModal;
window.closeRejectModal = closeRejectModal;

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: var(--shadow-lg);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 2000;
        border-left: 4px solid var(--info-color);
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification.success {
        border-left-color: var(--success-color);
    }
    
    .notification.error {
        border-left-color: var(--danger-color);
    }
    
    .notification.info {
        border-left-color: var(--info-color);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 3rem;
    }
    
    .spinner {
        border: 3px solid var(--border-color);
        border-top: 3px solid var(--primary-color);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .error {
        color: var(--danger-color);
        text-align: center;
        padding: 2rem;
    }
`;
document.head.appendChild(style);