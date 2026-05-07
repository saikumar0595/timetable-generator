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
    <title>Manage Groups | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('groups', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Group Management', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 fade-in">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Form Column -->
                <div class="xl:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 xl:sticky xl:top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xl">Add New Group</h3>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Group Name</label>
                                <input type="text" name="group_name" required placeholder="e.g. B.Tech CSE-A" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-amber-500 text-sm transition-all focus:bg-white">
                            </div>

                            <div class="pt-4">
                                <button type="submit" name="add_group" class="w-full bg-amber-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-amber-500/20 hover:bg-amber-600 transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i>
                                    Save Group
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Column -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Student Groups</h3>
                                <p class="text-xs text-slate-500 font-medium">Manage and view all registered student groups</p>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase"><?= count($groups) ?> Total</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Group Name</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(empty($groups)): ?>
                                        <tr>
                                            <td colspan="2" class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No groups registered yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach($groups as $g): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($g['name']) ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="?delete_group=<?= $g['id'] ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Erase this group?');">
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