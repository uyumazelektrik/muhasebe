<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Top Header Area -->
<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Personel Yönetimi</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Personel listesini ve giriş bilgilerini yönetin.</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold tracking-wide transition-all shadow-lg shadow-primary/20 shrink-0">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Yeni Personel</span>
        </button>
    </div>
</header>

<!-- Content Scroll Area -->
<div class="flex-1 p-4 sm:px-8 w-full min-w-0">
    
    <!-- Data Table Card -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#1c222e]">
                        <th class="py-4 px-6 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Personel / Kullanıcı</th>
                        <th class="py-4 px-6 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Bugünkü Durum</th>
                        <th class="py-4 px-6 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Rol</th>
                        <th class="py-4 px-6 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Saatlik Ücret</th>
                        <th class="py-4 px-6 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-card-dark">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 font-black text-sm border border-slate-200 dark:border-slate-700">
                                    <?php echo strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8')); ?>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-bold text-sm"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                    <span class="text-[#9da6b9] text-[10px] uppercase font-bold tracking-tight">Kullanıcı: <?php echo htmlspecialchars($user['username'] ?: 'Tanımsız'); ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <?php if (!$user['today_status']): ?>
                                <span class="bg-slate-100 dark:bg-slate-800 text-slate-400 text-[10px] font-black uppercase px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700">Belirsiz</span>
                            <?php else: ?>
                                <?php 
                                    $s = $user['today_status'];
                                    
                                    // Default green (present)
                                    $color = 'bg-green-500/10 text-green-600 border-green-500/20';
                                    
                                    // Red (absent)
                                    if ($s === 'absent') {
                                        $color = 'bg-red-500/10 text-red-600 border-red-500/20';
                                    }
                                    
                                    // Purple (leaves)
                                    if (in_array($s, ['annual_leave', 'paid_leave', 'sick_leave', 'weekly_leave'])) {
                                        $color = 'bg-purple-500/10 text-purple-600 border-purple-500/20';
                                    }
                                    
                                    // Blue (holidays)
                                    if (in_array($s, ['holiday', 'public_holiday'])) {
                                        $color = 'bg-blue-500/10 text-blue-600 border-blue-500/20';
                                    }
                                    
                                    // Orange (late / excused late)
                                    if (in_array($s, ['late', 'excused_late'])) {
                                        $color = 'bg-orange-500/10 text-orange-600 border-orange-500/20';
                                    }
                                    
                                    // Gray (unpaid leave)
                                    if ($s === 'unpaid_leave') {
                                        $color = 'bg-slate-500/10 text-slate-600 border-slate-500/20';
                                    }
                                    
                                    $statusLabels = [
                                        'present' => 'İş Başında',
                                        'late' => 'Gecikmeli',
                                        'excused_late' => 'Mazeretli Geç',
                                        'absent' => 'Devamsız',
                                        'holiday' => 'Tatil',
                                        'public_holiday' => 'Resmi Tatil',
                                        'annual_leave' => 'Yıllık İzin',
                                        'sick_leave' => 'Raporlu',
                                        'paid_leave' => 'Ücretli İzin',
                                        'unpaid_leave' => 'Ücretsiz İzin',
                                        'weekly_leave' => 'Haftalık İzin'
                                    ];
                                ?>
                                <span class="<?php echo $color; ?> text-[10px] font-black uppercase px-2 py-1 rounded-md border">
                                    <?php echo $statusLabels[$s] ?? $s; ?>
                                </span>
                                <?php if ($user['clock_in'] && $user['clock_in'] !== '-'): ?>
                                    <p class="text-[9px] text-slate-400 mt-1 font-mono"><?php echo substr($user['clock_in'], 0, 5); ?> - <?php echo substr($user['clock_out'] ?? '--:--', 0, 5); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="text-[10px] font-black uppercase px-2 py-1 rounded-md border <?php echo $user['role'] === 'admin' ? 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20' : 'bg-slate-500/10 text-slate-600 border-slate-500/20'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Yönetici' : 'Personel'; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <span class="text-slate-900 dark:text-white font-black text-sm"><?php echo number_format($user['hourly_rate'], 2); ?> ₺</span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo site_url('finance/user-transactions') . '?user_id=' . $user['id']; ?>" class="text-slate-400 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 dark:hover:text-green-400 dark:hover:bg-green-500/10 transition-colors" title="Cari Kart">
                                    <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                                </a>
                                <button onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="text-slate-400 hover:text-blue-600 p-2 rounded-lg hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-500/10 transition-colors" title="Düzenle">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <form action="<?php echo site_url('api/delete-user'); ?>" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');" class="inline-block" style="margin:0;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="text-slate-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Sil">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yeni Personel Ekle -->
<div id="addUserModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">Yeni Personel Ekle</h3>
        <form method="POST" action="<?php echo site_url('api/add-user'); ?>" class="space-y-4">
            <label class="block">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Ad Soyad</span>
                <input type="text" name="full_name" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-primary text-sm h-11">
            </label>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Kullanıcı Adı</span>
                    <input type="text" name="username" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-primary text-sm h-11">
                </label>
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Şifre</span>
                    <input type="password" name="password" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-primary text-sm h-11">
                </label>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Rol</span>
                    <select name="role" class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-primary text-sm h-11">
                        <option value="personel">Personel</option>
                        <option value="admin">Yönetici</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Saatlik Ücret</span>
                    <input type="number" step="0.01" name="hourly_rate" value="0.00" class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-primary text-sm h-11">
                </label>
            </div>
            
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 transition-colors">İptal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 shadow-lg shadow-primary/20 transition-all">Personeli Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Personel Düzenle -->
<div id="editUserModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">Personel Düzenle</h3>
        <form method="POST" action="<?php echo site_url('api/edit-user'); ?>" class="space-y-4">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <label class="block">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Ad Soyad</span>
                <input type="text" name="full_name" id="edit_user_name" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm h-11">
            </label>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1 block min-h-[24px]">Kullanıcı Adı</span>
                    <input type="text" name="username" id="edit_username" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm h-11">
                </label>
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1 block min-h-[24px]">Yeni Şifre (İsteğe Bağlı)</span>
                    <input type="password" name="password" placeholder="Değişmeyecekse boş bırakın" class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm h-11">
                </label>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Rol</span>
                    <select name="role" id="edit_user_role" class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm h-11">
                        <option value="personel">Personel</option>
                        <option value="admin">Yönetici</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Saatlik Ücret</span>
                    <input type="number" step="0.01" name="hourly_rate" id="edit_user_rate" required class="mt-1 block w-full rounded-xl bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm h-11">
                </label>
            </div>
            
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 font-bold text-sm">İptal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-sm transition-all shadow-lg shadow-primary/20">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_user_name').value = user.full_name;
        document.getElementById('edit_user_role').value = user.role;
        document.getElementById('edit_user_rate').value = user.hourly_rate;
        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('editUserModal').classList.remove('hidden');
    }

    // Close on outside click
    window.onclick = function(event) {
        const addModal = document.getElementById('addUserModal');
        const editModal = document.getElementById('editUserModal');
        if (event.target == addModal) addModal.classList.add('hidden');
        if (event.target == editModal) editModal.classList.add('hidden');
    }
</script>
