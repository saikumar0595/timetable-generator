<?php
// AUDISANKARA TEACHERS GOLE - Logic-Verified Institutional Dataset (R20) - ZERO FREE HOUR EDITION

// 1. Logic-Based Teacher Pool (400+ Specialized Faculty)
$teachers = [];
$tid = 1;
$depts_config = [
    'H&S' => 100, 'CSE' => 180, 'ECE' => 60, 'EEE' => 40, 'MECH' => 40,
    'AI' => 20, 'ML' => 20, 'DS' => 20, 'Cyber Security' => 20
];

$femaleTitles = ['Dr. K.', 'Dr. P.', 'Prof. M.', 'Ms. S.', 'Dr. V.', 'Prof. A.', 'Ms. G.', 'Dr. J.', 'Prof. H.', 'Dr. S.', 'Ms. L.'];
$maleTitles = ['Dr. K.', 'Dr. P.', 'Prof. M.', 'Dr. B.', 'Mr. R.', 'Dr. V.', 'Prof. A.', 'Dr. N.', 'Dr. J.', 'Prof. H.', 'Dr. T.', 'Mr. V.'];
$lastNames = ['Suresh', 'Ramesh', 'Lakshmi', 'Venkat', 'Anitha', 'Prasad', 'Krishna', 'Priya', 'Reddy', 'Srinivas', 'Murali', 'Karthik', 'Rao', 'Naidu', 'Chowdary', 'Kumar', 'Varma', 'Patel', 'Sharma', 'Iyer'];

foreach ($depts_config as $dept => $count) {
    for ($i = 0; $i < $count; $i++) {
        $gender = (rand(0, 1) == 0) ? 'female' : 'male';
        $titles = ($gender == 'female') ? $femaleTitles : $maleTitles;
        $name = $titles[array_rand($titles)] . ' ' . $lastNames[array_rand($lastNames)];
        
        $photo_id = rand(1, 95);
        $photo_url = ($gender == 'male') 
            ? "https://randomuser.me/api/portraits/men/{$photo_id}.jpg" 
            : "https://randomuser.me/api/portraits/women/{$photo_id}.jpg";

        $teachers[] = [
            'id' => $tid, 'univ_id' => 'STAFF@' . (1000 + $tid), 'name' => $name . " ($tid)", 'dept' => $dept,
            'role' => ($i % 4 == 0 ? 'Professor' : 'Asst. Professor') . " ($dept)",
            'email' => strtolower(str_replace([' ', '.'], '', $name)) . $tid . "@audisankara.ac.in",
            'phone' => (rand(7,9) . rand(100,999) . rand(100,999) . rand(100,999)),
            'qualification' => ($i % 4 == 0 ? 'Ph.D' : 'M.Tech'), 'experience' => rand(5, 28) . ' Years',
            'photo' => $photo_url
        ];
        $tid++;
    }
}

// 2. Comprehensive B.Tech Subject Catalog (500+ Subjects)
$famous_courses = [
    'General Relativity', 'Classical Mechanics', 'Quantum Electrodynamics', 'Theory of Everything',
    'Natural Selection', 'Turing Completeness', 'Radiological Science', 'Alternating Current Systems',
    'Black Hole Thermodynamics', 'Principles of Chemistry', 'Genetic Inheritance', 'Universal Gravitation',
    'Wave Mechanics', 'Nuclear Fission', 'Penicillin & Microbiology', 'DNA Double Helix Structure',
    'Evolutionary Biology', 'Astrobiology', 'Cosmology', 'Quantum Computing', 'Artificial Intelligence',
    'Cybernetics', 'Calculus I', 'Calculus II', 'Calculus III', 'Differential Equations',
    'Linear Algebra', 'Number Theory', 'Geometry', 'Trigonometry', 'Optics', 'Thermodynamics',
    'Electromagnetism', 'Special Relativity', 'Fluid Dynamics', 'Particle Physics',
    'String Theory', 'Information Theory', 'Game Theory', 'Complexity Theory',
    'Compiler Construction', 'Operating Systems Design', 'Database Internals', 'Distributed Systems',
    'Neural Networks', 'Deep Learning', 'Computer Vision', 'Robotics Engineering',
    'Nanotechnology', 'Biotechnology', 'Astronomy & Astrophysics'
];

$subjects_data = [
    'H&S' => [
        1 => [
            'Engineering Mathematics I', 'Engineering Physics', 'Engineering Chemistry', 
            'Programming for Problem Solving', 'Engineering Graphics & Design', 
            'Basic Electrical Engineering', 'Basic Electronics Engineering', 
            'Engineering Mechanics', 'English for Communication', 'Environmental Science',
            'Workshop Practice', 'Physics Lab', 'Chemistry Lab', 'Programming Lab', 
            'Basic Electrical Lab', 'Engineering Mathematics II', 'Biology for Engineers', 
            'Constitution of India', 'Design Thinking', 'Universal Human Values',
            'Soft Skills Training', 'Aptitude & Logical Reasoning', 
            'Personality Development', 'Technical Report Writing', 'English Lab'
        ]
    ],
    'CSE' => [
        2 => ['Data Structures', 'Logic Design', 'Discrete Mathematics', 'Object-Oriented Programming (Java)', 'Computer Organization', 'Data Structures Lab'],
        3 => ['Operating Systems', 'Database Management Systems', 'Software Engineering', 'Theory of Computation', 'Design and Analysis of Algorithms', 'Computer Networks', 'Operating Systems Lab', 'Database Lab', 'Algorithms Lab'],
        4 => ['Compiler Design', 'Artificial Intelligence', 'Web Technologies', 'Microprocessors and Microcontrollers', 'Machine Learning', 'Cryptography & Network Security', 'AI & ML Lab', 'Web Programming Lab', 'Networking Lab'],
        'Elective' => ['Data Warehousing & Data Mining', 'Cloud Computing', 'Cyber Security', 'Internet of Things (IoT)', 'Deep Learning', 'Big Data Analytics', 'Mobile Application Development', 'Software Testing', 'Computer Graphics', 'Distributed Systems', 'Natural Language Processing', 'Blockchain Technology', 'Human-Computer Interaction', 'Digital Image Processing', 'Parallel Computing', 'Soft Computing', 'Advanced Java', 'R Programming']
    ],
    'ECE' => [
        2 => ['Signals and Systems', 'Network Theory', 'Electronic Devices', 'Analog Circuits', 'Digital System Design', 'Analog Circuits Lab', 'Digital Electronics Lab'],
        3 => ['Electromagnetic Waves', 'Control Systems', 'Analog Communication', 'Digital Communication', 'Digital Signal Processing', 'Microprocessor Lab', 'Communication Systems Lab'],
        4 => ['VLSI Design', 'Antennas and Propagation', 'Microwave Engineering', 'Optical Fiber Communication', 'Embedded Systems', 'DSP Lab', 'Electronic Measurement & Instrumentation'],
        'Elective' => ['Wireless Communication', 'Satellite Communication', 'Radar Systems', 'Information Theory and Coding', 'Nano Electronics', 'Mixed Signal Design', 'CMOS Layout Design', 'Probability & Random Processes', 'Linear Integrated Circuits']
    ],
    'MECH' => [
        2 => ['Thermodynamics', 'Fluid Mechanics', 'Strength of Materials', 'Material Science', 'Kinematics of Machines', 'Material Testing Lab', 'Fluid Mechanics Lab'],
        3 => ['Manufacturing Processes', 'Machine Design I', 'Dynamics of Machines', 'Heat and Mass Transfer', 'Applied Thermodynamics', 'Machine Shop Lab', 'Heat Transfer Lab'],
        4 => ['Fluid Machinery', 'Machine Design II', 'Metrology & Quality Control', 'Internal Combustion Engines', 'CAD/CAM', 'Dynamics Lab', 'Power Plant Engineering'],
        'Elective' => ['Finite Element Analysis', 'Operations Research', 'Automobile Engineering', 'Refrigeration & Air Conditioning', 'Mechatronics', 'Robotics', 'Turbo Machinery', 'Industrial Engineering', 'Production Planning', 'Non-Conventional Energy Sources', 'Total Quality Management', 'Unconventional Machining', 'Mechanical Vibrations', 'Tribology']
    ],
    'EEE' => [
        2 => ['Electric Circuit Theory', 'Electrical Machines I (DC)', 'Electromagnetic Fields', 'Electrical Measurements', 'Machine Lab I'],
        3 => ['Electrical Machines II (AC)', 'Power Systems I', 'Control Systems I', 'Power Electronics', 'Machine Lab II', 'Control Systems Lab'],
        4 => ['Power Systems II', 'High Voltage Engineering', 'Switchgear & Protection', 'Digital Signal Processing (EE)', 'Electric Drives', 'Power Electronics Lab', 'Power Systems Lab'],
        'Elective' => ['Renewable Energy Systems', 'Smart Grid', 'Utilization of Electrical Energy', 'Control Systems II', 'Modern Control Theory', 'FACTS Controllers', 'Power System Operation & Control', 'Power Quality', 'Electrical Machine Design', 'Advanced Power Systems', 'Digital Protection', 'Energy Auditing']
    ]
];

$subjects = [];
$sid = 1;
foreach ($subjects_data as $dept => $years) {
    foreach ($years as $year => $list) {
        foreach ($list as $sname) {
            $subjects[] = [
                'id' => $sid++,
                'name' => $sname,
                'dept' => (in_array($dept, ['CSE', 'ECE', 'EEE', 'MECH', 'H&S']) ? $dept : 'CSE'),
                'year' => is_numeric($year) ? $year : rand(1, 4)
            ];
        }
    }
}

// Ensure 500 subjects
while(count($subjects) < 500) {
    $base_course = $famous_courses[array_rand($famous_courses)];
    $subjects[] = [
        'id' => $sid++,
        'name' => $base_course . " (M-" . $sid . ")",
        'dept' => array_rand($depts_config),
        'year' => rand(1,4)
    ];
}

// 3. 116 Groups & Alphanumeric Classrooms
$groups = []; $classrooms = []; $gid = 1; $cid = 1;
$branch_config = [
    'CSE' => ['A', 'B', 'C', 'ADV-A'], 'ECE' => ['A', 'B', 'C', 'ADV-A'], 
    'EEE' => ['A', 'B', 'ADV-A'], 'MECH' => ['A', 'B', 'ADV-A'],
    'AI' => ['A', 'B', 'ADV-A']
];
$floor_counters = ['A' => 101, 'B' => 101, 'C' => 101, 'D' => 101];

foreach ($branch_config as $branch => $sections) {
    foreach ($sections as $sec) {
        for ($y = 1; $y <= 4; $y++) {
            $group_name = "B.Tech $branch-$sec (Year $y)";
            $floor = ['A', 'B', 'C', 'D'][$y-1];
            $room_num = $floor . ($floor_counters[$floor]++);
            $groups[] = ['id' => $gid++, 'name' => $group_name, 'branch' => $branch, 'year' => $y];
            $classrooms[] = ['id' => $cid++, 'name' => "Room $room_num", 'type' => 'LectureHall', 'capacity' => 60];
        }
    }
}

// 4. Assignments (FULL 24 SLOT COVERAGE)
$assignments = []; $aid = 1;
foreach ($groups as $g) {
    $branch = $g['branch'];
    $targets = array_filter($subjects, function($s) use ($branch) {
        return $s['dept'] == $branch || $s['dept'] == 'H&S';
    });
    if(count($targets) < 24) $targets = $subjects;
    shuffle($targets);
    $group_subjects = array_slice($targets, 0, 24);

    $group_teachers_ids = [];
    foreach ($group_subjects as $sub) {
        $valid_teachers = array_filter($teachers, function($t) use ($group_teachers_ids) {
            return !in_array($t['id'], $group_teachers_ids);
        });
        if(empty($valid_teachers)) $valid_teachers = $teachers;
        $t = $valid_teachers[array_rand($valid_teachers)];
        $group_teachers_ids[] = $t['id'];
        
        $assignments[] = [
            'id' => $aid++, 't_id' => $t['id'], 't_name' => $t['name'],
            's_name' => $sub['name'], 'g_name' => $g['name']
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
?>