<?php
// Veritabanı ve yardımcı fonksiyonları dahil et
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

// Stokları çek
try {
    $stmt = $pdo->query("SELECT *, name as urun_adi, unit as birim, stock_quantity as miktar, avg_cost as alis_fiyat, critical_level as kritik_esik FROM inv_products ORDER BY name ASC");
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
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Görsel</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Barkod</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider">Ürün Adı</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Birim</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Miktar</th>
                            <?php if (current_role() === 'admin'): ?>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-right">Alış Fiyat</th>
                            <?php endif; ?>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-right">Satış Fiyat</th>
                            <th class="py-4 px-6 text-xs font-semibold text-[#9da6b9] uppercase tracking-wider text-center">Kaynak</th>
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
                                    <td class="py-4 px-6 text-center">
                                        <?php if (!empty($stock['gorsel'])): ?>
                                            <img src="<?php echo $stock['gorsel']; ?>" class="w-10 h-10 object-cover rounded-lg mx-auto border border-slate-200 dark:border-slate-800">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                                                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
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
                                        <?php 
                                        $kaynak = $stock['kaynak'] ?? 'Manuel';
                                        $kaynakColor = match($kaynak) {
                                            'AI' => 'bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                            'Fatura' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                            default => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400 border-slate-200 dark:border-slate-800'
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase border <?php echo $kaynakColor; ?>">
                                            <?php echo htmlspecialchars($kaynak); ?>
                                        </span>
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
<div id="stockModal" class="hidden fixed inset-0 bg-slate-900/60 dark:bg-black/90 z-50 flex items-end sm:items-center justify-center backdrop-blur-sm p-0 sm:p-4 transition-all duration-300">
    <div class="bg-white dark:bg-[#111827] w-full max-w-lg rounded-t-[40px] sm:rounded-[32px] shadow-2xl border-t border-x border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all max-h-[94vh] flex flex-col translate-y-0">
        <!-- Mobil Tutamaç -->
        <div class="flex justify-center pt-3 pb-1 sm:hidden">
            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
        </div>
        
        <!-- Header -->
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50">
            <div class="flex flex-col">
                <h3 id="modalTitle" class="text-xl font-extrabold text-slate-900 dark:text-white">Yeni Ürün Ekle</h3>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Stok Kayıt Sistemi</span>
            </div>
            <div class="flex items-center gap-3">
                <label for="aiFileInput" class="flex items-center gap-2 px-4 py-2 rounded-2xl bg-primary text-white text-[11px] font-black uppercase tracking-wider cursor-pointer hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/30">
                    <span class="material-symbols-outlined text-sm">magic_button</span>
                    AI TANI
                </label>
                <button onclick="closeStockModal()" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 rounded-2xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-all">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <!-- AI Loading -->
        <div id="aiLoading" class="hidden bg-primary/10 border-b border-primary/20 animate-pulse">
            <div class="px-6 py-3 flex items-center gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-sm spin">sync</span>
                </div>
                <p class="text-xs font-bold text-primary italic">Gemini AI görseli inceliyor...</p>
            </div>
        </div>
        
        <!-- Form -->
        <form id="stockForm" method="POST" action="<?php echo public_url('api/add-stock'); ?>" class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
            <input type="hidden" name="id" id="stockId">
            <input type="hidden" name="gorsel" id="stockGorsel">
            <input type="hidden" name="kaynak" id="stockKaynak" value="Manuel">
            
            <div class="space-y-6">
                <!-- Ürün Adı -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                        <span class="material-symbols-outlined text-xs">label</span> Ürün Adı
                    </label>
                    <input type="text" name="urun_adi" id="urun_adi" required 
                           class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-bold focus:border-primary transition-all px-5 dark:text-white"
                           placeholder="Marka ve Model girin...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                            <span class="material-symbols-outlined text-xs">qr_code</span> Barkod
                        </label>
                        <input type="text" name="barcode" id="barcode" 
                               class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-mono focus:border-primary transition-all px-5 dark:text-white"
                               placeholder="13 Hane">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                            <span class="material-symbols-outlined text-xs">widgets</span> Birim
                        </label>
                        <select name="birim" id="birim" class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-bold focus:border-primary transition-all px-5 dark:text-white appearance-none">
                            <option value="Adet">Adet</option>
                            <option value="Metre">Metre</option>
                            <option value="KG">KG</option>
                            <option value="Paket">Paket</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                            <span class="material-symbols-outlined text-xs">reorder</span> Miktar
                        </label>
                        <input type="number" step="0.01" name="miktar" id="miktar" value="0.00" 
                               class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-black focus:border-primary transition-all px-5 dark:text-white">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1 text-red-400">
                            <span class="material-symbols-outlined text-xs">error</span> Kritik Limit
                        </label>
                        <input type="number" step="0.01" name="kritik_esik" id="kritik_esik" value="5.00" 
                               class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-black focus:border-primary transition-all px-5 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php if (current_role() === 'admin'): ?>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                            <span class="material-symbols-outlined text-xs">shopping_basket</span> Alış Fiyatı
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="alis_fiyat" id="alis_fiyat" value="0.00" 
                                   class="w-full h-14 bg-slate-50 dark:bg-[#0b0f1a] border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-black focus:border-primary transition-all pl-5 pr-12 dark:text-white">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 font-bold text-slate-400 italic">₺</span>
                        </div>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="alis_fiyat" id="alis_fiyat" value="0.00">
                    <?php endif; ?>
                    
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-primary-light uppercase tracking-widest ml-1">
                            <span class="material-symbols-outlined text-xs">monetization_on</span> Satış Fiyatı
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="satis_fiyat" id="satis_fiyat" value="0.00" 
                                   class="w-full h-14 bg-primary/5 dark:bg-primary/10 border-2 border-primary/30 dark:border-primary/50 rounded-2xl text-sm font-black text-primary focus:border-primary transition-all pl-5 pr-12">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 font-black text-primary italic">₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Butonlar (Mobile Sticky-ish) -->
            <div class="mt-10 mb-8 space-y-3">
                <button type="submit" class="w-full h-16 rounded-2xl bg-primary text-white font-black text-base shadow-xl shadow-primary/40 flex items-center justify-center gap-3 transition-all active:scale-95">
                    <span class="material-symbols-outlined">check_circle</span>
                    STOKU KAYDET
                </button>
                <button type="button" onclick="closeStockModal()" class="w-full h-14 rounded-2xl bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold text-sm transition-all active:scale-95">
                    İptal Et
                </button>
            </div>
            
            <!-- Mobile Safe Area Padding -->
            <div class="h-6 sm:hidden"></div>
        </form>
    </div>
</div>

<input type="file" id="aiFileInput" accept="image/*" capture="environment" class="hidden">
<canvas id="aiCanvas" class="hidden"></canvas>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.spin { animation: spin 2s linear infinite; }
</style>

<script>
// AI Fotoğraf İşleme
const aiFileInput = document.getElementById('aiFileInput');
const aiLoading = document.getElementById('aiLoading');

aiFileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    aiLoading.classList.remove('hidden');

    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.getElementById('aiCanvas');
            let width = img.width;
            let height = img.height;
            const MAX_SIZE = 1024;

            if (width > height) {
                if (width > MAX_SIZE) { height *= MAX_SIZE / width; width = MAX_SIZE; }
            } else {
                if (height > MAX_SIZE) { width *= MAX_SIZE / height; height = MAX_SIZE; }
            }

            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            const compressedBase64 = canvas.toDataURL('image/jpeg', 0.7);
            
            // Gemini API'ye gönder (Mevcut endpoint'i kullanıyoruz)
            fetch('<?php echo public_url('api/gemini-search'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ image: compressedBase64 })
            })
            .then(res => res.json())
            .then(data => {
                aiLoading.classList.add('hidden');
                
                // AI'dan gelen verileri form alanlarına doldur
                if (data.status === 'success' || data.status === 'not_found') {
                    // Eğer ürün veritabanında varsa uyarı ver ama alanları doldur
                    if (data.status === 'success') {
                        alert('Bu ürün zaten stokta kayıtlı: ' + data.item.urun_adi);
                        // Düzenleme moduna geçebiliriz
                        openStockModal(data.item);
                        return;
                    }

                    // Eğer ürün yeni ise sadece alanları doldur
                    const aiData = data.ai_data || {
                        name: data.identified_as,
                        barcode: data.ai_barcode
                    };

                    document.getElementById('urun_adi').value = aiData.name || '';
                    document.getElementById('barcode').value = aiData.barcode || '';
                    
                    // Görseli ve Kaynağı set et
                    document.getElementById('stockGorsel').value = compressedBase64;
                    document.getElementById('stockKaynak').value = 'AI';
                    
                    // Bildirim
                    const notify = document.createElement('div');
                    notify.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-xl shadow-2xl z-[100] animate-bounce font-bold text-sm';
                    notify.innerText = '✨ AI: ' + aiData.name + ' tanımlandı!';
                    document.body.appendChild(notify);
                    setTimeout(() => notify.remove(), 3000);
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(err => {
                aiLoading.classList.add('hidden');
                alert('Bağlantı hatası: ' + err.message);
            });
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});

function openStockModal(data = null) {
    const modal = document.getElementById('stockModal');
    const form = document.getElementById('stockForm');
    const title = document.getElementById('modalTitle');
    const stockId = document.getElementById('stockId');
    
    // AI loading'i her açılışta gizle
    aiLoading.classList.add('hidden');

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
        document.getElementById('stockGorsel').value = data.gorsel || '';
        document.getElementById('stockKaynak').value = data.kaynak || 'Manuel';
    } else {
        title.innerText = 'Yeni Ürün Ekle';
        form.action = '<?php echo public_url("api/add-stock"); ?>';
        form.reset();
        stockId.value = '';
        document.getElementById('stockGorsel').value = '';
        document.getElementById('stockKaynak').value = 'Manuel';
    }
    
    modal.classList.remove('hidden');
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
