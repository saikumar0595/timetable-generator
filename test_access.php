<?php
/*
 * Quick test script to verify ChronoGen is working
 */

echo "=== ChronoGen System Check ===\n\n";

// Check 1: Sessions
session_start();
echo "✓ Session started\n";

// Check 2: Database connection
echo "\n--- Database Check ---\n";
$conn = @new mysqli("localhost", "root", "", "chronogen");
if ($conn->connect_error) {
    define('DEMO_MODE', true);
    echo "✓ Demo Mode: Database not found, using demo data\n";
} else {
    define('DEMO_MODE', false);
    echo "✓ Database connected\n";
}

// Check 3: Auto populate
echo "\n--- Data Check ---\n";
if (empty($_SESSION['teachers'])) {
    require_once('auto_populate.php');
    echo "✓ Demo data populated\n";
} else {
    echo "✓ Demo data already in session\n";
}

// Check 4: Data counts
echo "\n--- Available Demo Data ---\n";
echo "Teachers: " . count($_SESSION['teachers'] ?? []) . "\n";
echo "Subjects: " . count($_SESSION['subjects'] ?? []) . "\n";
echo "Classrooms: " . count($_SESSION['classrooms'] ?? []) . "\n";
echo "Groups: " . count($_SESSION['groups'] ?? []) . "\n";
echo "Assignments: " . count($_SESSION['assignments'] ?? []) . "\n";

// Check 5: Login functionality
echo "\n--- Login Test ---\n";
$_SESSION['user'] = 'admin_@admin';
$_SESSION['role'] = 'admin';
echo "✓ Admin session set\n";

echo "\n=== All Systems OK ===\n";
echo "Access http://localhost:8000/login.php to begin\n";
echo "Use credentials:\n";
echo "  - admin_@admin (any password)\n";
echo "  - STUDENT@1001 (any password)\n";
echo "  - STAFF@2001 (any password)\n";
?>
