<?php
/**
 * ChronoGen Conflict Matrix Component
 * Displays scheduling conflicts in a clear, actionable format
 */

function renderConflictMatrix($conflicts) {
    ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Conflict Analysis</h3>
                <p class="text-xs text-slate-500">Automated detection of schedule violations</p>
            </div>
            <span class="px-3 py-1 bg-<?= empty($conflicts) ? 'emerald' : 'red' ?>-100 text-<?= empty($conflicts) ? 'emerald' : 'red' ?>-700 text-[10px] font-bold rounded-full uppercase">
                <?= count($conflicts) ?> Issues Found
            </span>
        </div>
        
        <?php if (empty($conflicts)): ?>
            <div class="py-10 text-center">
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-emerald-500 text-2xl"></i>
                </div>
                <p class="text-slate-500 font-medium">No conflicts detected in current schedule.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($conflicts as $conflict): ?>
                    <div class="flex items-start gap-4 p-4 rounded-xl border border-red-100 bg-red-50/30 group hover:bg-red-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                            <i class="fas <?= $conflict['type'] == 'Room Conflict' ? 'fa-door-closed' : ($conflict['type'] == 'Teacher Conflict' ? 'fa-user-times' : 'fa-users-slash') ?>"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-800 text-sm"><?= $conflict['type'] ?></h4>
                                <span class="text-[10px] font-bold text-slate-400 uppercase"><?= $conflict['day'] ?> • <?= $conflict['period'] ?></span>
                            </div>
                            <p class="text-xs text-slate-600"><?= $conflict['message'] ?></p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-white border border-red-200 text-red-600 rounded text-[9px] font-bold uppercase"><?= $conflict['resource'] ?></span>
                            </div>
                        </div>
                        <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white transition-all opacity-0 group-hover:opacity-100">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>
