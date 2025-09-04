// Students Page JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        }
    });

    // Student card animations
    const studentCards = document.querySelectorAll('.student-card');

    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Add animation class and observe cards
    studentCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        observer.observe(card);
    });

    // Modal functionality
    const modal = document.getElementById('studentModal');
    const modalBody = document.getElementById('modalBody');
    const closeBtn = document.querySelector('.close');

    // View profile buttons
    document.querySelectorAll('.view-profile-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const studentId = this.dataset.studentId;
            loadStudentProfile(studentId);
        });
    });

    // Close modal
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Load student profile
    function loadStudentProfile(studentId) {
        modalBody.innerHTML = '<div class="loading">Loading student profile...</div>';
        modal.style.display = 'block';

        // AJAX request to get student details
        // Fixed path: ../api/get-student-profile.php since we're in pages directory
        fetch(`../api/get-student-profile.php?id=${studentId}`)
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                displayStudentProfile(data);
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="error">Failed to load student profile. Please try again.</div>';
            });
    }

    // Display student profile in modal
    function displayStudentProfile(data) {
        // Check if data has success property and handle accordingly
        if (data.success === false) {
            modalBody.innerHTML = `<div class="error">${data.message || 'Failed to load student profile.'}</div>`;
            return;
        }

        // Fix profile image path
        const profileImage = data.profile_image ?
            (data.profile_image.includes('assets/') ? data.profile_image.replace('../../', '../') : '../assets/uploads/profiles/' + data.profile_image) :
            '../assets/images/default-avatar.png';

        modalBody.innerHTML = `
            <div class="modal-student-profile">
                <div class="profile-header">
                    <img src="${profileImage}" 
                         alt="${data.full_name}" class="profile-avatar">
                    <div class="profile-info">
                        <h2>${data.full_name}</h2>
                        <p class="profile-degree">${data.degree || 'Not specified'}</p>
                        ${data.university ? `<p class="profile-university"><i class="fas fa-university"></i> ${data.university}</p>` : ''}
                        ${data.cgpa ? `
                            <div class="profile-cgpa">
                                <i class="fas fa-star"></i> CGPA: ${data.cgpa}
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="profile-contact">
                    <a href="mailto:${data.email}" class="contact-btn">
                        <i class="fas fa-envelope"></i> ${data.email}
                    </a>
                    ${data.phone ? `
                        <a href="tel:${data.phone}" class="contact-btn">
                            <i class="fas fa-phone"></i> ${data.phone}
                        </a>
                    ` : ''}
                    ${data.linkedin ? `
                        <a href="${data.linkedin}" target="_blank" class="contact-btn">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    ` : ''}
                    ${data.github ? `
                        <a href="${data.github}" target="_blank" class="contact-btn">
                            <i class="fab fa-github"></i> GitHub
                        </a>
                    ` : ''}
                </div>
                
                ${data.bio ? `
                    <div class="profile-section">
                        <h3>About</h3>
                        <p>${data.bio}</p>
                    </div>
                ` : ''}
                
                ${data.research_interest ? `
                    <div class="profile-section">
                        <h3>Research Interests</h3>
                        <p>${data.research_interest}</p>
                    </div>
                ` : ''}
                
                ${data.skills && data.skills.length > 0 ? `
                    <div class="profile-section">
                        <h3>Skills</h3>
                        <div class="skills-list">
                            ${data.skills.map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${data.experience && data.experience.length > 0 ? `
                    <div class="profile-section">
                        <h3>Experience</h3>
                        <div class="experience-list">
                            ${data.experience.map(exp => `
                                <div class="experience-item">
                                    <h4>${exp.position}</h4>
                                    <p class="company">${exp.company}</p>
                                    <p class="duration">${formatDate(exp.start_date)} - ${exp.end_date ? formatDate(exp.end_date) : 'Present'}</p>
                                    ${exp.description ? `<p class="description">${exp.description}</p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${data.achievements && data.achievements.length > 0 ? `
                    <div class="profile-section">
                        <h3>Achievements & Certifications</h3>
                        <div class="achievements-list">
                            ${data.achievements.map(ach => `
                                <div class="achievement-item">
                                    <span class="achievement-type ${ach.type}">${ach.type}</span>
                                    <h4>${ach.title}</h4>
                                    ${ach.description ? `<p>${ach.description}</p>` : ''}
                                    <p class="date">${formatDate(ach.date)}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${data.publications && data.publications.length > 0 ? `
                    <div class="profile-section">
                        <h3>Publications</h3>
                        <div class="publications-list">
                            ${data.publications.map(pub => `
                                <div class="publication-item">
                                    <h4>${pub.title}</h4>
                                    <p class="journal">${pub.journal}</p>
                                    <p class="year">${pub.year}</p>
                                    ${pub.url ? `<a href="${pub.url}" target="_blank" class="publication-link">View Publication</a>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Format date
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short' };
        return date.toLocaleDateString('en-US', options);
    }

    // Auto-submit filters on change
    const filterSelect = document.querySelector('.filter-select');
    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            this.closest('form').submit();
        });
    }

    // Search with debounce
    const searchInput = document.querySelector('input[name="search"]');
    const skillsInput = document.querySelector('input[name="skills"]');

    [searchInput, skillsInput].forEach(input => {
        if (input) {
            let timeout;
            input.addEventListener('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (this.value.length === 0 || this.value.length >= 3) {
                        this.closest('form').submit();
                    }
                }, 500);
            });
        }
    });

    // Skills suggestion (if implemented)
    if (skillsInput) {
        const commonSkills = ['Python', 'Java', 'JavaScript', 'React', 'Node.js', 'Machine Learning',
            'Data Science', 'AI', 'Web Development', 'Mobile Development', 'C++',
            'SQL', 'MongoDB', 'AWS', 'Docker', 'Git', 'Agile', 'UI/UX'];

        // Create datalist for suggestions
        const datalist = document.createElement('datalist');
        datalist.id = 'skills-suggestions';
        commonSkills.forEach(skill => {
            const option = document.createElement('option');
            option.value = skill;
            datalist.appendChild(option);
        });
        document.body.appendChild(datalist);
        skillsInput.setAttribute('list', 'skills-suggestions');
    }

    // Quick contact functionality
    studentCards.forEach(card => {
        const emailLink = card.querySelector('.social-link[title="Email"]');
        if (emailLink) {
            emailLink.addEventListener('click', function (e) {
                e.preventDefault();
                const email = this.href.replace('mailto:', '');
                showQuickContact(email);
            });
        }
    });

    // Show quick contact
    function showQuickContact(email) {
        const quickContact = document.createElement('div');
        quickContact.className = 'quick-contact';
        quickContact.innerHTML = `
            <div class="quick-contact-content">
                <h3>Send Quick Message</h3>
                <p>To: ${email}</p>
                <textarea placeholder="Type your message..." rows="4"></textarea>
                <div class="quick-contact-actions">
                    <button class="send-btn">Send Email</button>
                    <button class="cancel-btn">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(quickContact);

        // Handle send
        quickContact.querySelector('.send-btn').addEventListener('click', function () {
            const message = quickContact.querySelector('textarea').value;
            window.location.href = `mailto:${email}?body=${encodeURIComponent(message)}`;
            quickContact.remove();
        });

        // Handle cancel
        quickContact.querySelector('.cancel-btn').addEventListener('click', function () {
            quickContact.remove();
        });
    }

    // Toast notification
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

// Add necessary CSS for new elements
const style = document.createElement('style');
style.textContent = `
    .loading {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    
    .error {
        text-align: center;
        padding: 3rem;
        color: #ef4444;
    }
    
    .modal-student-profile {
        max-width: 100%;
    }
    
    .profile-header {
        display: flex;
        gap: 2rem;
        align-items: start;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e5e7eb;
    }
    
    .profile-info h2 {
        margin: 0 0 0.5rem;
        color: #1f2937;
    }
    
    .profile-degree {
        color: #6b7280;
        margin: 0 0 0.5rem;
    }
    
    .profile-university {
        color: #6b7280;
        margin: 0 0 0.5rem;
    }
    
    .profile-cgpa {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #fef3c7;
        color: #92400e;
        padding: 0.375rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .profile-contact {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .contact-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f3f4f6;
        color: #4b5563;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .contact-btn:hover {
        background: #667eea;
        color: white;
    }
    
    .profile-section {
        margin-bottom: 2rem;
    }
    
    .profile-section h3 {
        color: #1f2937;
        margin-bottom: 1rem;
    }
    
    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .experience-item,
    .achievement-item,
    .publication-item {
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .experience-item h4,
    .achievement-item h4,
    .publication-item h4 {
        margin: 0 0 0.5rem;
        color: #1f2937;
    }
    
    .company,
    .journal {
        color: #6b7280;
        font-weight: 500;
    }
    
    .duration,
    .date,
    .year {
        color: #9ca3af;
        font-size: 0.875rem;
    }
    
    .achievement-type {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    
    .achievement-type.certification {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .achievement-type.award {
        background: #fef3c7;
        color: #92400e;
    }
    
    .achievement-type.achievement {
        background: #d1fae5;
        color: #065f46;
    }
    
    .publication-link {
        color: #667eea;
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .publication-link:hover {
        text-decoration: underline;
    }
    
    .quick-contact {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1100;
    }
    
    .quick-contact-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
    }
    
    .quick-contact-content h3 {
        margin: 0 0 0.5rem;
    }
    
    .quick-contact-content p {
        color: #6b7280;
        margin: 0 0 1rem;
    }
    
    .quick-contact-content textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        resize: vertical;
        margin-bottom: 1rem;
    }
    
    .quick-contact-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }
    
    .send-btn,
    .cancel-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .send-btn {
        background: #667eea;
        color: white;
    }
    
    .cancel-btn {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    .toast {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: #1f2937;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .toast.show {
        transform: translateY(0);
        opacity: 1;
    }
    
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-avatar {
            margin: 0 auto;
        }
        
        .profile-contact {
            justify-content: center;
        }
    }
`;
document.head.appendChild(style);