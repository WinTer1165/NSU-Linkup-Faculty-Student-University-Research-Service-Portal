// assets/js/chatbot.js

class NSUChatbot {
    constructor() {
        this.messagesContainer = document.getElementById('chatMessages');
        this.chatForm = document.getElementById('chatForm');
        this.chatInput = document.getElementById('chatInput');
        this.sendButton = document.getElementById('sendButton');
        this.typingIndicator = document.getElementById('typingIndicator');
        this.quickActionButtons = document.querySelectorAll('.quick-action-btn');

        this.initializeEventListeners();
        this.conversationContext = [];
    }

    initializeEventListeners() {
        // Form submission
        this.chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // Quick action buttons
        this.quickActionButtons.forEach(button => {
            button.addEventListener('click', () => {
                const message = button.getAttribute('data-message');
                this.chatInput.value = message;
                this.sendMessage();
            });
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.querySelector('.sidebar');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
    }

    sendMessage() {
        const message = this.chatInput.value.trim();

        if (!message) return;

        // Add user message to chat
        this.addMessage(message, 'user');

        // Clear input
        this.chatInput.value = '';

        // Show typing indicator
        this.showTypingIndicator();

        // Disable send button
        this.sendButton.disabled = true;

        // Send to API
        this.sendToAPI(message);
    }

    async sendToAPI(message) {
        try {
            // First, test if API is reachable
            const testResponse = await fetch('../api/test.php');
            if (!testResponse.ok) {
                console.error('API test failed:', testResponse.status);
            } else {
                const testData = await testResponse.json();
                console.log('API test response:', testData);
            }

            const response = await fetch('../api/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    context: this.conversationContext
                })
            });

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', responseText);
                throw new Error('Server returned invalid JSON. Check console for details.');
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Hide typing indicator
            this.hideTypingIndicator();

            if (data.success) {
                // Add bot response
                this.addMessage(data.response, 'bot', data.data);

                // Update conversation context
                this.conversationContext.push({
                    user: message,
                    assistant: data.response
                });

                // Keep only last 10 messages for context
                if (this.conversationContext.length > 10) {
                    this.conversationContext = this.conversationContext.slice(-10);
                }
            } else {
                console.error('API error:', data);
                const errorMessage = data.error || 'Sorry, I encountered an error. Please try again.';
                const debugInfo = data.debug ? `\n\nDebug info: ${JSON.stringify(data.debug)}` : '';
                this.addMessage(errorMessage + debugInfo, 'bot');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            this.hideTypingIndicator();
            const errorMessage = `Error: ${error.message}\n\nPlease check the browser console for more details.`;
            this.addMessage(errorMessage, 'bot');
        } finally {
            // Re-enable send button
            this.sendButton.disabled = false;
            this.chatInput.focus();
        }
    }

    addMessage(message, type, data = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;

        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        if (type === 'bot') {
            // Bot message with avatar
            messageDiv.innerHTML = `
                <div class="bot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    ${this.formatBotMessage(message, data)}
                    <div class="message-time">${currentTime}</div>
                </div>
            `;
        } else {
            // User message
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${this.escapeHtml(message)}
                    <div class="message-time">${currentTime}</div>
                </div>
            `;
        }

        // Remove welcome message if it exists
        const welcomeMessage = this.messagesContainer.querySelector('.welcome-message');
        if (welcomeMessage) {
            welcomeMessage.remove();
        }

        this.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    formatBotMessage(message, data) {
        let formattedMessage = this.escapeHtml(message);

        // Replace newlines with <br>
        formattedMessage = formattedMessage.replace(/\n/g, '<br>');

        // Format faculty information if present
        if (data && data.faculty) {
            formattedMessage += this.formatFacultyInfo(data.faculty);
        }

        // Format research posts if present
        if (data && data.research) {
            formattedMessage += this.formatResearchInfo(data.research);
        }

        // Format skills if present
        if (data && data.skills) {
            formattedMessage += this.formatSkillsInfo(data.skills);
        }

        return formattedMessage;
    }

    formatFacultyInfo(faculty) {
        let html = '';

        if (Array.isArray(faculty)) {
            faculty.forEach(f => {
                html += `
                    <div class="info-card">
                        <h4>${f.prefix || ''} ${f.full_name}</h4>
                        ${f.title ? `<p><span class="label">Title:</span> ${f.title}</p>` : ''}
                        ${f.office ? `<p><span class="label">Office:</span> ${f.office}</p>` : ''}
                        ${f.email ? `<p><span class="label">Email:</span> ${f.email}</p>` : ''}
                        ${f.phone ? `<p><span class="label">Phone:</span> ${f.phone}</p>` : ''}
                        ${f.office_hours ? `<p><span class="label">Office Hours:</span> ${f.office_hours}</p>` : ''}
                        ${f.research_interests ? `<p><span class="label">Research Areas:</span> ${f.research_interests}</p>` : ''}
                    </div>
                `;
            });
        }

        return html;
    }

    formatResearchInfo(research) {
        let html = '';

        if (Array.isArray(research)) {
            research.forEach(r => {
                const matchPercentage = r.match_percentage || null;
                html += `
                    <div class="research-match">
                        <h4>${r.title}
                            ${matchPercentage ? `<span class="match-percentage">${matchPercentage}% Match</span>` : ''}
                        </h4>
                        <p><span class="label">Faculty:</span> ${r.faculty_name}</p>
                        <p><span class="label">Department:</span> ${r.department}</p>
                        ${r.min_cgpa ? `<p><span class="label">Min CGPA:</span> ${r.min_cgpa}</p>` : ''}
                        ${r.duration ? `<p><span class="label">Duration:</span> ${r.duration}</p>` : ''}
                        ${r.salary ? `<p><span class="label">Salary:</span> ${r.salary}</p>` : ''}
                        ${r.apply_deadline ? `<p><span class="label">Deadline:</span> ${this.formatDate(r.apply_deadline)}</p>` : ''}
                        ${r.tags ? `<p><span class="label">Required Skills:</span> ${r.tags}</p>` : ''}
                    </div>
                `;
            });
        }

        return html;
    }

    formatSkillsInfo(skills) {
        let html = '<div class="skills-list">';

        if (Array.isArray(skills)) {
            skills.forEach(skill => {
                html += `<span class="skill-tag">${skill}</span>`;
            });
        }

        html += '</div>';
        return html;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    showTypingIndicator() {
        this.typingIndicator.classList.add('active');
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.typingIndicator.classList.remove('active');
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize chatbot when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new NSUChatbot();
});