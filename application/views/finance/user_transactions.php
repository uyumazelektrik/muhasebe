<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-2">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Cari Kart - <?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Finansal hareketler ve izinler.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo site_url('users'); ?>" class="grow sm:grow-0 px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-gray-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors font-medium text-xs sm:text-sm text-center">
                Geri Dön
            </a>
            <button onclick="document.getElementById('addTransactionModal').classList.remove('hidden')" class="grow sm:grow-0 flex items-center justify-center gap-2 px-3 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors font-bold text-xs sm:text-sm shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[16px] sm:text-[18px]">add</span>
                Yeni İşlem
            </button>
        </div>
    </div>
</header>

<main class="flex-1 p-4 sm:px-8 w-full min-w-0">
    <?php if ($this->input->get('status')): ?>
    <div class="mb-4">
        <?php if ($this->input->get('status') === 'success'): ?>
            <div class="px-4 py-3 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                İşlem başarıyla gerçekleştirildi.
            </div>
        <?php else: ?>
            <div class="px-4 py-3 rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                İşlem sırasında bir hata oluştu.
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="mb-6">
        <form method="GET" action="<?php echo site_url('finance/user-transactions'); ?>" class="flex flex-wrap items-center gap-2 sm:gap-3 bg-slate-50 dark:bg-slate-800/50 p-2 sm:p-3 rounded-xl border border-slate-200 dark:border-slate-700">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <div class="flex flex-col gap-1 min-w-[100px] flex-1 sm:flex-none">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Ay</span>
                <select name="month" class="h-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-card-dark px-3 text-sm text-slate-900 dark:text-white focus:border-primary">
                    <option value="">Tümü</option>
                    <?php 
                        $months = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
                        foreach ($months as $k=>$m) {
                            $sel = ($month==$k)?'selected':'';
                            echo "<option value='$k' $sel>$m</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="flex flex-col gap-1 min-w-[100px] flex-1 sm:flex-none">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1">Yıl</span>
                <select name="year" class="h-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-card-dark px-3 text-sm text-slate-900 dark:text-white focus:border-primary">
                    <option value="">Tümü</option>
                    <?php foreach (range(date('Y')-1, date('Y')+1) as $y) { $sel = ($year==$y)?'selected':''; echo "<option value='$y' $sel>$y</option>"; } ?>
                </select>
            </div>
            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white hover:bg-blue-600 transition-colors font-bold text-sm mt-auto">Filtrele</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="p-4 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">payments</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo number_format($totals['payment'], 2); ?> ₺</h4>
                    <span class="text-sm text-[#9da6b9]">Toplam Ödeme</span>
                </div>
            </div>
        </div>
        
        <div class="p-4 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">trending_down</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo number_format($totals['advance'], 2); ?> ₺</h4>
                    <span class="text-sm text-[#9da6b9]">Toplam Avans</span>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <?php if ($balance >= 0): ?>
                <div class="size-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">trending_up</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-green-600 dark:text-green-400"><?php echo number_format($balance, 2); ?> ₺</h4>
                    <span class="text-sm text-[#9da6b9]">Alacak (Firmadan)</span>
                </div>
                <?php else: ?>
                <div class="size-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">trending_down</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-red-600 dark:text-red-400"><?php echo number_format(abs($balance), 2); ?> ₺</h4>
                    <span class="text-sm text-[#9da6b9]">Borç (Firmaya)</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">beach_access</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo $usedLeave; ?> / <?php echo $user['annual_leave_days']; ?></h4>
                    <span class="text-sm text-[#9da6b9]">Kullanılan İzin (Gün)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden w-full">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">İşlem Geçmişi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase">Tarih</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase">Tür</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase">Açıklama</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Tutar</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Henüz işlem kaydı bulunmamaktadır.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $txn): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-gray-300">
                                <?php echo date('d.m.Y', strtotime($txn['date'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php
                                $typeLabels = [
                                    'payment' => ['Ödeme', 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
                                    'advance' => ['Avans', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
                                    'expense' => ['Harcama', 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                                    'salary_accrual' => ['Maaş Tahakkuku', 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300']
                                ];
                                $label = $typeLabels[$txn['type']] ?? ['Diğer', 'bg-gray-100 text-gray-800'];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $label[1]; ?>">
                                    <?php echo $label[0]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-gray-400">
                                <?php echo htmlspecialchars($txn['description'] ?: '-'); ?>
                            </td>

                            <?php 
                                if($txn["affects_balance"]==1){ $symbol =  "+"; $color="text-green-600 dark:text-green-400"; } 
                                else if($txn["affects_balance"]==-1){ $symbol = "-"; $color="text-red-600 dark:text-red-400"; } 
                                else { $symbol = ""; $color="text-slate-600 dark:text-gray-400"; }    
                            ?>

                            <td class="px-6 py-4 text-sm font-bold text-right <?php echo $color; ?>">
                                <?php echo $symbol; ?> <?php echo number_format($txn['amount'], 2); ?> ₺
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditTransactionModal(<?php echo htmlspecialchars(json_encode($txn)); ?>)" class="text-slate-400 hover:text-blue-500 transition-colors p-1" title="Düzenle">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <form method="POST" action="<?php echo site_url('api/delete-staff-transaction'); ?>" onsubmit="return confirm('Bu işlemi silmek istediğinize emin misiniz?');" style="margin:0">
                                        <input type="hidden" name="id" value="<?php echo $txn['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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
        
        <?php if ($totalPages > 1): ?>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="text-sm text-slate-500 dark:text-slate-400">
                Toplam <?php echo $totalTransactions; ?> kayıt, Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?>
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?php echo site_url('finance/user-transactions') . '?user_id=' . $user['id'] . ($month? '&month='.$month.'&year='.$year:'') . '&page=' . ($page-1); ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">&laquo; Önceki</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo site_url('finance/user-transactions') . '?user_id=' . $user['id'] . ($month? '&month='.$month.'&year='.$year:'') . '&page=' . ($page+1); ?>" class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-600 dark:text-slate-300">Sonraki &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal: Add Transaction -->
<div id="addTransactionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark p-6 rounded-xl w-full max-w-md shadow-2xl border border-gray-200 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Yeni İşlem Ekle</h3>
        <form method="POST" action="<?php echo site_url('api/add-staff-transaction'); ?>" class="flex flex-col gap-4">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">İşlem Türü</span>
                <select name="type" id="add_txn_type" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                    <option value="payment">Ödeme (Maaş)</option>
                    <option value="advance">Avans</option>
                    <option value="expense">Harcama</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Tutar (₺)</span>
                <input type="number" step="0.01" name="amount" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Tarih</span>
                <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>

            <div class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300 mb-2 block">Bakiye Etkisi</span>
                <div class="flex flex-col gap-2 border border-gray-200 dark:border-slate-700 rounded-lg p-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="0" class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Etkisiz (Sadece Kayıt)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="1" checked class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Bakiyeye Ekle (+)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="-1" class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Bakiyeden Düş (-)</span>
                    </label>
                </div>
                <p class="text-[10px] text-slate-500 mt-2 px-1">Ödeme (+): Firmanın personele borcunu ödemesi. Avans (-): Personelin firmadan alacağıdır.</p>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Açıklama</span>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary"></textarea>
            </label>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('addTransactionModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-700 text-slate-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800">İptal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-600">Ekle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Transaction -->
<div id="editTransactionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark p-6 rounded-xl w-full max-w-md shadow-2xl border border-gray-200 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">İşlemi Düzenle</h3>
        <form method="POST" action="<?php echo site_url('api/edit-staff-transaction'); ?>" class="flex flex-col gap-4">
            <input type="hidden" name="id" id="edit_txn_id">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">İşlem Türü</span>
                <select name="type" id="edit_txn_type" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                    <option value="payment">Ödeme (Maaş)</option>
                    <option value="advance">Avans</option>
                    <option value="expense">Harcama</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Tutar (₺)</span>
                <input type="number" step="0.01" name="amount" id="edit_txn_amount" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Tarih</span>
                <input type="date" name="date" id="edit_txn_date" required class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
            </label>

            <div class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300 mb-2 block">Bakiye Etkisi</span>
                <div class="flex flex-col gap-2 border border-gray-200 dark:border-slate-700 rounded-lg p-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="0" id="edit_affects_0" class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Etkisiz (Sadece Kayıt)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="1" id="edit_affects_1" class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Bakiyeye Ekle (+)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="affects_balance" value="-1" id="edit_affects_-1" class="size-4 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-gray-300">Bakiyeden Düş (-)</span>
                    </label>
                </div>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Açıklama</span>
                <textarea name="description" id="edit_txn_description" rows="3" class="mt-1 block w-full rounded-lg bg-gray-50 dark:bg-input-dark border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary"></textarea>
            </label>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('editTransactionModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-700 text-slate-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800">İptal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-600">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditTransactionModal(txn) {
        document.getElementById('edit_txn_id').value = txn.id;
        document.getElementById('edit_txn_type').value = txn.type;
        document.getElementById('edit_txn_amount').value = txn.amount;
        document.getElementById('edit_txn_date').value = txn.date;
        
        const affectsValue = txn.affects_balance || 0;
        const radioId = 'edit_affects_' + affectsValue;
        const radio = document.getElementById(radioId);
        if (radio) radio.checked = true;
        
        document.getElementById('edit_txn_description').value = txn.description || '';
        document.getElementById('editTransactionModal').classList.remove('hidden');
    }

    function setAddAffectsByType() {
        const type = document.getElementById('add_txn_type').value;
        const radios = document.querySelectorAll('#addTransactionModal input[name="affects_balance"]');
        radios.forEach(r => r.checked = false);
        if (type === 'advance') {
            document.querySelector('#addTransactionModal input[name="affects_balance"][value="-1"]').checked = true;
        } else if (type === 'payment' || type === 'salary_accrual') {
            document.querySelector('#addTransactionModal input[name="affects_balance"][value="0"]').checked = true;
        } else if (type === 'expense') {
            document.querySelector('#addTransactionModal input[name="affects_balance"][value="1"]').checked = true;
        }
    }
    document.getElementById('add_txn_type').addEventListener('change', setAddAffectsByType);

    // Close on outside click
    window.onclick = function(event) {
        const addModal = document.getElementById('addTransactionModal');
        const editModal = document.getElementById('editTransactionModal');
        if (event.target == addModal) addModal.classList.add('hidden');
        if (event.target == editModal) editModal.classList.add('hidden');
    }
</script>
