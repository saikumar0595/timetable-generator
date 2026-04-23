<?php
session_start();

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // 1. Logic-Based Role Detection
    if (strpos($username, 'STUDENT@') === 0) {
        $_SESSION['role'] = 'student';
        $_SESSION['user'] = $username;
        header("Location: view_timetable.php");
        exit();
    } elseif (strpos($username, 'STAFF@') === 0) {
        $_SESSION['role'] = 'faculty';
        $_SESSION['user'] = $username;
        header("Location: view_timetable.php");
        exit();
    } elseif (strpos($username, '_@') !== false) {
        // Format: NAME_@SYMBOLS
        $_SESSION['role'] = 'admin';
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid University ID Format! <br><small>Use STUDENT@123, STAFF@123, or ADMIN_@!!!</small>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Institutional Login | Audisankara University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .input-glow:focus { box-shadow: 0 0 15px rgba(99, 102, 241, 0.3); border-color: #6366f1; }
    </style>
</head>
<body class="h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <!-- Animated background blobs -->
    <div class="absolute top-0 -left-4 w-72 h-72 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 -right-4 w-72 h-72 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="max-w-md w-full glass p-10 rounded-[2.5rem] shadow-2xl relative z-10">
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-gradient-to-tr from-indigo-600 to-indigo-400 rounded-3xl mx-auto mb-6 flex items-center justify-center shadow-lg shadow-indigo-500/40 transform rotate-3">
                <svg width="40" height="40" viewBox="0 0 100 100" fill="none">
                    <path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="white" stroke="#fbbf24" stroke-width="5"/>
                    <text x="50" y="55" font-weight="bold" font-size="28" fill="#1e3a8a" text-anchor="middle">A</text>
                </svg>
            </div>
            <h1 class="text-white text-3xl font-bold tracking-tight">ChronoGen AI</h1>
            <p class="text-slate-400 text-sm mt-2">Enterprise University Authentication</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-2xl text-xs mb-6 text-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">University ID</label>
                <div class="relative">
                    <i class="fas fa-id-card absolute left-5 top-1/2 -translate-y-1/2 text-slate-600"></i>
                    <input type="text" name="username" required placeholder="STUDENT@... or STAFF@..." class="w-full bg-slate-900/50 border border-slate-800 rounded-2xl pl-12 pr-5 py-4 text-white outline-none transition-all input-glow placeholder:text-slate-700">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Security Key</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-600"></i>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-900/50 border border-slate-800 rounded-2xl pl-12 pr-5 py-4 text-white outline-none transition-all input-glow placeholder:text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" class="w-4 h-4 rounded border-slate-800 bg-slate-900 text-indigo-600 focus:ring-0">
                    <span class="text-xs text-slate-500 group-hover:text-slate-400 transition-colors">Remember Me</span>
                </label>
                <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Forgot Access?</a>
            </div>

            <button type="submit" name="login" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] mt-4">
                Verify & Enter
            </button>
        </form>

        <div class="mt-10 border-t border-slate-800/50 pt-8 text-center">
            <p class="text-slate-600 text-[10px] uppercase font-bold tracking-widest">Audisankara Secure Gateway v3.5</p>
        </div>
    </div>
</body>
</html>