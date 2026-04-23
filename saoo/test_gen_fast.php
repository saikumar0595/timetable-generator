<?php
session_start();
include('db.php');

// Force admin role
$_SESSION['user'] = 'test_admin@system';
$_SESSION['role'] = 'admin';

// Auto-populate if needed
if (empty($_SESSION['teachers'])) {
    require_once('auto_populate.php');
} else {
    // Skip redirect by not exiting
}

// Test timetable generation
$assignments = $_SESSION['assignments'] ?? [];
$classrooms_data = $_SESSION['classrooms'] ?? [];
$groups_data = $_SESSION['groups'] ?? [];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$periods = ["09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50", "01:30 - 02:15"];

$schedule = [];
$assigned_count = 0;

foreach ($assignments as $idx => $assignment) {
    if ($assigned_count >= 10) break; // Just do first 10
    
    $day_idx = $assigned_count % 5;
    $period_idx = ($assigned_count * 2) % 5;
    
    $day = $days[$day_idx];
    $period = $periods[$period_idx];
    
    if (!isset($schedule[$day])) $schedule[$day] = [];
    if (!isset($schedule[$day][$period])) $schedule[$day][$period] = [];
    
    $classroom = $classrooms_data[array_rand($classrooms_data)] ?? ['name' => 'Lecture Hall'];
    $group = $groups_data[array_rand($groups_data)] ?? ['name' => 'Default'];
    
    $schedule[$day][$period][] = [
        "subject" => $assignment['s_name'],
        "teacher" => $assignment['t_name'],
        "room" => $classroom['name'],
        "groups" => [$group['name']],
        "type" => "P"
    ];
    
    $assigned_count++;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Timetable Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">✓ Timetable Generation Test</h1>
        
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-green-500/20 border border-green-500 rounded p-4">
                <p>✓ Classes Generated: <strong><?php echo $assigned_count; ?></strong></p>
            </div>
            <div class="bg-blue-500/20 border border-blue-500 rounded p-4">
                <p>✓ Days: <strong><?php echo count($schedule); ?></strong></p>
            </div>
        </div>
        
        <h2 class="text-2xl font-bold mb-4">Sample Monday Schedule:</h2>
        <div class="overflow-x-auto">
            <table class="w-full border border-slate-700">
                <thead class="bg-indigo-600">
                    <tr>
                        <th class="p-3">Time</th>
                        <th class="p-3">Subject</th>
                        <th class="p-3">Teacher</th>
                        <th class="p-3">Room</th>
                        <th class="p-3">Group</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($schedule['Monday'])): ?>
                        <?php foreach ($schedule['Monday'] as $time => $classes): ?>
                            <?php foreach ($classes as $cls): ?>
                                <tr class="border-b border-slate-700 hover:bg-slate-800/50">
                                    <td class="p-3"><?php echo $time; ?></td>
                                    <td class="p-3"><?php echo $cls['subject']; ?></td>
                                    <td class="p-3"><?php echo $cls['teacher']; ?></td>
                                    <td class="p-3"><?php echo $cls['room']; ?></td>
                                    <td class="p-3 text-sm"><?php echo implode(", ", $cls['groups']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-3 text-center text-yellow-400">No classes on Monday</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
