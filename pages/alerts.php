<!DOCTYPE html>
<html>
<head>
    <title>Alert Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Alert Display Styles */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        }

        .alert {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            position: relative;
        }

        .alert.show {
            opacity: 1;
            transform: translateX(0);
        }

        .alert.success {
            border-left-color: #28a745;
        }

        .alert.warning {
            border-left-color: #ffc107;
        }

        .alert.error {
            border-left-color: #dc3545;
        }

        .alert.info {
            border-left-color: #17a2b8;
        }

        .alert-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 8px;
        }

        .alert-title {
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
        }

        .alert-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .alert-message {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }

        .alert-timestamp {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }

        /* Control Panel */
        .control-panel {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .control-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .control-group {
            flex: 1;
            min-width: 200px;
        }

        .control-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        button.danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }

        button.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        button.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        /* Alert History */
        .alert-history {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .history-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-details {
            flex: 1;
        }

        .history-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 10px;
        }

        .history-type.success { background: #d4edda; color: #155724; }
        .history-type.warning { background: #fff3cd; color: #856404; }
        .history-type.error { background: #f8d7da; color: #721c24; }
        .history-type.info { background: #d1ecf1; color: #0c5460; }

        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Sound Toggle */
        .sound-toggle {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            border-radius: 50px;
            padding: 15px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sound-toggle:hover {
            transform: scale(1.05);
        }

        .sound-toggle.muted {
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Alert Container -->
    <div class="alert-container" id="alertContainer"></div>

    <!-- Sound Toggle -->
    <div class="sound-toggle" id="soundToggle" onclick="toggleSound()">
        <span id="soundIcon">🔊</span> <span id="soundText">Sound ON</span>
    </div>

    <div class="container">
        <h2>🚨 Advanced Alert Management System</h2>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number" id="totalAlerts">0</div>
                <div class="stat-label">Total Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayAlerts">0</div>
                <div class="stat-label">Today's Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="errorAlerts">0</div>
                <div class="stat-label">Error Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="avgResponseTime">0</div>
                <div class="stat-label">Avg Response (s)</div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <h3>🎛️ Alert Control Panel</h3>
            
            <div class="control-row">
                <div class="control-group">
                    <label for="alertType">Alert Type:</label>
                    <select id="alertType">
                        <option value="success">Success</option>
                        <option value="info">Information</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="alertTitle">Alert Title:</label>
                    <input type="text" id="alertTitle" placeholder="Enter alert title">
                </div>
            </div>

            <div class="control-row">
                <div class="control-group">
                    <label for="alertMessage">Alert Message:</label>
                    <textarea id="alertMessage" placeholder="Enter alert message"></textarea>
                </div>
            </div>

            <div class="control-row">
                <div class="control-group">
                    <label for="alertDuration">Auto-close after (seconds):</label>
                    <input type="number" id="alertDuration" value="5" min="1" max="60">
                </div>
                
                <div class="control-group">
                    <label for="alertSound">Play Sound:</label>
                    <select id="alertSound">
                        <option value="none">No Sound</option>
                        <option value="beep">Beep</option>
                        <option value="notification">Notification</option>
                        <option value="error">Error Sound</option>
                    </select>
                </div>
            </div>

            <div class="control-row">
                <button onclick="showCustomAlert()">🚨 Show Custom Alert</button>
                <button onclick="showRandomAlert()" class="warning">🎲 Random Alert</button>
                <button onclick="showBulkAlerts()" class="success">📤 Bulk Test (5 alerts)</button>
                <button onclick="clearAllAlerts()" class="danger">🗑️ Clear All</button>
            </div>

            <div class="control-row">
                <button onclick="testTrafficAlert()">🚦 Traffic Alert</button>
                <button onclick="testSystemAlert()">⚙️ System Alert</button>
                <button onclick="testSecurityAlert()">🔒 Security Alert</button>
                <button onclick="testMaintenanceAlert()">🔧 Maintenance Alert</button>
            </div>
        </div>

        <!-- Alert History -->
        <div class="alert-history">
            <div class="history-header">
                <h3>📋 Alert History</h3>
                <button onclick="clearHistory()" class="danger">Clear History</button>
            </div>
            <div id="alertHistory">
                <p style="text-align: center; color: #999; padding: 20px;">No alerts in history yet.</p>
            </div>
        </div>
    </div>

    <!-- Audio elements for sounds -->
    <audio id="beepSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmccBDuMx+Hkc1sfLY+x5qKGLgopbNPVs2ckTUJFc5LVtmU8FABbqOPzlwl2sOKOXKhaBOK+ZjKkqjnJflhAeQpAmWlVl+/oaTczVJZMYpPp3KiUpFFMKJrvPjJ1S3bLZgVHdHV8qPGW3YBLIJSppJu+xNaFlXRUNqlwcMRGJnmBh6cCjYQ1yoNEFNcHNNNbE9phLjgwq3eQs1OlmI2kG0hWmYfmKAsjxCHRh3aDSREGGYfDcLmL" type="audio/wav">
    </audio>

    <script>
        // Global variables
        let alertCounter = 0;
        let alertHistory = [];
        let soundEnabled = true;
        let alertStats = {
            total: 0,
            today: 0,
            error: 0,
            responseTime: []
        };

        // Alert icons mapping
        const alertIcons = {
            success: '✅',
            info: 'ℹ️',
            warning: '⚠️',
            error: '❌'
        };

        // Create and show alert
        function createAlert(type, title, message, duration = 5000, sound = 'none') {
            const alertId = ++alertCounter;
            const timestamp = new Date();
            
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${type}`;
            alertDiv.id = `alert-${alertId}`;
            
            alertDiv.innerHTML = `
                <div class="alert-header">
                    <div class="alert-title">
                        <span class="alert-icon">${alertIcons[type]}</span>
                        ${title}
                    </div>
                    <button class="alert-close" onclick="closeAlert(${alertId})">&times;</button>
                </div>
                <div class="alert-message">${message}</div>
                <div class="alert-timestamp">${timestamp.toLocaleTimeString()}</div>
            `;

            // Add to container
            document.getElementById('alertContainer').appendChild(alertDiv);
            
            // Show animation
            setTimeout(() => {
                alertDiv.classList.add('show');
            }, 100);

            // Play sound
            playAlertSound(sound, type);

            // Auto close
            if (duration > 0) {
                setTimeout(() => {
                    closeAlert(alertId);
                }, duration);
            }

            // Add to history
            addToHistory(type, title, message, timestamp);
            
            // Update stats
            updateStats(type);

            return alertId;
        }

        // Close alert
        function closeAlert(alertId) {
            const alertDiv = document.getElementById(`alert-${alertId}`);
            if (alertDiv) {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    alertDiv.remove();
                }, 300);
            }
        }

        // Clear all alerts
        function clearAllAlerts() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }, index * 100);
            });
        }

        // Show custom alert from form
        function showCustomAlert() {
            const type = document.getElementById('alertType').value;
            const title = document.getElementById('alertTitle').value || 'Custom Alert';
            const message = document.getElementById('alertMessage').value || 'This is a custom alert message.';
            const duration = parseInt(document.getElementById('alertDuration').value) * 1000;
            const sound = document.getElementById('alertSound').value;

            createAlert(type, title, message, duration, sound);
        }

        // Show random alert
        function showRandomAlert() {
            const types = ['success', 'info', 'warning', 'error'];
            const titles = [
                'System Update', 'User Login', 'Data Backup', 'Connection Lost',
                'File Uploaded', 'Process Complete', 'Warning Alert', 'Error Occurred'
            ];
            const messages = [
                'Operation completed successfully.',
                'Please check the system status.',
                'Action required from administrator.',
                'Something went wrong, please try again.',
                'Your request has been processed.',
                'System is running normally.',
                'Please verify your input.',
                'Connection has been restored.'
            ];

            const randomType = types[Math.floor(Math.random() * types.length)];
            const randomTitle = titles[Math.floor(Math.random() * titles.length)];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];

            createAlert(randomType, randomTitle, randomMessage, 5000, 'notification');
        }

        // Show bulk alerts for testing
        function showBulkAlerts() {
            const alerts = [
                { type: 'info', title: 'System Started', message: 'Application has been initialized successfully.' },
                { type: 'success', title: 'Data Loaded', message: 'All user data has been loaded successfully.' },
                { type: 'warning', title: 'High Memory Usage', message: 'System memory usage is above 80%.' },
                { type: 'error', title: 'Database Error', message: 'Failed to connect to the database server.' },
                { type: 'success', title: 'Backup Complete', message: 'System backup has been completed successfully.' }
            ];

            alerts.forEach((alert, index) => {
                setTimeout(() => {
                    createAlert(alert.type, alert.title, alert.message, 7000, 'beep');
                }, index * 1000);
            });
        }

        // Specific alert types
        function testTrafficAlert() {
            createAlert('warning', '🚦 Traffic Alert', 'Heavy traffic detected on Route 101. Expected delay: 15 minutes.', 8000, 'notification');
        }

        function testSystemAlert() {
            createAlert('info', '⚙️ System Maintenance', 'Scheduled maintenance will begin in 30 minutes. Please save your work.', 10000, 'beep');
        }

        function testSecurityAlert() {
            createAlert('error', '🔒 Security Alert', 'Suspicious login attempt detected from unknown IP address.', 0, 'error');
        }

        function testMaintenanceAlert() {
            createAlert('success', '🔧 Maintenance Complete', 'System maintenance has been completed successfully. All services are now online.', 6000, 'notification');
        }

        // Play alert sounds
        function playAlertSound(soundType, alertType) {
            if (!soundEnabled) return;

            // Create audio context for different sounds
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            
            if (soundType === 'beep' || (soundType === 'none' && alertType === 'error')) {
                // Simple beep sound
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = alertType === 'error' ? 800 : 600;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            }
        }

        // Toggle sound
        function toggleSound() {
            soundEnabled = !soundEnabled;
            const toggle = document.getElementById('soundToggle');
            const icon = document.getElementById('soundIcon');
            const text = document.getElementById('soundText');
            
            if (soundEnabled) {
                toggle.classList.remove('muted');
                icon.textContent = '🔊';
                text.textContent = 'Sound ON';
            } else {
                toggle.classList.add('muted');
                icon.textContent = '🔇';
                text.textContent = 'Sound OFF';
            }
        }

        // Add to history
        function addToHistory(type, title, message, timestamp) {
            alertHistory.unshift({
                id: alertCounter,
                type,
                title,
                message,
                timestamp
            });

            // Keep only last 50 alerts
            if (alertHistory.length > 50) {
                alertHistory = alertHistory.slice(0, 50);
            }

            updateHistoryDisplay();
        }

        // Update history display
        function updateHistoryDisplay() {
            const historyContainer = document.getElementById('alertHistory');
            
            if (alertHistory.length === 0) {
                historyContainer.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No alerts in history yet.</p>';
                return;
            }

            historyContainer.innerHTML = alertHistory.map(alert => `
                <div class="history-item">
                    <div class="history-details">
                        <span class="history-type ${alert.type}">${alert.type}</span>
                        <strong>${alert.title}</strong>
                        <div style="color: #666; font-size: 13px; margin-top: 4px;">${alert.message}</div>
                    </div>
                    <div style="color: #999; font-size: 12px;">
                        ${alert.timestamp.toLocaleString()}
                    </div>
                </div>
            `).join('');
        }

        // Clear history
        function clearHistory() {
            if (confirm('Are you sure you want to clear all alert history?')) {
                alertHistory = [];
                updateHistoryDisplay();
                alertStats = { total: 0, today: 0, error: 0, responseTime: [] };
                updateStatsDisplay();
            }
        }

        // Update statistics
        function updateStats(type) {
            alertStats.total++;
            alertStats.today++; // In real app, check if today
            if (type === 'error') alertStats.error++;
            
            // Simulate response time
            alertStats.responseTime.push(Math.random() * 2 + 0.5);
            if (alertStats.responseTime.length > 100) {
                alertStats.responseTime = alertStats.responseTime.slice(-100);
            }

            updateStatsDisplay();
        }

        // Update statistics display
        function updateStatsDisplay() {
            document.getElementById('totalAlerts').textContent = alertStats.total;
            document.getElementById('todayAlerts').textContent = alertStats.today;
            document.getElementById('errorAlerts').textContent = alertStats.error;
            
            const avgResponse = alertStats.responseTime.length > 0 
                ? (alertStats.responseTime.reduce((a, b) => a + b, 0) / alertStats.responseTime.length).toFixed(1)
                : 0;
            document.getElementById('avgResponseTime').textContent = avgResponse;
        }

        // Initialize demo alert (your original code enhanced)
        function initializeDemoAlert() {
            setTimeout(() => {
                createAlert('info', '🎉 Welcome!', 'Alert system initialized successfully. This enhanced alert system includes sound, history, and statistics!', 8000, 'notification');
            }, 1000);
        }

        // Initialize the system
        document.addEventListener('DOMContentLoaded', function() {
            initializeDemoAlert();
            
            // Auto-generate random alerts every 30 seconds for demo
            setInterval(() => {
                if (Math.random() > 0.7) { // 30% chance
                    showRandomAlert();
                }
            }, 30000);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        document.getElementById('alertType').value = 'success';
                        break;
                    case '2':
                        e.preventDefault();
                        document.getElementById('alertType').value = 'info';
                        break;
                    case '3':
                        e.preventDefault();
                        document.getElementById('alertType').value = 'warning';
                        break;
                    case '4':
                        e.preventDefault();
                        document.getElementById('alertType').value = 'error';
                        break;
                    case 'Enter':
                        e.preventDefault();
                        showCustomAlert();
                        break;
                    case 'Escape':
                        e.preventDefault();
                        clearAllAlerts();
                        break;
                }
            }
        });
    </script>
</body>
</html>