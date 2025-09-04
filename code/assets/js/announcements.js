// assets/js/announcements.js

document.addEventListener('DOMContentLoaded', function () {
    initializeAnnouncements();
    initializeMobileMenu();
    initializeSearch();
    markAnnouncementsAsRead();
});

// Initialize announcements functionality
function initializeAnnouncements() {
    // Initialize announcement card clicks
    initializeAnnouncementCards();

    // Initialize modals
    initializeModals();

    // Initialize tooltips
    initializeTooltips();

    // Initialize real-time updates
    setInterval(checkForNewAnnouncements, 300000); // Check every 5 minutes

    // Initialize scroll animations
    initializeScrollAnimations();
}

// Initialize announcement card clicks
function initializeAnnouncementCards() {
    const announcementCards = document.querySelectorAll('.announcement-card');

    announcementCards.forEach(card => {
        card.addEventListener('click', function (e) {
            // Don't trigger if clicking on action buttons
            if (!e.target.closest('.announcement-actions') && !e.target.closest('.action-btn')) {
                const announcementId = this.getAttribute('data-id');
                if (announcementId) {
                    viewAnnouncementDetails(announcementId);
                }
            }
        });

        // Add hover effect for better UX
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
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

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performLiveSearch(this.value);
            }, 300);
        });

        // Clear search
        const clearSearch = document.querySelector('.clear-search');
        if (clearSearch) {
            clearSearch.addEventListener('click', function () {
                searchInput.value = '';
                performLiveSearch('');
            });
        }
    }
}

// Initialize modals
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeAnnouncementModal();
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAnnouncementModal();
        }
    });

    // Close button
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeAnnouncementModal);
    });
}

// Initialize scroll animations
function initializeScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideInUp 0.6s ease forwards';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    const cards = document.querySelectorAll('.announcement-card');
    cards.forEach(card => {
        observer.observe(card);
    });
}

// Mark announcements as read when viewed
function markAnnouncementsAsRead() {
    const unreadCards = document.querySelectorAll('.announcement-card.unread');

    if (unreadCards.length > 0) {
        // Mark as read after 3 seconds of being visible
        setTimeout(() => {
            const announcementIds = Array.from(unreadCards).map(card => card.getAttribute('data-id'));
            markAsRead(announcementIds);
        }, 3000);
    }
}

// Mark announcements as read
function markAsRead(announcementIds) {
    if (announcementIds.length === 0) return;

    const formData = new FormData();
    formData.append('action', 'mark_read');
    formData.append('announcement_ids', JSON.stringify(announcementIds));

    fetch('api/announcement-actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread styling
                announcementIds.forEach(id => {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    if (card) {
                        card.classList.remove('unread');
                    }
                });
            }
        })
        .catch(error => {
            console.log('Failed to mark announcements as read:', error);
        });
}

// View announcement details
function viewAnnouncementDetails(announcementId) {
    showLoading('Loading announcement details...');

    fetch(`api/get-announcement-details.php?id=${announcementId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAnnouncementModal(data.announcement);
                markAsRead([announcementId]);
            } else {
                showError(data.message || 'Failed to load announcement details');
            }
        })
        .catch(error => {
            hideLoading();
            // showError('Error loading announcement details');
            console.error('Error:', error);
        });
}

// Show announcement modal
function showAnnouncementModal(announcement) {
    const modal = document.getElementById('announcementModal');
    let modalBody = document.getElementById('modalBody');

    // Create modal if it doesn't exist
    if (!modal) {
        createAnnouncementModal();
        modalBody = document.getElementById('modalBody');
    }

    modalBody.innerHTML = generateAnnouncementModalContent(announcement);

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Initialize modal-specific functionality
    initializeModalActions(announcement);
}

// Create announcement modal
function createAnnouncementModal() {
    const modal = document.createElement('div');
    modal.id = 'announcementModal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody"></div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add close functionality
    modal.querySelector('.close').addEventListener('click', closeAnnouncementModal);
}

// Generate announcement modal content
function generateAnnouncementModalContent(announcement) {
    const createdDate = new Date(announcement.created_at);
    const timeAgo = getTimeAgo(createdDate);

    return `
        <div class="announcement-modal-content">
            <div class="announcement-modal-header">
                <h2 class="announcement-modal-title">${escapeHtml(announcement.title)}</h2>
                <div class="announcement-modal-meta">
                    <div class="meta-item">
                        <i class="fas fa-user-shield"></i>
                        <span>Published by ${escapeHtml(announcement.admin_name)}</span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <span>${formatDate(announcement.created_at)}</span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <span>${timeAgo}</span>
                    </div>
                </div>
            </div>
            
            <div class="announcement-modal-content-text">
                ${formatContent(announcement.content)}
            </div>
            
            <div class="announcement-modal-footer">
                <div class="announcement-tags">
                    ${generateAnnouncementTags(announcement)}
                </div>
                <div class="announcement-actions-modal">
                    <button class="btn btn-outline" onclick="shareAnnouncement(${announcement.announcement_id})">
                        <i class="fas fa-share"></i>
                        Share
                    </button>
                    <button class="btn btn-outline" onclick="bookmarkAnnouncement(${announcement.announcement_id})">
                        <i class="fas fa-bookmark"></i>
                        Bookmark
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Generate announcement tags based on content
function generateAnnouncementTags(announcement) {
    const tags = [];
    const content = announcement.content.toLowerCase();
    const title = announcement.title.toLowerCase();

    // Auto-generate tags based on keywords
    if (content.includes('exam') || title.includes('exam')) {
        tags.push('Examinations');
    }
    if (content.includes('registration') || title.includes('registration')) {
        tags.push('Registration');
    }
    if (content.includes('event') || title.includes('event')) {
        tags.push('Events');
    }
    if (content.includes('deadline') || title.includes('deadline')) {
        tags.push('Important');
    }
    if (content.includes('holiday') || title.includes('holiday')) {
        tags.push('Holiday');
    }
    if (content.includes('scholarship') || title.includes('scholarship')) {
        tags.push('Scholarship');
    }

    return tags.map(tag => `<span class="announcement-tag">${tag}</span>`).join('');
}

// Initialize modal actions
function initializeModalActions(announcement) {
    // Any additional modal-specific event listeners can be added here
}

// Close announcement modal
function closeAnnouncementModal() {
    const modal = document.getElementById('announcementModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Perform live search
function performLiveSearch(query) {
    const cards = document.querySelectorAll('.announcement-card');
    let visibleCount = 0;

    cards.forEach(card => {
        if (query.length < 2) {
            card.style.display = 'block';
            visibleCount++;
            return;
        }

        const title = card.querySelector('.announcement-title').textContent.toLowerCase();
        const content = card.querySelector('.announcement-content').textContent.toLowerCase();
        const author = card.querySelector('.announcement-author').textContent.toLowerCase();

        const matches = title.includes(query.toLowerCase()) ||
            content.includes(query.toLowerCase()) ||
            author.includes(query.toLowerCase());

        if (matches) {
            card.style.display = 'block';
            visibleCount++;
            // Highlight search terms
            highlightSearchTerms(card, query);
        } else {
            card.style.display = 'none';
        }
    });

    // Update results count
    updateResultsCount(visibleCount);

    // Show no results message if needed
    showNoResultsMessage(visibleCount === 0 && query.length >= 2);
}

// Highlight search terms
function highlightSearchTerms(card, query) {
    if (query.length < 2) return;

    const title = card.querySelector('.announcement-title');
    const content = card.querySelector('.announcement-content');

    [title, content].forEach(element => {
        const originalText = element.getAttribute('data-original') || element.textContent;
        element.setAttribute('data-original', originalText);

        const highlightedText = originalText.replace(
            new RegExp(`(${escapeRegex(query)})`, 'gi'),
            '<mark style="background: #fef3c7; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">$1</mark>'
        );

        element.innerHTML = highlightedText;
    });
}

// Clear search highlights
function clearSearchHighlights() {
    const elementsWithHighlight = document.querySelectorAll('[data-original]');
    elementsWithHighlight.forEach(element => {
        element.textContent = element.getAttribute('data-original');
        element.removeAttribute('data-original');
    });
}

// Update results count
function updateResultsCount(count) {
    let resultsInfo = document.querySelector('.search-results-info');
    if (!resultsInfo) {
        resultsInfo = document.createElement('div');
        resultsInfo.className = 'search-results-info';
        resultsInfo.style.cssText = `
            margin: 1rem 0;
            color: #64748b;
            font-weight: 600;
            text-align: center;
        `;
        document.querySelector('.announcements-container').insertBefore(
            resultsInfo,
            document.querySelector('.announcements-grid')
        );
    }

    resultsInfo.textContent = `Showing ${count} announcements`;
}

// Show no results message
function showNoResultsMessage(show) {
    let noResults = document.querySelector('.no-search-results');

    if (show && !noResults) {
        noResults = document.createElement('div');
        noResults.className = 'no-search-results no-data';
        noResults.innerHTML = `
            <div class="no-data-icon">
                <i class="fas fa-search"></i>
            </div>
            <h2>No announcements found</h2>
            <p>Try adjusting your search terms.</p>
        `;
        document.querySelector('.announcements-grid').appendChild(noResults);
    } else if (!show && noResults) {
        noResults.remove();
    }
}

// Share announcement
function shareAnnouncement(announcementId) {
    const title = document.querySelector('.announcement-modal-title').textContent;
    const url = `${window.location.origin}${window.location.pathname}?announcement=${announcementId}`;

    if (navigator.share) {
        navigator.share({
            title: title,
            text: `Check out this announcement: ${title}`,
            url: url
        }).catch(err => {
            copyToClipboard(url);
        });
    } else {
        copyToClipboard(url);
    }
}

// Bookmark announcement
function bookmarkAnnouncement(announcementId) {
    showLoading('Bookmarking announcement...');

    const formData = new FormData();
    formData.append('action', 'bookmark');
    formData.append('announcement_id', announcementId);

    fetch('api/announcement-actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('Announcement bookmarked!');
                // Update button state
                const button = document.querySelector(`button[onclick="bookmarkAnnouncement(${announcementId})"]`);
                if (button) {
                    button.innerHTML = '<i class="fas fa-bookmark"></i> Bookmarked';
                    button.classList.add('bookmarked');
                    button.onclick = null;
                }
            } else {
                showError(data.message || 'Failed to bookmark announcement');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error bookmarking announcement');
        });
}

// Check for new announcements
function checkForNewAnnouncements() {
    fetch('api/check-new-announcements.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                showNotification(`${data.count} new announcement${data.count > 1 ? 's' : ''} available!`, 'info');
                // Optionally reload the page or add new announcements dynamically
            }
        })
        .catch(error => {
            console.log('Failed to check for new announcements:', error);
        });
}

// Utility Functions
function formatContent(content) {
    // Convert line breaks to HTML
    let formatted = escapeHtml(content).replace(/\n/g, '<br>');

    // Convert URLs to links
    formatted = formatted.replace(
        /(https?:\/\/[^\s]+)/g,
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
    );

    // Convert email addresses to mailto links
    formatted = formatted.replace(
        /([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g,
        '<a href="mailto:$1">$1</a>'
    );

    return formatted;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getTimeAgo(date) {
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else {
        return 'Just now';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Link copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showSuccess('Link copied to clipboard!');
    });
}

function showLoading(message = 'Loading...') {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    `;

    overlay.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);">
            <div style="margin-bottom: 1rem;">
                <div style="width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top: 3px solid #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            </div>
            <p style="margin: 0; color: #64748b; font-weight: 600;">${message}</p>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;

    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showError(message) {
    showNotification(message, 'error');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        max-width: 400px;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: slideInRight 0.3s ease;
        cursor: pointer;
    `;

    const bgColor = type === 'success' ? '#d1fae5' : type === 'error' ? '#fecaca' : '#dbeafe';
    const textColor = type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#1e40af';
    const borderColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';

    notification.style.background = bgColor;
    notification.style.color = textColor;
    notification.style.borderLeft = `4px solid ${borderColor}`;

    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-${icon}"></i>
            <span style="flex: 1; font-weight: 600;">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: ${textColor}; cursor: pointer; font-size: 1.25rem; opacity: 0.7; transition: opacity 0.3s ease;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <style>
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);

    // Remove on click
    notification.addEventListener('click', () => {
        notification.remove();
    });
}

function initializeTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');

    elements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    if (!text) return;

    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #1e3a8a;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    `;

    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';

    setTimeout(() => tooltip.style.opacity = '1', 10);
}

function hideTooltip() {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Export functions for global access
window.announcementsFunctions = {
    viewAnnouncementDetails,
    shareAnnouncement,
    bookmarkAnnouncement,
    closeAnnouncementModal,
    showSuccess,
    showError,
    showLoading,
    hideLoading
};