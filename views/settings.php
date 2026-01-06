<?php include __DIR__ . '/layout/header.php'; ?>

<!-- Top Header Area -->
<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-6 sm:px-8 shrink-0">
    <div class="flex flex-col gap-1 mb-2">
        <h2 class="text-slate-900 dark:text-white text-3xl font-black leading-tight tracking-tight">Sistem Ayarları</h2>
        <p class="text-[#9da6b9] text-base font-normal">Vardiya saatlerini ve genel sistem ayarlarını yapılandırın.</p>
    </div>
</header>

<div class="flex-1 overflow-y-auto p-6 sm:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- Shift Settings -->
        <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Vardiya Tanımları
                    </h3>
                    <p class="text-sm text-[#9da6b9] mt-1">Personelin çalışma saatlerini yönetin.</p>
                </div>
                <button onclick="document.getElementById('addShiftModal').classList.remove('hidden')" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-white text-sm font-bold rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Yeni Ekle
                </button>
            </div>
            
            <div class="p-6">
                <div class="grid gap-4">
                    <?php if (count($shifts) > 0): ?>
                        <?php foreach ($shifts as $shift): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 dark:text-white text-base"><?php echo htmlspecialchars($shift['name']); ?></span>
                                <div class="flex items-center gap-2 text-sm text-[#9da6b9] mt-1">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                                    <span><?php echo substr($shift['start_time'], 0, 5); ?> - <?php echo substr($shift['end_time'], 0, 5); ?></span>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button onclick="openEditShiftModal(<?php echo htmlspecialchars(json_encode($shift)); ?>)" class="flex items-center gap-1 px-3 py-1.5 rounded-md bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-blue-500 hover:text-blue-500 transition-colors text-sm font-medium">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                    Düzenle
                                </button>
                                <form action="<?php echo public_url('api/delete-shift'); ?>" method="POST" onsubmit="return confirm('Bu vardiyayı silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="shift_id" value="<?php echo $shift['id']; ?>">
                                    <button type="submit" class="flex items-center gap-1 px-3 py-1.5 rounded-md bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-red-500 hover:text-red-500 transition-colors text-sm font-medium">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-slate-500">
                            Henüz vardiya tanımlanmamış.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- General System Settings -->
        <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">tune</span>
                    Genel Ayarlar
                </h3>
                <p class="text-sm text-[#9da6b9] mt-1">Sistem genelindeki ceza ve hak ediş katsayılarını belirleyin.</p>
            </div>
            
            <form action="<?php echo public_url('api/update-gen-settings'); ?>" method="POST" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-[#9da6b9] uppercase mb-1">Geç Gelme Cezası Çarpanı</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="late_penalty_multiplier" value="<?php echo htmlspecialchars($settings['late_penalty_multiplier']); ?>" class="w-full pl-12 bg-white dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-md text-sm font-medium text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-bold">x</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Saat başına kesilecek ücret katsayısı.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#9da6b9] uppercase mb-1">Ek Mesai Çarpanı</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="overtime_multiplier" value="<?php echo htmlspecialchars($settings['overtime_multiplier']); ?>" class="w-full pl-12 bg-white dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-md text-sm font-medium text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-bold">x</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Normal mesai sonrası çalışma katsayısı.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#9da6b9] uppercase mb-1">Resmi Tatil Çarpanı</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="holiday_multiplier" value="<?php echo htmlspecialchars($settings['holiday_multiplier']); ?>" class="w-full pl-12 bg-white dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-md text-sm font-medium text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-bold">x</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Resmi tatillerde çalışma katsayısı.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Ayarları Güncelle
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Modal: Vardiya Ekle -->
<div id="addShiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl w-full max-w-md shadow-2xl border border-gray-200 dark:border-border-dark">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Yeni Vardiya Ekle</h3>
        <form method="POST" action="<?php echo public_url('api/add-shift'); ?>">
            <label class="block mb-3">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Vardiya Adı</span>
                <input type="text" name="name" required placeholder="Örn: Sabah" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Başlangıç</span>
                    <input type="time" name="start_time" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Bitiş</span>
                    <input type="time" name="end_time" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('addShiftModal')" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">İptal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-600">Ekle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Vardiya Düzenle -->
<div id="editShiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl w-full max-w-md shadow-2xl border border-gray-200 dark:border-border-dark">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Vardiya Düzenle</h3>
        <form method="POST" action="<?php echo public_url('api/edit-shift'); ?>">
            <input type="hidden" name="shift_id" id="edit_shift_id">
            <label class="block mb-3">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Vardiya Adı</span>
                <input type="text" name="name" id="edit_shift_name" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Başlangıç</span>
                    <input type="time" name="start_time" id="edit_shift_start" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Bitiş</span>
                    <input type="time" name="end_time" id="edit_shift_end" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('editShiftModal')" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">İptal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-600">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function openEditShiftModal(shift) {
        document.getElementById('edit_shift_id').value = shift.id;
        document.getElementById('edit_shift_name').value = shift.name;
        document.getElementById('edit_shift_start').value = shift.start_time;
        document.getElementById('edit_shift_end').value = shift.end_time;
        
        document.getElementById('editShiftModal').classList.remove('hidden');
    }
    
    // Basitçe dışarı tıklayınca kapatma
    window.onclick = function(event) {
        const modals = ['addShiftModal', 'editShiftModal'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (event.target == modal) {
                closeModal(id);
            }
        });
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
