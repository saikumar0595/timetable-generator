<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'] ?? 'student';

// Strictly redirect non-admins away from the main dashboard
if ($role != 'admin') {
    header("Location: view_timetable.php");
    exit();
}

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

$recent_activity = DEMO_MODE ? array_slice($_SESSION['assignments'] ?? [], -5) : mysqli_fetch_all(mysqli_query($conn, "SELECT ts.id, t.name as t_name, s.name as s_name FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id JOIN subjects s ON ts.subject_id = s.id ORDER BY ts.id DESC LIMIT 5"), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | ChronoGen Timetable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20">
        <div class="h-20 flex items-center px-8 border-b border-slate-800">
            <div class="w-8 h-8 mr-3">
                <svg width="32" height="32" viewBox="0 0 100 100" fill="none">
                    <path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="#1e3a8a" stroke="#fbbf24" stroke-width="5"/>
                    <text x="50" y="55" font-weight="bold" font-size="28" fill="white" text-anchor="middle">A</text>
                </svg>
            </div>
            <span class="text-lg font-bold tracking-tight uppercase">Audisankara</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl border border-indigo-500/20 shadow-sm"><i class="fas fa-th-large w-5 text-center"></i><span class="font-medium">Dashboard</span></a>
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-chalkboard-teacher w-5 text-center"></i><span class="font-medium">Teachers</span></a>
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-book w-5 text-center"></i><span class="font-medium">Subjects</span></a>
            <a href="manage_classrooms.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-door-open w-5 text-center"></i><span class="font-medium">Classrooms</span></a>
            <a href="manage_groups.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-users w-5 text-center"></i><span class="font-medium">Student Groups</span></a>
            <a href="view_timetable.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-calendar-alt w-5 text-center"></i><span class="font-medium">Timetable</span></a>
            <div class="mt-8 border-t border-slate-800 pt-6">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all group"><i class="fas fa-sign-out-alt w-5 text-center"></i><span class="font-medium">Sign Out</span></a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">System Overview</h2>
            <div class="flex items-center gap-4">
                <a href="populate_demo.php" class="px-6 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-amber-500/30 hover:bg-amber-600 transition-all">
                    <i class="fas fa-sync-alt mr-2"></i> Reset Demo Data
                </a>
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <?php if(isset($_SESSION['flash_message'])): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-8 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0"><i class="fas fa-check-circle text-emerald-500"></i></div>
                        <div class="ml-3"><p class="text-sm font-bold text-emerald-800"><?= $_SESSION['flash_message'] ?></p></div>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div><p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Teachers</p><h3 class="text-2xl font-bold text-slate-800"><?= $teacher_count ?></h3></div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl"><i class="fas fa-book"></i></div>
                    <div><p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Subjects</p><h3 class="text-2xl font-bold text-slate-800"><?= $subject_count ?></h3></div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl"><i class="fas fa-door-open"></i></div>
                    <div><p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Classrooms</p><h3 class="text-2xl font-bold text-slate-800"><?= $classroom_count ?></h3></div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
                    <div><p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Groups</p><h3 class="text-2xl font-bold text-slate-800"><?= $group_count ?></h3></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 space-y-6">
                    <?php if(!empty($_SESSION['last_generated_stats'])): 
                        $ai_stats = $_SESSION['last_generated_stats']; ?>
                        <div class="bg-slate-900 p-6 rounded-2xl shadow-xl border-t-4 border-indigo-500 text-white">
                            <h3 class="font-bold text-lg mb-6 flex items-center gap-3">
                                <i class="fas fa-microchip text-indigo-400"></i> AI Engine Status
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1 uppercase tracking-widest text-slate-400">
                                        <span>Accuracy</span>
                                        <span><?= $ai_stats['hard_constraints'] ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-indigo-500 h-full" style="width: <?= $ai_stats['hard_constraints'] ?>%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div class="bg-slate-800/50 p-3 rounded-xl border border-slate-800">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase">Efficiency</p>
                                        <h4 class="text-lg font-bold text-indigo-400"><?= $ai_stats['soft_constraints'] ?>%</h4>
                                    </div>
                                    <div class="bg-slate-800/50 p-3 rounded-xl border border-slate-800">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase">Avg. Idle</p>
                                        <h4 class="text-lg font-bold text-amber-400"><?= $ai_stats['avg_idle_groups'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-6">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="view_timetable.php?generate=1" class="flex items-center justify-between p-4 bg-indigo-600 text-white rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all"><span>Generate AI Schedule</span><i class="fas fa-arrow-right"></i></a>
                            <a href="legacy_admin.php" class="flex items-center justify-between p-4 bg-slate-900 text-sky-400 rounded-xl font-bold hover:bg-slate-800 border border-sky-900 shadow-lg shadow-sky-900/20"><span>Legacy Admin</span><i class="fas fa-terminal"></i></a>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <!-- Workload Analytics Chart -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6 p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Entity Distribution Analytics</h3>
                        <div class="relative h-64 w-full">
                            <canvas id="entityChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50"><h3 class="font-bold text-slate-700">Recent Assignments</h3></div>
                        <div class="divide-y divide-slate-50">
                            <?php foreach($recent_activity as $ra): ?>
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50">
                                <div><p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($ra['t_name']) ?></p><p class="text-xs text-slate-400">Assigned to <span class="font-bold text-indigo-500"><?= htmlspecialchars($ra['s_name']) ?></span></p></div>
                                <span class="text-[10px] font-bold text-slate-300 uppercase">ACTIVE</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('entityChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Teachers', 'Subjects', 'Classrooms', 'Groups'],
                        datasets: [{
                            data: [<?= $teacher_count ?>, <?= $subject_count ?>, <?= $classroom_count ?>, <?= $group_count ?>],
                            backgroundColor: ['#6366f1', '#ec4899', '#10b981', '#f59e0b'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
</body>
</html>