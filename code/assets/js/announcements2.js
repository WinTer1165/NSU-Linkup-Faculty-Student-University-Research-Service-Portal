// assets/js/announcements.js

document.addEventListener('DOMContentLoaded', function () {
    initializeToggles();
    initializeForm();
    initializeSearch();
});

// Initialize publish toggles
function initializeToggles() {
    const toggles = document.querySelectorAll('.publish-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const announcementId = this.dataset.id;
            const isPublished = this.checked;

            togglePublishStatus(announcementId, isPublished);
        });
    });
}

// Toggle publish status
function togglePublishStatus(announcementId, isPublished) {
    const formData = new FormData();
    formData.append('announcement_id', announcementId);

    fetch('announcements.php?ajax=1&action=toggle_publish', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status badge
                const card = document.querySelector(`[data-id="${announcementId}"]`);
                const badge = card.querySelector('.status-badge');

                if (data.is_published) {
                    badge.textContent = 'Published';
                    badge.classList.remove('unpublished');
                    badge.classList.add('published');
                } else {
                    badge.textContent = 'Unpublished';
                    badge.classList.remove('published');
                    badge.classList.add('unpublished');
                }

                showNotification('Status updated successfully', 'success');
            } else {
                showNotification(data.message || 'Failed to update status', 'error');
                // Revert toggle
                const toggle = document.querySelector(`[data-id="${announcementId}"]`);
                toggle.checked = !toggle.checked;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Show create modal
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Announcement';
    document.getElementById('submitBtnText').textContent = 'Create Announcement';
    document.getElementById('announcementForm').reset();
    document.getElementById('announcementForm').action.value = 'create';
    document.getElementById('announcementId').value = '';
    document.getElementById('announcementModal').style.display = 'block';
}

// Edit announcement
function editAnnouncement(announcementId) {
    const card = document.querySelector(`[data-id="${announcementId}"]`);
    const title = card.querySelector('.announcement-title').textContent;
    const content = card.querySelector('.announcement-content').textContent.trim();

    document.getElementById('modalTitle').textContent = 'Edit Announcement';
    document.getElementById('submitBtnText').textContent = 'Update Announcement';
    document.getElementById('announcementTitle').value = title;
    document.getElementById('announcementContent').value = content;
    document.getElementById('announcementId').value = announcementId;
    document.getElementById('announcementModal').style.display = 'block';
}

// Delete announcement
function deleteAnnouncement(announcementId) {
    if (!confirm('Are you sure you want to delete this announcement?')) {
        return;
    }

    const formData = new FormData();
    formData.append('announcement_id', announcementId);

    fetch('announcements.php?ajax=1&action=delete', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove card with animation
                const card = document.querySelector(`[data-id="${announcementId}"]`);
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    card.remove();

                    // Check if list is empty
                    const list = document.querySelector('.announcements-list');
                    if (list.children.length === 0) {
                        list.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">📢</div>
                            <h3>No announcements yet</h3>
                            <p>Create your first announcement to get started.</p>
                        </div>
                    `;
                    }
                }, 300);

                showNotification('Announcement deleted successfully', 'success');
                updateStats();
            } else {
                showNotification(data.message || 'Failed to delete announcement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Close modal
function closeModal() {
    document.getElementById('announcementModal').style.display = 'none';
    document.getElementById('announcementForm').reset();
}

// Initialize form
function initializeForm() {
    const form = document.getElementById('announcementForm');

    form.addEventListener('submit', function (e) {
        if (form.action.value !== 'create') {
            e.preventDefault();
            updateAnnouncement();
        }
    });

    // Close modal when clicking outside
    window.addEventListener('click', function (e) {
        const modal = document.getElementById('announcementModal');
        if (e.target === modal) {
            closeModal();
        }
    });

    // Character counter for title
    const titleInput = document.getElementById('announcementTitle');
    const titleCounter = document.createElement('span');
    titleCounter.className = 'char-counter';
    titleCounter.textContent = '0 / 200';
    titleInput.parentNode.appendChild(titleCounter);

    titleInput.addEventListener('input', function () {
        titleCounter.textContent = `${this.value.length} / 200`;
    });
}

// Update announcement
function updateAnnouncement() {
    const formData = new FormData();
    formData.append('announcement_id', document.getElementById('announcementId').value);
    formData.append('title', document.getElementById('announcementTitle').value);
    formData.append('content', document.getElementById('announcementContent').value);

    fetch('announcements.php?ajax=1&action=edit', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update card
                const announcementId = document.getElementById('announcementId').value;
                const card = document.querySelector(`[data-id="${announcementId}"]`);
                card.querySelector('.announcement-title').textContent = document.getElementById('announcementTitle').value;
                card.querySelector('.announcement-content').textContent = document.getElementById('announcementContent').value;

                closeModal();
                showNotification('Announcement updated successfully', 'success');
            } else {
                showNotification(data.message || 'Failed to update announcement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

// Initialize search functionality
function initializeSearch() {
    const searchContainer = document.createElement('div');
    searchContainer.className = 'search-container';
    searchContainer.innerHTML = `
        <input type="text" 
               id="searchInput" 
               class="search-input" 
               placeholder="Search announcements...">
    `;

    const pageHeader = document.querySelector('.page-header');
    pageHeader.appendChild(searchContainer);

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', debounce(function () {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.announcement-card');

        cards.forEach(card => {
            const title = card.querySelector('.announcement-title').textContent.toLowerCase();
            const content = card.querySelector('.announcement-content').textContent.toLowerCase();

            if (title.includes(searchTerm) || content.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }, 300));
}

// Update statistics
function updateStats() {
    const cards = document.querySelectorAll('.announcement-card');
    const published = document.querySelectorAll('.status-badge.published').length;
    const unpublished = cards.length - published;

    // Update stat boxes
    const statBoxes = document.querySelectorAll('.stat-content h3');
    if (statBoxes[0]) statBoxes[0].textContent = cards.length;
    if (statBoxes[1]) statBoxes[1].textContent = published;
    if (statBoxes[2]) statBoxes[2].textContent = unpublished;
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

    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Get notification icon based on type
function getNotificationIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
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

// Add keyboard shortcuts
document.addEventListener('keydown', function (e) {
    // Ctrl + N for new announcement
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        showCreateModal();
    }

    // ESC to close modal
    if (e.key === 'Escape') {
        const modal = document.getElementById('announcementModal');
        if (modal.style.display === 'block') {
            closeModal();
        }
    }
});

// Export functions for global use
window.showCreateModal = showCreateModal;
window.editAnnouncement = editAnnouncement;
window.deleteAnnouncement = deleteAnnouncement;
window.closeModal = closeModal;

// Add styles for notifications
const style = document.createElement('style');
style.textContent = `
    .search-container {
        margin-left: auto;
    }
    
    .search-input {
        padding: 0.5rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 0.5rem;
        width: 250px;
    }
    
    .char-counter {
        display: block;
        text-align: right;
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: var(--shadow-lg);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 2000;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .notification.success {
        border-left: 4px solid var(--success-color);
    }
    
    .notification.error {
        border-left: 4px solid var(--danger-color);
    }
    
    .notification.warning {
        border-left: 4px solid var(--warning-color);
    }
    
    .notification.info {
        border-left: 4px solid var(--info-color);
    }
    
    @media (max-width: 768px) {
        .search-container {
            width: 100%;
            margin-top: 1rem;
        }
        
        .search-input {
            width: 100%;
        }
        
        .notification {
            left: 20px;
            right: 20px;
            transform: translateY(-100px);
        }
        
        .notification.show {
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);