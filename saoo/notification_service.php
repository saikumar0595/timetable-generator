<?php
session_start();
require_once('alert_daemon.php');

if (!isset($_SESSION['user'])) { die("Access Denied"); }

$daemon = new AlertDaemon();
$alerts_count = $daemon->check_and_alert();

$sms_status = null;
if (isset($_SESSION['sms_sent_flash'])) {
    $sms_status = $_SESSION['sms_sent_flash'];
    unset($_SESSION['sms_sent_flash']);
}

echo json_encode([
    "status" => "success",
    "time" => date('H:i'),
    "alerts_triggered" => $alerts_count,
    "sms_sent" => $sms_status
]);
?>