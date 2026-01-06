<?php
// Veritabanı ve yardımcı fonksiyonları dahil et
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

// İşleri çek
try {
    $sql = "SELECT i.*, u.full_name as personel_ad FROM isler i LEFT JOIN users u ON i.personel_id = u.id";
    $params = [];
    
    if (current_role() === 'personel') {
        $sql .= " WHERE i.personel_id = ?";
        $params[] = current_user_id();
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
}

// Personelleri çek (Yeni iş modalı için)
$personnel = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name ASC")->fetchAll();

$pageTitle = "İş Takibi";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <!-- Üst Başlık Alanı -->
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">İş Takibi</h2>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Aktif projeleri ve saha operasyonlarını yönetin.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('addJobModal').classList.remove('hidden')" class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold tracking-wide transition-all shadow-lg shadow-primary/20 shrink-0">
                    <span class="material-symbols-outlined text-[20px]">add_task</span>
                    <span>Yeni İş Aç</span>
                </button>
            </div>
        </div>
    </header>

    <!-- İçerik Alanı -->
    <main class="flex-1 p-4 sm:px-8 w-full min-w-0">
        
        <?php if (!empty($_GET['status'])): ?>
        <div class="mb-6 animate-in fade-in slide-in-from-top-4 duration-300">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $_GET['status'] === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400'; ?> border">
                <span class="material-symbols-outlined"><?php echo $_GET['status'] === 'success' ? 'check_circle' : 'error'; ?></span>
                <span class="text-sm font-bold"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- İş Kartları Grid (Mobil için ideal) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php if (empty($jobs)): ?>
                <div class="md:col-span-3 py-12 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl opacity-20">engineering</span>
                    <p class="mt-2">Henüz kayıtlı iş bulunmuyor.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col group hover:border-primary/50 transition-colors">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white line-clamp-1"><?php echo htmlspecialchars($job['musteri_adi']); ?></h3>
                                <p class="text-xs text-slate-500 truncate mt-0.5"><?php echo htmlspecialchars($job['is_tanimi']); ?></p>
                            </div>
                            <?php
                                $statusColors = [
                                    'Beklemede' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                                    'Devam Ediyor' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                                    'Tamamlandı' => 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400',
                                    'İptal' => 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                                ];
                                $statusColor = $statusColors[$job['durum']] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $statusColor; ?>">
                                <?php echo $job['durum']; ?>
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-symbols-outlined text-slate-400">person</span>
                                <span class="text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($job['personel_ad'] ?? 'Atanmamış'); ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold">
                                <span class="material-symbols-outlined text-slate-400">payments</span>
                                <span class="text-slate-900 dark:text-white"><?php echo number_format($job['toplam_tutar'], 2); ?> ₺</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 mt-auto flex justify-between items-center">
                        <span class="text-[10px] text-slate-400"><?php echo date('d.m.Y H:i', strtotime($job['created_at'])); ?></span>
                        <a href="<?php echo public_url('job-detail'); ?>?id=<?php echo $job['id']; ?>" class="flex items-center gap-1 text-xs font-bold text-primary hover:text-blue-600 transition-colors">
                            Detay ve Sarfiyat
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal: Yeni İş Aç -->
<div id="addJobModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Yeni İş Kaydı</h3>
            <button onclick="document.getElementById('addJobModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form action="<?php echo public_url('api/add-job'); ?>" method="POST" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Müşteri / Firma Adı</label>
                    <input type="text" name="musteri_adi" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">İş Tanımı</label>
                    <textarea name="is_tanimi" rows="3" required class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Sorumlu Personel</label>
                    <select name="personel_id" class="w-full bg-slate-50 dark:bg-input-dark border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary">
                        <option value="">Seçiniz...</option>
                        <?php foreach($personnel as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('addJobModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">İptal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:bg-blue-600 transition-colors shadow-lg shadow-primary/20">Kaydet ve Başlat</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
