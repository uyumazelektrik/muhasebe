<?php
// Veritabanı ve yardımcı fonksiyonları dahil et
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

// Stokları çek
try {
    $stmt = $pdo->query("SELECT * FROM stoklar ORDER BY urun_adi ASC");
    $stocks = $stmt->fetchAll();
} catch (PDOException $e) {
    $stocks = [];
}

// Başlık ayarla
$pageTitle = "Stok Listesi";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <!-- Üst Başlık Alanı -->
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Stok Yönetimi</h2>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Ürün stoklarını ve kritik seviyeleri takip edin.</p>
            </div>
            <?php if (current_role() === 'admin'): ?>
            <div class="flex gap-2">
                <button onclick="openStockModal()" class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold tracking-wide transition-all shadow-lg shadow-primary/20 shrink-0">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Yeni Ürün Ekle</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- İçerik Alanı -->
    <main class="flex-1 p-4 sm:px-8 w-full min-w-0">
        
        <?php if (!empty($_GET['status'])): ?>
        <div class="mb-6 animate-in fade-in slide-in-from-top-4 duration-300">
            <?php if ($_GET['status'] === 'success'): ?>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-sm font-bold"><?php echo htmlspecialchars($_GET['message'] ?? 'İşlem başarılı'); ?></span>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-sm font-bold"><?php echo htmlspecialchars($_GET['message'] ?? 'İşlem başarısız'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- İstatistik Özetleri -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white dark:bg-card-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                        <span class="material-symbols-outlined text-[24px]">inventory_2</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Toplam Ürün</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo count($stocks); ?></p>
                    </div>
                </div>
            </div>
            
            <?php 
            $kritikCount = 0;
            foreach($stocks as $s) if($s['miktar'] <= $s['kritik_esik']) $kritikCount++;
            ?>
            <div class="bg-white dark:bg-card-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-red-500/10 text-red-500">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Kritik Stok</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo $kritikCount; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stok Tablosu -->
        <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#1c222e]">
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Barkod</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Ürün Adı</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Birim</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Miktar</th>
                            <?php if (current_role() === 'admin'): ?>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-right">Alış Fiyat</th>
                            <?php endif; ?>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-right">Satış Fiyat</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Durum</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <?php if (empty($stocks)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-4xl opacity-20">inventory</span>
                                        <p>Henüz stok kaydı bulunmuyor.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stocks as $stock): ?>
                                <?php $isCritical = ($stock['miktar'] <= $stock['kritik_esik']); ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="py-4 px-6 text-xs text-slate-400 font-mono">
                                        <?php echo htmlspecialchars($stock['barcode'] ?? '-'); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-slate-900 dark:text-white font-bold text-sm"><?php echo htmlspecialchars($stock['urun_adi']); ?></span>
                                            <span class="text-[10px] text-slate-500">ID: #<?php echo $stock['id']; ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-medium border border-slate-200 dark:border-slate-700">
                                            <?php echo htmlspecialchars($stock['birim']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="text-sm font-black <?php echo $isCritical ? 'text-red-500' : 'text-slate-700 dark:text-slate-300'; ?>">
                                            <?php echo number_format($stock['miktar'], 2); ?>
                                        </span>
                                    </td>
                                    <?php if (current_role() === 'admin'): ?>
                                    <td class="py-4 px-6 text-right font-mono text-sm text-slate-600 dark:text-slate-400">
                                        <?php echo number_format($stock['alis_fiyat'], 2); ?> ₺
                                    </td>
                                    <?php endif; ?>
                                    <td class="py-4 px-6 text-right font-mono text-sm font-bold text-primary">
                                        <?php echo number_format($stock['satis_fiyat'], 2); ?> ₺
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($isCritical): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-[10px] font-bold uppercase tracking-wider animate-pulse">
                                                <span class="size-1.5 rounded-full bg-red-600 dark:bg-red-400"></span>
                                                Kritik
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 text-[10px] font-bold uppercase tracking-wider">
                                                <span class="size-1.5 rounded-full bg-green-600 dark:bg-green-400"></span>
                                                Normal
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if (current_role() === 'admin'): ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='openStockModal(<?php echo json_encode($stock); ?>)' class="p-2 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors" title="Düzenle">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <form method="POST" action="<?php echo public_url('api/delete-stock'); ?>" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $stock['id']; ?>">
                                                <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Sil">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">-</span>
                                        <?php endif; ?>
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

<!-- Modal: Stok Ekle/Düzenle -->
<div id="stockModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Yeni Ürün Ekle</h3>
            <button onclick="closeStockModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="stockForm" method="POST" action="<?php echo public_url('api/add-stock'); ?>" class="p-6">
            <input type="hidden" name="id" id="stockId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Barkod</label>
                    <input type="text" name="barcode" id="barcode" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ürün Adı</label>
                    <input type="text" name="urun_adi" id="urun_adi" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Birim</label>
                    <select name="birim" id="birim" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                        <option value="Adet">Adet</option>
                        <option value="Metre">Metre</option>
                        <option value="KG">KG</option>
                        <option value="Paket">Paket</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Başlangıç Miktarı</label>
                    <input type="number" step="0.01" name="miktar" id="miktar" value="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kritik Eşik</label>
                    <input type="number" step="0.01" name="kritik_esik" id="kritik_esik" value="5.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                
                <?php if (current_role() === 'admin'): ?>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Alış Fiyatı (₺)</label>
                    <input type="number" step="0.01" name="alis_fiyat" id="alis_fiyat" value="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                <?php else: ?>
                    <input type="hidden" name="alis_fiyat" id="alis_fiyat" value="0.00">
                <?php endif; ?>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Satış Fiyatı (₺)</label>
                    <input type="number" step="0.01" name="satis_fiyat" id="satis_fiyat" value="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
            </div>
            
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeStockModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">İptal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 transition-colors shadow-lg shadow-primary/20">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStockModal(data = null) {
    const modal = document.getElementById('stockModal');
    const form = document.getElementById('stockForm');
    const title = document.getElementById('modalTitle');
    const stockId = document.getElementById('stockId');
    
    if (data) {
        title.innerText = 'Ürünü Düzenle';
        form.action = '<?php echo public_url("api/edit-stock"); ?>';
        stockId.value = data.id;
        document.getElementById('urun_adi').value = data.urun_adi;
        document.getElementById('barcode').value = data.barcode || '';
        document.getElementById('birim').value = data.birim;
        document.getElementById('miktar').value = data.miktar;
        document.getElementById('kritik_esik').value = data.kritik_esik;
        document.getElementById('alis_fiyat').value = data.alis_fiyat;
        document.getElementById('satis_fiyat').value = data.satis_fiyat;
    } else {
        title.innerText = 'Yeni Ürün Ekle';
        form.action = '<?php echo public_url("api/add-stock"); ?>';
        form.reset();
        stockId.value = '';
    }
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.querySelector('.bg-white').classList.add('scale-100');
    }, 10);
}

function closeStockModal() {
    const modal = document.getElementById('stockModal');
    modal.classList.add('hidden');
}

// Esc ile kapatma
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeStockModal();
});

// Dışarı tıklayınca kapatma
window.onclick = function(event) {
    const modal = document.getElementById('stockModal');
    if (event.target == modal) closeStockModal();
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
