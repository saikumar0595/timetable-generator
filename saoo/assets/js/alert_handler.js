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
        
        // Resume audio context on first interaction
        const resumeAudio = () => {
            if (this.audio_context && this.audio_context.state === 'suspended') {
                this.audio_context.resume();
                console.log('🔊 Audio Context Resumed');
            } else if (!this.audio_context) {
                this.audio_context = new (window.AudioContext || window.webkitAudioContext)();
                console.log('🔊 Audio Context Created');
            }
            document.removeEventListener('click', resumeAudio);
            document.removeEventListener('keydown', resumeAudio);
        };
        document.addEventListener('click', resumeAudio);
        document.addEventListener('keydown', resumeAudio);
        
        // Start checking for alerts
        this.start_monitoring();

        // Start countdown timer sync
        this.start_countdown_sync();
        
        // Register Service Worker for notifications
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw_alerts.js')
                .then(() => console.log('✓ Service Worker registered for alerts'))
                .catch(err => console.warn('Service Worker registration failed:', err));
        }
    }

    /**
     * Sync countdown timer with server
     */
    start_countdown_sync() {
        this.sync_timer();
        setInterval(() => this.sync_timer(), 30000); // Sync every 30s
        
        // Local countdown tick (every 1s)
        setInterval(() => {
            if (this.next_event_diff > 0) {
                this.next_event_diff--;
                this.update_countdown_display();
            } else if (this.next_event_diff === 0) {
                // When countdown hits zero, sync immediately
                this.next_event_diff = -1;
                this.sync_timer();
            }
        }, 1000);
    }

    sync_timer() {
        fetch('get_timetable_status.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.next_event) {
                    this.next_event = data.next_event;
                    this.next_event_diff = data.next_event.diff;
                    document.getElementById('next-class-timer')?.classList.remove('hidden');
                    this.update_countdown_display();
                } else {
                    document.getElementById('next-class-timer')?.classList.add('hidden');
                }
            })
            .catch(err => console.warn('Timer sync failed:', err));
    }

    update_countdown_display() {
        const timerEl = document.getElementById('timer-countdown');
        const labelEl = document.getElementById('timer-label');
        if (!timerEl || !this.next_event) return;

        const h = Math.floor(this.next_event_diff / 3600);
        const m = Math.floor((this.next_event_diff % 3600) / 60);
        const s = this.next_event_diff % 60;

        labelEl.innerText = this.next_event.type === 'STARTING' ? 'Starts In:' : 'Ends In:';
        timerEl.innerText = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        
        // Visual warning when less than 5 minutes
        if (this.next_event_diff <= 300) {
            timerEl.classList.add('text-red-600');
            timerEl.classList.remove('text-indigo-600', 'text-slate-600');
        } else {
            timerEl.classList.remove('text-red-600');
            timerEl.classList.add('text-indigo-600');
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
        fetch('get_alerts.php')
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
                icon: notification.icon || 'assets_login/img/authentication.svg',
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
     * Play audio alert and voice notification
     */
    play_audio_alert(alert_config) {
        if (!alert_config) return;

        const volume = alert_config.volume || 0.8;
        const repeat = alert_config.repeat || 2;
        const message = alert_config.message || "Attention";

        // 1. Play Siren Tone
        if (!this.audio_context) {
            this.audio_context = new (window.AudioContext || window.webkitAudioContext)();
        }
        const ctx = this.audio_context;
        for (let i = 0; i < repeat; i++) {
            const start_time = ctx.currentTime + (i * 1);
            this.play_alert_tone(ctx, start_time, volume);
        }

        // 2. Voice Synthesis Notification
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(message);
            utterance.rate = 0.9;
            utterance.pitch = 1.0;
            utterance.volume = volume;
            window.speechSynthesis.speak(utterance);
        }

        console.log('🔊 Audio & Voice alert played');
    }

    /**
     * Generate and play alert tone (Siren Style)
     */
    play_alert_tone(ctx, start_time, volume) {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(ctx.destination);

        // Siren Effect: Slide frequency from low to high
        osc.frequency.setValueAtTime(440, start_time);
        osc.frequency.exponentialRampToValueAtTime(880, start_time + 0.3);
        osc.frequency.exponentialRampToValueAtTime(440, start_time + 0.6);

        gain.gain.setValueAtTime(0, start_time);
        gain.gain.linearRampToValueAtTime(volume, start_time + 0.1);
        gain.gain.linearRampToValueAtTime(0, start_time + 0.6);

        osc.start(start_time);
        osc.stop(start_time + 0.6);
    }

    /**
     * Show SMS Sent Toast
     */
    show_sms_toast(message) {
        const toast = document.createElement('div');
        toast.className = 'sms-toast';
        toast.innerHTML = `<i class="fas fa-sms"></i> <strong>SMS ALERT SENT:</strong> ${message}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e3a8a;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 10000;
            font-size: 14px;
            animation: fadeInUp 0.5s ease;
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
}

// Add fadeInUp animation
const styleSheet = document.createElement("style")
styleSheet.textContent = `
    @keyframes fadeInUp {
        from { opacity: 0; transform: translate(-50%, 20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
`;
document.head.appendChild(styleSheet);

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
        fetch('snooze_alerts.php', {
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
        fetch('acknowledge_alert.php?id=' + alert_id)
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
