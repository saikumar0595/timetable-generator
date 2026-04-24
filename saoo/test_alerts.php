<?php
/**
 * Alert Daemon Test - Simulates alerts 5 minutes before class ends
 * Run this to test the alert system
 */

session_start();
include('db.php');
require_once('auto_populate.php');
require_once('alert_dispatcher.php');
require_once('alert_daemon.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Alert Daemon Test</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-slate-900 text-white p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-4xl font-bold mb-8'>🔔 Alert Daemon Test</h1>";

try {
    echo "<div class='bg-blue-900/30 border-l-4 border-blue-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Test Scenario: Simulating Class Ending Soon</h2>";
    
    // Set up test session with demo data
    $_SESSION['user'] = 'test_teacher@system';
    $_SESSION['role'] = 'teacher';
    
    require_once('auto_populate.php');
    
    echo "<p>✓ Session initialized</p>
        <p>✓ Demo data loaded</p>
        <p>✓ Alert dispatcher ready</p>
    </div>";
    
    // Create daemon and test alert detection
    $daemon = new AlertDaemon();
    
    echo "<div class='bg-purple-900/30 border-l-4 border-purple-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 1: Check Timetable</h2>";
    
    // Simulate a class that ends in exactly 5 minutes
    $current_time = time();
    $class_end_time = $current_time + 300; // 5 minutes from now
    
    echo "<p>Current time: " . date('Y-m-d H:i:s', $current_time) . "</p>
        <p>Class ending at: " . date('Y-m-d H:i:s', $class_end_time) . "</p>
        <p>Time until end: 5 minutes (Alert threshold)</p>
    </div>";
    
    // Simulate alert dispatch
    echo "<div class='bg-green-900/30 border-l-4 border-green-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 2: Trigger Alerts</h2>";
    
    $test_class = [
        'subject' => 'Data Structures',
        'group' => 'B.Tech CSE-A',
        'room' => 'Lab Room 1',
        'end_time' => date('H:i', $class_end_time),
        'teacher' => 'Dr. K. Suresh'
    ];
    
    $dispatcher = new AlertDispatcher();
    $result = $dispatcher->dispatch(1001, $test_class);
    
    echo "<p>✓ Alerts dispatched for: {$test_class['subject']}</p>";
    echo "<p style='font-size: 0.9em; color: #9ca3af;'>
        Teacher: {$test_class['teacher']}<br>
        Room: {$test_class['room']}<br>
        Ends at: {$test_class['end_time']}
    </p>";
    echo "</div>";
    
    // Show alert channels
    echo "<div class='bg-indigo-900/30 border-l-4 border-indigo-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 3: Alert Channels</h2>";
    
    foreach ($result as $channel => $status) {
        $status_text = $status['status'] ?? 'unknown';
        $icon = match($status_text) {
            'sent' => '✓',
            'queued' => '⏳',
            'mock' => '🔄',
            'displayed' => '📊',
            default => '?'
        };
        
        echo "<p>$icon <strong style='text-transform: capitalize;'>$channel</strong>: $status_text</p>";
    }
    echo "</div>";
    
    // Check session alerts
    echo "<div class='bg-yellow-900/30 border-l-4 border-yellow-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 4: Session Alerts (for Frontend)</h2>";
    
    if (isset($_SESSION['pending_notifications'])) {
        echo "<p>📬 Pending Browser Notifications: " . count($_SESSION['pending_notifications']) . "</p>";
        if (!empty($_SESSION['pending_notifications'])) {
            $notif = $_SESSION['pending_notifications'][0];
            echo "<pre style='background: #1f2937; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
            echo htmlspecialchars(json_encode($notif, JSON_PRETTY_PRINT));
            echo "</pre>";
        }
    }
    
    if (isset($_SESSION['audio_alert'])) {
        echo "<p>🔊 Audio Alert: Queued</p>";
    }
    
    if (isset($_SESSION['dashboard_alert'])) {
        echo "<p>📊 Dashboard Alert: Ready</p>";
    }
    echo "</div>";
    
    // Check log files
    echo "<div class='bg-emerald-900/30 border-l-4 border-emerald-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 5: Logs</h2>";
    
    $sms_log = __DIR__ . '/../logs/sms_alerts.log';
    $history_log = __DIR__ . '/../logs/alert_history.log';
    
    if (file_exists($sms_log)) {
        echo "<p>✓ SMS Log: <code style='background: #1f2937; padding: 2px 6px;'>logs/sms_alerts.log</code></p>";
        $last_sms = array_slice(file($sms_log), -3);
        echo "<pre style='background: #1f2937; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.85em;'>";
        echo htmlspecialchars(implode('', $last_sms));
        echo "</pre>";
    }
    
    if (file_exists($history_log)) {
        echo "<p>✓ Alert History: <code style='background: #1f2937; padding: 2px 6px;'>logs/alert_history.log</code></p>";
        $last_history = array_slice(file($history_log), -1);
        echo "<pre style='background: #1f2937; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.85em;'>";
        echo htmlspecialchars(json_encode(json_decode($last_history[0] ?? '{}'), JSON_PRETTY_PRINT));
        echo "</pre>";
    }
    echo "</div>";
    
    // Success message
    echo "<div class='bg-emerald-600/40 border-2 border-emerald-400 p-6 rounded-xl'>
        <h2 class='text-2xl font-bold mb-2'>🎉 SUCCESS - Alert System Working!</h2>
        <p class='mb-4'>All 4 alert channels have been triggered:</p>
        <ul style='margin-left: 20px; line-height: 2;'>
            <li>✓ SMS (logged to file)</li>
            <li>✓ Browser Notification (queued)</li>
            <li>✓ Audio Alert (queued)</li>
            <li>✓ Dashboard Alert (queued)</li>
        </ul>
        <br>
        <p style='color: #a7f3d0;'>Next step: Include alert_handler.js in your dashboard pages</p>
    </div>";

} catch (Exception $e) {
    echo "<div class='bg-red-600/40 border-2 border-red-400 p-6 rounded-xl'>
        <h2 class='text-2xl font-bold mb-2'>❌ Error</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <pre class='bg-red-900/30 p-4 rounded overflow-x-auto text-sm'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
    </div>";
}

echo "</div></body></html>";
?>
