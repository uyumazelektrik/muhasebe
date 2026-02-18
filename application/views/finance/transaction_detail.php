<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <a href="javascript:history.back()" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">
                        <?php 
                            $type_titles = [
                                'tahsilat' => 'Tahsilat Makbuzu',
                                'odeme' => 'Ödeme Detayı',
                                'virman' => 'Virman Detayı'
                            ];
                            echo $type_titles[$transaction['type']] ?? 'İşlem Detayı';
                        ?>
                    </h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal ml-9">
                    <?php echo $transaction['document_no'] ?: 'Belge #' . $transaction['id']; ?>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="edit_toggle_btn" onclick="toggleEditMode()" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-amber-500/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </button>
                <div id="edit_actions" class="hidden flex items-center gap-3">
                    <button type="button" onclick="cancelEditMode()" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">close</span>
                        İptal
                    </button>
                    <button type="button" onclick="saveTransaction()" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-green-500/30 flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Kaydet
                    </button>
                </div>
                <button type="button" onclick="deleteTransaction()" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-red-500/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">delete</span>
                    Sil
                </button>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <form id="transaction_form" class="w-full" method="POST">
            <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
            <input type="hidden" name="type" value="<?php echo $transaction['type']; ?>">
            
            <div class="max-w-2xl mx-auto bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 p-6 mb-6 shadow-xl relative overflow-hidden">
                <!-- Decoration -->
                <div class="absolute -top-10 -right-10 opacity-[0.03] rotate-12">
                    <span class="material-symbols-outlined text-[180px]">
                        <?php 
                            if($transaction['type'] == 'tahsilat') echo 'account_balance_wallet';
                            elseif($transaction['type'] == 'odeme') echo 'payments';
                            else echo 'sync_alt';
                        ?>
                    </span>
                </div>

                <div class="relative z-10 flex flex-col items-center text-center">
                    <span class="px-3 py-1 bg-primary font-black text-white text-[9px] uppercase tracking-[0.2em] rounded-full mb-4 shadow-lg shadow-primary/20">
                        <?php 
                            if($transaction['type'] == 'tahsilat') echo 'Tahsilat Makbuzu';
                            elseif($transaction['type'] == 'odeme') echo 'Ödeme Makbuzu';
                            else echo 'Virman / Transfer';
                        ?>
                    </span>

                    <div class="flex flex-col items-center">
                        <div class="view-mode">
                             <h2 class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter">
                                <?php echo number_format(abs($transaction['amount']), 2); ?> <span class="text-2xl font-bold opacity-30">₺</span>
                            </h2>
                        </div>
                        <div class="edit-mode hidden flex items-center justify-center">
                            <div class="relative group">
                                <input type="number" name="amount" id="txn_amount" step="0.01" value="<?php echo abs($transaction['amount']); ?>" 
                                       class="text-4xl font-black bg-slate-50 dark:bg-white/5 border-2 border-slate-100 dark:border-white/5 focus:border-primary focus:bg-white dark:focus:bg-[#1e293b] focus:ring-4 focus:ring-primary/10 text-center w-64 px-4 py-4 rounded-2xl text-slate-900 dark:text-white transition-all editable outline-none" disabled>
                                <span class="absolute -right-10 top-1/2 -translate-y-1/2 text-2xl font-black text-primary/50 group-focus-within:text-primary transition-colors">₺</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-4 text-slate-500 font-medium text-sm">
                        <div class="view-mode flex items-center gap-2">
                            <span class="material-symbols-outlined text-xs">calendar_today</span>
                            <?php echo date('d.m.Y', strtotime($transaction['transaction_date'])); ?>
                        </div>
                        <div class="edit-mode hidden flex items-center gap-2 bg-white dark:bg-white/5 px-6 py-2.5 rounded-2xl border-2 border-slate-100 dark:border-white/5 focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all cursor-pointer" onclick="this.querySelector('input').showPicker()">
                            <span class="material-symbols-outlined text-sm text-primary">calendar_today</span>
                            <input type="date" name="invoice_date" value="<?php echo $transaction['transaction_date']; ?>" 
                                   class="bg-transparent border-none p-0 text-sm focus:ring-0 text-slate-900 dark:text-white editable font-black outline-none cursor-pointer" disabled>
                        </div>
                    </div>
                    
                    <?php if(!empty($transaction['due_date']) && $transaction['due_date'] != $transaction['transaction_date']): ?>
                    <div class="flex items-center gap-2 mt-2 text-amber-600 dark:text-amber-400 font-medium text-sm">
                        <div class="view-mode flex items-center gap-2">
                            <span class="material-symbols-outlined text-xs">event</span>
                            Vade: <?php echo date('d.m.Y', strtotime($transaction['due_date'])); ?>
                        </div>
                        <div class="edit-mode hidden flex items-center gap-2 bg-white dark:bg-white/5 px-6 py-2.5 rounded-2xl border-2 border-amber-100 dark:border-amber-500/20 focus-within:border-amber-500 focus-within:ring-4 focus-within:ring-amber-500/10 transition-all cursor-pointer" onclick="this.querySelector('input').showPicker()">
                            <span class="material-symbols-outlined text-sm text-amber-600">event</span>
                            <input type="date" name="due_date" value="<?php echo $transaction['due_date']; ?>" 
                                   class="bg-transparent border-none p-0 text-sm focus:ring-0 text-amber-900 dark:text-amber-400 editable font-black outline-none cursor-pointer" disabled>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="edit-mode hidden flex items-center gap-2 mt-2 text-slate-500 font-medium text-sm">
                        <div class="flex items-center gap-2 bg-white dark:bg-white/5 px-6 py-2.5 rounded-2xl border-2 border-slate-100 dark:border-white/5 focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all cursor-pointer" onclick="this.querySelector('input').showPicker()">
                            <span class="material-symbols-outlined text-sm text-primary">event</span>
                            <span class="text-xs font-bold">Vade:</span>
                            <input type="date" name="due_date" value="<?php echo $transaction['transaction_date']; ?>" 
                                   class="bg-transparent border-none p-0 text-sm focus:ring-0 text-slate-900 dark:text-white editable font-black outline-none cursor-pointer" disabled>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="w-16 h-1 bg-slate-100 dark:bg-white/5 rounded-full my-6"></div>

                    <div class="w-full space-y-4 text-left">
                        
                        <?php if($transaction['type'] == 'virman'): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-4 border border-slate-100 dark:border-white/5">
                                <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xs">outbox</span>
                                    GÖNDEREN (KAYNAK)
                                </p>
                                <?php $sender = ($transaction['amount'] < 0) ? $transaction : ($linked_txn ?? $transaction); ?>
                                <div class="view-mode flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-red-100 dark:bg-red-500/10 text-red-600 flex items-center justify-center font-black text-xs shrink-0">
                                        <?php echo mb_substr($sender['entity_name'] ?? 'C', 0, 1); ?>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($sender['entity_name']); ?></h4>
                                </div>
                                <div class="edit-mode hidden mt-1">
                                    <div class="relative searchable-select z-50 text-left">
                                        <div class="relative">
                                            <input type="text" class="search-input w-full px-4 py-2 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none" 
                                                   placeholder="Cari Ara..." autocomplete="off" value="<?php echo htmlspecialchars($sender['entity_name']); ?>">
                                            <input type="hidden" name="entity_id" value="<?php echo $sender['entity_id']; ?>" class="hidden-input">
                                        </div>
                                        <div class="options-list absolute w-[280px] mt-2 bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-white/10 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                            <?php foreach($entities as $e): ?>
                                                <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                                    <span><?php echo htmlspecialchars($e['name']); ?></span>
                                                    <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-4 border border-slate-100 dark:border-white/5">
                                <p class="text-[9px] font-black text-green-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xs">move_to_inbox</span>
                                    ALICI (HEDEF)
                                </p>
                                <?php $receiver = ($transaction['amount'] > 0) ? $transaction : ($linked_txn ?? $transaction); ?>
                                <div class="view-mode flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-green-100 dark:bg-green-500/10 text-green-600 flex items-center justify-center font-black text-xs shrink-0">
                                        <?php echo mb_substr($receiver['entity_name'] ?? 'C', 0, 1); ?>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($receiver['entity_name']); ?></h4>
                                </div>
                                <div class="edit-mode hidden mt-1">
                                    <div class="relative searchable-select z-50 text-left">
                                        <div class="relative">
                                            <input type="text" class="search-input w-full px-4 py-2 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none" 
                                                   placeholder="Cari Ara..." autocomplete="off" value="<?php echo htmlspecialchars($receiver['entity_name'] ?? ''); ?>">
                                            <input type="hidden" name="linked_entity_id" value="<?php echo $receiver['entity_id'] ?? ''; ?>" class="hidden-input">
                                        </div>
                                        <div class="options-list absolute w-[280px] mt-2 bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-white/10 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                            <?php foreach($entities as $e): ?>
                                                <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                                    <span><?php echo htmlspecialchars($e['name']); ?></span>
                                                    <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-4 border border-slate-100 dark:border-white/5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-xs">person</span>
                                İlgili Cari
                            </p>
                            <div class="view-mode">
                                <a href="<?php echo site_url('customers/detail/' . $transaction['entity_id']); ?>" class="flex items-center gap-3 hover:bg-slate-100 dark:hover:bg-white/5 p-2 -m-2 rounded-xl transition-colors group">
                                    <div class="size-10 rounded-xl bg-slate-900 dark:bg-primary text-white flex items-center justify-center font-black text-lg shrink-0">
                                        <?php echo mb_substr($transaction['entity_name'] ?? 'C', 0, 1); ?>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-black text-slate-900 dark:text-white leading-tight group-hover:text-primary transition-colors"><?php echo htmlspecialchars($transaction['entity_name'] ?? 'Bilinmeyen'); ?></h4>
                                        <p class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($transaction['phone'] ?? 'Telefon belirtilmemiş'); ?></p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300 ml-auto opacity-0 group-hover:opacity-100 transition-all">chevron_right</span>
                                </a>
                            </div>
                            <div class="edit-mode hidden mt-1">
                                <div class="relative searchable-select z-[60] text-left">
                                    <div class="relative">
                                        <input type="text" class="search-input w-full px-4 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none" 
                                               placeholder="Cari Ara..." autocomplete="off" value="<?php echo htmlspecialchars($transaction['entity_name'] ?? ''); ?>">
                                        <input type="hidden" name="entity_id" value="<?php echo $transaction['entity_id']; ?>" class="hidden-input">
                                    </div>
                                    <div class="options-list absolute w-full mt-2 bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-white/10 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                        <?php foreach($entities as $e): ?>
                                            <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                                <span><?php echo htmlspecialchars($e['name']); ?></span>
                                                <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($transaction['type'] != 'virman'): ?>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-4 border border-slate-100 dark:border-white/5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-xs">account_balance</span>
                                KASA / BANKA
                            </p>
                            <div class="view-mode text-sm font-bold text-slate-800 dark:text-slate-200 uppercase">
                                <?php echo htmlspecialchars($transaction['wallet_name'] ?? 'Belirlenmedi'); ?>
                            </div>
                            <div class="edit-mode hidden mt-1">
                                <select name="wallet_id" class="editable w-full px-4 py-2.5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer" disabled>
                                    <option value="">Seçilmedi</option>
                                    <?php foreach($wallets as $w): ?>
                                        <option value="<?php echo $w['id']; ?>" <?php echo $transaction['wallet_id'] == $w['id'] ? 'selected' : ''; ?> class="bg-white dark:bg-[#1e293b]"><?php echo htmlspecialchars($w['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-4 border border-slate-100 dark:border-white/5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-xs">tag</span>
                                <?php echo $transaction['type'] == 'virman' ? 'TRANSFER AYRINTISI' : 'BELGE NUMARASI'; ?>
                            </p>
                            <div class="view-mode font-mono font-bold text-slate-800 dark:text-slate-200 text-sm">
                                <?php echo $transaction['document_no'] ?: '---'; ?>
                            </div>
                            <div class="edit-mode hidden mt-1">
                                <input type="text" name="invoice_no" value="<?php echo $transaction['document_no']; ?>" 
                                       class="editable w-full px-4 py-2.5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-mono font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white" placeholder="No giriniz..." disabled>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-xs">notes</span>
                                İşlem Notu
                            </p>
                            <div class="view-mode text-sm text-slate-600 dark:text-slate-300 italic leading-relaxed bg-amber-50 dark:bg-amber-500/5 p-3 rounded-xl border border-amber-100 dark:border-amber-500/10">
                                 "<?php echo htmlspecialchars(($transaction['notes'] ?? $transaction['description'] ?? '') ?: 'Açıklama girilmemiş.'); ?>"
                            </div>
                             <div class="edit-mode hidden">
                                <textarea name="notes" rows="5" class="editable w-full px-6 py-5 bg-white dark:bg-white/5 border-2 border-slate-100 dark:border-white/5 rounded-[2rem] text-sm font-medium focus:border-primary focus:bg-white dark:focus:bg-[#1e293b] focus:ring-4 focus:ring-primary/10 transition-all outline-none text-slate-900 dark:text-white leading-[1.8] min-h-[160px]" placeholder="İşlem notu giriniz..." disabled><?php echo ($transaction['notes'] ?? $transaction['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
var isEditMode = false;

function toggleEditMode() {
    isEditMode = true;
    document.querySelectorAll('.editable').forEach(el => el.disabled = false);
    
    document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
    
    setupSearchableSelects();

    document.getElementById('edit_actions').classList.remove('hidden');
    document.getElementById('edit_toggle_btn').classList.add('hidden');
}

function cancelEditMode() {
    location.reload();
}

function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.onclick = (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        };

        input.onkeyup = (e) => {
            const val = e.target.value.toLocaleLowerCase("tr-TR");
            list.classList.remove('hidden');
            options.forEach(opt => {
                const text = opt.textContent.toLocaleLowerCase("tr-TR");
                if(text.includes(val)) {
                    opt.classList.remove('hidden');
                } else {
                    opt.classList.add('hidden');
                }
            });
        };

        options.forEach(opt => {
            opt.onclick = (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
            };
        });

        document.onclick = (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        };
    });
}

async function saveTransaction() {
    const form = document.getElementById('transaction_form');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('<?php echo site_url('finance/api_save_transaction'); ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.status === 'success') {
            showToast('İşlem başarıyla güncellendi', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Hata: ' + result.message, 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

async function deleteTransaction() {
    const confirmed = await showConfirm({
        title: 'İşlemi Sil',
        message: 'Bu işlemi tamamen silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });
    
    if (!confirmed) return;

    const formData = new FormData();
    formData.append('transaction_id', <?php echo $transaction['id']; ?>);
    
    try {
        const response = await fetch('<?php echo site_url('api/delete_transaction'); ?>', { method: 'POST', body: formData });
        const res = await response.json();
        
        if (res.success || res.status === 'success') {
            showToast('İşlem başarıyla silindi', 'success');
            setTimeout(() => window.location.href = '<?php echo site_url('customers/detail/' . $transaction['entity_id']); ?>', 1000);
        } else {
            showToast(res.message || res.error, 'error');
        }
    } catch(err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}
</script>
