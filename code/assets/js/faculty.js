// assets/js/faculty.js

document.addEventListener('DOMContentLoaded', function () {
    initializeFaculty();
    initializeMobileMenu();
    initializeSearch();
    initializeFilters();
});

// Initialize faculty functionality
function initializeFaculty() {
    // Initialize view profile buttons
    initializeViewProfileButtons();

    // Initialize modals
    initializeModals();

    // Initialize tooltips
    initializeTooltips();

    // Initialize scroll animations
    initializeScrollAnimations();

    // Initialize contact buttons
    initializeContactButtons();
}

// Initialize view profile buttons
function initializeViewProfileButtons() {
    const viewButtons = document.querySelectorAll('.view-profile-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const facultyId = this.getAttribute('data-faculty-id');
            if (facultyId) {
                viewFacultyProfile(facultyId);
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
            closeFacultyModal();
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeFacultyModal();
        }
    });

    // Close button
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeFacultyModal);
    });
}

// Initialize scroll animations
function initializeScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    const cards = document.querySelectorAll('.faculty-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        card.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(card);
    });
}

// Initialize contact buttons
function initializeContactButtons() {
    const socialLinks = document.querySelectorAll('.social-link');

    socialLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // Add analytics tracking here if needed
            const platform = this.title || 'Unknown';
            console.log(`Contact link clicked: ${platform}`);
        });
    });
}

// View faculty profile
function viewFacultyProfile(facultyId) {
    showLoading('Loading faculty profile...');

    console.log('Loading profile for faculty ID:', facultyId);

    // Fixed path: ../api/get-faculty-profile.php since we're in pages directory
    fetch(`../api/get-faculty-profile.php?id=${facultyId}`)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // First get the text to see what we're receiving
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

            // Check for errors in the response
            if (data.error || (data.success === false)) {
                console.error('API Error:', data.error || data.message);
                showError(data.message || data.error || 'Failed to load faculty profile');
                return;
            }

            if (data.success && data.faculty) {
                showFacultyModal(data.faculty);
            } else {
                showError('Invalid response format');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error loading faculty profile: ' + error.message);
            console.error('Error:', error);
        });
}

// Show faculty modal
function showFacultyModal(faculty) {
    const modal = document.getElementById('facultyModal');
    let modalBody = document.getElementById('modalBody');

    // Create modal if it doesn't exist
    if (!modal) {
        createFacultyModal();
        modalBody = document.getElementById('modalBody');
    }

    modalBody.innerHTML = generateFacultyModalContent(faculty);

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Initialize modal-specific functionality
    initializeModalActions(faculty);
}

// Create faculty modal
function createFacultyModal() {
    const modal = document.createElement('div');
    modal.id = 'facultyModal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody"></div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add close functionality
    modal.querySelector('.close').addEventListener('click', closeFacultyModal);
}

// Generate faculty modal content
function generateFacultyModalContent(faculty) {
    const researchPosts = faculty.research_posts || [];

    // Fix profile image path
    const profileImage = faculty.profile_image ?
        (faculty.profile_image.includes('assets/') ? faculty.profile_image.replace('../../', '../') : '../assets/uploads/profiles/' + faculty.profile_image) :
        '../assets/images/default-avatar.png';

    return `
        <div class="faculty-modal-content">
            <div class="faculty-modal-header">
                <img src="${profileImage}" 
                     alt="Faculty" class="faculty-modal-avatar">
                <div class="faculty-modal-info">
                    <h2>${faculty.prefix ? escapeHtml(faculty.prefix) + ' ' : ''}${escapeHtml(faculty.full_name)}</h2>
                    ${faculty.title ? `<p class="faculty-modal-title">${escapeHtml(faculty.title)}</p>` : ''}
                    ${faculty.office ? `
                        <p class="faculty-modal-office">
                            <i class="fas fa-door-open"></i>
                            ${escapeHtml(faculty.office)}
                        </p>
                    ` : ''}
                    ${faculty.office_hours ? `
                        <p class="faculty-modal-office">
                            <i class="far fa-clock"></i>
                            Office Hours: ${escapeHtml(faculty.office_hours)}
                        </p>
                    ` : ''}
                </div>
            </div>
            
            <div class="faculty-modal-sections">
                ${faculty.biography ? generateBiographySection(faculty.biography) : ''}
                ${faculty.education ? generateEducationSection(faculty.education) : ''}
                ${faculty.research_interests ? generateResearchInterestsSection(faculty.research_interests) : ''}
                ${faculty.courses_taught ? generateCoursesSection(faculty.courses_taught) : ''}
                ${researchPosts.length > 0 ? generateResearchPostsSection(researchPosts) : ''}
            </div>
            
            ${generateFacultyContactSection(faculty)}
        </div>
    `;
}

// Generate biography section
function generateBiographySection(biography) {
    return `
        <div class="modal-section">
            <h3>Biography</h3>
            <p>${escapeHtml(biography).replace(/\n/g, '<br>')}</p>
        </div>
    `;
}

// Generate education section
function generateEducationSection(education) {
    return `
        <div class="modal-section">
            <h3>Education</h3>
            <p>${escapeHtml(education).replace(/\n/g, '<br>')}</p>
        </div>
    `;
}

// Generate research interests section
function generateResearchInterestsSection(interests) {
    return `
        <div class="modal-section">
            <h3>Research Interests</h3>
            <p>${escapeHtml(interests).replace(/\n/g, '<br>')}</p>
        </div>
    `;
}

// Generate courses section
function generateCoursesSection(courses) {
    return `
        <div class="modal-section">
            <h3>Courses Taught</h3>
            <p>${escapeHtml(courses).replace(/\n/g, '<br>')}</p>
        </div>
    `;
}

// Generate research posts section
function generateResearchPostsSection(researchPosts) {
    return `
        <div class="modal-section">
            <h3>Current Research Opportunities</h3>
            ${researchPosts.map(post => `
                <div class="faculty-modal-research">
                    <h4 class="research-post-title">${escapeHtml(post.title)}</h4>
                    <div class="research-post-meta">
                        ${post.department ? `<span><i class="fas fa-building"></i> ${escapeHtml(post.department)}</span>` : ''}
                        ${post.salary ? `<span><i class="fas fa-dollar-sign"></i> ${escapeHtml(post.salary)}</span>` : ''}
                        ${post.duration ? `<span><i class="far fa-clock"></i> ${escapeHtml(post.duration)}</span>` : ''}
                        <span><i class="far fa-calendar"></i> Apply by ${formatDate(post.apply_deadline)}</span>
                    </div>
                    <p class="research-post-description">${escapeHtml(post.description || '').replace(/\n/g, '<br>')}</p>
                    <a href="research.php?id=${post.research_id}" class="btn btn-primary btn-sm" style="display: inline-block; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 0.375rem; font-weight: 600; margin-top: 0.75rem;">
                        View Details
                    </a>
                </div>
            `).join('')}
        </div>
    `;
}

// Generate faculty contact section
function generateFacultyContactSection(faculty) {
    const links = [];

    if (faculty.email) {
        links.push(`
            <a href="mailto:${escapeHtml(faculty.email)}" class="modal-social-link email">
                <i class="fas fa-envelope"></i>
                Send Email
            </a>
        `);
    }

    if (faculty.linkedin) {
        links.push(`
            <a href="${escapeHtml(faculty.linkedin)}" target="_blank" class="modal-social-link linkedin">
                <i class="fab fa-linkedin"></i>
                LinkedIn Profile
            </a>
        `);
    }

    if (faculty.google_scholar) {
        links.push(`
            <a href="${escapeHtml(faculty.google_scholar)}" target="_blank" class="modal-social-link scholar">
                <i class="fas fa-graduation-cap"></i>
                Google Scholar
            </a>
        `);
    }

    if (faculty.github) {
        links.push(`
            <a href="${escapeHtml(faculty.github)}" target="_blank" class="modal-social-link github">
                <i class="fab fa-github"></i>
                GitHub Profile
            </a>
        `);
    }

    if (faculty.website) {
        links.push(`
            <a href="${escapeHtml(faculty.website)}" target="_blank" class="modal-social-link website">
                <i class="fas fa-globe"></i>
                Personal Website
            </a>
        `);
    }

    if (links.length === 0) return '';

    return `
        <div class="modal-section">
            <h3>Contact & Links</h3>
            <div class="faculty-modal-links">
                ${links.join('')}
            </div>
        </div>
    `;
}

// Initialize modal actions
function initializeModalActions(faculty) {
    // Contact tracking
    const contactLinks = document.querySelectorAll('.modal-social-link');
    contactLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Track contact attempts
            trackFacultyContact(faculty.faculty_id, this.textContent.trim());
        });
    });

    // Research post links
    const researchLinks = document.querySelectorAll('.research-post-link');
    researchLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Track research interest
            console.log(`Research post clicked from faculty profile: ${faculty.faculty_id}`);
        });
    });
}

// Track faculty contact
function trackFacultyContact(facultyId, contactType) {
    // This could be used for analytics
    console.log(`Contact initiated: ${contactType} for faculty ${facultyId}`);

    // You could send this to your analytics service
    // fetch('../api/track-contact.php', {
    //     method: 'POST',
    //     body: JSON.stringify({
    //         faculty_id: facultyId,
    //         contact_type: contactType,
    //         timestamp: new Date().toISOString()
    //     })
    // });
}

// Close faculty modal
function closeFacultyModal() {
    const modal = document.getElementById('facultyModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Perform live search
function performLiveSearch(query) {
    if (query.length < 2) {
        // Show all cards if query is too short
        const cards = document.querySelectorAll('.faculty-card');
        cards.forEach(card => card.style.display = 'block');
        updateResultsCount(cards.length);
        return;
    }

    const cards = document.querySelectorAll('.faculty-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.querySelector('.faculty-name').textContent.toLowerCase();
        const title = card.querySelector('.faculty-title')?.textContent.toLowerCase() || '';
        const interests = card.querySelector('.faculty-interests span')?.textContent.toLowerCase() || '';
        const courses = card.querySelector('.faculty-courses span')?.textContent.toLowerCase() || '';
        const departments = Array.from(card.querySelectorAll('.dept-tag')).map(tag => tag.textContent.toLowerCase()).join(' ');

        const matches = name.includes(query.toLowerCase()) ||
            title.includes(query.toLowerCase()) ||
            interests.includes(query.toLowerCase()) ||
            courses.includes(query.toLowerCase()) ||
            departments.includes(query.toLowerCase());

        if (matches) {
            card.style.display = 'block';
            visibleCount++;
            // Highlight search terms
            highlightSearchTerms(card, query);
        } else {
            card.style.display = 'none';
        }
    });

    updateResultsCount(visibleCount);
}

// Highlight search terms
function highlightSearchTerms(card, query) {
    if (query.length < 2) return;

    const elementsToHighlight = [
        card.querySelector('.faculty-name'),
        card.querySelector('.faculty-title'),
        card.querySelector('.faculty-interests span'),
        card.querySelector('.faculty-courses span')
    ].filter(el => el !== null);

    elementsToHighlight.forEach(element => {
        const originalText = element.getAttribute('data-original') || element.textContent;
        element.setAttribute('data-original', originalText);

        const highlightedText = originalText.replace(
            new RegExp(`(${escapeRegex(query)})`, 'gi'),
            '<mark style="background: #fef3c7; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">$1</mark>'
        );

        element.innerHTML = highlightedText;
    });
}

// Update results count
function updateResultsCount(count) {
    const resultsInfo = document.querySelector('.results-info');
    if (resultsInfo) {
        resultsInfo.textContent = `Showing ${count} faculty members`;
    }
}

// Filter by department
function filterByDepartment(department) {
    const cards = document.querySelectorAll('.faculty-card');
    let visibleCount = 0;

    cards.forEach(card => {
        if (!department) {
            card.style.display = 'block';
            visibleCount++;
            return;
        }

        const departments = Array.from(card.querySelectorAll('.dept-tag')).map(tag => tag.textContent.toLowerCase());
        const matches = departments.includes(department.toLowerCase());

        if (matches) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    updateResultsCount(visibleCount);
}

// Sort faculty cards
function sortFaculty(sortBy) {
    const container = document.querySelector('.faculty-grid');
    const cards = Array.from(container.querySelectorAll('.faculty-card'));

    cards.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                const nameA = a.querySelector('.faculty-name').textContent.trim();
                const nameB = b.querySelector('.faculty-name').textContent.trim();
                return nameA.localeCompare(nameB);

            case 'title':
                const titleA = a.querySelector('.faculty-title')?.textContent.trim() || '';
                const titleB = b.querySelector('.faculty-title')?.textContent.trim() || '';
                return titleA.localeCompare(titleB);

            case 'research':
                const researchA = a.querySelector('.stat-item')?.textContent.includes('Research') ? 1 : 0;
                const researchB = b.querySelector('.stat-item')?.textContent.includes('Research') ? 1 : 0;
                return researchB - researchA;

            default:
                return 0;
        }
    });

    // Re-append sorted cards
    cards.forEach(card => container.appendChild(card));
}

// Utility Functions
function formatDate(dateString) {
    if (!dateString) return '';
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

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
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
window.facultyFunctions = {
    viewFacultyProfile,
    closeFacultyModal,
    filterByDepartment,
    sortFaculty,
    showSuccess,
    showError,
    showLoading,
    hideLoading
};