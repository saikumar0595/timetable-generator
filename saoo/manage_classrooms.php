<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

if (isset($_POST['add_classroom'])) {
    $name = trim($_POST['room_name']);
    $type = $_POST['room_type'];
    $capacity = intval($_POST['room_capacity']);
    if (!empty($name)) {
        if (DEMO_MODE) {
            $id = empty($_SESSION['classrooms']) ? 1 : max(array_column($_SESSION['classrooms'], 'id')) + 1;
            $_SESSION['classrooms'][] = ['id' => $id, 'name' => $name, 'type' => $type, 'capacity' => $capacity];
        } else {
            $stmt = $conn->prepare("INSERT INTO classrooms (name, type, capacity) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $name, $type, $capacity);
            $stmt->execute();
        }
        header("Location: manage_classrooms.php"); exit();
    }
}

if (isset($_GET['delete_classroom'])) {
    $id = intval($_GET['delete_classroom']);
    if (DEMO_MODE) { $_SESSION['classrooms'] = array_filter($_SESSION['classrooms'], function($c) use ($id) { return $c['id'] != $id; }); }
    else { $conn->query("DELETE FROM classrooms WHERE id = $id"); }
    header("Location: manage_classrooms.php"); exit();
}

$classrooms = DEMO_MODE ? ($_SESSION['classrooms'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM classrooms"), MYSQLI_ASSOC);

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
    <title>Manage Classrooms | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('classrooms', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Facility Management', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 fade-in">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Form Column -->
                <div class="xl:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 xl:sticky xl:top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-door-open text-lg"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xl">Add New Classroom</h3>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Room Name</label>
                                <input type="text" name="room_name" required placeholder="e.g. LH-301" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all focus:bg-white">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Room Type</label>
                                <select name="room_type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-indigo-500 transition-all focus:bg-white">
                                    <option value="LectureHall">Lecture Hall</option>
                                    <option value="Lab">Lab</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Capacity (Seats)</label>
                                <input type="number" name="room_capacity" required value="60" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all focus:bg-white">
                            </div>

                            <div class="pt-4">
                                <button type="submit" name="add_classroom" class="w-full bg-indigo-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i>
                                    Save Facility
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
                                <h3 class="font-bold text-slate-800 text-lg">Facility Directory</h3>
                                <p class="text-xs text-slate-500 font-medium">Manage and view all registered rooms</p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase"><?= count($classrooms) ?> Total</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Room Name</th>
                                        <th class="px-6 py-4">Type</th>
                                        <th class="px-6 py-4 text-center">Capacity</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(empty($classrooms)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No classrooms registered yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach($classrooms as $c): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($c['name']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?= $c['type'] == 'Lab' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' ?>">
                                                <?= $c['type'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 bg-slate-100 rounded-full text-xs font-bold text-slate-600">
                                                <?= $c['capacity'] ?? 60 ?> Seats
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="?delete_classroom=<?= $c['id'] ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Erase this classroom?');">
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