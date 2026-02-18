<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Personel İşlemleri</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Personel yönetimi, giriş-çıkış takibi ve maaş işlemlerini yönetin</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        
        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            
            <!-- Giriş-Çıkış Kayıtları -->
            <a href="<?php echo site_url('attendance-logs'); ?>" class="group bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0 group-hover:bg-blue-500 transition-all duration-300">
                    <span class="material-symbols-outlined text-2xl text-blue-500 group-hover:text-white transition-colors">timer</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-blue-500 transition-colors">Giriş-Çıkış Kayıtları</h3>
                    <p class="text-xs text-slate-500">Tüm kayıtları görüntüle</p>
                </div>
                <span class="material-symbols-outlined text-slate-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

            <!-- Maaş & Hakediş -->
            <a href="<?php echo site_url('payroll'); ?>" class="group bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4 hover:border-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 transition-all duration-300">
                    <span class="material-symbols-outlined text-2xl text-emerald-500 group-hover:text-white transition-colors">payments</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-500 transition-colors">Maaş & Hakediş</h3>
                    <p class="text-xs text-slate-500">Hesaplamalara git</p>
                </div>
                <span class="material-symbols-outlined text-slate-400 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

            <!-- Personel Yönetimi -->
            <a href="<?php echo site_url('users'); ?>" class="group bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4 hover:border-purple-500 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center shrink-0 group-hover:bg-purple-500 transition-all duration-300">
                    <span class="material-symbols-outlined text-2xl text-purple-500 group-hover:text-white transition-colors">group</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-purple-500 transition-colors">Personel Yönetimi</h3>
                    <p class="text-xs text-slate-500">Personeli yönet</p>
                </div>
                <span class="material-symbols-outlined text-slate-400 group-hover:text-purple-500 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Hızlı Giriş-Çıkış Kayıt Formu -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl text-blue-500">add_circle</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hızlı Kayıt Ekle</h3>
                    </div>
                    
                    <form id="quick_attendance_form" class="space-y-4">
                        <!-- Personel Seçimi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Personel</label>
                            <select id="att_user_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                                <option value="">Personel Seçin</option>
                                <?php foreach($all_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Tarih -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tarih</label>
                            <input type="date" id="att_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                        </div>
                        
                        <!-- Giriş - Çıkış Saati -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Giriş</label>
                                <input type="time" id="att_clock_in" value="<?php echo $default_clock_in; ?>" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Çıkış</label>
                                <input type="time" id="att_clock_out" value="<?php echo $default_clock_out; ?>" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                            </div>
                        </div>
                        
                        <!-- Durum -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Durum</label>
                            <select id="att_status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                                <option value="present">Normal Mesai</option>
                                <option value="excused_late">Mazeretli Geç Kalma</option>
                                <option value="public_holiday">Resmi Tatil</option>
                                <option value="annual_leave">Yıllık İzin</option>
                                <option value="paid_leave">Ücretli İzin</option>
                                <option value="sick_leave">Raporlu</option>
                                <option value="unpaid_leave">Ücretsiz İzin</option>
                                <option value="weekly_leave">Haftalık İzin</option>
                            </select>
                        </div>
                        
                        <!-- Not -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Not (Opsiyonel)</label>
                            <input type="text" id="att_note" placeholder="Açıklama..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                        </div>
                        
                        <button type="submit" class="w-full px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <!-- Son Giriş-Çıkış Kayıtları -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-xl text-emerald-500">schedule</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Son Giriş-Çıkış Kayıtları</h3>
                                <p class="text-xs text-slate-500">Son 10 kayıt gösteriliyor</p>
                            </div>
                        </div>
                        <a href="<?php echo site_url('attendance-logs'); ?>" class="text-sm font-bold text-primary hover:text-blue-600 flex items-center gap-1">
                            Tümünü Gör
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Tarih</th>
                                    <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Personel</th>
                                    <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Giriş/Çıkış</th>
                                    <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Durum</th>
                                </tr>
                            </thead>
                            <tbody id="attendance_logs_body" class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-card-dark">
                                <?php $this->load->view('personnel/_logs_list', ['logs' => $logs]); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

    </main>
</div>

<!-- Toast Notification -->
<!-- Toast removed, using global layout toast -->

<script>
// Local toast removed, using global showToast(message, type)

// Refresh Logs Function
async function refreshLogs() {
    try {
        const response = await fetch('<?php echo site_url('api/get-attendance-logs'); ?>');
        const html = await response.text();
        document.getElementById('attendance_logs_body').innerHTML = html;
    } catch (err) {
        console.error('Logs refresh failed:', err);
    }
}

// Form Submit Handler
document.getElementById('quick_attendance_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    
    const data = {
        user_id: document.getElementById('att_user_id').value,
        date: document.getElementById('att_date').value,
        clock_in: document.getElementById('att_clock_in').value,
        clock_out: document.getElementById('att_clock_out').value,
        status: document.getElementById('att_status').value,
        note: document.getElementById('att_note').value
    };
    
    if (!data.user_id) {
        showToast('Lütfen personel seçin', 'info');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Kaydediliyor...';
    
    try {
        const response = await fetch('<?php echo site_url('api/save-attendance'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Kayıt başarıyla eklendi!', 'success');
            
            // Reset form
            document.getElementById('att_note').value = '';
            
            // Refresh logs dynamically
            await refreshLogs();
        } else {
            showToast(result.message || 'Bir sorun oluştu', 'error');
        }
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnContent;
    }
});
</script>
