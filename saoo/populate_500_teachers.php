<?php
session_start();

// Famous Scientists List
$famous_scientists = [
    'Albert Einstein', 'Isaac Newton', 'Marie Curie', 'Nikola Tesla', 'Stephen Hawking',
    'Charles Darwin', 'Galileo Galilei', 'Ada Lovelace', 'Alan Turing', 'Richard Feynman',
    'James Clerk Maxwell', 'Niels Bohr', 'Werner Heisenberg', 'Max Planck', 'Enrico Fermi',
    'Erwin Schrodinger', 'Louis Pasteur', 'Gregor Mendel', 'Rosalind Franklin', 'Jane Goodall',
    'Rachel Carson', 'Carl Sagan', 'Neil deGrasse Tyson', 'Michio Kaku', 'Brian Cox',
    'Dmitri Mendeleev', 'Antoine Lavoisier', 'Robert Boyle', 'Michael Faraday', 'Ernest Rutherford',
    'John Dalton', 'Linus Pauling', 'Guglielmo Marconi', 'Alexander Graham Bell', 'Thomas Edison',
    'Benjamin Franklin', 'Leonardo da Vinci', 'Archimedes', 'Pythagoras', 'Euclid',
    'Hypatia', 'Barbara McClintock', 'Chien-Shiung Wu', 'Grace Hopper', 'Katherine Johnson',
    'Dorothy Vaughan', 'Mary Jackson', 'Margaret Hamilton', 'Hedy Lamarr', 'Vera Rubin'
];

// Famous Courses List
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

$depts = ['CSE', 'ECE', 'EEE', 'MECH', 'AI', 'ML', 'DS', 'Cyber Security', 'H&S'];

// 1. Populate 500 Teachers
$teachers = [];
for ($i = 1; $i <= 500; $i++) {
    $base_name = $famous_scientists[($i - 1) % count($famous_scientists)];
    $suffix = (floor(($i-1) / count($famous_scientists)) > 0) ? " " . (floor(($i-1) / count($famous_scientists)) + 1) : "";
    $name = $base_name . $suffix;
    $dept = $depts[array_rand($depts)];
    $gender = (rand(0, 1) == 0) ? 'male' : 'female';
    $photo_id = rand(1, 95);
    $photo_url = ($gender == 'male') 
        ? "https://randomuser.me/api/portraits/men/{$photo_id}.jpg" 
        : "https://randomuser.me/api/portraits/women/{$photo_id}.jpg";

    $teachers[] = [
        'id' => $i, 'univ_id' => 'STAFF@' . (2000 + $i), 'name' => "Prof. " . $name, 'dept' => $dept,
        'role' => (rand(0, 3) == 0 ? 'Professor' : 'Asst. Professor'),
        'email' => strtolower(str_replace(' ', '.', $name)) . $i . "@audisankara.ac.in",
        'phone' => '+91 ' . rand(70000, 99999) . rand(10000, 99999),
        'qualification' => (rand(0, 1) == 0 ? 'Ph.D' : 'M.Tech'),
        'experience' => rand(2, 30) . ' Years', 'photo' => $photo_url
    ];
}

// 2. Populate 500 Subjects
$subjects = [];
for ($i = 1; $i <= 500; $i++) {
    $base_course = $famous_courses[($i - 1) % count($famous_courses)];
    $suffix = (floor(($i-1) / count($famous_courses)) > 0) ? " " . (floor(($i-1) / count($famous_courses)) + 1) : "";
    $course_name = $base_course . $suffix;
    $dept = $depts[array_rand($depts)];
    $subjects[] = [
        'id' => $i, 'name' => $course_name, 'dept' => $dept, 'year' => rand(1, 4)
    ];
}

// 3. Populate Groups & Classrooms (Keep as is or scale)
$groups = $_SESSION['groups'] ?? [];
$classrooms = $_SESSION['classrooms'] ?? [];

// 4. Generate 1632 Assignments (Old Project Standard)
$assignments = [];
for ($i = 1; $i <= 1632; $i++) {
    $t = $teachers[array_rand($teachers)];
    $s = $subjects[array_rand($subjects)];
    $g = $groups[array_rand($groups)];
    $assignments[] = [
        'id' => $i, 't_id' => $t['id'], 't_name' => $t['name'],
        's_name' => $s['name'], 'g_name' => $g['name']
    ];
}

$_SESSION['teachers'] = $teachers;
$_SESSION['subjects'] = $subjects;
$_SESSION['assignments'] = $assignments;
$_SESSION['flash_message'] = "✅ SUCCESS: 500 Teachers & 500 Subjects (Famous Data Pack) loaded successfully!";
header("Location: index.php");
exit();
?>
