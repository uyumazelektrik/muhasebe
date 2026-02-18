<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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
                            <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="attendance_logs_full_body" class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-card-dark">
                        <?php $this->load->view('attendance/_logs_full_list', ['logs' => $logs]); ?>
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
                        <a href="<?php echo site_url('attendance-logs'); ?>?page=<?php echo $page - 1 . $queryString; ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">
                            &laquo; Önceki
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo site_url('attendance-logs'); ?>?page=<?php echo $page + 1 . $queryString; ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">
                            Sonraki &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal: Katılım Düzenle -->
<div id="editAttendanceModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">Kaydı Düzenle</h3>
            <button onclick="document.getElementById('editAttendanceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="editAttendanceForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="edit_log_id">
            <input type="hidden" name="user_id" id="edit_log_user_id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Tarih</label>
                    <input type="date" name="date" id="edit_log_date" required class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Tür</label>
                    <select name="status" id="edit_log_status" class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                        <option value="present">Normal Mesai</option>
                        <option value="excused_late">Mazeretli Geç Kalma</option>
                        <option value="holiday">Resmi Tatil</option>
                        <option value="annual_leave">Yıllık İzin</option>
                        <option value="paid_leave">Ücretli İzin</option>
                        <option value="sick_leave">Raporlu</option>
                        <option value="unpaid_leave">Ücretsiz İzin</option>
                        <option value="weekly_leave">Haftalık İzin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Giriş Saati</label>
                    <input type="time" name="clock_in" id="edit_log_in" required class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Çıkış Saati</label>
                    <input type="time" name="clock_out" id="edit_log_out" required class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Not / Açıklama</label>
                <textarea name="note" id="edit_log_note" rows="2" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary p-3"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('editAttendanceModal').classList.add('hidden')" class="flex-1 h-11 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 font-bold text-sm">İptal</button>
                <button type="button" onclick="saveEditAttendance()" class="flex-1 h-11 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 transition-all shadow-lg">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: İzin/Tatil Girişi -->
<div id="manualEntryModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">İzin / Tatil Girişi</h3>
            <button onclick="document.getElementById('manualEntryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="manualAttendanceForm" class="p-6 space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Personel Seçiniz</label>
                <select name="user_id" required class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                    <option value="">Seçiniz...</option>
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
                        <option value="excused_late">Mazeretli Geç Kalma</option>
                        <option value="holiday">Resmi Tatil</option>
                        <option value="annual_leave">Yıllık İzin</option>
                        <option value="paid_leave">Ücretli İzin</option>
                        <option value="sick_leave">Raporlu</option>
                        <option value="unpaid_leave">Ücretsiz İzin</option>
                        <option value="weekly_leave">Haftalık İzin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Giriş Saati</label>
                    <input type="time" name="clock_in" value="09:00" class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Çıkış Saati</label>
                    <input type="time" name="clock_out" value="17:00" class="w-full h-11 bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Not / Açıklama</label>
                <textarea name="note" rows="2" placeholder="Örn: 29 Ekim Cumhuriyet Bayramı" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary p-3"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('manualEntryModal').classList.add('hidden')" class="flex-1 h-11 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 font-bold text-sm">İptal</button>
                <button type="button" onclick="saveManualAttendance()" class="flex-1 h-11 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-all shadow-lg">Kaydı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAttendanceModal(log) {
    document.getElementById('edit_log_id').value = log.id;
    document.getElementById('edit_log_user_id').value = log.user_id;
    document.getElementById('edit_log_date').value = log.date;
    document.getElementById('edit_log_status').value = log.status || 'present';
    document.getElementById('edit_log_note').value = log.note || '';
    document.getElementById('edit_log_in').value = log.clock_in ? log.clock_in.substring(0, 5) : '09:00';
    document.getElementById('edit_log_out').value = log.clock_out ? log.clock_out.substring(0, 5) : '17:00';
    
    document.getElementById('editAttendanceModal').classList.remove('hidden');
}

async function refreshFullLogs() {
    const url = new URL('<?php echo site_url('api/get-attendance-logs-html'); ?>');
    const params = new URLSearchParams(window.location.search);
    url.search = params.toString();
    
    try {
        const res = await fetch(url);
        const html = await res.text();
        document.getElementById('attendance_logs_full_body').innerHTML = html;
    } catch(e) {
        console.error('Logs refresh failed:', e);
    }
}

async function saveEditAttendance() {
    const form = document.getElementById('editAttendanceForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const btn = event.target;
    const originalText = btn.innerText;

    btn.disabled = true;
    btn.innerText = 'Güncelleniyor...';
    
    try {
        const res = await fetch('<?php echo site_url('api/edit-attendance'); ?>', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if(result.status === 'success') {
            document.getElementById('editAttendanceModal').classList.add('hidden');
            showToast('Kayıt başarıyla güncellendi', 'success');
            await refreshFullLogs();
        } else {
            showToast('Hata: ' + result.message, 'error');
        }
    } catch(e) { 
        showToast('Bağlantı hatası: ' + e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

async function saveManualAttendance() {
    const form = document.getElementById('manualAttendanceForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const btn = event.target;
    const originalText = btn.innerText;

    if(!data.user_id) {
        showToast('Lütfen personel seçin', 'info');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Oluşturuluyor...';
    
    try {
        const res = await fetch('<?php echo site_url('api/save-attendance'); ?>', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if(result.status === 'success') {
            document.getElementById('manualEntryModal').classList.add('hidden');
            form.reset();
            showToast('İzin/Tatil kaydı oluşturuldu', 'success');
            await refreshFullLogs();
        } else {
            showToast('Hata: ' + result.message, 'error');
        }
    } catch(e) { 
        showToast('Bağlantı hatası: ' + e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

async function deleteLog(id) {
    if(!confirm('Bu kaydı silmek istediğinize emin misiniz?')) return;
    try {
        const res = await fetch('<?php echo site_url('api/delete-attendance'); ?>', {
            method: 'POST',
            body: JSON.stringify({id: id})
        });
        const result = await res.json();
        if(result.status === 'success') {
            showToast('Kayıt başarıyla silindi', 'success');
            await refreshFullLogs();
        } else {
            showToast('Hata: ' + result.message, 'error');
        }
    } catch(e) { 
        showToast('Bağlantı hatası: ' + e.message, 'error');
    }
}

// Close modals on outside click
window.onclick = function(event) {
    const editModal = document.getElementById('editAttendanceModal');
    const manualModal = document.getElementById('manualEntryModal');
    if (event.target == editModal) editModal.classList.add('hidden');
    if (event.target == manualModal) manualModal.classList.add('hidden');
}
</script>
