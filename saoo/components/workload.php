<?php
/**
 * ChronoGen Teacher Workload Component
 * Displays personal workload metrics for a teacher
 */

function renderTeacherWorkload($workload_data) {
    if (!$workload_data) return;
    ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Workload Summary</h3>
                <p class="text-xs text-slate-500">Weekly teaching load analytics</p>
            </div>
            <div class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                 style="background-color: <?= $workload_data['color'] ?>15; color: <?= $workload_data['color'] ?>">
                <?= $workload_data['level'] ?> Load
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Hours</p>
                <div class="flex items-end gap-1">
                    <span class="text-2xl font-bold text-slate-800"><?= $workload_data['total_hours'] ?></span>
                    <span class="text-[10px] font-bold text-slate-400 pb-1">hrs/wk</span>
                </div>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Sessions</p>
                <div class="flex items-end gap-1">
                    <span class="text-2xl font-bold text-slate-800"><?= $workload_data['sessions_count'] ?></span>
                    <span class="text-[10px] font-bold text-slate-400 pb-1">lectures</span>
                </div>
            </div>
        </div>
        
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daily Distribution</p>
                <span class="text-[10px] font-bold text-indigo-600"><?= max($workload_data['days']) ?>h Peak</span>
            </div>
            <div class="flex items-end gap-1.5 h-20 pt-2">
                <?php foreach ($workload_data['days'] as $day => $hours): 
                    $height = ($hours / 10) * 100; // Assuming 10h is max
                ?>
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="w-full bg-slate-100 rounded-t-md relative overflow-hidden" style="height: 100%">
                            <div class="absolute bottom-0 left-0 w-full transition-all duration-500 group-hover:brightness-110" 
                                 style="height: <?= $height ?>%; background-color: <?= $workload_data['color'] ?>">
                                 <div class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[9px] px-1.5 py-0.5 rounded transition-opacity whitespace-nowrap">
                                     <?= $hours ?> hrs
                                 </div>
                            </div>
                        </div>
                        <span class="text-[9px] font-bold text-slate-400 mt-2 uppercase"><?= substr($day, 0, 1) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
?>
