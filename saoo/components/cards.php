<?php
/**
 * ChronoGen Card Components
 * Reusable cards for displaying data
 */

/**
 * Stat Card Component
 */
function renderStatCard($icon, $icon_color, $label, $value, $trend = null, $trend_value = '') {
    $color_map = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'pink' => 'bg-pink-50 text-pink-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'purple' => 'bg-purple-50 text-purple-600',
    ];
    
    $bg_class = $color_map[$icon_color] ?? $color_map['indigo'];
    ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl <?= $bg_class ?> flex items-center justify-center text-xl">
                <i class="<?= $icon ?>"></i>
            </div>
            <?php if ($trend): ?>
            <div class="flex items-center gap-1 text-sm font-semibold <?= $trend === 'up' ? 'text-emerald-600' : 'text-red-600' ?>">
                <i class="fas fa-arrow-<?= $trend === 'up' ? 'up' : 'down' ?>"></i>
                <?= htmlspecialchars($trend_value) ?>
            </div>
            <?php endif; ?>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= htmlspecialchars($label) ?></p>
        <h3 class="text-3xl font-bold text-slate-800"><?= htmlspecialchars($value) ?></h3>
    </div>
    <?php
}

/**
 * Action Card Component (clickable card with icon and title)
 */
function renderActionCard($icon, $title, $description, $onclick = '', $href = '') {
    $tag = $href ? 'a' : 'button';
    $attrs = $href ? "href=\"" . htmlspecialchars($href) . "\"" : "onclick=\"" . $onclick . "\"";
    ?>
    <<?= $tag ?> <?= $attrs ?> class="p-6 rounded-2xl border-2 border-slate-200 hover:border-indigo-500 hover:shadow-lg transition-all group cursor-pointer bg-white">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
            <i class="<?= $icon ?>"></i>
        </div>
        <h4 class="font-semibold text-slate-800 mb-1"><?= htmlspecialchars($title) ?></h4>
        <p class="text-sm text-slate-500"><?= htmlspecialchars($description) ?></p>
    </<?= $tag ?>>
    <?php
}

/**
 * Data Card Component (displays key-value data)
 */
function renderDataCard($title, $items) {
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4"><?= htmlspecialchars($title) ?></h3>
        <div class="space-y-3">
            <?php foreach ($items as $label => $value): ?>
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 last:border-0">
                <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($label) ?></span>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Empty State Component
 */
function renderEmptyState($icon, $title, $message, $button_label = '', $button_action = '') {
    ?>
    <div class="flex flex-col items-center justify-center py-12 px-4">
        <div class="text-6xl text-slate-300 mb-4">
            <i class="<?= $icon ?>"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2"><?= htmlspecialchars($title) ?></h3>
        <p class="text-slate-500 text-center mb-6 max-w-sm"><?= htmlspecialchars($message) ?></p>
        <?php if ($button_label): ?>
        <button onclick="<?= $button_action ?>" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
            <?= htmlspecialchars($button_label) ?>
        </button>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Badge Component
 */
function renderBadge($text, $type = 'primary', $size = 'md') {
    $types = [
        'primary' => 'bg-indigo-100 text-indigo-800',
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-2 text-base',
    ];
    
    $type_class = $types[$type] ?? $types['primary'];
    $size_class = $sizes[$size] ?? $sizes['md'];
    ?>
    <span class="<?= $type_class ?> <?= $size_class ?> font-semibold rounded-full inline-block">
        <?= htmlspecialchars($text) ?>
    </span>
    <?php
}
?>
