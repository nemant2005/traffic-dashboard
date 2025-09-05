<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Traffic Monitoring</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../plugins/chartjs/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .navbar .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .navbar a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.1);
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .live-text {
            color: #10b981;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-section {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section h1 {
            font-size: 2rem;
            color: white;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: rgba(255,255,255,0.8);
            font-size: 1.1rem;
        }

        .refresh-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .refresh-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .refresh-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .refresh-btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .auto-refresh-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 24px;
            background: rgba(255,255,255,0.3);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-switch.active {
            background: #10b981;
        }

        .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .toggle-switch.active .toggle-slider {
            left: 28px;
        }

        .last-updated {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-card.loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-change.positive {
            color: #059669;
        }

        .stat-change.negative {
            color: #dc2626;
        }

        .chart-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .activity-feed {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 14px;
            color: white;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            display: none;
        }

        @media (max-width: 768px) {
            .chart-section {
                grid-template-columns: 1fr;
            }
            
            .bottom-grid {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .navbar .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .welcome-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .refresh-controls {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">
            <i class="fas fa-traffic-light"></i>
            Traffic Dashboard
        </div>
        <div class="nav-links">
            <div class="live-indicator">
                <div class="live-dot"></div>
                <span class="live-text">LIVE</span>
            </div>
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="add-traffic.php"><i class="fas fa-plus"></i> Add Traffic</a>
            <a href="view-traffic.php"><i class="fas fa-table"></i> View Data</a>
            <a href="map.php"><i class="fas fa-map"></i> Map</a>
            <a href="alerts.php"><i class="fas fa-bell"></i> Alerts</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="error-message" id="errorMessage">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="errorText"></span>
    </div>

    <div class="container">
        <div class="welcome-section">
            <div>
                <h1>Welcome Nemant! 👋</h1>
                <p id="currentDate">Loading...</p>
            </div>
            <div class="refresh-controls">
                <button class="refresh-btn" id="refreshBtn" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
                <div class="auto-refresh-toggle">
                    <span>Auto-refresh</span>
                    <div class="toggle-switch active" id="autoRefreshToggle" onclick="toggleAutoRefresh()">
                        <div class="toggle-slider"></div>
                    </div>
                </div>
                <div class="last-updated" id="lastUpdated">
                    Never updated
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card" id="activeIncidentsCard">
                <div class="stat-header">
                    <div class="stat-title">Active Incidents</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value" id="activeIncidentsValue">Loading...</div>
                <div class="stat-change" id="activeIncidentsChange">
                    <i class="fas fa-arrow-up"></i> Loading...
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="activeIncidentsProgress" style="width: 0%; background: linear-gradient(135deg, #667eea, #764ba2);"></div>
                </div>
            </div>

            <div class="stat-card" id="totalReportsCard">
                <div class="stat-header">
                    <div class="stat-title">Total Reports</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalReportsValue">Loading...</div>
                <div class="stat-change" id="totalReportsChange">
                    <i class="fas fa-arrow-up"></i> Loading...
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="totalReportsProgress" style="width: 0%; background: linear-gradient(135deg, #f093fb, #f5576c);"></div>
                </div>
            </div>

            <div class="stat-card" id="responseTimeCard">
                <div class="stat-header">
                    <div class="stat-title">Response Time</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value" id="responseTimeValue">Loading...</div>
                <div class="stat-change" id="responseTimeChange">
                    <i class="fas fa-arrow-down"></i> Loading...
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="responseTimeProgress" style="width: 0%; background: linear-gradient(135deg, #4facfe, #00f2fe);"></div>
                </div>
            </div>

            <div class="stat-card" id="systemStatusCard">
                <div class="stat-header">
                    <div class="stat-title">System Status</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="systemStatusValue">Loading...</div>
                <div class="stat-change" id="systemStatusChange">
                    <i class="fas fa-arrow-up"></i> Loading...
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="systemStatusProgress" style="width: 0%; background: linear-gradient(135deg, #43e97b, #38f9d7);"></div>
                </div>
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-card">
                <div class="chart-title">Traffic Severity Overview - Last 7 Days</div>
                <canvas id="severityChart" height="120"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-title">Recent Activities</div>
                <div class="activity-feed" id="activityFeed">
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin"></i>
                        Loading activities...
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-grid">
            <div class="chart-card">
                <div class="chart-title">Latest Projects</div>
                <div id="projectsList" style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin"></i>
                        Loading projects...
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Monthly Trend</div>
                <canvas id="trendChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <script>
        let severityChart, trendChart;
        let autoRefreshInterval;
        let isAutoRefreshEnabled = true;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentDate();
            refreshData();
            startAutoRefresh();
        });

        function updateCurrentDate() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dateString = now.toLocaleDateString('en-US', options);
            document.getElementById('currentDate').textContent = `Today is ${dateString} - Here's your traffic monitoring overview`;
        }

        async function refreshData() {
            const refreshBtn = document.getElementById('refreshBtn');
            const errorMessage = document.getElementById('errorMessage');
            
            // Show loading state
            refreshBtn.classList.add('loading');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Loading...';
            
            // Add loading class to stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.add('loading');
            });

            errorMessage.style.display = 'none';
            try {
                const response = await fetch('api/dashboard-data.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Unknown error occurred');
                }

                updateStats(data.data.stats);
                updateCharts(data.data.charts);
                updateActivities(data.data.activities);
                updateProjects(data.data.projects);
                updateLastUpdated();

            } catch (error) {
                console.error('Error fetching data:', error);
                showError('Failed to fetch live data: ' + error.message);
            } finally {
                // Remove loading state
                refreshBtn.classList.remove('loading');
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.remove('loading');
                });
            }
        }

        function updateStats(stats) {
            // Active Incidents
            document.getElementById('activeIncidentsValue').textContent = stats.activeIncidents.value;
            updateStatChange('activeIncidents', stats.activeIncidents.change, stats.activeIncidents.changeType, 'from last week');
            
            // Total Reports
            document.getElementById('totalReportsValue').textContent = stats.totalReports.value;
            updateStatChange('totalReports', stats.totalReports.change, stats.totalReports.changeType, 'this month');
            
            // Response Time
            document.getElementById('responseTimeValue').textContent = stats.responseTime.value;
            updateStatChange('responseTime', stats.responseTime.change, stats.responseTime.changeType, 'avg response');
            
            // System Status
            document.getElementById('systemStatusValue').textContent = stats.systemStatus.value;
            updateStatChange('systemStatus', stats.systemStatus.change, stats.systemStatus.changeType, 'infrastructure');

            // Update progress bars with animation
            setTimeout(() => {
                document.getElementById('activeIncidentsProgress').style.width = Math.min(100, Math.abs(stats.activeIncidents.change) * 2) + '%';
                document.getElementById('totalReportsProgress').style.width = Math.min(100, Math.abs(stats.totalReports.change) * 2) + '%';
                document.getElementById('responseTimeProgress').style.width = '85%';
                document.getElementById('systemStatusProgress').style.width = '90%';
            }, 100);
        }

        function updateStatChange(statId, change, changeType, suffix) {
            const changeElement = document.getElementById(statId + 'Change');
            const icon = changeType === 'positive' ? 'fa-arrow-up' : 'fa-arrow-down';
            const sign = change >= 0 ? '+' : '';
            
            changeElement.className = `stat-change ${changeType}`;
            changeElement.innerHTML = `<i class="fas ${icon}"></i> ${sign}${change}% ${suffix}`;
        }

        function updateCharts(chartData) {
            updateSeverityChart(chartData.severity);
            updateTrendChart(chartData.trend);
        }

        function updateSeverityChart(data) {
            const ctx = document.getElementById('severityChart').getContext('2d');
            
            if (severityChart) {
                severityChart.destroy();
            }

            severityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels.length > 0 ? data.labels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: data.datasets.map(dataset => ({
                        ...dataset,
                        borderRadius: 8,
                        borderSkipped: false,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateTrendChart(data) {
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            
            if (trendChart) {
                trendChart.destroy();
            }

            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: data.labels.length > 0 ? data.labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Traffic Reports',
                        data: data.data.length > 0 ? data.data : [1200, 1350, 1100, 1500, 1300, 1450],
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgb(99, 102, 241)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateActivities(activities) {
            const activityFeed = document.getElementById('activityFeed');
            
            if (activities.length === 0) {
                activityFeed.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-info-circle"></i>
                        No recent activities
                    </div>
                `;
                return;
            }

            const activitiesHtml = activities.map((activity, index) => {
                const iconColor = getActivityIconColor(activity.type);
                const icon = getActivityIcon(activity.type);
                
                return `
                    <div class="activity-item" style="animation-delay: ${index * 0.1}s;">
                        <div class="activity-icon" style="background: ${iconColor};">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">${activity.title}</div>
                            <div class="activity-time">${activity.time}</div>
                        </div>
                    </div>
                `;
            }).join('');

            activityFeed.innerHTML = activitiesHtml;
        }

        function updateProjects(projects) {
            const projectsList = document.getElementById('projectsList');
            
            if (projects.length === 0) {
                projectsList.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-project-diagram"></i>
                        No active projects
                    </div>
                `;
                return;
            }

            const projectsHtml = projects.map(project => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-weight: 600; color: #1e293b;">${project.name}</div>
                        <div style="font-size: 0.85rem; color: #64748b;">Progress: ${project.progress}%</div>
                    </div>
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: conic-gradient(#6366f1 ${project.progress}%, #e2e8f0 ${project.progress}%); display: flex; align-items: center; justify-content: center; font-weight: 600; color: #1e293b;">
                        ${project.progress}%
                    </div>
                </div>
            `).join('');

            projectsList.innerHTML = projectsHtml;
        }

        function getActivityIconColor(type) {
            const colors = {
                'high': '#ef4444',
                'medium': '#f59e0b',
                'low': '#10b981',
                'info': '#3b82f6',
                'warning': '#f59e0b',
                'success': '#10b981',
                'error': '#ef4444'
            };
            return colors[type] || '#64748b';
        }

        function getActivityIcon(type) {
            const icons = {
                'high': 'fa-exclamation',
                'medium': 'fa-warning',
                'low': 'fa-info',
                'info': 'fa-info',
                'warning': 'fa-warning',
                'success': 'fa-check',
                'error': 'fa-exclamation'
            };
            return icons[type] || 'fa-circle';
        }

        function updateLastUpdated() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('lastUpdated').textContent = `Last updated: ${timeString}`;
        }

        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            errorText.textContent = message;
            errorMessage.style.display = 'block';
            
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        function toggleAutoRefresh() {
            const toggle = document.getElementById('autoRefreshToggle');
            isAutoRefreshEnabled = !isAutoRefreshEnabled;
            
            if (isAutoRefreshEnabled) {
                toggle.classList.add('active');
                startAutoRefresh();
            } else {
                toggle.classList.remove('active');
                stopAutoRefresh();
            }
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            
            if (isAutoRefreshEnabled) {
                autoRefreshInterval = setInterval(refreshData, 30000); // Refresh every 30 seconds
            }
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        // Page visibility API to pause/resume auto-refresh when tab is not visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else if (isAutoRefreshEnabled) {
                startAutoRefresh();
                refreshData(); // Refresh immediately when tab becomes visible
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>

</body>
</html>