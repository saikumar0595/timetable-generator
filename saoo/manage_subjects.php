<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// ... (Existing logic for subjects & assignments)
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
                    'id' => $id, 't_id' => $t_id, 't_name' => $_SESSION['teachers'][$teacher]['name'],
                    's_name' => $_SESSION['subjects'][$subject]['name'], 'g_name' => $_SESSION['groups'][$group]['name']
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

if (isset($_GET['delete_subject'])) {
    $id = intval($_GET['delete_subject']);
    if (DEMO_MODE) { $_SESSION['subjects'] = array_filter($_SESSION['subjects'], function($s) use ($id) { return $s['id'] != $id; }); }
    else { $conn->query("DELETE FROM subjects WHERE id = $id"); }
    header("Location: manage_subjects.php"); exit();
}

if (isset($_GET['delete_assignment'])) {
    $id = intval($_GET['delete_assignment']);
    if (DEMO_MODE) { $_SESSION['assignments'] = array_filter($_SESSION['assignments'], function($a) use ($id) { return $a['id'] != $id; }); }
    else { $conn->query("DELETE FROM teacher_subjects WHERE id = $id"); }
    header("Location: manage_subjects.php"); exit();
}

$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
$subjects = DEMO_MODE ? ($_SESSION['subjects'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM subjects"), MYSQLI_ASSOC);
$assignments = DEMO_MODE ? ($_SESSION['assignments'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT ts.id, t.name as t_name, s.name as s_name, g.name as g_name FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id JOIN subjects s ON ts.subject_id = s.id JOIN groups g ON ts.group_id = g.id"), MYSQLI_ASSOC);
$groups = DEMO_MODE ? ($_SESSION['groups'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM groups"), MYSQLI_ASSOC);
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
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Curriculum Management</h2>
            <div class="flex items-center gap-4">
                <a href="legacy_admin.php" class="px-4 py-2 bg-slate-900 text-sky-400 rounded-lg text-xs font-bold border border-sky-900 hover:bg-slate-800 transition">
                    <i class="fas fa-terminal mr-2"></i> Legacy Console
                </a>
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold">
                    <?php echo substr($_SESSION['user'], 0, 1); ?>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Subjects -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-6">Create New Subject</h3>
                        <form method="POST" class="flex gap-4">
                            <input type="text" name="subject_name" required placeholder="Subject Name" class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <button type="submit" name="add_subject" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all">Add</button>
                        </form>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold"><tr class="border-b"><th class="px-6 py-4">Subject</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($subjects as $s): ?>
                                <tr class="hover:bg-slate-50 transition-colors"><td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($s['name']) ?></td><td class="px-6 py-4 text-right"><a href="?delete_subject=<?= $s['id'] ?>" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></a></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Assignments -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-6">Link Faculty & Subject</h3>
                        <form method="POST" class="space-y-4">
                            <select name="teacher_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm">
                                <option value="">Select Teacher</option>
                                <?php foreach($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
                            </select>
                            <select name="subject_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm">
                                <option value="">Select Subject</option>
                                <?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                            </select>
                            <select name="group_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm">
                                <option value="">Select Group</option>
                                <?php foreach($groups as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option><?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_subject" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all">Link Entity</button>
                        </form>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold"><tr class="border-b"><th class="px-6 py-4">Assignment</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($assignments as $a): ?>
                                <tr class="hover:bg-slate-50 transition-colors"><td class="px-6 py-4"><div class="font-bold text-slate-700"><?= htmlspecialchars($a['t_name']) ?></div><div class="text-xs text-indigo-500"><?= htmlspecialchars($a['s_name']) ?> (<?= htmlspecialchars($a['g_name']) ?>)</div></td><td class="px-6 py-4 text-right"><a href="?delete_assignment=<?= $a['id'] ?>" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></a></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>