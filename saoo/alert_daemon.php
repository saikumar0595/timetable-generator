<?php
/**
 * Alert Daemon - Monitors timetable and triggers alerts via AlertDispatcher
 * Can run as a standalone background process or be called via web
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('alert_dispatcher.php');

class AlertDaemon {
    private $dispatcher;
    
    public function __construct() {
        $this->dispatcher = new AlertDispatcher();
    }
    
    /**
     * Run a single check of the timetable
     */
    public function check_and_alert() {
        // Load timetable from session (Web mode) or file (CLI mode fallback)
        $timetable = $_SESSION['last_generated_timetable'] ?? $this->load_timetable_from_file();
        
        if (empty($timetable)) return 0;
        
        $current_day = date('l');
        $current_time = time();
        
        // Mock current day if weekend for demo
        if ($current_day === 'Saturday' || $current_day === 'Sunday') {
            $current_day = 'Monday';
        }
        
        $today_schedule = $timetable[$current_day] ?? [];
        $alerts_triggered = 0;
        
        foreach ($today_schedule as $period => $sessions) {
            $times = explode(' - ', $period);
            if (count($times) !== 2) continue;
            
            $end_time_str = $times[1];
            
            // Adjust end time for comparison
            $end_timestamp = strtotime(date('Y-m-d') . ' ' . $end_time_str);
            
            // If class ends in 5 minutes (300 seconds)
            // Range check: 240 to 300 seconds remaining
            $diff = $end_timestamp - $current_time;
            
            if ($diff > 0 && $diff <= 300) {
                foreach ($sessions as $session) {
                    $class_info = [
                        'subject' => $session['subject'],
                        'group' => implode(', ', $session['groups']),
                        'room' => $session['room'],
                        'end_time' => $end_time_str,
                        'teacher' => $session['teacher']
                    ];
                    
                    $teacher_id = $this->find_teacher_id($session['teacher']);
                    
                    // Create unique alert tag to prevent duplicates
                    $alert_tag = 'alert_' . md5($session['teacher'] . $period . date('Ymd'));
                    if (!isset($_SESSION['sent_alerts'])) $_SESSION['sent_alerts'] = [];
                    
                    if (!in_array($alert_tag, $_SESSION['sent_alerts'])) {
                        $this->dispatcher->dispatch($teacher_id, $class_info);
                        $_SESSION['sent_alerts'][] = $alert_tag;
                        $alerts_triggered++;
                    }
                }
            }
        }
        
        return $alerts_triggered;
    }
    
    private function find_teacher_id($name) {
        if (isset($_SESSION['teachers'])) {
            foreach ($_SESSION['teachers'] as $t) {
                if ($t['name'] === $name) return $t['id'];
            }
        }
        return 0;
    }
    
    private function load_timetable_from_file() {
        // Attempt to load from shared output file
        $file = __DIR__ . '/../timetable-generator/test_output.json';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $data['schedule'] ?? [];
        }
        return [];
    }
}

// CLI Execution Logic
if (php_sapi_name() === 'cli' && realpath($argv[0]) === realpath(__FILE__)) {
    $daemon = new AlertDaemon();
    echo "[" . date('Y-m-d H:i:s') . "] 🔔 ChronoGen Alert Daemon Started\n";
    echo "[INFO] Monitoring for classes ending in 5 minutes...\n";
    
    while (true) {
        $count = $daemon->check_and_alert();
        if ($count > 0) {
            echo "[" . date('Y-m-d H:i:s') . "] ✓ Triggered $count alerts.\n";
        }
        sleep(30); // Check every 30 seconds
    }
}
?>
