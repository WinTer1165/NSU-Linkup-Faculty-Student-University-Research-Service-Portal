// assets/js/manage-users.js

document.addEventListener('DOMContentLoaded', function () {
    initializeFilters();
    initializeTableActions();
    initializeSearch();
    initializeKeyboardShortcuts();
});

// Initialize filters
function initializeFilters() {
    // Auto-submit on filter change
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            this.form.submit();
            showLoadingOverlay();
        });
    });

    // Search with debouncing
    const searchInput = document.querySelector('.filter-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
                showLoadingOverlay();
            }, 500);
        });
    }
}

// Initialize table actions
function initializeTableActions() {
    // Add hover effect to rows
    const rows = document.querySelectorAll('.user-row');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function () {
            this.style.backgroundColor = 'var(--light-bg)';
        });

        row.addEventListener('mouseleave', function () {
            this.style.backgroundColor = '';
        });
    });
}

// View user profile
function viewUserProfile(userId, userType) {
    // Open in new tab based on user type
    const urls = {
        'student': `../student/profile.php?id=${userId}`,
        'faculty': `../faculty/profile.php?id=${userId}`,
        'organizer': `../organizer/dashboard.php?id=${userId}`
    };

    if (urls[userType]) {
        window.open(urls[userType], '_blank');
    }
}

// Toggle ban status
function toggleBan(userId, shouldBan) {
    const action = shouldBan ? 'ban' : 'unban';
    const message = shouldBan ?
        'Are you sure you want to ban this user? They will not be able to log in.' :
        'Are you sure you want to unban this user? They will be able to log in again.';

    showConfirmModal({
        title: shouldBan ? 'Ban User' : 'Unban User',
        message: message,
        icon: shouldBan ? '🚫' : '✅',
        confirmText: shouldBan ? 'Ban User' : 'Unban User',
        confirmClass: shouldBan ? 'btn-danger' : 'btn-success',
        onConfirm: () => {
            const formData = new FormData();
            formData.append('user_id', userId);

            fetch('manage-users.php?ajax=1&action=toggle_ban', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        const row = document.querySelector(`[data-user-id="${userId}"]`);
                        if (row) {
                            const statusCell = row.querySelector('.user-status');
                            const actionButton = row.querySelector(shouldBan ? '.btn-ban' : '.btn-unban');

                            if (data.is_banned) {
                                statusCell.innerHTML = '<span class="status-badge banned">Banned</span>';
                                actionButton.className = 'btn-icon btn-unban';
                                actionButton.title = 'Unban User';
                                actionButton.onclick = () => toggleBan(userId, false);
                            } else {
                                statusCell.innerHTML = '<span class="status-badge active">Active</span>';
                                actionButton.className = 'btn-icon btn-ban';
                                actionButton.title = 'Ban User';
                                actionButton.onclick = () => toggleBan(userId, true);
                            }

                            // Highlight row
                            highlightRow(row, data.is_banned ? '#ef4444' : '#10b981');
                        }

                        showNotification(
                            data.is_banned ? 'User banned successfully' : 'User unbanned successfully',
                            'success'
                        );
                        updateStatistics();
                    } else {
                        showNotification(data.message || 'Failed to update ban status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        }
    });
}

// Assign organizer role
function assignOrganizerRole(userId, userName) {
    showConfirmModal({
        title: 'Assign Organizer Role',
        message: `Are you sure you want to assign organizer role to ${userName}? This will change their user type and give them access to create events.`,
        icon: '🎯',
        confirmText: 'Assign Role',
        confirmClass: 'btn-primary',
        onConfirm: () => {
            const formData = new FormData();
            formData.append('user_id', userId);

            fetch('manage-users.php?ajax=1&action=assign_organizer', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row and update statistics
                        const row = document.querySelector(`[data-user-id="${userId}"]`);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';

                            setTimeout(() => {
                                row.remove();
                                updateStatistics();
                            }, 300);
                        }

                        showNotification('Organizer role assigned successfully', 'success');
                    } else {
                        showNotification(data.message || 'Failed to assign role', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        }
    });
}

// Delete user
function deleteUser(userId, userName) {
    showConfirmModal({
        title: 'Delete User',
        message: `Are you sure you want to permanently delete ${userName}? This action cannot be undone.`,
        icon: '🗑️',
        confirmText: 'Delete User',
        confirmClass: 'btn-danger',
        onConfirm: () => {
            const formData = new FormData();
            formData.append('user_id', userId);

            fetch('manage-users.php?ajax=1&action=delete_user', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row with animation
                        const row = document.querySelector(`[data-user-id="${userId}"]`);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';

                            setTimeout(() => {
                                row.remove();

                                // Check if table is empty
                                const tbody = document.querySelector('.users-table tbody');
                                if (tbody && tbody.children.length === 0) {
                                    const container = document.querySelector('.users-table-container');
                                    container.innerHTML = `
                                    <div class="empty-state">
                                        <div class="empty-icon">👥</div>
                                        <h3>No users found</h3>
                                        <p>Try adjusting your filters.</p>
                                    </div>
                                `;
                                }

                                updateStatistics();
                            }, 300);
                        }

                        showNotification('User deleted successfully', 'success');
                    } else {
                        showNotification(data.message || 'Failed to delete user', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        }
    });
}

// Initialize search functionality
function initializeSearch() {
    // Add search highlighting
    const searchTerm = new URLSearchParams(window.location.search).get('search');
    if (searchTerm) {
        highlightSearchTerms(searchTerm);
    }
}

// Highlight search terms
function highlightSearchTerms(searchTerm) {
    const terms = searchTerm.toLowerCase().split(' ');
    const cells = document.querySelectorAll('.user-name, .user-email');

    cells.forEach(cell => {
        let text = cell.textContent;
        terms.forEach(term => {
            if (term && text.toLowerCase().includes(term)) {
                const regex = new RegExp(`(${term})`, 'gi');
                text = text.replace(regex, '<mark>$1</mark>');
            }
        });
        cell.innerHTML = text;
    });
}

// Initialize keyboard shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Alt + S to focus search
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            document.querySelector('.filter-input')?.focus();
        }

        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement.matches('.filter-input')) {
            document.activeElement.value = '';
            document.querySelector('.filters-form').submit();
        }
    });
}

// Show confirmation modal
function showConfirmModal(options) {
    const modal = document.createElement('div');
    modal.className = 'confirm-modal';
    modal.innerHTML = `
        <div class="confirm-content">
            <div class="confirm-icon">${options.icon || '❓'}</div>
            <h3 class="confirm-title">${options.title}</h3>
            <p class="confirm-message">${options.message}</p>
            <div class="confirm-actions">
                <button class="btn btn-secondary" onclick="closeConfirmModal(this)">Cancel</button>
                <button class="btn ${options.confirmClass || 'btn-primary'}" id="confirmButton">
                    ${options.confirmText || 'Confirm'}
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    modal.style.display = 'block';

    // Add event listener to confirm button
    document.getElementById('confirmButton').addEventListener('click', function () {
        closeConfirmModal(this);
        if (options.onConfirm) {
            options.onConfirm();
        }
    });

    // Close on background click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeConfirmModal();
        }
    });
}

// Close confirmation modal
function closeConfirmModal(button) {
    const modal = button ? button.closest('.confirm-modal') : document.querySelector('.confirm-modal');
    if (modal) {
        modal.style.display = 'none';
        setTimeout(() => modal.remove(), 300);
    }
}

// Highlight row
function highlightRow(row, color) {
    const originalBg = row.style.backgroundColor;
    row.style.backgroundColor = color;
    row.style.opacity = '0.3';

    setTimeout(() => {
        row.style.transition = 'all 0.5s ease';
        row.style.backgroundColor = originalBg;
        row.style.opacity = '1';
    }, 500);
}

// Update statistics
function updateStatistics() {
    const stats = {
        students: document.querySelectorAll('.type-student').length,
        faculty: document.querySelectorAll('.type-faculty').length,
        organizers: document.querySelectorAll('.type-organizer').length,
        banned: document.querySelectorAll('.status-badge.banned').length
    };

    // Update stat values
    const statItems = document.querySelectorAll('.stat-item');
    statItems[0].querySelector('.stat-value').textContent = stats.students;
    statItems[1].querySelector('.stat-value').textContent = stats.faculty;
    statItems[2].querySelector('.stat-value').textContent = stats.organizers;
    statItems[3].querySelector('.stat-value').textContent = stats.banned;
}

// Show loading overlay
function showLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
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
window.viewUserProfile = viewUserProfile;
window.toggleBan = toggleBan;
window.assignOrganizerRole = assignOrganizerRole;
window.deleteUser = deleteUser;
window.closeConfirmModal = closeConfirmModal;

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
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    mark {
        background: rgba(99, 102, 241, 0.2);
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
    }
`;
document.head.appendChild(style);