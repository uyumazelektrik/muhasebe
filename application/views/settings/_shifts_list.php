<?php if (empty($shifts)): ?>
    <tr>
        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Vardiya tanımı bulunmuyor</td>
    </tr>
<?php else: ?>
    <?php foreach($shifts as $shift): ?>
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50">
        <td class="px-6 py-4">
            <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($shift['name']); ?></span>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="text-sm font-mono text-emerald-600 dark:text-emerald-400"><?php echo substr($shift['start_time'], 0, 5); ?></span>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="text-sm font-mono text-red-600 dark:text-red-400"><?php echo substr($shift['end_time'], 0, 5); ?></span>
        </td>
        <td class="px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-2">
                <button onclick='openShiftModal(<?php echo json_encode($shift); ?>)' class="p-2 text-slate-400 hover:text-blue-500 transition-colors" title="Düzenle">
                    <span class="material-symbols-outlined text-sm">edit</span>
                </button>
                <button onclick="deleteShift(<?php echo $shift['id']; ?>)" class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="Sil">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
