<?php
// Audisankara University - Logic-Verified Institutional Dataset (R20) - ZERO FREE HOUR EDITION

// 1. Logic-Based Teacher Pool (124 Specialized Faculty)
$teachers = [];
$tid = 1;
$depts_config = [
    'H&S' => 30, 'CSE' => 20, 'ECE' => 16, 'EEE' => 12, 'MECH' => 12,
    'AI' => 10, 'ML' => 8, 'DS' => 8, 'Cyber Security' => 8
];

$firstNames = ['Dr. K.', 'Dr. P.', 'Prof. M.', 'Dr. B.', 'Ms. S.', 'Mr. R.', 'Dr. V.', 'Prof. A.', 'Dr. N.', 'Ms. G.', 'Dr. J.', 'Prof. H.'];
$lastNames = ['Suresh', 'Ramesh', 'Lakshmi', 'Venkat', 'Anitha', 'Prasad', 'Krishna', 'Priya', 'Reddy', 'Srinivas', 'Murali', 'Karthik', 'Rao', 'Naidu', 'Chowdary'];

foreach ($depts_config as $dept => $count) {
    for ($i = 0; $i < $count; $i++) {
        $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        $teachers[] = [
            'id' => $tid, 'univ_id' => 'STAFF@' . (1000 + $tid), 'name' => $name, 'dept' => $dept,
            'role' => ($i % 4 == 0 ? 'Professor' : 'Asst. Professor') . " ($dept)",
            'email' => strtolower(str_replace([' ', '.'], '', $name)) . rand(10,99) . "@audisankara.ac.in",
            'qualification' => ($i % 4 == 0 ? 'Ph.D' : 'M.Tech'), 'experience' => rand(5, 28) . ' Years',
            'photo' => "https://i.pravatar.cc/150?u=tid" . $tid
        ];
        $tid++;
    }
}

// 2. Expanded R20 Subject Catalog (Increased for No-Free-Hours)
$subjects = [
    ['id' => 1, 'name' => 'Linear Algebra and Calculus', 'dept' => 'H&S', 'year' => 1],
    ['id' => 2, 'name' => 'Applied Physics', 'dept' => 'H&S', 'year' => 1],
    ['id' => 3, 'name' => 'Communicative English', 'dept' => 'H&S', 'year' => 1],
    ['id' => 4, 'name' => 'Engineering Chemistry', 'dept' => 'H&S', 'year' => 1],
    ['id' => 5, 'name' => 'Problem Solving & Programming', 'dept' => 'CSE', 'year' => 1],
    ['id' => 6, 'name' => 'Data Structures', 'dept' => 'CSE', 'year' => 2],
    ['id' => 7, 'name' => 'Database Systems', 'dept' => 'CSE', 'year' => 2],
    ['id' => 8, 'name' => 'Operating Systems', 'dept' => 'CSE', 'year' => 3],
    ['id' => 9, 'name' => 'Computer Networks', 'dept' => 'CSE', 'year' => 3],
    ['id' => 10, 'name' => 'Software Engineering', 'dept' => 'CSE', 'year' => 2],
    ['id' => 11, 'name' => 'Design & Analysis of Algorithms', 'dept' => 'CSE', 'year' => 3],
    ['id' => 12, 'name' => 'Network Analysis', 'dept' => 'ECE', 'year' => 2],
    ['id' => 13, 'name' => 'Digital Logic Design', 'dept' => 'ECE', 'year' => 2],
    ['id' => 14, 'name' => 'Analog Communications', 'dept' => 'ECE', 'year' => 3],
    ['id' => 15, 'name' => 'Signals and Systems', 'dept' => 'ECE', 'year' => 2],
    ['id' => 16, 'name' => 'Power Systems', 'dept' => 'EEE', 'year' => 3],
    ['id' => 17, 'name' => 'Electrical Machines', 'dept' => 'EEE', 'year' => 2],
    ['id' => 18, 'name' => 'Control Systems', 'dept' => 'EEE', 'year' => 3],
    ['id' => 19, 'name' => 'Thermodynamics', 'dept' => 'MECH', 'year' => 2],
    ['id' => 20, 'name' => 'Machine Design', 'dept' => 'MECH', 'year' => 3],
    ['id' => 21, 'name' => 'Fluid Mechanics', 'dept' => 'MECH', 'year' => 2],
    ['id' => 22, 'name' => 'Artificial Intelligence', 'dept' => 'AI', 'year' => 3],
    ['id' => 23, 'name' => 'Machine Learning', 'dept' => 'ML', 'year' => 4],
    ['id' => 24, 'name' => 'Data Science Foundations', 'dept' => 'DS', 'year' => 3],
    ['id' => 25, 'name' => 'Ethical Hacking', 'dept' => 'Cyber Security', 'year' => 4],
    ['id' => 26, 'name' => 'Network Security', 'dept' => 'Cyber Security', 'year' => 3],
    ['id' => 27, 'name' => 'Deep Learning', 'dept' => 'AI', 'year' => 4],
    ['id' => 28, 'name' => 'Big Data Analytics', 'dept' => 'DS', 'year' => 4],
    ['id' => 29, 'name' => 'Digital Image Processing', 'dept' => 'ECE', 'year' => 4],
    ['id' => 30, 'name' => 'VLSI Design', 'dept' => 'ECE', 'year' => 4],
    ['id' => 31, 'name' => 'Compiler Design', 'dept' => 'CSE', 'year' => 3],
    ['id' => 32, 'name' => 'Web Technologies', 'dept' => 'CSE', 'year' => 3],
    ['id' => 33, 'name' => 'Mobile Application Development', 'dept' => 'CSE', 'year' => 4],
    ['id' => 34, 'name' => 'Distributed Systems', 'dept' => 'CSE', 'year' => 4],
    ['id' => 35, 'name' => 'Computer Graphics', 'dept' => 'CSE', 'year' => 3],
    ['id' => 36, 'name' => 'Digital Signal Processing', 'dept' => 'ECE', 'year' => 3],
    ['id' => 37, 'name' => 'Microprocessors & Microcontrollers', 'dept' => 'ECE', 'year' => 3],
    ['id' => 38, 'name' => 'Embedded Systems', 'dept' => 'ECE', 'year' => 4],
    ['id' => 39, 'name' => 'Antenna & Wave Propagation', 'dept' => 'ECE', 'year' => 3],
    ['id' => 40, 'name' => 'Satellite Communications', 'dept' => 'ECE', 'year' => 4],
    ['id' => 41, 'name' => 'Utilization of Electrical Energy', 'dept' => 'EEE', 'year' => 4],
    ['id' => 42, 'name' => 'Power Electronics', 'dept' => 'EEE', 'year' => 3],
    ['id' => 43, 'name' => 'Renewable Energy Sources', 'dept' => 'EEE', 'year' => 4],
    ['id' => 44, 'name' => 'Electrical Measurements', 'dept' => 'EEE', 'year' => 2],
    ['id' => 45, 'name' => 'Heat Transfer', 'dept' => 'MECH', 'year' => 3],
    ['id' => 46, 'name' => 'CAD/CAM', 'dept' => 'MECH', 'year' => 4],
    ['id' => 47, 'name' => 'Robotics', 'dept' => 'MECH', 'year' => 4],
    ['id' => 48, 'name' => 'Automobile Engineering', 'dept' => 'MECH', 'year' => 4],
    ['id' => 49, 'name' => 'Natural Language Processing', 'dept' => 'AI', 'year' => 4],
    ['id' => 50, 'name' => 'Reinforcement Learning', 'dept' => 'ML', 'year' => 4],
    ['id' => 51, 'name' => 'Cloud Computing', 'dept' => 'CSE', 'year' => 4],
    ['id' => 52, 'name' => 'Information Security', 'dept' => 'Cyber Security', 'year' => 4]
];

// 3. 88 Groups & Alphanumeric Classrooms
$groups = []; $classrooms = []; $gid = 1; $cid = 1;
$branch_config = [
    'CSE' => ['A', 'B', 'C'], 'ECE' => ['A', 'B', 'C'], 'EEE' => ['A', 'B', 'C'], 'MECH' => ['A', 'B', 'C'],
    'AI' => ['A', 'B'], 'AI-ML' => ['A', 'B'], 'AI-DS' => ['A', 'B'], 'DS' => ['A', 'B'], 'Cyber Security' => ['A', 'B']
];
$floor_counters = ['A' => 101, 'B' => 101, 'C' => 101, 'D' => 101];

foreach ($branch_config as $branch => $sections) {
    foreach ($sections as $sec) {
        for ($y = 1; $y <= 4; $y++) {
            $group_name = "B.Tech $branch-$sec (Year $y)";
            $floor = ['A', 'B', 'C', 'D'][$y-1];
            $room_num = $floor . ($floor_counters[$floor]++);
            
            $groups[] = ['id' => $gid++, 'name' => $group_name, 'branch' => $branch, 'year' => $y, 'room' => "Room $room_num"];
            
            $classrooms[] = [
                'id' => $cid++, 'name' => "Room $room_num", 'desc' => "$branch-$sec Home Room", 'type' => 'LectureHall',
                'capacity' => (in_array($branch, ['CSE', 'ECE', 'EEE', 'MECH']) ? 65 : 60)
            ];
        }
    }
}

// 4. Assignments (INCREASED LOAD: 7 subjects per group)
$assignments = []; $aid = 1;
foreach ($groups as $g) {
    $year = $g['year']; $branch = $g['branch'];
    
    $targets = array_filter($subjects, function($s) use ($branch, $year) {
        if ($year == 1) return $s['year'] == 1;
        $mapped_dept = (strpos($branch, 'AI') !== false) ? 'AI' : $branch;
        return ($s['dept'] == $mapped_dept || $s['dept'] == 'CSE' || $s['dept'] == 'H&S') && $s['year'] <= $year;
    });
    
    shuffle($targets);
    $yearSubjects = array_slice($targets, 0, 7); // FULL DAY COVERAGE
    if(count($yearSubjects) < 7) $yearSubjects = array_slice($subjects, 0, 7);

    foreach ($yearSubjects as $sub) {
        $valid_teachers = array_filter($teachers, function($t) use ($sub) { return $t['dept'] == $sub['dept']; });
        if(empty($valid_teachers)) $valid_teachers = $teachers;
        $t = $valid_teachers[array_rand($valid_teachers)];
        $assignments[] = [
            'id' => $aid++, 't_id' => $t['id'], 't_name' => $t['name'],
            's_name' => $sub['name'], 'g_name' => $g['name'], 'g_room' => $g['room']
        ];
    }
}

$_SESSION['teachers'] = $teachers;
$_SESSION['subjects'] = $subjects;
$_SESSION['groups'] = $groups;
$_SESSION['classrooms'] = $classrooms;
$_SESSION['assignments'] = $assignments;

// 5. AUTO-PREPARE AI INPUT
$base_dir = realpath(__DIR__ . '/../timetable-generator');
if ($base_dir) {
    $rooms_by_type = [];
    foreach($classrooms as $cr) { $rooms_by_type[$cr['type']][] = $cr['name']; }
    
    $json_data = ["Casovi" => [], "Ucionice" => $rooms_by_type];
    foreach ($assignments as $a) {
        $json_data["Casovi"][] = [
            "Nastavnik" => $a['t_name'], "Predmet" => $a['s_name'], 
            "Grupe" => [$a['g_name']], "Tip" => "P", "Trajanje" => 2, "Ucionica" => "LectureHall"
        ];
    }
    file_put_contents($base_dir . '/input.json', json_encode($json_data, JSON_PRETTY_PRINT));
}

$_SESSION['flash_message'] = "University Dataset Populated: " . count($teachers) . " Faculty, " . count($groups) . " Groups Ready!";
header("Location: index.php");
exit();
?>