<?php
// admin/chatbot-settings.php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $api_key = $_POST['api_key'] ?? '';
        $model = $_POST['model'] ?? 'gpt-4o';
        $max_tokens = $_POST['max_tokens'] ?? 500;
        $temperature = $_POST['temperature'] ?? 0.7;
        $enabled = isset($_POST['enabled']) ? 1 : 0;

        // Update settings in database
        $settings = [
            'openai_api_key' => $api_key,
            'model' => $model,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'enabled' => $enabled
        ];

        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("UPDATE chatbot_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
            $stmt->close();
        }

        $success_message = "Chatbot settings updated successfully!";
    }

    if (isset($_POST['clear_logs'])) {
        $stmt = $conn->prepare("DELETE FROM chatbot_logs");
        $stmt->execute();
        $stmt->close();
        $success_message = "Chat logs cleared successfully!";
    }
}

// Get current settings
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM chatbot_settings");
$stmt->execute();
$result = $stmt->get_result();
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$stmt->close();

// Get chat logs statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_conversations,
        COUNT(DISTINCT user_id) as unique_users,
        DATE(MAX(created_at)) as last_conversation
    FROM chatbot_logs
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get recent conversations
$stmt = $conn->prepare("
    SELECT cl.*, u.email, u.user_type 
    FROM chatbot_logs cl
    JOIN users u ON cl.user_id = u.user_id
    ORDER BY cl.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_logs = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .settings-header {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .settings-card h2 {
            color: #0f172a;
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #10b981;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 0.75rem;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table th,
        .logs-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .logs-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Include admin sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>NSU LinkUp</h2>
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    Users
                </a>
                <a href="chatbot-settings.php" class="active">
                    <i class="fas fa-robot"></i>
                    Chatbot Settings
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="settings-container">
                <div class="settings-header">
                    <h1>AI Chatbot Settings</h1>
                    <p>Configure the AI assistant settings and monitor usage</p>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="settings-card">
                    <h2><i class="fas fa-chart-bar"></i> Usage Statistics</h2>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['total_conversations'] ?? 0; ?></div>
                            <div class="stat-label">Total Conversations</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['unique_users'] ?? 0; ?></div>
                            <div class="stat-label">Unique Users</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['last_conversation'] ?? 'Never'; ?></div>
                            <div class="stat-label">Last Activity</div>
                        </div>
                    </div>
                </div>

                <div class="settings-grid">
                    <!-- OpenAI Settings -->
                    <div class="settings-card">
                        <h2><i class="fas fa-cog"></i> OpenAI Configuration</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="api_key">API Key</label>
                                <input type="password"
                                    id="api_key"
                                    name="api_key"
                                    value="<?php echo htmlspecialchars($settings['openai_api_key'] ?? ''); ?>"
                                    placeholder="sk-...">
                            </div>

                            <div class="form-group">
                                <label for="model">Model</label>
                                <select id="model" name="model">
                                    <option value="gpt-4o" <?php echo ($settings['model'] ?? '') === 'gpt-4o' ? 'selected' : ''; ?>>GPT-4o (Recommended)</option>
                                    <option value="gpt-4-turbo" <?php echo ($settings['model'] ?? '') === 'gpt-4-turbo' ? 'selected' : ''; ?>>GPT-4 Turbo</option>
                                    <option value="gpt-3.5-turbo" <?php echo ($settings['model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="max_tokens">Max Tokens</label>
                                <input type="number"
                                    id="max_tokens"
                                    name="max_tokens"
                                    value="<?php echo $settings['max_tokens'] ?? 500; ?>"
                                    min="50"
                                    max="2000">
                            </div>

                            <div class="form-group">
                                <label for="temperature">Temperature (0-1)</label>
                                <input type="number"
                                    id="temperature"
                                    name="temperature"
                                    value="<?php echo $settings['temperature'] ?? 0.7; ?>"
                                    min="0"
                                    max="1"
                                    step="0.1">
                            </div>

                            <div class="form-group">
                                <label for="enabled">Enable Chatbot</label>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                        id="enabled"
                                        name="enabled"
                                        <?php echo ($settings['enabled'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <button type="submit" name="update_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </form>
                    </div>

                    <!-- Recent Conversations -->
                    <div class="settings-card">
                        <h2><i class="fas fa-comments"></i> Recent Conversations</h2>
                        <div style="overflow-x: auto;">
                            <table class="logs-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = $recent_logs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['email']); ?></td>
                                            <td><?php echo ucfirst($log['user_type']); ?></td>
                                            <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <form method="POST" style="margin-top: 1.5rem;" onsubmit="return confirm('Are you sure you want to clear all chat logs?');">
                            <button type="submit" name="clear_logs" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Clear All Logs
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>