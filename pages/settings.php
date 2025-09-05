<?php 
require_once("../includes/auth.php");

// Database connection (adjust according to your setup)
// require_once("../includes/config.php");

// Handle form submissions
if ($_POST) {
    $response = array();
    
    if (isset($_POST['save_theme'])) {
        $theme = $_POST['theme'];
        // Save theme to database or session
        $_SESSION['user_theme'] = $theme;
        $response['status'] = 'success';
        $response['message'] = 'Theme saved successfully!';
    }
    
    if (isset($_POST['save_general'])) {
        $site_name = $_POST['site_name'];
        $admin_email = $_POST['admin_email'];
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Save to database
        // $stmt = $pdo->prepare("UPDATE settings SET site_name=?, admin_email=?, maintenance_mode=? WHERE id=1");
        // $stmt->execute([$site_name, $admin_email, $maintenance_mode]);
        
        $response['status'] = 'success';
        $response['message'] = 'General settings saved!';
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            // Verify current password and update
            // $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $response['status'] = 'success';
            $response['message'] = 'Password changed successfully!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Passwords do not match!';
        }
    }
    
    // Return JSON response for AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Get current settings from database or session
$current_theme = $_SESSION['user_theme'] ?? 'light';
// $settings = getSettingsFromDatabase(); // Your function
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .settings-tab {
            display: none;
        }
        
        .settings-tab.active {
            display: block;
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-button.active {
            color: #007cba;
            border-bottom-color: #007cba;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn {
            background: #007cba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: none;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-wrapper input {
            width: auto;
        }
        
        /* Dark mode styles */
        body.dark-mode {
            background: #1a1a1a;
            color: #fff;
        }
        
        body.dark-mode .settings-container {
            background: #2d2d2d;
            color: #fff;
        }
        
        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background: #3d3d3d;
            color: #fff;
            border-color: #555;
        }
        
        body.dark-mode .tab-buttons {
            border-bottom-color: #555;
        }
        
        body.dark-mode .tab-button {
            color: #ccc;
        }
        
        body.dark-mode .tab-button.active {
            color: #4a9eff;
            border-bottom-color: #4a9eff;
        }
    </style>
</head>
<body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    <div class="settings-container">
        <h2>Admin Settings</h2>
        
        <!-- Alert Messages -->
        <div id="alert-message" class="alert"></div>
        
        <!-- Tab Navigation -->
        <div class="tab-buttons">
            <button class="tab-button active" onclick="showTab('appearance')">Appearance</button>
            <button class="tab-button" onclick="showTab('general')">General</button>
            <button class="tab-button" onclick="showTab('security')">Security</button>
            <button class="tab-button" onclick="showTab('system')">System</button>
        </div>
        
        <!-- Appearance Settings -->
        <div id="appearance" class="settings-tab active">
            <h3>Appearance Settings</h3>
            <form id="appearance-form">
                <div class="form-group">
                    <label>Theme:</label>
                    <select id="theme-select" name="theme">
                        <option value="light" <?php echo $current_theme === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $current_theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        <option value="auto">Auto (System)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Primary Color:</label>
                    <input type="color" name="primary_color" value="#007cba">
                </div>
                
                <div class="form-group">
                    <label>Font Size:</label>
                    <select name="font_size">
                        <option value="small">Small</option>
                        <option value="medium" selected>Medium</option>
                        <option value="large">Large</option>
                    </select>
                </div>
                
                <button type="submit" name="save_theme" class="btn">Save Appearance</button>
            </form>
        </div>
        
        <!-- General Settings -->
        <div id="general" class="settings-tab">
            <h3>General Settings</h3>
            <form id="general-form">
                <div class="form-group">
                    <label>Site Name:</label>
                    <input type="text" name="site_name" value="My Admin Panel" required>
                </div>
                
                <div class="form-group">
                    <label>Admin Email:</label>
                    <input type="email" name="admin_email" value="admin@example.com" required>
                </div>
                
                <div class="form-group">
                    <label>Site Description:</label>
                    <textarea name="site_description" placeholder="Enter site description..."></textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="maintenance_mode" id="maintenance">
                        <label for="maintenance">Enable Maintenance Mode</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="email_notifications" id="email_notif" checked>
                        <label for="email_notif">Enable Email Notifications</label>
                    </div>
                </div>
                
                <button type="submit" name="save_general" class="btn">Save General Settings</button>
            </form>
        </div>
        
        <!-- Security Settings -->
        <div id="security" class="settings-tab">
            <h3>Security Settings</h3>
            <form id="security-form">
                <div class="form-group">
                    <label>Current Password:</label>
                    <input type="password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password:</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label>Session Timeout (minutes):</label>
                    <input type="number" name="session_timeout" value="30" min="5" max="1440">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="two_factor" id="2fa">
                        <label for="2fa">Enable Two-Factor Authentication</label>
                    </div>
                </div>
                
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
        
        <!-- System Settings -->
        <div id="system" class="settings-tab">
            <h3>System Settings</h3>
            <form id="system-form">
                <div class="form-group">
                    <label>Max Upload Size (MB):</label>
                    <input type="number" name="max_upload" value="10" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label>Timezone:</label>
                    <select name="timezone">
                        <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">America/New_York</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Format:</label>
                    <select name="date_format">
                        <option value="d/m/Y">DD/MM/YYYY</option>
                        <option value="m/d/Y">MM/DD/YYYY</option>
                        <option value="Y-m-d" selected>YYYY-MM-DD</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="debug_mode" id="debug">
                        <label for="debug">Enable Debug Mode</label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Save System Settings</button>
                <button type="button" class="btn btn-danger" onclick="clearCache()">Clear Cache</button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.settings-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Theme switching functionality
        const themeSelect = document.getElementById("theme-select");
        themeSelect.addEventListener("change", function() {
            if (this.value === "dark") {
                document.body.classList.add("dark-mode");
            } else if (this.value === "light") {
                document.body.classList.remove("dark-mode");
            } else if (this.value === "auto") {
                // Auto theme based on system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add("dark-mode");
                } else {
                    document.body.classList.remove("dark-mode");
                }
            }
        });
        
        // Form submission with AJAX
        function submitForm(formId, successMessage) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.status, data.message || successMessage);
            })
            .catch(error => {
                showAlert('error', 'An error occurred. Please try again.');
            });
        }
        
        // Show alert messages
        function showAlert(type, message) {
            const alertDiv = document.getElementById('alert-message');
            alertDiv.className = `alert ${type}`;
            alertDiv.textContent = message;
            alertDiv.style.display = 'block';
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
        
        // Handle form submissions
        document.getElementById('appearance-form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('appearance-form', 'Appearance settings saved!');
        });
        
        document.getElementById('general-form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('general-form', 'General settings saved!');
        });
        
        document.getElementById('security-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const newPass = this.new_password.value;
            const confirmPass = this.confirm_password.value;
            
            if (newPass !== confirmPass) {
                showAlert('error', 'Passwords do not match!');
                return;
            }
            
            submitForm('security-form', 'Password changed successfully!');
        });
        
        document.getElementById('system-form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('system-form', 'System settings saved!');
        });
        
        // Clear cache function
        function clearCache() {
            if (confirm('Are you sure you want to clear the cache?')) {
                // Add your cache clearing logic here
                showAlert('success', 'Cache cleared successfully!');
            }
        }
        
        // Auto-save theme preference
        themeSelect.addEventListener('change', function() {
            const formData = new FormData();
            formData.append('save_theme', '1');
            formData.append('theme', this.value);
            formData.append('ajax', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
        });
    </script>
</body>
</html>