<?php
/**
 * Alert Dispatcher - Central hub for all alert types
 * Routes alerts to SMS, Browser Notifications, Audio, and Dashboard
 */

session_start();
header('Content-Type: application/json');

class AlertDispatcher {
    private $alert_type = [];
    private $pending_alerts = [];
    
    public function __construct() {
        // Load preferences from session or database
        $this->load_preferences();
    }
    
    /**
     * Dispatch alert via all enabled channels
     */
    public function dispatch($teacher_id, $class_info) {
        $prefs = $this->get_teacher_preferences($teacher_id);
        $result = [];
        
        // Alert message content
        $alert_message = $this->format_alert_message($class_info);
        
        // SMS Alert
        if ($prefs['enable_sms']) {
            $result['sms'] = $this->send_sms($teacher_id, $prefs['phone_number'], $alert_message);
        }
        
        // Browser Notification
        if ($prefs['enable_browser']) {
            $result['browser'] = $this->send_browser_notification($teacher_id, $alert_message);
        }
        
        // Audio Alarm
        if ($prefs['enable_audio']) {
            $result['audio'] = $this->send_audio_alert($teacher_id);
        }
        
        // Dashboard Alert
        $result['dashboard'] = $this->create_dashboard_alert($teacher_id, $alert_message);
        
        // Log alert
        $this->log_alert($teacher_id, $class_info, $result);
        
        return $result;
    }
    
    /**
     * Format alert message with class details
     */
    private function format_alert_message($class_info) {
        return sprintf(
            "⚠️ CLASS ENDING SOON\n\n" .
            "Subject: %s\n" .
            "Group: %s\n" .
            "Room: %s\n" .
            "Ends at: %s\n\n" .
            "Wrap up your class!",
            $class_info['subject'] ?? 'N/A',
            $class_info['group'] ?? 'N/A',
            $class_info['room'] ?? 'N/A',
            $class_info['end_time'] ?? 'N/A'
        );
    }
    
    /**
     * Send SMS via gateway or mock
     */
    private function send_sms($teacher_id, $phone, $message) {
        if (empty($phone)) {
            return ['status' => 'failed', 'reason' => 'No phone number'];
        }
        
        // Try local SMS gateway first
        if ($this->sms_gateway_available()) {
            return $this->send_via_gateway($phone, $message);
        }
        
        // Fallback to mock SMS (log to file)
        return $this->mock_sms($teacher_id, $phone, $message);
    }
    
    /**
     * Check if local SMS gateway is available
     */
    private function sms_gateway_available() {
        // Check for SMS gateway service
        // This would check for a local service on a specific port
        // For now, returns false (fallback to mock)
        return false;
    }
    
    /**
     * Send via real SMS gateway
     */
    private function send_via_gateway($phone, $message) {
        // TODO: Implement actual SMS gateway integration
        // Examples: Twilio, AWS SNS, local gateway
        return [
            'status' => 'sent',
            'provider' => 'sms_gateway',
            'phone' => $phone,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Mock SMS (log to file for testing)
     */
    private function mock_sms($teacher_id, $phone, $message) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        
        $log_file = $log_dir . '/sms_alerts.log';
        $log_entry = sprintf(
            "[%s] Teacher #%d | Phone: %s | Message: %s\n",
            date('Y-m-d H:i:s'),
            $teacher_id,
            $phone,
            str_replace("\n", " | ", $message)
        );
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        return [
            'status' => 'mock',
            'mode' => 'logged_to_file',
            'phone' => $phone,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Send browser notification via Service Worker
     */
    private function send_browser_notification($teacher_id, $message) {
        // Store notification in session for client to pick up
        $_SESSION['pending_notifications'][] = [
            'title' => 'Class Ending Soon! 🔔',
            'body' => $message,
            'icon' => '/assets/images/alert-icon.png',
            'tag' => 'class-alert-' . time(),
            'timestamp' => time()
        ];
        
        return [
            'status' => 'queued',
            'type' => 'browser_notification',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create audio alert notification
     */
    private function send_audio_alert($teacher_id) {
        $_SESSION['audio_alert'] = [
            'sound_type' => 'alarm',
            'volume' => 0.8,
            'duration' => 5, // seconds
            'repeat' => 2,
            'timestamp' => time()
        ];
        
        return [
            'status' => 'queued',
            'type' => 'audio_alert',
            'sound' => 'alarm.mp3',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create dashboard alert banner
     */
    private function create_dashboard_alert($teacher_id, $message) {
        $_SESSION['dashboard_alert'] = [
            'message' => $message,
            'type' => 'warning',
            'dismissible' => true,
            'timestamp' => time()
        ];
        
        return [
            'status' => 'displayed',
            'type' => 'dashboard_banner',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get teacher alert preferences
     */
    private function get_teacher_preferences($teacher_id) {
        // Default preferences
        $defaults = [
            'enable_sms' => true,
            'enable_browser' => true,
            'enable_audio' => true,
            'phone_number' => '',
            'snooze_duration' => 300
        ];
        
        // Try to load from session/database
        if (isset($_SESSION['teacher_preferences'][$teacher_id])) {
            return array_merge($defaults, $_SESSION['teacher_preferences'][$teacher_id]);
        }
        
        return $defaults;
    }
    
    /**
     * Load preferences from database/session
     */
    private function load_preferences() {
        // Initialize session preferences if not exists
        if (!isset($_SESSION['teacher_preferences'])) {
            $_SESSION['teacher_preferences'] = [];
        }
    }
    
    /**
     * Log alert to history
     */
    private function log_alert($teacher_id, $class_info, $result) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        
        $log_file = $log_dir . '/alert_history.log';
        $log_entry = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'teacher_id' => $teacher_id,
            'class' => $class_info,
            'result' => $result
        ]) . "\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}

// Return alert dispatcher as singleton
if (!isset($_SESSION['alert_dispatcher'])) {
    $_SESSION['alert_dispatcher'] = new AlertDispatcher();
}

echo json_encode(['status' => 'ok', 'dispatcher_ready' => true]);
?>
