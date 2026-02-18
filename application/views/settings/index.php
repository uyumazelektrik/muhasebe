<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Sistem Ayarları</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Uygulama ayarlarını ve vardiya tanımlarını yönetin</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Vardiyalar Section -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl text-blue-500">schedule</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Vardiya Tanımları</h3>
                            <p class="text-xs text-slate-500">Çalışma vardiyalarını yönetin</p>
                        </div>
                    </div>
                    <button onclick="openShiftModal()" class="px-4 py-2 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Yeni Vardiya
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Vardiya Adı</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">Başlangıç</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">Bitiş</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="shifts_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php $this->load->view('settings/_shifts_list', ['shifts' => $shifts]); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Genel Ayarlar Section -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl text-purple-500">calculate</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Maaş Hesaplama Çarpanları</h3>
                            <p class="text-xs text-slate-500">Puantaj hesaplamalarında kullanılan çarpanlar</p>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Ayar</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">Değer</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="settings_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php $this->load->view('settings/_settings_list', ['settings' => $settings]); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Veritabanı Yönetimi Section -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden lg:col-span-2">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl text-orange-500">database</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Veritabanı Yönetimi</h3>
                            <p class="text-xs text-slate-500">Yedekleme, geri yükleme ve bakım işlemleri</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Actions -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2">İşlemler</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button onclick="checkDbConnection()" class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors text-left group">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-emerald-500 text-lg group-hover:scale-110 transition-transform">wifi</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Bağlantı Kontrol</span>
                                </button>

                                <button onclick="checkDbHealth()" class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors text-left group">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-blue-500 text-lg group-hover:scale-110 transition-transform">health_metrics</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Sağlık Kontrolü</span>
                                </button>

                                <div class="grid grid-cols-2 gap-2">
                                    <button onclick="backupDb()" class="flex items-center gap-2 px-3 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors text-left group">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-purple-500 text-lg group-hover:scale-110 transition-transform">cloud_download</span>
                                        </div>
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Tam Yedek</span>
                                    </button>

                                    <button onclick="openBackupModal()" class="flex items-center gap-2 px-3 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors text-left group">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-indigo-500 text-lg group-hover:scale-110 transition-transform">table_view</span>
                                        </div>
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Özel Yedek</span>
                                    </button>
                                </div>

                                <button onclick="document.getElementById('restoreFile').click()" class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors text-left group">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-amber-500 text-lg group-hover:scale-110 transition-transform">cloud_upload</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Yedek Yükle</span>
                                </button>
                                <input type="file" id="restoreFile" class="hidden" accept=".sql" onchange="restoreDb(this)">

                                <button onclick="clearDb()" class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl transition-colors text-left group sm:col-span-2">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-red-500 text-lg group-hover:scale-110 transition-transform">delete_forever</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-red-600 dark:text-red-400 block">Veritabanını Temizle</span>
                                        <span class="text-xs text-red-400 dark:text-red-500/70">Kullanıcılar ve ayarlar hariç tüm verileri siler</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Console/Log Output -->
                        <div class="flex flex-col h-full">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2 flex items-center justify-between">
                                İşlem Kayıtları
                                <button onclick="document.getElementById('dbConsole').innerHTML = ''" class="text-xs text-slate-400 hover:text-slate-600">Temizle</button>
                            </h4>
                            <div id="dbConsole" class="flex-1 bg-slate-900 rounded-xl p-4 font-mono text-xs text-green-400 overflow-y-auto max-h-[300px] min-h-[200px] whitespace-pre-wrap">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

    </main>
</div>

<!-- Shift Modal -->
<div id="shiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <!-- ... existing shift modal content ... -->
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 id="shiftModalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Vardiya Ekle</h3>
            <button onclick="closeShiftModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="shiftForm" class="p-6 space-y-4">
            <input type="hidden" id="shift_id" name="id">
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Vardiya Adı</label>
                <input type="text" id="shift_name" name="name" required placeholder="Örn: Sabah Vardiyası" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Başlangıç Saati</label>
                    <input type="time" id="shift_start" name="start_time" required value="09:00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Bitiş Saati</label>
                    <input type="time" id="shift_end" name="end_time" required value="18:00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeShiftModal()" class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors">İptal</button>
                <button type="submit" class="flex-1 px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-colors">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Backup Modal -->
<div id="backupModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Özel Yedekleme</h3>
            <button onclick="closeBackupModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto">
            <div class="mb-4">
                <p class="text-sm text-slate-500 mb-2">Yedeklemek istediğiniz tabloları seçin:</p>
                <div class="flex items-center gap-2 mb-3">
                    <input type="checkbox" id="selectAllTables" onchange="toggleAllTables(this)" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="selectAllTables" class="text-sm font-bold text-slate-700 dark:text-slate-300 cursor-pointer">Tümünü Seç/Kaldır</label>
                </div>
            </div>
            
            <div id="tablesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <!-- Tables will be loaded here -->
                <div class="text-center py-4 text-slate-400">Yükleniyor...</div>
            </div>
        </div>
        
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 shrink-0 flex gap-3">
            <button onclick="closeBackupModal()" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors">İptal</button>
            <button onclick="downloadSelectedBackup()" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">download</span>
                Seçilenleri İndir
            </button>
        </div>
    </div>
</div>

<!-- Setting Modal -->
<div id="settingModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 id="settingModalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Çarpan Düzenle</h3>
            <button onclick="closeSettingModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="settingForm" class="p-6 space-y-4">
            <input type="hidden" id="setting_key" name="key">
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Ayar</label>
                <div id="setting_label" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300"></div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Çarpan Değeri</label>
                <input type="number" id="setting_value" name="value" step="0.1" min="0" required placeholder="Örn: 1.5" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-center text-lg font-bold focus:border-primary transition-colors">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeSettingModal()" class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors">İptal</button>
                <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold transition-colors">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
// ==================== SHIFT FUNCTIONS ====================

window.openShiftModal = function(shift = null) {
    document.getElementById('shiftModalTitle').textContent = shift ? 'Vardiya Düzenle' : 'Yeni Vardiya';
    document.getElementById('shift_id').value = shift ? shift.id : '';
    document.getElementById('shift_name').value = shift ? shift.name : '';
    document.getElementById('shift_start').value = shift ? shift.start_time.substring(0, 5) : '09:00';
    document.getElementById('shift_end').value = shift ? shift.end_time.substring(0, 5) : '18:00';
    document.getElementById('shiftModal').classList.remove('hidden');
}

window.closeShiftModal = function() {
    document.getElementById('shiftModal').classList.add('hidden');
}

window.refreshShifts = async function() {
    try {
        const response = await fetch('<?php echo site_url('settings/api_get_shifts_html'); ?>');
        const html = await response.text();
        document.getElementById('shifts_tbody').innerHTML = html;
    } catch (err) {
        console.error('Shifts refresh failed:', err);
    }
}

document.getElementById('shiftForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    
    submitBtn.disabled = true;
    submitBtn.innerText = 'Kaydediliyor...';
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_save_shift'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Vardiya başarıyla kaydedildi', 'success');
            closeShiftModal();
            await refreshShifts();
        } else {
            showToast(result.message || 'Bir sorun oluştu', 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});

window.deleteShift = async function(id) {
    if (!confirm('Bu vardiyayı silmek istediğinizden emin misiniz?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_delete_shift'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Vardiya silindi', 'success');
            await refreshShifts();
        } else {
            showToast(result.message || 'Silme başarısız', 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

// ==================== SETTING FUNCTIONS ====================

var settingLabels = {
    'late_penalty_multiplier': 'Geç Kalma Ceza Çarpanı',
    'holiday_multiplier': 'Resmi Tatil Çarpanı',
    'overtime_multiplier': 'Ek Mesai Çarpanı'
};

window.openSettingModal = function(setting) {
    if (!setting) return; // No add new, only edit
    
    const label = settingLabels[setting.setting_key] || setting.setting_key;
    document.getElementById('settingModalTitle').textContent = label + ' Düzenle';
    document.getElementById('setting_key').value = setting.setting_key;
    document.getElementById('setting_label').textContent = label;
    document.getElementById('setting_value').value = setting.setting_value;
    document.getElementById('settingModal').classList.remove('hidden');
}

window.closeSettingModal = function() {
    document.getElementById('settingModal').classList.add('hidden');
}

window.refreshSettings = async function() {
    try {
        const response = await fetch('<?php echo site_url('settings/api_get_settings_html'); ?>');
        const html = await response.text();
        document.getElementById('settings_tbody').innerHTML = html;
    } catch (err) {
        console.error('Settings refresh failed:', err);
    }
}

document.getElementById('settingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    
    submitBtn.disabled = true;
    submitBtn.innerText = 'Güncelleniyor...';
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_save_setting'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Ayar başarıyla güncellendi', 'success');
            closeSettingModal();
            await refreshSettings();
        } else {
            showToast(result.message || 'Bir sorun oluştu', 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});

window.deleteSetting = async function(key) {
    if (!confirm('Bu ayarı silmek istediğinizden emin misiniz?')) return;
    
    const formData = new FormData();
    formData.append('key', key);
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_delete_setting'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Ayar silindi', 'success');
            await refreshSettings();
        } else {
            showToast(result.message || 'Silme başarısız', 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}


// ==================== DATABASE FUNCTIONS ====================


function logDb(message, isError = false) {
    const consoleEl = document.getElementById('dbConsole');
    const time = new Date().toLocaleTimeString();
    const color = isError ? 'text-red-400' : 'text-green-400';
    consoleEl.innerHTML += `<div class="${color}">[${time}] ${message}</div>`;
    consoleEl.scrollTop = consoleEl.scrollHeight;
}

window.checkDbConnection = async function() {
    logDb('Bağlantı kontrol ediliyor...');
    try {
        const response = await fetch('<?php echo site_url('settings/api_db_check_connection'); ?>');
        const result = await response.json();
        logDb(result.message, result.status !== 'success');
    } catch (err) {
        logDb('Hata: ' + err.message, true);
    }
}

window.checkDbHealth = async function() {
    logDb('Sağlık kontrolü yapılıyor...');
    try {
        const response = await fetch('<?php echo site_url('settings/api_db_check_health'); ?>');
        const result = await response.json();
        logDb(result.message, result.status !== 'success');
    } catch (err) {
        logDb('Hata: ' + err.message, true);
    }
}

// ==================== DATABASE FUNCTIONS ====================
window.backupDb = function() {
    logDb('Tam yedekleme başlatılıyor...');
    let url = '<?php echo site_url('settings/api_db_backup'); ?>';
    
    // Fix mixed content issue if on HTTPS
    if (window.location.protocol === 'https:' && url.startsWith('http:')) {
        url = url.replace('http:', 'https:');
    }
    
    window.location.href = url;
    logDb('Yedekleme dosyası indiriliyor.');
}


window.openBackupModal = function() {
    document.getElementById('backupModal').classList.remove('hidden');
    loadTables();
}

window.closeBackupModal = function() {
    document.getElementById('backupModal').classList.add('hidden');
}

window.loadTables = async function() {
    const list = document.getElementById('tablesList');
    list.innerHTML = '<div class="text-center py-4 text-slate-400">Yükleniyor...</div>';
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_get_tables'); ?>');
        const result = await response.json();
        
        if (result.status === 'success') {
            list.innerHTML = '';
            result.tables.forEach(table => {
                const item = document.createElement('div');
                item.className = 'flex items-center gap-2 p-2 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors';
                item.innerHTML = `
                    <input type="checkbox" id="table_${table}" value="${table}" class="table-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="table_${table}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer flex-1">${table}</label>
                `;
                list.appendChild(item);
            });
        } else {
            list.innerHTML = '<div class="text-red-400 text-center">Tablolar yüklenemedi.</div>';
        }
    } catch (err) {
        list.innerHTML = '<div class="text-red-400 text-center">Bağlantı hatası.</div>';
    }
}

window.toggleAllTables = function(source) {
    const checkboxes = document.querySelectorAll('.table-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

window.downloadSelectedBackup = function() {
    const selected = Array.from(document.querySelectorAll('.table-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('Lütfen en az bir tablo seçin.');
        return;
    }
    
    logDb('Özel yedekleme başlatılıyor (' + selected.length + ' tablo)...');
    
    // Construct URL with selected tables
    let url = '<?php echo site_url('settings/api_db_backup'); ?>?tables=' + selected.join(',');
    
    if (window.location.protocol === 'https:' && url.startsWith('http:')) {
        url = url.replace('http:', 'https:');
    }
    
    window.location.href = url;
    
    closeBackupModal();
    logDb('Özel yedek indiriliyor.');
}


window.restoreDb = async function(input) {
    if (!input.files || !input.files[0]) return;
    
    if (!confirm('DİKKAT! Bu işlem mevcut verilerin üzerine yazabilir. Devam etmek istiyor musunuz?')) {
        input.value = '';
        return;
    }

    const file = input.files[0];
    const formData = new FormData();
    formData.append('backup_file', file);
    
    logDb('Yükleme başlatılıyor: ' + file.name);
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_db_restore'); ?>', {
            method: 'POST',
            body: formData
        });
        
        let result;
        const text = await response.text();
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e, 'Response text:', text);
            logDb('Sunucudan geçersiz yanıt geldi. SQL dosyası çok büyük olabilir veya sunucu hatası oluştu.', true);
            return;
        }
        
        logDb(result.message, result.status !== 'success');
    } catch (err) {
        logDb('Bağlantı hatası: ' + err.message, true);
    }
    
    input.value = ''; // Reset
}

window.clearDb = async function() {
    if (!confirm('DİKKAT! Kullanıcılar ve ayarlar dışındaki TÜM VERİLER SİLİNECEK. Bu işlem geri alınamaz!\n\nDevam etmek için onaylayın.')) return;
    
    logDb('Veritabanı temizleniyor...');
    
    try {
        const response = await fetch('<?php echo site_url('settings/api_db_clear'); ?>', {
            method: 'POST'
        });
        const result = await response.json();
        logDb(result.message, result.status !== 'success');
        
        if (result.cleared_tables) {
            logDb('Silinen Tablolar: ' + result.cleared_tables.join(', '));
        }
    } catch (err) {
        logDb('Hata: ' + err.message, true);
    }
}

// Close modals on outside click
window.addEventListener('click', function(e) {
    if (e.target.id === 'shiftModal') closeShiftModal();
    if (e.target.id === 'settingModal') closeSettingModal();
});
</script>
