<?php
session_start();
include('db.php');

// Auto-populate demo data if needed
if (empty($_SESSION['teachers'])) {
    require_once('auto_populate.php');
}

// Set demo admin session
$_SESSION['user'] = 'test_@system';
$_SESSION['role'] = 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChronoGen System Check</title>
    <style>
        body { font-family: Arial; background: #0f172a; color: white; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .status { background: rgba(100,200,100,0.2); border: 1px solid #64c864; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: rgba(200,100,100,0.2); border: 1px solid #c86464; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .success { background: rgba(100,200,100,0.2); border: 1px solid #64c864; padding: 15px; border-radius: 8px; margin: 10px 0; }
        h1 { color: #6366f1; }
        .demo-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 4px; }
        .demo-link:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ ChronoGen Application is Running</h1>
        
        <div class="success">
            <strong>System Status: OPERATIONAL</strong>
            <p>PHP Development Server: Running on port 8000</p>
            <p>Mode: <?php echo DEMO_MODE ? '✓ Demo Mode (No Database Required)' : 'Database Connected'; ?></p>
        </div>
        
        <h2>Demo Data Loaded:</h2>
        <div class="status">
            <p>✓ Teachers: <?php echo count($_SESSION['teachers'] ?? []); ?></p>
            <p>✓ Subjects: <?php echo count($_SESSION['subjects'] ?? []); ?></p>
            <p>✓ Classrooms: <?php echo count($_SESSION['classrooms'] ?? []); ?></p>
            <p>✓ Groups: <?php echo count($_SESSION['groups'] ?? []); ?></p>
            <p>✓ Assignments: <?php echo count($_SESSION['assignments'] ?? []); ?></p>
        </div>
        
        <h2>Login Instructions:</h2>
        <div class="status">
            <p><strong>Access as Admin:</strong> Username: <code>admin_@admin</code> | Password: (any)</p>
            <p><strong>Access as Student:</strong> Username: <code>STUDENT@1001</code> | Password: (any)</p>
            <p><strong>Access as Faculty:</strong> Username: <code>STAFF@2001</code> | Password: (any)</p>
        </div>
        
        <h2>Network Access:</h2>
        <div class="status">
            <p>Local: http://localhost:8000/login.php</p>
            <p>Network: http://172.27.224.1:8000/login.php</p>
        </div>
        
        <a href="/login.php" class="demo-link">→ Go to Login Page</a>
    </div>
</body>
</html>
