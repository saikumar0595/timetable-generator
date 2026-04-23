<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$editing_teacher = null;
if (isset($_GET['edit_teacher'])) {
    $id = intval($_GET['edit_teacher']);
    foreach ($_SESSION['teachers'] as $t) {
        if ($t['id'] == $id) {
            $editing_teacher = $t;
            break;
        }
    }
}

// Handle Add/Update
if (isset($_POST['save_teacher'])) {
    $id = isset($_POST['teacher_id_pk']) ? intval($_POST['teacher_id_pk']) : 0;
    $univ_id = trim($_POST['univ_id']);
    $name = trim($_POST['teacher_name']);
    $email = trim($_POST['teacher_email']);
    $qualification = trim($_POST['teacher_qualification']);
    $experience = trim($_POST['teacher_experience']);
    $photo = trim($_POST['teacher_photo']);
    
    if (!empty($name)) {
        if (DEMO_MODE) {
            if ($id > 0) {
                // Update
                foreach ($_SESSION['teachers'] as &$t) {
                    if ($t['id'] == $id) {
                        $t['univ_id'] = $univ_id;
                        $t['name'] = $name;
                        $t['email'] = $email;
                        $t['qualification'] = $qualification;
                        $t['experience'] = $experience;
                        $t['photo'] = $photo;
                        break;
                    }
                }
            } else {
                // Add
                $new_id = empty($_SESSION['teachers']) ? 1 : max(array_column($_SESSION['teachers'], 'id')) + 1;
                $_SESSION['teachers'][] = [
                    'id' => $new_id, 
                    'univ_id' => !empty($univ_id) ? $univ_id : 'STAFF@' . (1000 + $new_id),
                    'name' => $name, 
                    'email' => $email,
                    'role' => 'Faculty',
                    'qualification' => $qualification,
                    'phone' => '+91 ...',
                    'experience' => $experience,
                    'photo' => !empty($photo) ? $photo : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random'
                ];
            }
        }
        header("Location: manage_teachers.php");
        exit();
    }
}

if (isset($_GET['delete_teacher'])) {
    $id = intval($_GET['delete_teacher']);
    if (DEMO_MODE) {
        $_SESSION['teachers'] = array_filter($_SESSION['teachers'], function($t) use ($id) { return $t['id'] != $id; });
    }
    header("Location: manage_teachers.php"); exit();
}

$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : [];
$assignments = $_SESSION['assignments'] ?? [];

function get_teacher_subjects($tid, $assignments) {
    $subs = [];
    foreach($assignments as $a) {
        if(($a['t_id'] ?? 0) == $tid) $subs[] = $a['s_name'];
    }
    return array_unique($subs);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers | Audisankara University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20">
        <div class="h-20 flex items-center px-8 border-b border-slate-800">
            <div class="w-8 h-8 mr-3">
                <svg width="32" height="32" viewBox="0 0 100 100" fill="none"><path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="#1e3a8a" stroke="#fbbf24" stroke-width="5"/><text x="50" y="55" font-weight="bold" font-size="28" fill="white" text-anchor="middle">A</text></svg>
            </div>
            <span class="text-lg font-bold tracking-tight uppercase">Audisankara</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-th-large w-5"></i><span>Dashboard</span></a>
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl border border-indigo-500/20 shadow-sm"><i class="fas fa-chalkboard-teacher w-5"></i><span>Teachers</span></a>
            <a href="manage_subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-book w-5"></i><span>Subjects</span></a>
            <a href="manage_classrooms.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-door-open w-5"></i><span>Classrooms</span></a>
            <a href="view_timetable.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group"><i class="fas fa-calendar-alt w-5"></i><span>Timetable</span></a>
            <div class="mt-8 border-t border-slate-800 pt-6">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all group"><i class="fas fa-sign-out-alt w-5"></i><span>Sign Out</span></a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Faculty Management</h2>
            <div class="flex items-center gap-4">
                <a href="legacy_admin.php" class="px-4 py-2 bg-slate-900 text-sky-400 rounded-lg text-xs font-bold border border-sky-900 hover:bg-slate-800 transition"><i class="fas fa-terminal mr-2"></i> Legacy Console</a>
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Form Column -->
                <div class="xl:col-span-1">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 sticky top-0">
                        <h3 class="font-bold text-slate-800 text-xl mb-8"><?= $editing_teacher ? 'Edit' : 'Add New' ?> Teacher</h3>
                        <form method="POST" class="space-y-5">
                            <input type="hidden" name="teacher_id_pk" value="<?= $editing_teacher['id'] ?? 0 ?>">
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">University ID</label>
                                <input type="text" name="univ_id" value="<?= $editing_teacher['univ_id'] ?? '' ?>" placeholder="STAFF@1001" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                                <input type="text" name="teacher_name" required value="<?= $editing_teacher['name'] ?? '' ?>" placeholder="Dr. Suresh Reddy" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Qualification</label>
                                <input type="text" name="teacher_qualification" value="<?= $editing_teacher['qualification'] ?? '' ?>" placeholder="Ph.D in AI" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Experience</label>
                                <input type="text" name="teacher_experience" value="<?= $editing_teacher['experience'] ?? '' ?>" placeholder="15 Years" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                                <input type="email" name="teacher_email" value="<?= $editing_teacher['email'] ?? '' ?>" placeholder="suresh@audisankara.ac.in" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Photo URL</label>
                                <input type="text" name="teacher_photo" value="<?= $editing_teacher['photo'] ?? '' ?>" placeholder="https://..." class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            </div>

                            <button type="submit" name="save_teacher" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                                <?= $editing_teacher ? 'Update' : 'Register' ?> Faculty Member
                            </button>
                            <?php if($editing_teacher): ?>
                                <a href="manage_teachers.php" class="block text-center text-xs text-slate-400 hover:text-slate-600 mt-2 font-bold">Cancel Editing</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Table Column -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-bold tracking-widest">
                                <tr>
                                    <th class="px-6 py-5">Faculty</th>
                                    <th class="px-6 py-5">Experience</th>
                                    <th class="px-6 py-5">Subjects</th>
                                    <th class="px-6 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($teachers as $t): 
                                    $t_subs = get_teacher_subjects($t['id'], $assignments);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <img src="<?= $t['photo'] ?>" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                            <div>
                                                <div class="font-bold text-slate-700 leading-tight"><?= htmlspecialchars($t['name']) ?></div>
                                                <div class="text-[10px] font-bold text-indigo-500 uppercase"><?= htmlspecialchars($t['univ_id']) ?> • <?= htmlspecialchars($t['qualification'] ?? 'N/A') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-slate-500 font-medium"><?= htmlspecialchars($t['experience'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach(array_slice($t_subs, 0, 3) as $s): ?>
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[9px] font-bold border border-indigo-100 uppercase"><?= htmlspecialchars($s) ?></span>
                                            <?php endforeach; ?>
                                            <?php if(count($t_subs) > 3): ?>
                                                <span class="text-[9px] text-slate-400 font-bold">+<?= count($t_subs)-3 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right space-x-2">
                                        <a href="?edit_teacher=<?= $t['id'] ?>" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors"><i class="fas fa-edit"></i></a>
                                        <a href="?delete_teacher=<?= $t['id'] ?>" class="p-2 text-slate-400 hover:text-red-600 transition-colors" onclick="return confirm('Erase this record?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
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