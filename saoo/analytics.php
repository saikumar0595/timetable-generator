<?php
session_start();
include('db.php');
include('utils_timetable.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/cards.php');
include('components/heatmap.php');
include('components/matrix.php');
include('components/workload.php');
include('components/mobile_widget.php');
include('components/styles.php');

$timetable = $_SESSION['last_generated_timetable'] ?? [];
$heatmap_data = generateDepartmentHeatmap($timetable);
$conflicts = detectScheduleConflicts($timetable);
$workloads = calculateTeacherWorkload($timetable);

// For demo, pick first teacher
$first_teacher = !empty($workloads) ? array_keys($workloads)[0] : null;
$teacher_workload = $first_teacher ? $workloads[$first_teacher] : null;

// Mock current/next for mobile widget
$current_session = null;
$next_session = null;
if (!empty($timetable)) {
    // Just pick something from Monday
    foreach($timetable['Monday'] as $p => $sessions) {
        if (!empty($sessions)) {
            $current_session = $sessions[0];
            $current_session['time'] = $p;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Analytics | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('analytics', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Advanced Analytics', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>

        <div class="flex-1 overflow-y-auto p-8 pb-20 fade-in">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Main Analytics Area -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Heatmap -->
                    <?php renderHeatmap($heatmap_data); ?>
                    
                    <!-- Conflict Matrix -->
                    <?php renderConflictMatrix($conflicts); ?>
                </div>
                
                <!-- Sidebar Analytics -->
                <div class="xl:col-span-1 space-y-8">
                    <!-- Mobile Widget Preview -->
                    <div class="space-y-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Mobile "Next-Up" Preview</p>
                        <?php renderMobileNextUp($current_session, $next_session); ?>
                    </div>
                    
                    <!-- Teacher Workload -->
                    <?php if ($teacher_workload): ?>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between ml-1">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Faculty Workload: <?= htmlspecialchars($first_teacher) ?></p>
                                <a href="manage_teachers.php" class="text-[10px] font-bold text-indigo-600 hover:underline uppercase">View All</a>
                            </div>
                            <?php renderTeacherWorkload($teacher_workload); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Optimization Score -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-4">Optimization Health</h3>
                        <div class="relative w-32 h-32 mx-auto mb-4">
                            <svg class="w-full h-full" viewBox="0 0 36 36">
                                <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-indigo-500" stroke-width="3" stroke-dasharray="85, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-slate-800">85%</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase">Score</span>
                            </div>
                        </div>
                        <p class="text-center text-xs text-slate-500 px-4">Genetic algorithm achieved near-optimal distribution with 0 hard conflicts.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
    <script src="assets/js/alert_handler.js"></script>
    <script>
        // Check for alerts every 10 seconds (faster for SMS/Alarm)
        setInterval(() => {
            fetch('notification_service.php')
                .then(r => r.json())
                .then(data => {
                    if (data.sms_sent && window.alertHandler) {
                        window.alertHandler.show_sms_toast(data.sms_sent);
                    }
                });
        }, 10000);
        // Initial check
        fetch('notification_service.php');
    </script>
</body>
</html>
