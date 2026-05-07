<?php
/**
 * Timetable Status API
 * Returns current class and time until next event
 */
session_start();
header('Content-Type: application/json');

$timetable = $_SESSION['last_generated_timetable'] ?? [];
if (empty($timetable)) {
    echo json_encode(['success' => false, 'error' => 'No timetable']);
    exit();
}

$current_day = date('l');
// Mock for weekends
if ($current_day === 'Saturday' || $current_day === 'Sunday') $current_day = 'Monday';

$schedule = $timetable[$current_day] ?? [];
$current_time = time();
$next_event = null;
$min_diff = 86400; // Max seconds in day

foreach ($schedule as $period => $sessions) {
    $times = explode(' - ', $period);
    if (count($times) !== 2) continue;

    $start = strtotime(date('Y-m-d') . ' ' . $times[0]);
    $end = strtotime(date('Y-m-d') . ' ' . $times[1]);

    // Check Start
    $diff_start = $start - $current_time;
    if ($diff_start > 0 && $diff_start < $min_diff) {
        $min_diff = $diff_start;
        $next_event = [
            'type' => 'STARTING',
            'time' => $times[0],
            'diff' => $diff_start,
            'subject' => $sessions[0]['subject'] ?? 'Class'
        ];
    }

    // Check End
    $diff_end = $end - $current_time;
    if ($diff_end > 0 && $diff_end < $min_diff) {
        $min_diff = $diff_end;
        $next_event = [
            'type' => 'ENDING',
            'time' => $times[1],
            'diff' => $diff_end,
            'subject' => $sessions[0]['subject'] ?? 'Class'
        ];
    }
}

echo json_encode([
    'success' => true,
    'next_event' => $next_event,
    'server_time' => date('H:i:s')
]);
?>
