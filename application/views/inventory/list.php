<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="p-6">
    <!-- Header/Toolbar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo $status == 'passive' ? 'Pasif Stoklar' : 'Stok Takibi'; ?></h2>
            <p class="text-xs font-medium text-gray-500 mt-1">Toplam <span id="totalCountText"><?php echo $total_count; ?></span> çeşit ürün kayıtlı</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <?php if($status == 'active'): ?>
                <a href="<?php echo site_url('inventory?status=passive'); ?>" class="bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-gray-200 transition-all">
                    <span class="material-symbols-outlined text-[20px]">archive</span>
                    Pasif Stoklar
                </a>
                <a href="<?php echo site_url('inventory/create'); ?>" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    Yeni Ürün
                </a>
                <button onclick="triggerAIAnalysis()" class="bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-purple-100 transition-all border border-purple-200 dark:border-purple-500/20">
                    <span class="material-symbols-outlined text-[20px]">auto_awesome</span>
                    AI Analiz
                </button>
                <input type="file" id="aiImageInput" class="hidden" accept="image/*" onchange="handleAIImageSelect(this)">
            <?php else: ?>
                <a href="<?php echo site_url('inventory'); ?>" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                    Aktif Stoklara Dön
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-card-dark p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <div class="flex-1 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">search</span>
                <input type="text" name="search" oninput="handleSearch(this)" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ürün adı, barkod veya eşleşen isimler ile ara..." class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-surface-dark border-none rounded-xl text-sm font-medium outline-none ring-1 ring-gray-100 dark:ring-gray-700 focus:ring-2 focus:ring-primary transition-all">
            </div>
            <select name="limit" onchange="this.form.submit()" class="px-4 py-3 bg-gray-50 dark:bg-surface-dark border-none rounded-xl text-sm font-bold outline-none ring-1 ring-gray-100 dark:ring-gray-700">
                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20 Kayıt</option>
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 Kayıt</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 Kayıt</option>
            </select>
        </form>
    </div>

    <!-- Inventory Table -->
    <!-- Inventory Table -->
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-surface-dark/50 text-xs font-black text-gray-400 uppercase tracking-widest">
                        <th class="px-6 py-4 text-left">Ürün Bilgisi</th>
                        <th class="px-6 py-4 text-left">Barkod</th>
                        <th class="px-6 py-4 text-center">Stok Miktarı</th>
                        <th class="px-6 py-4 text-right">Maliyet (Ort)</th>
                        <th class="px-6 py-4 text-right">Satış Fiyatı</th>
                        <th class="px-6 py-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-border-dark">
                <tbody id="productTableBody" class="divide-y divide-gray-100 dark:divide-border-dark">
                    <?php $this->load->view('inventory/product_rows'); ?>
                </tbody>
            </table>
        </div>
    </div>

<script>


var searchTimeout;
function handleSearch(input) {
    if(searchTimeout) clearTimeout(searchTimeout);
    
    // Show loading state
    const tbody = document.getElementById('productTableBody');
    tbody.style.opacity = '0.5';
    
    searchTimeout = setTimeout(() => {
        const query = input.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('search', query);
        currentUrl.searchParams.set('ajax', '1');
        // Reset page to 1 when searching
        currentUrl.searchParams.set('page', '1');
        
        fetch(currentUrl.toString())
            .then(response => response.json())
            .then(data => {
                document.getElementById('productTableBody').innerHTML = data.rows;
                document.getElementById('paginationContainer').innerHTML = data.pagination;
                document.getElementById('totalCountText').innerText = data.total_count;
                
                // Update URL without reload
                const displayUrl = new URL(window.location.href);
                displayUrl.searchParams.set('search', query);
                displayUrl.searchParams.set('page', '1');
                window.history.pushState({}, '', displayUrl.toString());
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Arama sırasında bir hata oluştu', 'error');
            })
            .finally(() => {
                tbody.style.opacity = '1';
            });
    }, 500);
}

async function restoreProduct(id) {
    const confirmed = await showConfirm({
        title: 'Ürünü Geri Yükle',
        message: 'Bu ürünü tekrar aktif etmek istiyor musunuz?',
        confirmText: 'Evet, Aktif Et',
        type: 'success' // Using success type (blue/green usually) for restoration
    });

    if (!confirmed) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo site_url("inventory/api_restore"); ?>', {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();
        
        if(res.status == 'success') {
            showToast('Ürün başarıyla aktif edildi.', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Hata: ' + res.message, 'error');
        }
    } catch (err) {
        showToast('Bir hata oluştu: ' + err, 'error');
    }
}

function triggerAIAnalysis() {
    document.getElementById('aiImageInput').click();
}

async function handleAIImageSelect(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    showToast('Görsel analiz ediliyor...', 'info');
    
    const modal = document.getElementById('aiResultModal');
    const resultDiv = document.getElementById('aiAnalysisResult');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    resultDiv.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 gap-4">
            <div class="relative">
                <div class="size-16 border-4 border-purple-200 dark:border-purple-500/20 border-t-purple-600 rounded-full animate-spin"></div>
                <span class="material-symbols-outlined absolute inset-0 flex items-center justify-center text-purple-600 animate-pulse">auto_awesome</span>
            </div>
            <p class="text-sm font-bold text-gray-500">Ürün analiz ediliyor, lütfen bekleyin...</p>
        </div>
    `;

    try {
        const reader = new FileReader();
        reader.onload = async function(e) {
            // Save to sessionStorage for use in create page
            sessionStorage.setItem('pendingAIImage', e.target.result);
            
            const base64 = e.target.result.split(',')[1];
            
            const formData = new FormData();
            formData.append('image_data', base64);
            formData.append('mime_type', file.type);

            const response = await fetch('<?php echo site_url("inventory/api_analyze_image"); ?>', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                renderAIResults(data.data);
            } else {
                resultDiv.innerHTML = `<div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm">${data.message}</div>`;
            }
        };
        reader.readAsDataURL(file);
    } catch (err) {
        showToast('Bir hata oluştu: ' + err, 'error');
        closeAIModal();
    }
    
    input.value = '';
}

function renderAIResults(data) {
    const resultDiv = document.getElementById('aiAnalysisResult');
    const pendingImage = sessionStorage.getItem('pendingAIImage');
    
    resultDiv.innerHTML = `
        <div class="space-y-4">
            ${pendingImage ? `<img src="${pendingImage}" class="w-full h-40 object-cover rounded-xl border border-gray-100 dark:border-border-dark shadow-sm">` : ''}
            <div class="p-4 bg-purple-50 dark:bg-purple-500/10 rounded-2xl">
                <div class="text-[10px] font-black text-purple-600/60 dark:text-purple-400/60 uppercase tracking-widest mb-1">Tespit Edilen Ürün</div>
                <div class="text-lg font-black text-gray-900 dark:text-white leading-tight">${data.product_name}</div>
                ${data.barcode ? `
                    <div class="mt-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-gray-400">barcode</span>
                        <span class="text-xs font-mono font-bold text-gray-500">${data.barcode}</span>
                    </div>
                ` : ''}
            </div>
            
            <div class="space-y-3">
                <button onclick="searchInventory('${data.barcode || data.product_name}')" class="w-full flex items-center justify-between p-4 bg-white dark:bg-white/5 border border-gray-100 dark:border-border-dark rounded-xl hover:border-primary transition-all group text-left">
                    <div class="flex items-center gap-3 text-left">
                        <div class="size-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined">search</span>
                        </div>
                        <div class="text-left">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">Stoklarda Ara</div>
                            <div class="text-[10px] text-gray-500">Mevcut ürünler arasında filtrele</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-gray-300 group-hover:text-primary transition-colors">chevron_right</span>
                </button>

                <a href="<?php echo site_url('inventory/create'); ?>?new_search=${encodeURIComponent(data.product_name)}&barcode=${data.barcode || ''}" class="w-full flex items-center justify-between p-4 bg-white dark:bg-white/5 border border-gray-100 dark:border-border-dark rounded-xl hover:border-green-500 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-lg bg-green-50 text-green-600 dark:bg-green-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined">add</span>
                        </div>
                        <div class="text-left">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">Yeni Kart Oluştur</div>
                            <div class="text-[10px] text-gray-500">Bu bilgilerle yeni ürün ekle</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-gray-300 group-hover:text-green-500 transition-colors">chevron_right</span>
                </a>
            </div>
        </div>
    `;
}

function searchInventory(query) {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.value = query;
        searchInput.form.submit();
    }
}

function closeAIModal() {
    document.getElementById('aiResultModal').classList.add('hidden');
    document.getElementById('aiResultModal').classList.remove('flex');
}
</script>


<!-- AI Result Modal -->
<div id="aiResultModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
                AI Ürün Tanımlama
            </h3>
            <button onclick="closeAIModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-5">
            <div id="aiAnalysisResult" class="space-y-4">
                <div class="flex flex-col items-center justify-center py-8 gap-4">
                    <div class="animate-pulse space-y-3 w-full">
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100 dark:border-border-dark flex gap-3">
            <button onclick="closeAIModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                Kapat
            </button>
        </div>
    </div>
</div>

    <!-- Pagination -->
    <div id="paginationContainer" class="mt-6 flex justify-between items-center bg-white dark:bg-card-dark p-4 rounded-2xl border border-gray-100 dark:border-border-dark">
        <?php $this->load->view('inventory/pagination'); ?>
    </div>
</div>
