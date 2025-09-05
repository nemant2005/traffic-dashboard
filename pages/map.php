<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mumbai Traffic Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #2d3748;
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            color: #718096;
            font-size: 1.1rem;
        }

        .control-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .control-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .control-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }

        select, input, button {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #48bb78, #38a169);
        }

        .btn-danger {
            background: linear-gradient(45deg, #f56565, #e53e3e);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ed8936, #dd6b20);
        }

        .map-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #map {
            height: 600px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #718096;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .legend {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .legend h3 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
        }

        .legend-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .custom-popup {
            min-width: 250px;
            border-radius: 15px;
        }

        .popup-header {
            font-weight: 700;
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }

        .popup-detail {
            margin: 8px 0;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .popup-detail strong {
            color: #4a5568;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }

        .alert.show {
            transform: translateX(0);
        }

        .alert-success {
            background: linear-gradient(45deg, #48bb78, #38a169);
        }

        .alert-error {
            background: linear-gradient(45deg, #f56565, #e53e3e);
        }

        @media (max-width: 768px) {
            .control-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-traffic-light"></i> Mumbai Traffic Management System</h1>
            <p>Real-time traffic monitoring and incident management for Mumbai city</p>
        </div>

        <div class="control-panel">
            <div class="control-row">
                <div class="control-group">
                    <label for="incidentType">Incident Type:</label>
                    <select id="incidentType">
                        <option value="all">All Incidents</option>
                        <option value="accident">Accident</option>
                        <option value="construction">Construction</option>
                        <option value="congestion">Heavy Traffic</option>
                        <option value="weather">Weather Related</option>
                        <option value="event">Special Event</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="severityLevel">Severity Level:</label>
                    <select id="severityLevel">
                        <option value="all">All Levels</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="timeRange">Time Range:</label>
                    <select id="timeRange">
                        <option value="live">Live Data</option>
                        <option value="1hour">Last 1 Hour</option>
                        <option value="3hours">Last 3 Hours</option>
                        <option value="24hours">Last 24 Hours</option>
                    </select>
                </div>
            </div>

            <div class="control-row">
                <button class="btn" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
                <button class="btn btn-secondary" onclick="addIncident()">
                    <i class="fas fa-plus"></i> Report Incident
                </button>
                <button class="btn btn-warning" onclick="showTrafficFlow()">
                    <i class="fas fa-route"></i> Traffic Flow
                </button>
                <button class="btn btn-danger" onclick="emergencyMode()">
                    <i class="fas fa-exclamation-triangle"></i> Emergency Mode
                </button>
                <button class="btn" onclick="generateReport()">
                    <i class="fas fa-chart-bar"></i> Generate Report
                </button>
            </div>
        </div>

        <div class="map-container">
            <div id="map"></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-number" id="totalIncidents">0</div>
                <div class="stat-label">Active Incidents</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-number" id="criticalIncidents">0</div>
                <div class="stat-label">Critical Incidents</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number" id="avgDelay">0</div>
                <div class="stat-label">Avg Delay (min)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-road"></i></div>
                <div class="stat-number" id="affectedRoads">0</div>
                <div class="stat-label">Affected Roads</div>
            </div>
        </div>

        <div class="legend">
            <h3><i class="fas fa-map-signs"></i> Incident Legend</h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <div class="legend-icon" style="background: #dc2626;"></div>
                    <span><strong>Critical:</strong> Major accidents, road closures</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background: #ea580c;"></div>
                    <span><strong>High:</strong> Serious congestion, minor accidents</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background: #ca8a04;"></div>
                    <span><strong>Medium:</strong> Moderate traffic, construction</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background: #16a34a;"></div>
                    <span><strong>Low:</strong> Minor delays, routine events</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        // Global variables
        let map;
        let incidentMarkers = [];
        let trafficIncidents = [];

        // Mumbai traffic incident data
        const mumbaiIncidents = [
            {
                id: 1,
                lat: 19.0760,
                lng: 72.8777,
                location: "Chhatrapati Shivaji Maharaj Terminus",
                type: "congestion",
                severity: "high",
                delay: 25,
                description: "Heavy traffic due to rush hour",
                timestamp: new Date(),
                affectedLanes: 3,
                estimatedClearTime: "45 minutes"
            },
            {
                id: 2,
                lat: 19.0176,
                lng: 72.8562,
                location: "Bandra-Worli Sea Link",
                type: "accident",
                severity: "critical",
                delay: 40,
                description: "Multi-vehicle collision, emergency services on site",
                timestamp: new Date(Date.now() - 30 * 60000),
                affectedLanes: 2,
                estimatedClearTime: "2 hours"
            },
            {
                id: 3,
                lat: 19.1136,
                lng: 72.8697,
                location: "Andheri East",
                type: "construction",
                severity: "medium",
                delay: 15,
                description: "Road maintenance work in progress",
                timestamp: new Date(Date.now() - 2 * 60 * 60000),
                affectedLanes: 1,
                estimatedClearTime: "3 days"
            },
            {
                id: 4,
                lat: 19.0728,
                lng: 72.8826,
                location: "Marine Drive",
                type: "event",
                severity: "low",
                delay: 8,
                description: "Cultural event causing minor delays",
                timestamp: new Date(Date.now() - 60 * 60000),
                affectedLanes: 1,
                estimatedClearTime: "2 hours"
            },
            {
                id: 5,
                lat: 19.0330,
                lng: 72.8570,
                location: "Bandra Station",
                type: "congestion",
                severity: "high",
                delay: 30,
                description: "Train station rush causing road congestion",
                timestamp: new Date(Date.now() - 15 * 60000),
                affectedLanes: 4,
                estimatedClearTime: "1 hour"
            }
        ];

        // Severity colors and icons
        const severityStyles = {
            critical: { color: '#dc2626', icon: 'fa-exclamation-circle', size: 20 },
            high: { color: '#ea580c', icon: 'fa-exclamation-triangle', size: 16 },
            medium: { color: '#ca8a04', icon: 'fa-info-circle', size: 14 },
            low: { color: '#16a34a', icon: 'fa-check-circle', size: 12 }
        };

        // Type icons
        const typeIcons = {
            accident: 'fa-car-crash',
            construction: 'fa-tools',
            congestion: 'fa-traffic-light',
            weather: 'fa-cloud-rain',
            event: 'fa-calendar-alt'
        };

        // Initialize map on DOM content loaded
        document.addEventListener("DOMContentLoaded", function () {
            initializeMap();
            loadTrafficData();
            setupEventListeners();
            startAutoRefresh();
        });

        function initializeMap() {
            map = L.map('map').setView([19.0760, 72.8777], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add map click event for adding incidents
            map.on('click', onMapClick);
        }

        function loadTrafficData() {
            trafficIncidents = [...mumbaiIncidents];
            displayIncidents();
            updateStatistics();
        }

        function displayIncidents() {
            // Clear existing markers
            incidentMarkers.forEach(marker => map.removeLayer(marker));
            incidentMarkers = [];

            // Get filter values
            const typeFilter = document.getElementById('incidentType').value;
            const severityFilter = document.getElementById('severityLevel').value;

            // Filter incidents
            let filteredIncidents = trafficIncidents.filter(incident => {
                const typeMatch = typeFilter === 'all' || incident.type === typeFilter;
                const severityMatch = severityFilter === 'all' || incident.severity === severityFilter;
                return typeMatch && severityMatch;
            });

            // Create markers for filtered incidents
            filteredIncidents.forEach(incident => {
                const style = severityStyles[incident.severity];
                
                // Create custom icon
                const iconHtml = `
                    <div style="
                        background: ${style.color};
                        width: ${style.size + 10}px;
                        height: ${style.size + 10}px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border: 3px solid white;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                        color: white;
                        font-size: ${style.size - 4}px;
                    ">
                        <i class="fas ${typeIcons[incident.type]}"></i>
                    </div>
                `;

                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-incident-marker',
                    iconSize: [style.size + 10, style.size + 10],
                    iconAnchor: [(style.size + 10) / 2, (style.size + 10) / 2]
                });

                const marker = L.marker([incident.lat, incident.lng], { icon: customIcon });

                // Create detailed popup
                const popupContent = createPopupContent(incident);
                marker.bindPopup(popupContent);

                marker.addTo(map);
                incidentMarkers.push(marker);
            });
        }

        function createPopupContent(incident) {
            const timeAgo = getTimeAgo(incident.timestamp);
            return `
                <div class="custom-popup">
                    <div class="popup-header">
                        <i class="fas ${typeIcons[incident.type]}"></i>
                        ${incident.location}
                    </div>
                    <div class="popup-detail">
                        <strong>Type:</strong>
                        <span>${incident.type.charAt(0).toUpperCase() + incident.type.slice(1)}</span>
                    </div>
                    <div class="popup-detail">
                        <strong>Severity:</strong>
                        <span style="color: ${severityStyles[incident.severity].color}; font-weight: bold;">
                            ${incident.severity.toUpperCase()}
                        </span>
                    </div>
                    <div class="popup-detail">
                        <strong>Delay:</strong>
                        <span>${incident.delay} minutes</span>
                    </div>
                    <div class="popup-detail">
                        <strong>Affected Lanes:</strong>
                        <span>${incident.affectedLanes}</span>
                    </div>
                    <div class="popup-detail">
                        <strong>Reported:</strong>
                        <span>${timeAgo}</span>
                    </div>
                    <div class="popup-detail">
                        <strong>Est. Clear Time:</strong>
                        <span>${incident.estimatedClearTime}</span>
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <strong>Description:</strong><br>
                        <span>${incident.description}</span>
                    </div>
                </div>
            `;
        }

        function updateStatistics() {
            const total = trafficIncidents.length;
            const critical = trafficIncidents.filter(i => i.severity === 'critical').length;
            const avgDelay = trafficIncidents.reduce((sum, i) => sum + i.delay, 0) / total;
            const affectedRoads = new Set(trafficIncidents.map(i => i.location)).size;

            document.getElementById('totalIncidents').textContent = total;
            document.getElementById('criticalIncidents').textContent = critical;
            document.getElementById('avgDelay').textContent = Math.round(avgDelay);
            document.getElementById('affectedRoads').textContent = affectedRoads;
        }

        function setupEventListeners() {
            document.getElementById('incidentType').addEventListener('change', displayIncidents);
            document.getElementById('severityLevel').addEventListener('change', displayIncidents);
            document.getElementById('timeRange').addEventListener('change', handleTimeRangeChange);
        }

        function refreshData() {
            showAlert('Refreshing traffic data...', 'success');
            
            // Simulate data refresh with random updates
            trafficIncidents.forEach(incident => {
                incident.delay = Math.max(1, incident.delay + Math.floor(Math.random() * 10) - 5);
                incident.timestamp = new Date(incident.timestamp.getTime() + Math.random() * 60000);
            });

            displayIncidents();
            updateStatistics();
            
            setTimeout(() => {
                showAlert('Traffic data updated successfully!', 'success');
            }, 1500);
        }

        function addIncident() {
            const center = map.getCenter();
            const types = ['accident', 'construction', 'congestion', 'weather', 'event'];
            const severities = ['low', 'medium', 'high', 'critical'];
            
            const newIncident = {
                id: trafficIncidents.length + 1,
                lat: center.lat + (Math.random() - 0.5) * 0.02,
                lng: center.lng + (Math.random() - 0.5) * 0.02,
                location: `Reported Location ${trafficIncidents.length + 1}`,
                type: types[Math.floor(Math.random() * types.length)],
                severity: severities[Math.floor(Math.random() * severities.length)],
                delay: Math.floor(Math.random() * 60) + 5,
                description: "User reported incident - requires verification",
                timestamp: new Date(),
                affectedLanes: Math.floor(Math.random() * 4) + 1,
                estimatedClearTime: `${Math.floor(Math.random() * 3) + 1} hours`
            };

            trafficIncidents.push(newIncident);
            displayIncidents();
            updateStatistics();
            showAlert('New incident reported successfully!', 'success');
        }

        function showTrafficFlow() {
            showAlert('Traffic flow analysis activated!', 'success');
            // Add traffic flow visualization logic here
        }

        function emergencyMode() {
            showAlert('Emergency mode activated! Prioritizing critical incidents.', 'error');
            document.getElementById('severityLevel').value = 'critical';
            displayIncidents();
        }

        function generateReport() {
            const report = {
                timestamp: new Date().toISOString(),
                totalIncidents: trafficIncidents.length,
                severityBreakdown: {
                    critical: trafficIncidents.filter(i => i.severity === 'critical').length,
                    high: trafficIncidents.filter(i => i.severity === 'high').length,
                    medium: trafficIncidents.filter(i => i.severity === 'medium').length,
                    low: trafficIncidents.filter(i => i.severity === 'low').length
                },
                typeBreakdown: {
                    accident: trafficIncidents.filter(i => i.type === 'accident').length,
                    construction: trafficIncidents.filter(i => i.type === 'construction').length,
                    congestion: trafficIncidents.filter(i => i.type === 'congestion').length,
                    weather: trafficIncidents.filter(i => i.type === 'weather').length,
                    event: trafficIncidents.filter(i => i.type === 'event').length
                },
                averageDelay: trafficIncidents.reduce((sum, i) => sum + i.delay, 0) / trafficIncidents.length,
                topIncidents: trafficIncidents
                    .sort((a, b) => b.delay - a.delay)
                    .slice(0, 5)
                    .map(i => ({ location: i.location, delay: i.delay, severity: i.severity, type: i.type }))
            };

            console.log('Mumbai Traffic Report:', report);
            showAlert('Detailed report generated! Check console for full data.', 'success');
        }

        function onMapClick(e) {
            if (confirm('Report a traffic incident at this location?')) {
                const location = prompt('Enter location name:', 'Custom Location');
                const type = prompt('Enter incident type (accident/construction/congestion/weather/event):', 'congestion');
                const severity = prompt('Enter severity (low/medium/high/critical):', 'medium');
                const description = prompt('Enter description:', 'User reported incident');

                if (location && type && severity && description) {
                    const newIncident = {
                        id: trafficIncidents.length + 1,
                        lat: e.latlng.lat,
                        lng: e.latlng.lng,
                        location: location,
                        type: type,
                        severity: severity,
                        delay: Math.floor(Math.random() * 30) + 5,
                        description: description,
                        timestamp: new Date(),
                        affectedLanes: Math.floor(Math.random() * 3) + 1,
                        estimatedClearTime: `${Math.floor(Math.random() * 2) + 1} hours`
                    };

                    trafficIncidents.push(newIncident);
                    displayIncidents();
                    updateStatistics();
                    showAlert('Incident reported successfully!', 'success');
                }
            }
        }

        function handleTimeRangeChange() {
            const timeRange = document.getElementById('timeRange').value;
            // Filter incidents based on time range
            showAlert(`Filtering incidents for: ${timeRange}`, 'success');
        }

        function startAutoRefresh() {
            setInterval(() => {
                refreshData();
            }, 300000); // Refresh every 5 minutes
        }

        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            document.body.appendChild(alert);

            setTimeout(() => alert.classList.add('show'), 100);
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => document.body.removeChild(alert), 300);
            }, 3000);
        }

        function getTimeAgo(timestamp) {
            const now = new Date();
            const diff = now - timestamp;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(minutes / 60);

            if (hours > 0) {
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else if (minutes > 0) {
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else {
                return 'Just now';
            }
        }
    </script>
</body>
</html>