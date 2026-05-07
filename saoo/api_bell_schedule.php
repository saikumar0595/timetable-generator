<?php
/**
 * Bell Schedule API - Returns bell times for the ESP8266 Smart Alarm System
 */

session_start();
header('Content-Type: application/json');

// Logging function
function log_sync_event($status, $details = "") {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $log_file = $log_dir . '/sync_events.log';
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] IP: $client_ip | Status: $status | $details\n";
    file_put_contents($log_file, $entry, FILE_APPEND);
}

try {
    // Mock data or load from timetable
    $periods = [
        "09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50",
        "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"
    ];

    $bell_times = [];
    foreach ($periods as $period) {
        $times = explode(' - ', $period);
        foreach ($times as $t) {
            $t = trim($t);
            if (!in_array($t, $bell_times)) {
                $bell_times[] = $t;
            }
        }
    }

    // Fetch dynamic stats for the ESP8266 Dashboard
    $teacher_count = isset($_SESSION['teachers']) ? count($_SESSION['teachers']) : 12; // Default for demo
    $active_alerts = 2; 

    log_sync_event("SUCCESS", "Fetched " . count($bell_times) . " bell times");

    echo json_encode([
        'success' => true,
        'date' => date('Y-m-d'),
        'day' => date('l'),
        'bell_times' => $bell_times,
        'count' => count($bell_times),
        'teacher_count' => $teacher_count,
        'active_alerts' => $active_alerts,
        'status' => 'SYSTEM_HEALTHY'
    ]);

} catch (Exception $e) {
    log_sync_event("ERROR", $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>
