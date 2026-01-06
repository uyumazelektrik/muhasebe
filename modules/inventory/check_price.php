<?php
$pageTitle = "Fiyat Sorgulama";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1 mb-2">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Fiyat Sorgula</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Barkod okutun veya ürün adı arayın.</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 flex flex-col items-center justify-start pt-12">
        
        <div class="w-full max-w-2xl bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl p-8">
            <div class="relative mb-8">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                    <span class="material-symbols-outlined text-[32px]">barcode_scanner</span>
                </span>
                <input type="text" id="searchInput" autofocus 
                       class="w-full h-16 pl-14 pr-4 rounded-2xl bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-700 text-xl font-bold focus:border-primary transition-all"
                       placeholder="Barkod okutun veya isim yazın...">
            </div>

            <div id="resultArea" class="hidden">
                <div class="flex flex-col items-center text-center p-6 border-t border-slate-100 dark:border-slate-800 pt-10">
                    <h3 id="resName" class="text-2xl font-black text-slate-900 dark:text-white mb-2">---</h3>
                    <p id="resBarcode" class="text-sm text-slate-400 font-mono mb-8">---</p>
                    
                    <div class="grid grid-cols-2 gap-8 w-full">
                        <div class="p-6 rounded-2xl bg-primary/5 border border-primary/10">
                            <p class="text-[10px] font-bold text-primary uppercase tracking-widest mb-2 font-black">Satış Fiyatı</p>
                            <h4 id="resPrice" class="text-4xl font-black text-primary">0.00 ₺</h4>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kalan Stok</p>
                            <h4 id="resStock" class="text-4xl font-black text-slate-700 dark:text-slate-300">0</h4>
                        </div>
                    </div>

                    <div id="resCritical" class="hidden mt-6 px-4 py-2 bg-red-500/10 border border-red-500/20 rounded-xl text-red-500 text-xs font-bold uppercase tracking-widest">
                        KRİTİK STOK SEVİYESİNDE!
                    </div>
                </div>
            </div>

            <div id="emptyArea" class="flex flex-col items-center py-12 text-slate-300">
                <span class="material-symbols-outlined text-[64px] opacity-20">search</span>
                <p class="mt-4 font-bold text-sm">Arama yapmak için yazmaya başlayın.</p>
            </div>
        </div>

    </main>
</div>

<script>
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const resultArea = document.getElementById('resultArea');
const emptyArea = document.getElementById('emptyArea');

searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        resultArea.classList.add('hidden');
        emptyArea.classList.remove('hidden');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`<?php echo public_url('api/search-stock'); ?>?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.item) {
                    const item = data.item;
                    document.getElementById('resName').innerText = item.urun_adi;
                    document.getElementById('resBarcode').innerText = item.barcode || 'Barkod Yok';
                    document.getElementById('resPrice').innerText = parseFloat(item.satis_fiyat).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
                    document.getElementById('resStock').innerText = parseFloat(item.miktar) + ' ' + item.birim;
                    
                    if (parseFloat(item.miktar) <= parseFloat(item.kritik_esik)) {
                        document.getElementById('resCritical').classList.remove('hidden');
                    } else {
                        document.getElementById('resCritical').classList.add('hidden');
                    }

                    resultArea.classList.remove('hidden');
                    emptyArea.classList.add('hidden');
                } else {
                    resultArea.classList.add('hidden');
                    emptyArea.classList.remove('hidden');
                }
            });
    }, 300);
});
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
