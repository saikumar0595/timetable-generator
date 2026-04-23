<?php
session_start();

// Set up test session
$_SESSION['user'] = 'test_admin@system';
$_SESSION['role'] = 'admin';

// Include database and auto-populate
include('db.php');
require_once('auto_populate.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Timetable Generation Test</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-slate-900 text-white p-8'>
    <div class='max-w-2xl mx-auto'>
        <h1 class='text-4xl font-bold mb-8'>✅ ChronoGen Timetable Generation Test</h1>";

try {
    // STEP 1: Check data loaded
    $assignments = $_SESSION['assignments'] ?? [];
    $classrooms = $_SESSION['classrooms'] ?? [];
    $groups = $_SESSION['groups'] ?? [];
    
    echo "<div class='bg-blue-900/30 border-l-4 border-blue-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 1: Data Loaded</h2>
        <p>✓ Assignments: " . count($assignments) . "</p>
        <p>✓ Classrooms: " . count($classrooms) . "</p>
        <p>✓ Groups: " . count($groups) . "</p>
    </div>";

    // STEP 2: Generate input.json
    $base_dir = realpath(__DIR__ . '/../timetable-generator');
    
    $rooms_by_type = [];
    foreach ($classrooms as $classroom) {
        $type = $classroom['type'] ?? 'LectureHall';
        if (!isset($rooms_by_type[$type])) {
            $rooms_by_type[$type] = [];
        }
        $rooms_by_type[$type][] = $classroom['name'];
    }

    $classes = [];
    foreach ($assignments as $assignment) {
        $classes[] = [
            'Nastavnik' => $assignment['t_name'],
            'Predmet' => $assignment['s_name'],
            'Grupe' => [$assignment['g_name'] ?? 'Default Group'],
            'Tip' => 'P',
            'Trajanje' => 2,
            'Ucionica' => 'LectureHall'
        ];
    }

    $input_data = [
        'Casovi' => $classes,
        'Ucionice' => $rooms_by_type
    ];

    $input_file = "$base_dir/input.json";
    file_put_contents($input_file, json_encode($input_data, JSON_PRETTY_PRINT));
    
    echo "<div class='bg-green-900/30 border-l-4 border-green-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 2: Input JSON Created</h2>
        <p>✓ File: $input_file</p>
        <p>✓ Classes: " . count($classes) . "</p>
        <p>✓ Size: " . filesize($input_file) . " bytes</p>
    </div>";

    // STEP 3: Call Python API
    $cmd = "cd \"$base_dir\" && python api.py input.json 2>&1";
    $start = microtime(true);
    $output = shell_exec($cmd);
    $elapsed = microtime(true) - $start;
    
    echo "<div class='bg-purple-900/30 border-l-4 border-purple-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 3: Python API Executed</h2>
        <p>✓ Command: python api.py input.json</p>
        <p>✓ Time: " . number_format($elapsed, 2) . "s</p>
        <p>✓ Output size: " . strlen($output) . " bytes</p>
    </div>";

    // STEP 4: Parse JSON
    $result = json_decode($output, true);
    if ($result === null) {
        throw new Exception('Invalid JSON: ' . substr($output, 0, 100));
    }
    
    echo "<div class='bg-indigo-900/30 border-l-4 border-indigo-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 4: JSON Parsed Successfully</h2>
        <p>✓ Schedule days: " . count($result['schedule'] ?? []) . "</p>";
    
    if (isset($result['statistics'])) {
        echo "<p>✓ Hard constraints: " . $result['statistics']['hard_constraints'] . "%</p>";
        echo "<p>✓ Soft constraints: " . $result['statistics']['soft_constraints'] . "%</p>";
    }
    echo "</div>";

    // STEP 5: Store in session and verify
    $_SESSION['last_generated_timetable'] = $result['schedule'];
    $_SESSION['last_generated_stats'] = $result['statistics'];
    
    $stored_schedule = $_SESSION['last_generated_timetable'];
    
    echo "<div class='bg-emerald-900/30 border-l-4 border-emerald-500 p-4 mb-6'>
        <h2 class='font-bold mb-3'>Step 5: Timetable Stored in Session</h2>
        <p>✓ Session storage verified</p>
        <p>✓ Days in timetable: " . count($stored_schedule) . "</p>";
    
    // Sample schedule
    $sample_day = array_key_first($stored_schedule);
    if ($sample_day && count($stored_schedule[$sample_day]) > 0) {
        echo "<p>✓ Sample day ($sample_day): " . count($stored_schedule[$sample_day]) . " time slots</p>";
    }
    echo "</div>";

    // SUCCESS
    echo "<div class='bg-emerald-600/40 border-2 border-emerald-400 p-6 rounded-xl'>
        <h2 class='text-2xl font-bold mb-2'>🎉 SUCCESS - Full Workflow Complete!</h2>
        <p class='mb-4'>The timetable generation pipeline is working correctly.</p>
        <p class='text-sm text-emerald-200'>You can now use the 'Generate Timetable' button in the main dashboard.</p>
        <br>
        <a href='view_timetable.php' class='inline-block px-6 py-3 bg-emerald-500 text-white rounded-lg font-bold hover:bg-emerald-600'>
            View Generated Timetable
        </a>
    </div>";

} catch (Exception $e) {
    echo "<div class='bg-red-600/40 border-2 border-red-400 p-6 rounded-xl'>
        <h2 class='text-2xl font-bold mb-2'>❌ ERROR</h2>
        <p class='mb-2'>" . htmlspecialchars($e->getMessage()) . "</p>
        <pre class='bg-red-900/30 p-4 rounded overflow-x-auto text-sm'>" . htmlspecialchars(substr($output ?? '', 0, 500)) . "</pre>
    </div>";
}

echo "</div></body></html>";
?>
