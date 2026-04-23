<?php
session_start();

// Skip auto_populate, use pre-computed test data
$_SESSION['assignments'] = array(
    ['t_name' => 'Dr. K. Suresh', 's_name' => 'Data Structures', 'g_name' => 'B.Tech CSE-A (Year 1)'],
    ['t_name' => 'Ms. G. Ramesh', 's_name' => 'Database Systems', 'g_name' => 'B.Tech CSE-B (Year 2)'],
    ['t_name' => 'Prof. M. Lakshmi', 's_name' => 'Operating Systems', 'g_name' => 'B.Tech CSE-A (Year 3)'],
);

$_SESSION['classrooms'] = array(
    ['type' => 'LectureHall', 'name' => 'Room A101'],
    ['type' => 'LectureHall', 'name' => 'Room B101'],
    ['type' => 'LectureHall', 'name' => 'Room C101'],
);

$base_dir = realpath(__DIR__ . '/../timetable-generator');
echo "Base dir: $base_dir<br>";

// Generate input.json
$assignments = $_SESSION['assignments'];
$classrooms = $_SESSION['classrooms'];

$rooms_by_type = [];
foreach($classrooms as $cr) { 
    $rooms_by_type[$cr['type']][] = $cr['name']; 
}

$json_data = ["Casovi" => [], "Ucionice" => $rooms_by_type];
foreach ($assignments as $a) {
    $json_data["Casovi"][] = [
        "Nastavnik" => $a['t_name'], 
        "Predmet" => $a['s_name'], 
        "Grupe" => [$a['g_name']], 
        "Tip" => "P", 
        "Trajanje" => 2, 
        "Ucionica" => "LectureHall"
    ];
}

$input_file = "$base_dir/input.json";
file_put_contents($input_file, json_encode($json_data, JSON_PRETTY_PRINT));
echo "Input file created: " . (file_exists($input_file) ? "Yes" : "No") . "<br>";
echo "Input file size: " . filesize($input_file) . " bytes<br>";

// Try running Python directly with simple command
echo "Testing Python directly...<br>";
$test_cmd = "python -c \"print('Python works')\"";
$test_result = shell_exec($test_cmd);
echo "Result: " . htmlspecialchars($test_result) . "<br>";

// Now try the API
echo "Running API...<br>";
$cmd = "cd \"$base_dir\" && python api.py input.json 2>&1";
echo "Command: $cmd<br>";

$start = microtime(true);
$output = shell_exec($cmd);
$elapsed = microtime(true) - $start;

echo "Elapsed: $elapsed seconds<br>";
echo "Output length: " . strlen($output) . " bytes<br>";

if (!empty($output)) {
    $json_data = json_decode($output, true);
    if ($json_data && isset($json_data['statistics'])) {
        echo "<h2>✓ SUCCESS - Timetable Generated</h2>";
        echo "<pre>";
        print_r($json_data['statistics']);
        echo "</pre>";
    } else {
        echo "JSON Parse Error<br>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 300)) . "</pre>";
    }
} else {
    echo "No output<br>";
}
?>
