<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
$assignments = DEMO_MODE ? ($_SESSION['assignments'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT ts.*, s.name as s_name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id"), MYSQLI_ASSOC);

$teacher_subjects = [];
foreach ($assignments as $a) {
    $t_id = $a['t_id'] ?? ($a['teacher_id'] ?? null);
    if ($t_id) $teacher_subjects[$t_id][] = $a['s_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Directory | Audisankara University</title>
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
                <svg width="32" height="32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="#1e3a8a" stroke="#fbbf24" stroke-width="5"/>
                    <text x="50" y="55" font-family="Arial, sans-serif" font-weight="bold" font-size="28" fill="#ffffff" text-anchor="middle">A</text>
                </svg>
            </div>
            <span class="text-lg font-bold tracking-tight uppercase text-white/90">Audisankara</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-th-large w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="teacher_directory.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl border border-indigo-500/20 shadow-sm">
                <i class="fas fa-id-badge w-5 text-center"></i>
                <span class="font-medium">Faculty Directory</span>
            </a>
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-chalkboard-teacher w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Teachers</span>
            </a>
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-book w-5 text-center group-hover:text-pink-400 transition-colors"></i>
                <span class="font-medium">Subjects</span>
            </a>
            <a href="manage_classrooms.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-door-open w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Classrooms</span>
            </a>
            <a href="manage_groups.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-users w-5 text-center group-hover:text-amber-400 transition-colors"></i>
                <span class="font-medium">Student Groups</span>
            </a>
            <a href="view_timetable.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-calendar-alt w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                <span class="font-medium">Timetable</span>
            </a>
            <div class="mt-8 border-t border-slate-800 pt-6">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all duration-200 group">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="font-medium">Sign Out</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Faculty Directory</h2>
            <div class="flex items-center gap-4">
                <a href="populate_demo.php" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-sm font-bold border border-amber-200 hover:bg-amber-100 transition">
                    <i class="fas fa-sync-alt mr-2"></i> Reload Data
                </a>
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                    <?php echo substr($_SESSION['user'], 0, 1); ?>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($teachers as $t): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all group">
                    <div class="h-24 bg-gradient-to-r from-indigo-600 to-purple-600 relative">
                        <img src="<?= $t['photo'] ?? 'https://ui-avatars.com/api/?name='.urlencode($t['name']) ?>" class="w-20 h-20 rounded-2xl border-4 border-white absolute -bottom-10 left-6 shadow-lg object-cover">
                    </div>
                    <div class="pt-12 pb-6 px-6">
                        <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($t['name']) ?></h3>
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4"><?= htmlspecialchars($t['role'] ?? 'Faculty') ?></p>
                        <div class="space-y-2 text-sm text-slate-500 mb-6">
                            <div class="flex items-center gap-2"><i class="fas fa-envelope w-4 text-slate-300"></i> <?= htmlspecialchars($t['email']) ?></div>
                            <div class="flex items-center gap-2"><i class="fas fa-graduation-cap w-4 text-slate-300"></i> <?= htmlspecialchars($t['qualification'] ?? 'N/A') ?></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach(($teacher_subjects[$t['id']] ?? ['General']) as $s): ?>
                                <span class="px-2 py-1 bg-slate-50 text-slate-500 rounded text-[10px] font-bold border border-slate-100 uppercase"><?= htmlspecialchars($s) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>