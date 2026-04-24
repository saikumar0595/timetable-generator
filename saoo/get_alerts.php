<?php
/**
 * Get Alerts API - Returns pending alerts for the current user
 * Called by JavaScript alert_handler.js
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$response = [
    'success' => true,
    'browser_notification' => null,
    'audio_alert' => null,
    'dashboard_alert' => null
];

// Check for pending notifications in session
if (isset($_SESSION['pending_notifications']) && !empty($_SESSION['pending_notifications'])) {
    $response['browser_notification'] = array_shift($_SESSION['pending_notifications']);
}

// Check for audio alert
if (isset($_SESSION['audio_alert'])) {
    $response['audio_alert'] = $_SESSION['audio_alert'];
    unset($_SESSION['audio_alert']);
}

// Check for dashboard alert
if (isset($_SESSION['dashboard_alert'])) {
    $response['dashboard_alert'] = $_SESSION['dashboard_alert'];
    unset($_SESSION['dashboard_alert']);
}

echo json_encode($response);
?>
