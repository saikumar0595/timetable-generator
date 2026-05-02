<?php
session_start();

$error = "";
$demo_tip = "";

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
        $error = "Invalid University ID Format! Use STUDENT@123, STAFF@123, or ADMIN_@!!!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChronoGen AI - Institutional Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1a1f35 100%);
            min-height: 100vh;
        }
        
        .glass { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
        }
        
        .input-glow {
            transition: all 0.3s ease;
        }
        
        .input-glow:focus { 
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4), inset 0 0 10px rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Animated background blobs -->
    <div class="absolute top-0 -left-4 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
    <div class="absolute -bottom-8 -right-4 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
    <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-pulse" style="animation-delay: 4s;"></div>

    <!-- Main Container -->
    <div class="grid md:grid-cols-2 gap-8 max-w-6xl w-full relative z-10">
        <!-- Left Side - Branding -->
        <div class="hidden md:flex flex-col justify-center space-y-8">
            <div>
                <div class="float-animation mb-6">
                    <div class="w-32 h-32 bg-gradient-to-tr from-indigo-600 to-indigo-400 rounded-3xl flex items-center justify-center shadow-2xl shadow-indigo-500/50">
                        <svg width="80" height="80" viewBox="0 0 100 100" fill="none">
                            <path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="white" stroke="#fbbf24" stroke-width="5"/>
                            <text x="50" y="55" font-weight="bold" font-size="40" fill="#1e3a8a" text-anchor="middle">A</text>
                        </svg>
                    </div>
                </div>
                <h1 class="text-5xl font-bold text-white mb-3 tracking-tight">ChronoGen AI</h1>
                <p class="text-indigo-300 text-lg">Intelligent Timetable Management System</p>
            </div>
            
            <div class="space-y-4 text-slate-300">
                <div class="flex gap-3 items-start">
                    <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                    <div>
                        <p class="font-semibold text-white">AI-Powered Scheduling</p>
                        <p class="text-sm text-slate-400">Genetic algorithms optimize conflict-free timetables</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                    <div>
                        <p class="font-semibold text-white">Real-Time Analytics</p>
                        <p class="text-sm text-slate-400">Monitor resource utilization across campus</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                    <div>
                        <p class="font-semibold text-white">Offline Access</p>
                        <p class="text-sm text-slate-400">No internet required - works completely offline</p>
                    </div>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 uppercase tracking-widest">
                <i class="fas fa-shield-alt mr-2"></i> AUDISANKARA TEACHERS COLLEGE
            </p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex items-center justify-center">
            <div class="w-full max-w-md glass p-8 md:p-10 rounded-3xl shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Welcome Back</h2>
                    <p class="text-slate-400 text-sm">Enter your university credentials</p>
                </div>

                <?php if($error): ?>
                    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl text-sm flex gap-3 items-start fade-in">
                        <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Demo Credentials Info -->
                <div class="mb-6 p-3 bg-indigo-500/10 border border-indigo-500/30 rounded-xl text-indigo-200 text-xs">
                    <p class="font-semibold mb-2"><i class="fas fa-lightbulb mr-2"></i> Demo Credentials:</p>
                    <p class="mb-1"><span class="font-mono bg-indigo-900/30 px-2 py-1 rounded">STUDENT@1001</span> or <span class="font-mono bg-indigo-900/30 px-2 py-1 rounded">admin_@admin</span></p>
                    <p class="text-indigo-300/70">(Password: any value)</p>
                </div>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">University ID</label>
                        <div class="relative">
                            <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            <input type="text" name="username" required placeholder="STUDENT@123 or STAFF@456..." 
                                   class="w-full bg-slate-900/40 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-white outline-none input-glow placeholder:text-slate-600 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Security Key</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            <input type="password" name="password" required placeholder="••••••••" 
                                   class="w-full bg-slate-900/40 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-white outline-none input-glow placeholder:text-slate-600 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-700 bg-slate-900/40 text-indigo-600 accent-indigo-600">
                            <span class="text-xs text-slate-500 group-hover:text-slate-400 transition-colors">Remember me</span>
                        </label>
                        <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Forgot access?</a>
                    </div>

                    <button type="submit" name="login" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white py-3 rounded-xl font-bold text-lg shadow-xl shadow-indigo-500/30 transition-all active:scale-95 mt-6 flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-right-long"></i> Sign In
                    </button>
                </form>

                <div class="mt-8 border-t border-slate-700/50 pt-6">
                    <p class="text-center text-slate-600 text-xs">
                        <i class="fas fa-shield-alt mr-1"></i> Secure authentication required
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fade In Animation CSS -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</body>
</html>