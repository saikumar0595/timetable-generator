<?php
/**
 * ChronoGen Heatmap Component
 * Visualizes scheduling density for capacity planning
 */

function renderHeatmap($heatmap_data) {
    $days = array_keys($heatmap_data);
    if (empty($days)) return;
    $periods = array_keys($heatmap_data[$days[0]]);
    
    // Find max value for color scaling
    $max_val = 0;
    foreach ($heatmap_data as $day_data) {
        foreach ($day_data as $val) {
            if ($val > $max_val) $max_val = $val;
        }
    }
    if ($max_val == 0) $max_val = 1;
    ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Departmental Heatmap</h3>
                <p class="text-xs text-slate-500">Resource utilization density across the week</p>
            </div>
            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase">
                <span>Free</span>
                <div class="flex gap-0.5">
                    <div class="w-3 h-3 bg-slate-100 rounded-sm"></div>
                    <div class="w-3 h-3 bg-indigo-100 rounded-sm"></div>
                    <div class="w-3 h-3 bg-indigo-300 rounded-sm"></div>
                    <div class="w-3 h-3 bg-indigo-600 rounded-sm"></div>
                </div>
                <span>Busy</span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-1">
                <thead>
                    <tr>
                        <th class="w-20"></th>
                        <?php foreach ($periods as $p): ?>
                            <th class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter text-center py-2 min-w-[60px]">
                                <?= explode(' - ', $p)[0] ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($heatmap_data as $day => $p_data): ?>
                    <tr>
                        <td class="text-[10px] font-bold text-slate-500 uppercase pr-2"><?= substr($day, 0, 3) ?></td>
                        <?php foreach ($p_data as $count): 
                            $intensity = ($count / $max_val) * 100;
                            $bg_class = 'bg-slate-100';
                            if ($intensity > 75) $bg_class = 'bg-indigo-600';
                            elseif ($intensity > 50) $bg_class = 'bg-indigo-400';
                            elseif ($intensity > 25) $bg_class = 'bg-indigo-200';
                            elseif ($intensity > 0) $bg_class = 'bg-indigo-100';
                        ?>
                            <td class="h-8 <?= $bg_class ?> rounded-md transition-all hover:scale-105 cursor-help relative group" title="<?= $count ?> active sessions">
                                <?php if ($count > 0): ?>
                                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold <?= $intensity > 50 ? 'text-white' : 'text-indigo-600' ?>">
                                        <?= $count ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>
