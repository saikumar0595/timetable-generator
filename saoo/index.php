<?php
session_start();
include('db.php');

// Security Check
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Initialize Demo Data (Same logic as before)
if (DEMO_MODE && !isset($_SESSION['teachers'])) {
    $_SESSION['teachers'] = [
        [
            'id' => 1, 
            'name' => 'Prof. K. Suresh', 
            'email' => 'hod.cse@audisankara.ac.in',
            'role' => 'HOD - CSE',
            'qualification' => 'Ph.D in Computer Science',
            'phone' => '+91 98765 43210',
            'experience' => '18 Years',
            'photo' => 'https://ui-avatars.com/api/?name=K+Suresh&background=4f46e5&color=fff'
        ],
        [
            'id' => 2, 
            'name' => 'Dr. P. Ramesh', 
            'email' => 'ramesh.ece@audisankara.ac.in',
            'role' => 'Associate Professor',
            'qualification' => 'Ph.D in VLSI Design',
            'phone' => '+91 98765 43211',
            'experience' => '12 Years',
            'photo' => 'https://ui-avatars.com/api/?name=P+Ramesh&background=0ea5e9&color=fff'
        ],
        [
            'id' => 3, 
            'name' => 'Prof. M. Lakshmi', 
            'email' => 'lakshmi.eee@audisankara.ac.in',
            'role' => 'Professor',
            'qualification' => 'M.Tech, Ph.D (Power Systems)',
            'phone' => '+91 98765 43212',
            'experience' => '15 Years',
            'photo' => 'https://ui-avatars.com/api/?name=M+Lakshmi&background=ec4899&color=fff'
        ],
        [
            'id' => 4, 
            'name' => 'Dr. G. Murali', 
            'email' => 'murali.cse@audisankara.ac.in',
            'role' => 'Professor',
            'qualification' => 'Ph.D in Cyber Security',
            'phone' => '+91 98765 43213',
            'experience' => '20 Years',
            'photo' => 'https://ui-avatars.com/api/?name=G+Murali&background=10b981&color=fff'
        ],
        [
            'id' => 5, 
            'name' => 'Dr. B. Venkat', 
            'email' => 'venkat.ai@audisankara.ac.in',
            'role' => 'Director (AI)',
            'qualification' => 'Ph.D in Machine Learning',
            'phone' => '+91 98765 43214',
            'experience' => '10 Years',
            'photo' => 'https://ui-avatars.com/api/?name=B+Venkat&background=f59e0b&color=fff'
        ]
    ];
    $_SESSION['subjects'] = [
        ['id' => 1, 'name' => 'Python Programming'],
        ['id' => 2, 'name' => 'Data Structures & Algorithms'],
        ['id' => 3, 'name' => 'Artificial Intelligence'],
        ['id' => 4, 'name' => 'Engineering Mathematics I'],
        ['id' => 5, 'name' => 'Cyber Security'],
        ['id' => 6, 'name' => 'Machine Learning']
    ];
    $_SESSION['classrooms'] = [
        ['id' => 1, 'name' => 'LH-101 (Main Block)', 'type' => 'LectureHall'],
        ['id' => 2, 'name' => 'LH-102 (Main Block)', 'type' => 'LectureHall'],
        ['id' => 3, 'name' => 'LH-103 (Main Block)', 'type' => 'LectureHall'],
        ['id' => 4, 'name' => 'Computer Lab-1', 'type' => 'Lab'],
        ['id' => 5, 'name' => 'AI Innovation Lab', 'type' => 'Lab']
    ];
    $_SESSION['groups'] = [
        ['id' => 1, 'name' => 'B.Tech CSE-A (Year 1)'],
        ['id' => 2, 'name' => 'B.Tech CSE-B (Year 1)'],
        ['id' => 3, 'name' => 'B.Tech AI-DS (Year 1)'],
        ['id' => 4, 'name' => 'B.Tech IT (Year 1)']
    ];
    $_SESSION['assignments'] = [
        ['id' => 1, 't_id' => 1, 't_name' => 'Prof. K. Suresh', 's_name' => 'Data Structures & Algorithms', 'g_name' => 'B.Tech CSE-A (Year 1)'],
        ['id' => 2, 't_id' => 5, 't_name' => 'Dr. B. Venkat', 's_name' => 'Artificial Intelligence', 'g_name' => 'B.Tech AI-DS (Year 1)'],
        ['id' => 3, 't_id' => 2, 't_name' => 'Dr. P. Ramesh', 's_name' => 'Engineering Mathematics I', 'g_name' => 'B.Tech CSE-B (Year 1)'],
        ['id' => 4, 't_id' => 4, 't_name' => 'Dr. G. Murali', 's_name' => 'Cyber Security', 'g_name' => 'B.Tech IT (Year 1)'],
        ['id' => 5, 't_id' => 1, 't_name' => 'Prof. K. Suresh', 's_name' => 'Machine Learning', 'g_name' => 'B.Tech CSE-A (Year 1)']
    ];
}

// Get Counts & Chart Data
if (DEMO_MODE) {
    $teacher_count = count($_SESSION['teachers']);
    $subject_count = count($_SESSION['subjects']);
    $assign_count  = count($_SESSION['assignments']);
    $workload = [];
    foreach ($_SESSION['teachers'] as $t) $workload[$t['name']] = 0;
    foreach ($_SESSION['assignments'] as $a) if (isset($workload[$a['t_name']])) $workload[$a['t_name']] += 2;
} else {
    $teacher_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM teachers"))[0];
    $subject_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM subjects"))[0];
    $assign_count  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_subjects"))[0];
    $workload = [];
    $teachers_res = mysqli_query($conn, "SELECT * FROM teachers");
    while($t = mysqli_fetch_assoc($teachers_res)) $workload[$t['name']] = 0;
    $assign_res = mysqli_query($conn, "SELECT t.name, COUNT(*) as count FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id GROUP BY t.name");
    while($row = mysqli_fetch_assoc($assign_res)) $workload[$row['name']] = $row['count'] * 2;
}

$chart_labels = json_encode(array_keys($workload));
$chart_data = json_encode(array_values($workload));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Audisankara University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20 transition-all duration-300">
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
            
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl transition-all duration-200 border border-indigo-500/20 shadow-sm">
                <i class="fas fa-th-large w-5 text-center"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="teacher_directory.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-id-badge w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
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

            <a href="gentelella/production/index.html" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-desktop w-5 text-center group-hover:text-blue-400 transition-colors"></i>
                <span class="font-medium">Legacy Admin View</span>
            </a>

            <div class="mt-8 border-t border-slate-800 pt-6">
                <div class="px-4 py-3 mb-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Teacher Alerts</span>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-tight mb-3">WhatsApp & Email notifications active for all periods.</p>
                    <button onclick="checkNotifications()" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp"></i> Sync Alerts
                    </button>
                </div>

                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all duration-200 group">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="font-medium">Sign Out</span>
                </a>
            </div>
        </nav>

        <?php if (DEMO_MODE): ?>
        <div class="p-4 bg-slate-800/50 border-t border-slate-800">
            <div class="flex items-center gap-3 text-xs text-amber-400">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="font-medium">Demo Mode Active</span>
            </div>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 relative">
        
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="absolute top-24 left-1/2 transform -translate-x-1/2 z-50 animate-bounce">
            <div class="bg-emerald-500 text-white px-6 py-3 rounded-full shadow-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-bold"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Overview</h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
                    <p class="text-xs text-slate-400">Administrator</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                    <?php echo substr($_SESSION['user'], 0, 1); ?>
                </div>
            </div>
        </header>

        <!-- Scrolling Header Bar -->
        <div class="w-full bg-slate-900 overflow-hidden h-12 flex items-center border-b border-slate-800">
            <div class="whitespace-nowrap animate-scroll flex items-center">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
            </div>
        </div>

        <style>
            @keyframes scroll {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .animate-scroll {
                display: flex;
                width: max-content;
                animation: scroll 20s linear infinite;
            }
        </style>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-8 pb-20">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Teachers -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-300"></div>
                    <div class="relative z-10">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Faculty Members</p>
                        <h3 class="text-4xl font-bold text-slate-800"><?php echo $teacher_count; ?></h3>
                        <div class="mt-4 flex items-center text-indigo-500 text-sm font-medium">
                            <span class="bg-indigo-50 px-2 py-1 rounded-md">Total Count</span>
                        </div>
                    </div>
                </div>

                <!-- Subjects -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-pink-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-300"></div>
                    <div class="relative z-10">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Active Subjects</p>
                        <h3 class="text-4xl font-bold text-slate-800"><?php echo $subject_count; ?></h3>
                        <div class="mt-4 flex items-center text-pink-500 text-sm font-medium">
                            <span class="bg-pink-50 px-2 py-1 rounded-md">Curriculum</span>
                        </div>
                    </div>
                </div>

                <!-- Assignments -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-300"></div>
                    <div class="relative z-10">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Allocations</p>
                        <h3 class="text-4xl font-bold text-slate-800"><?php echo $assign_count; ?></h3>
                        <div class="mt-4 flex items-center text-emerald-500 text-sm font-medium">
                            <span class="bg-emerald-50 px-2 py-1 rounded-md">Linked Courses</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Chart Section -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-96 flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-slate-700 text-lg">Workload Analytics</h3>
                        <select class="text-xs border-none bg-slate-50 rounded-lg px-3 py-1 text-slate-500 font-medium focus:ring-0 cursor-pointer hover:bg-slate-100 transition">
                            <option>This Semester</option>
                        </select>
                    </div>
                    <div class="flex-1 relative w-full h-full">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-700 text-lg mb-4 px-1">Live Notifications</h3>
                    
                    <div id="notificationFeed" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 min-h-[150px] flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Recent Alerts</span>
                            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-md text-slate-500 font-mono"><?php echo date('H:i'); ?></span>
                        </div>
                        <div id="notificationList" class="space-y-3 flex-1 overflow-y-auto max-h-[200px]">
                            <div class="text-center py-6">
                                <i class="fas fa-bell-slash text-slate-200 text-2xl mb-2"></i>
                                <p class="text-xs text-slate-400">No alerts triggered yet today.</p>
                            </div>
                        </div>
                    </div>

                    <h3 class="font-bold text-slate-700 text-lg mb-4 px-1 mt-6">Quick Actions</h3>
                    
                    <a href="manage_teachers.php" class="flex items-center p-4 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-indigo-100 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Add Teacher</h4>
                            <p class="text-xs text-slate-400">Expand your faculty list</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-300 group-hover:text-indigo-400 transition-colors"></i>
                    </a>

                    <a href="manage_subjects.php" class="flex items-center p-4 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-pink-100 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl group-hover:bg-pink-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                            <i class="fas fa-link"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold text-slate-800 group-hover:text-pink-600 transition-colors">Assign Subject</h4>
                            <p class="text-xs text-slate-400">Link courses to teachers</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-300 group-hover:text-pink-400 transition-colors"></i>
                    </a>

                    <a href="view_timetable.php" class="flex items-center p-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg shadow-indigo-500/30 text-white hover:shadow-xl hover:-translate-y-1 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl backdrop-blur-sm">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div class="ml-4">
                                                    <h4 class="font-bold">Generate Schedule</h4>
                                                    <p class="text-xs text-indigo-100/80">Run AI Algorithm</p>
                                                </div>
                                                <i class="fas fa-arrow-right ml-auto text-white/50 group-hover:text-white transition-colors"></i>
                                            </a>
                            
                                            <a href="populate_demo.php" onclick="return confirm('⚠️ Warning: This will overwrite your current data with University Demo Data.\n\nContinue?');" class="flex items-center p-4 bg-amber-50 rounded-2xl shadow-sm border border-amber-100 hover:shadow-md hover:border-amber-200 transition-all group mt-6">
                                                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                                                    <i class="fas fa-database"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="font-bold text-slate-800 group-hover:text-amber-600 transition-colors">Reset Data</h4>
                                                    <p class="text-xs text-slate-400">Load University Dataset</p>
                                                </div>
                                                <i class="fas fa-sync-alt ml-auto text-amber-300 group-hover:text-amber-500 transition-colors"></i>
                                            </a>                </div>
            </div>
        </div>
    </main>

    <script>
        async function checkNotifications() {
            const list = document.getElementById('notificationList');
            list.innerHTML = '<div class="text-center py-6"><i class="fas fa-spinner fa-spin text-indigo-500"></i><p class="text-xs text-slate-400 mt-2">Checking schedule...</p></div>';
            
            try {
                const response = await fetch('notification_service.php');
                const data = await response.json();
                
                if (data.status === 'success' && data.upcoming_alerts.length > 0) {
                    list.innerHTML = '';
                    data.upcoming_alerts.forEach(alert => {
                        list.innerHTML += `
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 animate-fadeIn">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fab fa-whatsapp text-emerald-500 text-xs"></i>
                                    <span class="text-[10px] font-bold text-emerald-700">${alert.teacher}</span>
                                </div>
                                <p class="text-[10px] text-slate-600 leading-tight">${alert.message}</p>
                            </div>
                        `;
                    });
                } else {
                    list.innerHTML = `
                        <div class="text-center py-6">
                            <i class="fas fa-check-circle text-emerald-300 text-2xl mb-2"></i>
                            <p class="text-xs text-slate-400">All teachers notified for current period.</p>
                        </div>
                    `;
                }
            } catch (error) {
                list.innerHTML = '<div class="text-center py-6 text-red-400 text-xs font-medium">Sync failed. Generate timetable first.</div>';
            }
        }

        const ctx = document.getElementById('workloadChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $chart_labels; ?>,
                datasets: [{
                    label: 'Assigned Hours',
                    data: <?php echo $chart_data; ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)', // Indigo-600
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [2, 4], color: '#f1f5f9' },
                        ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Inter', sans-serif", size: 11, weight: '600' }, color: '#64748b' }
                    }
                },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8 } }
            }
        });
    </script>
</body>
</html>