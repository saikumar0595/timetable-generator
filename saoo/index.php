<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'] ?? 'student';

if ($role != 'admin') {
    header("Location: view_timetable.php");
    exit();
}

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/cards.php');

// Fetch Statistics
if (DEMO_MODE) {
    $teacher_count = count($_SESSION['teachers'] ?? []);
    $subject_count = count($_SESSION['subjects'] ?? []);
    $classroom_count = count($_SESSION['classrooms'] ?? []);
    $group_count = count($_SESSION['groups'] ?? []);
    $assignment_count = count($_SESSION['assignments'] ?? []);
} else {
    $teacher_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM teachers"))[0];
    $subject_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM subjects"))[0];
    $classroom_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM classrooms"))[0];
    $group_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM groups"))[0];
    $assignment_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_subjects"))[0];
}

$recent_activity = DEMO_MODE ? array_slice($_SESSION['assignments'] ?? [], -5) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ChronoGen AI - Institutional Timetable System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        .stat-card {
            @apply bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php renderSidebar('dashboard', 'admin'); ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <?php renderHeader('Dashboard', $_SESSION['user'], 'admin', true); ?>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8 pb-20">
                <!-- Flash Message -->
                <?php if(isset($_SESSION['flash_message'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg fade-in">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                            <p class="text-sm font-semibold text-emerald-800"><?= htmlspecialchars($_SESSION['flash_message']) ?></p>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <?php 
                    renderStatCard('fas fa-chalkboard-teacher', 'indigo', 'Teachers', $teacher_count, 'up', '+2 this month');
                    renderStatCard('fas fa-book', 'pink', 'Subjects', $subject_count, 'stable', '-');
                    renderStatCard('fas fa-door-open', 'emerald', 'Classrooms', $classroom_count, 'up', '+0 this month');
                    renderStatCard('fas fa-users', 'amber', 'Student Groups', $group_count, 'stable', '-');
                    ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - AI Status & Quick Actions -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- AI Engine Status -->
                        <?php if(!empty($_SESSION['last_generated_stats'])): 
                            $ai_stats = $_SESSION['last_generated_stats']; 
                        ?>
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-2xl shadow-lg border border-slate-700 text-white">
                            <h3 class="font-bold text-lg mb-6 flex items-center gap-3">
                                <i class="fas fa-microchip text-indigo-400"></i> AI Engine
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-2 uppercase tracking-widest text-slate-400">
                                        <span>Optimization</span>
                                        <span><?= (int)$ai_stats['hard_constraints'] ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-700/50 h-2 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-full rounded-full" style="width: <?= (int)$ai_stats['hard_constraints'] ?>%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div class="bg-slate-700/50 p-3 rounded-lg border border-slate-600">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Efficiency</p>
                                        <p class="text-lg font-bold text-indigo-400"><?= (int)$ai_stats['soft_constraints'] ?>%</p>
                                    </div>
                                    <div class="bg-slate-700/50 p-3 rounded-lg border border-slate-600">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Avg Idle</p>
                                        <p class="text-lg font-bold text-amber-400"><?= (int)$ai_stats['avg_idle_groups'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-bolt text-amber-500"></i> Quick Actions
                            </h3>
                            <div class="space-y-3">
                                <a href="view_timetable.php?generate=1" class="flex items-center justify-between p-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all group">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-robot group-hover:scale-110 transition-transform"></i> Generate Schedule
                                    </span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                <a href="manage_teachers.php" class="flex items-center justify-between p-3.5 bg-slate-100 text-slate-800 rounded-xl font-semibold hover:bg-slate-200 transition-all">
                                    <span><i class="fas fa-users mr-2"></i>Manage Teachers</span>
                                    <i class="fas fa-arrow-right text-slate-400"></i>
                                </a>
                                <a href="populate_demo.php" class="flex items-center justify-between p-3.5 bg-slate-100 text-slate-800 rounded-xl font-semibold hover:bg-slate-200 transition-all">
                                    <span><i class="fas fa-database mr-2"></i>Reset Demo Data</span>
                                    <i class="fas fa-arrow-right text-slate-400"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Recent Activity & Resources -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Resource Overview -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            renderActionCard('fas fa-chalkboard-teacher', 'Manage Teachers', 'Add, edit, or remove faculty members', '', 'manage_teachers.php');
                            renderActionCard('fas fa-book', 'Manage Subjects', 'Organize courses and assignments', '', 'manage_subjects.php');
                            renderActionCard('fas fa-door-open', 'Manage Rooms', 'Configure classroom resources', '', 'manage_classrooms.php');
                            renderActionCard('fas fa-users-class', 'Manage Groups', 'Create and manage student groups', '', 'manage_groups.php');
                            ?>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-history text-blue-500"></i> Recent Activity
                            </h3>
                            <?php if(!empty($recent_activity)): ?>
                                <div class="space-y-3">
                                    <?php foreach(array_slice($recent_activity, 0, 5) as $activity): ?>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($activity['t_name'] ?? 'Unknown') ?></p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($activity['s_name'] ?? 'N/A') ?></p>
                                        </div>
                                        <span class="badge badge-primary text-xs">Assigned</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-6">
                                    <i class="fas fa-inbox text-3xl text-slate-300 mb-2 block"></i>
                                    <p class="text-slate-500 font-medium">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- System Status -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-heartbeat text-emerald-500"></i> System Health
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                                    <p class="text-xs text-emerald-600 font-semibold uppercase mb-1">Server</p>
                                    <div class="flex items-center justify-center gap-1 text-emerald-600">
                                        <i class="fas fa-circle text-sm"></i>
                                        <span class="font-bold">Online</span>
                                    </div>
                                </div>
                                <div class="text-center p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                                    <p class="text-xs text-indigo-600 font-semibold uppercase mb-1">Database</p>
                                    <div class="flex items-center justify-center gap-1 text-indigo-600">
                                        <i class="fas fa-circle text-sm"></i>
                                        <span class="font-bold">Connected</span>
                                    </div>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-xs text-blue-600 font-semibold uppercase mb-1">API</p>
                                    <div class="flex items-center justify-center gap-1 text-blue-600">
                                        <i class="fas fa-circle text-sm"></i>
                                        <span class="font-bold">Running</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize tooltips and interactions
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded successfully');
        });
    </script>
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
