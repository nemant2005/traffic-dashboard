/**
 * Traffic Management Alert System
 * Comprehensive alert system for traffic monitoring and incident management
 * Author: Traffic Management Team
 * Version: 2.0
 */

class TrafficAlertSystem {
    constructor() {
        this.alerts = [];
        this.alertQueue = [];
        this.maxAlerts = 5;
        this.alertContainer = null;
        this.soundEnabled = true;
        this.init();
    }

    // Initialize alert system
    init() {
        this.createAlertContainer();
        this.createSoundElements();
        this.setupKeyboardShortcuts();
    }

    // Create main alert container
    createAlertContainer() {
        if (!document.getElementById('traffic-alert-container')) {
            const container = document.createElement('div');
            container.id = 'traffic-alert-container';
            container.className = 'traffic-alert-container';
            document.body.appendChild(container);
            this.alertContainer = container;
        }
    }

    // Create sound elements for different alert types
    createSoundElements() {
        const sounds = {
            emergency: 'data:audio/mpeg;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhbcXVlLmNvbQBURU5DAAAAHQAAAUBN',
            critical: 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmccBSuGuv',
            warning: 'data:audio/wav;base64,UklGRlQKAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQQJAAB/hYiIqK6krJ7A/AYfGDtdLCcMCQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8NDQ8ND'
        };

        Object.keys(sounds).forEach(type => {
            const audio = document.createElement('audio');
            audio.id = `traffic-sound-${type}`;
            audio.src = sounds[type];
            audio.preload = 'auto';
            document.body.appendChild(audio);
        });
    }

    // Setup keyboard shortcuts for alerts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        this.emergency('Emergency alert triggered via keyboard');
                        break;
                    case '2':
                        e.preventDefault();
                        this.critical('Critical alert triggered via keyboard');
                        break;
                    case '3':
                        e.preventDefault();
                        this.warning('Warning alert triggered via keyboard');
                        break;
                }
            }
        });
    }

    // Main alert creation function
    createAlert(type, title, message, options = {}) {
        const alertId = 'alert-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        
        const alertConfig = {
            id: alertId,
            type: type,
            title: title,
            message: message,
            timestamp: new Date(),
            duration: options.duration || this.getDefaultDuration(type),
            persistent: options.persistent || false,
            location: options.location || null,
            severity: options.severity || type,
            actionRequired: options.actionRequired || false,
            playSound: options.playSound !== false,
            showMap: options.showMap || false,
            coordinates: options.coordinates || null,
            affectedRoutes: options.affectedRoutes || [],
            estimatedDelay: options.estimatedDelay || null,
            alternativeRoutes: options.alternativeRoutes || [],
            emergencyContacts: options.emergencyContacts || [],
            onAction: options.onAction || null,
            onDismiss: options.onDismiss || null
        };

        // Add to alerts array
        this.alerts.push(alertConfig);

        // Create and display alert element
        this.displayAlert(alertConfig);

        // Play sound if enabled
        if (this.soundEnabled && alertConfig.playSound) {
            this.playAlertSound(type);
        }

        // Auto-remove non-persistent alerts
        if (!alertConfig.persistent) {
            setTimeout(() => {
                this.removeAlert(alertId);
            }, alertConfig.duration);
        }

        // Log alert for analytics
        this.logAlert(alertConfig);

        return alertId;
    }

    // Display alert in UI
    displayAlert(config) {
        // Check if maximum alerts reached
        if (this.alertContainer.children.length >= this.maxAlerts) {
            this.removeOldestAlert();
        }

        const alertElement = document.createElement('div');
        alertElement.id = config.id;
        alertElement.className = `traffic-alert traffic-alert-${config.type}`;
        
        alertElement.innerHTML = `
            <div class="alert-content">
                <div class="alert-header">
                    <div class="alert-icon">
                        <i class="fas ${this.getAlertIcon(config.type)}"></i>
                    </div>
                    <div class="alert-title-section">
                        <h4 class="alert-title">${config.title}</h4>
                        <span class="alert-timestamp">${this.formatTime(config.timestamp)}</span>
                    </div>
                    <div class="alert-actions">
                        ${config.showMap ? '<button class="alert-btn alert-map-btn" onclick="trafficAlerts.showOnMap(\'' + config.id + '\')"><i class="fas fa-map"></i></button>' : ''}
                        ${config.actionRequired ? '<button class="alert-btn alert-action-btn" onclick="trafficAlerts.takeAction(\'' + config.id + '\')"><i class="fas fa-exclamation-triangle"></i></button>' : ''}
                        <button class="alert-btn alert-close-btn" onclick="trafficAlerts.removeAlert(\'' + config.id + '\')"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="alert-body">
                    <p class="alert-message">${config.message}</p>
                    ${config.location ? `<div class="alert-location"><i class="fas fa-map-marker-alt"></i> ${config.location}</div>` : ''}
                    ${config.estimatedDelay ? `<div class="alert-delay"><i class="fas fa-clock"></i> Estimated Delay: ${config.estimatedDelay} minutes</div>` : ''}
                    ${config.affectedRoutes.length > 0 ? `<div class="alert-routes"><i class="fas fa-route"></i> Affected Routes: ${config.affectedRoutes.join(', ')}</div>` : ''}
                    ${config.alternativeRoutes.length > 0 ? `<div class="alert-alternatives"><i class="fas fa-directions"></i> Alternative Routes: ${config.alternativeRoutes.join(', ')}</div>` : ''}
                </div>
                ${config.emergencyContacts.length > 0 ? this.createEmergencyContactsSection(config.emergencyContacts) : ''}
            </div>
            <div class="alert-progress-bar"></div>
        `;

        // Add animation
        alertElement.style.opacity = '0';
        alertElement.style.transform = 'translateX(100%)';
        
        this.alertContainer.appendChild(alertElement);

        // Trigger entrance animation
        requestAnimationFrame(() => {
            alertElement.style.opacity = '1';
            alertElement.style.transform = 'translateX(0)';
        });

        // Start progress bar animation if not persistent
        if (!config.persistent) {
            this.animateProgressBar(alertElement, config.duration);
        }
    }

    // Traffic-specific alert types
    emergency(message, options = {}) {
        return this.createAlert('emergency', '🚨 EMERGENCY ALERT', message, {
            duration: 0, // Persistent
            persistent: true,
            actionRequired: true,
            playSound: true,
            severity: 'critical',
            ...options
        });
    }

    accident(location, details, options = {}) {
        return this.createAlert('critical', '🚗 TRAFFIC ACCIDENT', `Accident reported at ${location}. ${details}`, {
            location: location,
            duration: 30000,
            showMap: true,
            actionRequired: true,
            estimatedDelay: options.estimatedDelay || 15,
            affectedRoutes: options.affectedRoutes || [],
            alternativeRoutes: options.alternativeRoutes || [],
            emergencyContacts: ['Traffic Police: 100', 'Ambulance: 108'],
            ...options
        });
    }

    roadClosure(location, reason, options = {}) {
        return this.createAlert('critical', '🚧 ROAD CLOSURE', `${location} is temporarily closed due to ${reason}`, {
            location: location,
            duration: 0,
            persistent: true,
            showMap: true,
            actionRequired: false,
            affectedRoutes: options.affectedRoutes || [],
            alternativeRoutes: options.alternativeRoutes || [],
            ...options
        });
    }

    heavyTraffic(location, cause, options = {}) {
        return this.createAlert('warning', '🚦 HEAVY TRAFFIC', `Heavy congestion reported at ${location} due to ${cause}`, {
            location: location,
            duration: 20000,
            showMap: true,
            estimatedDelay: options.estimatedDelay || 10,
            affectedRoutes: options.affectedRoutes || [],
            alternativeRoutes: options.alternativeRoutes || [],
            ...options
        });
    }

    weatherAlert(condition, affectedAreas, options = {}) {
        return this.createAlert('warning', '🌧️ WEATHER ALERT', `${condition} affecting traffic in ${affectedAreas.join(', ')}`, {
            duration: 25000,
            affectedRoutes: affectedAreas,
            estimatedDelay: options.estimatedDelay || 5,
            ...options
        });
    }

    construction(location, duration, options = {}) {
        return this.createAlert('info', '🔧 CONSTRUCTION WORK', `Construction work in progress at ${location}. Expected duration: ${duration}`, {
            location: location,
            duration: 15000,
            showMap: true,
            affectedRoutes: options.affectedRoutes || [],
            alternativeRoutes: options.alternativeRoutes || [],
            ...options
        });
    }

    speedLimit(location, newLimit, options = {}) {
        return this.createAlert('info', '⚡ SPEED LIMIT CHANGE', `Speed limit changed to ${newLimit} km/h at ${location}`, {
            location: location,
            duration: 10000,
            ...options
        });
    }

    routeUpdate(message, routes, options = {}) {
        return this.createAlert('success', '🗺️ ROUTE UPDATE', message, {
            duration: 12000,
            affectedRoutes: routes,
            showMap: true,
            ...options
        });
    }

    trafficCleared(location, options = {}) {
        return this.createAlert('success', '✅ TRAFFIC CLEARED', `Traffic congestion cleared at ${location}`, {
            location: location,
            duration: 8000,
            showMap: true,
            ...options
        });
    }

    // Utility functions
    critical(message, options = {}) {
        return this.createAlert('critical', '⚠️ CRITICAL ALERT', message, {
            duration: 25000,
            actionRequired: true,
            ...options
        });
    }

    warning(message, options = {}) {
        return this.createAlert('warning', '⚠️ WARNING', message, {
            duration: 15000,
            ...options
        });
    }

    info(message, options = {}) {
        return this.createAlert('info', 'ℹ️ INFORMATION', message, {
            duration: 10000,
            ...options
        });
    }

    success(message, options = {}) {
        return this.createAlert('success', '✅ SUCCESS', message, {
            duration: 8000,
            ...options
        });
    }

    // Helper functions
    getDefaultDuration(type) {
        const durations = {
            emergency: 0,      // Persistent
            critical: 30000,   // 30 seconds
            warning: 15000,    // 15 seconds
            info: 10000,       // 10 seconds
            success: 8000      // 8 seconds
        };
        return durations[type] || 10000;
    }

    getAlertIcon(type) {
        const icons = {
            emergency: 'fa-exclamation-triangle',
            critical: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
            success: 'fa-check-circle'
        };
        return icons[type] || 'fa-bell';
    }

    formatTime(timestamp) {
        return timestamp.toLocaleTimeString('en-US', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    createEmergencyContactsSection(contacts) {
        return `
            <div class="alert-emergency-contacts">
                <h5><i class="fas fa-phone"></i> Emergency Contacts:</h5>
                <div class="emergency-contacts-list">
                    ${contacts.map(contact => `<div class="emergency-contact">${contact}</div>`).join('')}
                </div>
            </div>
        `;
    }

    animateProgressBar(element, duration) {
        const progressBar = element.querySelector('.alert-progress-bar');
        if (progressBar && duration > 0) {
            progressBar.style.transition = `width ${duration}ms linear`;
            progressBar.style.width = '0%';
        }
    }

    // Alert management functions
    removeAlert(alertId) {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            // Exit animation
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                if (alertElement.parentNode) {
                    alertElement.parentNode.removeChild(alertElement);
                }
            }, 300);
        }

        // Remove from alerts array
        this.alerts = this.alerts.filter(alert => alert.id !== alertId);
    }

    removeOldestAlert() {
        const oldestAlert = this.alertContainer.firstChild;
        if (oldestAlert) {
            this.removeAlert(oldestAlert.id);
        }
    }

    clearAllAlerts() {
        this.alerts.forEach(alert => {
            this.removeAlert(alert.id);
        });
    }

    showOnMap(alertId) {
        const alert = this.alerts.find(a => a.id === alertId);
        if (alert && alert.coordinates && window.map) {
            window.map.setView([alert.coordinates.lat, alert.coordinates.lng], 15);
            this.info(`Showing ${alert.location} on map`);
        }
    }

    takeAction(alertId) {
        const alert = this.alerts.find(a => a.id === alertId);
        if (alert && alert.onAction) {
            alert.onAction(alert);
        } else {
            // Default action
            this.info('Action taken for alert: ' + alert.title);
        }
    }

    playAlertSound(type) {
        if (!this.soundEnabled) return;
        
        const soundElement = document.getElementById(`traffic-sound-${type}`);
        if (soundElement) {
            soundElement.currentTime = 0;
            soundElement.play().catch(e => console.log('Sound play failed:', e));
        }
    }

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        return this.soundEnabled;
    }

    logAlert(config) {
        console.log(`[Traffic Alert] ${config.type.toUpperCase()}: ${config.title} - ${config.message}`);
        
        // Send to analytics if available
        if (window.analytics) {
            window.analytics.track('traffic_alert', {
                type: config.type,
                location: config.location,
                severity: config.severity,
                timestamp: config.timestamp
            });
        }
    }

    // Bulk operations
    createBulkAlerts(alerts) {
        alerts.forEach(alert => {
            this.createAlert(alert.type, alert.title, alert.message, alert.options);
        });
    }

    getActiveAlerts() {
        return this.alerts;
    }

    getAlertsByType(type) {
        return this.alerts.filter(alert => alert.type === type);
    }

    getAlertsByLocation(location) {
        return this.alerts.filter(alert => alert.location === location);
    }
}

// CSS Styles for alerts
const alertStyles = `
    .traffic-alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        width: 400px;
        max-width: 90vw;
    }

    .traffic-alert {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        border-left: 4px solid #007bff;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }

    .traffic-alert-emergency { border-left-color: #dc3545; }
    .traffic-alert-critical { border-left-color: #fd7e14; }
    .traffic-alert-warning { border-left-color: #ffc107; }
    .traffic-alert-info { border-left-color: #17a2b8; }
    .traffic-alert-success { border-left-color: #28a745; }

    .alert-content {
        padding: 16px;
    }

    .alert-header {
        display: flex;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .alert-icon {
        margin-right: 12px;
        font-size: 20px;
        margin-top: 2px;
    }

    .traffic-alert-emergency .alert-icon { color: #dc3545; }
    .traffic-alert-critical .alert-icon { color: #fd7e14; }
    .traffic-alert-warning .alert-icon { color: #ffc107; }
    .traffic-alert-info .alert-icon { color: #17a2b8; }
    .traffic-alert-success .alert-icon { color: #28a745; }

    .alert-title-section {
        flex: 1;
    }

    .alert-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .alert-timestamp {
        font-size: 12px;
        color: #666;
    }

    .alert-actions {
        display: flex;
        gap: 5px;
    }

    .alert-btn {
        background: none;
        border: none;
        padding: 5px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .alert-btn:hover {
        background: rgba(0, 0, 0, 0.1);
    }

    .alert-message {
        margin: 0 0 8px 0;
        color: #555;
        line-height: 1.4;
    }

    .alert-location, .alert-delay, .alert-routes, .alert-alternatives {
        font-size: 13px;
        color: #666;
        margin: 4px 0;
    }

    .alert-emergency-contacts {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #eee;
    }

    .alert-emergency-contacts h5 {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #dc3545;
    }

    .emergency-contact {
        font-size: 12px;
        color: #666;
        margin: 2px 0;
    }

    .alert-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: rgba(0, 123, 255, 0.3);
        transition: width linear;
    }

    @media (max-width: 768px) {
        .traffic-alert-container {
            width: calc(100vw - 40px);
            right: 20px;
            left: 20px;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = alertStyles;
document.head.appendChild(styleSheet);

// Initialize global alert system
const trafficAlerts = new TrafficAlertSystem();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TrafficAlertSystem;
}