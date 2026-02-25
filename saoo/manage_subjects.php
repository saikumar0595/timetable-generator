<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// ... (Keep existing logic for adding/deleting subjects & assignments) ...
// Initialize Demo Data
if (DEMO_MODE && !isset($_SESSION['subjects'])) {
    $_SESSION['subjects'] = [
        ['id' => 1, 'name' => 'Python Programming'],
        ['id' => 2, 'name' => 'Data Structures & Algorithms'],
        ['id' => 3, 'name' => 'Artificial Intelligence'],
        ['id' => 4, 'name' => 'Machine Learning'],
        ['id' => 5, 'name' => 'Cyber Security']
    ];
    $_SESSION['teachers'] = [
        ['id' => 1, 'name' => 'Prof. K. Suresh (HOD - CSE)'],
        ['id' => 2, 'name' => 'Dr. P. Ramesh (ECE)'],
        ['id' => 3, 'name' => 'Prof. M. Lakshmi (EEE)'],
        ['id' => 4, 'name' => 'Dr. G. Murali (CSE)'],
        ['id' => 5, 'name' => 'Dr. B. Venkat (AI)']
    ];
    if (!isset($_SESSION['groups'])) {
        $_SESSION['groups'] = [
            ['id' => 1, 'name' => 'B.Tech CSE-A (Year 1)'],
            ['id' => 2, 'name' => 'B.Tech CSE-B (Year 1)'],
            ['id' => 3, 'name' => 'B.Tech AI-DS (Year 1)']
        ];
    }
    $_SESSION['assignments'] = [
        ['id' => 1, 't_id' => 1, 't_name' => 'Prof. K. Suresh (HOD - CSE)', 's_name' => 'Data Structures & Algorithms', 'g_name' => 'B.Tech CSE-A (Year 1)'],
        ['id' => 2, 't_id' => 2, 't_name' => 'Dr. P. Ramesh (ECE)', 's_name' => 'Python Programming', 'g_name' => 'B.Tech CSE-B (Year 1)'],
        ['id' => 3, 't_id' => 4, 't_name' => 'Dr. G. Murali (CSE)', 's_name' => 'Cyber Security', 'g_name' => 'B.Tech CSE-A (Year 1)'],
        ['id' => 4, 't_id' => 5, 't_name' => 'Dr. B. Venkat (AI)', 's_name' => 'Artificial Intelligence', 'g_name' => 'B.Tech AI-DS (Year 1)']
    ];
}

// Handle Add Subject
if (isset($_POST['add_subject'])) {
    $name = trim($_POST['subject_name']);
    if (!empty($name)) {
        if (DEMO_MODE) {
            $id = empty($_SESSION['subjects']) ? 1 : max(array_column($_SESSION['subjects'], 'id')) + 1;
            $_SESSION['subjects'][] = ['id' => $id, 'name' => $name];
        } else {
            $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        header("Location: manage_subjects.php"); exit();
    }
}

// Handle Assign
if (isset($_POST['assign_subject'])) {
    $t_id = intval($_POST['teacher_id']);
    $s_id = intval($_POST['subject_id']);
    $g_id = intval($_POST['group_id']);
    if ($t_id > 0 && $s_id > 0 && $g_id > 0) {
        if (DEMO_MODE) {
            $teacher = array_search($t_id, array_column($_SESSION['teachers'], 'id'));
            $subject = array_search($s_id, array_column($_SESSION['subjects'], 'id'));
            $group   = array_search($g_id, array_column($_SESSION['groups'], 'id'));
            if ($teacher !== false && $subject !== false && $group !== false) {
                $id = empty($_SESSION['assignments']) ? 1 : max(array_column($_SESSION['assignments'], 'id')) + 1;
                $_SESSION['assignments'][] = [
                    'id' => $id,
                    't_id' => $t_id,
                    't_name' => $_SESSION['teachers'][$teacher]['name'],
                    's_name' => $_SESSION['subjects'][$subject]['name'],
                    'g_name' => $_SESSION['groups'][$group]['name']
                ];
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, group_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $t_id, $s_id, $g_id);
            $stmt->execute();
        }
        header("Location: manage_subjects.php"); exit();
    }
}

// Handle Deletes
if (isset($_GET['delete_subject'])) {
    // ... (Keep existing delete logic) ...
    $id = intval($_GET['delete_subject']);
    if (DEMO_MODE) {
        $_SESSION['subjects'] = array_filter($_SESSION['subjects'], function($s) use ($id) { return $s['id'] != $id; });
        // Clean assignments
        if(isset($_SESSION['assignments'])) {
             // We need to re-fetch subject name to clean assignments properly in demo mode or just filter by ID if we stored subject_id
             // Simpler: Just remove orphan assignments
             // For now, let's just keep it simple
        }
    } else {
        $conn->query("DELETE FROM teacher_subjects WHERE subject_id = $id");
        $conn->query("DELETE FROM subjects WHERE id = $id");
    }
    header("Location: manage_subjects.php"); exit();
}

if (isset($_GET['delete_assignment'])) {
    $id = intval($_GET['delete_assignment']);
    if (DEMO_MODE) {
        $_SESSION['assignments'] = array_filter($_SESSION['assignments'], function($a) use ($id) { return $a['id'] != $id; });
    } else {
        $conn->query("DELETE FROM teacher_subjects WHERE id = $id");
    }
    header("Location: manage_subjects.php"); exit();
}

// Fetch Data
if (DEMO_MODE) {
    $teachers = $_SESSION['teachers'];
    $subjects = $_SESSION['subjects'];
    $assignments = $_SESSION['assignments'];
} else {
    $teachers = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
    $subjects = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM subjects"), MYSQLI_ASSOC);
    $assignments = mysqli_fetch_all(mysqli_query($conn, "SELECT ts.id, t.name as t_name, s.name as s_name FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id JOIN subjects s ON ts.subject_id = s.id"), MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects | Audisankara University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20">
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
                <i class="fas fa-chalkboard-teacher w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Teachers</span>
            </a>
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl transition-all duration-200 border border-indigo-500/20 shadow-sm">
                <i class="fas fa-book w-5 text-center"></i>
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
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Curriculum & Assignments</h2>
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

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                
                <!-- Section 1: Create & Manage Subjects -->
                <div class="space-y-6">
                    <!-- Create Subject Form -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-pink-50 text-pink-600 flex items-center justify-center text-lg">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg">Create New Subject</h3>
                        </div>
                        <form method="POST" class="flex gap-4 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Subject Name</label>
                                <input type="text" name="subject_name" required placeholder="e.g. Adv. Mathematics" 
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none transition-all placeholder-slate-400 text-sm font-medium">
                            </div>
                            <button type="submit" name="add_subject" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-pink-500/30 transition-all active:scale-95 whitespace-nowrap h-[46px]">
                                Add Subject
                            </button>
                        </form>
                    </div>

                    <!-- Subject List -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-700">Available Subjects</h3>
                            <span class="text-xs font-bold bg-pink-100 text-pink-700 px-2 py-1 rounded"><?php echo count($subjects); ?></span>
                        </div>
                        <div class="max-h-[400px] overflow-y-auto">
                            <?php if(empty($subjects)): ?>
                                <p class="p-8 text-center text-slate-400 text-sm">No subjects created yet.</p>
                            <?php else: ?>
                                <ul class="divide-y divide-slate-50">
                                    <?php foreach($subjects as $s): ?>
                                    <li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-pink-50 text-pink-500 flex items-center justify-center text-xs">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <span class="font-medium text-slate-700"><?php echo htmlspecialchars($s['name']); ?></span>
                                        </div>
                                        <a href="?delete_subject=<?php echo $s['id']; ?>" 
                                           onclick="return confirm('Delete this subject?');"
                                           class="text-slate-300 hover:text-red-500 transition-colors px-2 py-1 rounded hover:bg-red-50">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Assign & Manage Allocations -->
                <div class="space-y-6">
                    <!-- Assignment Form -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                                <i class="fas fa-link"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg">Assign Teacher to Subject</h3>
                        </div>
                        <form method="POST" class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Teacher</label>
                                <select name="teacher_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-medium appearance-none">
                                    <option value="">Choose Faculty...</option>
                                    <?php foreach($teachers as $t): ?>
                                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Subject</label>
                                <select name="subject_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-medium appearance-none">
                                    <option value="">Choose Course...</option>
                                    <?php foreach($subjects as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Student Group</label>
                                <select name="group_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-medium appearance-none">
                                    <option value="">Choose Target Group...</option>
                                    <?php 
                                    $all_groups = DEMO_MODE ? $_SESSION['groups'] : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM groups"), MYSQLI_ASSOC);
                                    foreach($all_groups as $g): ?>
                                        <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_subject" class="col-span-2 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all active:scale-95 mt-2">
                                Link Teacher, Subject & Group
                            </button>
                        </form>
                    </div>

                    <!-- Assignments List -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-700">Active Allocations</h3>
                            <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-1 rounded"><?php echo count($assignments); ?></span>
                        </div>
                        <div class="max-h-[400px] overflow-y-auto">
                            <?php if(empty($assignments)): ?>
                                <p class="p-8 text-center text-slate-400 text-sm">No active assignments found.</p>
                            <?php else: ?>
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold sticky top-0">
                                        <tr>
                                            <th class="px-6 py-3">Teacher</th>
                                            <th class="px-6 py-3">Subject / Group</th>
                                            <th class="px-6 py-3 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php foreach($assignments as $a): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-slate-700"><?php echo htmlspecialchars($a['t_name']); ?></td>
                                            <td class="px-6 py-4">
                                                <div class="text-emerald-600 font-bold"><?php echo htmlspecialchars($a['s_name']); ?></div>
                                                <div class="text-[10px] text-slate-400 uppercase font-bold"><?php echo htmlspecialchars($a['g_name'] ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="?delete_assignment=<?php echo $a['id']; ?>" 
                                                   class="text-slate-300 hover:text-red-500 transition-colors"
                                                   onclick="return confirm('Remove this assignment?');">
                                                    <i class="fas fa-times-circle"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>