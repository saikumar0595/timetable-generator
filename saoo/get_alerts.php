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

// 1. Check Shared Storage (Bridge from CLI)
$shared_file = __DIR__ . '/../logs/shared_alerts.json';
if (file_exists($shared_file)) {
    $shared_alerts = json_decode(file_get_contents($shared_file), true) ?: [];
    if (!empty($shared_alerts)) {
        $alert = array_shift($shared_alerts); // Get oldest
        file_put_contents($shared_file, json_encode($shared_alerts)); // Save remaining

        // Map to browser response
        $response['browser_notification'] = [
            'title' => 'System Alert 🔔',
            'body' => $alert['message'],
            'tag' => 'shared-' . $alert['timestamp']
        ];
        $response['audio_alert'] = [
            'volume' => 0.8,
            'message' => $alert['message']
        ];
        $response['dashboard_alert'] = [
            'message' => $alert['message'],
            'type' => 'info'
        ];
        
        // Return immediately if we found a shared alert to avoid flooding
        echo json_encode($response);
        exit();
    }
}

// 2. Check Session-based notifications (Internal Web triggers)
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
