<?php
/**
 * Alert Daemon - Monitors timetable for classes ending soon
 * Triggers alerts 5 minutes before class end time
 * Runs as a continuous background process
 */

session_start();
include('db.php');
require_once('alert_dispatcher.php');

class AlertDaemon {
    private $check_interval = 300; // 5 minutes in seconds
    private $warning_time = 300; // Alert 5 minutes before class ends
    private $dispatcher;
    
    public function __construct() {
        $this->dispatcher = $_SESSION['alert_dispatcher'] ?? null;
        if (!$this->dispatcher) {
            require_once('alert_dispatcher.php');
            $this->dispatcher = new AlertDispatcher();
        }
    }
    
    /**
     * Main daemon loop - checks for upcoming class endings
     */
    public function run() {
        echo "📡 Alert Daemon Started at " . date('Y-m-d H:i:s') . "\n";
        echo "⏱️  Check interval: {$this->check_interval}s | Warning time: {$this->warning_time}s\n";
        echo "════════════════════════════════════════════════════════\n\n";
        
        $iteration = 0;
        while (true) {
            $iteration++;
            $current_time = time();
            $current_datetime = date('Y-m-d H:i:s', $current_time);
            
            echo "[$iteration] Check at $current_datetime\n";
            
            // Get current timetable
            $timetable = $this->get_current_timetable();
            
            if (!empty($timetable)) {
                // Find classes ending in 5 minutes
                $upcoming_alerts = $this->find_classes_ending_soon($timetable, $current_time);
                
                if (!empty($upcoming_alerts)) {
                    echo "   ⚠️  Found " . count($upcoming_alerts) . " class(es) ending soon!\n";
                    
                    foreach ($upcoming_alerts as $alert) {
                        $this->process_alert($alert);
                    }
                } else {
                    echo "   ✓ No classes ending in next 5 minutes\n";
                }
            } else {
                echo "   ℹ️  No timetable loaded (using demo data or database)\n";
            }
            
            echo "\n";
            
            // Wait for next check interval
            sleep($this->check_interval);
        }
    }
    
    /**
     * Get current timetable from session or database
     */
    private function get_current_timetable() {
        // Try to load from session first
        if (!empty($_SESSION['last_generated_timetable'])) {
            return $_SESSION['last_generated_timetable'];
        }
        
        // Try database if available
        if (!DEMO_MODE && isset($GLOBALS['conn'])) {
            return $this->load_timetable_from_db();
        }
        
        // Load demo timetable
        if (empty($_SESSION['assignments'])) {
            require_once('auto_populate.php');
        }
        
        return $this->generate_demo_timetable();
    }
    
    /**
     * Load timetable from database
     */
    private function load_timetable_from_db() {
        // TODO: Query database for current timetable
        return [];
    }
    
    /**
     * Generate demo timetable for testing
     */
    private function generate_demo_timetable() {
        // Return a simple demo timetable for testing
        return [
            'Monday' => [
                '09:30 - 10:20' => [
                    ['subject' => 'Data Structures', 'teacher' => 'Dr. K. Suresh', 'group' => 'CSE-A', 'room' => 'A101']
                ],
                '10:20 - 11:10' => [
                    ['subject' => 'Database Systems', 'teacher' => 'Ms. G. Ramesh', 'group' => 'CSE-B', 'room' => 'B101']
                ]
            ]
        ];
    }
    
    /**
     * Find classes that end within the warning window (5 minutes from now)
     */
    private function find_classes_ending_soon($timetable, $current_time) {
        $alerts = [];
        $current_day = strtolower(date('l', $current_time)); // e.g., 'monday'
        $current_hour = intval(date('H', $current_time));
        $current_minute = intval(date('i', $current_time));
        $current_time_minutes = $current_hour * 60 + $current_minute;
        
        // Define time slots and their end times (in minutes from midnight)
        $time_slots = [
            '09:30 - 10:20' => 620,  // 10:20 = 620 minutes
            '10:20 - 11:10' => 670,
            '11:10 - 12:00' => 720,
            '12:00 - 12:50' => 770,
            '01:30 - 02:15' => 855,  // 1:30 PM = 13:30 = 810 min, end 14:15 = 855 min
            '02:15 - 03:00' => 900,
            '03:00 - 03:45' => 945,
            '03:45 - 04:30' => 990
        ];
        
        // Check each day in timetable
        foreach ($timetable as $day => $periods) {
            $day_lower = strtolower($day);
            
            // Only check current day
            if ($day_lower !== $current_day) continue;
            
            // Check each time period
            foreach ($periods as $time_slot => $classes) {
                if (!isset($time_slots[$time_slot])) continue;
                
                $end_time_minutes = $time_slots[$time_slot];
                $time_until_end = ($end_time_minutes - $current_time_minutes) * 60; // Convert to seconds
                
                // Check if class ends between now and 5 minutes from now
                if ($time_until_end > 0 && $time_until_end <= $this->warning_time) {
                    // This class is ending soon!
                    foreach ($classes as $class_info) {
                        $alerts[] = [
                            'day' => $day,
                            'time_slot' => $time_slot,
                            'end_time_seconds' => $time_until_end,
                            'class_info' => $class_info,
                            'teacher' => $class_info['teacher'] ?? 'Unknown',
                            'subject' => $class_info['subject'] ?? 'Unknown',
                            'group' => implode(', ', $class_info['groups'] ?? [$class_info['group'] ?? 'N/A']),
                            'room' => $class_info['room'] ?? 'N/A'
                        ];
                    }
                }
            }
        }
        
        return $alerts;
    }
    
    /**
     * Process alert for a teacher
     */
    private function process_alert($alert) {
        $teacher_name = $alert['teacher'];
        $subject = $alert['subject'];
        $end_seconds = $alert['end_time_seconds'];
        $end_minutes = ceil($end_seconds / 60);
        
        echo "   → Alert: $teacher_name | $subject | ends in $end_minutes min\n";
        
        // Find teacher ID (for real system, would query database)
        $teacher_id = $this->get_teacher_id($teacher_name);
        
        // Dispatch alert via all channels
        if ($teacher_id) {
            $result = $this->dispatcher->dispatch($teacher_id, [
                'subject' => $alert['subject'],
                'group' => $alert['group'],
                'room' => $alert['room'],
                'end_time' => date('H:i', time() + $end_seconds)
            ]);
            
            echo "     ✓ Alerts sent via: " . implode(', ', array_keys($result)) . "\n";
        }
    }
    
    /**
     * Get teacher ID from name (for demo)
     */
    private function get_teacher_id($teacher_name) {
        // In real system, would query database
        // For now, return a dummy ID based on hash
        return abs(crc32($teacher_name)) % 1000;
    }
}

// Run the daemon
$daemon = new AlertDaemon();

// Check if running from command line or HTTP request
if (php_sapi_name() === 'cli') {
    // Running from command line - start continuous daemon
    $daemon->run();
} else {
    // Running from HTTP - do a single check
    echo "Alert Daemon (HTTP Mode - Single Check)\n";
    echo "For continuous monitoring, run from command line\n\n";
    
    // Quick status check
    echo json_encode([
        'status' => 'ok',
        'mode' => 'http_single_check',
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => 'Daemon ready. For continuous monitoring, start from command line.'
    ]);
}
?>
