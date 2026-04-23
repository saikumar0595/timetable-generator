<?php
echo "<h1>Testing Timetable Gen</h1>";
echo "<p>Starting test...</p>";

$base_dir = realpath(__DIR__ . '/../timetable-generator');
$cmd = "cd \"$base_dir\" && python api.py input.json";

echo "<p>Command: $cmd</p>";
echo "<p>Working directory: $base_dir</p>";

// Try exec
$output = array();
$return_var = 0;
exec($cmd, $output, $return_var);

echo "<p>Return code: $return_var</p>";
echo "<p>Lines of output: " . count($output) . "</p>";

if (!empty($output)) {
    $json_str = implode("\n", $output);
    $data = json_decode($json_str, true);
    
    if ($data) {
        echo "<h2>✓ SUCCESS - Timetable Generated</h2>";
        echo "<pre>";
        print_r($data['statistics']);
        echo "</pre>";
    } else {
        echo "<h2>✗ Failed to parse JSON</h2>";
        echo "<pre>" . htmlspecialchars(substr($json_str, 0, 500)) . "</pre>";
    }
} else {
    echo "<h2>✗ No output from command</h2>";
}
?>
