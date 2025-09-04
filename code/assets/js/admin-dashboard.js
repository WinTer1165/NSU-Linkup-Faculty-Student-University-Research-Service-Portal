// admin-dashboard.js

document.addEventListener('DOMContentLoaded', function () {
    // Initialize User Growth Chart
    initializeUserGrowthChart();

    // Hide settings and profile links from navigation
    hideNavigationItems();

    // Initialize other dashboard features
    initializeDashboardFeatures();
});

// Initialize the user growth chart
function initializeUserGrowthChart() {
    const ctx = document.getElementById('userGrowthChart');

    if (!ctx || typeof growthData === 'undefined') {
        console.error('Chart canvas or growth data not found');
        return;
    }

    // Extract labels and data from growthData
    const labels = growthData.map(item => item.date);
    const data = growthData.map(item => item.count);

    // Create gradient
    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

    // Chart configuration
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Users',
                data: data,
                borderColor: '#6366f1',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#6366f1',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function (context) {
                            return 'New Users: ' + context.parsed.y;
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12
                        },
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    };

    // Create the chart
    new Chart(ctx, config);
}

// Hide settings and profile navigation items
function hideNavigationItems() {
    // Hide settings link
    const settingsLinks = document.querySelectorAll('a[href*="settings"], a[href*="Settings"]');
    settingsLinks.forEach(link => {
        const parent = link.closest('li') || link.parentElement;
        if (parent) {
            parent.style.display = 'none';
        }
    });

    // Hide profile link (but keep logout)
    const profileLinks = document.querySelectorAll('a[href*="profile"]:not([href*="logout"])');
    profileLinks.forEach(link => {
        // Make sure it's not the logout link
        const text = link.textContent.toLowerCase();
        if (!text.includes('logout') && !text.includes('log out')) {
            const parent = link.closest('li') || link.parentElement;
            if (parent) {
                parent.style.display = 'none';
            }
        }
    });

    // Alternative method: Hide by text content
    const navLinks = document.querySelectorAll('nav a, .nav a, .navbar a, .navigation a');
    navLinks.forEach(link => {
        const text = link.textContent.trim().toLowerCase();
        if (text === 'settings' || text === 'my profile' || text === 'profile') {
            const parent = link.closest('li') || link.parentElement;
            if (parent) {
                parent.style.display = 'none';
            }
        }
    });
}

// Initialize other dashboard features
function initializeDashboardFeatures() {
    // Animate stats on page load
    animateStats();

    // Add hover effects to action cards
    initializeActionCards();

    // Initialize tooltips if any
    initializeTooltips();
}

// Animate statistics numbers
function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');

    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
        if (!isNaN(finalValue)) {
            animateValue(stat, 0, finalValue, 1500);
        }
    });
}

// Animate a value from start to end
function animateValue(element, start, end, duration) {
    const range = end - start;
    const startTime = performance.now();

    function updateValue(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentValue = Math.floor(start + (range * easeOutQuart));

        element.textContent = currentValue.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    }

    requestAnimationFrame(updateValue);
}

// Initialize action cards hover effects
function initializeActionCards() {
    const actionCards = document.querySelectorAll('.action-card');

    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Initialize tooltips
function initializeTooltips() {
    // Add tooltips to action badges
    const actionBadges = document.querySelectorAll('.action-badge');

    actionBadges.forEach(badge => {
        badge.setAttribute('title', 'Click to view details');
        badge.style.cursor = 'pointer';
    });
}

// Check for updates every 30 seconds
function checkForUpdates() {
    setInterval(() => {
        console.log('Checking for updates...');
    }, 30000);
}

// Utility function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

// Handle table row clicks (for activities table)
document.addEventListener('click', function (e) {
    const row = e.target.closest('.activities-table tbody tr');
    if (row) {
        row.style.backgroundColor = 'rgba(99, 102, 241, 0.1)';
        setTimeout(() => {
            row.style.backgroundColor = '';
        }, 300);
    }
});