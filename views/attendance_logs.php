<?php include __DIR__ . '/layout/header.php'; ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <!-- Header -->
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-2">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Tüm Hareketler</h2>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Geçmişe dönük personel giriş-çıkışları.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <?php if (current_role() === 'admin'): ?>
                <button onclick="document.getElementById('manualEntryModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 h-[38px] rounded-lg flex items-center justify-center gap-2 transition-all font-bold text-sm">
                    <span class="material-symbols-outlined text-[20px]">event_note</span>
                    İzin/Tatil Girişi
                </button>
                <?php endif; ?>
                
                <form method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3 bg-slate-50 dark:bg-slate-800/50 p-2 sm:p-3 rounded-xl border border-slate-200 dark:border-slate-700 w-full sm:w-auto">
                <div class="flex flex-col gap-1 min-w-[120px] flex-1 sm:flex-none">
                    <label class="text-[10px] font-bold text-slate-500 uppercase px-1">Başlangıç</label>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-primary focus:border-primary">
                </div>
                <div class="flex flex-col gap-1 min-w-[120px] flex-1 sm:flex-none">
                    <label class="text-[10px] font-bold text-slate-500 uppercase px-1">Bitiş</label>
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-primary focus:border-primary">
                </div>
                <button type="submit" class="bg-primary hover:bg-blue-600 text-white px-4 h-[38px] rounded-lg flex items-center justify-center gap-2 transition-all font-bold text-sm flex-1 sm:flex-none mt-auto">
                    <span class="material-symbols-outlined text-[20px]">filter_list</span>
                    Filtrele
                </button>
            </form>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 p-4 sm:px-8 flex flex-col min-h-0 w-full">
        <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col flex-1 min-h-0 w-full">
            <!-- Table Header -->
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-slate-800/50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Personel</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Giriş/Çıkış</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Durum</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Fazla Mesai</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-card-dark">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">Kayıt bulunamadı.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-gray-300">
                                    <?php echo date('d.m.Y', strtotime($log['date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                    <?php echo htmlspecialchars($log['full_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600 dark:text-gray-400">
                                    <?php echo substr($log['clock_in'], 0, 5); ?> - <?php echo substr($log['clock_out'], 0, 5); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($log['is_late']): ?>
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
                                            'excused' => ['İzinli (Mazeretli)', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'],
                                            'excused_late' => ['Mazeretli Geç', 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
                                            'paid_leave' => ['Ücretli İzin', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
                                            'weekly_leave' => ['Haftalık İzin', 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300'],
                                            'unpaid_leave' => ['Ücretsiz İzin', 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'],
                                            'annual_leave' => ['Yıllık İzin', 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
                                            'sick_leave' => ['Raporlu', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
                                            'absent' => ['Devamsız', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300']
                                        ];
                                        $label = $statusLabels[$log['status']] ?? [ucfirst($log['status']), 'bg-gray-100 text-gray-800'];
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $label[1]; ?>">
                                            <?php echo $label[0]; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($log['note'])): ?>
                                        <div class="mt-1 flex justify-center">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-gray-500 cursor-help" title="<?php echo htmlspecialchars($log['note']); ?>">description</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($log['overtime_hours'] > 0): ?>
                                        <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 font-bold text-sm">
                                            +<?php echo format_hours($log['overtime_hours']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="openEditAttendanceModal(<?php echo htmlspecialchars(json_encode($log)); ?>)" class="text-slate-400 hover:text-blue-500 transition-colors p-1" title="Düzenle">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <form method="POST" action="<?php echo public_url('api/delete-log'); ?>" onsubmit="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="id" value="<?php echo $log['id']; ?>">
                                            <button class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Sil">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Toplam <?php echo $totalLogs; ?> kayıt, Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?>
                </div>
                <div class="flex gap-2">
                    <?php 
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo public_url('attendance-logs'); ?>?page=<?php echo $page - 1 . $queryString; ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">
                            &laquo; Önceki
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?php echo public_url('attendance-logs'); ?>?page=<?php echo $i . $queryString; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-primary text-white border-primary' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'; ?> text-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo public_url('attendance-logs'); ?>?page=<?php echo $page + 1 . $queryString; ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">
                            Sonraki &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal: Katılım Düzenle (Dashboard'dan kopyalandı) -->
<div id="editAttendanceModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark p-6 rounded-xl w-full max-w-lg shadow-2xl border border-gray-200 dark:border-border-dark overflow-y-auto max-h-[90vh]">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Kaydı Düzenle</h3>
        <form method="POST" action="<?php echo public_url('api/edit-attendance'); ?>" class="flex flex-col gap-4">
            <input type="hidden" name="id" id="edit_log_id">
            <input type="hidden" name="user_id" id="edit_log_user_id">
            
            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Tarih</span>
                    <input type="date" name="date" id="edit_log_date" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Vardiya</span>
                    <select name="shift_id" id="edit_log_shift" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="1">Sabah (A)</option>
                    </select>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Giriş Saati</span>
                    <input type="time" name="clock_in" id="edit_log_in" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Çıkış Saati</span>
                    <input type="time" name="clock_out" id="edit_log_out" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </label>
            </div>

            <div class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300 mb-2 block">Durum & İstisnalar</span>
                <div class="flex flex-col gap-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="present" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Normal Mesai</span>
                    </label>
                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="holiday" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Resmi Tatil</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="excused" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">İzinli (Mazeretli)</span>
                    </label>
                     <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="excused_late" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Mazeretli Geç Kalma</span>
                    </label>
                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="annual_leave" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Yıllık İzin</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="paid_leave" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Ücretli İzinli</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="unpaid_leave" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Ücretsiz İzinli</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="weekly_leave" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Haftalık İzin</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input name="status" value="sick_leave" type="radio" class="size-4 rounded-full border-gray-300 dark:border-gray-600 bg-white dark:bg-surface-dark text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300 text-red-500 font-bold">Raporlu (Sağlık)</span>
                    </label>
                </div>
            </div>

            <label class="block mb-2">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Not / Açıklama</span>
                <textarea name="note" id="edit_log_note" rows="3" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-background-dark border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm"></textarea>
            </label>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('editAttendanceModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">İptal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-600">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditAttendanceModal(log) {
        document.getElementById('edit_log_id').value = log.id;
        document.getElementById('edit_log_user_id').value = log.user_id;
        document.getElementById('edit_log_date').value = log.date;
        document.getElementById('edit_log_shift').value = log.shift_id || 1;
        document.getElementById('edit_log_note').value = log.note || '';
        
        // Saatleri formatla (sadece HH:MM kısmını al)
        document.getElementById('edit_log_in').value = log.clock_in ? log.clock_in.substring(0, 5) : '09:00';
        document.getElementById('edit_log_out').value = log.clock_out ? log.clock_out.substring(0, 5) : '17:00';
        
        // Radio butonunu seç
        const status = log.status || 'present';
        const radio = document.querySelector(`input[name="status"][value="${status}"]`);
        if(radio) radio.checked = true;
        
        document.getElementById('editAttendanceModal').classList.remove('hidden');
    }
    
    // Dışarı tıklayınca kapatma
    window.onclick = function(event) {
        const editModal = document.getElementById('editAttendanceModal');
        const manualModal = document.getElementById('manualEntryModal');
        if (event.target == editModal) editModal.classList.add('hidden');
        if (event.target == manualModal) manualModal.classList.add('hidden');
    }
</script>

<!-- Modal: Manuel İzin/Tatil Girişi -->
<div id="manualEntryModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">İzin / Tatil Girişi</h3>
            <button onclick="document.getElementById('manualEntryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form action="<?php echo public_url('api/clock-in'); ?>" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Personel Seçiniz (Toptan Tatil için Boş Bırakmayın)</label>
                <select name="user_id" required class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                    <option value="">Seçiniz...</option>
                    <?php 
                        // Dashboard'dan gelen $users'ı kullanamayız burada (sadece 1 kişi olabilir), index.php'den geçmeli veya burada query atmalıyız.
                        // Attendance_logs controller'ında $users seçilmiyor olabilir.
                        // Şimdilik admin ise tüm kullanıcıları çekecek bir script ekleyelim controller'a.
                    ?>
                    <?php if (isset($all_users)): ?>
                        <?php foreach($all_users as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Tarih</label>
                    <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Tür</label>
                    <select name="status" class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                        <option value="present">Normal Mesai</option>
                        <option value="holiday">Resmi Tatil</option>
                        <option value="annual_leave">Yıllık İzin</option>
                        <option value="paid_leave">Ücretli İzin</option>
                        <option value="sick_leave">Raporlu</option>
                        <option value="unpaid_leave">Ücretsiz İzin</option>
                        <option value="weekly_leave">Haftalık İzin</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="clock_in" value="-">
            <input type="hidden" name="clock_out" value="-">

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Not / Açıklama</label>
                <textarea name="note" rows="2" placeholder="Örn: 29 Ekim Cumhuriyet Bayramı" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary p-3"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('manualEntryModal').classList.add('hidden')" class="flex-1 h-11 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 font-bold text-sm">İptal</button>
                <button type="submit" class="flex-1 h-11 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">Kaydı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
