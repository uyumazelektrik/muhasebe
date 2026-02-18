<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="flex flex-col h-screen w-full overflow-hidden bg-background-light dark:bg-background-dark">
    <!-- Stats Cards -->
    <div class="shrink-0 px-4 sm:px-8 pt-6 pb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total TL Positive -->
            <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 p-5 rounded-2xl border border-emerald-500/20">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-400 text-2xl">trending_up</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Toplam Varlık</p>
                        <p class="text-2xl font-black text-emerald-400">
                            <?php 
                            $positive = 0;
                            foreach($wallets as $w) if($w['balance'] > 0) $positive += $w['balance'];
                            echo number_format($positive, 2, ',', '.'); 
                            ?> <span class="text-sm">₺</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Negative (Debt) -->
            <div class="bg-gradient-to-br from-red-500/10 to-red-600/5 p-5 rounded-2xl border border-red-500/20">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">trending_down</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Toplam Borç</p>
                        <p class="text-2xl font-black text-red-400">
                            <?php 
                            $negative = 0;
                            foreach($wallets as $w) if($w['balance'] < 0) $negative += abs($w['balance']);
                            echo number_format($negative, 2, ',', '.'); 
                            ?> <span class="text-sm">₺</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Net Balance -->
            <div class="bg-gradient-to-br from-blue-500/10 to-indigo-600/5 p-5 rounded-2xl border border-blue-500/20">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-400 text-2xl">account_balance</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Net Durum (Bakiye)</p>
                        <p class="text-2xl font-black <?php echo $stats['total_tl'] >= 0 ? 'text-blue-400' : 'text-red-400'; ?>">
                            <?php echo number_format($stats['total_tl'], 2, ',', '.'); ?> <span class="text-sm">₺</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="shrink-0 px-4 sm:px-8 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex items-center gap-2">
            <button class="wallet-tab active px-5 py-2.5 rounded-xl text-sm font-bold transition-all" data-type="all">
                Tümü
            </button>
            <button class="wallet-tab px-5 py-2.5 rounded-xl text-sm font-bold transition-all" data-type="CASH">
                Nakit
            </button>
            <button class="wallet-tab px-5 py-2.5 rounded-xl text-sm font-bold transition-all" data-type="BANK">
                Banka
            </button>
            <button class="wallet-tab px-5 py-2.5 rounded-xl text-sm font-bold transition-all" data-type="CREDIT_CARD">
                Kredi Kartı
            </button>
        </div>

        <!-- Search & Actions -->
        <div class="flex items-center gap-3 flex-wrap">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">search</span>
                <input type="text" id="searchInput" placeholder="Kasa ismi ile ara..." 
                    class="pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 text-sm font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none w-56">
            </div>
            <button onclick="openTransferModal()" class="bg-purple-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-purple-700 transition-all">
                <span class="material-symbols-outlined text-lg">swap_horiz</span>
                Transfer
            </button>
            <button onclick="openExternalModal()" class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-emerald-700 transition-all">
                <span class="material-symbols-outlined text-lg">add_card</span>
                Para Giriş/Çıkış
            </button>
            <button onclick="openAddModal()" class="bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-600 transition-all">
                <span class="material-symbols-outlined text-lg">add</span>
                Yeni Kasa
            </button>
        </div>
    </div>

    <!-- Table -->
    <main class="flex-1 overflow-y-auto px-4 sm:px-8">
        <div class="bg-white dark:bg-card-dark rounded-2xl border border-gray-100 dark:border-border-dark overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-surface-dark border-b border-gray-100 dark:border-border-dark">
                    <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                        <th class="px-6 py-4 text-left">Kasa Bilgileri</th>
                        <th class="px-6 py-4 text-left">Tür</th>
                        <th class="px-6 py-4 text-left">Para Birimi</th>
                        <th class="px-6 py-4 text-right">Bakİye</th>
                        <th class="px-6 py-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="walletTableBody" class="divide-y divide-gray-50 dark:divide-border-dark">
                    <?php foreach($wallets as $w): 
                        $isPositive = $w['balance'] >= 0;
                    ?>
                    <tr class="wallet-row hover:bg-gray-50 dark:hover:bg-white/5 transition-colors" data-type="<?php echo $w['wallet_type']; ?>" data-name="<?php echo strtolower($w['name']); ?>">
                        <td class="px-6 py-4">
                            <p class="text-sm font-black text-gray-900 dark:text-white uppercase"><?php echo htmlspecialchars($w['name']); ?></p>
                            <p class="text-xs text-gray-400 font-mono">ID: #<?php echo $w['id']; ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider
                                <?php 
                                    $bgClass = 'bg-gray-100 dark:bg-gray-500/10 text-gray-700 dark:text-gray-400';
                                    
                                    switch($w['wallet_type']) {
                                        case 'BANK_ACCOUNT':
                                            $bgClass = 'bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400';
                                            break;
                                        case 'CASH':
                                            $bgClass = 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400';
                                            break;
                                        case 'CREDIT_CARD':
                                            $bgClass = 'bg-purple-100 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400';
                                            break;
                                        case 'GOLD_ACCOUNT':
                                            $bgClass = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
                                            break;
                                        case 'LOAN':
                                            $bgClass = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
                                            break;
                                        case 'SAFE':
                                            $bgClass = 'bg-cyan-100 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400';
                                            break;
                                    }
                                    echo $bgClass;
                                ?>">
                                <?php 
                                    $types = [
                                        'CASH' => 'Nakit Kasa', 
                                        'BANK_ACCOUNT' => 'Banka Hesabı', 
                                        'CREDIT_CARD' => 'Kredi Kartı', 
                                        'GOLD_ACCOUNT' => 'Altın Hesabı',
                                        'LOAN' => 'Kredi Hesabı',
                                        'SAFE' => 'Kasa (Safe)'
                                    ];
                                    echo $types[$w['wallet_type']] ?? ($w['wallet_type'] ?: 'Belirsiz');
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-gray-600 dark:text-gray-300"><?php echo $w['asset_type']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-black <?php echo $isPositive ? 'text-emerald-500' : 'text-red-500'; ?>">
                                <?php echo number_format($w['balance'], 2, ',', '.'); ?> <span class="text-xs font-bold"><?php echo $w['asset_type']; ?></span>
                            </p>
                            <p class="text-[10px] font-bold <?php echo $isPositive ? 'text-emerald-500/60' : 'text-red-500/60'; ?> uppercase">
                                <?php echo $isPositive ? 'Alacak' : 'Borç'; ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="<?php echo site_url('finance/wallet-detail/'.$w['id']); ?>" 
                                   class="p-2 hover:bg-blue-100 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-600 rounded-lg transition-colors" title="Detay">
                                    <span class="material-symbols-outlined text-xl">visibility</span>
                                </a>
                                <button onclick='openEditModal(<?php echo json_encode($w); ?>)' 
                                        class="p-2 hover:bg-amber-100 dark:hover:bg-amber-500/10 text-gray-400 hover:text-amber-600 rounded-lg transition-colors" title="Düzenle">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </button>
                                <button onclick="deleteWallet(<?php echo $w['id']; ?>, '<?php echo htmlspecialchars($w['name']); ?>')" 
                                        class="p-2 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 rounded-lg transition-colors" title="Sil">
                                    <span class="material-symbols-outlined text-xl">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($wallets)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600 mb-3 block">account_balance_wallet</span>
                            <p class="text-gray-400 font-medium">Henüz kasa/banka hesabı eklenmemiş</p>
                            <button onclick="openAddModal()" class="mt-4 text-primary font-bold text-sm">+ İlk kasanızı ekleyin</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit Modal -->
<div id="walletModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-black text-gray-900 dark:text-white">Yeni Kasa/Banka Ekle</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="walletForm" class="p-5 space-y-4">
            <input type="hidden" id="walletId" name="id">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kasa/Banka Adı *</label>
                <input type="text" id="walletName" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="Örn: Ana Kasa, Ziraat Bankası">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hesap Türü</label>
                    <select id="walletType" name="wallet_type"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="CASH">Nakit Kasa</option>
                        <option value="BANK_ACCOUNT">Banka Hesabı</option>
                        <option value="CREDIT_CARD">Kredi Kartı</option>
                        <option value="GOLD_ACCOUNT">Altın Hesabı</option>
                        <option value="LOAN">Kredi Hesabı</option>
                        <option value="SAFE">Kasa (Safe)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Para Birimi</label>
                    <select id="assetType" name="asset_type"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="TL">TL</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
            </div>
            
            <div id="balanceField">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Başlangıç Bakiyesi</label>
                <input type="number" id="walletBalance" name="balance" step="0.01" value="0"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="0.00">
            </div>
            
            <!-- Dynamic Date Fields -->
            <div class="grid grid-cols-2 gap-3 hidden" id="dateFields">
                <div id="statementDayWrapper">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hesap Kesim Günü</label>
                    <input type="number" id="statementDay" name="statement_day" min="1" max="31"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="1-31">
                </div>
                <div id="paymentDayWrapper">
                    <label id="paymentDayLabel" class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Son Ödeme Günü</label>
                    <input type="number" id="paymentDay" name="payment_day" min="1" max="31"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="1-31">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Sahip Cari</label>
                <select id="ownerEntity" name="owner_entity_id" 
                    class="select2-entity w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <option value="">Cari Seçin...</option>
                    <?php foreach($entities as $e): ?>
                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama</label>
                <textarea id="walletDescription" name="description" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all resize-none"
                    placeholder="Hesap hakkında notlar..."></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-600 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between bg-purple-600">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-white text-2xl">swap_horiz</span>
                <h3 class="text-lg font-black text-white">Hesaplar Arası Transfer</h3>
            </div>
            <button onclick="closeTransferModal()" class="p-2 hover:bg-white/20 rounded-lg text-white/80 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="transferForm" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kaynak Hesap (Para Çıkacak) *</label>
                <select name="from_wallet_id" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <option value="">Hesap Seçin...</option>
                    <?php foreach($wallets as $w): ?>
                    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance'], 2); ?> <?php echo $w['asset_type']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex justify-center py-2">
                <div class="size-10 rounded-full bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">arrow_downward</span>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hedef Hesap (Para Girecek) *</label>
                <select name="to_wallet_id" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <option value="">Hesap Seçin...</option>
                    <?php foreach($wallets as $w): ?>
                    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance'], 2); ?> <?php echo $w['asset_type']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Transfer Tutarı *</label>
                <input type="number" name="amount" step="0.01" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama</label>
                <input type="text" name="description" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="Örn: Kasadan bankaya transfer">
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTransferModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-colors">
                    Transfer Yap
                </button>
            </div>
        </form>
    </div>
</div>

<!-- External Transaction Modal -->
<div id="externalModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between bg-emerald-600">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-white text-2xl">add_card</span>
                <h3 class="text-lg font-black text-white">Harici Para Giriş/Çıkış</h3>
            </div>
            <button onclick="closeExternalModal()" class="p-2 hover:bg-white/20 rounded-lg text-white/80 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="externalForm" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">İşlem Türü *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="giris" class="peer hidden" checked>
                        <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-border-dark peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-500/10 transition-all text-center">
                            <span class="material-symbols-outlined text-3xl text-gray-400 peer-checked:text-emerald-500">arrow_downward</span>
                            <p class="text-sm font-bold text-gray-600 dark:text-gray-300 mt-1">Para Girişi</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="cikis" class="peer hidden">
                        <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-border-dark peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-500/10 transition-all text-center">
                            <span class="material-symbols-outlined text-3xl text-gray-400 peer-checked:text-red-500">arrow_upward</span>
                            <p class="text-sm font-bold text-gray-600 dark:text-gray-300 mt-1">Para Çıkışı</p>
                        </div>
                    </label>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hesap *</label>
                <select name="wallet_id" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <option value="">Hesap Seçin...</option>
                    <?php foreach($wallets as $w): ?>
                    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance'], 2); ?> <?php echo $w['asset_type']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tutar *</label>
                <input type="number" name="amount" step="0.01" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama *</label>
                <input type="text" name="description" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="Örn: Sermaye girişi, Kişisel harcama">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeExternalModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.wallet-tab {
    background: transparent;
    color: #6b7280;
    border: 1px solid transparent;
}
.wallet-tab:hover {
    background: rgba(255,255,255,0.05);
    color: #9ca3af;
}
.wallet-tab.active {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.3);
}
</style>

<script>
let isEditing = false;

// Initialize Select2
$(document).ready(function() {
    $('.select2-entity').select2({
        dropdownParent: $('#walletModal'),
        width: '100%',
        placeholder: "Cari Seçin...",
        allowClear: true,
        language: "tr"
    });

    // Dynamic fields logic
    $('#walletType').on('change', function() {
        const type = $(this).val();
        const dateFields = $('#dateFields');
        const statementWrapper = $('#statementDayWrapper');
        const paymentWrapper = $('#paymentDayWrapper');
        const paymentLabel = $('#paymentDayLabel');
        
        dateFields.addClass('hidden');
        statementWrapper.addClass('hidden');
        paymentWrapper.addClass('hidden').removeClass('col-span-2');
        
        if (type === 'CREDIT_CARD') {
            dateFields.removeClass('hidden');
            statementWrapper.removeClass('hidden');
            paymentWrapper.removeClass('hidden');
            paymentLabel.text('Son Ödeme Günü');
        } else if (type === 'LOAN') {
            dateFields.removeClass('hidden');
            paymentWrapper.removeClass('hidden').addClass('col-span-2');
            paymentLabel.text('Taksit Ödeme Günü');
        }
    });
});

// Tab filtering
document.querySelectorAll('.wallet-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.wallet-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filterWallets();
    });
}); // Missing closing bracket fixed

// Search filtering
document.getElementById('searchInput').addEventListener('input', filterWallets);

function filterWallets() {
    const activeTab = document.querySelector('.wallet-tab.active').dataset.type;
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    
    document.querySelectorAll('.wallet-row').forEach(row => {
        const type = row.dataset.type;
        const name = row.dataset.name;
        
        const matchesType = activeTab === 'all' || type === activeTab;
        const matchesSearch = name.includes(searchText);
        
        row.style.display = (matchesType && matchesSearch) ? '' : 'none';
    });
}

function openAddModal() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = 'Yeni Kasa/Banka Ekle';
    document.getElementById('walletForm').reset();
    document.getElementById('walletId').value = '';
    // Reset select2 value
    $('#ownerEntity').val(null).trigger('change');
    // Trigger type change to reset dynamic fields
    $('#walletType').trigger('change');
    document.getElementById('balanceField').style.display = 'block';
    document.getElementById('walletModal').classList.remove('hidden');
    document.getElementById('walletModal').classList.add('flex');
}

function openEditModal(wallet) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Kasa/Banka Düzenle';
    document.getElementById('walletId').value = wallet.id;
    document.getElementById('walletName').value = wallet.name;
    document.getElementById('walletType').value = wallet.wallet_type;
    document.getElementById('assetType').value = wallet.asset_type;
    document.getElementById('walletDescription').value = wallet.description || '';
    
    // Set dynamic fields
    document.getElementById('statementDay').value = wallet.statement_day || '';
    document.getElementById('paymentDay').value = wallet.payment_day || '';
    
    // Set select2 value
    $('#ownerEntity').val(wallet.owner_entity_id || '').trigger('change');
    // Trigger type change to show relevant fields
    $('#walletType').trigger('change');
    
    document.getElementById('balanceField').style.display = 'none';
    document.getElementById('walletModal').classList.remove('hidden');
    document.getElementById('walletModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('walletModal').classList.add('hidden');
    document.getElementById('walletModal').classList.remove('flex');
}

document.getElementById('walletForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = isEditing ? '<?php echo site_url('finance/api_edit_wallet'); ?>' : '<?php echo site_url('finance/api_add_wallet'); ?>';
    
    try {
        const response = await fetch(url, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast(isEditing ? 'Kasa güncellendi' : 'Kasa oluşturuldu');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || 'İşlem başarısız'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});

async function deleteWallet(id, name) {
    if (!confirm(`"${name}" kasasını silmek istediğinizden emin misiniz?`)) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo site_url('finance/api_delete_wallet'); ?>', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast('Kasa silindi');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || 'Silme başarısız'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

document.getElementById('walletModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ========== TRANSFER MODAL ==========
function openTransferModal() {
    document.getElementById('transferModal').classList.remove('hidden');
    document.getElementById('transferModal').classList.add('flex');
}

function closeTransferModal() {
    document.getElementById('transferModal').classList.add('hidden');
    document.getElementById('transferModal').classList.remove('flex');
    document.getElementById('transferForm').reset();
}

document.getElementById('transferForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Validate same wallet
    if (formData.get('from_wallet_id') === formData.get('to_wallet_id')) {
        showToast('Kaynak ve hedef hesap aynı olamaz', 'error');
        return;
    }
    
    try {
        const response = await fetch('<?php echo site_url('finance/api_wallet_transfer'); ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Transfer başarılı');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || 'Transfer başarısız'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});

document.getElementById('transferModal').addEventListener('click', function(e) {
    if (e.target === this) closeTransferModal();
});

// ========== EXTERNAL TRANSACTION MODAL ==========
function openExternalModal() {
    document.getElementById('externalModal').classList.remove('hidden');
    document.getElementById('externalModal').classList.add('flex');
}

function closeExternalModal() {
    document.getElementById('externalModal').classList.add('hidden');
    document.getElementById('externalModal').classList.remove('flex');
    document.getElementById('externalForm').reset();
}

document.getElementById('externalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo site_url('finance/api_external_transaction'); ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('İşlem kaydedildi');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Hata: ' + (result.message || 'İşlem başarısız'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});

document.getElementById('externalModal').addEventListener('click', function(e) {
    if (e.target === this) closeExternalModal();
});
</script>
