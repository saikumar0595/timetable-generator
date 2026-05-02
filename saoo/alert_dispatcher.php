<?php
/**
 * Alert Dispatcher - Central hub for all alert types
 * Routes alerts to SMS, Browser Notifications, Audio, and Dashboard
 */

class AlertDispatcher {
    private $preferences = [];
    
    public function __construct() {
        // Load preferences
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
    
    private function send_sms($teacher_id, $phone, $message) {
        if (empty($phone)) {
            // Find phone in teachers session if not in prefs
            if (isset($_SESSION['teachers'])) {
                foreach ($_SESSION['teachers'] as $t) {
                    if ($t['id'] == $teacher_id) {
                        $phone = $t['phone'];
                        break;
                    }
                }
            }
        }

        if (empty($phone)) return ['status' => 'failed', 'reason' => 'No phone number'];
        
        // Mock SMS (log to file)
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        
        $log_file = $log_dir . '/sms_alerts.log';
        $log_entry = sprintf(
            "[%s] SMS to [%s]: %s\n",
            date('Y-m-d H:i:s'),
            $phone,
            str_replace("\n", " | ", $message)
        );
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        return ['status' => 'sent', 'mode' => 'mock_logged', 'phone' => $phone];
    }
    
    private function send_browser_notification($teacher_id, $message) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['pending_notifications'][] = [
            'title' => 'Class Ending Soon! 🔔',
            'body' => $message,
            'tag' => 'class-alert-' . time(),
            'timestamp' => time()
        ];
        return ['status' => 'queued'];
    }
    
    private function send_audio_alert($teacher_id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['audio_alert'] = [
            'volume' => 0.8,
            'duration' => 5,
            'repeat' => 2,
            'timestamp' => time()
        ];
        return ['status' => 'queued'];
    }
    
    private function create_dashboard_alert($teacher_id, $message) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['dashboard_alert'] = [
            'message' => $message,
            'type' => 'warning',
            'dismissible' => true,
            'timestamp' => time()
        ];
        return ['status' => 'displayed'];
    }
    
    private function get_teacher_preferences($teacher_id) {
        return [
            'enable_sms' => true,
            'enable_browser' => true,
            'enable_audio' => true,
            'phone_number' => ''
        ];
    }
    
    private function log_alert($teacher_id, $class_info, $result) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        $log_file = $log_dir . '/alert_history.log';
        $log_entry = json_encode(['time' => date('Y-m-d H:i:s'), 'tid' => $teacher_id, 'class' => $class_info, 'result' => $result]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}
?>
