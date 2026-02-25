<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// ... (Keep existing logic for adding/deleting teachers) ...
// Initialize Demo Data
if (DEMO_MODE && !isset($_SESSION['teachers'])) {
    $_SESSION['teachers'] = [
        ['id' => 1, 'name' => 'Prof. K. Suresh (HOD - CSE)', 'email' => 'hod.cse@audisankara.ac.in'],
        ['id' => 2, 'name' => 'Dr. P. Ramesh (ECE)', 'email' => 'ramesh.ece@audisankara.ac.in'],
        ['id' => 3, 'name' => 'Prof. M. Lakshmi (EEE)', 'email' => 'lakshmi.eee@audisankara.ac.in'],
        ['id' => 4, 'name' => 'Dr. G. Murali (CSE)', 'email' => 'murali.cse@audisankara.ac.in'],
        ['id' => 5, 'name' => 'Dr. B. Venkat (AI)', 'email' => 'venkat.ai@audisankara.ac.in']
    ];
}

// Handle Add
if (isset($_POST['add_teacher'])) {
    $name = trim($_POST['teacher_name']);
    $email = trim($_POST['teacher_email']);
    $role = trim($_POST['teacher_role']);
    $qualification = trim($_POST['teacher_qualification']);
    $phone = trim($_POST['teacher_phone']);
    $experience = trim($_POST['teacher_experience']);
    
    // Handle File Upload
    $photo_path = '';
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_ext = pathinfo($_FILES['teacher_photo']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('teacher_') . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['teacher_photo']['tmp_name'], $target_file)) {
            $photo_path = $target_file;
        }
    }
    
    if (empty($photo_path)) {
        $photo_path = 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random';
    }
    
    if (!empty($name)) {
        if (DEMO_MODE) {
            $id = empty($_SESSION['teachers']) ? 1 : max(array_column($_SESSION['teachers'], 'id')) + 1;
            $_SESSION['teachers'][] = [
                'id' => $id, 
                'name' => $name, 
                'email' => $email,
                'role' => $role,
                'qualification' => $qualification,
                'phone' => $phone,
                'experience' => $experience,
                'photo' => $photo_path
            ];
        } else {
            $stmt = $conn->prepare("INSERT INTO teachers (name, email, role, qualification, phone, experience, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $role, $qualification, $phone, $experience, $photo_path);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: manage_teachers.php");
        exit();
    }
}

// Handle Delete
if (isset($_GET['delete_teacher'])) {
    $id = intval($_GET['delete_teacher']);
    if (DEMO_MODE) {
        $_SESSION['teachers'] = array_filter($_SESSION['teachers'], function($t) use ($id) { return $t['id'] != $id; });
        if(isset($_SESSION['assignments'])) {
             $_SESSION['assignments'] = array_filter($_SESSION['assignments'], function($a) use ($id) { return $a['t_id'] != $id; });
        }
    } else {
        $conn->query("DELETE FROM teacher_subjects WHERE teacher_id = $id");
        $conn->query("DELETE FROM teachers WHERE id = $id");
    }
    header("Location: manage_teachers.php");
    exit();
}

// Fetch Teachers
$teachers = DEMO_MODE ? $_SESSION['teachers'] : mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM teachers"), MYSQLI_ASSOC);
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

    <!-- Sidebar (Same as Dashboard) -->
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20">
        <div class="h-20 flex items-center px-8 border-b border-slate-800">
            <!-- Mini ASCET Logo -->
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
            <a href="manage_teachers.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-400 rounded-xl transition-all duration-200 border border-indigo-500/20 shadow-sm">
                <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
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
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Faculty Management</h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
                    <p class="text-xs text-slate-400">Administrator</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                    <?php echo substr($_SESSION['user'], 0, 1); ?>
                </div>
            </div>
        </header>

        <!-- Scrolling Header Bar -->
        <div class="w-full bg-slate-900 overflow-hidden h-12 flex items-center border-b border-slate-800">
            <div class="whitespace-nowrap animate-scroll flex items-center">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
                <img src="assets_login/img/header.png" alt="Header" class="h-8 mx-4">
            </div>
        </div>

        <style>
            @keyframes scroll {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .animate-scroll {
                display: flex;
                width: max-content;
                animation: scroll 20s linear infinite;
            }
        </style>

        <div class="flex-1 overflow-y-auto p-8 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Add Teacher Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 sticky top-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg">Add New Teacher</h3>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Full Name</label>
                                <input type="text" name="teacher_name" required placeholder="e.g. Dr. John Doe" 
                                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Role / Designation</label>
                                    <input type="text" name="teacher_role" placeholder="e.g. Professor" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Qualification</label>
                                    <input type="text" name="teacher_qualification" placeholder="e.g. Ph.D" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Experience</label>
                                    <input type="text" name="teacher_experience" placeholder="e.g. 10 Years" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Phone Number</label>
                                    <input type="text" name="teacher_phone" placeholder="+91 ..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Email Address</label>
                                <input type="email" name="teacher_email" placeholder="e.g. john@audisankara.ac.in" 
                                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Upload Profile Photo</label>
                                <input type="file" name="teacher_photo" accept="image/*" 
                                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <button type="submit" name="add_teacher" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all flex items-center justify-center gap-2 mt-2">
                                <i class="fas fa-plus"></i> Save Faculty Member
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Teacher List -->
                <div class="lg:col-span-2">
                    <div class="x_panel bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="x_title px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h2 class="font-bold text-slate-700 m-0">Faculty Directory <small class="text-xs font-normal text-slate-400 ml-2">Active Members</small></h2>
                            <ul class="nav navbar-right panel_toolbox flex gap-3 text-slate-400">
                                <li><a class="collapse-link hover:text-indigo-500 cursor-pointer"><i class="fa fa-chevron-up"></i></a></li>
                                <li><a class="close-link hover:text-red-500 cursor-pointer"><i class="fa fa-close"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        
                        <div class="x_content p-0">
                            <?php if (empty($teachers)): ?>
                                <div class="p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400 text-2xl">
                                        <i class="fas fa-users-slash"></i>
                                    </div>
                                    <h3 class="text-slate-800 font-bold mb-1">No Teachers Found</h3>
                                    <p class="text-slate-500 text-sm">Add your first faculty member to get started.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped jambo_table bulk_action w-full text-left border-collapse">
                                        <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500 font-semibold tracking-wider">
                                            <tr class="headings">
                                                <th class="column-title px-6 py-4">Name </th>
                                                <th class="column-title px-6 py-4">Contact </th>
                                                <th class="column-title px-6 py-4 no-link last text-right"><span class="nobr">Action</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <?php foreach($teachers as $t): ?>
                                            <tr class="even pointer hover:bg-slate-50/80 transition-colors group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                                            <?php echo substr($t['name'], 0, 1); ?>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($t['name']); ?></div>
                                                            <div class="text-xs text-slate-400">ID: #<?php echo $t['id']; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if(!empty($t['email'])): ?>
                                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                                            <i class="far fa-envelope text-slate-400"></i>
                                                            <?php echo htmlspecialchars($t['email']); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-xs text-slate-300 italic">No email provided</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-right last">
                                                    <a href="?delete_teacher=<?php echo $t['id']; ?>" 
                                                       class="btn btn-danger btn-xs inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 hover:text-white hover:bg-red-500 transition-all"
                                                       onclick="return confirm('Are you sure you want to remove this teacher?');"
                                                       title="Delete Teacher">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>