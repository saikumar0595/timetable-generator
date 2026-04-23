<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

if (isset($_POST['add_group'])) {
    $name = trim($_POST['group_name']);
    if (!empty($name)) {
        if (DEMO_MODE) {
            $id = empty($_SESSION['groups']) ? 1 : max(array_column($_SESSION['groups'], 'id')) + 1;
            $_SESSION['groups'][] = ['id' => $id, 'name' => $name];
        } else {
            $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        header("Location: manage_groups.php"); exit();
    }
}

if (isset($_GET['delete_group'])) {
    $id = intval($_GET['delete_group']);
    if (DEMO_MODE) { $_SESSION['groups'] = array_filter($_SESSION['groups'], function($g) use ($id) { return $g['id'] != $id; }); }
    else { $conn->query("DELETE FROM groups WHERE id = $id"); }
    header("Location: manage_groups.php"); exit();
}

$groups = DEMO_MODE ? ($_SESSION['groups'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM groups"), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Groups | Audisankara University</title>
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
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-book w-5 text-center group-hover:text-pink-400 transition-colors"></i>
                <span class="font-medium">Subjects</span>
            </a>
            <a href="manage_classrooms.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all duration-200 group">
                <i class="fas fa-door-open w-5 text-center group-hover:text-indigo-400 transition-colors"></i>
                <span class="font-medium">Classrooms</span>
            </a>
            <a href="manage_groups.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl transition-all duration-200 border border-indigo-500/20 shadow-sm">
                <i class="fas fa-users w-5 text-center"></i>
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
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Group Management</h2>
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 text-lg mb-6">Add New Group</h3>
                        <form method="POST" class="space-y-4">
                            <input type="text" name="group_name" required placeholder="Group Name (e.g. B.Tech CSE-A)" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <button type="submit" name="add_group" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all">Save Group</button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold"><tr class="border-b"><th class="px-6 py-4">Group Name</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($groups as $g): ?>
                                <tr class="hover:bg-slate-50 transition-colors"><td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($g['name']) ?></td><td class="px-6 py-4 text-right"><a href="?delete_group=<?= $g['id'] ?>" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></a></td></tr>
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