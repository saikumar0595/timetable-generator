<?php
/**
 * ChronoGen Sidebar Component
 * Reusable navigation sidebar for all pages
 * 
 * Usage: renderSidebar($current_page, $role);
 */

function renderSidebar($current_page = '', $role = 'admin') {
    $nav_items = [
        'admin' => [
            ['icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'href' => 'index.php', 'id' => 'dashboard'],
            ['icon' => 'fas fa-chart-pie', 'label' => 'Analytics', 'href' => 'analytics.php', 'id' => 'analytics'],
            ['icon' => 'fas fa-bell', 'label' => 'Alert System', 'href' => 'test_alerts.php', 'id' => 'alerts'],
            ['icon' => 'fas fa-terminal', 'label' => 'Mainframe', 'href' => 'legacy_admin.php', 'id' => 'legacy'],
            ['icon' => 'fas fa-chalkboard-teacher', 'label' => 'Teachers', 'href' => 'manage_teachers.php', 'id' => 'teachers'],
            ['icon' => 'fas fa-book', 'label' => 'Subjects', 'href' => 'manage_subjects.php', 'id' => 'subjects'],
            ['icon' => 'fas fa-door-open', 'label' => 'Classrooms', 'href' => 'manage_classrooms.php', 'id' => 'classrooms'],
            ['icon' => 'fas fa-users', 'label' => 'Student Groups', 'href' => 'manage_groups.php', 'id' => 'groups'],
            ['icon' => 'fas fa-calendar-alt', 'label' => 'Timetable', 'href' => 'view_timetable.php', 'id' => 'timetable'],
        ],
        'student' => [
            ['icon' => 'fas fa-calendar-alt', 'label' => 'My Timetable', 'href' => 'view_timetable.php', 'id' => 'timetable'],
            ['icon' => 'fas fa-chalkboard-teacher', 'label' => 'Teachers', 'href' => 'teacher_directory.php', 'id' => 'teachers'],
        ],
        'faculty' => [
            ['icon' => 'fas fa-chart-line', 'label' => 'My Analytics', 'href' => 'analytics.php', 'id' => 'analytics'],
            ['icon' => 'fas fa-calendar-alt', 'label' => 'My Schedule', 'href' => 'view_timetable.php', 'id' => 'timetable'],
            ['icon' => 'fas fa-book', 'label' => 'Subjects', 'href' => 'manage_subjects.php', 'id' => 'subjects'],
        ],
    ];
    
    $items = $nav_items[$role] ?? $nav_items['student'];
    ?>
    <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20 fixed lg:relative h-screen lg:h-auto overflow-y-auto">
        <!-- Header -->
        <div class="h-20 flex items-center px-8 border-b border-slate-800">
            <div class="w-8 h-8 mr-3 flex-shrink-0">
                <svg width="32" height="32" viewBox="0 0 100 100" fill="none">
                    <path d="M50 95C50 95 85 75 85 35V15L50 5L15 15V35C15 75 50 95 50 95Z" fill="#1e3a8a" stroke="#fbbf24" stroke-width="5"/>
                    <text x="50" y="55" font-weight="bold" font-size="28" fill="white" text-anchor="middle">A</text>
                </svg>
            </div>
            <span class="text-lg font-bold tracking-tight uppercase hidden sm:inline">ChronoGen</span>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Menu</p>
            
            <?php foreach ($items as $item): ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" 
                   class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all group <?= ($current_page === $item['id']) ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : '' ?>">
                    <i class="<?= $item['icon'] ?> w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        
        <!-- Footer -->
        <div class="border-t border-slate-800 p-4 mt-auto">
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all mb-2">
                <i class="fas fa-user w-5 text-center"></i>
                <span class="font-medium">Profile</span>
            </a>
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="font-medium">Sign Out</span>
            </a>
        </div>
    </aside>

    <style>
        /* Mobile sidebar overlay */
        @media (max-width: 1024px) {
            aside.hidden {
                display: none !important;
            }
            aside:not(.hidden) {
                position: fixed;
                left: 0;
                top: 0;
                z-index: 30;
            }
        }
    </style>
    <?php
}
?>
