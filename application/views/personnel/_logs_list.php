<?php if (empty($logs)): ?>
    <tr>
        <td colspan="4" class="px-6 py-8 text-center text-text-secondary">Kayıt bulunamadı.</td>
    </tr>
<?php else: ?>
    <?php foreach ($logs as $log): ?>
    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-gray-300">
            <?php echo date('d.m.Y', strtotime($log['date'])); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
            <?php echo htmlspecialchars($log['full_name'] ?? 'Bilinmiyor'); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600 dark:text-gray-400">
            <?php echo substr($log['clock_in'], 0, 5); ?> - <?php echo substr($log['clock_out'], 0, 5); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="inline-flex items-center gap-2">
            <?php if (!empty($log['is_late'])): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                    Geç Kaldı
                </span>
            <?php elseif($log['status'] == 'present'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                    Normal Mesai
                </span>
            <?php else: ?>
                <?php 
                $statusLabels = [
                    'holiday' => ['Resmi Tatil', 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                    'public_holiday' => ['Resmi Tatil', 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                    'excused' => ['İzinli (Mazeretli)', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'],
                    'excused_late' => ['Mazeretli Geç', 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
                    'paid_leave' => ['Ücretli İzin', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
                    'weekly_leave' => ['Haftalık İzin', 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300'],
                    'unpaid_leave' => ['Ücretsiz İzin', 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'],
                    'annual_leave' => ['Yıllık İzin', 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
                    'sick_leave' => ['Raporlu', 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300'],
                    'absent' => ['Devamsız', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300']
                ];
                $label = $statusLabels[$log['status']] ?? [ucfirst($log['status'] ?? 'Unknown'), 'bg-gray-100 text-gray-800'];
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $label[1]; ?>">
                    <?php echo $label[0]; ?>
                </span>
            <?php endif; ?>
            
            <?php if (!empty($log['note'])): ?>
                <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-gray-500 cursor-help" title="<?php echo htmlspecialchars($log['note']); ?>">description</span>
            <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
