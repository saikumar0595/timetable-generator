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

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/cards.php');
include('components/table.php');
include('components/styles.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('subjects', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Curriculum Management', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>

        <div class="flex-1 overflow-y-auto p-8 pb-20 fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Subjects Section -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-book-medical text-lg"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xl">Create New Subject</h3>
                        </div>
                        <form method="POST" class="flex gap-4">
                            <input type="text" name="subject_name" required placeholder="Subject Name (e.g. Data Structures)" class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all focus:bg-white">
                            <button type="submit" name="add_subject" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Subject List</h3>
                                <p class="text-xs text-slate-500 font-medium">All registered academic subjects</p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase"><?= count($subjects) ?> Total</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Subject Name</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(empty($subjects)): ?>
                                        <tr>
                                            <td colspan="2" class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No subjects found.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach($subjects as $s): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($s['name']) ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="?delete_subject=<?= $s['id'] ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Erase this subject?');">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assignments Section -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fas fa-link text-lg"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xl">Link Faculty & Subject</h3>
                        </div>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Faculty Member</label>
                                <select name="teacher_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-indigo-500 transition-all focus:bg-white">
                                    <option value="">Select Teacher</option>
                                    <?php foreach($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Subject</label>
                                    <select name="subject_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-indigo-500 transition-all focus:bg-white">
                                        <option value="">Select Subject</option>
                                        <?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Student Group</label>
                                    <select name="group_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-indigo-500 transition-all focus:bg-white">
                                        <option value="">Select Group</option>
                                        <?php foreach($groups as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="assign_subject" class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-bold shadow-lg hover:bg-slate-800 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-link"></i> Link Entity
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Active Assignments</h3>
                                <p class="text-xs text-slate-500 font-medium">Current faculty-subject-group mappings</p>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase"><?= count($assignments) ?> Active</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Assignment Detail</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(empty($assignments)): ?>
                                        <tr>
                                            <td colspan="2" class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No assignments yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach($assignments as $a): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-700"><?= htmlspecialchars($a['t_name']) ?></div>
                                            <div class="text-[10px] font-bold text-indigo-500 uppercase mt-0.5 tracking-tight"><?= htmlspecialchars($a['s_name']) ?> <span class="text-slate-300 mx-1">•</span> <?= htmlspecialchars($a['g_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="?delete_assignment=<?= $a['id'] ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Remove this assignment?');">
                                                <i class="fas fa-times text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
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