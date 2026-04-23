<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$teachers = $_SESSION['teachers'] ?? [];
$subjects = $_SESSION['subjects'] ?? [];
$classrooms = $_SESSION['classrooms'] ?? [];
$groups = $_SESSION['groups'] ?? [];
$assignments = $_SESSION['assignments'] ?? [];
$timetable = $_SESSION['last_generated_timetable'] ?? [];

// R20 CSE Topics Mapping (Reused from view_timetable)
$subject_topics = [
    'Linear Algebra and Calculus' => ['Matrices & Determinants', 'Eigenvalues', 'Vector Calculus'],
    'Applied Physics' => ['Quantum Mechanics', 'Lasers', 'Fiber Optics'],
    'Communicative English' => ['Vocabulary Building', 'Reading Comprehension', 'Business Letters'],
    'Problem Solving and Programming' => ['Control Structures', 'Arrays & Strings', 'Pointers in C'],
    'Data Structures' => ['Linked Lists', 'Stacks & Queues', 'Binary Trees'],
    'Chemistry' => ['Electrochemistry', 'Polymers', 'Nanomaterials'],
    'Discrete Mathematics and Graph Theory' => ['Propositional Logic', 'Graph Coloring', 'Combinatorics'],
    'Digital Electronics' => ['Boolean Algebra', 'Logic Gates', 'Flip-Flops'],
    'Python Programming' => ['Lists & Dictionaries', 'Functions', 'OOP in Python'],
    'Object Oriented Programming through Java' => ['Inheritance', 'Polymorphism', 'Exception Handling'],
    'Computer Organization' => ['Instruction Set', 'Memory Hierarchy', 'Pipelining'],
    'Database Management Systems' => ['ER Models', 'SQL Queries', 'Normalization'],
    'Operating Systems' => ['Process Scheduling', 'Deadlocks', 'Memory Management'],
    'Software Engineering' => ['SDLC Models', 'Agile Methodology', 'Testing Strategies'],
    'Computer Networks' => ['OSI Model', 'TCP/IP Protocol', 'Routing Algorithms'],
    'Formal Languages and Automata Theory' => ['Finite Automata', 'Context-Free Grammars', 'Turing Machines'],
    'Design and Analysis of Algorithms' => ['Divide & Conquer', 'Dynamic Programming', 'Greedy Method'],
    'Artificial Intelligence' => ['Search Algorithms', 'Knowledge Representation', 'Expert Systems'],
    'Data Warehousing and Data Mining' => ['Data Preprocessing', 'Association Rules', 'Clustering'],
    'Web Technologies' => ['HTML5 & CSS3', 'JavaScript DOM', 'PHP & MySQL'],
    'Compiler Design' => ['Lexical Analysis', 'Syntax Analysis', 'Code Generation'],
    'Cryptography and Network Security' => ['RSA Algorithm', 'AES Encryption', 'Digital Signatures'],
    'Cloud Computing' => ['IaaS, PaaS, SaaS', 'Virtualization', 'AWS Services'],
    'Big Data Analytics' => ['Hadoop Architecture', 'MapReduce', 'Spark'],
    'Machine Learning' => ['Supervised Learning', 'Neural Networks', 'Decision Trees'],
    'Internet of Things' => ['IoT Architecture', 'Sensors & Actuators', 'Arduino/Raspberry Pi']
];

function get_topic($subject_name) {
    global $subject_topics;
    if (isset($subject_topics[$subject_name])) {
        $topics = $subject_topics[$subject_name];
        return $topics[array_rand($topics)]; 
    }
    return "General Revision";
}

// Logic to determine current period
$periods_time = [
    "09:30 - 10:20" => "09:30:00-10:20:00", "10:20 - 11:10" => "10:20:00-11:10:00",
    "11:10 - 12:00" => "11:10:00-12:00:00", "12:00 - 12:50" => "12:00:00-12:50:00",
    "01:30 - 02:15" => "13:30:00-14:15:00", "02:15 - 03:00" => "14:15:00-15:00:00",
    "03:00 - 03:45" => "15:00:00-15:45:00", "03:45 - 04:30" => "15:45:00-16:30:00"
];

$current_time = date("H:i:s");
$current_day = date("l");
$current_period_name = null;

foreach($periods_time as $name => $range) {
    list($start, $end) = explode("-", $range);
    if ($current_time >= $start && $current_time <= $end) {
        $current_period_name = $name;
        break;
    }
}

// SIMULATION: If after hours, pick a random period for demo purposes
if (!$current_period_name) {
    $current_period_name = array_keys($periods_time)[rand(0, 7)];
    $current_day = "Monday";
}

$active_classes = $timetable[$current_day][$current_period_name] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legacy Admin Mainframe | ChronoGen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'JetBrains Mono', monospace; background-color: #0f172a; color: #38bdf8; }
        .crt-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            background-size: 100% 2px, 3px 100%;
            pointer-events: none; z-index: 50; opacity: 0.2;
        }
        .scanline {
            width: 100%; height: 100px; z-index: 60; position: absolute; pointer-events: none;
            background: linear-gradient(0deg, rgba(0,0,0,0) 0%, rgba(56,189,248,0.15) 50%, rgba(0,0,0,0) 100%);
            opacity: 0.1; animation: scanline 10s linear infinite;
        }
        @keyframes scanline { 0% { top: -100px; } 100% { top: 100%; } }
        .data-table th { background-color: #1e293b; color: #7dd3fc; border: 1px solid #334155; padding: 6px; }
        .data-table td { border: 1px solid #1e293b; padding: 6px 10px; font-size: 0.75rem; }
        .data-table tr:hover { background-color: #0f2942; }
        .active-row { background-color: #0c4a6e !important; border-left: 4px solid #38bdf8 !important; }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col relative">
    <div class="crt-overlay"></div>
    <div class="scanline"></div>

    <!-- Header -->
    <header class="bg-slate-900 border-b-2 border-sky-900 p-2 flex justify-between items-center z-10 shadow-[0_0_15px_rgba(14,165,233,0.3)]">
        <div class="flex items-center gap-4">
            <i class="fas fa-server text-sky-400 text-2xl animate-pulse"></i>
            <div>
                <h1 class="text-sky-400 font-bold text-lg tracking-widest leading-tight">SYS.ADMIN // CHRONOGEN V3.5</h1>
                <p class="text-xs text-sky-700 uppercase">Live Master Control Grid</p>
            </div>
        </div>
        <div class="flex flex-col items-end text-[10px] font-bold text-sky-500">
            <div class="flex gap-2">
                <span class="px-2 py-0.5 bg-slate-800 border border-sky-900">DAY: <?= strtoupper($current_day) ?></span>
                <span class="px-2 py-0.5 bg-slate-800 border border-sky-900 animate-pulse text-emerald-400">CLOCK: <?= date("H:i:s") ?></span>
            </div>
            <span class="mt-1 text-sky-700">PERIOD: <?= $current_period_name ?></span>
        </div>
    </header>

    <!-- Main Grid -->
    <main class="flex-1 p-2 flex flex-col gap-2 overflow-hidden z-10">
        
        <!-- Top Section: LIVE MONITOR -->
        <div class="h-1/2 bg-slate-950 border-2 border-emerald-900/50 flex flex-col overflow-hidden relative shadow-[inset_0_0_20px_rgba(16,185,129,0.1)]">
            <div class="bg-emerald-900/20 text-emerald-400 text-xs font-bold p-2 uppercase border-b border-emerald-900/50 flex justify-between items-center">
                <span><i class="fas fa-satellite-dish mr-2 animate-ping"></i> LIVE MONITOR: ACTIVE_SESSIONS (<?= count($active_classes) ?> DETECTED)</span>
                <span class="text-[10px] bg-emerald-950 px-2 py-0.5 border border-emerald-500/30">SYSTEM_AUTO_REFRESH: ACTIVE</span>
            </div>
            <div class="overflow-y-auto flex-1 data-table custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-20">
                        <tr>
                            <th>ROOM_ID</th>
                            <th>SEC_NAME</th>
                            <th>FACULTY_NAME</th>
                            <th>SUBJECT_TITLE</th>
                            <th>CURRENT_TOPIC_MODULE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($active_classes)): ?>
                            <tr><td colspan="6" class="text-center py-10 text-sky-900">NO ACTIVE SESSIONS DETECTED IN THIS PERIOD</td></tr>
                        <?php else: ?>
                            <?php foreach($active_classes as $cls): ?>
                            <tr class="active-row group">
                                <td class="font-bold text-sky-100"><?= htmlspecialchars($cls['room']) ?></td>
                                <td class="text-amber-400"><?= htmlspecialchars(implode(", ", $cls['groups'])) ?></td>
                                <td class="text-emerald-400 font-bold"><?= htmlspecialchars($cls['teacher']) ?></td>
                                <td class="text-sky-300"><?= htmlspecialchars($cls['subject']) ?></td>
                                <td class="italic text-sky-500 underline decoration-sky-900/50 underline-offset-4">
                                    <?= get_topic($cls['subject']) ?>
                                </td>
                                <td><span class="px-1 bg-emerald-500 text-slate-900 font-bold text-[9px] animate-pulse">IN_PROGRESS</span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Section: Static Data Grid -->
        <div class="flex-1 flex gap-2 overflow-hidden">
            <!-- Faculty Micro Grid -->
            <div class="w-1/4 bg-slate-900 border border-sky-900 flex flex-col overflow-hidden">
                <div class="bg-slate-800 text-sky-500 text-xs font-bold p-1 uppercase border-b border-sky-900">DB: FACULTY_REGISTRY</div>
                <div class="overflow-y-auto flex-1 data-table">
                    <table class="w-full">
                        <tbody>
                            <?php foreach(array_slice($teachers, 0, 50) as $t): ?>
                            <tr><td class="text-sky-700"><?= $t['id'] ?></td><td class="truncate max-w-[150px]"><?= htmlspecialchars($t['name']) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Master Assignment Matrix -->
            <div class="flex-1 bg-slate-900 border border-sky-900 flex flex-col overflow-hidden">
                <div class="bg-slate-800 text-sky-500 text-xs font-bold p-1 uppercase border-b border-sky-900 flex justify-between">
                    <span>DB: MASTER_ASSIGNMENT_MATRIX</span>
                    <a href="index.php" class="text-[10px] text-sky-100 bg-sky-900 px-2 hover:bg-sky-700 underline">RETURN_TO_UI -></a>
                </div>
                <div class="overflow-y-auto flex-1 data-table">
                    <table class="w-full">
                        <thead><tr><th>A_ID</th><th>NAME</th><th>COURSE</th><th>GROUP</th></tr></thead>
                        <tbody>
                            <?php foreach(array_slice($assignments, 0, 100) as $a): ?>
                            <tr>
                                <td class="text-sky-800">0x<?= dechex($a['id']) ?></td>
                                <td><?= htmlspecialchars($a['t_name']) ?></td>
                                <td class="text-emerald-600"><?= htmlspecialchars($a['s_name']) ?></td>
                                <td class="text-amber-600"><?= htmlspecialchars($a['g_name']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Chatbot Injection Script -->
    <script src="assets/js/chatbot.js"></script>
    <script>
        // Automatic Refresh for Live Monitor
        setInterval(() => {
            // Only refresh if tab is active to save resources
            if(!document.hidden) location.reload();
        }, 60000); // Refresh every 1 minute
    </script>
</body>
</html>