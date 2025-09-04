// audit-logs.js

document.addEventListener('DOMContentLoaded', function () {
    // Initialize filter form handlers
    initializeFilters();

    // Initialize modal functionality
    initializeModal();

    // Initialize export functionality
    initializeExport();

    // Add row hover effects
    initializeTableEffects();
});

// Initialize filter functionality
function initializeFilters() {
    const filterForm = document.querySelector('.filters-form');
    const filterSelects = document.querySelectorAll('.filter-select');
    const filterInputs = document.querySelectorAll('.filter-input');

    // Auto-submit on select change
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
        });
    });

    // Handle enter key on input fields
    filterInputs.forEach(input => {
        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterForm.submit();
            }
        });
    });

    // Clear filters button functionality
    const clearButton = document.querySelector('a[href="audit-logs.php"]');
    if (clearButton) {
        clearButton.addEventListener('click', function (e) {
            e.preventDefault();
            // Clear all form inputs
            filterSelects.forEach(select => select.value = '');
            filterInputs.forEach(input => input.value = '');
            // Redirect to clean URL
            window.location.href = 'audit-logs.php';
        });
    }
}

// Show log details in modal
function showLogDetails(logId) {
    const log = window.logsData.find(l => l.log_id == logId);
    if (!log) {
        console.error('Log not found:', logId);
        return;
    }

    const modal = document.getElementById('logDetailsModal');
    const content = document.getElementById('logDetailsContent');

    let detailsHTML = `
        <div class="detail-section">
            <h3>Basic Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Log ID:</label>
                    <span>#${log.log_id}</span>
                </div>
                <div class="detail-item">
                    <label>Timestamp:</label>
                    <span>${formatDateTime(log.created_at)}</span>
                </div>
                <div class="detail-item">
                    <label>User:</label>
                    <span>${log.user_name || 'Unknown'} (${log.user_email || 'System'})</span>
                </div>
                <div class="detail-item">
                    <label>Action:</label>
                    <span class="action-badge action-${log.action.toLowerCase()}">${log.action}</span>
                </div>
                <div class="detail-item">
                    <label>Table:</label>
                    <span>${log.table_affected || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Record ID:</label>
                    <span>${log.record_id || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>IP Address:</label>
                    <span>${log.ip_address}</span>
                </div>
            </div>
        </div>
    `;

    // Add old values if present
    if (log.old_values) {
        try {
            const oldValues = JSON.parse(log.old_values);
            detailsHTML += `
                <div class="detail-section">
                    <h3>Previous Values</h3>
                    <div class="json-content">
                        <pre>${JSON.stringify(oldValues, null, 2)}</pre>
                    </div>
                </div>
            `;
        } catch (e) {
            detailsHTML += `
                <div class="detail-section">
                    <h3>Previous Values</h3>
                    <div class="json-content">
                        <pre>${escapeHtml(log.old_values)}</pre>
                    </div>
                </div>
            `;
        }
    }

    // Add new values if present
    if (log.new_values) {
        try {
            const newValues = JSON.parse(log.new_values);
            detailsHTML += `
                <div class="detail-section">
                    <h3>New Values</h3>
                    <div class="json-content">
                        <pre>${JSON.stringify(newValues, null, 2)}</pre>
                    </div>
                </div>
            `;
        } catch (e) {
            detailsHTML += `
                <div class="detail-section">
                    <h3>New Values</h3>
                    <div class="json-content">
                        <pre>${escapeHtml(log.new_values)}</pre>
                    </div>
                </div>
            `;
        }
    }

    // Add changes comparison if both old and new values exist
    if (log.old_values && log.new_values) {
        try {
            const oldValues = JSON.parse(log.old_values);
            const newValues = JSON.parse(log.new_values);
            const changes = compareValues(oldValues, newValues);

            if (changes.length > 0) {
                detailsHTML += `
                    <div class="detail-section">
                        <h3>Changes Summary</h3>
                        <table class="changes-table">
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Old Value</th>
                                    <th>New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${changes.map(change => `
                                    <tr>
                                        <td>${change.field}</td>
                                        <td class="old-value">${escapeHtml(String(change.oldValue))}</td>
                                        <td class="new-value">${escapeHtml(String(change.newValue))}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Error comparing values:', e);
        }
    }

    content.innerHTML = detailsHTML;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close details modal
function closeDetailsModal() {
    const modal = document.getElementById('logDetailsModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Initialize modal functionality
function initializeModal() {
    const modal = document.getElementById('logDetailsModal');

    // Close on click outside
    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeDetailsModal();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeDetailsModal();
        }
    });
}

// Export logs to CSV
function exportLogs() {
    // Get current filter parameters
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('export', 'csv');

    // Create download URL
    const exportUrl = 'export-audit-logs.php?' + urlParams.toString();

    // Trigger download
    window.location.href = exportUrl;
}

// Initialize export functionality
function initializeExport() {
    // You can add more export options here if needed
}

// Initialize table effects
function initializeTableEffects() {
    const rows = document.querySelectorAll('.log-row');

    rows.forEach(row => {
        row.addEventListener('mouseenter', function () {
            this.style.backgroundColor = 'rgba(99, 102, 241, 0.05)';
        });

        row.addEventListener('mouseleave', function () {
            this.style.backgroundColor = '';
        });
    });
}

// Compare old and new values
function compareValues(oldObj, newObj) {
    const changes = [];
    const allKeys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)]);

    allKeys.forEach(key => {
        if (oldObj[key] !== newObj[key]) {
            changes.push({
                field: key,
                oldValue: oldObj[key] ?? 'null',
                newValue: newObj[key] ?? 'null'
            });
        }
    });

    return changes;
}

// Format date time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Start real-time updates every 30 seconds
function startRealtimeUpdates() {
    setInterval(() => {
        console.log('Checking for new logs...');
    }, 30000);
}

// Search functionality 
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Debounce search input
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                console.log('Search query:', this.value);
            }, 500);
        });
    }
});