<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
$assignments = DEMO_MODE ? ($_SESSION['assignments'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT ts.*, s.name as s_name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id"), MYSQLI_ASSOC);

// Map subjects to teachers
$teacher_subjects = [];
foreach ($assignments as $a) {
    $t_id = $a['t_id'] ?? ($a['teacher_id'] ?? null);
    if ($t_id) {
        $teacher_subjects[$t_id][] = $a['s_name'];
    }
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
            <span class="text-lg font-bold tracking-tight uppercase">Audisankara</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all">
                <i class="fas fa-th-large w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="teacher_directory.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl border border-indigo-500/20 shadow-sm">
                <i class="fas fa-id-badge w-5"></i>
                <span class="font-medium">Faculty Directory</span>
            </a>
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all">
                <i class="fas fa-chalkboard-teacher w-5"></i>
                <span class="font-medium">Manage Teachers</span>
            </a>
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all">
                <i class="fas fa-book w-5"></i>
                <span class="font-medium">Subjects</span>
            </a>
            <a href="view_timetable.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all">
                <i class="fas fa-calendar-alt w-5"></i>
                <span class="font-medium">Timetable</span>
            </a>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Faculty Dashboard</h2>
            <div class="flex items-center gap-4">
                <a href="populate_demo.php" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-sm font-bold border border-amber-200 hover:bg-amber-100 transition">
                    <i class="fas fa-sync-alt mr-2"></i> Reload Profiles
                </a>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($teachers as $t): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                    <!-- Profile Header -->
                    <div class="relative h-32 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <div class="absolute -bottom-12 left-6">
                            <img src="<?php echo $t['photo'] ?? 'https://ui-avatars.com/api/?name='.urlencode($t['name']).'&background=random'; ?>" 
                                 class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg object-cover" 
                                 alt="Teacher Photo">
                        </div>
                    </div>

                    <!-- Profile Body -->
                    <div class="pt-16 pb-6 px-6">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-slate-800 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($t['name']); ?></h3>
                            <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mt-1"><?php echo htmlspecialchars($t['role'] ?? 'Faculty Member'); ?></p>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <i class="fas fa-graduation-cap w-5 text-slate-400"></i>
                                <span><?php echo htmlspecialchars($t['qualification'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <i class="fas fa-briefcase w-5 text-slate-400"></i>
                                <span><?php echo htmlspecialchars($t['experience'] ?? 'N/A'); ?> Experience</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <i class="fas fa-envelope w-5 text-slate-400"></i>
                                <span class="truncate"><?php echo htmlspecialchars($t['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <i class="fas fa-phone-alt w-5 text-slate-400"></i>
                                <span><?php echo htmlspecialchars($t['phone'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <!-- Subjects Tag Cloud -->
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Expertise & Dealing Subjects</p>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $subs = $teacher_subjects[$t['id']] ?? ['General Studies'];
                                foreach ($subs as $s): 
                                ?>
                                    <span class="px-3 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-bold border border-slate-100">
                                        <?php echo htmlspecialchars($s); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>