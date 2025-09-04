-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 10:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nsu_linkup`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `user_id`, `full_name`) VALUES
(5, 14, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `admin_id`, `title`, `content`, `is_published`, `created_at`) VALUES
(8, 5, 'Scheduled Maintenance', 'Dear Users,\r\nPlease be informed that the system will undergo scheduled maintenance on August 22, 2025, from 12:00 AM to 4:00 AM (GMT+6). During this time, some services may be temporarily unavailable. We recommend saving your work and logging out before the maintenance period to avoid data loss.\r\n\r\nThank you for your patience and cooperation.\r\n– Admin Team', 1, '2025-08-14 15:51:01'),
(9, 5, 'New Feature Launch', 'Hello Everyone,\r\nWe are excited to announce the launch of the AI-Powered Search Feature starting August 28, 2025. This new feature will allow faster and more accurate search results, helping you find the information you need in seconds.\r\n\r\nFor a detailed guide on how to use the new search, please check the Profile section in your dashboard.\r\n\r\nWe look forward to your feedback!\r\n– Admin Team', 1, '2025-08-14 15:51:31'),
(10, 5, 'New Feature Launch 2', 'Hello Everyone,\r\nWe are excited to announce the launch of the AI-Powered Search Feature starting August 28, 2025. This new feature will allow faster and more accurate search results, helping you find the information you need in seconds.\r\n\r\nFor a detailed guide on how to use the new search, please check the “Help &amp; Support” section in your dashboard.\r\n\r\nWe look forward to your feedback!\r\n– Admin Team', 1, '2025-08-14 15:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_affected` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `table_affected`, `record_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(154, NULL, 'REGISTER', 'users', 9, NULL, NULL, '::1', '2025-08-14 15:17:17'),
(155, 9, 'LOGOUT', 'users', 9, NULL, NULL, '::1', '2025-08-14 15:17:30'),
(156, NULL, 'REGISTER', 'users', 10, NULL, NULL, '::1', '2025-08-14 15:24:41'),
(157, 10, 'UPDATE_PROFILE_PICTURE', 'students', 10, NULL, NULL, '::1', '2025-08-14 15:24:50'),
(158, 10, 'UPDATE_PROFILE_PICTURE', 'students', 10, NULL, NULL, '::1', '2025-08-14 15:24:50'),
(159, 10, 'UPDATE_BASIC_INFO', 'students', 10, NULL, NULL, '::1', '2025-08-14 15:25:57'),
(160, 10, 'UPDATE_EDUCATION', 'students', 10, NULL, NULL, '::1', '2025-08-14 15:26:10'),
(161, 10, 'ADD_EXPERIENCE', 'student_experience', 7, NULL, NULL, '::1', '2025-08-14 15:26:30'),
(162, 10, 'ADD_EXPERIENCE', 'student_experience', 8, NULL, NULL, '::1', '2025-08-14 15:26:57'),
(163, 10, 'DELETE_EXPERIENCE', 'student_experience', 7, NULL, NULL, '::1', '2025-08-14 15:27:09'),
(164, 10, 'ADD_EXPERIENCE', 'student_experience', 9, NULL, NULL, '::1', '2025-08-14 15:27:31'),
(165, 10, 'DELETE_EXPERIENCE', 'student_experience', 9, NULL, NULL, '::1', '2025-08-14 15:27:44'),
(166, 10, 'DELETE_EXPERIENCE', 'student_experience', 8, NULL, NULL, '::1', '2025-08-14 15:27:45'),
(167, 10, 'ADD_EXPERIENCE', 'student_experience', 10, NULL, NULL, '::1', '2025-08-14 15:28:00'),
(168, 10, 'ADD_EXPERIENCE', 'student_experience', 11, NULL, NULL, '::1', '2025-08-14 15:28:42'),
(169, 10, 'DELETE_EXPERIENCE', 'student_experience', 10, NULL, NULL, '::1', '2025-08-14 15:30:47'),
(170, 10, 'DELETE_EXPERIENCE', 'student_experience', 11, NULL, NULL, '::1', '2025-08-14 15:30:49'),
(171, 10, 'ADD_EXPERIENCE', 'student_experience', 12, NULL, NULL, '::1', '2025-08-14 15:31:45'),
(172, 10, 'ADD_SKILL', 'student_skills', 4, NULL, NULL, '::1', '2025-08-14 15:31:52'),
(173, 10, 'ADD_SKILL', 'student_skills', 5, NULL, NULL, '::1', '2025-08-14 15:31:55'),
(174, 10, 'ADD_SKILL', 'student_skills', 6, NULL, NULL, '::1', '2025-08-14 15:31:56'),
(175, 10, 'ADD_SKILL', 'student_skills', 7, NULL, NULL, '::1', '2025-08-14 15:31:57'),
(176, 10, 'ADD_SKILL', 'student_skills', 8, NULL, NULL, '::1', '2025-08-14 15:31:59'),
(177, 10, 'ADD_SKILL', 'student_skills', 9, NULL, NULL, '::1', '2025-08-14 15:32:03'),
(178, 10, 'ADD_SKILL', 'student_skills', 10, NULL, NULL, '::1', '2025-08-14 15:32:08'),
(179, 10, 'ADD_SKILL', 'student_skills', 11, NULL, NULL, '::1', '2025-08-14 15:32:22'),
(180, 10, 'ADD_SKILL', 'student_skills', 12, NULL, NULL, '::1', '2025-08-14 15:32:26'),
(181, 10, 'ADD_ACHIEVEMENT', 'student_achievements', 2, NULL, NULL, '::1', '2025-08-14 15:32:53'),
(182, 10, 'ADD_ACHIEVEMENT', 'student_achievements', 3, NULL, NULL, '::1', '2025-08-14 15:33:14'),
(183, 10, 'ADD_PUBLICATION', 'student_publications', 2, NULL, NULL, '::1', '2025-08-14 15:35:09'),
(184, 10, 'ADD_PUBLICATION', 'student_publications', 3, NULL, NULL, '::1', '2025-08-14 15:35:26'),
(185, 10, 'ADD_PUBLICATION', 'student_publications', 4, NULL, NULL, '::1', '2025-08-14 15:35:56'),
(186, 10, 'UPDATE_BASIC_INFO', 'students', 10, NULL, NULL, '::1', '2025-08-14 15:36:57'),
(187, 10, 'LOGOUT', 'users', 10, NULL, NULL, '::1', '2025-08-14 15:37:30'),
(188, 9, 'LOGIN', 'users', 9, NULL, NULL, '::1', '2025-08-14 15:37:55'),
(189, 9, 'UPDATE_BASIC_INFO', 'students', 9, NULL, NULL, '::1', '2025-08-14 15:39:59'),
(190, 9, 'UPDATE_EDUCATION', 'students', 9, NULL, NULL, '::1', '2025-08-14 15:40:09'),
(191, 9, 'ADD_SKILL', 'student_skills', 13, NULL, NULL, '::1', '2025-08-14 15:40:26'),
(192, 9, 'ADD_SKILL', 'student_skills', 14, NULL, NULL, '::1', '2025-08-14 15:40:28'),
(193, 9, 'ADD_SKILL', 'student_skills', 15, NULL, NULL, '::1', '2025-08-14 15:40:30'),
(194, 9, 'ADD_SKILL', 'student_skills', 16, NULL, NULL, '::1', '2025-08-14 15:40:30'),
(195, 9, 'ADD_SKILL', 'student_skills', 17, NULL, NULL, '::1', '2025-08-14 15:40:37'),
(196, 9, 'ADD_SKILL', 'student_skills', 18, NULL, NULL, '::1', '2025-08-14 15:40:41'),
(197, 9, 'ADD_SKILL', 'student_skills', 19, NULL, NULL, '::1', '2025-08-14 15:40:46'),
(198, 9, 'ADD_SKILL', 'student_skills', 20, NULL, NULL, '::1', '2025-08-14 15:40:51'),
(199, 9, 'ADD_SKILL', 'student_skills', 21, NULL, NULL, '::1', '2025-08-14 15:40:58'),
(200, 9, 'ADD_SKILL', 'student_skills', 22, NULL, NULL, '::1', '2025-08-14 15:41:03'),
(201, 9, 'UPDATE_PROFILE_PICTURE', 'students', 9, NULL, NULL, '::1', '2025-08-14 15:41:34'),
(202, 9, 'UPDATE_PROFILE_PICTURE', 'students', 9, NULL, NULL, '::1', '2025-08-14 15:41:34'),
(203, 9, 'LOGOUT', 'users', 9, NULL, NULL, '::1', '2025-08-14 15:41:46'),
(204, NULL, 'REGISTER', 'users', 11, NULL, NULL, '::1', '2025-08-14 15:42:46'),
(205, 11, 'UPDATE_PROFILE_PICTURE', 'students', 11, NULL, NULL, '::1', '2025-08-14 15:42:51'),
(206, 11, 'UPDATE_PROFILE_PICTURE', 'students', 11, NULL, NULL, '::1', '2025-08-14 15:42:51'),
(207, 11, 'UPDATE_BASIC_INFO', 'students', 11, NULL, NULL, '::1', '2025-08-14 15:43:12'),
(208, 11, 'UPDATE_EDUCATION', 'students', 11, NULL, NULL, '::1', '2025-08-14 15:43:24'),
(209, 11, 'ADD_EXPERIENCE', 'student_experience', 13, NULL, NULL, '::1', '2025-08-14 15:43:45'),
(210, 11, 'ADD_SKILL', 'student_skills', 23, NULL, NULL, '::1', '2025-08-14 15:44:11'),
(211, 11, 'DELETE_SKILL', 'student_skills', 23, NULL, NULL, '::1', '2025-08-14 15:44:15'),
(212, 11, 'ADD_SKILL', 'student_skills', 24, NULL, NULL, '::1', '2025-08-14 15:44:17'),
(213, 11, 'ADD_SKILL', 'student_skills', 25, NULL, NULL, '::1', '2025-08-14 15:44:17'),
(214, 11, 'ADD_SKILL', 'student_skills', 26, NULL, NULL, '::1', '2025-08-14 15:44:18'),
(215, 11, 'ADD_SKILL', 'student_skills', 27, NULL, NULL, '::1', '2025-08-14 15:44:18'),
(216, 11, 'ADD_SKILL', 'student_skills', 28, NULL, NULL, '::1', '2025-08-14 15:44:19'),
(217, 11, 'ADD_SKILL', 'student_skills', 29, NULL, NULL, '::1', '2025-08-14 15:44:20'),
(218, 11, 'LOGOUT', 'users', 11, NULL, NULL, '::1', '2025-08-14 15:44:42'),
(219, NULL, 'REGISTER', 'users', 12, NULL, NULL, '::1', '2025-08-14 15:46:44'),
(220, NULL, 'REGISTER', 'users', 13, NULL, NULL, '::1', '2025-08-14 15:48:06'),
(221, 14, 'Admin account created', 'admins', 14, NULL, NULL, '::1', '2025-08-14 15:49:01'),
(222, 14, 'LOGIN', 'users', 14, NULL, NULL, '::1', '2025-08-14 15:49:25'),
(223, 14, 'VERIFY_USER', 'users', 12, '{\"is_verified\":0}', '{\"is_verified\":1}', '::1', '2025-08-14 15:49:36'),
(224, 14, 'VERIFY_USER', 'users', 13, '{\"is_verified\":0}', '{\"is_verified\":1}', '::1', '2025-08-14 15:49:39'),
(225, 14, 'CREATE_ANNOUNCEMENT', 'announcements', 8, NULL, NULL, '::1', '2025-08-14 15:51:01'),
(226, 14, 'CREATE_ANNOUNCEMENT', 'announcements', 9, NULL, NULL, '::1', '2025-08-14 15:51:31'),
(227, NULL, 'REGISTER', 'users', 15, NULL, NULL, '127.0.0.1', '2025-08-14 15:54:43'),
(228, 14, 'CREATE_ANNOUNCEMENT', 'announcements', 10, NULL, NULL, '::1', '2025-08-14 15:55:39'),
(229, 14, 'VERIFY_USER', 'users', 15, '{\"is_verified\":0}', '{\"is_verified\":1}', '::1', '2025-08-14 15:56:35'),
(230, 14, 'BAN_USER', 'users', 15, '{\"is_banned\":0}', '{\"is_banned\":1}', '::1', '2025-08-14 15:56:54'),
(231, 14, 'UNBAN_USER', 'users', 15, '{\"is_banned\":1}', '{\"is_banned\":0}', '::1', '2025-08-14 15:57:08'),
(232, 14, 'LOGOUT', 'users', 14, NULL, NULL, '::1', '2025-08-14 15:57:49'),
(233, 12, 'LOGIN', 'users', 12, NULL, NULL, '::1', '2025-08-14 15:58:17'),
(234, 12, 'LOGOUT', 'users', 12, NULL, NULL, '::1', '2025-08-14 16:02:06'),
(235, 14, 'LOGIN', 'users', 14, NULL, NULL, '::1', '2025-08-14 16:03:21'),
(236, 14, 'BAN_USER', 'users', 15, '{\"is_banned\":0}', '{\"is_banned\":1}', '::1', '2025-08-14 16:04:01'),
(237, 14, 'UNBAN_USER', 'users', 15, '{\"is_banned\":1}', '{\"is_banned\":0}', '::1', '2025-08-14 16:04:04'),
(238, 10, 'LOGIN', 'users', 10, NULL, NULL, '127.0.0.1', '2025-08-18 09:07:03'),
(239, 13, 'LOGIN', 'users', 13, NULL, NULL, '::1', '2025-08-18 09:08:55'),
(240, 10, 'APPLY_RESEARCH', 'research_applications', 4, NULL, NULL, '127.0.0.1', '2025-08-18 09:14:47'),
(241, 10, 'LOGIN', 'users', 10, NULL, NULL, '::1', '2025-08-21 15:22:50'),
(242, 13, 'LOGIN', 'users', 13, NULL, NULL, '::1', '2025-08-22 00:02:47'),
(243, 10, 'LOGIN', 'users', 10, NULL, NULL, '::1', '2025-08-22 08:16:29'),
(244, 10, 'LOGIN', 'users', 10, NULL, NULL, '::1', '2025-08-22 12:36:49'),
(245, 10, 'LOGIN', 'users', 10, NULL, NULL, '::1', '2025-08-22 12:40:47'),
(246, 13, 'LOGIN', 'users', 13, NULL, NULL, '127.0.0.1', '2025-08-22 12:41:28'),
(247, 14, 'LOGIN', 'users', 14, NULL, NULL, '::1', '2025-08-22 22:22:47'),
(248, 10, 'LOGIN', 'users', 10, NULL, NULL, '::1', '2025-09-04 20:51:30'),
(249, 10, 'LOGOUT', 'users', 10, NULL, NULL, '::1', '2025-09-04 20:53:13'),
(250, 13, 'LOGIN', 'users', 13, NULL, NULL, '::1', '2025-09-04 20:53:24'),
(251, 13, 'LOGOUT', 'users', 13, NULL, NULL, '::1', '2025-09-04 20:54:41'),
(252, 14, 'LOGIN', 'users', 14, NULL, NULL, '::1', '2025-09-04 20:54:53');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_logs`
--

CREATE TABLE `chatbot_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_message` text DEFAULT NULL,
  `bot_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_logs`
--

INSERT INTO `chatbot_logs` (`log_id`, `user_id`, `user_message`, `bot_response`, `created_at`) VALUES
(1, 13, 'Which students have skills in machine learning?', 'I\'m sorry, but I don\'t have access to information about students or their specific skills. I can assist you with information related to faculty and their research areas at North South University. If you have any questions related to faculty expertise or need assistance with the university\'s resources, feel free to ask!', '2025-08-22 00:05:31'),
(2, 13, 'Show me students with CGPA above 3.5', 'I apologize, but I cannot provide specific student information such as names or CGPAs. However, if you have any other questions or need information related to North South University, feel free to ask!', '2025-08-22 00:05:35'),
(3, 13, 'What are the trending research areas?', 'To find the trending research areas, you can explore the research publications and ongoing projects within the NSU LinkUp database. If you have access, you can look for patterns in the most published topics or the areas receiving the most funding. Additionally, you can attend faculty meetings or workshops that focus on research to get insights into current trends at North South University. If you need further assistance or specific data from NSU LinkUp, please let me know!', '2025-08-22 00:05:55'),
(4, 13, 'Which students have skills in machine learning?', 'I\'m sorry, but I don\'t have access to information about students or their skills. I can only provide information about the faculty members at North South University. If you have questions about faculty expertise or research areas, feel free to ask!', '2025-08-22 00:11:53'),
(5, 13, 'What are the trending research areas?', 'I\'m sorry, but I don\'t have specific details on the trending research areas at North South University. However, I can help you find general information or connect you with the relevant department or faculty member who might have more insights. Please let me know how you\'d like to proceed!', '2025-08-22 00:13:01'),
(6, 13, 'Which students have skills in machine learning?', 'I\'m sorry, but I don\'t have information about students and their skills in the provided database. If you have questions about faculty members or research areas related to machine learning, feel free to ask!', '2025-08-22 00:16:48'),
(7, 13, 'How many applications do I have pending?', 'I\'m sorry, but I don\'t have access to individual application statuses or personal data within the NSU LinkUp system. You might want to check your account on the NSU LinkUp platform directly or contact the administrative office for assistance with your application status.', '2025-08-22 00:17:00'),
(8, 13, 'What are the trending research areas?', 'The trending research areas at North South University, as per the available database, include:\n\n1. Machine Learning\n2. Data Science\n3. Cybersecurity\n4. Renewable Energy\n5. Artificial Intelligence\n\nIf you need more detailed information about these research areas or specific projects, feel free to ask!', '2025-08-22 00:17:12'),
(9, 13, 'Which faculty members work in AI research?', 'The faculty members working in AI research at North South University are:\n\n1. Sadia Hamid Kazi: Associate Professor, with research interests in Artificial Intelligence (AI), Machine Learning (ML), Deep Learning, Natural Language Processing (NLP), and Computer Vision.\n\n2. Tajul Haq: Assistant Professor, with research interests in Robotics, Vision based AI, Multi-Modal AI, and Vision based Automation.\n\nIf you need more information or have specific questions, feel free to ask!', '2025-08-22 00:17:17'),
(10, 13, 'how many faculty are there', 'Based on the provided database, there are three faculty members listed:\n\n1. Mahbubul Alam Majumdar\n2. Sadia Hamid Kazi\n3. Tajul Haq\n\nIf you have any other questions or need more information, feel free to ask!', '2025-08-22 00:17:30'),
(11, 13, 'Show me students with CGPA above 3.5', 'I\'m sorry, but I don\'t have access to specific student data or their CGPAs in the provided database. If you need information related to faculty, research areas, or other university-related topics, feel free to ask!', '2025-08-22 00:17:39'),
(12, 13, 'Show me students with CGPA', 'I\'m sorry, but I don\'t have access to specific student data, including CGPAs, in the provided database. If you have other questions related to faculty or university research areas, please let me know!', '2025-08-22 00:17:57'),
(13, 13, 'how can i gain DSA skills', 'While I don\'t have specific information on courses or programs related to Data Structures and Algorithms (DSA) at North South University, I can suggest some general approaches to gaining DSA skills:\n\n1. **University Courses**: Check the course catalog or speak with your academic advisor to see if NSU offers courses in Data Structures and Algorithms.\n\n2. **Online Courses**: Platforms like Coursera, edX, and Khan Academy offer online courses on DSA that you can take at your own pace.\n\n3. **Books and Resources**: Consider reading books like \"Introduction to Algorithms\" by Cormen, Leiserson, Rivest, and Stein, which is a comprehensive resource on algorithms.\n\n4. **Practice Problems**: Websites like LeetCode, HackerRank, and CodeSignal offer a wide range of problems to practice DSA skills.\n\n5. **Workshops and Seminars**: Keep an eye out for any workshops or seminars hosted by NSU that might cover DSA or related topics.\n\nIf you are looking for faculty guidance, you might consider reaching out to professors who specialize in Computer Science or related fields for advice.', '2025-08-22 00:18:17'),
(14, 10, 'What research opportunities match my profile?', 'To provide you with specific research opportunities that match your profile, I would need some details about your academic background, interests, and any specific fields of study you are interested in. Please share more information so that I can assist you better with opportunities related to NSU.', '2025-08-22 08:57:24'),
(15, 10, 'Which skills should I develop for research?', 'To excel in research at North South University, consider developing the following skills:\n\n1. **Critical Thinking and Analysis**: Being able to assess information critically and develop logical conclusions is crucial for research.\n\n2. **Data Analysis**: Proficiency in statistical software and data analysis tools can be vital, especially for quantitative research.\n\n3. **Academic Writing**: Strong writing skills are necessary for documenting research findings and writing papers or theses.\n\n4. **Literature Review**: The ability to conduct comprehensive literature reviews to understand existing research and identify gaps.\n\n5. **Time Management**: Managing time effectively to balance research with other academic responsibilities.\n\n6. **Technical Skills**: Depending on your field, this might include laboratory skills, programming, or the use of specific research tools.\n\n7. **Collaboration and Communication**: Ability to work with a team and communicate findings clearly to others.\n\n8. **Problem-Solving**: Developing innovative solutions to research problems.\n\nFocusing on these skills will help you prepare for research opportunities at NSU.', '2025-08-22 08:57:35'),
(16, 10, 'How to connect with faculty for research?', 'To connect with faculty for research opportunities at North South University, follow these steps:\n\n1. **Research Faculty Interests**: Review the research interests of faculty members to find those that align with your own. For example:\n   - Prof. Mahbubul Alam Majumdar focuses on Machine Learning, Computational Finance, Cryptocurrencies, Blockchain, Quantum Computing, and Cryptography.\n   - Dr. Sadia Hamid Kazi specializes in Artificial Intelligence, Machine Learning, Deep Learning, Natural Language Processing, and Computer Vision.\n   - Dr. Tajul Haq is interested in Robotics, Vision-based AI, Multi-Modal AI, and Vision-based Automation.\n\n2. **Prepare Your Profile**: Update your CV and prepare a brief statement of your research interests and any relevant experience.\n\n3. **Email Faculty Members**: Reach out via email to express your interest in their research. Include your CV, a brief introduction, and why you are interested in their work. For example:\n   - Email Prof. Mahbubul Alam Majumdar at f1@northsouth.edu\n   - Email Dr. Sadia Hamid Kazi at f2@northsouth.edu\n   - Email Dr. Tajul Haq at f3@northsouth.edu\n\n4. **Attend Office Hours**: Visit their office during office hours for a face-to-face meeting. Offices are:\n   - Prof. Majumdar: SAC1000\n   - Dr. Kazi: SAC935\n   - Dr. Haq: SAC805\n\n5. **Be Professional and Persistent**: Follow up if you do not receive a response, and be polite and professional in all your communications.\n\nConnecting with faculty requires a proactive approach, so take the initiative to reach out and express your enthusiasm for their research areas.', '2025-08-22 08:57:43'),
(17, 10, 'How to connect with faculty for research?', 'To connect with faculty for research at North South University, you can follow these steps:\n\n1. **Identify Your Area of Interest**: Review the faculty research areas to find a match with your interests. For example:\n   - If you\'re interested in Machine Learning or Quantum Computing, you might consider contacting Professor Mahbubul Alam Majumdar.\n   - For interests in AI, Machine Learning, Deep Learning, or NLP, Associate Professor Sadia Hamid Kazi would be a suitable contact.\n   - If robotics or vision-based AI piques your interest, Assistant Professor Tajul Haq could be a potential mentor.\n\n2. **Prepare a Professional Email**: Draft an email expressing your interest in their research area. Be concise and professional. Include:\n   - A brief introduction about yourself (e.g., your year, major, and any relevant experience or coursework).\n   - Specific reasons why you\'re interested in their research.\n   - Any questions you might have about their work or potential research opportunities.\n\n3. **Contact Information**: Use the provided email addresses to reach out:\n   - Professor Mahbubul Alam Majumdar: f1@northsouth.edu\n   - Associate Professor Sadia Hamid Kazi: f2@northsouth.edu\n   - Assistant Professor Tajul Haq: f3@northsouth.edu\n\n4. **Visit Office Hours**: If possible, visit them during office hours to discuss your interest in person. Their offices are located at:\n   - Mahbubul Alam Majumdar: SAC1000\n   - Sadia Hamid Kazi: SAC935\n   - Tajul Haq: SAC805\n\nBy following these steps, you\'ll increase your chances of successfully engaging with faculty members for research opportunities.', '2025-08-22 12:41:49'),
(18, 10, 'What\'s the status of my applications?', 'I\'m sorry, but I don\'t have access to individual application statuses or personal student records. For information on the status of your applications at North South University, I recommend checking the university\'s official student portal or contacting the admissions office directly for assistance. If you have any other questions related to NSU, feel free to ask!', '2025-08-22 12:42:02'),
(19, 10, 'How can I improve my research application?', 'To improve your research application at North South University, consider the following steps:\n\n1. **Tailor Your Application**: Customize your application for each faculty member or research opportunity. Highlight your skills and experiences that align with their research interests.\n\n2. **Strong Statement of Purpose**: Clearly articulate your research interests, goals, and why you are interested in working with the specific faculty member. Explain how your background and skills make you a suitable candidate.\n\n3. **Relevant Experience**: Include any relevant coursework, projects, or work experience. If you\'ve worked on projects or internships related to the faculty\'s research area, make sure to highlight these experiences.\n\n4. **Highlight Skills**: Emphasize any technical skills or methodologies you have mastered that are pertinent to the research. For example, if you\'re applying to work with Professor Mahbubul Alam Majumdar on Machine Learning, showcase your proficiency in relevant programming languages or tools.\n\n5. **Academic Performance**: While grades aren\'t everything, strong academic performance, particularly in courses related to the research field, can strengthen your application.\n\n6. **Recommendations**: Obtain strong letters of recommendation from professors or professionals who can speak to your abilities and potential as a researcher.\n\n7. **Proofread**: Ensure your application is free from grammatical errors and typos. A well-written application reflects attention to detail and professionalism.\n\nBy focusing on these aspects, you can enhance the quality of your research application and improve your chances of being selected for research opportunities at NSU.', '2025-08-22 12:42:25'),
(20, 10, 'Show me upcoming research deadlines', 'I don\'t have access to specific dates for upcoming research deadlines at North South University. To find out about these deadlines, I recommend checking the official university website, contacting the relevant department directly, or speaking with your academic advisor. They can provide the most up-to-date information on research application deadlines and other important timelines. If you have further questions about NSU, feel free to ask!', '2025-08-22 12:42:31'),
(21, 13, 'What are the trending research areas?', 'The trending research areas at North South University, as identified by the NSU LinkUp data, include:\n\n1. Data Science and Artificial Intelligence\n2. Renewable Energy and Sustainability\n3. Health Informatics\n4. Cybersecurity\n5. Biotechnology and Molecular Biology\n\nIf you are interested in any of these areas, I can help you find more information or connect you with faculty members involved in these research fields.', '2025-08-22 12:45:19'),
(22, 10, 'What research opportunities match my profile?', 'To provide guidance on research opportunities that match your profile, I would need specific details such as your major, current academic standing, and any particular areas of interest you have. Please share more information so I can assist you better with options available at North South University.', '2025-08-22 12:52:43'),
(23, 13, 'What are the trending research areas?', 'The trending research areas at North South University, based on the latest information from NSU LinkUp, include:\n\n1. Data Science and Artificial Intelligence\n2. Renewable Energy and Sustainability\n3. Biomedical Engineering and Biotechnology\n4. Cybersecurity and Information Assurance\n5. Business Analytics and Financial Technology\n\nIf you are interested in any specific area or need more detailed information about faculty members working in these fields, feel free to ask!', '2025-08-22 13:03:36'),
(24, 10, 'How to connect with faculty for research?', 'To connect with faculty for research at North South University, you can follow these steps:\n\n1. **Identify Your Area of Interest**: Determine which research area aligns with your interests. For instance, if you\'re interested in Machine Learning, you might want to connect with Professor Sadia Hamid Kazi or Professor Mahbubul Alam Majumdar.\n\n2. **Review Faculty Profiles**: Look at the research interests of faculty members. For example:\n   - Professor Mahbubul Alam Majumdar specializes in Machine Learning, Computational Finance, Cryptocurrencies, Blockchain, Quantum Computing, and related areas.\n   - Associate Professor Sadia Hamid Kazi focuses on Artificial Intelligence, Machine Learning, Deep Learning, Natural Language Processing, and Computer Vision.\n   - Assistant Professor Tajul Haq works on Robotics, Vision-based AI, Multi-Modal AI, and Vision-based Automation.\n\n3. **Contact Faculty**: Use their provided email addresses to reach out. Be professional and concise in your email. Introduce yourself, express your interest in their research area, and inquire about potential research opportunities. For instance:\n   - Email Professor Mahbubul Alam Majumdar at f1@northsouth.edu if you\'re interested in topics like Quantum Computing or Cryptocurrencies.\n   - Email Associate Professor Sadia Hamid Kazi at f2@northsouth.edu for AI or NLP-related research.\n   - Email Assistant Professor Tajul Haq at f3@northsouth.edu if you are interested in Robotics or Vision-based AI.\n\n4. **Visit Their Office**: If possible, visit their office during office hours for a face-to-face discussion. For example, you can find:\n   - Professor Mahbubul Alam Majumdar at SAC1000\n   - Associate Professor Sadia Hamid Kazi at SAC935\n   - Assistant Professor Tajul Haq at SAC805\n\n5. **Prepare for the Meeting**: Before any meeting or discussion, be well-prepared with questions and have a clear idea of what you want to gain from the research experience.\n\nBy following these steps, you can effectively connect with faculty members who share your research interests.', '2025-08-22 13:03:50'),
(25, 10, 'What research opportunities match my profile?', 'To help determine which research opportunities might match your profile, it would be helpful to know about your academic background, interests, and any specific skills or areas you are passionate about. However, based on the faculty research areas available at North South University, here are some general matches you might consider:\n\n1. **Machine Learning and AI**: If you have a background in computer science, mathematics, or a related field, and are interested in Machine Learning, Deep Learning, or AI, you could explore opportunities with:\n   - Professor Mahbubul Alam Majumdar, who specializes in Machine Learning, Computational Finance, and Quantum Computing.\n   - Associate Professor Sadia Hamid Kazi, focusing on Artificial Intelligence, Machine Learning, Natural Language Processing, and Computer Vision.\n\n2. **Robotics and Automation**: For students interested in robotics, vision-based AI, or automation, Assistant Professor Tajul Haq could be a great match. His work includes Robotics, Vision-based AI, and Vision-based Automation.\n\n3. **Computational Finance and Cryptocurrencies**: If your interests lie in finance and technology, working with Professor Mahbubul Alam Majumdar on topics like Computational Finance, Cryptocurrencies, or Blockchain might be suitable.\n\n4. **Natural Language Processing and Computer Vision**: Students fascinated by how machines can understand and interpret human language or visual data might find engaging research opportunities with Associate Professor Sadia Hamid Kazi.\n\nTo find the best fit for your profile, consider your skills and interests, and reach out to the relevant faculty members to discuss potential research projects.', '2025-08-22 13:04:00'),
(26, 10, 'Show me upcoming research deadlines', 'I\'m sorry, but I currently don\'t have access to real-time data or specific upcoming deadlines for research projects at North South University. I recommend checking the official NSU website, contacting the relevant department, or reaching out to the university\'s research office for the most accurate and up-to-date information on upcoming research deadlines. If you have any other questions related to NSU, feel free to ask!', '2025-09-04 20:51:58'),
(27, 10, 'What research opportunities match my profile?', 'To provide you with specific research opportunities that match your profile, I would need more details about your academic background, field of interest, and any specific areas you are looking to explore at North South University. Generally, I recommend checking the NSU website or contacting faculty members in your department who might be leading research projects. They can offer guidance on available opportunities that align with your interests and qualifications. If you have any other questions or need further assistance related to NSU, feel free to ask!', '2025-09-04 20:52:06'),
(28, 10, 'How can I improve my research application?', 'Improving your research application can significantly enhance your chances of securing a position or funding. Here are some tips tailored for North South University students:\n\n1. **Understand the Requirements**: Carefully read the application guidelines provided by NSU or the specific department. Ensure you meet all criteria and deadlines.\n\n2. **Highlight Relevant Experience**: Clearly outline any previous research experience, coursework, or skills relevant to the project. If you\'ve worked on similar projects or have relevant qualifications, make sure to include them.\n\n3. **Strong Statement of Purpose**: Write a compelling statement of purpose that outlines your interest in the research area, your motivation, and how you plan to contribute to the project. Mention any faculty members you are interested in working with at NSU.\n\n4. **Tailor Your Application**: Customize your application materials for each opportunity. Ensure that your resume, cover letter, and any other documents specifically align with the research opportunity.\n\n5. **Seek Faculty Advice**: Reach out to NSU faculty members for feedback on your application. They can provide valuable insights into what makes a successful application.\n\n6. **Proofread and Edit**: Ensure your application is free of grammatical and typographical errors. Consider having someone else review your application for clarity and coherence.\n\n7. **Recommendation Letters**: Obtain strong letters of recommendation from professors or mentors who can vouch for your skills and suitability for the research opportunity.\n\n8. **Demonstrate Passion and Initiative**: Show your enthusiasm for the research topic and any independent initiatives you\'ve taken related to your field of interest.\n\nIf you need more specific guidance or have other questions related to NSU, feel free to ask!', '2025-09-04 20:52:28'),
(29, 13, 'Which students have skills in machine learning?', 'I\'m sorry, but I do not have access to student information or their specific skills. I can assist you with information regarding faculty members and their research areas. If you have questions about faculty expertise in machine learning, feel free to ask!', '2025-09-04 20:53:42');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_settings`
--

CREATE TABLE `chatbot_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_settings`
--

INSERT INTO `chatbot_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'openai_api_key', 'API KEY', 'OpenAI API key for chatbot functionality', '2025-08-21 14:52:32'),
(2, 'model', 'gpt-4o', 'OpenAI model to use', '2025-08-21 14:49:09'),
(3, 'max_tokens', '500', 'Maximum tokens per response', '2025-08-21 14:49:09'),
(4, 'temperature', '0.7', 'Response creativity (0-1)', '2025-08-21 14:49:09'),
(5, 'enabled', '1', 'Whether chatbot is enabled', '2025-08-21 14:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `contact_queries`
--

CREATE TABLE `contact_queries` (
  `query_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_queries`
--

INSERT INTO `contact_queries` (`query_id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(4, 'Test Name 1', 'tm@nsu.edu', 'Account Issues', 'Test Message 1', 1, '2025-08-14 15:56:13');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `type` enum('hackathon','workshop','fest','other') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `prefix` varchar(10) DEFAULT NULL,
  `office` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `office_hours` varchar(100) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `research_interests` text DEFAULT NULL,
  `courses_taught` text DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `about` text DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `google_scholar` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `user_id`, `profile_image`, `full_name`, `title`, `prefix`, `office`, `phone`, `office_hours`, `education`, `research_interests`, `courses_taught`, `biography`, `about`, `linkedin`, `google_scholar`, `github`, `website`) VALUES
(4, 12, '689e07e773a8c.jpg', 'Mahbubul Alam Majumdar', 'Professor', 'Dr.', 'SAC1000', '', 'MW 10:50PM', 'PhD', 'Machine Learning\r\nComputational Finance\r\nCryptocurrencies, Blockchain and related ideas\r\nQuantum Computing, Quantum Algorithms and Cryptography', 'CSE215, CSE225, CSE445, CSE499', 'Passionate about coding, problem-solving, and technology innovation.', '', 'https://www.linkedin.com/feed/', 'https://scholar.google.com/', '', ''),
(5, 13, '68a2ee7554c21.jpg', 'Sadia Hamid Kazi', 'Associate Professor', 'Dr.', 'SAC935', '', '', 'Ph.D. degree in Information Systems', 'Artificial Intelligence (AI)\r\n\r\nMachine Learning (ML)\r\n\r\nDeep Learning\r\n\r\nNatural Language Processing (NLP)\r\n\r\nComputer Vision', '', '', '', '', '', '', ''),
(6, 15, NULL, 'Tajul Haq', 'Assistant Professor', 'Dr.', 'SAC805', '0111 1111 111', NULL, 'PhD - Kyushy Institute of Technology, Japan', 'Robotics, Vision based AI, Multi-Modal AI, Vision based Automation', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `organizers`
--

CREATE TABLE `organizers` (
  `organizer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `research_applications`
--

CREATE TABLE `research_applications` (
  `application_id` int(11) NOT NULL,
  `research_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_applications`
--

INSERT INTO `research_applications` (`application_id`, `research_id`, `student_id`, `cover_letter`, `status`, `applied_at`, `reviewed_at`) VALUES
(4, 6, 3, 'I am writing to express my strong interest in joining the research project on Explainable Machine Learning (XAI) in Intelligent Tutoring Systems. As a final-year Computer Science student with a deep interest in both machine learning and educational technology, I am excited about the opportunity to contribute to a project that combines technical innovation with real-world impact on learning.\r\n\r\nMachine learning has already transformed personalized education, but many models remain “black boxes” that students and teachers find difficult to trust. What excites me about this research is its focus on explainability—making predictions transparent, interpretable, and human-centered. I believe this aligns closely with my academic interests and my goal of developing AI that enhances, rather than replaces, human decision-making.', 'accepted', '2025-08-18 09:14:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `research_posts`
--

CREATE TABLE `research_posts` (
  `research_id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `min_cgpa` decimal(3,2) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `apply_deadline` date DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `student_roles` text DEFAULT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `number_required` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_posts`
--

INSERT INTO `research_posts` (`research_id`, `faculty_id`, `title`, `min_cgpa`, `department`, `apply_deadline`, `duration`, `tags`, `description`, `student_roles`, `salary`, `number_required`, `is_active`, `created_at`) VALUES
(6, 5, 'Enhancing Student Learning using Explainable Machine Learning Models', 3.50, 'Computer Science &amp; Engineering', '2025-08-20', '1 semester', 'Python, SQL', 'This research explores the integration of explainable machine learning (XAI) techniques into intelligent tutoring systems and educational apps. While ML models can predict student performance and suggest personalized study plans, many act as “black boxes.” This project focuses on designing models that not only make accurate predictions but also provide transparent reasoning behind their decisions.', 'Students will take on different roles based on interest and expertise:\r\n\r\nData Engineer – Responsible for collecting, cleaning, and preprocessing the educational dataset. Ensures data quality, handles missing values, and prepares features.\r\n\r\nML Model Developer – Designs, trains, and evaluates different machine learning models. Focuses on optimizing performance and comparing algorithms.\r\n\r\nXAI Specialist – Implements explainability methods (SHAP, LIME, Grad-CAM) and interprets the results to highlight feature importance.\r\n\r\nEvaluator &amp; Tester – Designs experiments to test both accuracy and interpretability. Runs user studies (teachers/students) to measure trust and usability.\r\n\r\nDocumentation &amp; Presentation Lead – Prepares reports, visualizations, and presentations to communicate findings clearly.', 'Unpaid', 3, 1, '2025-08-18 09:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `research_interest` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `university` varchar(100) DEFAULT 'NSU',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `full_name`, `profile_image`, `bio`, `research_interest`, `phone`, `linkedin`, `github`, `address`, `degree`, `university`, `start_date`, `end_date`, `cgpa`) VALUES
(2, 9, 'Saydel Islam', 'profile_9_689e03ae7562f.jpg', 'Passionate about coding, problem-solving, and technology innovation. Proficient in multiple programming languages including Python, C, and Java. Interested in AI-powered systems, data science, and cybersecurity.', 'Artificial Intelligence (AI)\r\n\r\nMachine Learning (ML)\r\n\r\nDeep Learning\r\n\r\nNatural Language Processing (NLP)\r\n\r\nComputer Vision\r\n\r\nData Science &amp; Analytics\r\n\r\nCybersecurity &amp; Privacy', '+880 1722 5053 23', 'https://www.linkedin.com/feed/', 'https://github.com/', 'House 12, Uttara, Dhaka', 'BSc in CSE', 'NSU', '2023-09-01', '0000-00-00', 3.70),
(3, 10, 'Ashraful Haque', 'profile_10_689dffc230403.jpg', 'I am lazy:)', 'Machine Learning, AI, Algorithms', '0175 9445 323', 'https://www.linkedin.com/feed/', 'https://github.com/', 'House 50, Bashundhara RA, Dhaka', 'BSc in CSE', 'NSU', '2024-01-14', '0000-00-00', 3.60),
(4, 11, 'Samia Jahan', 'profile_11_689e03fb35c2a.jpg', 'Curious and driven CS student focused on building impactful software solutions. Experienced with Flutter, Firebase, and MySQL. Enjoys combining technical expertise with creative problem-solving.', 'Software Engineering &amp; Architecture', '0171 1224 455', '', '', '', 'BSc in Mathematics', 'NSU', '2024-12-06', '0000-00-00', 3.20);

-- --------------------------------------------------------

--
-- Table structure for table `student_achievements`
--

CREATE TABLE `student_achievements` (
  `achievement_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` enum('certification','award','achievement') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_achievements`
--

INSERT INTO `student_achievements` (`achievement_id`, `student_id`, `title`, `description`, `date`, `type`) VALUES
(2, 3, 'Udemy Complete C++ Course', '', '2025-07-01', 'award'),
(3, 3, 'CSE311 Project', '', '2025-08-23', 'achievement');

-- --------------------------------------------------------

--
-- Table structure for table `student_experience`
--

CREATE TABLE `student_experience` (
  `exp_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_experience`
--

INSERT INTO `student_experience` (`exp_id`, `student_id`, `company`, `position`, `start_date`, `end_date`, `description`) VALUES
(12, 3, 'NSU', 'RA', '2025-08-01', NULL, 'Working as RA at LIB602'),
(13, 4, 'NSU', 'ST', '2025-04-01', '2025-11-30', '');

-- --------------------------------------------------------

--
-- Table structure for table `student_publications`
--

CREATE TABLE `student_publications` (
  `publication_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `journal` varchar(200) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_publications`
--

INSERT INTO `student_publications` (`publication_id`, `student_id`, `title`, `journal`, `year`, `url`) VALUES
(2, 3, 'Federated Learning at the Edge: Privacy-Preserving Training for IoT', 'Journal of Distributed Systems', 2025, NULL),
(3, 3, 'Event-Driven Microservices: Patterns for High-Throughput Systems', 'Software Architecture Review', 2023, NULL),
(4, 3, 'Graph Neural Networks for Road Traffic Forecasting', 'Journal of Intelligent Transportation Data', 2020, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_skills`
--

CREATE TABLE `student_skills` (
  `skill_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `skill_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_skills`
--

INSERT INTO `student_skills` (`skill_id`, `student_id`, `skill_name`) VALUES
(4, 3, 'Python'),
(5, 3, 'SQL'),
(6, 3, 'Data Analysis'),
(7, 3, 'JavaScript'),
(8, 3, 'Java'),
(9, 3, 'C++'),
(10, 3, 'Docker'),
(11, 3, 'Pytorch'),
(12, 3, 'Unity'),
(13, 2, 'c'),
(14, 2, 'c++'),
(15, 2, 'JavaScript'),
(16, 2, 'Java'),
(17, 2, 'Python'),
(18, 2, 'NoSQL'),
(19, 2, 'Operating Systems'),
(20, 2, 'Artificial Intelligence'),
(21, 2, 'Cybersecurity'),
(22, 2, 'Deep Learning'),
(24, 4, 'JavaScript'),
(25, 4, 'Python'),
(26, 4, 'Data Analysis'),
(27, 4, 'Java'),
(28, 4, 'Machine Learning'),
(29, 4, 'Git');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','student','faculty','organizer') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `user_type`, `is_verified`, `is_banned`, `created_at`, `updated_at`) VALUES
(9, 'a@northsouth.edu', '$2y$10$Rvi5O2ifo2Broh8yTLpsiugeE01nmWIv/O3xwIObtXxIhs3MuhpCe', 'student', 1, 0, '2025-08-14 15:17:17', '2025-08-14 15:17:17'),
(10, 'ah@northsouth.edu', '$2y$10$a.9/f6.pP3EHaiPzQlkrkO8m/BfXpWKsnyH7jJB3cUC/UwAA5wOrC', 'student', 1, 0, '2025-08-14 15:24:41', '2025-08-14 15:24:41'),
(11, 'c@northsouth.edu', '$2y$10$H3FHGX8OTjExirynQYAd4uKGYUzHzib/adxGuxXaYg0uD5GdjYJFS', 'student', 1, 0, '2025-08-14 15:42:46', '2025-08-14 15:42:46'),
(12, 'f1@northsouth.edu', '$2y$10$Sw/6rl/IOOOyJuQyCk1asuneKdM7NsClo2WgIwJKBaky5LC2CCkD6', 'faculty', 1, 0, '2025-08-14 15:46:44', '2025-08-14 15:49:36'),
(13, 'f2@northsouth.edu', '$2y$10$KrA1mmijeyjWdL5SF2LpN.Q0nvFPdHWv5w/9MYfz7O.gm8jHlYnxm', 'faculty', 1, 0, '2025-08-14 15:48:06', '2025-08-14 15:49:39'),
(14, 'admin@northsouth.edu', '$2y$10$Y6KIcOYONeHDIm6EFbs3kuIcHUCvFGW.pIyEKSVML5hSw.wmveCFK', 'admin', 1, 0, '2025-08-14 15:49:01', '2025-08-14 15:49:01'),
(15, 'f3@northsouth.edu', '$2y$10$kOZzTtjxDdgjQhsE1uf9kexvYf6n1gtU2ZpMhRHZEPVvbzMKe5FRe', 'faculty', 1, 0, '2025-08-14 15:54:43', '2025-08-14 16:04:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chatbot_logs`
--
ALTER TABLE `chatbot_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chatbot_settings`
--
ALTER TABLE `chatbot_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD PRIMARY KEY (`query_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `organizers`
--
ALTER TABLE `organizers`
  ADD PRIMARY KEY (`organizer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `research_applications`
--
ALTER TABLE `research_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`research_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `research_posts`
--
ALTER TABLE `research_posts`
  ADD PRIMARY KEY (`research_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD PRIMARY KEY (`achievement_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_experience`
--
ALTER TABLE `student_experience`
  ADD PRIMARY KEY (`exp_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_publications`
--
ALTER TABLE `student_publications`
  ADD PRIMARY KEY (`publication_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `chatbot_logs`
--
ALTER TABLE `chatbot_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `chatbot_settings`
--
ALTER TABLE `chatbot_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_queries`
--
ALTER TABLE `contact_queries`
  MODIFY `query_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `organizers`
--
ALTER TABLE `organizers`
  MODIFY `organizer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `research_applications`
--
ALTER TABLE `research_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `research_posts`
--
ALTER TABLE `research_posts`
  MODIFY `research_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_achievements`
--
ALTER TABLE `student_achievements`
  MODIFY `achievement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_experience`
--
ALTER TABLE `student_experience`
  MODIFY `exp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_publications`
--
ALTER TABLE `student_publications`
  MODIFY `publication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_skills`
--
ALTER TABLE `student_skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `chatbot_logs`
--
ALTER TABLE `chatbot_logs`
  ADD CONSTRAINT `chatbot_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`organizer_id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty`
--
ALTER TABLE `faculty`
  ADD CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `organizers`
--
ALTER TABLE `organizers`
  ADD CONSTRAINT `organizers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `research_applications`
--
ALTER TABLE `research_applications`
  ADD CONSTRAINT `research_applications_ibfk_1` FOREIGN KEY (`research_id`) REFERENCES `research_posts` (`research_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `research_applications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `research_posts`
--
ALTER TABLE `research_posts`
  ADD CONSTRAINT `research_posts_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD CONSTRAINT `student_achievements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_experience`
--
ALTER TABLE `student_experience`
  ADD CONSTRAINT `student_experience_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_publications`
--
ALTER TABLE `student_publications`
  ADD CONSTRAINT `student_publications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD CONSTRAINT `student_skills_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
