<?php
session_start();

// Audisankara University - Rich Professional Dataset

$teachers = [
    [
        'id' => 1, 'name' => 'Prof. K. Suresh', 'email' => 'hod.cse@audisankara.ac.in',
        'role' => 'Head of Department', 'qualification' => 'Ph.D (IIT Bombay)', 'phone' => '+91 99001 12233', 
        'experience' => '20+ Years', 'photo' => 'https://i.pravatar.cc/150?u=1'
    ],
    [
        'id' => 2, 'name' => 'Dr. P. Ramesh', 'email' => 'ramesh.ece@audisankara.ac.in',
        'role' => 'Associate Professor', 'qualification' => 'Ph.D (VLSI)', 'phone' => '+91 99001 12234', 
        'experience' => '12 Years', 'photo' => 'https://i.pravatar.cc/150?u=2'
    ],
    [
        'id' => 3, 'name' => 'Prof. M. Lakshmi', 'email' => 'lakshmi.eee@audisankara.ac.in',
        'role' => 'Professor', 'qualification' => 'Ph.D (Renewable Energy)', 'phone' => '+91 99001 12235', 
        'experience' => '15 Years', 'photo' => 'https://i.pravatar.cc/150?u=3'
    ],
    [
        'id' => 4, 'name' => 'Mr. V. Ravi Kumar', 'email' => 'ravi.mech@audisankara.ac.in',
        'role' => 'Assistant Professor', 'qualification' => 'M.Tech (Design)', 'phone' => '+91 99001 12236', 
        'experience' => '8 Years', 'photo' => 'https://i.pravatar.cc/150?u=4'
    ],
    [
        'id' => 5, 'name' => 'Dr. B. Venkat', 'email' => 'venkat.ai@audisankara.ac.in',
        'role' => 'Director (AI Research)', 'qualification' => 'Ph.D (Deep Learning)', 'phone' => '+91 99001 12237', 
        'experience' => '10 Years', 'photo' => 'https://i.pravatar.cc/150?u=6'
    ],
    [
        'id' => 6, 'name' => 'Ms. S. Anitha', 'email' => 'anitha.it@audisankara.ac.in',
        'role' => 'Assistant Professor', 'qualification' => 'M.Tech (IT)', 'phone' => '+91 99001 12238', 
        'experience' => '5 Years', 'photo' => 'https://i.pravatar.cc/150?u=7'
    ],
    [
        'id' => 7, 'name' => 'Dr. G. Murali', 'email' => 'murali.cse@audisankara.ac.in',
        'role' => 'Professor', 'qualification' => 'Ph.D (Cyber Security)', 'phone' => '+91 99001 12239', 
        'experience' => '18 Years', 'photo' => 'https://i.pravatar.cc/150?u=8'
    ]
];

$subjects = [
    ['id' => 1, 'name' => 'Artificial Intelligence'],
    ['id' => 2, 'name' => 'Machine Learning'],
    ['id' => 3, 'name' => 'Data Structures'],
    ['id' => 4, 'name' => 'Python Programming'],
    ['id' => 5, 'name' => 'Digital Electronics'],
    ['id' => 6, 'name' => 'Cyber Security'],
    ['id' => 7, 'name' => 'Cloud Computing'],
    ['id' => 8, 'name' => 'Operating Systems']
];

$groups = [
    ['id' => 1, 'name' => 'B.Tech CSE-A (Year 1)'],
    ['id' => 2, 'name' => 'B.Tech CSE-B (Year 1)'],
    ['id' => 3, 'name' => 'B.Tech AI-DS (Year 1)'],
    ['id' => 4, 'name' => 'B.Tech IT (Year 1)']
];

$classrooms = [
    ['id' => 1, 'name' => 'LH-101', 'type' => 'LectureHall'],
    ['id' => 2, 'name' => 'LH-102', 'type' => 'LectureHall'],
    ['id' => 3, 'name' => 'LH-103', 'type' => 'LectureHall'],
    ['id' => 4, 'name' => 'Computer Lab-1', 'type' => 'Lab'],
    ['id' => 5, 'name' => 'Computer Lab-2', 'type' => 'Lab']
];

$assignments = [
    ['id' => 1, 't_id' => 1, 't_name' => 'Prof. K. Suresh', 's_name' => 'Data Structures', 'g_name' => 'B.Tech CSE-A (Year 1)'],
    ['id' => 2, 't_id' => 5, 't_name' => 'Dr. B. Venkat', 's_name' => 'Artificial Intelligence', 'g_name' => 'B.Tech AI-DS (Year 1)'],
    ['id' => 3, 't_id' => 2, 't_name' => 'Dr. P. Ramesh', 's_name' => 'Digital Electronics', 'g_name' => 'B.Tech CSE-B (Year 1)'],
    ['id' => 4, 't_id' => 7, 't_name' => 'Dr. G. Murali', 's_name' => 'Cyber Security', 'g_name' => 'B.Tech IT (Year 1)'],
    ['id' => 5, 't_id' => 6, 't_name' => 'Ms. S. Anitha', 's_name' => 'Operating Systems', 'g_name' => 'B.Tech CSE-A (Year 1)'],
    ['id' => 6, 't_id' => 4, 't_name' => 'Mr. V. Ravi Kumar', 's_name' => 'Python Programming', 'g_name' => 'B.Tech CSE-B (Year 1)'],
    ['id' => 7, 't_id' => 3, 't_name' => 'Prof. M. Lakshmi', 's_name' => 'Machine Learning', 'g_name' => 'B.Tech AI-DS (Year 1)']
];

$_SESSION['teachers'] = $teachers;
$_SESSION['subjects'] = $subjects;
$_SESSION['groups'] = $groups;
$_SESSION['classrooms'] = $classrooms;
$_SESSION['assignments'] = $assignments;

$_SESSION['flash_message'] = "University Faculty Profiles Loaded!";
header("Location: teacher_directory.php");
exit();
?>