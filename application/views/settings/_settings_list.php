<?php if (empty($settings)): ?>
    <tr>
        <td colspan="3" class="px-6 py-8 text-center text-slate-500">Ayar bulunmuyor</td>
    </tr>
<?php else: ?>
    <?php 
    $settingLabels = [
        'late_penalty_multiplier' => ['Geç Kalma Ceza Çarpanı', 'Geç kalınan her dakika için uygulanacak ceza çarpanı'],
        'holiday_multiplier' => ['Resmi Tatil Çarpanı', 'Resmi tatil günlerinde çalışma için uygulanacak çarpan'],
        'overtime_multiplier' => ['Ek Mesai Çarpanı', 'Fazla mesai saatleri için uygulanacak çarpan']
    ];
    ?>
    <?php foreach($settings as $setting): ?>
    <?php $label = $settingLabels[$setting['setting_key']] ?? [$setting['setting_key'], '']; ?>
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50">
        <td class="px-6 py-4">
            <div>
                <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($label[0]); ?></span>
                <?php if (!empty($label[1])): ?>
                <p class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($label[1]); ?></p>
                <?php endif; ?>
            </div>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="text-lg font-bold text-purple-600 dark:text-purple-400"><?php echo htmlspecialchars($setting['setting_value']); ?>x</span>
        </td>
        <td class="px-6 py-4 text-center">
            <button onclick='openSettingModal(<?php echo json_encode($setting); ?>)' class="px-4 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors flex items-center gap-2 mx-auto">
                <span class="material-symbols-outlined text-sm">edit</span>
                Düzenle
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
