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
    
    // Photo Upload Logic
    $photo = $_POST['existing_photo'] ?? 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random';
    
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = pathinfo($_FILES['teacher_photo']['name'], PATHINFO_EXTENSION);
        $file_name = 'teacher_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['teacher_photo']['tmp_name'], $target_path)) {
            $photo = $target_path;
        }
    }
    
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
                    'photo' => $photo
                ];
            }
        } else {
            // DATABASE MODE: Execute SQL queries
            // Example: mysqli_query($conn, "UPDATE teachers SET name='$name', ... WHERE id=$id");
            // Note: In production, use prepared statements!
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

// Include components
include('components/sidebar.php');
include('components/header.php');
include('components/cards.php');
include('components/table.php');
include('components/styles.php');
include('utils_timetable.php');

$teachers = DEMO_MODE ? ($_SESSION['teachers'] ?? []) : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers | ChronoGen AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php renderSidebar('teachers', $_SESSION['role'] ?? 'admin'); ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <!-- Header -->
        <?php renderHeader('Faculty Management', $_SESSION['user'], $_SESSION['role'] ?? 'admin', true); ?>


        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 fade-in">
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- Form Column -->
                <div class="xl:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 xl:sticky xl:top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-<?= $editing_teacher ? 'user-edit' : 'user-plus' ?> text-lg"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xl"><?= $editing_teacher ? 'Edit' : 'Add New' ?> Faculty</h3>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="teacher_id_pk" value="<?= $editing_teacher['id'] ?? 0 ?>">
                            <input type="hidden" name="existing_photo" value="<?= $editing_teacher['photo'] ?? '' ?>">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">University ID</label>
                                <input type="text" name="univ_id" value="<?= htmlspecialchars($editing_teacher['univ_id'] ?? '') ?>" placeholder="STAFF@1001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all focus:bg-white">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Full Name</label>
                                <input type="text" name="teacher_name" required value="<?= htmlspecialchars($editing_teacher['name'] ?? '') ?>" placeholder="Dr. Suresh Reddy" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all focus:bg-white">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Qualification</label>
                                    <input type="text" name="teacher_qualification" value="<?= htmlspecialchars($editing_teacher['qualification'] ?? '') ?>" placeholder="Ph.D" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all focus:bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Experience</label>
                                    <input type="text" name="teacher_experience" value="<?= htmlspecialchars($editing_teacher['experience'] ?? '') ?>" placeholder="10 Yrs" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all focus:bg-white">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                                <input type="email" name="teacher_email" value="<?= htmlspecialchars($editing_teacher['email'] ?? '') ?>" placeholder="faculty@univ.edu" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Upload Photo</label>
                                <div class="flex items-center gap-4">
                                    <?php if($editing_teacher && !empty($editing_teacher['photo'])): ?>
                                        <img src="<?= htmlspecialchars($editing_teacher['photo']) ?>" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                                    <?php endif; ?>
                                    <input type="file" name="teacher_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" name="save_teacher" class="w-full bg-indigo-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <?= $editing_teacher ? 'Update' : 'Register' ?> Faculty
                                </button>
                                <?php if($editing_teacher): ?>
                                    <a href="manage_teachers.php" class="block text-center text-[10px] text-slate-400 hover:text-indigo-600 mt-4 font-bold uppercase tracking-widest transition-colors">Cancel Editing</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Column -->
                <div class="xl:col-span-3">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Faculty Directory</h3>
                                <p class="text-xs text-slate-500 font-medium">Manage and view all registered teachers</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="relative hidden md:block">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="teacherSearch" onkeyup="filterTeachers()" placeholder="Search faculty..." class="pl-9 pr-4 py-2 bg-slate-100 border-none rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 w-64 outline-none transition-all">
                                </div>
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase"><?= count($teachers) ?> Total</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Faculty Member</th>
                                        <th class="px-6 py-4">Experience</th>
                                        <th class="px-6 py-4">Subjects</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(empty($teachers)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <i class="fas fa-users-slash text-4xl text-slate-200 mb-4"></i>
                                                    <p class="text-slate-400 font-medium text-sm">No faculty members registered yet.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach($teachers as $t): 
                                        $t_id = $t['id'] ?? 0;
                                        $t_name = $t['name'] ?? 'Unknown';
                                        $t_photo = $t['photo'] ?? '';
                                        $t_subs = get_teacher_subjects($t_id, $assignments);
                                        // Calculate workload on the fly for the badge
                                        $timetable = $_SESSION['last_generated_timetable'] ?? [];
                                        $workload = !empty($timetable) ? calculateTeacherWorkload($timetable, $t_name) : [];
                                        $t_load = $workload[$t_name] ?? ['level' => 'N/A', 'color' => '#94a3b8'];
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="relative">
                                                    <img src="<?= htmlspecialchars($t_photo) ?>" 
                                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($t_name) ?>&background=random';"
                                                         class="w-11 h-11 rounded-xl object-cover shadow-sm border border-slate-200">
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full" title="Active"></div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 leading-tight"><?= htmlspecialchars($t_name) ?></div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-tight"><?= htmlspecialchars($t['univ_id'] ?? 'N/A') ?></span>
                                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase"><?= htmlspecialchars($t['qualification'] ?? 'N/A') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-slate-700 font-semibold"><?= htmlspecialchars($t['experience'] ?? 'N/A') ?></span>
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-tighter" style="background-color: <?= $t_load['color'] ?>15; color: <?= $t_load['color'] ?>">
                                                        <?= $t_load['level'] ?>
                                                    </span>
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-tight">Experience & Load</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                                <?php if(empty($t_subs)): ?>
                                                    <span class="text-[10px] text-slate-300 italic font-medium">No subjects assigned</span>
                                                <?php else: ?>
                                                    <?php foreach(array_slice($t_subs, 0, 3) as $s): ?>
                                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-bold border border-slate-200 uppercase truncate max-w-[80px]"><?= htmlspecialchars($s) ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if(count($t_subs) > 3): ?>
                                                        <span class="w-6 h-6 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-full text-[9px] font-bold border border-indigo-100">+<?= count($t_subs)-3 ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                                <a href="?edit_teacher=<?= $t['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Edit Faculty">
                                                    <i class="fas fa-pencil-alt text-xs"></i>
                                                </a>
                                                <a href="?delete_teacher=<?= $t['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Erase this record?');" title="Delete Faculty">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </a>
                                            </div>
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
    <script>
        function filterTeachers() {
            const input = document.getElementById('teacherSearch');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('table');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    const textValue = td.textContent || td.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
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