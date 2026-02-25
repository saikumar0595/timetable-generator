<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// ... (Keep existing logic for calling Python API) ...
// 2. Prepare Data for Python Algorithm
$base_dir = realpath(__DIR__ . '/../timetable-generator');
if (!$base_dir) { die("Error: Could not find timetable-generator directory."); }

// Fetch Data Logic
if (DEMO_MODE) {
    $assignments = $_SESSION['assignments'] ?? [];
    $classrooms_data = $_SESSION['classrooms'] ?? [];
    $groups_data = $_SESSION['groups'] ?? [];
} else {
    $result = mysqli_query($conn, "SELECT t.name as t_name, s.name as s_name FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id JOIN subjects s ON ts.subject_id = s.id");
    $assignments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $classrooms_data = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM classrooms"), MYSQLI_ASSOC);
    $groups_data = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM groups"), MYSQLI_ASSOC);
}

// Format Classrooms for Python
$rooms_by_type = [];
foreach($classrooms_data as $cr) {
    $rooms_by_type[$cr['type']][] = $cr['name'];
}

// Generate JSON
$json_data = [
    "Casovi" => [],
    "Ucionice" => $rooms_by_type
];

if (!empty($assignments)) {
    foreach ($assignments as $a) {
        // Use the actual group assigned to this subject/teacher link
        $assigned_group = $a['g_name'] ?? (!empty($groups_data) ? $groups_data[0]['name'] : "Default Group");
        
        $json_data["Casovi"][] = [
            "Nastavnik" => $a['t_name'], "Predmet" => $a['s_name'], "Grupe" => [$assigned_group],
            "Tip" => "P", "Trajanje" => 2, "Ucionica" => "LectureHall"
        ];
        if (rand(0, 1)) {
            $json_data["Casovi"][] = [
                "Nastavnik" => $a['t_name'], "Predmet" => $a['s_name'], "Grupe" => [$assigned_group],
                "Tip" => "L", "Trajanje" => 1, "Ucionica" => "Lab"
            ];
        }
    }
}

// Selected Group for Filtering
$selected_group = $_GET['group'] ?? ($groups_data[0]['name'] ?? null);

// Write & Execute
$input_file = $base_dir . DIRECTORY_SEPARATOR . 'input.json';
file_put_contents($input_file, json_encode($json_data, JSON_PRETTY_PRINT));

$python_script = 'api.py';
$input_file_basename = 'input.json';
$descriptorspec = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];
$process = proc_open("python \"$python_script\" \"$input_file_basename\"", $descriptorspec, $pipes, $base_dir);

if (is_resource($process)) {
    $output = stream_get_contents($pipes[1]);
    $error_output = stream_get_contents($pipes[2]);
    fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
    proc_close($process);
} else { $output = ""; }

$data = json_decode($output, true);
$timetable = $data['schedule'] ?? [];
$stats = $data['statistics'] ?? [];

if (empty($timetable)) {
    // Graceful error handling
    if (!empty($error_output)) {
        $error = "Python script failed. Output: <pre>" . htmlspecialchars($error_output) . "</pre>";
    } else {
        $error = "Python script returned empty or invalid data. Ensure sufficient assignments exist.";
    }
    $timetable = []; 
} else {
    // Save to session for Notification Service
    $_SESSION['last_generated_timetable'] = $timetable;
}

$periods = [
    "09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50",
    "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"
];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Timetable | Audisankara University</title>
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

    <!-- Sidebar (Same as Dashboard) -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20 no-print">
        <div class="h-20 flex items-center px-8 border-b border-slate-800">
            <!-- Mini ASCET Logo -->
            <div class="w-8 h-8 mr-3 filter drop-shadow-md">
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
            <a href="teacher_directory.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-id-badge w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Faculty Directory</span>
            </a>
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
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
            <a href="view_timetable.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl transition-all duration-200 border border-indigo-500/20 shadow-sm">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
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
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm no-print">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">AI Schedule Generator</h2>
            <div class="flex items-center gap-4">
                <div class="filters flex items-center gap-2 mr-4">
                    <label class="text-xs font-bold text-slate-400 uppercase">View For:</label>
                    <select onchange="window.location.href='?group='+this.value" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <?php foreach($groups_data as $g): ?>
                            <option value="<?php echo htmlspecialchars($g['name']); ?>" <?php echo $selected_group == $g['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button onclick="shareTimetable()" class="flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition font-medium text-sm shadow-lg shadow-emerald-500/30">
                    <i class="fas fa-share-alt"></i> Share
                </button>
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium text-sm shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-file-pdf"></i> Save PDF
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            
            <?php if (empty($timetable)): ?>
                <div class="max-w-4xl mx-auto text-center mt-20">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl animate-pulse">
                        <i class="fas fa-cog fa-spin"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Generating Schedule...</h3>
                    <p class="text-slate-500">If this takes too long, ensure you have assigned subjects to teachers and created classrooms.</p>
                </div>
            <?php else: ?>
            
                <!-- Technical Performance Report -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 no-print">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Hard Constraints</p>
                        <h4 class="text-xl font-bold text-emerald-600"><?php echo $stats['hard_constraints']; ?>%</h4>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?php echo $stats['hard_constraints']; ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Soft Constraints</p>
                        <h4 class="text-xl font-bold text-indigo-600"><?php echo $stats['soft_constraints']; ?>%</h4>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?php echo $stats['soft_constraints']; ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Group Idle</p>
                        <h4 class="text-xl font-bold text-slate-800"><?php echo $stats['avg_idle_groups']; ?></h4>
                        <p class="text-[10px] text-slate-400 mt-1">Total: <?php echo $stats['total_idle_groups']; ?> slots</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Free Hour</p>
                        <h4 class="text-xl font-bold <?php echo $stats['free_hour_exists'] == 'Yes' ? 'text-emerald-600' : 'text-rose-500'; ?>">
                            <?php echo $stats['free_hour_exists']; ?>
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
                                            $all_classes = $timetable[$day][$period] ?? [];
                                            
                                            // Filter for selected group
                                            $cell = null;
                                            foreach($all_classes as $cls) {
                                                if (in_array($selected_group, $cls['groups'])) {
                                                    $cell = $cls;
                                                    break;
                                                }
                                            }
                                    ?>
                                    <td class="px-2 py-3 border-r border-slate-100 last:border-0 h-24 align-top">
                                        <?php if ($cell): ?>
                                            <div class="h-full p-3 rounded-xl <?php echo $cell['type'] == 'P' ? 'bg-indigo-50 border border-indigo-100' : 'bg-emerald-50 border border-emerald-100'; ?> transition hover:shadow-md cursor-default group">
                                                <div class="font-bold <?php echo $cell['type'] == 'P' ? 'text-indigo-700' : 'text-emerald-700'; ?> text-sm mb-1 leading-tight">
                                                    <?php echo htmlspecialchars($cell['subject']); ?>
                                                </div>
                                                <div class="text-xs text-slate-500 flex items-center gap-1 mb-1">
                                                    <i class="fas fa-user-circle opacity-50"></i>
                                                    <?php echo htmlspecialchars($cell['teacher']); ?>
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
                                            <div class="h-full flex items-center justify-center text-slate-200">
                                                <i class="fas fa-minus text-xs opacity-20"></i>
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

        // Notification Permission Request
        if ("Notification" in window) {
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }
        }
    </script>
</body>
</html>