<?php
session_start();
$_SESSION['user'] = 'admin';
$_SESSION['role'] = 'admin';

include('db.php');
require_once('auto_populate.php');

$base_dir = realpath(__DIR__ . '/../timetable-generator');
$assignments = $_SESSION['assignments'] ?? [];

echo "Data Status:\n";
echo "- Assignments: " . count($assignments) . "\n";
echo "- Classrooms: " . count($_SESSION['classrooms'] ?? []) . "\n";
echo "- Groups: " . count($_SESSION['groups'] ?? []) . "\n";
echo "- Base dir: $base_dir\n";

// Test Python API call
if (!empty($assignments)) {
    $rooms_by_type = [];
    foreach ($_SESSION['classrooms'] ?? [] as $classroom) {
        $type = $classroom['type'] ?? 'LectureHall';
        if (!isset($rooms_by_type[$type])) $rooms_by_type[$type] = [];
        $rooms_by_type[$type][] = $classroom['name'];
    }

    $classes = [];
    foreach ($assignments as $assignment) {
        $classes[] = [
            'Nastavnik' => $assignment['t_name'],
            'Predmet' => $assignment['s_name'],
            'Grupe' => [$assignment['g_name'] ?? 'Default'],
            'Tip' => 'P',
            'Trajanje' => 2,
            'Ucionica' => 'LectureHall'
        ];
    }

    $input_file = "$base_dir/input.json";
    file_put_contents($input_file, json_encode(['Casovi' => $classes, 'Ucionice' => $rooms_by_type]));

    echo "\nCalling Python API...\n";
    $cmd = "cd \"$base_dir\" && python api.py input.json 2>&1";
    $output = shell_exec($cmd);

    $result = json_decode($output, true);
    if ($result && isset($result['schedule'])) {
        echo "✓ SUCCESS! Generated timetable with " . count($result['schedule']) . " days\n";
        if (isset($result['statistics'])) {
            echo "  - Hard constraints: " . $result['statistics']['hard_constraints'] . "%\n";
            echo "  - Soft constraints: " . $result['statistics']['soft_constraints'] . "%\n";
        }
    } else {
        echo "✗ FAILED!\n";
        echo "Output: " . substr($output, 0, 200) . "\n";
    }
}
?>
