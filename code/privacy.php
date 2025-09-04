<?php
// privacy.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Privacy Policy';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - NSU LinkUp</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .legal-content {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
        }

        .legal-content h1 {
            text-align: center;
            color: #333;
        }

        .legal-content p {
            color: #555;
        }

        .legal-section {
            margin-bottom: 20px;
        }

        .legal-section h2 {
            color: #007bff;
        }

        .contact-info p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="legal-content">
                <h1>Privacy Policy</h1>
                <p class="last-updated">Last updated: <?php echo date('F d, Y'); ?></p>

                <div class="legal-section">
                    <h2>1. Information We Collect</h2>
                    <p>At NSU LinkUp, we collect information that you provide directly to us, such as when you create an account, update your profile, or contact us for support.</p>

                    <h3>1.1 Account Information</h3>
                    <ul>
                        <li>Full name and email address</li>
                        <li>Academic information (degree, CGPA, university)</li>
                        <li>Professional information (research interests, experience, skills)</li>
                        <li>Contact information (phone number, address)</li>
                        <li>Profile pictures and other uploaded content</li>
                    </ul>

                    <h3>1.2 Usage Information</h3>
                    <ul>
                        <li>Log files and usage data</li>
                        <li>Device information and browser type</li>
                        <li>IP addresses and location data</li>
                        <li>Interaction with platform features</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>2. How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services</li>
                        <li>Create and manage user accounts</li>
                        <li>Facilitate connections between students, faculty, and organizers</li>
                        <li>Send important notifications and updates</li>
                        <li>Respond to your inquiries and provide customer support</li>
                        <li>Ensure platform security and prevent fraud</li>
                        <li>Conduct research and analytics to improve our services</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>3. Information Sharing and Disclosure</h2>
                    <p>We do not sell, trade, or otherwise transfer your personal information to third parties, except in the following circumstances:</p>

                    <h3>3.1 Within the Platform</h3>
                    <ul>
                        <li>Your profile information is visible to other verified users</li>
                        <li>Research posts and applications are shared with relevant faculty</li>
                        <li>Event information is shared with participants</li>
                    </ul>

                    <h3>3.2 Legal Requirements</h3>
                    <ul>
                        <li>When required by law or legal process</li>
                        <li>To protect our rights, property, or safety</li>
                        <li>To prevent fraud or security threats</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>4. Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information:</p>
                    <ul>
                        <li>Encrypted data transmission using SSL/TLS</li>
                        <li>Secure password hashing and storage</li>
                        <li>Regular security audits and updates</li>
                        <li>Access controls and user authentication</li>
                        <li>Audit logs for accountability</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>5. Your Rights and Choices</h2>
                    <p>You have the following rights regarding your personal information:</p>
                    <ul>
                        <li><strong>Access:</strong> Request a copy of your personal data</li>
                        <li><strong>Update:</strong> Modify your profile information at any time</li>
                        <li><strong>Delete:</strong> Request deletion of your account and data</li>
                        <li><strong>Restrict:</strong> Limit how we process your information</li>
                        <li><strong>Object:</strong> Opt out of certain data processing activities</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>6. Data Retention</h2>
                    <p>We retain your personal information for as long as necessary to:</p>
                    <ul>
                        <li>Maintain your account and provide services</li>
                        <li>Comply with legal obligations</li>
                        <li>Resolve disputes and enforce agreements</li>
                        <li>Fulfill the purposes outlined in this policy</li>
                    </ul>
                    <p>When you delete your account, we will remove your personal information within 30 days, except where retention is required by law.</p>
                </div>

                <div class="legal-section">
                    <h2>7. Cookies and Tracking Technologies</h2>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Maintain your login session</li>
                        <li>Remember your preferences</li>
                        <li>Analyze platform usage</li>
                        <li>Improve user experience</li>
                    </ul>
                    <p>You can control cookie settings through your browser preferences.</p>
                </div>

                <div class="legal-section">
                    <h2>8. Children's Privacy</h2>
                    <p>NSU LinkUp is intended for university students and academic professionals. We do not knowingly collect personal information from children under 18 years of age. If we become aware that a child under 18 has provided us with personal information, we will take steps to delete such information.</p>
                </div>

                <div class="legal-section">
                    <h2>9. International Data Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your personal information during such transfers.</p>
                </div>

                <div class="legal-section">
                    <h2>10. Changes to This Policy</h2>
                    <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by:</p>
                    <ul>
                        <li>Posting the updated policy on our website</li>
                        <li>Sending an email notification to registered users</li>
                        <li>Displaying a notice on the platform</li>
                    </ul>
                    <p>Your continued use of the platform after such changes constitutes acceptance of the updated policy.</p>
                </div>

                <div class="legal-section">
                    <h2>11. Contact Information</h2>
                    <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
                    <div class="contact-info">
                        <p><strong>Email:</strong> privacy@northsouth.edu</p>
                        <p><strong>Address:</strong> North South University, Bashundhara, Dhaka, Bangladesh</p>
                        <p><strong>Phone:</strong> +880-2-55668200</p>
                    </div>
                </div>

                <div class="legal-section">
                    <h2>12. Governing Law</h2>
                    <p>This Privacy Policy is governed by the laws of Bangladesh. Any disputes arising from this policy will be resolved in the courts of Dhaka, Bangladesh.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/common.js"></script>
</body>

</html>