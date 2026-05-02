<?php
/**
 * ChronoGen Mobile Next-Up Widget
 * Optimized view for on-the-go awareness
 */

function renderMobileNextUp($current_session, $next_session) {
    ?>
    <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-500/40 relative overflow-hidden">
        <!-- Abstract Background Decoration -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest">Currently In</p>
                        <p class="font-bold text-lg leading-tight"><?= $current_session['room'] ?? 'Free' ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest">Time Left</p>
                    <p class="font-bold text-lg">24m</p>
                </div>
            </div>
            
            <?php if ($current_session): ?>
            <div class="mb-8">
                <h4 class="text-2xl font-bold mb-1"><?= $current_session['subject'] ?></h4>
                <div class="flex items-center gap-2 text-indigo-100 text-sm">
                    <i class="fas fa-users opacity-60"></i>
                    <span><?= implode(', ', $current_session['groups']) ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-400 flex items-center justify-center text-emerald-900 text-xs">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-indigo-100 uppercase tracking-widest">Coming Up Next</p>
                            <p class="text-xs font-bold"><?= $next_session['subject'] ?? 'No more classes' ?> at <?= $next_session['time'] ?? '--:--' ?></p>
                        </div>
                    </div>
                    <div class="text-[9px] font-bold px-2 py-1 bg-white/20 rounded-md">
                        <?= $next_session['room'] ?? 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
