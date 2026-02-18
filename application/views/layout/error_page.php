<?php
// views/layout/error_page.php
$pageTitle = "İşlem Hatası";
include __DIR__ . '/header.php';
?>

<div class="flex flex-col items-center justify-center min-h-[60vh] px-4">
    <div class="max-w-md w-full bg-white dark:bg-card-dark rounded-[32px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 animate-in zoom-in duration-300">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-red-500 text-4xl">warning</span>
            </div>
            
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-4">Bir Hata Oluştu</h2>
            
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700 text-left mb-8">
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                    <?php echo htmlspecialchars($errorMessage ?? 'Bilinmeyen bir hata oluştu.'); ?>
                </p>
            </div>

            <div class="space-y-3">
                <button onclick="history.back()" class="w-full py-4 bg-primary hover:bg-blue-600 text-white font-black rounded-2xl shadow-lg shadow-primary/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Geri Dön ve Düzenle
                </button>
                
                <a href="<?php echo public_url('inventory'); ?>" class="w-full py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-2xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Stok Listesine Dön
                </a>
            </div>
        </div>
        
        <div class="bg-slate-50 dark:bg-slate-800/30 px-8 py-4 border-t border-slate-200 dark:border-slate-800">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center">
                Kod: <?php echo isset($errorCode) ? $errorCode : 'ERR_INVOICE_DUP'; ?>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
