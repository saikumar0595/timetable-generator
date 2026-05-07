<?php
session_start();
/**
 * Test script for dual alerts (Starting & Ending)
 */
require_once('alert_dispatcher.php');

// Mock teacher with phone number
$_SESSION['teachers'] = [
    ['id' => 1, 'name' => 'Dr. Smith', 'phone' => '+1234567890'],
    ['id' => 2, 'name' => 'Prof. Jones', 'phone' => '+0987654321']
];

$dispatcher = new AlertDispatcher();

$teacher_id = 1;
$class_info_starting = [
    'type' => 'STARTING',
    'subject' => 'Introduction to AI',
    'group' => 'CS-2024-A',
    'room' => 'L-101',
    'start_time' => '09:00',
    'teacher' => 'Dr. Smith'
];

$class_info_ending = [
    'type' => 'ENDING',
    'subject' => 'Data Structures',
    'group' => 'CS-2024-B',
    'room' => 'L-102',
    'end_time' => '11:00',
    'teacher' => 'Prof. Jones'
];

echo "Testing STARTING alert...\n";
$res1 = $dispatcher->dispatch($teacher_id, $class_info_starting);
print_r($res1);

echo "Testing ENDING alert...\n";
$res2 = $dispatcher->dispatch($teacher_id, $class_info_ending);
print_r($res2);

if (isset($_GET['redirect'])) {
    $_SESSION['flash_message'] = "Test alerts triggered successfully! Check your dashboard for notifications.";
    header("Location: " . $_GET['redirect']);
    exit();
}

echo "\nVerification: Check logs/sms_alerts.log for formatted messages.\n";
?>
