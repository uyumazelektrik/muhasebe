<?php
// Veritabanı ve yardımcı fonksiyonlar
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// İş detaylarını çek
$stmt = $pdo->prepare("
    SELECT i.*, c.access_token 
    FROM isler i 
    LEFT JOIN customers c ON i.customer_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    redirect_with_message(public_url('jobs'), 'error', 'İş kaydı bulunamadı.');
}

// Harcanan malzemeleri çek
$stmt = $pdo->prepare("SELECT s.*, st.name as urun_adi, st.unit as birim, st.gorsel FROM is_sarfiyat s JOIN inv_products st ON s.stok_id = st.id WHERE s.is_id = ? ORDER BY s.islem_tarihi DESC, s.created_at DESC");
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
                    <?php if ($job['durum'] === 'Tamamlandı'): ?>
                        <form action="<?php echo public_url('api/post-job-to-finance'); ?>" method="POST" onsubmit="return confirm('Bu işi cari harekete aktarmak ve iş listesinden silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20">
                                <span class="material-symbols-outlined text-sm">payments</span>
                                Cariye İşle
                            </button>
                        </form>
                    <?php endif; ?>
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
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_shopping_cart</span>
                        Malzeme Sarfiyatı Ekle
                    </h3>
                    <button type="button" onclick="openVisionModal()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-bold border border-amber-500/20 hover:bg-amber-500/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">distance</span>
                        Görsel ile Tanı
                    </button>
                </div>
                <form action="<?php echo public_url('api/add-job-material'); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <input type="hidden" name="is_id" value="<?php echo $job['id']; ?>">
                    
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Ürün Seçiniz</label>
                        <select name="stok_id" id="stok_select" required class="w-full">
                            <option value="">Seçiniz...</option>
                            <?php foreach($stocks as $s): ?>
                                <option value="<?php echo $s['id']; ?>" data-unit="<?php echo $s['birim']; ?>" data-price="<?php echo $s['satis_fiyat']; ?>">
                                    <?php echo htmlspecialchars($s['urun_adi']); ?> (Mevcut: <?php echo number_format($s['miktar'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Miktar</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="miktar" required placeholder="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary pr-12">
                            <span id="unit_display" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">Birim Fiyat</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="birim_fiyat" id="birim_fiyat_input" required placeholder="0.00" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">₺</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 px-1">İş Tarihi</label>
                        <input type="date" name="islem_tarihi" value="<?php echo date('Y-m-d'); ?>" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
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
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-center">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if(empty($materials)): ?>
                                <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm italic">Henüz malzeme sarfiyatı girilmemiş.</td></tr>
                            <?php else: ?>
                                <?php foreach($materials as $m): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="size-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0">
                                                <?php if($m['gorsel']): ?>
                                                    <img src="<?php echo $m['gorsel']; ?>" class="size-full object-cover">
                                                <?php else: ?>
                                                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-sm font-bold text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($m['urun_adi']); ?></span>
                                        </div>
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
                                    <td class="py-4 px-6 text-center text-[10px] text-slate-400 font-bold">
                                        <?php echo date('d.m.Y', strtotime($m['islem_tarihi'])); ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick='openEditMaterialModal(<?php echo json_encode($m); ?>)' class="p-1.5 text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <button onclick="deleteMaterial(<?php echo $m['id']; ?>)" class="p-1.5 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
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
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">İş Tarihi (Genel)</label>
                        <input type="date" name="job_date" value="<?php echo !empty($job['job_date']) ? $job['job_date'] : date('Y-m-d'); ?>" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">KDV Oranı</label>
                        <select name="tax_rate" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold">
                            <option value="0" <?php echo $job['tax_rate'] == 0 ? 'selected' : ''; ?>>%0</option>
                            <option value="1" <?php echo $job['tax_rate'] == 1 ? 'selected' : ''; ?>>%1</option>
                            <option value="10" <?php echo $job['tax_rate'] == 10 ? 'selected' : ''; ?>>%10</option>
                            <option value="20" <?php echo $job['tax_rate'] == 20 ? 'selected' : ''; ?>>%20</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">KDV Durumu</label>
                        <select name="tax_included" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold">
                            <option value="0" <?php echo empty($job['tax_included']) ? 'selected' : ''; ?>>KDV Hariç (+KDV)</option>
                            <option value="1" <?php echo !empty($job['tax_included']) ? 'selected' : ''; ?>>KDV Dahil (İçinde)</option>
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

<!-- Modal: Malzeme Düzenle -->
<div id="editMaterialModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Sarfiyat Düzenle</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMaterialForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="edit_sarfiyat_id">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Miktar</label>
                <input type="number" step="0.01" name="miktar" id="edit_miktar" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Birim Fiyat (Satış)</label>
                <input type="number" step="0.01" name="birim_fiyat" id="edit_fiyat" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">İş Tarihi</label>
                <input type="date" name="islem_tarihi" id="edit_tarih" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 transition-colors">Vazgeç</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 transition-colors shadow-lg shadow-primary/20">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Görsel Tanıma -->
<div id="visionModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all scale-100 text-center">
        <div class="p-8">
            <div class="size-20 bg-amber-500/10 rounded-3xl flex items-center justify-center text-amber-500 mx-auto mb-6">
                <span class="material-symbols-outlined text-[40px]">distance</span>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">Görsel Ürün Tanıma</h3>
            <p class="text-sm text-slate-500 mb-8 px-4">Malzemenin fotoğrafını çekin veya yükleyin. Yapay zeka ürünü tanıyıp listeden seçecektir.</p>
            
            <input type="file" id="visionInput" accept="image/*" class="hidden" onchange="processVisionImage(this)">
            <button onclick="document.getElementById('visionInput').click()" id="visionBtn" class="w-full h-14 bg-amber-500 text-white rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/30 active:scale-95 mb-4">
                <span class="material-symbols-outlined">photo_camera</span>
                <span>Fotoğraf Seç / Çek</span>
            </button>
            
            <button onclick="closeVisionModal()" class="text-slate-400 text-xs font-bold hover:text-slate-600">İptal</button>
            
            <!-- Loading State -->
            <div id="visionLoading" class="hidden mt-6 flex flex-col items-center">
                <div class="size-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-bold text-amber-600 mt-2 uppercase tracking-widest">Ürün Tanımlanıyor...</p>
            </div>
        </div>
    </div>
</div>

<!-- Select2 için gerekli kütüphaneler -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#stok_select').select2({
            placeholder: 'Malzeme seçin veya arayın...',
            allowClear: true,
            width: '100%'
        });

        $('#stok_select').on('change', function() {
            const selected = $(this).find(':selected');
            document.getElementById('unit_display').innerText = selected.data('unit') || '';
            document.getElementById('birim_fiyat_input').value = selected.data('price') || '0.00';
        });
    });

    function openEditMaterialModal(item) {
        document.getElementById('edit_sarfiyat_id').value = item.id;
        document.getElementById('edit_miktar').value = item.kullanilan_miktar;
        document.getElementById('edit_fiyat').value = item.birim_fiyat;
        document.getElementById('edit_tarih').value = item.islem_tarihi;
        document.getElementById('editMaterialModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editMaterialModal').classList.add('hidden');
    }

    $('#editMaterialForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('<?php echo public_url('api/edit-job-material'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') location.reload();
            else alert(res.message);
        });
    });

    function deleteMaterial(id) {
        if(!confirm('Bu malzemeyi silmek istediğinize emin misiniz? Stok iade edilecektir.')) return;
        
        fetch('<?php echo public_url('api/delete-job-material'); ?>', {
            method: 'POST',
            body: new URLSearchParams({id: id})
        })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') location.reload();
            else alert(res.message);
        });
    }

    function openVisionModal() {
        document.getElementById('visionModal').classList.remove('hidden');
    }

    function closeVisionModal() {
        document.getElementById('visionModal').classList.add('hidden');
    }

    async function processVisionImage(input) {
        if (!input.files || !input.files[0]) return;
        
        const loader = document.getElementById('visionLoading');
        const btn = document.getElementById('visionBtn');
        loader.classList.remove('hidden');
        btn.classList.add('opacity-50', 'pointer-events-none');

        const formData = new FormData();
        formData.append('image', input.files[0]);

        try {
            const res = await fetch('<?php echo public_url('api/analyze-job-material-image'); ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status === 'success' && data.match) {
                $('#stok_select').val(data.match.id).trigger('change');
                closeVisionModal();
                // Opsiyonel: Miktar kutusuna odakla
                document.querySelector('input[name="miktar"]').focus();
            } else {
                alert('Ürün bulunamadı veya eşleştirilemedi: ' + (data.product_name || 'Bilinmiyor'));
            }
        } catch (e) {
            alert('Yapay zeka analizinde hata oluştu.');
        } finally {
            loader.classList.add('hidden');
            btn.classList.remove('opacity-50', 'pointer-events-none');
            input.value = '';
        }
    }

    function copyPublicLink() {
        const input = document.getElementById('publicLinkInput');
        input.select();
        document.execCommand('copy');
        alert('Link kopyalandı!');
    }
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
