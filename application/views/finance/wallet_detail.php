<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="flex flex-col h-screen w-full overflow-hidden bg-background-light dark:bg-background-dark">
    <!-- Fixed Header -->
    <div class="shrink-0 border-b border-gray-100 dark:border-border-dark px-4 sm:px-8 pt-6 pb-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="<?php echo site_url('finance/wallets'); ?>" class="size-10 rounded-xl bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark flex items-center justify-center text-gray-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo htmlspecialchars($wallet['name']); ?></h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest 
                            <?php 
                                if($wallet['wallet_type'] === 'BANK') echo 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
                                elseif($wallet['wallet_type'] === 'CASH') echo 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400';
                                elseif($wallet['wallet_type'] === 'CREDIT_CARD') echo 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400';
                                else echo 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400';
                            ?>">
                            <?php 
                                $types = ['CASH'=>'Nakit', 'BANK'=>'Banka', 'CREDIT_CARD'=>'Kredi Kartı', 'SAFE'=>'Kasa'];
                                echo $types[$wallet['wallet_type']] ?? $wallet['wallet_type'];
                            ?>
                        </span>
                        <span class="text-xs text-gray-400 font-medium">| ID: #<?php echo $wallet['id']; ?></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openTransactionModal()" class="bg-primary text-white px-6 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    <span class="material-symbols-outlined text-[20px]">add_card</span> Yeni İşlem
                </button>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="shrink-0 border-b border-gray-100 dark:border-border-dark px-4 sm:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Balance Card -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-border-dark relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <span class="material-symbols-outlined text-6xl">account_balance_wallet</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Güncel Bakiye</p>
                <h3 class="text-3xl font-black <?php echo $wallet['balance'] >= 0 ? 'text-primary' : 'text-red-500'; ?>">
                    <?php echo number_format($wallet['balance'], 2); ?> <span class="text-lg"><?php echo $wallet['asset_type']; ?></span>
                </h3>
            </div>

            <!-- Transaction Count -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Toplam İşlem</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white"><?php echo $transaction_count; ?></h3>
            </div>

            <!-- Description -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark col-span-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Açıklama</p>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    <?php echo !empty($wallet['description']) ? htmlspecialchars($wallet['description']) : 'Açıklama girilmemiş'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Scrollable Transactions -->
    <main class="flex-1 overflow-y-auto px-4 sm:px-8 py-6">
        <div class="bg-white dark:bg-card-dark rounded-3xl shadow-sm border border-gray-100 dark:border-border-dark overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-border-dark flex items-center justify-between">
                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">İşlem Geçmişi</h4>
                <div class="flex gap-2">
                    <button class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg text-gray-400"><span class="material-symbols-outlined">filter_list</span></button>
                    <button class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg text-gray-400"><span class="material-symbols-outlined">download</span></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-gray-50/50 dark:bg-surface-dark/50">
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-4 text-left">Tarih</th>
                            <th class="px-6 py-4 text-left">İşlem Türü</th>
                            <th class="px-6 py-4 text-left">Cari</th>
                            <th class="px-6 py-4 text-left">Açıklama</th>
                            <th class="px-6 py-4 text-right text-red-400">Çıkış</th>
                            <th class="px-6 py-4 text-right text-emerald-400">Giriş</th>
                            <th class="px-6 py-4 text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-border-dark">
                        <?php if(empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <span class="material-symbols-outlined text-5xl mb-2 block opacity-50">receipt_long</span>
                                Bu kasada henüz işlem bulunmuyor.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($transactions as $t): 
                            // For WALLET perspective: tahsilat/collection = money IN, odeme/payment = money OUT
                            $type = $t['type'];
                            $isIncome = in_array($type, ['tahsilat', 'collection', 'income']);
                            $isExpense = in_array($type, ['odeme', 'payment', 'expense']);
                            // If not specifically income or expense, use amount sign
                            if (!$isIncome && !$isExpense) {
                                $isIncome = $t['amount'] > 0;
                                $isExpense = $t['amount'] < 0;
                            }
                            // Check if this is an invoice-related transaction
                            $isInvoice = in_array($type, ['fatura', 'fis', 'invoice']);
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                <?php echo date('d.m.Y', strtotime($t['transaction_date'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full
                                    <?php if($t['type'] === 'tahsilat' || $t['type'] === 'collection') echo 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'; ?>
                                    <?php if($t['type'] === 'odeme' || $t['type'] === 'payment') echo 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'; ?>
                                    <?php if($t['type'] === 'fatura' || $t['type'] === 'fis') echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'; ?>">
                                    <?php echo strtoupper($t['type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if(!empty($t['entity_id'])): ?>
                                <a href="<?php echo site_url('customers/detail/'.$t['entity_id']); ?>" 
                                   class="text-sm font-bold text-primary hover:text-blue-700 transition-colors">
                                    <?php echo htmlspecialchars($t['entity_name'] ?? 'Cari #'.$t['entity_id']); ?>
                                </a>
                                <?php else: ?>
                                <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($t['description']); ?></p>
                                <?php if(!empty($t['document_no'])): ?>
                                <a href="<?php echo site_url('invoices/detail/'.$t['id']); ?>" 
                                   class="text-xs text-primary hover:text-blue-700 font-mono inline-flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-xs">receipt</span>
                                    <?php echo $t['document_no']; ?>
                                </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($isExpense): ?>
                                    <span class="text-sm font-black text-red-500"><?php echo number_format(abs($t['amount']), 2); ?> <?php echo $wallet['asset_type']; ?></span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($isIncome): ?>
                                    <span class="text-sm font-black text-green-600 dark:text-green-400"><?php echo number_format(abs($t['amount']), 2); ?> <?php echo $wallet['asset_type']; ?></span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick='editWalletTransaction(<?php echo json_encode($t); ?>)' 
                                            class="p-1.5 hover:bg-amber-100 dark:hover:bg-amber-500/10 text-gray-400 hover:text-amber-600 rounded-lg transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button onclick="deleteWalletTransaction(<?php echo $t['id']; ?>)" 
                                            class="p-1.5 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 rounded-lg transition-colors" title="Sil">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Transaction Modal -->
<div id="transactionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-black text-gray-900 dark:text-white">Yeni Kasa İşlemi</h3>
            <button onclick="closeTransactionModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl text-gray-400">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="transactionForm" class="p-6 space-y-5">
            <input type="hidden" id="txn_id" name="id" value="">
            <input type="hidden" name="wallet_id" value="<?php echo $wallet['id']; ?>">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">İşlem Türü *</label>
                <select id="txn_type" name="type" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    <option value="tahsilat">Tahsilat (Giriş)</option>
                    <option value="odeme">Ödeme (Çıkış)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tutar *</label>
                <input type="number" id="txn_amount" name="amount" step="0.01" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                    placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama *</label>
                <input type="text" id="txn_description" name="description" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                    placeholder="İşlem açıklaması">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                <input type="date" id="txn_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeTransactionModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" id="modalSubmitBtn" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTransactionModal() {
    resetModalForAdd();
    document.getElementById('transactionModal').classList.remove('hidden');
    document.getElementById('transactionModal').classList.add('flex');
}

function closeTransactionModal() {
    document.getElementById('transactionModal').classList.add('hidden');
    document.getElementById('transactionModal').classList.remove('flex');
}

document.getElementById('transactionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const txnId = formData.get('id');
    
    // Determine if this is add or edit
    const url = txnId ? '<?php echo site_url('finance/api_edit_wallet_transaction'); ?>' : '<?php echo site_url('finance/api_add_wallet_transaction'); ?>';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(txnId ? 'İşlem güncellendi' : 'İşlem kaydedildi');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || 'İşlem başarısız'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});

document.getElementById('transactionModal').addEventListener('click', function(e) {
    if (e.target === this) closeTransactionModal();
});

var isEditMode = false;


// Make compatible with SPA by attaching to window
window.editWalletTransaction = function(trx) {
    // If it's an invoice or has a document number, redirect to invoice details
    if (trx.type === 'fatura' || trx.type === 'fis' || trx.type === 'invoice' || (trx.document_no && trx.document_no.length > 0)) {
        // Use navigateTo if available for SPA, or fallback
        const target = '<?php echo site_url('invoices/detail/'); ?>' + trx.id;
        if (typeof navigateTo === 'function') {
            navigateTo(target);
        } else {
            window.location.href = target;
        }
        return;
    }

    isEditMode = true;
    document.getElementById('modalTitle').textContent = 'İşlem Düzenle';
    document.getElementById('modalSubmitBtn').textContent = 'Güncelle';
    
    // Populate form fields
    document.getElementById('txn_id').value = trx.id;
    document.getElementById('txn_type').value = trx.type;
    document.getElementById('txn_amount').value = Math.abs(trx.amount);
    document.getElementById('txn_description').value = trx.description || '';
    document.getElementById('txn_date').value = trx.transaction_date;
    
    // Open modal
    document.getElementById('transactionModal').classList.remove('hidden');
    document.getElementById('transactionModal').classList.add('flex');
};

// Reset modal for adding new transaction
function resetModalForAdd() {
    isEditMode = false;
    document.getElementById('modalTitle').textContent = 'Yeni Kasa İşlemi';
    document.getElementById('modalSubmitBtn').textContent = 'Kaydet';
    document.getElementById('txn_id').value = '';
    document.getElementById('txn_type').value = 'tahsilat';
    document.getElementById('txn_amount').value = '';
    document.getElementById('txn_description').value = '';
    document.getElementById('txn_date').value = '<?php echo date('Y-m-d'); ?>';
}

// Delete transaction
async function deleteWalletTransaction(id) {
    if (!confirm('Bu işlemi silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz ve kasa bakiyesi güncellenecektir.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('transaction_id', id);
    
    try {
        const response = await fetch('<?php echo site_url('api/delete_transaction'); ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success || result.status === 'success') {
            showToast('İşlem silindi');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || result.error || 'İşlem silinemedi'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}
</script>
