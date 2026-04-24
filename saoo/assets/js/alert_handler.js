/**
 * Alert Handler - Displays alerts in browser
 * Handles browser notifications, audio alerts, and dashboard banners
 */

class AlertHandler {
    constructor() {
        this.check_interval = 5000; // Check every 5 seconds
        this.audio_context = null;
        this.init();
    }

    init() {
        console.log('🔔 Alert Handler Initialized');
        
        // Request notification permission
        this.request_notification_permission();
        
        // Start checking for alerts
        this.start_monitoring();
        
        // Register Service Worker for notifications
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw_alerts.js')
                .then(() => console.log('✓ Service Worker registered for alerts'))
                .catch(err => console.log('Service Worker registration failed:', err));
        }
    }

    /**
     * Request browser notification permission
     */
    request_notification_permission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    /**
     * Start monitoring for alerts
     */
    start_monitoring() {
        setInterval(() => {
            this.check_for_alerts();
        }, this.check_interval);
    }

    /**
     * Check for pending alerts via AJAX
     */
    check_for_alerts() {
        fetch('/get_alerts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Process each alert type
                    if (data.browser_notification) {
                        this.show_browser_notification(data.browser_notification);
                    }
                    if (data.audio_alert) {
                        this.play_audio_alert(data.audio_alert);
                    }
                    if (data.dashboard_alert) {
                        this.show_dashboard_alert(data.dashboard_alert);
                    }
                }
            })
            .catch(err => console.error('Alert check error:', err));
    }

    /**
     * Show browser notification
     */
    show_browser_notification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notif = new Notification(notification.title, {
                body: notification.body,
                icon: notification.icon || '/assets/images/alert-icon.png',
                tag: notification.tag,
                requireInteraction: true
            });

            // Click handler
            notif.onclick = () => {
                window.focus();
                notif.close();
            };

            // Auto-close after 10 seconds
            setTimeout(() => notif.close(), 10000);

            console.log('🔔 Notification sent:', notification.title);
        }
    }

    /**
     * Play audio alert
     */
    play_audio_alert(alert_config) {
        if (!alert_config) return;

        const volume = alert_config.volume || 0.8;
        const duration = (alert_config.duration || 5) * 1000;
        const repeat = alert_config.repeat || 2;

        // Create audio context
        if (!this.audio_context) {
            this.audio_context = new (window.AudioContext || window.webkitAudioContext)();
        }

        const ctx = this.audio_context;
        
        // Play alert tone multiple times
        for (let i = 0; i < repeat; i++) {
            const start_time = ctx.currentTime + (i * 1);
            this.play_alert_tone(ctx, start_time, volume);
        }

        console.log('🔊 Audio alert played');
    }

    /**
     * Generate and play alert tone
     */
    play_alert_tone(ctx, start_time, volume) {
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(ctx.destination);

        // Alert tone: alternating frequency
        osc.frequency.setValueAtTime(800, now);
        osc.frequency.setValueAtTime(1000, now + 0.1);
        osc.frequency.setValueAtTime(800, now + 0.2);

        gain.gain.setValueAtTime(0, now);
        gain.gain.linearRampToValueAtTime(volume, now + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.4);

        osc.start(now);
        osc.stop(now + 0.5);
    }

    /**
     * Show dashboard alert banner
     */
    show_dashboard_alert(alert) {
        if (!alert) return;

        // Create alert container
        const alert_div = document.createElement('div');
        alert_div.id = 'dashboard-alert-' + Date.now();
        alert_div.className = 'alert-banner alert-' + (alert.type || 'warning');
        alert_div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <strong>⏰ Alert:</strong> ${alert.message}
                </div>
                ${alert.dismissible ? `<button onclick="document.getElementById('${alert_div.id}').remove();" 
                    style="background: none; border: none; cursor: pointer; font-size: 20px; color: inherit;">
                    ×
                </button>` : ''}
            </div>
        `;

        // Add styling
        alert_div.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff9800;
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease;
            font-family: 'Inter', sans-serif;
        `;

        document.body.appendChild(alert_div);

        // Auto-remove after 15 seconds
        setTimeout(() => {
            if (alert_div.parentNode) {
                alert_div.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert_div.remove(), 300);
            }
        }, 15000);

        console.log('📊 Dashboard alert shown');
    }

    /**
     * Snooze all alerts for X minutes
     */
    snooze_alerts(minutes = 5) {
        fetch('/snooze_alerts.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ duration_minutes: minutes })
        })
        .then(response => response.json())
        .then(data => {
            console.log('✓ Alerts snoozed for', minutes, 'minutes');
            this.show_dashboard_alert({
                message: `All alerts snoozed for ${minutes} minutes`,
                type: 'success'
            });
        });
    }

    /**
     * Acknowledge alert
     */
    acknowledge_alert(alert_id) {
        fetch('/acknowledge_alert.php?id=' + alert_id)
            .then(response => response.json())
            .then(data => {
                console.log('✓ Alert acknowledged');
            });
    }
}

// CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .alert-banner {
        font-weight: 500;
        line-height: 1.5;
    }

    .alert-warning {
        background-color: #ff9800;
    }

    .alert-success {
        background-color: #4caf50;
    }

    .alert-error {
        background-color: #f44336;
    }

    .alert-info {
        background-color: #2196f3;
    }
`;
document.head.appendChild(style);

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.alertHandler = new AlertHandler();
});
