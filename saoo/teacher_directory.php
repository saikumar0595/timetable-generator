<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/cards.php');
include('components/styles.php');

$role = $_SESSION['role'] ?? 'student';
$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
$assignments = DEMO_MODE ? ($_SESSION['assignments'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT ts.*, s.name as s_name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id"), MYSQLI_ASSOC);

$teacher_subjects = [];
foreach ($assignments as $a) {
    $t_id = $a['t_id'] ?? ($a['teacher_id'] ?? null);
    if ($t_id) $teacher_subjects[$t_id][] = $a['s_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Directory | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('teachers', $role); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Faculty Directory', $_SESSION['user'], $role, true); ?>

        <div class="flex-1 overflow-y-auto p-8 pb-20 fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($teachers as $t): 
                    $t_name = $t['name'] ?? 'Unknown';
                    $t_photo = $t['photo'] ?? '';
                    $t_id = $t['id'] ?? 0;
                ?>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all group">
                    <div class="h-24 bg-gradient-to-r from-indigo-600 to-purple-600 relative">
                        <img src="<?= htmlspecialchars($t_photo) ?>" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($t_name) ?>&background=random';"
                             class="w-20 h-20 rounded-2xl border-4 border-white absolute -bottom-10 left-6 shadow-lg object-cover">
                    </div>
                    <div class="pt-12 pb-6 px-6">
                        <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($t_name) ?></h3>
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4"><?= htmlspecialchars($t['role'] ?? 'Faculty') ?></p>
                        <div class="space-y-2 text-sm text-slate-500 mb-6">
                            <div class="flex items-center gap-2"><i class="fas fa-envelope w-4 text-slate-300"></i> <?= htmlspecialchars($t['email'] ?? 'N/A') ?></div>
                            <div class="flex items-center gap-2"><i class="fas fa-graduation-cap w-4 text-slate-300"></i> <?= htmlspecialchars($t['qualification'] ?? 'N/A') ?></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach(($teacher_subjects[$t_id] ?? ['General']) as $s): ?>
                                <span class="px-2 py-1 bg-slate-50 text-slate-500 rounded text-[10px] font-bold border border-slate-100 uppercase"><?= htmlspecialchars($s) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
