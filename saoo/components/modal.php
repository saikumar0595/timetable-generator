<?php
/**
 * ChronoGen Modal/Dialog Component
 * Reusable modal for forms and alerts
 * 
 * Usage: renderModal($id, $title, $content, $buttons);
 */

function renderModal($id, $title, $content, $buttons = []) {
    ?>
    <div id="<?= $id ?>" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal('<?= $id ?>')">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full fade-in">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($title) ?></h3>
                <button onclick="closeModal('<?= $id ?>')" class="p-1 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-slate-400"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <?= $content ?>
            </div>
            
            <!-- Footer -->
            <?php if (!empty($buttons)): ?>
            <div class="flex gap-3 p-6 border-t border-slate-200 justify-end">
                <?php foreach ($buttons as $btn): ?>
                <button onclick="<?= $btn['onclick'] ?? '' ?>" 
                        class="<?= $btn['class'] ?? 'px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors' ?>">
                    <?= htmlspecialchars($btn['label']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
        }
    </script>
    <?php
}

/**
 * Form Input Component
 */
function renderFormInput($label, $name, $type = 'text', $placeholder = '', $required = true, $value = '') {
    ?>
    <div class="mb-4">
        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= htmlspecialchars($label) ?></label>
        <input type="<?= $type ?>" 
               name="<?= htmlspecialchars($name) ?>" 
               placeholder="<?= htmlspecialchars($placeholder) ?>"
               value="<?= htmlspecialchars($value) ?>"
               <?= $required ? 'required' : '' ?>
               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
    </div>
    <?php
}

/**
 * Form Select Component
 */
function renderFormSelect($label, $name, $options = [], $selected = '', $required = true) {
    ?>
    <div class="mb-4">
        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= htmlspecialchars($label) ?></label>
        <select name="<?= htmlspecialchars($name) ?>" 
                <?= $required ? 'required' : '' ?>
                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            <option value="">Select an option...</option>
            <?php foreach ($options as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>" <?= $value === $selected ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * Form Textarea Component
 */
function renderFormTextarea($label, $name, $placeholder = '', $required = false, $value = '', $rows = 4) {
    ?>
    <div class="mb-4">
        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= htmlspecialchars($label) ?></label>
        <textarea name="<?= htmlspecialchars($name) ?>" 
                  placeholder="<?= htmlspecialchars($placeholder) ?>"
                  rows="<?= $rows ?>"
                  <?= $required ? 'required' : '' ?>
                  class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"><?= htmlspecialchars($value) ?></textarea>
    </div>
    <?php
}

/**
 * Alert/Toast Component
 */
function renderAlert($type, $message, $dismissible = true) {
    $colors = [
        'success' => 'bg-emerald-50 border-emerald-500 text-emerald-800',
        'error' => 'bg-red-50 border-red-500 text-red-800',
        'warning' => 'bg-amber-50 border-amber-500 text-amber-800',
        'info' => 'bg-blue-50 border-blue-500 text-blue-800',
    ];
    
    $icons = [
        'success' => 'fas fa-check-circle text-emerald-500',
        'error' => 'fas fa-exclamation-circle text-red-500',
        'warning' => 'fas fa-exclamation-triangle text-amber-500',
        'info' => 'fas fa-info-circle text-blue-500',
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    $icon = $icons[$type] ?? $icons['info'];
    ?>
    <div class="<?= $color ?> border-l-4 p-4 mb-4 rounded-lg flex items-start gap-3 fade-in">
        <i class="<?= $icon ?> text-lg mt-0.5 flex-shrink-0"></i>
        <p class="text-sm font-medium flex-1"><?= htmlspecialchars($message) ?></p>
        <?php if ($dismissible): ?>
        <button onclick="this.parentElement.remove()" class="text-lg hover:opacity-70 transition-opacity">
            <i class="fas fa-times"></i>
        </button>
        <?php endif; ?>
    </div>
    <?php
}
?>
