<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Logic for subjects & assignments
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
        header("Location: manage_subjects.php?success=Subject+added"); exit();
    }
}

if (isset($_POST['assign_subject'])) {
    $t_id = intval($_POST['teacher_id']);
    $s_id = intval($_POST['subject_id']);
    $g_id = intval($_POST['group_id']);
    if ($t_id > 0 && $s_id > 0 && $g_id > 0) {
        if (DEMO_MODE) {
            // Robust lookup for demo mode entities
            $t_obj = null; foreach($_SESSION['teachers'] as $t) if($t['id'] == $t_id) { $t_obj = $t; break; }
            $s_obj = null; foreach($_SESSION['subjects'] as $s) if($s['id'] == $s_id) { $s_obj = $s; break; }
            $g_obj = null; foreach($_SESSION['groups'] as $g) if($g['id'] == $g_id) { $g_obj = $g; break; }
            
            if ($t_obj && $s_obj && $g_obj) {
                $id = empty($_SESSION['assignments']) ? 1 : max(array_column($_SESSION['assignments'], 'id')) + 1;
                $_SESSION['assignments'][] = [
                    'id' => $id, 
                    't_id' => $t_id, 
                    't_name' => $t_obj['name'],
                    's_name' => $s_obj['name'], 
                    'g_name' => $g_obj['name']
                ];
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, group_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $t_id, $s_id, $g_id);
            $stmt->execute();
        }
        header("Location: manage_subjects.php?success=Assignment+created"); exit();
    }
}

if (isset($_GET['delete_subject'])) {
    $id = intval($_GET['delete_subject']);
    if (DEMO_MODE) { $_SESSION['subjects'] = array_filter($_SESSION['subjects'], function($s) use ($id) { return $s['id'] != $id; }); }
    else { $conn->query("DELETE FROM subjects WHERE id = $id"); }
    header("Location: manage_subjects.php?success=Subject+deleted"); exit();
}

if (isset($_GET['delete_assignment'])) {
    $id = intval($_GET['delete_assignment']);
    if (DEMO_MODE) { $_SESSION['assignments'] = array_filter($_SESSION['assignments'], function($a) use ($id) { return $a['id'] != $id; }); }
    else { $conn->query("DELETE FROM teacher_subjects WHERE id = $id"); }
    header("Location: manage_subjects.php?success=Assignment+deleted"); exit();
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
include('components/modal.php');
include('components/styles.php');

// Prepare data for tables
$subject_rows = [];
foreach ($subjects as $s) {
    $subject_rows[] = [
        $s['name'],
        '<div class="text-right">
            <a href="?delete_subject=' . $s['id'] . '" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm(\'Erase this subject?\');">
                <i class="fas fa-trash-alt text-xs"></i>
            </a>
        </div>'
    ];
}

$assignment_rows = [];
foreach ($assignments as $a) {
    $assignment_rows[] = [
        '<div>
            <div class="font-bold text-slate-700">' . htmlspecialchars($a['t_name']) . '</div>
            <div class="text-[10px] font-bold text-indigo-500 uppercase mt-0.5 tracking-tight">' . htmlspecialchars($a['s_name']) . ' <span class="text-slate-300 mx-1">•</span> ' . htmlspecialchars($a['g_name']) . '</div>
        </div>',
        '<div class="text-right">
            <a href="?delete_assignment=' . $a['id'] . '" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm(\'Remove this assignment?\');">
                <i class="fas fa-times text-xs"></i>
            </a>
        </div>'
    ];
}

// Prepare options for selects
$teacher_options = [];
foreach ($teachers as $t) $teacher_options[$t['id']] = $t['name'];

$subject_options = [];
foreach ($subjects as $s) $subject_options[$s['id']] = $s['name'];

$group_options = [];
foreach ($groups as $g) $group_options[$g['id']] = $g['name'];
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
    <style>
        /* Custom styles to handle HTML in table cells */
        #subjects-table td, #assignments-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('subjects', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Curriculum Management', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 fade-in">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Department Overview</h2>
                    <p class="text-sm text-slate-500">Manage your course catalog and faculty assignments</p>
                </div>
                <div class="flex gap-3">
                    <a href="test_alerts.php?redirect=manage_subjects.php" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i class="fas fa-bell text-amber-500"></i> Test System Alerts
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <?php renderAlert('success', $_GET['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <?php renderAlert('info', $_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Subjects Section -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-book-medical text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-xl">Create New Subject</h3>
                                <p class="text-xs text-slate-500 font-medium">Add a new academic course to the system</p>
                            </div>
                        </div>
                        <form method="POST" class="flex gap-4 items-end">
                            <div class="flex-1">
                                <?php renderFormInput('Subject Name', 'subject_name', 'text', 'e.g. Data Structures', true); ?>
                            </div>
                            <div class="mb-4">
                                <button type="submit" name="add_subject" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2 h-[46px]">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </form>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-bold text-slate-800 text-lg">Subject List</h3>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase"><?= count($subjects) ?> Total</span>
                        </div>
                        <?php renderTable(['Subject Name', 'Action'], $subject_rows, 'subjects-table', true, true); ?>
                    </div>
                </div>

                <!-- Assignments Section -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fas fa-link text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-xl">Link Faculty & Subject</h3>
                                <p class="text-xs text-slate-500 font-medium">Assign teachers to subjects and student groups</p>
                            </div>
                        </div>
                        <form method="POST">
                            <?php renderFormSelect('Faculty Member', 'teacher_id', $teacher_options, '', true); ?>
                            <div class="grid grid-cols-2 gap-4">
                                <?php renderFormSelect('Subject', 'subject_id', $subject_options, '', true); ?>
                                <?php renderFormSelect('Student Group', 'group_id', $group_options, '', true); ?>
                            </div>
                            <button type="submit" name="assign_subject" class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-bold shadow-lg hover:bg-slate-800 transition-all active:scale-95 flex items-center justify-center gap-2 mt-2">
                                <i class="fas fa-link"></i> Link Entity
                            </button>
                        </form>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-bold text-slate-800 text-lg">Active Assignments</h3>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase"><?= count($assignments) ?> Active</span>
                        </div>
                        <?php renderTable(['Assignment Detail', 'Action'], $assignment_rows, 'assignments-table', true, true); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/chatbot.js"></script>
    <script src="assets/js/alert_handler.js"></script>
    <script>
        // Check for alerts every 10 seconds
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