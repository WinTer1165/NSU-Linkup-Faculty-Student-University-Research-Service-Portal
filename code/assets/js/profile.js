// assets/js/profile.js

document.addEventListener('DOMContentLoaded', function () {
    initializeProfile();
    initializeTabs();
    initializeForms();
    initializeMobileMenu();
});

// Initialize profile functionality
function initializeProfile() {
    // Initialize tooltips
    initializeTooltips();

    // Initialize file upload
    initializeFileUpload();

    // Initialize modals
    initializeModals();
}

// Initialize tab navigation
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.profile-nav-item');
    const tabContents = document.querySelectorAll('.profile-tab');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');

            // Remove active class from all buttons and tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));

            // Add active class to clicked button and corresponding tab
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
}

// Initialize forms
function initializeForms() {
    // Basic info form
    const basicForm = document.getElementById('basicInfoForm');
    if (basicForm) {
        basicForm.addEventListener('submit', handleBasicInfoSubmit);
    }

    // Education form
    const educationForm = document.getElementById('educationForm');
    if (educationForm) {
        educationForm.addEventListener('submit', handleEducationSubmit);
    }

    // Experience form
    const experienceForm = document.getElementById('experienceForm');
    if (experienceForm) {
        experienceForm.addEventListener('submit', handleExperienceSubmit);
    }

    // Achievement form
    const achievementForm = document.getElementById('achievementForm');
    if (achievementForm) {
        achievementForm.addEventListener('submit', handleAchievementSubmit);
    }

    // Publication form
    const publicationForm = document.getElementById('publicationForm');
    if (publicationForm) {
        publicationForm.addEventListener('submit', handlePublicationSubmit);
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

// Initialize file upload
function initializeFileUpload() {
    const fileInput = document.getElementById('profilePicture');
    if (fileInput) {
        fileInput.addEventListener('change', function (event) {
            handleProfilePictureUpload(event);
        });
    }
}

// Initialize modals
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }
    });
}

// Edit Basic Info
function editBasicInfo() {
    document.getElementById('basicInfoView').style.display = 'none';
    document.getElementById('basicInfoForm').style.display = 'block';
}

// Cancel Basic Edit
function cancelBasicEdit() {
    document.getElementById('basicInfoView').style.display = 'block';
    document.getElementById('basicInfoForm').style.display = 'none';
}

// Edit Education
function editEducation() {
    document.getElementById('educationView').style.display = 'none';
    document.getElementById('educationForm').style.display = 'block';
}

// Cancel Education Edit
function cancelEducationEdit() {
    document.getElementById('educationView').style.display = 'block';
    document.getElementById('educationForm').style.display = 'none';
}

// Handle Basic Info Form Submit
function handleBasicInfoSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_basic');

    showLoading('Updating basic information...');

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                updateBasicInfoView(formData);
                cancelBasicEdit();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while updating basic information.');
        });
}

// Handle Education Form Submit
function handleEducationSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_education');

    showLoading('Updating education details...');

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                updateEducationView(formData);
                cancelEducationEdit();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while updating education details.');
        });
}

// Handle Experience Form Submit
function handleExperienceSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_experience');

    showLoading('Adding experience...');

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                closeModal('experienceModal');
                addExperienceToList(formData, data.experience_id);
                document.getElementById('experienceForm').reset();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            window.location.reload();
            // showError('An error occurred while adding experience.');
        });
}

// Handle Achievement Form Submit
function handleAchievementSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_achievement');

    showLoading('Adding achievement...');

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                closeModal('achievementModal');
                addAchievementToList(formData, data.achievement_id);
                document.getElementById('achievementForm').reset();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while adding achievement.');
        });
}

// Handle Publication Form Submit
function handlePublicationSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_publication');

    showLoading('Adding publication...');

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                closeModal('publicationModal');
                addPublicationToList(formData, data.publication_id);
                document.getElementById('publicationForm').reset();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while adding publication.');
        });
}

// Handle Profile Picture Upload
function handleProfilePictureUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showError('Please select a valid image file (JPEG, PNG, or GIF).');
        return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showError('File size must be less than 5MB.');
        return;
    }

    showLoading('Uploading profile picture...');

    const formData = new FormData();
    formData.append('action', 'upload_profile_picture');
    formData.append('profile_picture', file);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                // Update profile picture display
                const pictureElement = document.querySelector('.profile-picture');
                const placeholderElement = document.querySelector('.profile-picture-placeholder');

                if (pictureElement) {
                    pictureElement.src = '../assets/uploads/profiles/' + data.filename + '?t=' + Date.now();
                } else if (placeholderElement) {
                    const img = document.createElement('img');
                    img.src = '../assets/uploads/profiles/' + data.filename + '?t=' + Date.now();
                    img.alt = 'Profile';
                    img.className = 'profile-picture';
                    placeholderElement.parentNode.replaceChild(img, placeholderElement);
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while uploading profile picture.');
        });
}

// Update Basic Info View
function updateBasicInfoView(formData) {
    const view = document.getElementById('basicInfoView');
    const phone = formData.get('phone') || 'Not provided';
    const bio = formData.get('bio') || 'No bio provided';
    const researchInterest = formData.get('research_interest') || 'No research interests specified';
    const linkedin = formData.get('linkedin');
    const github = formData.get('github');
    const address = formData.get('address') || 'Not provided';

    // Update the view elements
    view.querySelector('.info-item:nth-child(1) p').textContent = phone;
    view.querySelector('.info-item:nth-child(3) p').innerHTML = bio.replace(/\n/g, '<br>');
    view.querySelector('.info-item:nth-child(4) p').innerHTML = researchInterest.replace(/\n/g, '<br>');

    // Update LinkedIn
    const linkedinElement = view.querySelector('.info-item:nth-child(5)');
    if (linkedin) {
        linkedinElement.innerHTML = `<label>LinkedIn</label><a href="${linkedin}" target="_blank" class="social-link">View Profile →</a>`;
    } else {
        linkedinElement.innerHTML = `<label>LinkedIn</label><p>Not provided</p>`;
    }

    // Update GitHub
    const githubElement = view.querySelector('.info-item:nth-child(6)');
    if (github) {
        githubElement.innerHTML = `<label>GitHub</label><a href="${github}" target="_blank" class="social-link">View Profile →</a>`;
    } else {
        githubElement.innerHTML = `<label>GitHub</label><p>Not provided</p>`;
    }

    // Update Address
    view.querySelector('.info-item:nth-child(7) p').innerHTML = address.replace(/\n/g, '<br>');
}

// Update Education View
function updateEducationView(formData) {
    const view = document.getElementById('educationView');
    const degree = formData.get('degree') || 'Degree not specified';
    const university = formData.get('university');
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const cgpa = formData.get('cgpa');

    view.querySelector('h3').textContent = degree;
    view.querySelector('.university').textContent = university;

    const duration = view.querySelector('.education-details');
    const startText = startDate ? new Date(startDate).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'Start date not set';
    const endText = endDate ? new Date(endDate).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'Present';
    duration.innerHTML = `<span>${startText}</span><span>-</span><span>${endText}</span>`;

    const cgpaElement = view.querySelector('.cgpa');
    if (cgpa) {
        if (cgpaElement) {
            cgpaElement.textContent = `CGPA: ${parseFloat(cgpa).toFixed(2)}/4.00`;
        } else {
            const cgpaP = document.createElement('p');
            cgpaP.className = 'cgpa';
            cgpaP.textContent = `CGPA: ${parseFloat(cgpa).toFixed(2)}/4.00`;
            view.querySelector('.education-card').appendChild(cgpaP);
        }
    } else if (cgpaElement) {
        cgpaElement.remove();
    }
}

// Add Experience to List
function addExperienceToList(formData, experienceId) {
    const list = document.getElementById('experienceList');
    const position = formData.get('position');
    const company = formData.get('company');
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const description = formData.get('description');

    // Remove empty message if it exists
    const emptyMessage = list.querySelector('.empty-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const startText = new Date(startDate).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    const endText = endDate ? new Date(endDate).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'Present';

    const experienceItem = document.createElement('div');
    experienceItem.className = 'experience-item';
    experienceItem.setAttribute('data-id', experienceId);
    experienceItem.innerHTML = `
        <h3>${position}</h3>
        <p class="company">${company}</p>
        <p class="duration">${startText} - ${endText}</p>
        <p class="description">${description.replace(/\n/g, '<br>')}</p>
        <div class="item-actions">
            <button class="btn btn-sm btn-danger" onclick="deleteExperience(${experienceId})">Delete</button>
        </div>
    `;

    list.insertBefore(experienceItem, list.firstChild);
}

// Add Achievement to List
function addAchievementToList(formData, achievementId) {
    const list = document.getElementById('achievementsList');
    const title = formData.get('title');
    const type = formData.get('type');
    const description = formData.get('description');
    const date = formData.get('date');

    // Remove empty message if it exists
    const emptyMessage = list.querySelector('.empty-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const icon = type === 'certification' ? '🎓' : type === 'award' ? '🏆' : '⭐';
    const dateText = new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    const achievementItem = document.createElement('div');
    achievementItem.className = 'achievement-item';
    achievementItem.setAttribute('data-id', achievementId);
    achievementItem.innerHTML = `
        <div class="achievement-icon">${icon}</div>
        <div class="achievement-content">
            <h3>${title}</h3>
            <p>${description.replace(/\n/g, '<br>')}</p>
            <p class="achievement-date">${dateText}</p>
        </div>
        <div class="item-actions">
            <button class="btn btn-sm btn-danger" onclick="deleteAchievement(${achievementId})">Delete</button>
        </div>
    `;

    list.insertBefore(achievementItem, list.firstChild);
}

// Add Publication to List
function addPublicationToList(formData, publicationId) {
    const list = document.getElementById('publicationsList');
    const title = formData.get('title');
    const journal = formData.get('journal');
    const year = formData.get('year');
    const url = formData.get('url');

    // Remove empty message if it exists
    const emptyMessage = list.querySelector('.empty-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const publicationItem = document.createElement('div');
    publicationItem.className = 'publication-item';
    publicationItem.setAttribute('data-id', publicationId);

    let urlLink = '';
    if (url) {
        urlLink = `<a href="${url}" target="_blank" class="publication-link">View Publication →</a>`;
    }

    publicationItem.innerHTML = `
        <h3>${title}</h3>
        <p class="journal">${journal}, ${year}</p>
        ${urlLink}
        <div class="item-actions">
            <button class="btn btn-sm btn-danger" onclick="deletePublication(${publicationId})">Delete</button>
        </div>
    `;

    list.insertBefore(publicationItem, list.firstChild);
}

// Modal Functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Experience Functions
function addExperience() {
    showModal('experienceModal');
}

function deleteExperience(expId) {
    if (!confirm('Are you sure you want to delete this experience?')) {
        return;
    }

    showLoading('Deleting experience...');

    const formData = new FormData();
    formData.append('action', 'delete_experience');
    formData.append('exp_id', expId);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                document.querySelector(`[data-id="${expId}"]`).remove();

                // Check if list is empty
                const list = document.getElementById('experienceList');
                if (list.children.length === 0) {
                    list.innerHTML = '<p class="empty-message">No experience added yet.</p>';
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while deleting experience.');
        });
}

// Skills Functions
function manageSkills() {
    showModal('skillsModal');
}

function addSkill() {
    const skillInput = document.getElementById('new_skill');
    const skillName = skillInput.value.trim();

    if (!skillName) {
        showError('Please enter a skill name.');
        return;
    }

    showLoading('Adding skill...');

    const formData = new FormData();
    formData.append('action', 'add_skill');
    formData.append('skill_name', skillName);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                addSkillToList(skillName, data.skill_id);
                skillInput.value = '';
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while adding skill.');
        });
}

function quickAddSkill(skillName) {
    document.getElementById('new_skill').value = skillName;
    addSkill();
}

function addSkillToList(skillName, skillId) {
    const list = document.getElementById('skillsList');

    // Remove empty message if it exists
    const emptyMessage = list.querySelector('.empty-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    // Create skills container if it doesn't exist
    let container = list.querySelector('.skills-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'skills-container';
        list.appendChild(container);
    }

    const skillTag = document.createElement('span');
    skillTag.className = 'skill-tag';
    skillTag.setAttribute('data-id', skillId);
    skillTag.innerHTML = `
        ${skillName}
        <button class="skill-remove" onclick="removeSkill(${skillId})">&times;</button>
    `;

    container.appendChild(skillTag);
}

function removeSkill(skillId) {
    showLoading('Removing skill...');

    const formData = new FormData();
    formData.append('action', 'delete_skill');
    formData.append('skill_id', skillId);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                document.querySelector(`[data-id="${skillId}"]`).remove();

                // Check if container is empty
                const container = document.querySelector('.skills-container');
                if (container && container.children.length === 0) {
                    container.remove();
                    const list = document.getElementById('skillsList');
                    list.innerHTML = '<p class="empty-message">No skills added yet.</p>';
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while removing skill.');
        });
}

// Achievement Functions
function addAchievement() {
    showModal('achievementModal');
}

function deleteAchievement(achievementId) {
    if (!confirm('Are you sure you want to delete this achievement?')) {
        return;
    }

    showLoading('Deleting achievement...');

    const formData = new FormData();
    formData.append('action', 'delete_achievement');
    formData.append('achievement_id', achievementId);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                document.querySelector(`[data-id="${achievementId}"]`).remove();

                // Check if list is empty
                const list = document.getElementById('achievementsList');
                if (list.children.length === 0) {
                    list.innerHTML = '<p class="empty-message">No achievements added yet.</p>';
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while deleting achievement.');
        });
}

// Publication Functions
function addPublication() {
    showModal('publicationModal');
}

function deletePublication(publicationId) {
    if (!confirm('Are you sure you want to delete this publication?')) {
        return;
    }

    showLoading('Deleting publication...');

    const formData = new FormData();
    formData.append('action', 'delete_publication');
    formData.append('publication_id', publicationId);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                document.querySelector(`[data-id="${publicationId}"]`).remove();

                // Check if list is empty
                const list = document.getElementById('publicationsList');
                if (list.children.length === 0) {
                    list.innerHTML = '<p class="empty-message">No publications added yet.</p>';
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while deleting publication.');
        });
}

// Toggle end date for current position
function toggleEndDate(checkbox) {
    const endDateInput = document.getElementById('exp_end_date');
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
    }
}

// Utility Functions
function showLoading(message = 'Loading...') {
    // Create loading overlay
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
    showAlert(message, 'success');
}

function showError(message) {
    showAlert(message, 'error');
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        max-width: 400px;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: slideInRight 0.3s ease;
    `;

    const bgColor = type === 'success' ? '#d1fae5' : '#fecaca';
    const textColor = type === 'success' ? '#065f46' : '#991b1b';
    const borderColor = type === 'success' ? '#10b981' : '#ef4444';

    alert.style.background = bgColor;
    alert.style.color = textColor;
    alert.style.borderLeft = `4px solid ${borderColor}`;

    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
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

    document.body.appendChild(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
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
window.profileFunctions = {
    editBasicInfo,
    cancelBasicEdit,
    editEducation,
    cancelEducationEdit,
    addExperience,
    deleteExperience,
    manageSkills,
    addSkill,
    quickAddSkill,
    removeSkill,
    addAchievement,
    deleteAchievement,
    addPublication,
    deletePublication,
    toggleEndDate,
    showModal,
    closeModal,
    showSuccess,
    showError,
    handleProfilePictureUpload
};

// Global function for profile picture upload (needed for inline onclick)
function uploadProfilePicture(input) {
    if (input.files && input.files[0]) {
        const event = { target: input };
        handleProfilePictureUpload(event);
    }
}