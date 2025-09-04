// assets/js/events.js

document.addEventListener('DOMContentLoaded', function () {
    initializeEvents();
    initializeMobileMenu();
    initializeSearch();
    initializeFilters();
});

// Initialize events functionality
function initializeEvents() {
    // Initialize view details buttons
    initializeViewDetailsButtons();

    // Initialize modals
    initializeModals();

    // Initialize tooltips
    initializeTooltips();

    // Initialize real-time updates
    setInterval(checkForEventUpdates, 300000); // Check every 5 minutes

    // Initialize countdown timers
    initializeCountdowns();
}

// Initialize view details buttons
function initializeViewDetailsButtons() {
    const viewButtons = document.querySelectorAll('.view-details-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const eventId = this.getAttribute('data-event-id');
            if (eventId) {
                viewEventDetails(eventId);
            }
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
            }, 500);
        });

        // Search on Enter key
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
}

// Initialize filters
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Don't prevent default for actual navigation
            // The href will handle the navigation
        });
    });
}

// Initialize modals
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeEventModal();
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeEventModal();
        }
    });

    // Close button
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeEventModal);
    });
}

// Initialize countdown timers
function initializeCountdowns() {
    const eventCards = document.querySelectorAll('.event-card');

    eventCards.forEach(card => {
        const eventDate = card.getAttribute('data-event-date');
        if (eventDate) {
            updateCountdown(card, eventDate);
        }
    });

    // Update countdowns every minute
    setInterval(() => {
        eventCards.forEach(card => {
            const eventDate = card.getAttribute('data-event-date');
            if (eventDate) {
                updateCountdown(card, eventDate);
            }
        });
    }, 60000);
}

// Update countdown for an event
function updateCountdown(card, eventDate) {
    const now = new Date().getTime();
    const eventTime = new Date(eventDate).getTime();
    const difference = eventTime - now;

    if (difference > 0) {
        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));

        let countdownText = '';
        if (days > 0) {
            countdownText = `${days} day${days > 1 ? 's' : ''} to go`;
        } else if (hours > 0) {
            countdownText = `${hours} hour${hours > 1 ? 's' : ''} to go`;
        } else if (minutes > 0) {
            countdownText = `${minutes} minute${minutes > 1 ? 's' : ''} to go`;
        } else {
            countdownText = 'Starting soon!';
        }

        // Add or update countdown element
        let countdownElement = card.querySelector('.event-countdown');
        if (!countdownElement) {
            countdownElement = document.createElement('div');
            countdownElement.className = 'event-countdown';
            countdownElement.style.cssText = `
                position: absolute;
                bottom: 1rem;
                right: 1rem;
                background: rgba(16, 185, 129, 0.9);
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                z-index: 2;
            `;
            card.style.position = 'relative';
            card.appendChild(countdownElement);
        }

        countdownElement.textContent = countdownText;
    }
}

// View event details
function viewEventDetails(eventId) {
    showLoading('Loading event details...');

    fetch(`api/get-event-details.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showEventModal(data.event);
            } else {
                showError(data.message || 'Failed to load event details');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error loading event details');
            console.error('Error:', error);
        });
}

// Show event modal
function showEventModal(event) {
    const modal = document.getElementById('eventModal');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalBody) {
        console.error('Event modal elements not found');
        return;
    }

    modalBody.innerHTML = generateEventModalContent(event);

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Initialize modal-specific functionality
    initializeModalActions(event);
}

// Generate event modal content
function generateEventModalContent(event) {
    const eventDate = new Date(event.event_date);
    const now = new Date();
    const isPast = eventDate < now;
    const isToday = eventDate.toDateString() === now.toDateString();

    const typeIcons = {
        'hackathon': 'fa-code',
        'workshop': 'fa-tools',
        'fest': 'fa-music',
        'other': 'fa-calendar-check'
    };

    const typeIcon = typeIcons[event.type] || 'fa-calendar';

    return `
        <div class="event-modal-content">
            <div class="event-modal-header">
                <div class="event-type-indicator">
                    <span class="event-type-badge ${event.type}">
                        <i class="fas ${typeIcon}"></i>
                        ${event.type.charAt(0).toUpperCase() + event.type.slice(1)}
                    </span>
                    ${isToday ? '<span class="event-today-badge">Today!</span>' : ''}
                    ${isPast ? '<span class="event-past-badge">Completed</span>' : ''}
                </div>
                
                <h2 class="event-modal-title">${escapeHtml(event.title)}</h2>
                
                <div class="event-modal-meta">
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <div class="meta-content">
                            <div class="meta-label">Date</div>
                            <div class="meta-value">${formatEventDate(event.event_date)}</div>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="meta-content">
                            <div class="meta-label">Location</div>
                            <div class="meta-value">${escapeHtml(event.location)}</div>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <i class="fas fa-user-tie"></i>
                        <div class="meta-content">
                            <div class="meta-label">Organized by</div>
                            <div class="meta-value">${escapeHtml(event.organizer_name)}</div>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <div class="meta-content">
                            <div class="meta-label">Status</div>
                            <div class="meta-value">${isPast ? 'Completed' : isToday ? 'Today' : 'Upcoming'}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="event-description-section">
                <h3>About This Event</h3>
                <div class="event-description-full">
                    ${escapeHtml(event.description).replace(/\n/g, '<br>')}
                </div>
            </div>
            
            <div class="organizer-details">
                <h3>Organizer Information</h3>
                <div class="organizer-info">
                    <img src="${event.organizer_image || 'assets/images/default-avatar.png'}" 
                         alt="Organizer" class="organizer-avatar">
                    <div class="organizer-text">
                        <h4 class="organizer-name-full">${escapeHtml(event.organizer_name)}</h4>
                        ${event.organization ? `<p class="organizer-org-full">${escapeHtml(event.organization)}</p>` : ''}
                        ${event.organizer_email ? `
                            <a href="mailto:${escapeHtml(event.organizer_email)}" class="contact-organizer">
                                <i class="fas fa-envelope"></i> Contact Organizer
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            ${!isPast ? generateEventActions(event) : ''}
        </div>
    `;
}

// Generate event actions
function generateEventActions(event) {
    return `
        <div class="event-actions">
            <h3>Interested in this event?</h3>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="markInterested(${event.event_id})">
                    <i class="fas fa-heart"></i>
                    Mark as Interested
                </button>
                <button class="btn btn-outline" onclick="shareEvent(${event.event_id})">
                    <i class="fas fa-share"></i>
                    Share Event
                </button>
                ${event.organizer_email ? `
                    <a href="mailto:${event.organizer_email}" class="btn btn-outline">
                        <i class="fas fa-envelope"></i>
                        Contact Organizer
                    </a>
                ` : ''}
            </div>
        </div>
    `;
}

// Initialize modal actions
function initializeModalActions(event) {
    // Contact organizer links
    const contactLinks = document.querySelectorAll('.contact-organizer');
    contactLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            // You can add analytics tracking here
            window.location.href = this.href;
        });
    });

    // Social media links
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // You can add analytics tracking here
        });
    });
}

// Mark event as interested
function markInterested(eventId) {
    showLoading('Marking as interested...');

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('action', 'mark_interested');

    fetch('api/event-actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('Event marked as interested!');
                // Update button state
                const button = document.querySelector(`button[onclick="markInterested(${eventId})"]`);
                if (button) {
                    button.innerHTML = '<i class="fas fa-check"></i> Interested';
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                    button.onclick = null;
                }
            } else {
                showError(data.message || 'Failed to mark as interested');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error marking event as interested');
        });
}

// Share event
function shareEvent(eventId) {
    const eventTitle = document.querySelector('.event-modal-title').textContent;
    const eventUrl = window.location.href;

    // Check if Web Share API is supported
    if (navigator.share) {
        navigator.share({
            title: eventTitle,
            text: `Check out this event: ${eventTitle}`,
            url: eventUrl
        }).catch(err => {
            // Fallback to copying to clipboard
            copyToClipboard(eventUrl);
        });
    } else {
        // Fallback to copying to clipboard
        copyToClipboard(eventUrl);
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Event link copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showSuccess('Event link copied to clipboard!');
    });
}

// Close event modal
function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Perform live search
function performLiveSearch(query) {
    if (query.length < 2) {
        // Show all cards if query is too short
        const cards = document.querySelectorAll('.event-card');
        cards.forEach(card => card.style.display = 'block');
        return;
    }

    const cards = document.querySelectorAll('.event-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const title = card.querySelector('.event-title').textContent.toLowerCase();
        const description = card.querySelector('.event-description')?.textContent.toLowerCase() || '';
        const location = card.querySelector('.event-location')?.textContent.toLowerCase() || '';
        const organizer = card.querySelector('.organizer-name')?.textContent.toLowerCase() || '';

        const matches = title.includes(query.toLowerCase()) ||
            description.includes(query.toLowerCase()) ||
            location.includes(query.toLowerCase()) ||
            organizer.includes(query.toLowerCase());

        if (matches) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Update results info if it exists
    const resultsInfo = document.querySelector('.results-info');
    if (resultsInfo) {
        resultsInfo.textContent = `Showing ${visibleCount} events`;
    }
}

// Check for event updates
function checkForEventUpdates() {
    fetch('api/check-event-updates.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasUpdates) {
                showNotification('New events have been added!', 'info');
            }
        })
        .catch(error => {
            console.log('Update check failed:', error);
        });
}

// Utility Functions
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

function formatEventDate(dateString) {
    const date = new Date(dateString);
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return date.toLocaleDateString('en-US', options);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
window.eventsFunctions = {
    viewEventDetails,
    markInterested,
    shareEvent,
    closeEventModal,
    showSuccess,
    showError,
    showLoading,
    hideLoading
};