<?php
/**
 * ChronoGen Header Component
 * Reusable header for all dashboard pages
 * 
 * Usage: renderHeader($page_title, $user, $role, $show_actions = true);
 */

function renderHeader($page_title, $user, $role, $show_actions = true) {
    ?>
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shadow-sm sticky top-0">
        <div class="flex items-center gap-4 flex-1">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition-colors">
                <i class="fas fa-bars text-xl text-slate-600"></i>
            </button>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?= htmlspecialchars($page_title) ?></h2>
        </div>
        
        <div class="flex items-center gap-6">
            <?php if ($show_actions): ?>
                <div class="hidden md:flex items-center gap-4">
                    <button onclick="toggleTheme()" class="p-2 hover:bg-slate-100 rounded-lg transition-colors" title="Toggle theme">
                        <i class="fas fa-moon text-lg text-slate-600"></i>
                    </button>
                    <button onclick="showNotifications()" class="relative p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg text-slate-600"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($user) ?></p>
                    <p class="text-xs text-slate-500 uppercase"><?= ucfirst($role) ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg">
                    <?= strtoupper(substr($user, 0, 1)) ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            sidebar?.classList.toggle('hidden');
        }

        function toggleTheme() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        }

        function showNotifications() {
            alert('Notifications coming soon!');
        }
    </script>
    <?php
}
?>
