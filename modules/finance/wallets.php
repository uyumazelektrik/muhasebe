<?php
// modules/finance/wallets.php
$pageTitle = "Kasa & Banka Yönetimi";
require_once __DIR__ . '/../../src/Models/WalletModel.php';
require_once __DIR__ . '/../../src/Models/EntityModel.php';

$walletModel = new WalletModel($pdo);
$wallets = $walletModel->getAllActive();

// Eğer detay isteniyorsa
$walletId = $_GET['id'] ?? null;
$walletDetail = null;
$movements = [];

if ($walletId) {
    $walletDetail = $walletModel->find($walletId);
    if ($walletDetail) {
        // Cüzdan hareketlerini getir
        $stmt = $pdo->prepare("
            SELECT t.*, e.name as entity_name 
            FROM inv_entity_transactions t
            LEFT JOIN inv_entities e ON t.entity_id = e.id
            WHERE t.wallet_id = ?
            ORDER BY t.transaction_date DESC, t.id DESC
        ");
        $stmt->execute([$walletId]);
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include __DIR__ . '/../../views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                <span class="p-3 bg-primary/10 rounded-2xl text-primary material-symbols-outlined text-3xl">payments</span>
                Kasa & Banka Durumu
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1 ml-1 font-medium">İşletme varlıklarının ve ödeme kaynaklarının takibi</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openTransferModal()" 
                    class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all">
                <span class="material-symbols-outlined">sync_alt</span>
                Virman / Transfer
            </button>
            <button onclick="openCashTransactionModal()" 
                    class="px-4 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition-all">
                <span class="material-symbols-outlined">add_card</span>
                Para Giriş/Çıkış
            </button>
            <button onclick="openWalletModal()" 
                    class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 shadow-lg shadow-primary/20 transition-all">
                <span class="material-symbols-outlined">add</span>
                Yeni Hesap/Kart Ekle
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
        <?php
        $cashTotal = 0; $bankTotal = 0; $ccTotal = 0;
        foreach($wallets as $w) {
            if($w['wallet_type'] == 'CASH') $cashTotal += $w['balance'];
            if($w['wallet_type'] == 'BANK_ACCOUNT') $bankTotal += $w['balance'];
            if($w['wallet_type'] == 'CREDIT_CARD') $ccTotal += $w['balance'];
        }
        ?>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-2xl text-white shadow-lg shadow-emerald-500/20">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-white/20 rounded-lg material-symbols-outlined">payments</span>
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Toplam Nakit</span>
            </div>
            <div class="text-3xl font-black"><?php echo number_format($cashTotal, 2); ?> ₺</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-2xl text-white shadow-lg shadow-blue-500/20">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-white/20 rounded-lg material-symbols-outlined">account_balance</span>
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Banka Mevduat</span>
            </div>
            <div class="text-3xl font-black"><?php echo number_format($bankTotal, 2); ?> ₺</div>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 p-6 rounded-2xl text-white shadow-lg shadow-rose-500/20">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-white/20 rounded-lg material-symbols-outlined">credit_card</span>
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Kart Borçları</span>
            </div>
            <div class="text-3xl font-black"><?php echo number_format($ccTotal, 2); ?> ₺</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Cüzdan Listesi -->
        <div class="<?php echo $walletId ? 'lg:col-span-3' : 'lg:col-span-12'; ?> space-y-4">
            <div class="grid grid-cols-1 <?php echo $walletId ? 'grid-cols-1' : 'md:grid-cols-2 lg:grid-cols-3'; ?> gap-4">
                <?php foreach ($wallets as $w): 
                    $isActive = $walletId == $w['id'];
                    $icon = 'account_balance_wallet';
                    if($w['wallet_type'] == 'CREDIT_CARD') $icon = 'credit_card';
                    if($w['wallet_type'] == 'BANK_ACCOUNT') $icon = 'account_balance';
                    if($w['wallet_type'] == 'CASH') $icon = 'payments';
                ?>
                <div class="group relative overflow-hidden bg-white dark:bg-card-dark rounded-2xl p-6 border-2 transition-all duration-300 <?php echo $isActive ? 'border-primary shadow-xl shadow-primary/10 -translate-y-1' : 'border-transparent hover:border-gray-200 dark:hover:border-gray-700 shadow-lg'; ?>">
                    
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-gray-400"><?php echo $icon; ?></span>
                    </div>

                    <!-- Actions -->
                    <div class="absolute top-4 right-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                        <button onclick="editWallet(<?php echo htmlspecialchars(json_encode($w)); ?>)" 
                                class="p-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shadow-lg transition-all" title="Düzenle">
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </button>
                        <button onclick="deleteWallet(<?php echo $w['id']; ?>)" 
                                class="p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 shadow-lg transition-all" title="Sil">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </button>
                    </div>

                    <a href="<?php echo public_url('finance/wallets?id='.$w['id']); ?>" class="absolute inset-0 z-10"></a>

                    <div class="relative z-10 pointer-events-none">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-2 bg-gray-100 dark:bg-gray-800 rounded-xl text-gray-500 dark:text-gray-400 material-symbols-outlined">
                                <?php echo $icon; ?>
                            </span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?php echo $w['wallet_type']; ?></span>
                        </div>
                        
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-1"><?php echo htmlspecialchars($w['name']); ?></h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 font-medium"><?php echo htmlspecialchars($w['owner_name']); ?></p>
                        
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-[10px] font-black text-gray-400 uppercase mb-1">Mevcut Bakiye</div>
                                <div class="text-2xl font-black <?php echo $w['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo number_format($w['balance'], 2); ?> 
                                    <span class="text-sm font-bold opacity-60 ml-1"><?php echo $w['asset_type']; ?></span>
                                </div>
                            </div>
                            <?php if ($w['limit_amount'] > 0): ?>
                            <div class="text-right">
                                <div class="text-[10px] font-black text-gray-400 uppercase mb-1">Limit</div>
                                <div class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <?php echo number_format($w['limit_amount'], 0); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Hareket Detayı -->
        <?php if ($walletId && $walletDetail): ?>
        <div class="lg:col-span-9">
            <div class="bg-white dark:bg-card-dark rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                     <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                             <span class="material-symbols-outlined text-primary">history</span>
                             <?php echo htmlspecialchars($walletDetail['name']); ?> Hareketleri
                             <?php if($walletDetail['wallet_type'] == 'CREDIT_CARD'): ?>
                                <span class="ml-2 px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-bold">KESİM: <?php echo $walletDetail['statement_day']; ?>. GÜN</span>
                             <?php endif; ?>
                        </h3>
                    </div>
                    <a href="<?php echo public_url('finance/wallets'); ?>" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </a>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 text-left">İşlem / Vade Tarihi</th>
                                <th class="px-6 py-4 text-left">Cari / Açıklama</th>
                                <th class="px-6 py-4 text-center">Taksit No</th>
                                <th class="px-6 py-4 text-right">Tutar</th>
                                <th class="px-6 py-4 text-center no-print">Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-5xl mb-3 opacity-20 block">folder_open</span>
                                    <p class="font-medium">Bu cüzdana ait henüz bir işlem bulunmuyor.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($movements as $m): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-mono text-gray-800 dark:text-white">
                                            <?php echo date('d.m.Y', strtotime($m['transaction_date'])); ?>
                                        </div>
                                        <?php if($m['due_date'] && $m['due_date'] != $m['transaction_date']): ?>
                                        <div class="text-[10px] text-orange-500 font-bold uppercase mt-1">
                                            Vade: <?php echo date('d.m.Y', strtotime($m['due_date'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-800 dark:text-white">
                                            <?php echo htmlspecialchars($m['entity_name'] ?? 'Bilinmeyen'); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 truncate max-w-xs">
                                            <?php echo htmlspecialchars($m['description']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($m['installment_count'] > 1): ?>
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs font-black">
                                                <?php echo $m['installment_no'] ?? 1; ?> / <?php echo $m['installment_count']; ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-black <?php echo $m['amount'] < 0 ? 'text-red-600' : 'text-green-600'; ?>">
                                            <?php echo number_format($m['amount'], 2); ?> <?php echo $m['asset_type']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center no-print">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="editTransaction(<?php echo htmlspecialchars(json_encode($m)); ?>)" 
                                                    class="p-1 text-blue-500 hover:text-blue-700 transition-colors" title="Düzenle">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </button>
                                            <button onclick="deleteTransaction(<?php echo $m['id']; ?>)" 
                                                    class="p-1 text-red-500 hover:text-red-700 transition-colors" title="Sil">
                                                <span class="material-symbols-outlined text-sm">delete</span>
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
        </div>
        <?php else: ?>
        <div class="lg:col-span-12">
            <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-2xl p-8 text-center">
                <span class="material-symbols-outlined text-blue-400 text-5xl mb-4">info</span>
                <h3 class="text-xl font-bold text-blue-900 dark:text-blue-200 mb-2">Wallet Detaylarını Görüntüle</h3>
                <p class="text-blue-700 dark:text-blue-400 max-w-md mx-auto">Varlık hareketlerini ve hesap dökümünü incelemek için sol taraftaki kartlardan (veya yukarıdaki listeden) birini seçebilirsiniz.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>

<!-- TRANSFER MODAL -->
<div id="transferModal" class="fixed inset-0 bg-black/60 hidden z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <form id="transferForm" onsubmit="saveTransfer(event)" class="p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">sync_alt</span>
                Hesaplar Arası Virman / Transfer
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kaynak Hesap (Para Çıkacak)</label>
                    <select name="from_wallet_id" class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                        <option value="">Hesap Seçin...</option>
                        <?php foreach($wallets as $w): ?>
                            <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name'] . ' (' . $w['balance'] . ' ' . $w['asset_type'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hedef Hesap (Para Girecek)</label>
                    <select name="to_wallet_id" class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                        <option value="">Hesap Seçin...</option>
                        <?php foreach($wallets as $w): ?>
                            <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name'] . ' (' . $w['balance'] . ' ' . $w['asset_type'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Transfer Tutarı</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold text-2xl text-right" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">İşlem Tarihi</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Açıklama (Opsiyonel)</label>
                    <input type="text" name="description" placeholder="Örn: Nakit ödeme, Karta yatırılan" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold">
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeTransferModal()" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 transition-all">Vazgeç</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 shadow-lg shadow-amber-500/20 transition-all text-sm">Transferi Tamamla</button>
            </div>
        </form>
    </div>
</div>

<!-- CASH TRANSACTION MODAL -->
<div id="cashTransactionModal" class="fixed inset-0 bg-black/60 hidden z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <form id="cashTransactionForm" onsubmit="saveCashTransaction(event)" class="p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500">add_card</span>
                Manuel Para Giriş / Çıkış
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hesap / Kasa</label>
                    <select name="wallet_id" class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                        <option value="">Hesap Seçin...</option>
                        <?php foreach($wallets as $w): ?>
                            <option value="<?php echo $w['id']; ?>" <?php echo $walletId == $w['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($w['name'] . ' (' . $w['owner_name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">İşlem Yönü</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative">
                            <input type="radio" name="type" value="tahsilat" class="peer hidden" checked>
                            <div class="cursor-pointer text-center p-3 border rounded-xl peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 font-bold transition-all">
                                GİRİŞ (+)
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="type" value="odeme" class="peer hidden">
                            <div class="cursor-pointer text-center p-3 border rounded-xl peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-600 font-bold transition-all">
                                ÇIKIŞ (-)
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tutar</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold text-2xl text-right" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">İşlem Tarihi</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Açıklama</label>
                    <input type="text" name="description" placeholder="Örn: Sermaye ilavesi, Nakit satış" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeCashTransactionModal()" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 transition-all">Vazgeç</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition-all text-sm">Kaydet</button>
            </div>
        </form>
    </div>
</div>
<div id="walletModal" class="fixed inset-0 bg-black/60 hidden z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <form id="walletForm" onsubmit="saveWallet(event)" class="p-6">
            <input type="hidden" name="id" id="wallet_id">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_balance</span>
                Yeni Hesap / Kart Tanımla
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hesap Sahibi / Firma</label>
                    <input type="text" name="owner_name" placeholder="Örn: Uyumaz Elektrik, Şirket Adı" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                    <p class="text-[10px] text-gray-400 mt-1">Kartın veya hesabın ait olduğu firma bilgisini giriniz.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hesap / Kart Adı</label>
                    <input type="text" name="name" placeholder="Örn: Garanti Bonus, Merkez Kasa, İş Bankası Ticari" 
                           class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tür</label>
                        <select name="wallet_type" class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold">
                            <option value="CASH">Nakit / Kasa</option>
                            <option value="BANK_ACCOUNT">Banka Hesabı</option>
                            <option value="CREDIT_CARD">Kredi Kartı</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Para Birimi</label>
                        <select name="asset_type" class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold">
                            <option value="TL">TL</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GOLD">Altın (GR)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Limit (Varsa)</label>
                        <input type="number" name="limit_amount" value="0" step="0.01" 
                               class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kesim Günü</label>
                        <input type="number" name="statement_day" value="1" min="1" max="31"
                               class="w-full px-4 py-3 border rounded-xl bg-white dark:bg-input-dark dark:border-gray-700 font-bold">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeWalletModal()" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 transition-all">Vazgeç</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-primary/20 transition-all">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openWalletModal() {
    document.getElementById('walletForm').reset();
    document.getElementById('wallet_id').value = '';
    document.getElementById('modalTitle').innerText = 'Yeni Hesap / Kart Tanımla';
    document.getElementById('walletModal').classList.remove('hidden');
}
function closeWalletModal() {
    document.getElementById('walletModal').classList.add('hidden');
}
function openTransferModal() {
    document.getElementById('transferForm').reset();
    document.getElementById('transferModal').classList.remove('hidden');
}
function closeTransferModal() {
    document.getElementById('transferModal').classList.add('hidden');
}
function openCashTransactionModal() {
    document.getElementById('cashTransactionForm').reset();
    document.getElementById('cashTransactionModal').classList.remove('hidden');
}
function closeCashTransactionModal() {
    document.getElementById('cashTransactionModal').classList.add('hidden');
}
function editWallet(w) {
    document.getElementById('wallet_id').value = w.id;
    document.getElementById('modalTitle').innerText = 'Hesap / Kart Düzenle';
    
    const form = document.getElementById('walletForm');
    form.elements['owner_name'].value = w.owner_name;
    form.elements['name'].value = w.name;
    form.elements['wallet_type'].value = w.wallet_type;
    form.elements['asset_type'].value = w.asset_type;
    form.elements['limit_amount'].value = w.limit_amount;
    form.elements['statement_day'].value = w.statement_day;
    
    document.getElementById('walletModal').classList.remove('hidden');
}
async function deleteWallet(id) {
    if (!confirm('Bu hesabı silmek istediğinize emin misiniz? Hareket kayıtları korunacak ancak hesap listeden kaldırılacaktır.')) return;
    
    try {
        const response = await fetch('<?php echo public_url('api/delete-wallet'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}
async function saveWallet(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('<?php echo public_url('api/save-wallet'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}
async function saveTransfer(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('<?php echo public_url('api/wallet-transfer'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}

async function saveCashTransaction(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('<?php echo public_url('api/wallet-transaction'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}

async function deleteTransaction(id) {
    if (!confirm('Bu hareketi silmek istediğinize emin misiniz? Varsa bağlantılı diğer kayıtlar da silinecektir.')) return;
    
    try {
        const response = await fetch('<?php echo public_url('api/delete-entity-transaction'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}

function editTransaction(m) {
    // Cari ekstredeki detay modalını kullanabiliriz ama modal kodunu buraya da eklememiz lazım.
    // Şimdilik sadece cari ekstre sayfasına yönlendirelim? 
    // Hayır, kullanıcı burada düzenlemek istiyor.
    // Cari ekstredeki modal çok karmaşık, şimdilik basit bir yönlendirme yapalım veya modal ekleyelim.
    if(m.entity_id) {
        window.location.href = '<?php echo public_url('entity/statement?id='); ?>' + m.entity_id + '&open_id=' + m.id;
    }
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
