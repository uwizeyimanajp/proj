<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/config.php';
require_once '../whatsapp_config.php';

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsappNumber = trim($_POST['whatsapp_number']);

    // Validate Rwandan number
    if (empty($whatsappNumber)) {
        $error = 'WhatsApp number is required.';
    } elseif (!isValidRwandanNumber($whatsappNumber)) {
        $error = 'Please enter a valid Rwandan phone number (e.g., +250123456789 or 250123456789).';
    } else {
        // Save the number
        if (saveWhatsAppNumber($whatsappNumber)) {
            $message = 'WhatsApp number updated successfully!';
        } else {
            $error = 'Failed to save WhatsApp number. Please try again.';
        }
    }
}

// Get current WhatsApp number
$currentNumber = '';
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_number' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentNumber = $row['setting_value'];
    }
    $conn->close();
} catch (Exception $e) {
    // Database not available
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - FreshHarvest Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modern Admin Settings Styles */
        :root {
            --primary-color: #27ae60;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: var(--white);
            box-shadow: var(--shadow);
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), #219a52);
            color: white;
            text-align: center;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--dark-color);
            text-decoration: none;
            border-bottom: 1px solid #f8f9fa;
            transition: var(--transition);
        }

        .menu-item:hover, .menu-item.active {
            background: var(--light-color);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .menu-item i {
            margin-right: 0.75rem;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: var(--light-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Settings Container */
        .settings-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .settings-header {
            background: linear-gradient(135deg, var(--primary-color), #219a52);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .settings-header h2 {
            margin: 0 0 1rem 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .settings-header p {
            margin: 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .settings-content {
            padding: 3rem 2rem;
        }

        /* Form Section */
        .form-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border-left: 5px solid var(--primary-color);
            position: relative;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .form-section h3 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section h3 i {
            color: var(--primary-color);
            font-size: 1.8rem;
        }

        /* Current Number Display */
        .current-number {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--success-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .current-number i {
            color: var(--success-color);
            font-size: 1.5rem;
        }

        .current-number strong {
            color: var(--success-color);
            font-size: 1.1rem;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1rem;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: var(--transition);
            background: var(--white);
        }

        .form-group input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
            transform: translateY(-2px);
        }

        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
            line-height: 1.5;
        }

        /* Examples Section */
        .examples {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .examples h4 {
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            font-size: 1.2rem;
        }

        .examples ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .examples li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .examples li:last-child {
            border-bottom: none;
        }

        .example-format {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            color: var(--primary-color);
            font-weight: 600;
            border: 1px solid #dee2e6;
        }

        /* Test Section */
        .test-section {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
            border-left: 4px solid var(--secondary-color);
        }

        .test-section h4 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .test-link {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }

        .test-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
            background: linear-gradient(135deg, #128c7e 0%, #075e54 100%);
        }

        .test-link i {
            font-size: 1.1rem;
        }

        /* Buttons */
        .btn {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #219a52 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f3f4;
        }

        /* Messages */
        .message {
            padding: 1.25rem 1.75rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message i {
            font-size: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1rem;
            }

            .settings-container {
                margin: 0;
                border-radius: 0;
            }

            .settings-header {
                padding: 2rem 1rem;
            }

            .settings-header h2 {
                font-size: 2rem;
            }

            .settings-content {
                padding: 2rem 1rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .examples li {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Loading animation */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .btn.loading {
            animation: pulse 1.5s infinite;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-leaf"></i> FreshHarvest</h2>
            <p>Admin Dashboard</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="add_item.php" class="menu-item">
                <i class="fas fa-plus"></i> Add Item
            </a>
            <a href="categories.php" class="menu-item">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="settings.php" class="menu-item active">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-home"></i> Public Site
            </a>
            <a href="?logout=1" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                    <br>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Settings Container -->
        <div class="settings-container">
            <div class="settings-header">
                <h2><i class="fas fa-cog"></i> System Settings</h2>
                <p>Configure your FreshHarvest system settings</p>
            </div>

            <form method="POST" class="settings-form">
                <div class="form-section">
                    <h3><i class="fab fa-whatsapp"></i> WhatsApp Configuration</h3>

                    <?php if (!empty($currentNumber)): ?>
                        <div class="current-number">
                            <strong>Current WhatsApp Number:</strong> <?php echo htmlspecialchars($currentNumber); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="whatsapp_number">Rwandan WhatsApp Business Number</label>
                        <input type="text"
                               id="whatsapp_number"
                               name="whatsapp_number"
                               value="<?php echo htmlspecialchars($currentNumber); ?>"
                               placeholder="+250123456789 or 250123456789"
                               required>
                        <div class="help-text">
                            Enter your Rwandan WhatsApp Business number. This will be used for all customer orders and inquiries.
                        </div>
                    </div>

                    <div class="examples">
                        <h4>Valid Rwandan Number Formats:</h4>
                        <ul>
                            <li>
                                <span>International format</span>
                                <span class="example-format">+250123456789</span>
                            </li>
                            <li>
                                <span>Local format</span>
                                <span class="example-format">250123456789</span>
                            </li>
                            <li>
                                <span>With spaces</span>
                                <span class="example-format">+250 123 456 789</span>
                            </li>
                        </ul>
                    </div>

                    <div class="test-section">
                        <h4>Test WhatsApp Integration</h4>
                        <p>Click below to test your WhatsApp number with a sample message:</p>
                        <a href="<?php echo generateInquiryURL('Test Message from Admin Settings'); ?>" class="test-link" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            Send Test Message
                        </a>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Auto-format phone number input
        const phoneInput = document.getElementById('whatsapp_number');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

            // Auto-add +250 if it starts with 250 or just numbers
            if (value.length >= 9) {
                if (value.startsWith('250')) {
                    value = '+' + value;
                } else if (!value.startsWith('+') && value.length === 9) {
                    value = '+250' + value;
                }
            }

            e.target.value = value;
        });
    </script>
</body>
</html>
