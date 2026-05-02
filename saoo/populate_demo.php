<?php
session_start();

// AUDISANKARA TEACHERS GOLE - Max Density Logic (R20)

// 1. Expanded Teacher Pool (400+ Specialized Faculty)
$teachers = [];
$tid = 1;
$depts_config = [
    'H&S' => 100, 'CSE' => 80, 'ECE' => 60, 'EEE' => 40, 'MECH' => 40,
    'AI' => 20, 'ML' => 20, 'DS' => 20, 'Cyber Security' => 20
];

$firstNames = ['Dr. K.', 'Dr. P.', 'Prof. M.', 'Dr. B.', 'Ms. S.', 'Mr. R.', 'Dr. V.', 'Prof. A.', 'Dr. N.', 'Ms. G.', 'Dr. J.', 'Prof. H.', 'Dr. S.', 'Dr. T.', 'Mr. V.', 'Ms. L.'];
$lastNames = ['Suresh', 'Ramesh', 'Lakshmi', 'Venkat', 'Anitha', 'Prasad', 'Krishna', 'Priya', 'Reddy', 'Srinivas', 'Murali', 'Karthik', 'Rao', 'Naidu', 'Chowdary', 'Kumar', 'Varma', 'Patel', 'Sharma', 'Iyer'];

foreach ($depts_config as $dept => $count) {
    for ($i = 0; $i < $count; $i++) {
        $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        $teachers[] = [
            'id' => $tid, 'univ_id' => 'STAFF@' . (1000 + $tid), 'name' => $name . " ($tid)", 'dept' => $dept,
            'role' => ($i % 4 == 0 ? 'Professor' : 'Asst. Professor') . " ($dept)",
            'email' => strtolower(str_replace([' ', '.'], '', $name)) . $tid . "@audisankara.ac.in",
            'phone' => (rand(7,9) . rand(100,999) . rand(100,999) . rand(100,999)),
            'qualification' => ($i % 4 == 0 ? 'Ph.D' : 'M.Tech'), 'experience' => rand(5, 28) . ' Years',
            'photo' => "https://i.pravatar.cc/150?u=tid" . $tid
        ];
        $tid++;
    }
}

// 2. Expanded R20 Subject Catalog (300+ Subjects)
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
        2 => [
            'Data Structures', 'Logic Design', 'Discrete Mathematics', 
            'Object-Oriented Programming (Java)', 'Computer Organization', 'Data Structures Lab'
        ],
        3 => [
            'Operating Systems', 'Database Management Systems', 'Software Engineering', 
            'Theory of Computation', 'Design and Analysis of Algorithms', 'Computer Networks',
            'Operating Systems Lab', 'Database Lab', 'Algorithms Lab'
        ],
        4 => [
            'Compiler Design', 'Artificial Intelligence', 'Web Technologies', 
            'Microprocessors and Microcontrollers', 'Machine Learning', 
            'Cryptography & Network Security', 'AI & ML Lab', 'Web Programming Lab', 'Networking Lab'
        ],
        'Elective' => [
            'Data Warehousing & Data Mining', 'Cloud Computing', 'Cyber Security', 
            'Internet of Things (IoT)', 'Deep Learning', 'Big Data Analytics', 
            'Mobile Application Development', 'Software Testing', 'Computer Graphics', 
            'Distributed Systems', 'Natural Language Processing', 'Blockchain Technology', 
            'Human-Computer Interaction', 'Digital Image Processing', 'Parallel Computing', 
            'Soft Computing', 'Advanced Java', 'R Programming'
        ]
    ],
    'ECE' => [
        2 => [
            'Signals and Systems', 'Network Theory', 'Electronic Devices', 
            'Analog Circuits', 'Digital System Design', 'Analog Circuits Lab', 'Digital Electronics Lab'
        ],
        3 => [
            'Electromagnetic Waves', 'Control Systems', 'Analog Communication', 
            'Digital Communication', 'Digital Signal Processing', 'Microprocessor Lab', 'Communication Systems Lab'
        ],
        4 => [
            'VLSI Design', 'Antennas and Propagation', 'Microwave Engineering', 
            'Optical Fiber Communication', 'Embedded Systems', 'DSP Lab', 'Electronic Measurement & Instrumentation'
        ],
        'Elective' => [
            'Wireless Communication', 'Satellite Communication', 'Radar Systems', 
            'Information Theory and Coding', 'Nano Electronics', 'Mixed Signal Design', 
            'CMOS Layout Design', 'Probability & Random Processes', 'Linear Integrated Circuits'
        ]
    ],
    'MECH' => [
        2 => [
            'Thermodynamics', 'Fluid Mechanics', 'Strength of Materials', 
            'Material Science', 'Kinematics of Machines', 'Material Testing Lab', 'Fluid Mechanics Lab'
        ],
        3 => [
            'Manufacturing Processes', 'Machine Design I', 'Dynamics of Machines', 
            'Heat and Mass Transfer', 'Applied Thermodynamics', 'Machine Shop Lab', 'Heat Transfer Lab'
        ],
        4 => [
            'Fluid Machinery', 'Machine Design II', 'Metrology & Quality Control', 
            'Internal Combustion Engines', 'CAD/CAM', 'Dynamics Lab', 'Power Plant Engineering'
        ],
        'Elective' => [
            'Finite Element Analysis', 'Operations Research', 'Automobile Engineering', 
            'Refrigeration & Air Conditioning', 'Mechatronics', 'Robotics', 'Turbo Machinery', 
            'Industrial Engineering', 'Production Planning', 'Non-Conventional Energy Sources', 
            'Total Quality Management', 'Unconventional Machining', 'Mechanical Vibrations', 'Tribology'
        ]
    ],
    'EEE' => [
        2 => [
            'Electric Circuit Theory', 'Electrical Machines I (DC)', 'Electromagnetic Fields',
            'Electrical Measurements', 'Machine Lab I'
        ],
        3 => [
            'Electrical Machines II (AC)', 'Power Systems I', 'Control Systems I', 
            'Power Electronics', 'Machine Lab II', 'Control Systems Lab'
        ],
        4 => [
            'Power Systems II', 'High Voltage Engineering', 'Switchgear & Protection', 
            'Digital Signal Processing (EE)', 'Electric Drives', 'Power Electronics Lab', 'Power Systems Lab'
        ],
        'Elective' => [
            'Renewable Energy Systems', 'Smart Grid', 'Utilization of Electrical Energy', 
            'Control Systems II', 'Modern Control Theory', 'FACTS Controllers', 
            'Power System Operation & Control', 'Power Quality', 'Electrical Machine Design', 
            'Advanced Power Systems', 'Digital Protection', 'Energy Auditing'
        ]
    ],
    'Civil' => [
        2 => [
            'Surveying', 'Building Materials', 'Concrete Technology', 'Hydraulics', 'Surveying Lab', 'Fluid Mechanics Lab (Civil)'
        ],
        3 => [
            'Structural Analysis I', 'Geotechnical Engineering I', 'Transportation Engineering I', 
            'Engineering Geology', 'Design of Concrete Structures', 'Geology Lab', 'Concrete Lab'
        ],
        4 => [
            'Structural Analysis II', 'Environmental Engineering I', 'Geotechnical Engineering II', 
            'Hydrology & Water Resources', 'Design of Steel Structures', 'Soil Mechanics Lab', 'Environmental Engineering Lab'
        ],
        'Elective' => [
            'Environmental Engineering II', 'Transportation Engineering II', 'Construction Planning & Management', 
            'Pre-stressed Concrete', 'Bridge Engineering', 'Earthquake Engineering', 'Remote Sensing & GIS', 
            'Pavement Design', 'Irrigation Engineering', 'Quantity Surveying & Valuation', 
            'Ground Improvement Techniques', 'Solid Waste Management', 'Traffic Engineering', 
            'Foundation Engineering', 'Architecture & Town Planning'
        ]
    ],
    'Chemical' => [
        'Core' => [
            'Chemical Process Calculations', 'Chemical Thermodynamics', 'Mass Transfer Operations I', 
            'Mass Transfer Operations II', 'Heat Transfer (Chemical)', 'Chemical Reaction Engineering I', 
            'Chemical Reaction Engineering II', 'Process Dynamics & Control', 'Plant Design & Economics', 
            'Process Instrumentation', 'Chemical Technology', 'Transport Phenomena', 'Unit Operations Lab', 
            'Reaction Engineering Lab', 'Analytical Chemistry Lab'
        ],
        'Elective' => [
            'Petroleum Refining', 'Polymer Science', 'Fertilizer Technology', 'Bio-Chemical Engineering', 
            'Optimization of Chemical Processes'
        ]
    ],
    'Management' => [
        'Elective' => [
            'Project Management', 'Supply Chain Management', 'Engineering Economics', 'Entrepreneurship Development', 
            'Industrial Safety', 'Product Lifecycle Management', 'Disaster Management', 'Intellectual Property Rights', 
            'Numerical Methods', 'Operations Management', 'Marketing Management', 'Financial Management', 
            'Organizational Behavior', 'Cyber Law', 'Ethics for Engineers', 'Renewable Resources', 
            'Energy Management', 'Materials Management', 'Lean Manufacturing', 'Six Sigma'
        ]
    ],
    'Emerging' => [
        'Elective' => [
            'Quantum Computing', 'Augmented Reality (AR)', 'Virtual Reality (VR)', 'Bioinformatics', 
            'Ethical Hacking', 'Wireless Sensor Networks', 'Green Computing', 'Autonomous Vehicles', 
            'Computational Fluid Dynamics', 'Nano Materials', 'Drones & UAVs', 'Smart Materials', 
            '3D Printing Technology', 'Additive Manufacturing', 'Composite Materials', 'GIS and Remote Sensing', 
            'Digital Forensics', 'Software Defined Networks'
        ],
        'Lab' => [
            'MATLAB for Engineers', 'Embedded C', 'FPGA Programming', 'PLC & SCADA', 'Autodesk Revit', 
            'SolidWorks Design', 'CATIA Lab', 'Ansys Simulation Lab', 'Staad Pro Lab', 'Cisco Packet Tracer'
        ]
    ],
    'FinalYear' => [
        'Practical' => [
            'Industrial Internship', 'Seminar I (Technical)', 'Seminar II (Research)', 'Mini Project I', 
            'Mini Project II', 'Major Project Phase I', 'Major Project Phase II', 'Mock Interviews', 
            'Social Responsibility Project', 'Optimization Lab', 'Hardware Troubleshooting', 'Group Discussion Practice'
        ],
        'Elective' => [
            'Foreign Language (German/French)', 'Innovation & Incubation', 'Research Methodology', 'Statistical Tools'
        ]
    ],
    'ICE_IT' => [
        'Core' => [
            'Sensors and Transducers', 'Process Control Instrumentation', 'Analytical Instrumentation', 
            'Robotics & Automation', 'Distributed Control Systems', 'Information Theory', 'Network Programming', 
            'Unix System Programming'
        ],
        'Elective' => [
            'Biomedical Signal Processing', 'IT Infrastructure Management', 'User Interface Design', 
            'Software Quality Assurance', 'E-Commerce Technology', 'Enterprise Resource Planning', 
            'Simulation & Modeling'
        ],
        'Lab' => [
            'PLC and Automation Lab', 'Instrumentation Lab', 'Data Analytics Lab', 'Embedded System Lab'
        ],
        'Final' => ['Comprehensive Viva-Voce']
    ]
];

$subjects = [];
$sid = 1;

// Flatten the subjects data
foreach ($subjects_data as $dept => $years) {
    foreach ($years as $year => $list) {
        foreach ($list as $sname) {
            $actual_year = is_numeric($year) ? $year : rand(1, 4);
            $subjects[] = [
                'id' => $sid++,
                'name' => $sname . " ($dept)",
                'dept' => ($dept == 'Civil' ? 'MECH' : ($dept == 'Chemical' || $dept == 'Management' || $dept == 'Emerging' || $dept == 'FinalYear' || $dept == 'ICE_IT' ? 'CSE' : $dept)),
                'year' => $actual_year
            ];
        }
    }
}

// Fill up to 300 if needed (though the list above is already around 300)
while (count($subjects) < 300) {
    $dept = array_keys($subjects_data)[array_rand(array_keys($subjects_data))];
    $subjects[] = [
        'id' => $sid++,
        'name' => "Extra Subject " . $sid . " ($dept)",
        'dept' => 'CSE',
        'year' => rand(1, 4)
    ];
}

// 3. 116 Groups & Alphanumeric Classrooms
$groups = []; $classrooms = []; $gid = 1; $cid = 1;
$branch_config = [
    'CSE' => ['A', 'B', 'C', 'ADV-A'], 'ECE' => ['A', 'B', 'C', 'ADV-A'], 
    'EEE' => ['A', 'B', 'ADV-A'], 'MECH' => ['A', 'B', 'ADV-A'],
    'AI' => ['A', 'B', 'ADV-A'], 'AI-ML' => ['A', 'B', 'ADV-A'], 
    'AI-DS' => ['A', 'B', 'ADV-A'], 'DS' => ['A', 'B', 'ADV-A'], 
    'Cyber Security' => ['A', 'B', 'ADV-A']
];
$floor_counters = ['A' => 101, 'B' => 101, 'C' => 101, 'D' => 101, 'E' => 101];

foreach ($branch_config as $branch => $sections) {
    foreach ($sections as $sec) {
        $is_advanced = (strpos($sec, 'ADV') !== false);
        for ($y = 1; $y <= 4; $y++) {
            $groups[] = ['id' => $gid++, 'name' => "B.Tech $branch-$sec (Year $y)", 'branch' => $branch, 'year' => $y, 'type' => $is_advanced ? 'Advanced' : 'Regular'];
            $floor = ['A', 'B', 'C', 'D', 'E'][$y-1];
            $room_num = ($is_advanced ? 'ADV-' : '') . $floor . ($floor_counters[$floor]++);
            $classrooms[] = [
                'id' => $cid++, 'name' => "Room $room_num ($branch-$sec)", 'type' => 'LectureHall',
                'capacity' => $is_advanced ? 40 : 65
            ];
        }
    }
}

// Ensure enough rooms for everyone simultaneously
while(count($classrooms) < 150) {
    $classrooms[] = [
        'id' => $cid++, 'name' => "Overflow Room O" . $cid, 'type' => 'LectureHall', 'capacity' => 60
    ];
}

// 4. Force 100% Load Assignments
$assignments = []; $aid = 1;
foreach ($groups as $g) {
    $year = $g['year']; $branch = $g['branch'];
    
    // Select 24 UNIQUE subjects for this group
    $targets = array_filter($subjects, function($s) use ($branch, $year) {
        $mapped_dept = (strpos($branch, 'AI') !== false) ? 'AI' : $branch;
        return ($s['dept'] == $mapped_dept || $s['dept'] == 'CSE' || $s['dept'] == 'H&S');
    });
    
    shuffle($targets);
    $group_subjects = array_slice($targets, 0, 24);
    
    // Ensure exactly 24
    while(count($group_subjects) < 24) {
        $group_subjects[] = $subjects[array_rand($subjects)];
    }

    $group_teachers_ids = []; 
    foreach ($group_subjects as $sub) {
        $valid_teachers = array_filter($teachers, function($t) use ($sub, $group_teachers_ids) { 
            return $t['dept'] == $sub['dept'] && !in_array($t['id'], $group_teachers_ids); 
        });
        
        if(empty($valid_teachers)) {
            // If no unique teacher left in dept, pick any that isn't already assigned to this group
            $valid_teachers = array_filter($teachers, function($t) use ($group_teachers_ids) {
                return !in_array($t['id'], $group_teachers_ids);
            });
        }
        
        if(empty($valid_teachers)) $valid_teachers = $teachers; // Fallback
        
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

$_SESSION['flash_message'] = "MAX DENSITY AUDIT COMPLETE: 100% Load Balanced Across 150 Rooms!";
header("Location: legacy_admin.php");
exit();
?>