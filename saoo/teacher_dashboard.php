<?php
session_start();
include('db.php');
include('utils_timetable.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'] ?? 'student';
$username = $_SESSION['user'];

// For non-faculty, redirect to group timetable
if ($role != 'faculty' && $role != 'admin') {
    header("Location: view_timetable.php");
    exit();
}

// Find teacher name from username (univ_id)
$teacher_name = $username; // Default
if (isset($_SESSION['teachers'])) {
    foreach ($_SESSION['teachers'] as $t) {
        if (strcasecmp($t['univ_id'], $username) === 0 || strcasecmp($t['email'], $username) === 0) {
            $teacher_name = $t['name'];
            break;
        }
    }
}

// Get teacher's specific timetable
$timetable = $_SESSION['last_generated_timetable'] ?? [];
$periods = ["09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50", "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

$teacher_timetable = [];
foreach ($days as $day) {
    foreach ($periods as $period) {
        foreach (($timetable[$day][$period] ?? []) as $session) {
            if ($session['teacher'] == $teacher_name) {
                $teacher_timetable[$day][$period] = $session;
                break;
            }
        }
    }
}

// Calculate workload
$all_workloads = calculateTeacherWorkload($timetable);
$my_workload = $all_workloads[$teacher_name] ?? [
    'total_hours' => 0,
    'days' => array_fill_keys($days, 0),
    'sessions_count' => 0,
    'level' => 'N/A',
    'color' => '#94a3b8'
];

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/workload.php');
include('components/mobile_widget.php');
include('components/styles.php');

// Mock current/next for mobile widget
$current_session = null;
$next_session = null;
// Simple logic: find first session of today (or Monday for demo)
$today = date('l');
if (!isset($teacher_timetable[$today])) $today = 'Monday';

foreach($periods as $p) {
    if (isset($teacher_timetable[$today][$p])) {
        if (!$current_session) {
            $current_session = $teacher_timetable[$today][$p];
            $current_session['time'] = $p;
        } else {
            $next_session = $teacher_timetable[$today][$p];
            $next_session['time'] = $p;
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
    <title>Teacher Dashboard | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('dashboard', $role); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('My Teaching Dashboard', $_SESSION['user'], $role, true); ?>

        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row">
            
            <!-- Left Pane: Weekly Grid (Compressed) -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-8 border-r border-slate-200">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-xl">Weekly Schedule</h3>
                        <p class="text-sm text-slate-500">Your assigned sessions for the current semester</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase">
                            <?= $my_workload['total_hours'] ?> Hours / Week
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider w-24">Day</th>
                                    <?php foreach($periods as $p): ?>
                                        <th class="px-2 py-3 font-bold text-slate-500 uppercase tracking-tighter text-center border-l border-slate-100 min-w-[100px]">
                                            <span class="text-[10px]"><?= explode(' - ', $p)[0] ?></span>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($days as $day): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <th class="px-4 py-4 text-left font-bold text-indigo-900 bg-slate-50/30">
                                        <?= substr($day, 0, 3) ?>
                                    </th>
                                    <?php foreach($periods as $period): 
                                        $session = $teacher_timetable[$day][$period] ?? null;
                                    ?>
                                    <td class="p-1 border-l border-slate-100 h-20">
                                        <?php if ($session): ?>
                                            <div onclick="showSessionDetails(<?= htmlspecialchars(json_encode($session)) ?>)" 
                                                 class="h-full p-2 rounded-lg <?= $session['type'] == 'P' ? 'bg-indigo-50 border border-indigo-100 text-indigo-700' : 'bg-emerald-50 border border-emerald-100 text-emerald-700'; ?> transition hover:shadow-md cursor-pointer group">
                                                <div class="font-bold text-[10px] leading-tight mb-1"><?= htmlspecialchars($session['subject']) ?></div>
                                                <div class="flex justify-between items-center mt-auto">
                                                    <span class="text-[9px] opacity-70 font-bold"><?= htmlspecialchars($session['room']) ?></span>
                                                    <span class="text-[8px] font-black"><?= $session['type'] == 'P' ? 'LEC' : 'LAB' ?></span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="h-full flex items-center justify-center bg-emerald-50/20 rounded-lg border border-dashed border-emerald-100/50 group hover:bg-emerald-50/40 transition-colors">
                                                <span class="text-[8px] font-bold text-emerald-400 uppercase tracking-widest opacity-0 group-hover:opacity-100">Free</span>
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
                <div class="mt-6 flex items-center gap-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-indigo-100 rounded-sm"></div>
                        <span>Lecture</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-emerald-100 rounded-sm"></div>
                        <span>Practical/Lab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-emerald-50 border border-dashed border-emerald-200 rounded-sm"></div>
                        <span>Empty Slot</span>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Context Panel -->
            <div class="w-full lg:w-96 bg-white border-l border-slate-200 overflow-y-auto p-6 space-y-8">
                
                <!-- Next Up Widget -->
                <div class="space-y-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Live Awareness</p>
                    <?php renderMobileNextUp($current_session, $next_session); ?>
                </div>

                <!-- Session Details (Dynamic) -->
                <div id="session-details" class="space-y-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Session Details</p>
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 text-center py-12">
                        <i class="fas fa-mouse-pointer text-slate-200 text-3xl mb-4"></i>
                        <p class="text-sm text-slate-400 font-medium">Click a session to view details</p>
                    </div>
                </div>

                <!-- Workload Overview -->
                <div class="space-y-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Personal Workload</p>
                    <?php renderTeacherWorkload($my_workload); ?>
                </div>

            </div>
        </div>
    </main>

    <script>
        function showSessionDetails(session) {
            const container = document.getElementById('session-details');
            const groups = session.groups.join(', ');
            
            container.innerHTML = `
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Session Details</p>
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm fade-in">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i class="fas \${session.type == 'P' ? 'fa-book' : 'fa-flask'} text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg leading-tight">\${session.subject}</h4>
                            <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider">\${session.type == 'P' ? 'Lecture' : 'Lab / Practical'}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Room</span>
                            <span class="font-bold text-slate-800">\${session.room}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Groups</span>
                            <span class="font-bold text-slate-800">\${groups}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-slate-50">
                        <button class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold text-sm shadow-lg shadow-slate-200 hover:bg-black transition-all">
                            Take Attendance
                        </button>
                        <button class="w-full mt-3 py-3 border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">
                            Substitution Request
                        </button>
                    </div>
                </div>
            `;
        }
    </script>
    <script src="assets/js/chatbot.js"></script>
    <script src="assets/js/alert_handler.js"></script>
</body>
</html>
