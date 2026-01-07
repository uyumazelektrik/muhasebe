<?php
// Veritabanı ve yardımcı fonksiyonlar
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// İş detaylarını çek (Customer join ile)
$stmt = $pdo->prepare("
    SELECT i.*, u.full_name as personel_ad, c.access_token 
    FROM isler i 
    LEFT JOIN users u ON i.personel_id = u.id 
    LEFT JOIN customers c ON i.customer_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    redirect_with_message(public_url('jobs'), 'error', 'İş kaydı bulunamadı.');
}

// Harcanan malzemeleri çek
$stmt = $pdo->prepare("SELECT s.*, st.urun_adi, st.birim FROM is_sarfiyat s JOIN stoklar st ON s.stok_id = st.id WHERE s.is_id = ? ORDER BY s.created_at DESC");
$stmt->execute([$jobId]);
$materials = $stmt->fetchAll();

// Mevcut stokları çek (Dropdown için)
$stocks = $pdo->query("SELECT id, name as urun_adi, unit as birim, stock_quantity as miktar, satis_fiyat FROM inv_products WHERE stock_quantity > 0 ORDER BY name ASC")->fetchAll();

$pageTitle = "İş Detayı - " . $job['musteri_adi'];
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <!-- Üst Başlık -->
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <a href="<?php echo public_url('jobs'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white"><?php echo htmlspecialchars($job['musteri_adi']); ?></h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal"><?php echo htmlspecialchars($job['is_tanimi']); ?></p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="<?php echo public_url('api/update-job-status'); ?>" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                    <select name="durum" onchange="this.form.submit()" class="bg-white dark:bg-card-dark border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300">
                        <?php foreach(['Beklemede', 'Devam Ediyor', 'Tamamlandı', 'İptal'] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $job['durum'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sol Kolon: Malzeme Sarfiyat Formu ve Liste -->
        <div class="lg:col-span-2 space-y-6">
            
            <?php if (!empty($_GET['status'])): ?>
            <div class="animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $_GET['status'] === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400'; ?> border">
                    <span class="material-symbols-outlined text-[20px]"><?php echo $_GET['status'] === 'success' ? 'check_circle' : 'error'; ?></span>
                    <span class="text-sm font-bold"><?php echo htmlspecialchars($_GET['message']); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Malzeme Ekleme Formu -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_shopping_cart</span>
                    Malzeme Sarfiyatı Ekle
                </h3>
                <form action="<?php echo public_url('api/add-job-material'); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <input type="hidden" name="is_id" value="<?php echo $job['id']; ?>">
                    
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Ürün Seçiniz</label>
                        <select name="stok_id" id="stok_select" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                            <option value="">Seçiniz...</option>
                            <?php foreach($stocks as $s): ?>
                                <option value="<?php echo $s['id']; ?>" data-unit="<?php echo $s['birim']; ?>">
                                    <?php echo htmlspecialchars($s['urun_adi']); ?> (Mevcut: <?php echo number_format($s['miktar'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Kullanılan Miktar</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="miktar" required placeholder="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary pr-12">
                            <span id="unit_display" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400"></span>
                        </div>
                    </div>

                    <button type="submit" class="h-11 px-6 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Ekle
                    </button>
                </form>
            </div>

            <!-- Sarfiyat Listesi -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Kullanılan Malzemeler</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider">Malzeme</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-center">Miktar</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">Birim Fiyat</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">Tutar</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-center">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if(empty($materials)): ?>
                                <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm italic">Henüz malzeme sarfiyatı girilmemiş.</td></tr>
                            <?php else: ?>
                                <?php foreach($materials as $m): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-4 px-6">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($m['urun_adi']); ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400"><?php echo number_format($m['kullanilan_miktar'], 2); ?> <?php echo $m['birim']; ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-right font-mono text-xs text-slate-500">
                                        <?php echo number_format($m['birim_fiyat'], 2); ?> ₺
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold text-sm text-slate-900 dark:text-white">
                                        <?php echo number_format($m['kullanilan_miktar'] * $m['birim_fiyat'], 2); ?> ₺
                                    </td>
                                    <td class="py-4 px-6 text-center text-[10px] text-slate-400">
                                        <?php echo date('d.m.Y H:i', strtotime($m['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon: İş Özeti ve Finansal Ayarlar -->
        <div class="space-y-6">
            
            <!-- Toplam Kartı -->
            <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-200 dark:shadow-none flex flex-col gap-6 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 size-40 bg-white/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60">Genel Toplam (KDV Dahil)</span>
                    <?php $grandTotal = calculate_total_with_tax($job['toplam_tutar'], $job['tax_rate']); ?>
                    <h4 class="text-4xl font-black mt-1"><?php echo number_format($grandTotal, 2); ?> ₺</h4>
                    <p class="text-[10px] mt-2 opacity-80">Matrah: <?php echo number_format($job['toplam_tutar'], 2); ?> ₺ + %<?php echo number_format($job['tax_rate'], 0); ?> KDV</p>
                </div>

                <div class="h-px bg-white/10 relative z-10"></div>
                
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="flex flex-col gap-1">
                        <p class="text-[9px] uppercase font-bold opacity-60">Personel</p>
                        <p class="text-xs font-bold truncate"><?php echo htmlspecialchars($job['personel_ad'] ?? 'Atanmamış'); ?></p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-[9px] uppercase font-bold opacity-60">Fatura Durumu</p>
                        <p class="text-xs font-bold italic"><?php echo $job['invoice_status']; ?></p>
                    </div>
                </div>
            </div>

            <?php if (current_role() === 'admin'): ?>
            <!-- Finansal Ayarlar (Sadece Admin) -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Finansal Ayarlar</h4>
                <form action="<?php echo public_url('api/update-job-finance'); ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">KDV Oranı</label>
                        <select name="tax_rate" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold">
                            <option value="1" <?php echo $job['tax_rate'] == 1 ? 'selected' : ''; ?>>%1</option>
                            <option value="10" <?php echo $job['tax_rate'] == 10 ? 'selected' : ''; ?>>%10</option>
                            <option value="20" <?php echo $job['tax_rate'] == 20 ? 'selected' : ''; ?>>%20</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Fatura Durumu</label>
                        <select name="invoice_status" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold">
                            <option value="Kesilmedi" <?php echo $job['invoice_status'] === 'Kesilmedi' ? 'selected' : ''; ?>>Kesilmedi</option>
                            <option value="Kesildi" <?php echo $job['invoice_status'] === 'Kesildi' ? 'selected' : ''; ?>>Kesildi</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full h-10 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black transition-colors">Ayarları Kaydet</button>
                </form>
            </div>

            <!-- Paylaşım Linki -->
            <?php if ($job['access_token']): ?>
            <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-6">
                <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3">Müşteri Paylaşım Linki</h4>
                <div class="flex flex-col gap-3">
                    <input type="text" readonly value="<?php echo public_url('public/view_account.php?token=' . $job['access_token']); ?>" 
                           id="publicLinkInput"
                           class="w-full bg-white border-emerald-200 rounded-lg text-[10px] text-emerald-700 font-mono py-2 px-3 focus:ring-0">
                    <button onclick="copyPublicLink()" class="flex items-center justify-center gap-2 h-9 bg-emerald-500 text-white rounded-lg text-xs font-bold hover:bg-emerald-600 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                        Linki Kopyala
                    </button>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    // Birim göstergesi
    const stokSelect = document.getElementById('stok_select');
    const unitDisplay = document.getElementById('unit_display');
    
    if (stokSelect) {
        stokSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            unitDisplay.innerText = selected.dataset.unit || '';
        });
    }

    function copyPublicLink() {
        const input = document.getElementById('publicLinkInput');
        input.select();
        document.execCommand('copy');
        alert('Link kopyalandı!');
    }
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
