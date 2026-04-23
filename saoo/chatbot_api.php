<?php
session_start();
header('Content-Type: application/json');

$message = strtolower(trim($_POST['message'] ?? ''));
if (empty($message)) {
    echo json_encode(['reply' => "I didn't catch that. How can I assist you with ChronoGen today?"]);
    exit;
}

$teachers = $_SESSION['teachers'] ?? [];
$subjects = $_SESSION['subjects'] ?? [];
$classrooms = $_SESSION['classrooms'] ?? [];
$groups = $_SESSION['groups'] ?? [];
$stats = $_SESSION['last_generated_stats'] ?? null;

$reply = "";

if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
    $reply = "Hello! I am your ChronoGen AI Assistant. I can help you with timetable data, faculty counts, or performance stats.";
} elseif (strpos($message, 'stat') !== false || strpos($message, 'accuracy') !== false) {
    if ($stats) {
        $reply = "Our last AI generation achieved " . $stats['hard_constraints'] . "% accuracy and " . $stats['soft_constraints'] . "% efficiency. Average idle time per group is " . $stats['avg_idle_groups'] . " slots.";
    } else {
        $reply = "I don't have any performance statistics yet. Try clicking 'Update AI Schedule' to generate a new timetable!";
    }
} elseif (strpos($message, 'how many teachers') !== false || strpos($message, 'count teachers') !== false) {
    $reply = "We currently have " . count($teachers) . " faculty members registered in the system.";
} elseif (strpos($message, 'how many subjects') !== false || strpos($message, 'count subjects') !== false) {
    $reply = "There are " . count($subjects) . " subjects available in the curriculum.";
} elseif (strpos($message, 'how many rooms') !== false || strpos($message, 'count classrooms') !== false) {
    $reply = "The university has " . count($classrooms) . " classrooms (including lecture halls and labs) managed by ChronoGen.";
} elseif (strpos($message, 'timetable') !== false || strpos($message, 'generate') !== false) {
    $reply = "You can generate and view the intelligent timetable by clicking on 'Timetable' in the sidebar or going to the Legacy Admin matrix.";
} elseif (strpos($message, 'legacy') !== false || strpos($message, 'admin') !== false) {
    $reply = "You can access the dense Master Data Control Grid by visiting the Legacy Admin Mainframe. Check the top menu or type 'go to legacy'.";
} else {
    // Basic generic fallback
    $reply = "I'm still learning! You can ask me things like 'What are the stats?', 'How many teachers?', or 'How many rooms?'.";
}

// Simulate AI typing delay for effect
usleep(500000); 

echo json_encode(['reply' => $reply]);
?>
