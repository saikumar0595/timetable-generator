<?php
/**
 * Direct Timetable Generator Test
 * Bypasses login to test timetable generation
 */

session_start();
include('db.php');

// Force admin session
$_SESSION['user'] = 'test_admin@system';
$_SESSION['role'] = 'admin';

// Get assignment data
if (empty($_SESSION['teachers'])) {
    require_once('auto_populate.php');
}

$assignments = $_SESSION['assignments'] ?? [];
$classrooms_data = $_SESSION['classrooms'] ?? [];

// Prepare input for Python
$rooms_by_type = [];
foreach($classrooms_data as $cr) { 
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

$base_dir = realpath(__DIR__ . '/../timetable-generator');
file_put_contents($base_dir . '/input.json', json_encode($json_data, JSON_PRETTY_PRINT));

// Call Python API
$python_script = 'api.py';
$cmd = "python \"$python_script\" input.json";

$process = proc_open($cmd, [1 => ["pipe", "w"], 2 => ["pipe", "w"]], $pipes, $base_dir);
if (is_resource($process)) {
    $output = stream_get_contents($pipes[1]);
    $error_output = stream_get_contents($pipes[2]);
    fclose($pipes[1]); 
    fclose($pipes[2]);
    proc_close($process);

    $res = json_decode($output, true);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Timetable Generator Test</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { background: #0f172a; color: white; }
            .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="text-4xl font-bold mb-8">✓ Timetable Generation Test</h1>
            
            <?php if ($res && isset($res['schedule'])): ?>
                <div class="bg-green-500/20 border border-green-500 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-green-400">✓ SUCCESS</h2>
                    <p>Timetable generated with <?php echo count(array_merge(...array_map('array_values', array_values($res['schedule'])))); ?> total slots</p>
                </div>
                
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-slate-800 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Statistics</h3>
                        <p>Hard Constraints: <span class="text-green-400"><?php echo $res['statistics']['hard_constraints']; ?>%</span></p>
                        <p>Soft Constraints: <span class="text-blue-400"><?php echo $res['statistics']['soft_constraints']; ?>%</span></p>
                        <p>Classes Assigned: <span class="text-indigo-400"><?php echo $res['statistics']['classes_assigned']; ?>/<?php echo $res['statistics']['total_classes']; ?></span></p>
                    </div>
                    
                    <div class="bg-slate-800 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Schedule Summary</h3>
                        <p>Days: <?php echo count($res['schedule']); ?></p>
                        <p>Total Slots: <?php echo count(array_merge(...array_map('array_values', array_values($res['schedule'])))); ?></p>
                        <p>Input Classes: <?php echo count($json_data['Casovi']); ?></p>
                    </div>
                </div>
                
                <h3 class="text-2xl font-bold mb-4">Sample Schedule (Monday)</h3>
                <div class="bg-slate-800 rounded-lg overflow-hidden">
                    <?php if (isset($res['schedule']['Monday'])): ?>
                        <table class="w-full">
                            <thead>
                                <tr class="bg-indigo-600">
                                    <th class="p-3 text-left">Time</th>
                                    <th class="p-3 text-left">Subject</th>
                                    <th class="p-3 text-left">Teacher</th>
                                    <th class="p-3 text-left">Room</th>
                                    <th class="p-3 text-left">Groups</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($res['schedule']['Monday'] as $time => $classes): ?>
                                    <?php foreach ($classes as $class): ?>
                                        <tr class="border-b border-slate-700">
                                            <td class="p-3"><?php echo $time; ?></td>
                                            <td class="p-3"><?php echo $class['subject']; ?></td>
                                            <td class="p-3"><?php echo $class['teacher']; ?></td>
                                            <td class="p-3"><?php echo $class['room']; ?></td>
                                            <td class="p-3 text-sm"><?php echo implode(", ", $class['groups']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="mt-8">
                    <a href="/login.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-500">
                        → Go to Main Application
                    </a>
                </div>
                
            <?php else: ?>
                <div class="bg-red-500/20 border border-red-500 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-red-400">✗ FAILED</h2>
                    <p class="mt-2">Output: <pre class="bg-slate-900 p-4 rounded mt-2 overflow-auto"><?php echo htmlspecialchars($output); ?></pre></p>
                    <p class="mt-2">Error: <pre class="bg-slate-900 p-4 rounded mt-2 overflow-auto"><?php echo htmlspecialchars($error_output); ?></pre></p>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Failed to start process";
}
?>
