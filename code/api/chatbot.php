<?php
// api/chatbot.php

// Prevent any output before JSON
ob_start();

// Error handling - suppress notices
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any output that might have been generated
ob_clean();

// Set content type
header('Content-Type: application/json');

// Include required files
require_once '../includes/db_connect.php';

// Check if user is logged in and is student or faculty
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'faculty'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$context = $input['context'] ?? [];

if (empty($userMessage)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'No message provided']);
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    // Analyze intent and get data
    $intent = analyzeIntent($userMessage);
    $dbData = getRelevantData($intent, $userMessage, $user_id, $user_type, $conn);

    // Get OpenAI settings
    $settingsStmt = $conn->prepare("SELECT setting_key, setting_value FROM chatbot_settings");
    $settingsStmt->execute();
    $settingsResult = $settingsStmt->get_result();
    $settings = [];
    while ($row = $settingsResult->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $settingsStmt->close();

    // Check if chatbot is enabled
    if (!isset($settings['enabled']) || $settings['enabled'] != '1') {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Chatbot is currently disabled. Please contact administrator.'
        ]);
        exit();
    }

    // Check if API key exists
    if (empty($settings['openai_api_key'])) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'error' => 'OpenAI API key not configured. Please contact administrator.'
        ]);
        exit();
    }

    // Prepare system message
    $systemMessage = prepareSystemMessage($dbData, $user_type);

    // Prepare messages for OpenAI
    $messages = [
        ['role' => 'system', 'content' => $systemMessage]
    ];

    // Add conversation context
    foreach ($context as $ctx) {
        $messages[] = ['role' => 'user', 'content' => $ctx['user']];
        $messages[] = ['role' => 'assistant', 'content' => $ctx['assistant']];
    }

    // Add current message
    $messages[] = ['role' => 'user', 'content' => $userMessage];

    // Call OpenAI
    $openAIResponse = callOpenAI($settings['openai_api_key'], $messages, $settings);

    // Log conversation
    logChatConversation($user_id, $userMessage, $openAIResponse, $conn);

    // Clear any unwanted output and return clean JSON
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'response' => $openAIResponse,
        'data' => $dbData
    ]);
} catch (Exception $e) {
    error_log("Chatbot error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}

// Function to analyze user intent
function analyzeIntent($message)
{
    $message = strtolower($message);

    $intents = [
        'faculty_info' => ['faculty', 'professor', 'teacher', 'office', 'email', 'room', 'contact', 'office hours'],
        'research_posts' => ['research', 'opportunity', 'position', 'apply', 'deadline', 'cgpa', 'requirement'],
        'skills' => ['skill', 'learn', 'need', 'require', 'qualification', 'knowledge'],
        'best_match' => ['best', 'recommend', 'suitable', 'match', 'good for me', 'should i'],
        'ai_research' => ['ai', 'artificial intelligence', 'machine learning', 'deep learning', 'neural', 'nlp'],
    ];

    $detectedIntents = [];

    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $detectedIntents[] = $intent;
                break;
            }
        }
    }

    return $detectedIntents;
}

// Function to get relevant data from database
function getRelevantData($intents, $message, $user_id, $user_type, $conn)
{
    $data = [];

    // Get faculty information if mentioned
    if (in_array('faculty_info', $intents) || in_array('ai_research', $intents)) {
        $query = "SELECT f.*, u.email 
                  FROM faculty f 
                  JOIN users u ON f.user_id = u.user_id 
                  WHERE 1=1";

        $facultyStmt = $conn->prepare($query);
        $facultyStmt->execute();
        $facultyResult = $facultyStmt->get_result();
        $facultyList = [];

        while ($row = $facultyResult->fetch_assoc()) {
            // Check if this faculty is mentioned or matches AI research
            $nameParts = explode(' ', strtolower($row['full_name']));
            $mentioned = false;

            foreach ($nameParts as $part) {
                if (strlen($part) > 2 && strpos(strtolower($message), $part) !== false) {
                    $mentioned = true;
                    break;
                }
            }

            // Include if mentioned or if asking about AI and faculty has AI interests
            if (
                $mentioned ||
                (in_array('ai_research', $intents) &&
                    (stripos($row['research_interests'], 'ai') !== false ||
                        stripos($row['research_interests'], 'artificial') !== false ||
                        stripos($row['research_interests'], 'machine') !== false ||
                        stripos($row['research_interests'], 'learning') !== false))
            ) {
                $facultyList[] = $row;
            }
        }

        // If no specific faculty found but asking about faculty, get all
        if (empty($facultyList) && in_array('faculty_info', $intents)) {
            $facultyStmt->execute();
            $facultyResult = $facultyStmt->get_result();
            while ($row = $facultyResult->fetch_assoc()) {
                $facultyList[] = $row;
            }
        }

        if (!empty($facultyList)) {
            $data['faculty'] = $facultyList;
        }
    }

    // Get research posts
    if (in_array('research_posts', $intents) || in_array('best_match', $intents)) {
        // Get student's CGPA and skills if they're a student
        $studentData = null;
        if ($user_type === 'student') {
            $studentStmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
            $studentStmt->bind_param("i", $user_id);
            $studentStmt->execute();
            $studentData = $studentStmt->get_result()->fetch_assoc();

            // Get student skills
            if ($studentData) {
                $skillsStmt = $conn->prepare("SELECT skill_name FROM student_skills WHERE student_id = ?");
                $skillsStmt->bind_param("i", $studentData['student_id']);
                $skillsStmt->execute();
                $skillsResult = $skillsStmt->get_result();
                $studentSkills = [];
                while ($skill = $skillsResult->fetch_assoc()) {
                    $studentSkills[] = strtolower($skill['skill_name']);
                }
                $studentData['skills'] = $studentSkills;
            }
        }

        // Get active research posts
        $researchQuery = "SELECT r.*, f.full_name as faculty_name, f.research_interests as faculty_interests
                         FROM research_posts r
                         JOIN faculty f ON r.faculty_id = f.faculty_id
                         WHERE r.is_active = 1 AND r.apply_deadline >= CURDATE()";

        $researchStmt = $conn->prepare($researchQuery);
        $researchStmt->execute();
        $researchResult = $researchStmt->get_result();
        $researchPosts = [];

        while ($post = $researchResult->fetch_assoc()) {
            // Calculate match percentage for student
            if ($studentData) {
                $matchScore = 0;
                $factors = 0;

                // Check CGPA
                if ($studentData['cgpa'] && $post['min_cgpa']) {
                    $factors++;
                    if ($studentData['cgpa'] >= $post['min_cgpa']) {
                        $matchScore += 40; // 40% weight for CGPA
                    }
                }

                // Check skills match
                if (!empty($studentData['skills']) && $post['tags']) {
                    $factors++;
                    $requiredSkills = array_map('trim', explode(',', strtolower($post['tags'])));
                    $matchingSkills = array_intersect($studentData['skills'], $requiredSkills);
                    $skillMatchPercent = count($requiredSkills) > 0 ?
                        (count($matchingSkills) / count($requiredSkills)) * 60 : 0;
                    $matchScore += $skillMatchPercent; // 60% weight for skills
                }

                if ($factors > 0) {
                    $post['match_percentage'] = round($matchScore);
                }
            }

            // Include if AI-related or general query
            if (in_array('ai_research', $intents)) {
                if (
                    stripos($post['title'], 'ai') !== false ||
                    stripos($post['title'], 'artificial') !== false ||
                    stripos($post['title'], 'machine') !== false ||
                    stripos($post['title'], 'learning') !== false ||
                    stripos($post['description'], 'ai') !== false ||
                    stripos($post['description'], 'machine learning') !== false
                ) {
                    $researchPosts[] = $post;
                }
            } else {
                $researchPosts[] = $post;
            }
        }

        // Sort by match percentage if available
        if ($studentData) {
            usort($researchPosts, function ($a, $b) {
                $aMatch = $a['match_percentage'] ?? 0;
                $bMatch = $b['match_percentage'] ?? 0;
                return $bMatch - $aMatch;
            });
        }

        if (!empty($researchPosts)) {
            $data['research'] = array_slice($researchPosts, 0, 5); // Return top 5 matches
        }
    }

    // Get skills information
    if (in_array('skills', $intents)) {
        // Extract skills from research posts mentioned
        $skillsNeeded = [];

        if (
            stripos($message, 'machine learning') !== false ||
            stripos($message, 'ai') !== false ||
            stripos($message, 'artificial intelligence') !== false
        ) {
            $skillsNeeded = [
                'Python',
                'TensorFlow',
                'PyTorch',
                'Scikit-learn',
                'NumPy',
                'Pandas',
                'Machine Learning',
                'Deep Learning',
                'Neural Networks',
                'Natural Language Processing',
                'Computer Vision',
                'Statistics',
                'Linear Algebra',
                'Calculus',
                'Data Preprocessing'
            ];
        }

        if (!empty($skillsNeeded)) {
            $data['skills'] = $skillsNeeded;
        }
    }

    return $data;
}

// Function to prepare system message
function prepareSystemMessage($dbData, $user_type)
{
    $systemMessage = "You are the NSU LinkUp AI Assistant, helping {$user_type}s at North South University.
    
IMPORTANT RULES:
1. Only answer questions related to NSU LinkUp data and the university
2. Base your responses ONLY on the provided database information
3. Do not make up information not in the database
4. Be helpful, professional, and concise
5. If asked about topics outside NSU LinkUp, politely redirect to relevant topics

Available Information:\n";

    if (isset($dbData['faculty'])) {
        $systemMessage .= "\nFACULTY INFORMATION:\n";
        foreach ($dbData['faculty'] as $faculty) {
            $systemMessage .= "- {$faculty['full_name']}: {$faculty['title']}, Office: {$faculty['office']}, ";
            $systemMessage .= "Email: {$faculty['email']}, Research: {$faculty['research_interests']}\n";
        }
    }

    if (isset($dbData['research'])) {
        $systemMessage .= "\nRESEARCH OPPORTUNITIES:\n";
        foreach ($dbData['research'] as $research) {
            $systemMessage .= "- {$research['title']} by {$research['faculty_name']}: ";
            $systemMessage .= "Min CGPA: {$research['min_cgpa']}, Skills: {$research['tags']}, ";
            $systemMessage .= "Deadline: {$research['apply_deadline']}\n";
        }
    }

    $systemMessage .= "\nProvide specific, actionable advice based on this information.";

    return $systemMessage;
}

// Function to call OpenAI API
function callOpenAI($apiKey, $messages, $settings)
{
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => $settings['model'] ?? 'gpt-4o',
        'messages' => $messages,
        'max_tokens' => intval($settings['max_tokens'] ?? 500),
        'temperature' => floatval($settings['temperature'] ?? 0.7)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception('CURL error: ' . $curlError);
    }

    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
        throw new Exception('OpenAI API error (HTTP ' . $httpCode . '): ' . $errorMessage);
    }

    $result = json_decode($response, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Invalid OpenAI response structure');
    }

    return $result['choices'][0]['message']['content'];
}

// Function to log chat conversation
function logChatConversation($user_id, $userMessage, $botResponse, $conn)
{
    $stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, user_message, bot_response) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $userMessage, $botResponse);
        $stmt->execute();
        $stmt->close();
    }
}
