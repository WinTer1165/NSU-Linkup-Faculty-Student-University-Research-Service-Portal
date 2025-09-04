// assets/js/contact-queries.js

document.addEventListener('DOMContentLoaded', function () {
    initializeFilters();
    initializeQueryActions();
    initializeRealTimeUpdates();
    initializeKeyboardShortcuts();
});

// Initialize filter functionality
function initializeFilters() {
    // Auto-submit on filter change
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            this.form.submit();
        });
    });

    // Search input with debouncing
    const searchInput = document.querySelector('.filter-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }
}

// Initialize query actions
function initializeQueryActions() {
    // Add click handler for query cards
    const queryCards = document.querySelectorAll('.query-card');
    queryCards.forEach(card => {
        card.addEventListener('click', function (e) {
            if (!e.target.closest('button')) {
                const queryId = this.dataset.queryId;
                viewQuery(queryId);
            }
        });
    });
}

// View query details
function viewQuery(queryId) {
    // Show loading
    showLoadingModal();

    fetch(`contact-queries.php?ajax=1&action=get_details&query_id=${queryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showQueryDetails(data.data);

                // Update UI if message was marked as read
                if (!data.data.is_read) {
                    const card = document.querySelector(`[data-query-id="${queryId}"]`);
                    if (card) {
                        card.classList.remove('unread');
                        updateUnreadCount(-1);
                    }
                }
            } else {
                showNotification(data.message || 'Failed to load details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            hideLoadingModal();
        });
}

// Show query details in modal
function showQueryDetails(query) {
    const modal = document.getElementById('queryDetailsModal');
    const content = document.getElementById('queryDetailsContent');

    const detailsHtml = `
        <div class="details-grid">
            <div class="detail-section">
                <div class="detail-sender">
                    <div class="detail-avatar">
                        ${query.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="detail-sender-info">
                        <h3>${escapeHtml(query.name)}</h3>
                        <p>${escapeHtml(query.email)}</p>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Subject</div>
                <div class="detail-value">${escapeHtml(query.subject)}</div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Received</div>
                <div class="detail-value">${formatDateTime(query.created_at)}</div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Message</div>
                <div class="detail-message">${escapeHtml(query.message)}</div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="btn btn-primary" onclick="replyToQuery('${escapeHtml(query.email)}', '${escapeHtml(query.subject)}')">
                Reply to ${escapeHtml(query.name.split(' ')[0])}
            </button>
            ${!query.is_read ?
            `<button class="btn btn-secondary" onclick="markAsRead(${query.query_id})">Mark as Read</button>` :
            `<button class="btn btn-secondary" onclick="markAsUnread(${query.query_id})">Mark as Unread</button>`
        }
            <button class="btn btn-danger" onclick="deleteQuery(${query.query_id})">Delete</button>
        </div>
    `;

    content.innerHTML = detailsHtml;
    modal.style.display = 'block';
}

// Mark query as read
function markAsRead(queryId) {
    const formData = new FormData();
    formData.append('query_id', queryId);

    fetch('contact-queries.php?ajax=1&action=mark_read', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`[data-query-id="${queryId}"]`);
                if (card) {
                    card.classList.remove('unread');

                    // Update button
                    const button = card.querySelector('.btn-mark-read');
                    if (button) {
                        button.outerHTML = `
                        <button class="btn-action btn-mark-unread" onclick="markAsUnread(${queryId})">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Mark as Unread
                        </button>
                    `;
                    }

                    updateUnreadCount(-1);
                }

                // Update modal if open
                const modalButton = document.querySelector('.modal-actions .btn-secondary');
                if (modalButton && modalButton.textContent.includes('Mark as Read')) {
                    modalButton.textContent = 'Mark as Unread';
                    modalButton.onclick = () => markAsUnread(queryId);
                }

                showNotification('Marked as read', 'success');
            } else {
                showNotification(data.message || 'Failed to mark as read', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Mark query as unread
function markAsUnread(queryId) {
    const formData = new FormData();
    formData.append('query_id', queryId);

    fetch('contact-queries.php?ajax=1&action=mark_unread', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`[data-query-id="${queryId}"]`);
                if (card) {
                    card.classList.add('unread');

                    // Update button
                    const button = card.querySelector('.btn-mark-unread');
                    if (button) {
                        button.outerHTML = `
                        <button class="btn-action btn-mark-read" onclick="markAsRead(${queryId})">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Mark as Read
                        </button>
                    `;
                    }

                    updateUnreadCount(1);
                }

                // Update modal if open
                const modalButton = document.querySelector('.modal-actions .btn-secondary');
                if (modalButton && modalButton.textContent.includes('Mark as Unread')) {
                    modalButton.textContent = 'Mark as Read';
                    modalButton.onclick = () => markAsRead(queryId);
                }

                showNotification('Marked as unread', 'success');
            } else {
                showNotification(data.message || 'Failed to mark as unread', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Delete query
function deleteQuery(queryId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }

    const formData = new FormData();
    formData.append('query_id', queryId);

    fetch('contact-queries.php?ajax=1&action=delete', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove card with animation
                const card = document.querySelector(`[data-query-id="${queryId}"]`);
                if (card) {
                    const wasUnread = card.classList.contains('unread');

                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-20px)';

                    setTimeout(() => {
                        card.remove();

                        // Check if list is empty
                        const list = document.querySelector('.queries-list');
                        if (list && list.children.length === 0) {
                            list.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-icon">📧</div>
                                <h3>No messages found</h3>
                                <p>Try adjusting your filters or check back later.</p>
                            </div>
                        `;
                        }

                        if (wasUnread) {
                            updateUnreadCount(-1);
                        }
                        updateTotalCount(-1);
                    }, 300);
                }

                // Close modal if open
                closeDetailsModal();

                showNotification('Message deleted successfully', 'success');
            } else {
                showNotification(data.message || 'Failed to delete message', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Reply to query
function replyToQuery(email, subject) {
    // Construct mailto link
    const mailtoLink = `mailto:${email}?subject=Re: ${encodeURIComponent(subject)}`;
    window.location.href = mailtoLink;
}

// Close details modal
function closeDetailsModal() {
    document.getElementById('queryDetailsModal').style.display = 'none';
}

// Update unread count
function updateUnreadCount(change) {
    const unreadBadge = document.querySelector('.stat-badge.unread .stat-number');
    if (unreadBadge) {
        const currentCount = parseInt(unreadBadge.textContent);
        const newCount = Math.max(0, currentCount + change);
        animateNumber(unreadBadge, currentCount, newCount);
    }
}

// Update total count
function updateTotalCount(change) {
    const totalBadge = document.querySelector('.stat-badge.total .stat-number');
    if (totalBadge) {
        const currentCount = parseInt(totalBadge.textContent);
        const newCount = Math.max(0, currentCount + change);
        animateNumber(totalBadge, currentCount, newCount);
    }
}

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Check for new queries every 30 seconds
    setInterval(checkForNewQueries, 30000);
}

// Check for new queries
function checkForNewQueries() {
    fetch('get-new-queries.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                showNotification(`${data.count} new message(s) received`, 'info');

                // Add notification badge
                const pageTitle = document.querySelector('.page-title');
                if (pageTitle && !pageTitle.querySelector('.notification-badge')) {
                    pageTitle.insertAdjacentHTML('beforeend',
                        `<span class="notification-badge">${data.count}</span>`
                    );
                }
            }
        })
        .catch(error => console.error('Error checking for new queries:', error));
}

// Initialize keyboard shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // ESC to close modal
        if (e.key === 'Escape') {
            closeDetailsModal();
        }

        // R for refresh
        if (e.key === 'r' && !e.ctrlKey && !e.target.matches('input, textarea')) {
            e.preventDefault();
            location.reload();
        }

        // Number keys for quick filter
        if (!e.ctrlKey && !e.target.matches('input, textarea')) {
            if (e.key === '1') {
                document.querySelector('[name="status"]').value = 'all';
                document.querySelector('.filters-form').submit();
            } else if (e.key === '2') {
                document.querySelector('[name="status"]').value = 'unread';
                document.querySelector('.filters-form').submit();
            } else if (e.key === '3') {
                document.querySelector('[name="status"]').value = 'read';
                document.querySelector('.filters-form').submit();
            }
        }
    });
}

// Show loading modal
function showLoadingModal() {
    const loadingHtml = `
        <div id="loadingModal" class="modal" style="display: block;">
            <div class="modal-content" style="width: 200px; text-align: center;">
                <div class="spinner"></div>
                <p style="margin-top: 1rem;">Loading...</p>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHtml);
}

// Hide loading modal
function hideLoadingModal() {
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) {
        loadingModal.remove();
    }
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
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
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

// Export functions for global use
window.viewQuery = viewQuery;
window.markAsRead = markAsRead;
window.markAsUnread = markAsUnread;
window.deleteQuery = deleteQuery;
window.replyToQuery = replyToQuery;
window.closeDetailsModal = closeDetailsModal;

// Modal click outside handler
window.addEventListener('click', function (e) {
    const modal = document.getElementById('queryDetailsModal');
    if (e.target === modal) {
        closeDetailsModal();
    }
});

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
`;
document.head.appendChild(style);