<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col w-full min-h-screen md:h-screen md:overflow-hidden bg-background-light dark:bg-background-dark">
    <!-- Fixed Header -->
    <div class="shrink-0 border-b border-gray-100 dark:border-border-dark px-4 sm:px-8 pt-6 pb-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a id="back_btn" href="<?php echo site_url('entities'); ?>" class="size-10 rounded-xl bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark flex items-center justify-center text-gray-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <script>
                (function() {
                    try {
                        const prevUrl = sessionStorage.getItem('app_prev_url');
                        const currentHost = window.location.host;
                        const currentPath = window.location.pathname;
                        
                        if (prevUrl && prevUrl.includes(currentHost)) {
                            // Ensure we don't link back to the exact same page (loop prevention)
                            if (!prevUrl.includes(currentPath) && !prevUrl.includes('/api/')) {
                                document.getElementById('back_btn').href = prevUrl;
                            }
                        }
                    } catch(e) { console.log('Back nav error', e); }
                })();
                </script>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo htmlspecialchars($entity['name']); ?></h2>
                    <div class="flex items-center gap-2 mt-1">
                        <?php 
                            $typeLabel = 'Bilinmeyen';
                            $typeClass = 'bg-gray-100 text-gray-700';
                            
                            if ($entity['type'] === 'customer') {
                                $typeLabel = 'Müşteri';
                                $typeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
                            } elseif ($entity['type'] === 'supplier') {
                                $typeLabel = 'Tedarikçi';
                                $typeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400';
                            } elseif ($entity['type'] === 'both') {
                                $typeLabel = 'Müşteri & Tedarikçi';
                                $typeClass = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400';
                            } elseif ($entity['type'] === 'staff') {
                                $typeLabel = 'Personel';
                                $typeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
                            }
                        ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest <?php echo $typeClass; ?>">
                            <?php echo $typeLabel; ?>
                        </span>
                        <span class="text-xs text-gray-400 font-medium">| ID: #<?php echo $entity['id']; ?></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 no-scrollbar">
                <button onclick="openVirmanModal()" class="whitespace-nowrap bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-gray-200 transition-all">
                     <span class="material-symbols-outlined text-[20px]">swap_horiz</span> Virman
                </button>
                <button onclick="openStatementModal()" class="whitespace-nowrap bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-gray-200 transition-all">
                     <span class="material-symbols-outlined text-[20px]">description</span> Ekstre
                </button>
                <button onclick="openShareModal()" class="whitespace-nowrap bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-indigo-100 transition-all border border-indigo-100 dark:border-indigo-500/20">
                     <span class="material-symbols-outlined text-[20px]">share</span> Paylaş
                </button>
                <button onclick="openEditModal()" class="whitespace-nowrap bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-gray-200 transition-all">
                    <span class="material-symbols-outlined text-[20px]">edit</span> Düzenle
                </button>
                <button onclick="openTransactionModal()" class="whitespace-nowrap bg-primary text-white px-6 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    <span class="material-symbols-outlined text-[20px]">add_card</span> Yeni İşlem
                </button>
            </div>
        </div>
    </div>

    <!-- Fixed Info Section -->
    <div class="shrink-0 border-b border-gray-100 dark:border-border-dark px-4 sm:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Balance Card -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-border-dark relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <span class="material-symbols-outlined text-6xl">account_balance_wallet</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Güncel Bakiye</p>
                <h3 class="text-3xl font-black <?php echo $entity['balance'] <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'; ?>">
                    <?php echo number_format(abs($entity['balance']), 2); ?> <span class="text-lg">₺</span>
                </h3>
                <p class="text-xs font-bold mt-2 <?php echo $entity['balance'] <= 0 ? 'text-green-600/60' : 'text-red-500/60'; ?>">
                    <?php echo $entity['balance'] <= 0 ? 'ALACAKLI DURUMDA' : 'BORÇLU DURUMDA'; ?>
                </p>

                <?php if($entity['balance'] > 0 && isset($debt_breakdown)): ?>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-border-dark space-y-1">
                    <div class="flex justify-between items-center">
                         <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Vadesi Geçmiş</span>
                         <span class="text-sm font-black text-red-500"><?php echo number_format($debt_breakdown['overdue'], 2); ?> ₺</span>
                    </div>
                    <div class="flex justify-between items-center">
                         <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Vadesi Gelecek</span>
                         <span class="text-sm font-black text-amber-500"><?php echo number_format($debt_breakdown['upcoming'], 2); ?> ₺</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Contact Info -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark">
                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4">İletişim Bilgileri</h4>
                <div class="space-y-4">
                    <?php if($entity['phone']): ?>
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-sm">call</span>
                        </div>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300"><?php echo $entity['phone']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($entity['email']): ?>
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-sm">email</span>
                        </div>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300"><?php echo $entity['email']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($entity['tax_id']): ?>
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-sm">receipt_long</span>
                        </div>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300"><?php echo $entity['tax_id']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark">
                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4">İstatistikler</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">Toplam İşlem</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white"><?php echo count($transactions); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">Kayıt Tarihi</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white"><?php echo date('d.m.Y', strtotime($entity['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Transactions Section: This is the ONLY part that scrolls -->
    <main class="flex-1 md:overflow-y-auto px-4 sm:px-8 py-6">
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
                            <th class="px-6 py-4 text-left text-amber-500">Vade Tarihi</th>
                            <th class="px-6 py-4 text-left">İşlem Türü</th>
                            <th class="px-6 py-4 text-left">Açıklama</th>
                            <th class="px-6 py-4 text-right text-red-400">Borç</th>
                            <th class="px-6 py-4 text-right text-emerald-400">Alacak</th>
                            <th class="px-6 py-4 text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-border-dark">
                        <?php foreach($transactions as $t): 
                            // Determine if amount should be in debt or credit column based on transaction type and entity type
                            $showInDebt = false;
                            $showInCredit = false;
                            
                            // Handle by amount sign (Most reliable for accounting)
                            if ($t['amount'] > 0) {
                                $showInDebt = true;
                            } elseif ($t['amount'] < 0) {
                                $showInCredit = true;
                            }
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-gray-900 dark:text-white">
                                    <?php echo date('d.m.Y', strtotime($t['transaction_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if(!empty($t['due_date'])): ?>
                                    <div class="text-xs font-bold text-amber-600 dark:text-amber-500">
                                        <?php echo date('d.m.Y', strtotime($t['due_date'])); ?>
                                    </div>
                                    <?php 
                                        $days_diff = (strtotime($t['due_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                                        // Only show overdue if:
                                        // 1. It is overdue (days_diff < 0)
                                        // 2. It is a Debt transaction (amount > 0)
                                        // 3. It is effectively "Open/Unpaid" based on current balance (open_amount > 0)
                                        // Note: open_amount is calculated in Controllers based on FIFO logic.
                                        $open_amt = isset($t['open_amount']) ? $t['open_amount'] : 0;
                                        
                                        if($days_diff < 0 && $t['amount'] > 0 && $open_amt > 0.01) { 
                                            echo '<span class="text-[9px] font-black text-red-500 uppercase tracking-tighter">(' . abs(intval($days_diff)) . ' gün gecikti)</span>';
                                        }
                                    ?>
                                <?php else: ?>
                                    <span class="text-xs text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full
                                    <?php if($t['type'] === 'fatura' || $t['type'] === 'invoice' || $t['type'] === 'fis' || $t['type'] === 'sale' || $t['type'] === 'purchase') echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'; ?>
                                    <?php if($t['type'] === 'tahsilat' || $t['type'] === 'collection') echo 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'; ?>
                                    <?php if($t['type'] === 'odeme' || $t['type'] === 'payment') echo 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'; ?>">
                                    <?php echo strtoupper($t['type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($t['description']); ?></p>
                                <?php if(!empty($t['document_no'])): ?>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded w-fit font-mono">
                                        <span class="material-symbols-outlined text-[14px]">description</span>
                                        <?php echo $t['document_no']; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($showInDebt): ?>
                                    <span class="text-sm font-black text-red-500"><?php echo number_format(abs($t['amount']), 2); ?> ₺</span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($showInCredit): ?>
                                    <span class="text-sm font-black text-green-600 dark:text-green-400"><?php echo number_format(abs($t['amount']), 2); ?> ₺</span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?php if(in_array($t['type'], ['fatura', 'fis', 'invoice', 'purchase', 'sale'])): ?>
                                        <a href="<?php echo site_url('invoices/detail/' . $t['id']); ?>" class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900/20 text-blue-600 rounded-lg transition-colors" title="İncele">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo site_url('finance/transaction_detail/' . $t['id']); ?>" class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900/20 text-blue-600 rounded-lg transition-colors" title="İncele">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="deleteTransaction(<?php echo $t['id']; ?>)" class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/20 text-red-600 rounded-lg transition-colors" title="Sil">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit Entity Modal -->
<div id="editEntityModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Cari Düzenle</h3>
            <button onclick="closeEditModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="<?php echo site_url('customers/api_update'); ?>" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="id" value="<?php echo $entity['id']; ?>">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Adı/Ünvanı</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($entity['name']); ?>" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Tipi</label>
                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="customer" <?php echo $entity['type'] == 'customer' ? 'selected' : ''; ?>>Müşteri</option>
                        <option value="supplier" <?php echo $entity['type'] == 'supplier' ? 'selected' : ''; ?>>Tedarikçi</option>
                        <option value="both" <?php echo $entity['type'] == 'both' ? 'selected' : ''; ?>>Her İkisi (Müşteri & Tedarikçi)</option>
                        <option value="staff" <?php echo $entity['type'] == 'staff' ? 'selected' : ''; ?>>Personel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Telefon</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($entity['phone']); ?>"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">E-posta</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($entity['email']); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vergi No / T.C.</label>
                <input type="text" name="tax_id" value="<?php echo htmlspecialchars($entity['tax_id']); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Adres</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"><?php echo htmlspecialchars($entity['address']); ?></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                    Güncelle
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statement Modal -->
<div id="statementModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Hesap Ekstresi Al</h3>
            <button onclick="closeStatementModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="<?php echo site_url('customers/statement/' . $entity['id']); ?>" method="POST" target="_blank" class="p-5 space-y-4">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Başlangıç</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-01'); ?>" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Bitiş</label>
                    <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Ekstre Tipi</label>
                <div class="grid grid-cols-2 gap-3">
                     <label class="cursor-pointer">
                        <input type="radio" name="type" value="detailed" checked class="peer sr-only">
                        <div class="p-3 rounded-xl border border-gray-200 dark:border-border-dark text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary hover:bg-gray-50 transition-all">
                            <span class="text-sm font-bold">Ayrıntılı</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="summary" class="peer sr-only">
                        <div class="p-3 rounded-xl border border-gray-200 dark:border-border-dark text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary hover:bg-gray-50 transition-all">
                            <span class="text-sm font-bold">Özet (Bakiye)</span>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Çıktı Formatı</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="submit" name="format" value="pdf" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition-colors">
                        <span class="material-symbols-outlined">picture_as_pdf</span>
                        <span class="text-sm font-bold">PDF / Yazdır</span>
                    </button>
                    <button type="submit" name="format" value="excel" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition-colors">
                         <span class="material-symbols-outlined">table_view</span>
                         <span class="text-sm font-bold">Excel</span>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- Add Transaction Modal -->
<div id="addTransactionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Yeni İşlem Ekle</h3>
            <button onclick="closeTransactionModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="<?php echo site_url('customers/api_add_transaction'); ?>" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="entity_id" value="<?php echo $entity['id']; ?>">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">İşlem Türü</label>
                    <select name="type" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="tahsilat">Tahsilat</option>
                        <option value="odeme">Ödeme</option>
                        <option value="borc_dekontu">Cari Borçlandırma (Borç Dekontu)</option>
                        <option value="alacak_dekontu">Cari Alacaklandırma (Alacak Dekontu)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vade Tarihi (Opsiyonel)</label>
                <input type="date" name="due_date" value="<?php echo date('Y-m-d'); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tutar</label>
                <div class="relative">
                    <input type="number" name="amount" step="0.01" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="0.00">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₺</span>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kasa/Banka (Opsiyonel)</label>
                <select name="wallet_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <option value="">Seçiniz...</option>
                    <?php foreach($wallets as $w): ?>
                        <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance'], 2); ?> <?php echo $w['asset_type']; ?>)</option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-gray-400 mt-1">Seçilirse kasa bakiyesi de güncellenir.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama</label>
                <textarea name="description" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTransactionModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Virman Modal -->
<div id="virmanModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Cariler Arası Virman</h3>
            <button onclick="closeVirmanModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="<?php echo site_url('customers/api_transfer'); ?>" method="POST" class="p-5 space-y-4">
            
            <div class="relative searchable-select z-30">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kaynak Cari (Para Çıkan)</label>
                <div class="relative">
                    <input type="text" class="search-input w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-text font-bold" 
                        placeholder="Cari aramak için yazın..." autocomplete="off">
                    <input type="hidden" name="source_entity_id" class="hidden-input" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="options-list absolute w-full mt-1 bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                    <?php if(isset($allEntities)) foreach($allEntities as $ae): ?>
                        <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-border-dark last:border-0 transition-colors flex justify-between items-center" data-value="<?php echo $ae['id']; ?>">
                            <span><?php echo htmlspecialchars($ae['name']); ?></span>
                            <span class="text-xs text-gray-400 ml-1 block sm:inline">(<?php echo number_format($ae['balance'], 2); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative searchable-select z-20">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hedef Cari (Para Giren)</label>
                <div class="relative">
                    <input type="text" class="search-input w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-text font-bold" 
                        placeholder="Cari aramak için yazın..." autocomplete="off">
                    <input type="hidden" name="target_entity_id" class="hidden-input" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="options-list absolute w-full mt-1 bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                    <?php if(isset($allEntities)) foreach($allEntities as $ae): ?>
                        <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-border-dark last:border-0 transition-colors flex justify-between items-center" data-value="<?php echo $ae['id']; ?>">
                            <span><?php echo htmlspecialchars($ae['name']); ?></span>
                            <span class="text-xs text-gray-400 ml-1 block sm:inline">(<?php echo number_format($ae['balance'], 2); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>



            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vade Tarihi</label>
                    <input type="date" name="due_date" value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Transfer Tutarı</label>
                <div class="relative">
                    <input type="number" name="amount" step="0.01" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="0.00">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₺</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama</label>
                <textarea name="description" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeVirmanModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                    Transferi Yap
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Share Link Modal -->
<div id="shareModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Online Ekstre Paylaş</h3>
            <button onclick="closeShareModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6 text-center space-y-4">
             <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-2">
                <span class="material-symbols-outlined text-3xl">public</span>
             </div>
             <p class="text-sm text-gray-500">Aşağıdaki bağlantıyı müşterinizle paylaşarak cari hesap dökümünü online görüntülemesini sağlayabilirsiniz.</p>
             
             <div class="relative">
                <input type="text" id="shareLinkInput" readonly class="w-full pl-4 pr-12 py-3 bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-border-dark rounded-xl text-xs font-mono text-gray-600 dark:text-gray-300 focus:ring-2 ring-indigo-500/50 outline-none" value="Yükleniyor...">
                <button onclick="copyShareLink()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 bg-white dark:bg-card-dark shadow-sm border border-gray-100 dark:border-border-dark rounded-lg text-gray-500 hover:text-indigo-600 transition-colors" title="Kopyala">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                </button>
             </div>
             
             <div id="copyFeedback" class="text-xs font-bold text-emerald-500 h-4 opacity-0 transition-opacity">Bağlantı kopyalandı!</div>
        </div>
    </div>
</div>

<script>
function openShareModal() {
    const modal = document.getElementById('shareModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    const input = document.getElementById('shareLinkInput');
    input.value = "Yükleniyor...";
    
    // Reset feedback
    document.getElementById('copyFeedback').classList.add('opacity-0');
    
    const formData = new FormData();
    formData.append('id', <?php echo $entity['id']; ?>);
    
    fetch('<?php echo site_url("customers/api_get_share_link"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            input.value = data.link;
        } else {
            input.value = "Hata oluştu";
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        input.value = "Hata";
        console.error(err);
    });
}

function closeShareModal() {
    const modal = document.getElementById('shareModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    if(input.value === 'Yükleniyor...' || input.value === 'Hata') return;
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    // Try modern API first, fallback to execCommand
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(showFeedback).catch(() => fallbackCopy());
    } else {
        fallbackCopy();
    }
    
    function fallbackCopy() {
        try {
            document.execCommand('copy');
            showFeedback();
        } catch (err) {
            console.error('Kopyalama başarısız');
        }
    }

    function showFeedback() {
        const feedback = document.getElementById('copyFeedback');
        feedback.classList.remove('opacity-0');
        setTimeout(() => feedback.classList.add('opacity-0'), 2000);
    }
}

function openEditModal() {
    document.getElementById('editEntityModal').classList.remove('hidden');
    document.getElementById('editEntityModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editEntityModal').classList.add('hidden');
    document.getElementById('editEntityModal').classList.remove('flex');
}

function openTransactionModal() {
    document.getElementById('addTransactionModal').classList.remove('hidden');
    document.getElementById('addTransactionModal').classList.add('flex');
}

function closeTransactionModal() {
    document.getElementById('addTransactionModal').classList.add('hidden');
    document.getElementById('addTransactionModal').classList.remove('flex');
}

// Connect buttons to functions (Handled via onclick attributes in HTML)

function viewTransaction(id) {
    window.location.href = '<?php echo site_url('invoices/detail/'); ?>' + id;
}

function editTransaction(id) {
    alert('İşlem ID: ' + id + '\n\nDüzenleme özelliği yakında eklenecektir.');
}

async function deleteTransaction(id) {
    const confirmed = await showConfirm({
        title: 'İşlemi Sil',
        message: 'Bu işlemi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve stok hareketlerini tersine çevirebilir.',
        confirmText: 'Evet, Sil',
        cancelText: 'İptal',
        type: 'danger'
    });
    
    if (!confirmed) return;
    
    const formData = new FormData();
    formData.append('transaction_id', id);
    
    try {
        const res = await fetch('<?php echo site_url('api/delete_transaction'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await res.json();
        
        if (result.success || result.status === 'success') {
            showToast('İşlem başarıyla silindi', 'success');
            
            // Remove row from DOM without reload
            const btn = document.querySelector(`button[onclick="deleteTransaction(${id})"]`);
            if (btn) {
                const row = btn.closest('tr');
                if (row) {
                    row.style.transition = 'opacity 0.5s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 500);
                }
            }
            
            // Update Transaction Count
            const countEl = document.querySelector('.bg-white .space-y-3 .flex:first-child .text-sm');
            if(countEl) {
                 const newCount = Math.max(0, parseInt(countEl.textContent) - 1);
                 countEl.textContent = newCount;
            }

            // Update Balance if provided by API
            if (result.new_balance !== undefined) {
                const balanceVal = parseFloat(result.new_balance);
                const absBalance = Math.abs(balanceVal);
                
                // Select elements
                const balanceH3 = document.querySelector('.bg-white h3.text-3xl');
                const statusP = balanceH3.nextElementSibling; // The paragraph below h3
                
                // Update text
                balanceH3.innerHTML = absBalance.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' <span class="text-lg">₺</span>';
                statusP.textContent = balanceVal <= 0 ? 'ALACAKLI DURUMDA' : 'BORÇLU DURUMDA';
                
                // Update colors
                if (balanceVal <= 0) {
                    balanceH3.className = 'text-3xl font-black text-green-600 dark:text-green-400';
                    statusP.className = 'text-xs font-bold mt-2 text-green-600/60';
                } else {
                    balanceH3.className = 'text-3xl font-black text-red-500';
                    statusP.className = 'text-xs font-bold mt-2 text-red-500/60';
                }
            }
        } else {
            const msg = result.message || result.error || 'İşlem silinemedi';
            showToast('Hata: ' + msg, 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

// Virman Modal Functions
function openVirmanModal() {
    const modal = document.getElementById('virmanModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeVirmanModal() {
    const modal = document.getElementById('virmanModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openStatementModal() {
    const modal = document.getElementById('statementModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeStatementModal() {
    const modal = document.getElementById('statementModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        });

        input.addEventListener('keyup', (e) => {
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
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
            });
        });

        document.addEventListener('click', (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        });
    });
}

// Initialize on load
function initCustomersDetailPage() {
    setupSearchableSelects();
    // Diğer sayfa bazlı başlatmalar buraya...
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomersDetailPage);
} else {
    initCustomersDetailPage();
}
</script>
