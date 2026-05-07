<?php
session_start();
include('db.php');
include('utils_timetable.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'] ?? 'student';
if ($role != 'admin') {
    header("Location: view_timetable.php");
    exit();
}

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/styles.php');

$timetable = $_SESSION['last_generated_timetable'] ?? [];
$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
$periods = ["09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50", "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

$selected_day = $_GET['day'] ?? 'Monday';

// Build Matrix for the selected day
$matrix = [];
foreach ($teachers as $t) {
    $t_name = $t['name'];
    $matrix[$t_name] = [];
    foreach ($periods as $p) {
        $matrix[$t_name][$p] = null;
        foreach (($timetable[$selected_day][$p] ?? []) as $session) {
            if ($session['teacher'] == $t_name) {
                $matrix[$t_name][$p] = $session;
                break;
            }
        }
    }
}

// Get workload levels for colors
$workloads = calculateTeacherWorkload($timetable);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conflict Matrix | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('timetable', $role); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Conflict-Aware Matrix', $_SESSION['user'], $role, true); ?>

        <div class="flex-1 overflow-y-auto p-8 pb-24">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Faculty Load Matrix</h2>
                    <p class="text-sm text-slate-500">Real-time workload distribution & conflict detection</p>
                </div>
                
                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
                    <?php foreach($days as $d): ?>
                        <a href="?day=<?= $d ?>" class="px-4 py-2 rounded-xl font-bold text-xs transition-all <?= $selected_day == $d ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">
                            <?= $d ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="sticky left-0 z-10 bg-slate-50 px-6 py-4 text-left font-bold text-slate-500 uppercase tracking-wider w-64 border-r border-slate-200">Faculty Member</th>
                                <?php foreach($periods as $p): ?>
                                    <th class="px-4 py-4 font-bold text-slate-500 uppercase tracking-tighter text-center min-w-[150px] border-r border-slate-100">
                                        <?= $p ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($matrix as $teacher => $row): 
                                $t_load = $workloads[$teacher] ?? ['level' => 'N/A', 'color' => '#94a3b8'];
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <th class="sticky left-0 z-10 bg-white group-hover:bg-slate-50 transition-colors px-6 py-4 text-left border-r border-slate-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-8 rounded-full" style="background-color: <?= $t_load['color'] ?>"></div>
                                        <div>
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($teacher) ?></div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?= $t_load['level'] ?> Load</div>
                                        </div>
                                    </div>
                                </th>
                                <?php foreach($periods as $p): 
                                    $session = $row[$p];
                                ?>
                                <td class="px-2 py-3 border-r border-slate-100 last:border-0 h-24 align-top">
                                    <?php if ($session): ?>
                                        <div class="h-full p-3 rounded-xl <?= $session['type'] == 'P' ? 'bg-indigo-50 border border-indigo-100' : 'bg-emerald-50 border border-emerald-100'; ?> transition hover:shadow-md cursor-default">
                                            <div class="font-bold <?= $session['type'] == 'P' ? 'text-indigo-700' : 'text-emerald-700'; ?> text-[11px] mb-1 leading-tight">
                                                <?= htmlspecialchars($session['subject']) ?>
                                            </div>
                                            <div class="text-[10px] text-slate-500 flex items-center gap-1 mb-2">
                                                <i class="fas fa-users opacity-50"></i>
                                                <?= implode(', ', $session['groups']) ?>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="text-[9px] font-bold text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-100 uppercase">
                                                    <?= htmlspecialchars($session['room']) ?>
                                                </div>
                                                <span class="text-[9px] font-black <?= $session['type'] == 'P' ? 'text-indigo-400' : 'text-emerald-400'; ?>">
                                                    <?= $session['type'] == 'P' ? 'LEC' : 'LAB' ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="h-full flex items-center justify-center text-slate-200 bg-slate-50/30 rounded-xl border border-dashed border-slate-100">
                                            <span class="text-[9px] font-bold uppercase tracking-widest opacity-20">Available</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Workload Intensity Legend</h4>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#10b981]"></div>
                        <span class="text-xs font-medium text-slate-600">Normal (< 4h/day)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#3b82f6]"></div>
                        <span class="text-xs font-medium text-slate-600">Moderate (4-6h/day)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#0ea5e9]"></div>
                        <span class="text-xs font-medium text-slate-600">Heavy (6-8h/day)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#ef4444]"></div>
                        <span class="text-xs font-medium text-slate-600">Critical (> 8h/day)</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
    <script src="assets/js/alert_handler.js"></script>
</body>
</html>
