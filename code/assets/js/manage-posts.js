// assets/js/manage-posts.js

document.addEventListener('DOMContentLoaded', function () {
    initializeFilters();
    initializeSearch();
    initializeSorting();
    initializeMobileMenu();
});

// Initialize filter functionality
function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterPosts);
    }
}

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchPosts');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchPosts, 300));
    }
}

// Initialize sorting functionality
function initializeSorting() {
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', sortPosts);
    }
}

// Filter posts by status
function filterPosts() {
    const filterValue = document.getElementById('statusFilter').value;
    const posts = document.querySelectorAll('.post-card');

    posts.forEach(post => {
        const status = post.dataset.status;

        if (filterValue === 'all') {
            post.style.display = '';
        } else if (filterValue === status) {
            post.style.display = '';
        } else {
            post.style.display = 'none';
        }
    });

    checkEmptyState();
}

// Search posts
function searchPosts() {
    const searchTerm = document.getElementById('searchPosts').value.toLowerCase();
    const posts = document.querySelectorAll('.post-card');

    posts.forEach(post => {
        const title = post.querySelector('h3').textContent.toLowerCase();
        const description = post.querySelector('.post-description').textContent.toLowerCase();
        const department = post.querySelector('.detail-item span').textContent.toLowerCase();

        if (title.includes(searchTerm) || description.includes(searchTerm) || department.includes(searchTerm)) {
            post.style.display = '';
        } else {
            post.style.display = 'none';
        }
    });

    checkEmptyState();
}

// Sort posts
function sortPosts() {
    const sortValue = document.getElementById('sortBy').value;
    const postsContainer = document.querySelector('.posts-grid');
    if (!postsContainer) return;

    const posts = Array.from(postsContainer.querySelectorAll('.post-card'));

    posts.sort((a, b) => {
        switch (sortValue) {
            case 'newest':
                return new Date(b.dataset.created) - new Date(a.dataset.created);
            case 'oldest':
                return new Date(a.dataset.created) - new Date(b.dataset.created);
            case 'applications':
                return parseInt(b.dataset.applications) - parseInt(a.dataset.applications);
            case 'deadline':
                return new Date(a.dataset.deadline) - new Date(b.dataset.deadline);
            default:
                return 0;
        }
    });

    // Clear container and re-append sorted posts
    postsContainer.innerHTML = '';
    posts.forEach(post => postsContainer.appendChild(post));
}

// Check if posts grid is empty and show/hide empty state
function checkEmptyState() {
    const visiblePosts = document.querySelectorAll('.post-card:not([style*="display: none"])');
    const postsGrid = document.querySelector('.posts-grid');
    const emptyState = document.querySelector('.empty-state');

    if (visiblePosts.length === 0 && postsGrid) {
        // Create temporary empty state for filtered results
        if (!document.querySelector('.filter-empty-state')) {
            const filterEmpty = document.createElement('div');
            filterEmpty.className = 'empty-state filter-empty-state';
            filterEmpty.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>No Posts Found</h3>
                <p>Try adjusting your filters or search terms.</p>
            `;
            postsGrid.parentNode.appendChild(filterEmpty);
        }
        postsGrid.style.display = 'none';
    } else {
        if (postsGrid) {
            postsGrid.style.display = '';
        }
        const filterEmpty = document.querySelector('.filter-empty-state');
        if (filterEmpty) {
            filterEmpty.remove();
        }
    }
}

// Delete confirmation
function confirmDelete(researchId, title) {
    const modal = document.getElementById('deleteModal');
    const postTitle = document.getElementById('postTitle');
    const deleteIdInput = document.getElementById('deleteResearchId');

    postTitle.textContent = title;
    deleteIdInput.value = researchId;

    modal.classList.add('active');
    modal.style.display = 'block';
}

// Close delete modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
    modal.style.display = 'none';
}

// Initialize mobile menu
function initializeMobileMenu() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }
}

// Debounce function for search
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

// Export functions for global use
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        closeDeleteModal();
    }
}

// Add animation to cards on page load
window.addEventListener('load', function () {
    const cards = document.querySelectorAll('.post-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});