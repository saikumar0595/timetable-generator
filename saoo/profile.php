<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'] ?? 'student';
$username = $_SESSION['user'];

// Find the teacher profile if role is faculty
$my_profile = null;
if ($role == 'faculty' && isset($_SESSION['teachers'])) {
    foreach ($_SESSION['teachers'] as $t) {
        $univ_id_match = isset($t['univ_id']) && strcasecmp($t['univ_id'], $username) === 0;
        $email_match = strcasecmp($t['email'], $username) === 0;
        $name_match = stripos($t['name'], $username) !== false;
        
        if ($univ_id_match || $email_match || $name_match) {
            $my_profile = $t;
            break;
        }
    }
}

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    // In demo mode, we just update the session
    if (DEMO_MODE && $my_profile) {
        foreach ($_SESSION['teachers'] as &$t) {
            if ($t['id'] == $my_profile['id']) {
                $t['phone'] = $_POST['phone'];
                $t['email'] = $_POST['email'];
                $t['experience'] = $_POST['experience'];
                $my_profile = $t;
                break;
            }
        }
        $_SESSION['flash_message'] = "Profile Updated Successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | AUDISANKARA TEACHERS GOLE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('profile', $role); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Faculty Profile', $_SESSION['user'], $role, true); ?>

        <div class="flex-1 overflow-y-auto p-8">
            <?php if (!$my_profile && $role == 'faculty'): ?>
                <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl text-amber-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Teacher record not found for "<?= htmlspecialchars($username) ?>". Please contact Admin.
                </div>
            <?php elseif ($my_profile): ?>
                <div class="max-w-2xl bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="h-32 bg-gradient-to-r from-indigo-600 to-purple-600 relative">
                        <img src="<?= $my_profile['photo'] ?>" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($my_profile['name']) ?>&background=random';"
                             class="w-24 h-24 rounded-2xl border-4 border-white absolute -bottom-12 left-8 shadow-xl object-cover">
                    </div>
                    <div class="pt-16 pb-10 px-10">
                        <h3 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($my_profile['name']) ?></h3>
                        <p class="text-indigo-600 font-bold uppercase tracking-widest text-xs mt-1 mb-8"><?= htmlspecialchars($my_profile['role']) ?></p>
                        
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Official Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($my_profile['email']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Phone Number</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($my_profile['phone'] ?? '+91 ...') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Experience</label>
                                <input type="text" name="experience" value="<?= htmlspecialchars($my_profile['experience']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <button type="submit" name="update_profile" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all">
                                Update Professional Info
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-slate-400">Profile view is only available for Faculty accounts.</div>
            <?php endif; ?>
        </div>
    </main>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>