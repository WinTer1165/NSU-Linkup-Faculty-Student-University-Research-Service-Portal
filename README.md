# NSU LinkUp - Academic Research Collaboration Platform

<div align="center">

  [![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql)](https://mysql.com)
  [![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)
  [![Status](https://img.shields.io/badge/Status-Active-success)](https://github.com/yourusername/nsu-linkup)
</div>

##  About The Project

NSU LinkUp is a comprehensive web-based platform designed to bridge the gap between students and faculty at North South University. It facilitates research collaboration, provides AI-powered academic assistance, and creates a seamless environment for academic networking and growth.

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025031.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025140.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025240.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025300.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025334.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025409.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025510.png?raw=true" width="700"/>

<img src="https://github.com/WinTer1165/NSU-Linkup-Faculty-Student-University-Research-Service-Portal/blob/main/images/Screenshot%202025-09-05%20025528.png?raw=true" width="700"/>



###  Key Objectives
- Connect students with research opportunities
- Streamline the research application process
- Provide AI-powered guidance for academic success
- Foster collaboration between students and faculty
- Create a centralized hub for academic announcements

##  Features

###  Student Portal
- **Research Discovery**: Browse and filter research opportunities by department, salary, and requirements
- **AI Study Assistant**: Get personalized recommendations for research positions matching your profile
- **Smart Application System**: Apply for positions with CGPA verification and skill matching
- **Comprehensive Profile**: Showcase skills, achievements, publications, and experience
- **Application Tracking**: Real-time status updates on submitted applications
- **Peer Networking**: Connect with fellow students for collaboration

###  Faculty Portal
- **Research Post Management**: Create, edit, and manage research opportunities
- **Application Review System**: Efficiently review and manage student applications
- **AI Research Assistant**: Access faculty-exclusive insights about student qualifications
- **Student Discovery**: Find qualified candidates based on CGPA, skills, and interests
- **Profile Management**: Display research interests, office hours, and publications
- **Analytics Dashboard**: Track application statistics and post performance

###  Admin Dashboard
- **User Management**: Verify faculty/organizer accounts, manage user roles
- **Announcement System**: Post and manage system-wide announcements
- **Comprehensive Audit Logs**: Track all platform activities for security
- **Contact Query Management**: Handle and respond to platform inquiries
- **AI Chatbot Configuration**: Manage OpenAI settings and monitor usage
- **System Statistics**: View platform metrics and user engagement data

## Technology Stack

### Backend
- **Language**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache/Nginx
- **Session Management**: PHP Sessions with role-based access

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom styling with responsive design
- **JavaScript** - Dynamic interactions and AJAX
- **Font Awesome 6.0** - Icon library

### AI Integration
- **OpenAI GPT API** - Powers the intelligent chatbot assistants
- **Custom Intent Analysis** - Smart query understanding and routing

### Security
- **Prepared Statements** - SQL injection prevention
- **Input Sanitization** - XSS protection
- **Role-Based Access Control** - Granular permission system
- **Audit Logging** - Comprehensive activity tracking

##  Prerequisites

Before you begin, ensure you have the following installed:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Git for version control
- OpenAI API key (optional, for chatbot functionality)


##  User Roles & Access

### Admin
- **Registration**: Use secret key `NSU_ADMIN_2025`
- **Capabilities**: Full system access, user management, system configuration
- **Access Path**: `/admin/*`

### Faculty
- **Registration**: Requires admin verification
- **Capabilities**: Create research posts, review applications, access AI assistant
- **Access Path**: `/faculty/*`

### Student
- **Registration**: Immediate access after signup
- **Capabilities**: Apply for research, manage profile, use AI assistant
- **Access Path**: `/student/*`

### Organizer
- **Registration**: Requires admin verification
- **Capabilities**: Create and manage events
- **Access Path**: `/organizer/*`

##  Project Structure

```
nsu-linkup/
│
├── admin/                    # Admin portal pages
│   ├── dashboard.php            # Admin dashboard
│   ├── announcements.php        # Announcement management
│   ├── audit-logs.php          # System audit logs
│   ├── contact-queries.php     # Contact form submissions
│   ├── manage-users.php        # User management
│   └── verify-users.php        # User verification
│
├── api/                      # API endpoints
│   ├── chatbot.php             # AI chatbot endpoint
│   ├── get-faculty-profile.php # Faculty data API
│   ├── get-student-profile.php # Student data API
│   ├── submit-application.php  # Application submission
│   └── upload-profile-pic.php  # Profile picture upload
│
├── assets/                   # Static resources
│   ├──  css/                 # Stylesheets
│   │   ├── common.css          # Global styles
│   │   ├── dashboard.css       # Dashboard styles
│   │   └── [role-specific].css # Role-specific styles
│   ├── js/                  # JavaScript files
│   │   ├── common.js           # Global scripts
│   │   ├── chatbot.js          # Chatbot functionality
│   │   └── [page-specific].js  # Page-specific scripts
│   └── uploads/             # User uploads
│       └── profiles/           # Profile pictures
│
├── auth/                    # Authentication pages
│   ├── login.php               # Unified login
│   ├── student-signup.php     # Student registration
│   ├── faculty-signup.php     # Faculty registration
│   ├── admin-signup.php       # Admin registration
│   └── logout.php             # Logout handler
│
├── faculty/                 # Faculty portal
│   ├── dashboard.php          # Faculty dashboard
│   ├── profile.php           # Profile management
│   ├── create-research.php   # Create research posts
│   ├── manage-posts.php      # Manage research posts
│   ├── applications.php      # Review applications
│   ├── students.php          # Browse students
│   ├── faculty.php           # Faculty directory
│   └── chatbot.php           # AI assistant
│
├── includes/               # Core includes
│   ├── config.php            # Configuration
│   ├── db_connect.php       # Database connection
│   ├── functions.php         # Helper functions
│   ├── auth_check.php       # Authentication check
│   ├── header.php           # Page header
│   └── footer.php           # Page footer
│
└── student/               # Student portal
    ├── dashboard.php         # Student dashboard
    ├── profile.php          # Profile management
    ├── research.php         # Browse research
    ├── students.php         # Student directory
    ├── faculty.php          # Faculty directory
    ├── chatbot.php          # AI assistant
    └── announcements.php    # View announcements
```

##  Configuration Options

### Session Configuration
In `php.ini`:
```ini
session.save_path = "/var/lib/php/sessions"
session.gc_maxlifetime = 3600
session.cookie_httponly = 1
session.use_only_cookies = 1
```

### File Upload Settings
In `php.ini`:
```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

##  Security Features

- **SQL Injection Prevention**: All queries use prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token validation on forms
- **Session Security**: HTTP-only cookies, session regeneration
- **File Upload Security**: Type validation, size limits, secure storage
- **Access Control**: Role-based permissions, page-level restrictions
- **Audit Logging**: Comprehensive activity tracking with IP logging

##  Database Schema

### Core Tables
- `users` - User authentication and basic info
- `students` - Student profiles and details
- `faculty` - Faculty profiles and information
- `admins` - Administrator accounts
- `organizers` - Event organizer accounts

### Feature Tables
- `research_posts` - Research opportunities
- `research_applications` - Student applications
- `announcements` - System announcements
- `events` - Campus events
- `contact_queries` - Contact form submissions

### Support Tables
- `student_skills` - Student skill listings
- `student_experience` - Work experience
- `student_achievements` - Awards and certifications
- `student_publications` - Academic publications
- `audit_logs` - System activity logs
- `chatbot_logs` - AI conversation history
- `chatbot_settings` - AI configuration

##  AI Assistant Features

### Student Assistant
- Research opportunity matching based on profile
- Skill gap analysis and recommendations
- Application tips and guidance
- Academic planning advice

### Faculty Assistant
- Student qualification insights
- Research trend analysis
- Application pattern recognition
- Collaboration recommendations

##  Troubleshooting

### Common Issues & Solutions

#### File Upload Issues
```bash
# Check permissions
ls -la assets/uploads/

# Fix permissions
sudo chmod -R 755 assets/uploads/
sudo chown -R www-data:www-data assets/uploads/
```

#### Session Errors
```bash
# Check session directory
ls -la /var/lib/php/sessions/

# Fix permissions
sudo chmod 1733 /var/lib/php/sessions/
```

##  Testing

### Manual Testing Checklist
- [ ] User registration (all roles)
- [ ] Login/logout functionality
- [ ] Research post creation
- [ ] Application submission
- [ ] AI chatbot responses
- [ ] File uploads
- [ ] Search and filters
- [ ] Mobile responsiveness


##  API Documentation

### Endpoints

#### POST /api/chatbot.php
Submit a message to the AI assistant
```json
Request:
{
  "message": "Find research opportunities in AI",
  "context": []
}

Response:
{
  "success": true,
  "response": "Based on your profile...",
  "data": {}
}
```

#### GET /api/get-research-details.php
Get detailed research post information
```
Parameters:
- id: Research post ID

Response:
{
  "success": true,
  "research": { ... }
}
```

#### POST /api/submit-application.php
Submit application for research position
```json
Request:
{
  "research_id": 123,
  "cover_letter": "I am interested in..."
}
```


### Coding Standards
- Follow PSR-12 for PHP code
- Use meaningful variable names
- Comment complex logic
- Write secure, sanitized code

---

<div align="center">
  <strong>Built with ❤️ for NSU Students</strong>
</div>
