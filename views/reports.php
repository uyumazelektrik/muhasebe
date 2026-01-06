<?php include __DIR__ . '/layout/header.php'; ?>

<!-- Top Header Area -->
<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
    <div class="flex flex-col gap-1 mb-2">
        <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Raporlar</h2>
        <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Personel performans özeti.</p>
    </div>
</header>

<div class="flex-1 p-4 sm:px-8 w-full min-w-0">
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-card-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
            <span class="text-[#9da6b9] text-xs font-bold uppercase tracking-wider mb-2">Toplam Personel</span>
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                    <span class="material-symbols-outlined text-[24px]">group</span>
                </span>
                <span class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $totalEmployees; ?></span>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="bg-white dark:bg-card-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
            <span class="text-[#9da6b9] text-xs font-bold uppercase tracking-wider mb-2">Bu Ay Katılım</span>
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-lg bg-green-500/10 text-green-500">
                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                </span>
                <span class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $totalPresent; ?> <span class="text-sm font-normal text-slate-400">gün</span></span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-card-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
            <span class="text-[#9da6b9] text-xs font-bold uppercase tracking-wider mb-2">Toplam Geç Kalma</span>
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-lg bg-red-500/10 text-red-500">
                    <span class="material-symbols-outlined text-[24px]">schedule</span>
                </span>
                <span class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $totalLate; ?> <span class="text-sm font-normal text-slate-400">kez</span></span>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col w-full">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Personel Performans Özeti</h3>
            <span class="text-sm px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium"><?php echo $monthName; ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#1c222e]">
                        <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider min-w-[200px]">Personel</th>
                        <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Çalışılan Gün</th>
                        <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Geç Kalma</th>
                        <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Fazla Mesai</th>
                        <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php if (count($userStats) > 0): ?>
                        <?php foreach ($userStats as $stat): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-xs">
                                        <?php echo strtoupper(substr($stat['full_name'], 0, 1)); ?>
                                    </div>
                                    <span class="text-slate-900 dark:text-white font-medium text-sm"><?php echo htmlspecialchars($stat['full_name']); ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-slate-700 dark:text-slate-300 font-bold"><?php echo $stat['days_present']; ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <?php if ($stat['days_late'] > 0): ?>
                                    <span class="inline-flex items-center gap-1 text-red-500 font-medium text-sm">
                                        <?php echo $stat['days_late']; ?>
                                        <span class="material-symbols-outlined text-[16px]">warning</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-slate-600 dark:text-slate-400 text-sm"><?php echo $stat['total_overtime'] > 0 ? $stat['total_overtime'] . ' saat' : '-'; ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <!-- Basit bir performans göstergesi -->
                                <?php if ($stat['days_late'] == 0 && $stat['days_present'] > 0): ?>
                                    <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-500 ring-1 ring-inset ring-green-500/20">Mükemmel</span>
                                <?php elseif ($stat['days_late'] < 3): ?>
                                    <span class="inline-flex items-center rounded-md bg-yellow-500/10 px-2 py-1 text-xs font-medium text-yellow-500 ring-1 ring-inset ring-yellow-500/20">İyi</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-500 ring-1 ring-inset ring-red-500/20">Dikkat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                Henüz veri bulunmuyor.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
