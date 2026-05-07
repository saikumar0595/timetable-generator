<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Include components
include('components/sidebar.php');
include('components/header.php');

$role = $_SESSION['role'] ?? 'student';

// Helper function to safely access array values
function get_safe($array, $key, $default = 'N/A') {
    return $array[$key] ?? $default;
}

// ... (Keep existing logic for calling Python API) ...
$base_dir = realpath(__DIR__ . '/../timetable-generator');
$timetable = $_SESSION['last_generated_timetable'] ?? [];
$stats = $_SESSION['last_generated_stats'] ?? [];
$error = null;

// Handle Generation Request (Only Admin can generate)
if (isset($_GET['generate']) && $role == 'admin') {
    // Call the new generation API
    include('generate_timetable.php');
    exit();
}

$groups_data = DEMO_MODE ? ($_SESSION['groups'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM groups"), MYSQLI_ASSOC);
$selected_group = $_GET['group'] ?? (is_array($groups_data) && count($groups_data) > 0 ? $groups_data[0]['name'] : null);

$periods = ["09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50", "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// PERFORMANCE OPTIMIZATION: Pre-filter timetable for the selected group
$group_timetable = [];

if ($selected_group && !empty($timetable)) {
    foreach ($days as $day) {
        $prev_session_info = null;
        foreach ($periods as $period) {
            $found_for_group = false;
            foreach (($timetable[$day][$period] ?? []) as $cls) {
                if (in_array($selected_group, $cls['groups'])) {
                    $subject = trim(preg_replace('/\s+/', ' ', $cls['subject']));
                    $teacher = trim(preg_replace('/\s+/', ' ', $cls['teacher']));
                    $room = trim(preg_replace('/\s+/', ' ', $cls['room']));
                    
                    $current_session_info = "$subject|$teacher|$room";
                    
                    // Only add if it's NOT a continuation of the same class from the previous period
                    if ($current_session_info !== $prev_session_info) {
                        $group_timetable[$day][$period] = [
                            'subject' => $subject,
                            'teacher' => $teacher,
                            'room' => $room,
                            'type' => $cls['type']
                        ];
                    }
                    
                    $prev_session_info = $current_session_info;
                    $found_for_group = true;
                    break;
                }
            }
            if (!$found_for_group) {
                $prev_session_info = null;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Timetable | AUDISANKARA TEACHERS GOLE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            aside, .no-print, .filters { display: none !important; }
            main { margin: 0; padding: 0; width: 100%; overflow: visible; }
            body { background: white; -webkit-print-color-adjust: exact; }
            .print-border { border: 1px solid #000; box-shadow: none !important; }
            th { background-color: #f3f4f6 !important; color: #1f2937 !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php renderSidebar('timetable', $role); ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('AI Schedule Generator', $_SESSION['user'], $role, false); ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24">
            <!-- Filter Section (Mobile Friendly) -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
                <div class="flex items-center gap-3">
                    <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 flex items-center gap-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Group:</label>
                        <select onchange="window.location.href='?group='+this.value" class="bg-transparent font-bold text-indigo-600 focus:outline-none text-sm">
                            <?php foreach($groups_data as $g): ?>
                                <option value="<?php echo htmlspecialchars($g['name']); ?>" <?php echo $selected_group == $g['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
                    <button onclick="shareTimetable()" class="flex-shrink-0 flex items-center gap-2 px-3 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition font-medium text-xs shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button onclick="window.print()" class="flex-shrink-0 flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium text-xs shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <?php if($role=='admin'): ?>
                    <button onclick="generateTimetable(this)" class="flex-shrink-0 flex items-center gap-2 px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition font-medium text-xs shadow-lg shadow-amber-500/20">
                        <i class="fas fa-sync-alt"></i> <span>Regen</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500"></i></div>
                        <div class="ml-3"><p class="text-sm text-red-700"><?= $error ?></p></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($timetable)): ?>
                <div class="max-w-4xl mx-auto text-center mt-20">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">No Timetable Generated</h3>
                    <p class="text-slate-500 mb-8">The AI scheduling engine has not been triggered yet for this session.</p>
                    
                    <?php if($role == 'admin'): ?>
                        <button onclick="generateTimetable(this)" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all inline-flex items-center gap-2">
                            <i class="fas fa-rocket"></i> <span>Trigger Genetic Engine Now</span>
                        </button>
                    <?php else: ?>
                        <p class="text-indigo-600 font-medium italic">Please ask an administrator to generate the institutional schedule.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            
                <!-- Technical Performance Report -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 no-print">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Hard Constraints</p>
                        <h4 class="text-xl font-bold text-emerald-600"><?php echo get_safe($stats, 'hard_constraints', 0); ?>%</h4>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?php echo get_safe($stats, 'hard_constraints', 0); ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Soft Constraints</p>
                        <h4 class="text-xl font-bold text-indigo-600"><?php echo get_safe($stats, 'soft_constraints', 0); ?>%</h4>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?php echo get_safe($stats, 'soft_constraints', 0); ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Group Idle</p>
                        <h4 class="text-xl font-bold text-slate-800"><?php echo get_safe($stats, 'avg_idle_groups', 0); ?></h4>
                        <p class="text-[10px] text-slate-400 mt-1">Total: <?php echo get_safe($stats, 'total_idle_groups', 0); ?> slots</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Free Hour</p>
                        <h4 class="text-xl font-bold <?php echo get_safe($stats, 'free_hour_exists', 'No') == 'Yes' ? 'text-emerald-600' : 'text-rose-500'; ?>">
                            <?php echo get_safe($stats, 'free_hour_exists', 'No'); ?>
                        </h4>
                        <p class="text-[10px] text-slate-400 mt-1">Found in week</p>
                    </div>
                </div>
            
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print-border">
                    <div class="bg-slate-50 border-b border-slate-200 px-8 py-6 text-center hidden print:block">
                        <h1 class="text-3xl font-bold text-slate-900 uppercase tracking-widest">Weekly Timetable</h1>
                        <p class="text-slate-500 text-sm mt-1">Schedule for: <span class="text-indigo-600 font-bold"><?php echo htmlspecialchars($selected_group); ?></span></p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-6 py-4 text-left font-bold text-slate-500 uppercase tracking-wider w-32 border-r border-slate-100">Day / Time</th>
                                    <?php foreach($periods as $index => $p): 
                                        if ($index == 4): ?>
                                            <th class="px-2 py-4 font-bold text-slate-400 uppercase tracking-wider text-center border-r border-l border-slate-200 bg-slate-100 w-12">
                                                <span class="vertical-text text-[10px]">BREAK</span>
                                            </th>
                                        <?php endif; 
                                        $p_escaped = htmlspecialchars($p); 
                                    ?>
                                        <th class="px-4 py-4 font-bold text-slate-500 uppercase tracking-wider text-center border-r border-slate-100 last:border-0 min-w-[140px]">
                                            <?php echo $p_escaped; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($days as $day): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <th class="px-6 py-4 text-left font-bold text-indigo-900 bg-slate-50/30 border-r border-slate-100 w-32">
                                        <?php echo $day; ?>
                                    </th>
                                    <?php 
                                        for ($i = 0; $i < 8; $i++): 
                                            if ($i == 4): // Lunch Break
                                                echo '<td class="px-2 py-3 border-r border-l border-slate-200 bg-slate-100 h-24 align-middle text-center"><div class="font-bold text-slate-400 text-xs tracking-wider vertical-text">LUNCH</div></td>';
                                            endif;

                                            $period = $periods[$i];
                                            $cell = $group_timetable[$day][$period] ?? null;
                                            
                                            // LOGIC TO FILL THE SECOND HOUR OF A 2-HOUR SESSION
                                            if (!$cell && $i > 0) {
                                                $prev_period = $periods[$i-1];
                                                $cell = $group_timetable[$day][$prev_period] ?? null;
                                                // Only stretch if it's not the lunch break boundary
                                                if ($i == 4) $cell = null; 
                                            }
                                    ?>
                                    <td class="px-2 py-3 border-r border-slate-100 last:border-0 h-24 align-top">
                                        <?php if ($cell): ?>
                                            <div class="h-full p-3 rounded-xl <?php echo $cell['type'] == 'P' ? 'bg-indigo-50 border border-indigo-100' : 'bg-emerald-50 border border-emerald-100'; ?> transition hover:shadow-md cursor-default group">
                                                <div class="font-bold <?php echo $cell['type'] == 'P' ? 'text-indigo-700' : 'text-emerald-700'; ?> text-sm mb-1 leading-tight">
                                                    <?php echo htmlspecialchars($cell['teacher']); ?>
                                                </div>
                                                <div class="text-xs text-slate-500 flex items-center gap-1 mb-1">
                                                    <i class="fas fa-book-open opacity-50"></i>
                                                    <?php echo htmlspecialchars($cell['subject']); ?>
                                                </div>
                                                <div class="flex justify-between items-center mt-2">
                                                    <div class="text-[10px] font-medium text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">
                                                        <?php echo htmlspecialchars($cell['room']); ?>
                                                    </div>
                                                    <div class="text-[10px] font-bold <?php echo $cell['type'] == 'P' ? 'text-indigo-400' : 'text-emerald-400'; ?>">
                                                        <?php echo $cell['type'] == 'P' ? 'LEC' : 'LAB'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="h-full flex items-center justify-center text-slate-200 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                                <span class="text-[10px] font-bold uppercase tracking-widest opacity-30">Gap Detected</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-8 text-center text-slate-400 text-xs no-print">
                    <p>Generated by ChronoGen AI • Genetic Algorithm Optimization • v2.5 (Advanced Edition)</p>
                </div>

            <?php endif; ?>
        </div>
    </main>
    <style>
        .vertical-text { writing-mode: vertical-rl; transform: rotate(180deg); }
    </style>
    <script>
        function exportToCSV() {
            const table = document.querySelector('table');
            if (!table) return;
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
            const downloadLink = document.createElement('a');
            downloadLink.download = 'timetable_' + '<?php echo htmlspecialchars($selected_group); ?>' + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        function shareTimetable() {
            const group = '<?php echo $selected_group; ?>';
            const shareData = {
                title: 'Timetable - ' + group,
                text: 'Check out the weekly timetable for ' + group + '.',
                url: window.location.href
            };

            if (navigator.share) {
                navigator.share(shareData).catch(err => console.log('Error sharing', err));
            } else {
                // Fallback: Copy to clipboard
                const dummy = document.createElement('input');
                document.body.appendChild(dummy);
                dummy.value = window.location.href;
                dummy.select();
                document.execCommand('copy');
                document.body.removeChild(dummy);
                alert('Link copied to clipboard! (Web Share not supported in this browser)');
            }
        }

        // Generate timetable using Python AI engine
        async function generateTimetable(button) {
            button.disabled = true;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner animate-spin"></i> <span>Generating...</span>';
            
            try {
                const response = await fetch('api_generate.php', { method: 'POST' });
                const data = await response.json();
                
                if (data.success) {
                    alert('Timetable generated successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }
            } catch (error) {
                alert('Network error: ' + error.message);
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
        }

        // Notification Permission Request
        if ("Notification" in window) {
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }
        }
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