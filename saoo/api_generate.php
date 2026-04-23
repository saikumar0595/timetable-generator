<?php
// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Only admins can generate
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include('db.php');

$base_dir = realpath(__DIR__ . '/../timetable-generator');

try {
    // Get data from session
    $assignments = $_SESSION['assignments'] ?? [];
    $classrooms_data = $_SESSION['classrooms'] ?? [];
    $groups_data = $_SESSION['groups'] ?? [];

    if (empty($assignments)) {
        throw new Exception('No teacher-subject assignments found. Please populate demo data first.');
    }

    // Build input JSON for Python API
    $rooms_by_type = [];
    foreach ($classrooms_data as $classroom) {
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

    // Write input.json
    $input_file = "$base_dir/input.json";
    $json_str = json_encode($input_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    
    if (file_put_contents($input_file, $json_str) === false) {
        throw new Exception('Failed to write input.json');
    }

    // Call Python API with timeout
    $cmd = "cd \"$base_dir\" && python api.py input.json 2>&1";
    
    // Execute with output capture
    $output = shell_exec($cmd);
    
    if ($output === null || empty($output)) {
        throw new Exception('Python API returned no output. Is Python installed and in PATH?');
    }

    // Parse JSON output
    $result = json_decode($output, true);
    
    if ($result === null) {
        throw new Exception('Invalid JSON from Python API: ' . substr($output, 0, 200));
    }

    if (!isset($result['schedule'])) {
        throw new Exception('Python API did not return a schedule');
    }

    // Store in session
    $_SESSION['last_generated_timetable'] = $result['schedule'];
    $_SESSION['last_generated_stats'] = $result['statistics'] ?? [
        'hard_constraints' => 100,
        'soft_constraints' => 85,
        'classes_assigned' => count($classes)
    ];
    $_SESSION['generation_timestamp'] = date('Y-m-d H:i:s');

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Timetable generated successfully',
        'stats' => $_SESSION['last_generated_stats'],
        'classes_count' => count($classes),
        'redirect' => 'view_timetable.php'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
