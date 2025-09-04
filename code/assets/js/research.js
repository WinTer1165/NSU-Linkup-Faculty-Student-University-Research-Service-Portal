// assets/js/research.js

document.addEventListener('DOMContentLoaded', function () {
    initializeResearch();
    initializeMobileMenu();
    initializeSearch();
    initializeFilters();
    initializeApplicationButtons();
});

// Initialize research functionality
function initializeResearch() {
    // Initialize view details buttons
    initializeViewDetailsButtons();

    // Initialize modals
    initializeModals();

    // Initialize tooltips
    initializeTooltips();

    // Set user type from body attribute or PHP variable
    const userTypeElement = document.querySelector('body');
    if (userTypeElement) {
        const userType = userTypeElement.getAttribute('data-user-type') ||
            (typeof USER_TYPE !== 'undefined' ? USER_TYPE : 'student');
        userTypeElement.setAttribute('data-user-type', userType);
    }
}

// Initialize view details buttons
function initializeViewDetailsButtons() {
    const viewButtons = document.querySelectorAll('.view-details-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            if (!this.disabled) {
                const researchId = this.getAttribute('data-research-id');
                if (researchId) {
                    viewResearchDetails(researchId);
                }
            }
        });
    });
}

// Initialize application buttons (for the main page)
function initializeApplicationButtons() {
    const applyButtons = document.querySelectorAll('.apply-btn');

    applyButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const researchId = this.getAttribute('data-research-id');
            const researchTitle = this.getAttribute('data-research-title');

            if (researchId) {
                showApplicationModal(researchId, researchTitle);
            }
        });
    });
}

// Show application modal (for the Apply Now button on main page)
function showApplicationModal(researchId, researchTitle) {
    const modal = document.getElementById('applicationModal');
    const researchIdInput = document.getElementById('apply_research_id');
    const researchTitleInput = document.getElementById('research_title_display');

    if (modal && researchIdInput && researchTitleInput) {
        researchIdInput.value = researchId;
        researchTitleInput.value = researchTitle;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Close modal helper function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
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
    }
}

// Initialize filters
function initializeFilters() {
    const filterForm = document.querySelector('.filters-form');
    if (filterForm) {
        // Auto-submit on filter change
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    }
}

// Initialize modals
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            if (e.target.id === 'researchModal') {
                closeResearchModal();
            } else if (e.target.id === 'applicationModal') {
                closeModal('applicationModal');
            }
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeResearchModal();
            closeModal('applicationModal');
        }
    });

    // Close buttons
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                if (modal.id === 'researchModal') {
                    closeResearchModal();
                } else if (modal.id === 'applicationModal') {
                    closeModal('applicationModal');
                }
            }
        });
    });
}

// View research details
function viewResearchDetails(researchId) {
    showLoading('Loading research details...');

    console.log('Loading research details for ID:', researchId);

    // Fixed path: ../api/ since we're in pages directory
    fetch(`../api/get-research-details.php?id=${researchId}`)
        .then(response => {
            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.text().then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.error('Response was:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            hideLoading();
            console.log('Parsed data:', data);

            if (data.success) {
                showResearchModal(data.research);
            } else {
                showError(data.message || 'Failed to load research details');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error loading research details: ' + error.message);
            console.error('Error:', error);
        });
}

// Show research modal
function showResearchModal(research) {
    const modal = document.getElementById('researchModal');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalBody) {
        console.error('Research modal elements not found');
        return;
    }

    // Get user type
    const userType = getUserType();

    modalBody.innerHTML = generateResearchModalContent(research, userType);

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Initialize modal-specific functionality
    initializeModalActions(research.research_id);
}

// Generate research modal content
function generateResearchModalContent(research, userType) {
    const tagsArray = research.tags ? research.tags.split(',') : [];

    // Fix faculty image path
    const facultyImage = research.faculty_image || '../assets/images/default-avatar.png';

    let applicationSection = '';
    if (userType === 'student') {
        applicationSection = generateApplicationSection(research);
    }

    return `
        <div class="research-modal-content">
            <div class="research-modal-header">
                <div class="research-title-section">
                    <h2>${escapeHtml(research.title)}</h2>
                    <div class="research-meta-info">
                        <span class="department-badge">${escapeHtml(research.department || 'Department not specified')}</span>
                        ${research.salary ? `<span class="salary-badge">💰 ${escapeHtml(research.salary)}</span>` : ''}
                    </div>
                </div>
                
                <div class="faculty-card">
                    <img src="${facultyImage}" 
                         alt="Faculty" class="faculty-image">
                    <div class="faculty-info">
                        <h3>${escapeHtml(research.faculty_name)}</h3>
                        ${research.office ? `<p class="faculty-office">${escapeHtml(research.office)}</p>` : ''}
                        <a href="mailto:${escapeHtml(research.faculty_email)}" class="contact-faculty">
                            <i class="fas fa-envelope"></i> Contact Faculty
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="research-details-grid">
                <div class="research-main-content">
                    <div class="description-section">
                        <h3>Research Description</h3>
                        <div class="description-content">
                            ${escapeHtml(research.description || 'No description available').replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    
                    ${research.student_roles ? `
                        <div class="roles-section">
                            <h3>Student Roles & Responsibilities</h3>
                            <div class="roles-content">
                                ${escapeHtml(research.student_roles).replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    ` : ''}
                    
                    ${tagsArray.length > 0 ? `
                        <div class="tags-section">
                            <h3>Research Areas</h3>
                            <div class="tags-list">
                                ${tagsArray.map(tag => `<span class="research-tag">${escapeHtml(tag.trim())}</span>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
                
                <div class="research-sidebar">
                    <div class="requirements-card">
                        <h3>Requirements & Details</h3>
                        <div class="requirement-item">
                            <i class="fas fa-users"></i>
                            <span>Positions Available: <strong>${research.number_required || 'Not specified'}</strong></span>
                        </div>
                        ${research.min_cgpa ? `
                            <div class="requirement-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Minimum CGPA: <strong>${research.min_cgpa}</strong></span>
                            </div>
                        ` : ''}
                        <div class="requirement-item">
                            <i class="fas fa-clock"></i>
                            <span>Duration: <strong>${escapeHtml(research.duration || 'Not specified')}</strong></span>
                        </div>
                        <div class="requirement-item deadline-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Application Deadline: <strong>${formatDate(research.apply_deadline)}</strong></span>
                        </div>
                        ${research.application_count ? `
                            <div class="requirement-item">
                                <i class="fas fa-file-alt"></i>
                                <span>Applications Received: <strong>${research.application_count}</strong></span>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${applicationSection}
                </div>
            </div>
        </div>
    `;
}

// Generate application section for students
function generateApplicationSection(research) {
    const deadlinePassed = new Date(research.apply_deadline) < new Date();
    const hasApplied = research.has_applied == 1 || research.has_applied === true;
    const applicationStatus = research.application_status;

    if (deadlinePassed) {
        return `
            <div class="application-card deadline-passed">
                <h3>Application Closed</h3>
                <p>The application deadline for this research opportunity has passed.</p>
                <div class="deadline-info">
                    <i class="fas fa-calendar-times"></i>
                    Deadline was: ${formatDate(research.apply_deadline)}
                </div>
            </div>
        `;
    }

    if (hasApplied) {
        return `
            <div class="application-card already-applied">
                <h3>Application Submitted</h3>
                <p>You have already applied for this research opportunity.</p>
                <div class="application-status">
                    <i class="fas fa-check-circle"></i>
                    Status: <span class="status-${applicationStatus}">${applicationStatus ? applicationStatus.charAt(0).toUpperCase() + applicationStatus.slice(1) : 'Pending'}</span>
                </div>
                <button class="btn btn-outline" onclick="viewMyApplication(${research.research_id})">
                    View My Application
                </button>
            </div>
        `;
    }

    return `
        <div class="application-card">
            <h3>Apply for this Research</h3>
            <p>Submit your application to join this research opportunity.</p>
            
            <form id="modalApplicationForm" class="application-form">
                <div class="form-group">
                    <label for="modalCoverLetter">Cover Letter</label>
                    <textarea id="modalCoverLetter" name="cover_letter" rows="6" 
                              placeholder="Explain your interest in this research, relevant experience, and what you hope to contribute..."
                              required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Submit Application
                    </button>
                    <button type="button" class="btn btn-outline" onclick="closeResearchModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `;
}

// Initialize modal actions
function initializeModalActions(researchId) {
    const applicationForm = document.getElementById('modalApplicationForm');
    if (applicationForm) {
        applicationForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitApplication(researchId);
        });
    }
}

// Submit application
function submitApplication(researchId) {
    const coverLetterElement = document.getElementById('modalCoverLetter');

    if (!coverLetterElement) {
        showError('Form element not found');
        return;
    }

    const coverLetter = coverLetterElement.value.trim();

    if (!coverLetter) {
        showError('Please write a cover letter for your application.');
        return;
    }

    showLoading('Submitting your application...');

    const formData = new FormData();
    formData.append('research_id', researchId);
    formData.append('cover_letter', coverLetter);

    // Fixed path: ../api/ since we're in pages directory
    fetch('../api/submit-application.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.error('Response was:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('Application submitted successfully!');
                closeResearchModal();
                // Refresh the page to update the research card
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showError(data.message || 'Failed to submit application');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error submitting application: ' + error.message);
            console.error('Error:', error);
        });
}

// View my application
function viewMyApplication(researchId) {
    showLoading('Loading your application...');

    // Fixed path: ../api/ since we're in pages directory
    fetch(`../api/get-my-application.php?research_id=${researchId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.error('Response was:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                showApplicationDetailsModal(data.application);
            } else {
                showError(data.message || 'Failed to load application details');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error loading application details: ' + error.message);
            console.error('Error:', error);
        });
}

// Show application details modal
function showApplicationDetailsModal(application) {
    const modal = document.getElementById('researchModal');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalBody) {
        console.error('Modal elements not found');
        return;
    }

    const statusColor = {
        'pending': '#f59e0b',
        'accepted': '#10b981',
        'rejected': '#ef4444'
    };

    modalBody.innerHTML = `
        <div class="application-details-modal">
            <h2>Your Application Details</h2>
            
            <div class="application-info">
                <div class="info-section">
                    <h3>Research Position</h3>
                    <p class="research-title">${escapeHtml(application.research_title)}</p>
                    <p class="faculty-info">Faculty: ${escapeHtml(application.faculty_name)}</p>
                </div>
                
                <div class="info-section">
                    <h3>Application Status</h3>
                    <div class="status-badge" style="background-color: ${statusColor[application.status]}; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; display: inline-block; font-weight: 600;">
                        ${application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                    </div>
                    <p class="date-info">Applied on: ${formatDate(application.applied_at)}</p>
                    ${application.reviewed_at ? `<p class="date-info">Reviewed on: ${formatDate(application.reviewed_at)}</p>` : ''}
                </div>
                
                <div class="info-section">
                    <h3>Your Cover Letter</h3>
                    <div class="cover-letter-content">
                        ${escapeHtml(application.cover_letter).replace(/\n/g, '<br>')}
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-outline" onclick="closeResearchModal()">Close</button>
                    ${application.status === 'pending' ? `
                        <a href="mailto:${escapeHtml(application.faculty_email)}" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Contact Faculty
                        </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close research modal
function closeResearchModal() {
    const modal = document.getElementById('researchModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Perform live search
function performLiveSearch(query) {
    if (query.length < 2) return;

    const cards = document.querySelectorAll('.research-card');

    cards.forEach(card => {
        const title = card.querySelector('.research-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.research-description')?.textContent.toLowerCase() || '';
        const tags = card.querySelector('.research-tags')?.textContent.toLowerCase() || '';

        const matches = title.includes(query.toLowerCase()) ||
            description.includes(query.toLowerCase()) ||
            tags.includes(query.toLowerCase());

        card.style.display = matches ? 'block' : 'none';
    });

    // Update results count
    const visibleCards = document.querySelectorAll('.research-card:not([style*="display: none"])');
    const resultsInfo = document.querySelector('.results-info');
    if (resultsInfo) {
        resultsInfo.textContent = `Showing ${visibleCards.length} research opportunities`;
    }
}

// Get user type
function getUserType() {
    // This should be set by PHP or stored in a data attribute
    return document.body.getAttribute('data-user-type') || 'student';
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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function escapeHtml(text) {
    if (!text) return '';
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
window.researchFunctions = {
    viewResearchDetails,
    submitApplication,
    viewMyApplication,
    closeResearchModal,
    showSuccess,
    showError,
    showLoading,
    hideLoading
};

// Also make individual functions globally accessible
window.viewMyApplication = viewMyApplication;
window.closeResearchModal = closeResearchModal;
window.closeModal = closeModal;