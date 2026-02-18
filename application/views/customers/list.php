<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="p-6">
    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark flex items-center gap-4">
            <div class="size-12 rounded-xl bg-green-100 dark:bg-green-500/10 flex items-center justify-center text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined text-3xl">trending_up</span>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Toplam Alacaklarımız</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo number_format($stats['total_receivables'], 2); ?> ₺</h3>
            </div>
        </div>
        <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark flex items-center gap-4">
            <div class="size-12 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400">
                <span class="material-symbols-outlined text-3xl">trending_down</span>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Toplam Borçlarımız</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo number_format($stats['total_debt'], 2); ?> ₺</h3>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white dark:bg-card-dark p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button onclick="switchTab('general')" id="btn-general" class="px-6 py-2.5 rounded-xl text-sm font-bold border-2 border-primary text-primary transition-all">Genel Cariler</button>
            <button onclick="switchTab('staff')" id="btn-staff" class="px-6 py-2.5 rounded-xl text-sm font-bold border-2 border-transparent text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">Personel Cariler</button>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto">
            
            <!-- Unified Filter Component -->
            <div class="flex items-center gap-2 bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-border-dark rounded-xl p-1 focus-within:border-gray-400 dark:focus-within:border-gray-500 transition-all flex-1 md:min-w-[400px]">
                <select id="filterBalance" onchange="filterTable()" class="bg-transparent border-none text-sm font-medium outline-none text-gray-700 dark:text-gray-300 px-3 py-1.5 cursor-pointer rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors focus:ring-0">
                    <option value="" class="bg-white dark:bg-[#1e293b]">Tümü</option>
                    <option value="debtor" class="bg-white dark:bg-[#1e293b]">Borçlular</option>
                    <option value="creditor" class="bg-white dark:bg-[#1e293b]">Alacaklılar</option>
                </select>
                <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                <span class="material-symbols-outlined text-gray-400 pl-2">search</span>
                <input type="text" id="searchInput" oninput="filterTable()" placeholder="Cari ara..." class="bg-transparent border-none text-sm font-medium outline-none w-full px-2 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0">
            </div>
            
            <button onclick="openVirmanModal()" class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-gray-200 transition-all whitespace-nowrap">
                 <span class="material-symbols-outlined text-[20px]">swap_horiz</span> Virman
            </button>
            
            <button onclick="openAddModal()" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 whitespace-nowrap">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Yeni Cari
            </button>
        </div>
    </div>

    <!-- Content Tabs -->
    <div id="tab-general">
        <div class="bg-white dark:bg-card-dark rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-surface-dark/50 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">Cari Bilgileri</th>
                        <th class="px-6 py-4 text-left">Tür</th>
                        <th class="px-6 py-4 text-left">İletişim</th>
                        <th class="px-6 py-4 text-right">Bakiye</th>
                        <th class="px-6 py-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-border-dark">
                    <?php if (empty($generalEntities)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Genel cari kaydı bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach($generalEntities as $e): ?>
                        <tr class="data-row hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group" 
                            data-name="<?php echo htmlspecialchars($e['name']); ?>"
                            data-status="<?php echo $e['balance'] > 0 ? 'debtor' : ($e['balance'] < 0 ? 'creditor' : 'neutral'); ?>">
                            <td class="px-6 py-4">
                                <a href="<?php echo site_url('customers/detail/'.$e['id']); ?>" class="block font-bold text-gray-900 dark:text-white hover:text-primary transition-colors"><?php echo htmlspecialchars($e['name']); ?></a>
                                <div class="text-[10px] text-gray-400">ID: #<?php echo $e['id']; ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    $typeLabel = 'Tedarikçi';
                                    $typeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400';
                                    
                                    if($e['type'] === 'customer') {
                                        $typeLabel = 'Müşteri';
                                        $typeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
                                    } elseif($e['type'] === 'both') {
                                        $typeLabel = 'Müşteri & Tedarikçi';
                                        $typeClass = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400';
                                    }
                                ?>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?php echo $typeClass; ?>">
                                    <?php echo $typeLabel; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php if($e['phone']): ?> <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">call</span> <?php echo $e['phone']; ?></div> <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-sm <?php echo $e['balance'] == 0 ? 'text-gray-400' : ($e['balance'] > 0 ? 'text-red-500' : 'text-green-600 dark:text-green-400'); ?>">
                                    <?php echo number_format(abs($e['balance']), 2); ?> ₺
                                    <span class="text-[10px] uppercase font-medium ml-1"><?php echo $e['balance'] == 0 ? '-' : ($e['balance'] < 0 ? 'ALACAKLI' : 'BORÇLU'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo site_url('customers/detail/'.$e['id']); ?>" class="p-2 hover:bg-blue-100 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-500 rounded-lg transition-colors" title="Detay">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <button onclick="openTransactionModal(<?php echo $e['id']; ?>, 'tahsilat', '<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>')" class="p-2 hover:bg-green-100 dark:hover:bg-green-500/10 text-gray-400 hover:text-green-500 rounded-lg transition-colors" title="Tahsilat Ekle">
                                        <span class="material-symbols-outlined text-[20px]">arrow_downward</span>
                                    </button>
                                    <button onclick="openTransactionModal(<?php echo $e['id']; ?>, 'odeme', '<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>')" class="p-2 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-lg transition-colors" title="Ödeme Yap">
                                        <span class="material-symbols-outlined text-[20px]">arrow_upward</span>
                                    </button>
                                    <button onclick='openEditModal(<?php echo json_encode($e); ?>)' class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 rounded-lg transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button onclick="deleteEntity(<?php echo $e['id']; ?>)" class="p-2 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-lg transition-colors" title="Sil">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
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

    <!-- Staff Tab -->
    <div id="tab-staff" class="hidden">
        <div class="bg-white dark:bg-card-dark rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-surface-dark/50 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">Personel İsmi</th>
                        <th class="px-6 py-4 text-left">İletişim</th>
                        <th class="px-6 py-4 text-right">Maaş/Hakediş Durumu</th>
                        <th class="px-6 py-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-border-dark">
                    <?php if (empty($staffEntities)): ?>
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Personel kaydı bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach($staffEntities as $e): ?>
                        <tr class="data-row hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            data-name="<?php echo htmlspecialchars($e['name']); ?>"
                            data-status="<?php echo $e['balance'] > 0 ? 'debtor' : ($e['balance'] < 0 ? 'creditor' : 'neutral'); ?>">
                            <td class="px-6 py-4">
                                <a href="<?php echo site_url('customers/detail/'.$e['id']); ?>" class="block font-bold text-gray-900 dark:text-white hover:text-primary transition-colors"><?php echo htmlspecialchars($e['name']); ?></a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $e['phone']; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-sm <?php echo $e['balance'] == 0 ? 'text-gray-400' : ($e['balance'] > 0 ? 'text-red-500' : 'text-green-600 dark:text-green-400'); ?>">
                                    <?php echo number_format(abs($e['balance']), 2); ?> ₺
                                    <span class="text-[10px] uppercase font-medium ml-1"><?php echo $e['balance'] == 0 ? '-' : ($e['balance'] < 0 ? 'ALACAKLI' : 'BORÇLU'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo site_url('customers/detail/'.$e['id']); ?>" class="p-2 hover:bg-blue-100 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-500 rounded-lg transition-colors" title="Detay">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <button onclick="openTransactionModal(<?php echo $e['id']; ?>, 'tahsilat', '<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>')" class="p-2 hover:bg-green-100 dark:hover:bg-green-500/10 text-gray-400 hover:text-green-500 rounded-lg transition-colors" title="Tahsilat Ekle">
                                        <span class="material-symbols-outlined text-[20px]">arrow_downward</span>
                                    </button>
                                    <button onclick="openTransactionModal(<?php echo $e['id']; ?>, 'odeme', '<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>')" class="p-2 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-lg transition-colors" title="Ödeme Yap">
                                        <span class="material-symbols-outlined text-[20px]">arrow_upward</span>
                                    </button>
                                    <button onclick='openEditModal(<?php echo json_encode($e); ?>)' class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 rounded-lg transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button onclick="deleteEntity(<?php echo $e['id']; ?>)" class="p-2 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-lg transition-colors" title="Sil">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
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

<!-- Add Entity Modal -->
<div id="addEntityModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Yeni Cari Ekle</h3>
            <button onclick="closeAddModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="addEntityForm" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Adı/Ünvanı</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Tipi</label>
                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="customer">Müşteri</option>
                        <option value="supplier">Tedarikçi</option>
                        <option value="both">Her İkisi (Müşteri & Tedarikçi)</option>
                        <option value="staff">Personel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Telefon</label>
                    <input type="text" name="phone"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">E-posta</label>
                <input type="email" name="email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vergi No / T.C.</label>
                <input type="text" name="tax_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Adres</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
            </div>
            
            <!-- Optional Opening Balance -->
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açılış Bakiyesi (Opsiyonel)</label>
                <div class="relative">
                    <input type="number" name="balance" step="0.01" value="0.00"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                     <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₺</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Borç için eksi (-), Alacak için artı (+) değer giriniz.</p>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    İptal
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
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
            <input type="hidden" name="id" value="">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Adı/Ünvanı</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Cari Tipi</label>
                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <option value="customer">Müşteri</option>
                        <option value="supplier">Tedarikçi</option>
                        <option value="both">Her İkisi (Müşteri & Tedarikçi)</option>
                        <option value="staff">Personel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Telefon</label>
                    <input type="text" name="phone"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">E-posta</label>
                <input type="email" name="email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vergi No / T.C.</label>
                <input type="text" name="tax_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Adres</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
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

<!-- Add Transaction Modal -->
<div id="addTransactionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white" id="trxModalTitle">İşlem Ekle</h3>
            <button onclick="closeTransactionModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="<?php echo site_url('customers/api_add_transaction'); ?>" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="entity_id" id="trxEntityId">
            <input type="hidden" name="type" id="trxType">
            
            <div id="trxTypeDisplay" class="p-3 rounded-xl font-bold text-center">
                <!-- JS will fill this -->
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Vade Tarihi</label>
                    <input type="date" name="due_date" value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
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
                    <?php if(isset($wallets)) foreach($wallets as $w): ?>
                        <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
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
                    <input type="text" class="search-input w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-text" 
                        placeholder="Cari aramak için yazın..." autocomplete="off">
                    <input type="hidden" name="source_entity_id" class="hidden-input" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="options-list absolute w-full mt-1 bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                    <?php if(isset($allEntities)) foreach($allEntities as $e): ?>
                        <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-border-dark last:border-0 transition-colors" data-value="<?php echo $e['id']; ?>">
                            <?php echo htmlspecialchars($e['name']); ?> 
                            <span class="text-xs text-gray-400 ml-1 block sm:inline">(<?php echo number_format($e['balance'], 2); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative searchable-select z-20">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Hedef Cari (Para Giren)</label>
                <div class="relative">
                    <input type="text" class="search-input w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-text" 
                        placeholder="Cari aramak için yazın..." autocomplete="off">
                    <input type="hidden" name="target_entity_id" class="hidden-input" required>
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="options-list absolute w-full mt-1 bg-white dark:bg-card-dark border border-gray-100 dark:border-border-dark rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                    <?php if(isset($allEntities)) foreach($allEntities as $e): ?>
                        <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-border-dark last:border-0 transition-colors" data-value="<?php echo $e['id']; ?>">
                            <?php echo htmlspecialchars($e['name']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tarih</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required
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

<script>
    function openAddModal() {
        document.getElementById('addEntityModal').classList.remove('hidden');
        document.getElementById('addEntityModal').classList.add('flex');
    }

    function closeAddModal() {
        document.getElementById('addEntityModal').classList.add('hidden');
        document.getElementById('addEntityModal').classList.remove('flex');
    }

    // AJAX Form Submission for Add Entity
    document.getElementById('addEntityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Kaydediliyor...';
        
        const formData = new FormData(this);
        
        fetch('<?php echo site_url("customers/api_create"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast(data.message || 'Bir hata oluştu', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Sunucu ile iletişim kurulurken bir hata oluştu', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });

    function openEditModal(entity) {
        const modal = document.getElementById('editEntityModal');
        modal.querySelector('input[name="id"]').value = entity.id;
        modal.querySelector('input[name="name"]').value = entity.name;
        modal.querySelector('select[name="type"]').value = entity.type;
        modal.querySelector('input[name="phone"]').value = entity.phone || '';
        modal.querySelector('input[name="email"]').value = entity.email || '';
        modal.querySelector('input[name="tax_id"]').value = entity.tax_id || '';
        modal.querySelector('textarea[name="address"]').value = entity.address || '';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editEntityModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Transaction Modal Functions
    function openTransactionModal(id, type, name) {
        const modal = document.getElementById('addTransactionModal');
        document.getElementById('trxEntityId').value = id;
        document.getElementById('trxType').value = type;
        
        const display = document.getElementById('trxTypeDisplay');
        const title = document.getElementById('trxModalTitle');
        
        // decode html entity name
        const parser = new DOMParser();
        name = parser.parseFromString(`<!doctype html><body>${name}`, 'text/html').body.textContent;

        if (type === 'tahsilat') {
            title.textContent = `${name} - Tahsilat Ekle`;
            display.textContent = 'TAHSİLAT (PARA GİRİŞİ)';
            display.className = 'p-3 rounded-xl font-black text-center bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400';
        } else {
            title.textContent = `${name} - Ödeme Yap`;
            display.textContent = 'ÖDEME (PARA ÇIKIŞI)';
            display.className = 'p-3 rounded-xl font-black text-center bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTransactionModal() {
        const modal = document.getElementById('addTransactionModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
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

    function deleteEntity(id) {
        if(!confirm('Bu cariyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) return;
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('<?php echo site_url("customers/api_delete"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                location.reload();
            } else {
                alert(res.message || 'Hata oluştu');
            }
        })
        .catch(err => {
            alert('Bir hata oluştu: ' + err);
        });
    }

    function switchTab(tab) {
        const gen = document.getElementById('tab-general');
        const stf = document.getElementById('tab-staff');
        const bgen = document.getElementById('btn-general');
        const bstf = document.getElementById('btn-staff');

        if(tab === 'general') {
            gen.classList.remove('hidden');
            stf.classList.add('hidden');
            bgen.classList.add('border-primary', 'text-primary');
            bgen.classList.remove('border-transparent', 'text-gray-500');
            bstf.classList.remove('border-primary', 'text-primary');
            bstf.classList.add('border-transparent', 'text-gray-500');
        } else {
            stf.classList.remove('hidden');
            gen.classList.add('hidden');
            bstf.classList.add('border-primary', 'text-primary');
            bstf.classList.remove('border-transparent', 'text-gray-500');
            bgen.classList.remove('border-primary', 'text-primary');
            bgen.classList.add('border-transparent', 'text-gray-500');
        }
        filterTable();
    }

    function filterTable() {
        const searchInput = document.getElementById('searchInput');
        const search = searchInput.value.toLocaleLowerCase('tr-TR').trim();
        const type = document.getElementById('filterBalance').value;
        
        // Aktif tabloyu bul
        // Genel tab açıksa tab-general görünür, değilse tab-staff görünür (class="hidden" kontrolü ile)
        // Ancak switchTab class'ı toggle ediyor.
        // Hangi tabın class listesinde 'hidden' yoksa o aktiftir.
        
        let containerId = 'tab-general';
        if (document.getElementById('tab-general').classList.contains('hidden')) {
            containerId = 'tab-staff';
        }

        const container = document.getElementById(containerId);
        const rows = container.querySelectorAll('tr.data-row');

        rows.forEach(row => {
            const name = (row.dataset.name || '').toLocaleLowerCase('tr-TR');
            const status = row.dataset.status;
            
            let show = true;
            if (search && !name.includes(search)) show = false;
            if (show && type && type !== status) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    function setupSearchableSelects() {
        document.querySelectorAll('.searchable-select').forEach(container => {
            const input = container.querySelector('.search-input');
            const hiddenInput = container.querySelector('.hidden-input');
            const list = container.querySelector('.options-list');
            const options = list.querySelectorAll('.option-item');

            // Input Focus/Click -> Show List
            input.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close others
                document.querySelectorAll('.options-list').forEach(l => {
                    if(l !== list) l.classList.add('hidden');
                });
                list.classList.remove('hidden');
            });

            // Filter
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

            // Select Option
            options.forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = opt.dataset.value;
                    // Remove extra spaces but keep content
                    const text = opt.textContent.replace(/\s+/g, ' ').trim(); 
                    
                    input.value = text;
                    hiddenInput.value = value;
                    list.classList.add('hidden');
                });
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if(!container.contains(e.target)) {
                    list.classList.add('hidden');
                }
            });
        });
    }

    // Initialize dropdowns
    function initCustomersListPage() {
        setupSearchableSelects();
        // Any other initializations...
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCustomersListPage);
    } else {
        initCustomersListPage();
    }
</script>
