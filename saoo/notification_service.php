<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { die("Access Denied"); }

// Fetch Today's Timetable (Simulation of a background worker)
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$today = $days[date('w')];
$currentTime = date('H:i');

// In a real app, this would be a CRON job.
// For this project, we'll provide a manual "Trigger Alerts" or simulated logs.

$timetable = $_SESSION['last_generated_timetable'] ?? []; // We should save the latest in session

if (empty($timetable)) {
    echo json_encode(["status" => "error", "message" => "No timetable generated yet."]);
    exit();
}

$notifications_sent = [];
$today_schedule = $timetable[$today] ?? [];

foreach ($today_schedule as $period_time => $classes) {
    foreach ($classes as $class) {
        // period_time format "09:30 - 10:20"
        $startTime = explode(' - ', $period_time)[0];
        $time_diff = (strtotime($startTime) - strtotime($currentTime)) / 60;

        // If class starts in 5 minutes (or if we are just demonstrating the "send" capability)
        if ($time_diff <= 5 && $time_diff > 0) {
            $teacher = $class['teacher'];
            $subject = $class['subject'];
            $room = $class['room'];
            
            // Simulation of WhatsApp Link / Message
            $msg = "Reminder: Your class for $subject in $room starts at $startTime.";
            $notifications_sent[] = [
                "teacher" => $teacher,
                "message" => $msg,
                "type" => "WhatsApp/Email"
            ];
        }
    }
}

echo json_encode([
    "status" => "success",
    "today" => $today,
    "time" => $currentTime,
    "upcoming_alerts" => $notifications_sent
]);
?>