<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
function get_status_label($status) {
    return match($status) {
        'Pending' => 'Beklemede',
        'In Progress' => 'Devam Ediyor',
        'Completed' => 'Tamamlandı',
        'Cancelled' => 'İptal',
        default => $status
    };
}
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="<?php echo site_url('jobs'); ?>" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">İş Detayı / Malzeme Girişi</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium"><?php echo $job['customer_name'] ?: $job['customer_name_text']; ?> - <?php echo date('d.m.Y', strtotime($job['job_date'])); ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-primary/10 text-primary rounded-xl font-black text-xs uppercase tracking-widest">
                    <?php echo get_status_label($job['status']); ?>
                </span>
                <?php if($this->session->userdata('role') != 'personnel'): ?>
                <button onclick="window.print()" class="hidden sm:block p-3 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined">print</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:p-8 overflow-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            
            <!-- Left: Job Info & Material Adder -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Info Card -->
                <div class="bg-white dark:bg-card-dark rounded-[2rem] border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">info</span> İş Bilgileri
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Müşteri</p>
                            <p class="font-black text-slate-900 dark:text-white"><?php echo $job['customer_name'] ?: $job['customer_name_text']; ?></p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Açıklama</p>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300"><?php echo $job['description']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Material Adder -->
                <?php if($job['status'] != 'Completed'): ?>
                <div class="bg-white dark:bg-card-dark rounded-[2rem] border border-slate-200 dark:border-slate-800 p-6 shadow-xl sticky top-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add_shopping_cart</span> Malzeme Ekle
                        <button onclick="openSmartAdd()" class="ml-auto text-primary hover:bg-primary/10 p-2 rounded-lg transition-colors flex items-center gap-1 text-[10px] font-bold">
                            <span class="material-symbols-outlined text-sm">auto_fix_high</span>
                            Metin ile Ekle
                        </button>
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input type="text" id="materialSearch" placeholder="Malzeme adı veya barkod..." 
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-2xl focus:border-primary outline-none font-bold text-sm">
                            
                            <!-- Search Results Dropdown -->
                            <div id="searchResults" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden hidden z-50 max-h-[300px] overflow-y-auto">
                                <!-- Ajax results -->
                            </div>
                        </div>

                        <div id="selectedMaterialArea" class="hidden animate-in fade-in zoom-in duration-300">
                            <div class="p-4 bg-primary/5 border border-primary/20 rounded-2xl mb-4">
                                <p id="selName" class="text-sm font-black text-slate-900 dark:text-white mb-1"></p>
                                <input type="hidden" id="selId">
                            </div>

                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="number" id="selQty" value="1" step="any" min="0" class="w-full pl-4 pr-12 py-4 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-2xl font-bold">
                                    <span id="selUnit" class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">ADET</span>
                                </div>
                                <button onclick="addMaterial()" class="px-6 bg-primary hover:bg-primary-dark text-white rounded-2xl font-black transition-all shadow-lg shadow-primary/20">
                                    Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Material List -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Totals Bar -->
                <div class="hidden lg:flex bg-gradient-to-r from-slate-900 to-slate-800 dark:from-slate-800 dark:to-slate-900 rounded-[2rem] p-8 text-white justify-between items-center shadow-xl">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/50 mb-1">Müşteri / Cari</p>
                        <h4 class="text-2xl font-black">
                             <?php echo $job['customer_name'] ?: $job['customer_name_text']; ?>
                        </h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/50 mb-1">Toplam Kalem Sayısı</p>
                        <h4 class="text-2xl font-black"><?php echo count($materials); ?> Kalem</h4>
                    </div>
                </div>

                <!-- Materials List -->
                <div class="bg-white dark:bg-card-dark rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">inventory_2</span> Kullanılan Malzemeler
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-white/5">
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Malzeme</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Miktar</th>
                                    <?php if($job['status'] != 'Completed'): ?>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">İşlem</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php if(empty($materials)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium italic">
                                            Henüz malzeme eklenmemiş.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($materials as $m): ?>
                                        <tr class="group hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-black text-slate-900 dark:text-white"><?php echo $m['material_name']; ?></p>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-black whitespace-nowrap">
                                                    <?php echo floatval($m['quantity']); ?> 
                                                    <span class="text-[10px] text-slate-400 uppercase"><?php echo $m['unit']; ?></span>
                                                </span>
                                            </td>
                                            <?php if($job['status'] != 'Completed'): ?>
                                            <td class="px-6 py-4 text-center">
                                                <button onclick="removeMaterial(<?php echo $m['id']; ?>)" class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Actions Bar -->
                <div class="flex justify-end gap-3 pt-4">
                    <?php if($job['status'] != 'Completed'): ?>
                        <button onclick="updateStatus(<?php echo $job['id']; ?>, 'Completed')" class="px-8 py-5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[2rem] font-black shadow-xl shadow-emerald-500/20 transition-all flex items-center gap-3">
                            <span class="material-symbols-outlined">check_circle</span>
                            İşi Tamamlandı Olarak İşaretle
                        </button>
                    <?php elseif($job['invoice_status'] == 'Kesilmedi'): ?>
                        <button onclick="billJob()" class="px-8 py-5 bg-primary hover:bg-primary-dark text-white rounded-[2rem] font-black shadow-xl shadow-primary/20 transition-all flex items-center gap-3">
                            <span class="material-symbols-outlined">receipt_long</span>
                            İşi Faturalandır (Cariye İşle)
                        </button>
                    <?php else: ?>
                        <div class="px-8 py-5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-[2rem] font-black flex items-center gap-3 cursor-default">
                            <span class="material-symbols-outlined text-emerald-500">task_alt</span>
                            Faturalandırıldı
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Smart Add Modal -->
<div id="smartAddModal" class="hidden fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-lg text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">auto_fix_high</span>
                Akıllı Malzeme Ekleme
            </h3>
            <button onclick="closeSmartAdd()" class="p-2 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-slate-400">close</span>
            </button>
        </div>
        
        <div class="p-6 flex-1 overflow-y-auto min-h-[300px]" id="smartAddContent">
            <!-- Step 1: Text Input -->
            <div id="smartStep1">
                <div class="bg-blue-50 dark:bg-blue-500/10 p-4 rounded-xl mb-4">
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium leading-relaxed">
                        <strong class="block mb-1">Nasıl Kullanılır?</strong>
                        WhatsApp veya notlarınızdan kopyaladığınız listeyi buraya yapıştırın. Sistem satır satır ürünleri ve adetleri otomatik algılayacaktır.
                        <br><span class="opacity-70 text-[10px] font-mono mt-1 block">Örn: "4 adet bant armatür değişti"</span>
                    </p>
                </div>
                <textarea id="smartInputText" class="w-full h-48 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl p-4 text-sm font-medium focus:ring-2 ring-primary/50 outline-none resize-none placeholder-gray-400 dark:placeholder-gray-600" placeholder="Listeyi buraya yapıştırın..."></textarea>
            </div>

            <!-- Step 2: Results -->
            <div id="smartStep2" class="hidden space-y-3">
                <!-- Results will be injected here -->
            </div>
        </div>

        <div class="p-6 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3 bg-gray-50 dark:bg-slate-900/50 rounded-b-3xl">
            <button id="smartBackBtn" onclick="toSmartStep1()" class="hidden px-6 py-3 rounded-xl border border-gray-200 dark:border-slate-700 font-bold text-sm text-gray-500 hover:bg-white dark:hover:bg-white/5 transition-colors">Geri / Düzenle</button>
            <button id="smartActionBtn" onclick="analyzeText()" class="px-8 py-3 bg-primary text-white rounded-xl font-black text-sm hover:bg-blue-600 transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">analytics</span>
                Analiz Et
            </button>
        </div>
    </div>
</div>

<script>
var materialSearch = document.getElementById('materialSearch');
var searchResults = document.getElementById('searchResults');
var selectedArea = document.getElementById('selectedMaterialArea');
var searchTimeout;

if(materialSearch) {
    materialSearch.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        if(query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                const res = await fetch('<?php echo site_url("inventory_check/api_search_stock"); ?>?q=' + encodeURIComponent(query));
                const data = await res.json();
                
                if(data.status === 'success' && data.items.length > 0) {
                    searchResults.innerHTML = data.items.map(item => `
                        <div class="p-4 hover:bg-primary/5 cursor-pointer border-b border-slate-100 dark:border-slate-800 last:border-0 option-item-div" onclick="selectMaterial(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">${item.urun_adi}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">${item.barcode || 'Barkodsuz'}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black text-slate-400 uppercase">Seç</p>
                                    <p class="text-[10px] text-slate-400">Stok: ${item.miktar} ${item.birim}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Ürün bulunamadı.</div>';
                    searchResults.classList.remove('hidden');
                }
            } catch(err) {
                console.error(err);
            }
        }, 300);
    });
}

function selectMaterial(item) {
    document.getElementById('selId').value = item.id;
    document.getElementById('selName').innerText = item.urun_adi;
    document.getElementById('selUnit').innerText = item.birim;
    document.getElementById('selQty').value = 1;

    // Reset search
    searchResults.classList.add('hidden');
    materialSearch.value = '';
    
    // Show entry area
    selectedArea.classList.remove('hidden');
    
    // Smooth scroll to entry area if on mobile
    if(window.innerWidth < 1024) {
        selectedArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    setTimeout(() => document.getElementById('selQty').focus(), 100);
}

// Barcode Scanner Support (Enter key on search input)
if (materialSearch) {
    materialSearch.addEventListener('keydown', (e) => {
        if(e.key === 'Enter') {
            e.preventDefault();
            const results = searchResults.querySelectorAll('.option-item-div');
            if(results.length === 1) {
                // If only one result (likely barcode match), select it immediately
                results[0].click();
            }
        }
    });
}

async function addMaterial() {
    const job_id = <?php echo $job['id']; ?>;
    const product_id = document.getElementById('selId').value;
    const quantity = document.getElementById('selQty').value;
    const btn = event.currentTarget;

    if(!product_id || !quantity || quantity <= 0) {
        showToast('Lütfen geçerli miktar girin.', 'error');
        return;
    }

    // Disable button to prevent double click
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin shadow-none">sync</span>';

    const formData = new FormData();
    formData.append('job_id', job_id);
    formData.append('product_id', product_id);
    formData.append('quantity', quantity);

    try {
        const res = await fetch('<?php echo site_url("jobs/api_add_material"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            
            // Reset UI instead of reload
            selectedArea.classList.add('hidden');
            document.getElementById('selId').value = '';
            materialSearch.value = '';
            materialSearch.focus();
            
            // Refresh parts of the UI
            refreshMaterialList();
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        showToast('İşlem başarısız.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Ekle';
    }
}

// Dynamic UI Refresh Function
async function refreshMaterialList() {
    try {
        // We'll just fetch the detail page and extract the relevant sections
        const res = await fetch(window.location.href);
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update Material Table
        document.querySelector('tbody.divide-y').innerHTML = doc.querySelector('tbody.divide-y').innerHTML;
        
        // Update Totals
        document.querySelector('.bg-gradient-to-r').innerHTML = doc.querySelector('.bg-gradient-to-r').innerHTML;
        
    } catch(err) {
        console.error('Refresh Error:', err);
        // Fallback to reload if soft refresh fails
        location.reload();
    }
}

async function removeMaterial(id) {
    const confirmed = await showConfirm({
        title: 'Malzemeyi Sil',
        message: 'Bu malzemeyi iş formundan silmek istediğinize emin misiniz?',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });

    if(!confirmed) return;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('job_id', <?php echo $job['id']; ?>);

    try {
        const res = await fetch('<?php echo site_url("jobs/api_remove_material"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            refreshMaterialList(); // No page reload
        }
    } catch(err) {
        showToast('Hata oluştu.', 'error');
    }
}

async function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    try {
        const res = await fetch('<?php echo site_url("jobs/api_update_status"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch(err) {
        showToast('Hata.', 'error');
    }
}

async function billJob() {
    const confirmed = await showConfirm({
        title: 'İşi Faturalandır',
        message: 'Bu iş formundaki malzemeler cari hesaba borç olarak kaydedilecek ve stoktan düşülecek. Devam etmek istiyor musunuz?',
        confirmText: 'Evet, İşle',
        type: 'primary'
    });

    if(!confirmed) return;

    const formData = new FormData();
    formData.append('id', <?php echo $job['id']; ?>);

    try {
        const res = await fetch('<?php echo site_url("jobs/api_invoice_job"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = '<?php echo site_url("jobs"); ?>', 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        showToast('İşlem sırasında bir hata oluştu.', 'error');
    }
}

// Close search on click outside
{
    const ms = document.getElementById('materialSearch');
    const sr = document.getElementById('searchResults');
    if(ms && sr) {
        document.addEventListener('click', (e) => {
            if(!ms.contains(e.target) && !sr.contains(e.target)) {
                sr.classList.add('hidden');
            }
        });
    }
}

// Smart Add Logic
var parsedItems = [];

function openSmartAdd() {
    document.getElementById('smartAddModal').classList.remove('hidden');
    // Small delay to allow render
    setTimeout(() => document.getElementById('smartInputText').focus(), 100);
    toSmartStep1();
}

function closeSmartAdd() {
    document.getElementById('smartAddModal').classList.add('hidden');
    document.getElementById('smartInputText').value = '';
    parsedItems = [];
}

function toSmartStep1() {
    document.getElementById('smartStep1').classList.remove('hidden');
    document.getElementById('smartStep2').classList.add('hidden');
    document.getElementById('smartBackBtn').classList.add('hidden');
    
    const btn = document.getElementById('smartActionBtn');
    btn.innerHTML = '<span class="material-symbols-outlined text-lg">analytics</span> Analiz Et';
    btn.onclick = analyzeText;
    btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-500/20');
     btn.classList.add('bg-primary', 'hover:bg-blue-600', 'shadow-blue-500/20');
}

async function analyzeText() {
    const text = document.getElementById('smartInputText').value;
    if(!text.trim()) {
        showToast('Lütfen metin girin', 'warning');
        return;
    }

    const btn = document.getElementById('smartActionBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> İşleniyor...';

    try {
        const formData = new FormData();
        formData.append('text', text);
        
        const res = await fetch('<?php echo site_url("jobs/api_parse_text_items"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.status === 'success') {
            parsedItems = data.items.map(item => ({
                ...item,
                checked: item.status === 'found' && item.selected_product // Default checked if found
            }));
            
            renderSmartResults();
            
            // Switch to Step 2
            document.getElementById('smartStep1').classList.add('hidden');
            document.getElementById('smartStep2').classList.remove('hidden');
            document.getElementById('smartBackBtn').classList.remove('hidden');
            
            btn.innerHTML = '<span class="material-symbols-outlined text-lg">save</span> Seçilenleri Ekle';
            btn.onclick = saveSmartItems;
            btn.classList.remove('bg-primary', 'hover:bg-blue-600', 'shadow-blue-500/20');
            btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-500/20');
            
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        console.error(err);
        showToast('Analiz hatası', 'error');
    } finally {
        btn.disabled = false;
    }
}

function renderSmartResults() {
    const container = document.getElementById('smartStep2');
    container.innerHTML = '';
    
    if(parsedItems.length === 0 || parsedItems.every(i => i.status === 'ignored')) {
        container.innerHTML = `
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4 text-orange-500">
                    <span class="material-symbols-outlined text-3xl">priority_high</span>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white">Sonuç Bulunamadı</h4>
                <p class="text-sm text-slate-500 mt-1">Girilen metinde anlamlı ürün eşleşmesi yapılamadı.</p>
            </div>
        `;
        return;
    }

    parsedItems.forEach((item, index) => {
        if(item.status === 'ignored') return; // Skip ignored items in view

        let statusHtml = '';
        let actionHtml = '';
        let matchesCount = item.matches ? item.matches.length : 0;
        
        if(item.status === 'found') {
             // Always show dropdown to allow changing the selection
             // Remove price from display as requested
             const selectedId = item.selected_product ? item.selected_product.id : '';
             
             const options = item.matches.map(m => {
                 const isSelected = m.id == selectedId ? 'selected' : '';
                 return `<option value="${m.id}" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white" ${isSelected}>${m.name}</option>`;
             }).join('');

             // Visual styling: Green border/ring if high confidence or selected, Orange if pending
             const borderClass = item.selected_product 
                ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/5 focus:ring-emerald-500/50' 
                : 'border-orange-200 dark:border-orange-500/30 bg-white dark:bg-slate-800 focus:ring-orange-500/50';

             statusHtml = `
                 <div class="flex flex-col gap-1 w-full">
                    <select class="w-full text-xs p-3 rounded-xl border ${borderClass} text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 font-bold cursor-pointer transition-all appearance-none" onchange="selectItemMatch(${index}, this.value)">
                        <option value="" class="bg-white dark:bg-slate-800 text-slate-500">Seçim Yapın (${item.matches.length} Bulundu)</option>
                        ${options}
                    </select>
                    ${item.selected_product ? 
                        `<div class="absolute right-8 top-9 pointer-events-none text-emerald-500"><span class="material-symbols-outlined text-lg">check_circle</span></div>` : 
                        `<div class="absolute right-8 top-9 pointer-events-none text-slate-400"><span class="material-symbols-outlined text-lg">expand_more</span></div>`
                    }
                 </div>
            `;
            
            // Allow checking only if a product is selected
            actionHtml = `<input type="checkbox" ${item.checked ? 'checked' : ''} ${!item.selected_product ? 'disabled' : ''} class="w-6 h-6 rounded-lg text-emerald-500 border-gray-300 focus:ring-emerald-500 transition-all cursor-pointer ${!item.selected_product ? 'opacity-50' : ''}" onchange="toggleItem(${index}, this.checked)">`;

        } else {
             // Not found
             statusHtml = `
                <div class="flex flex-col gap-1 w-full">
                     <div class="flex items-center gap-2 text-rose-600 bg-rose-50 dark:bg-rose-500/10 px-3 py-2 rounded-lg text-xs font-bold border border-rose-100 dark:border-rose-500/20 w-full">
                        <span class="material-symbols-outlined text-sm shrink-0">search_off</span>
                        <span>Eşleşme Bulunamadı</span>
                    </div>
                </div>`;
             actionHtml = `<span class="material-symbols-outlined text-rose-300 text-2xl select-none">block</span>`;
        }

        const div = document.createElement('div');
        div.className = "flex items-start gap-4 p-4 bg-white dark:bg-white/5 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-sm transition-all hover:bg-gray-50 dark:hover:bg-white/10";
        div.innerHTML = `
            <div class="flex flex-col items-center gap-1">
                <div class="font-mono text-lg font-black text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-2 rounded-xl text-center min-w-[3.5rem] focus-within:ring-2 ring-primary/50 outline-none" contenteditable="true" onblur="updateQty(${index}, this.innerText)">
                    ${item.parsed_qty}
                </div>
                <span class="text-[10px] uppercase font-bold text-slate-400">Adet</span>
            </div>
            
            <div class="flex-1 min-w-0 pt-1">
                <p class="text-sm font-medium text-slate-400 mb-2 font-mono flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">format_quote</span>
                    ${item.original}
                </p>
                ${statusHtml}
            </div>
            
            <div class="pt-2" id="action-div-${index}">
                ${actionHtml}
            </div>
        `;
        container.appendChild(div);
    });
}

function selectItemMatch(index, productId) {
    if(!productId) {
         parsedItems[index].selected_product = null;
         parsedItems[index].checked = false;
    } else {
        const product = parsedItems[index].matches.find(m => m.id == productId);
        parsedItems[index].selected_product = product;
        parsedItems[index].checked = true;
    }
    // Re-render only the checkbox area would be better, but re-rendering full results is cleaner code-wise
    renderSmartResults();
}

function toggleItem(index, isChecked) {
    parsedItems[index].checked = isChecked;
    // Don't re-render entire list for checkbox toggle to keep focus/inputs
}

function updateQty(index, newQty) {
    // Sanitize input
    let clean = parseFloat(newQty.replace(',', '.'));
    if(isNaN(clean) || clean <= 0) clean = 1;
    parsedItems[index].parsed_qty = clean;
}

async function saveSmartItems() {
    const selected = parsedItems.filter(i => i.status === 'found' && i.selected_product && i.checked);
    
    if(selected.length === 0) {
        showToast('Eklenecek geçerli ürün seçilmedi.', 'warning');
        return;
    }
    
    const btn = document.getElementById('smartActionBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin shadow-none">sync</span> Kaydediliyor...';
    
    let successCount = 0;
    
    // We will do this sequentially to avoid race conditions on total matching or backend load, 
    // although parallel is faster. Sequential is safer for reliability.
    for(const item of selected) {
        try {
            const fd = new FormData();
            fd.append('job_id', <?php echo $job['id']; ?>);
            fd.append('product_id', item.selected_product.id);
            fd.append('quantity', item.parsed_qty);
            
            await fetch('<?php echo site_url("jobs/api_add_material"); ?>', {
                method: 'POST',
                body: fd
            });
            successCount++;
        } catch(e) { console.error('Item add failed', e); }
    }
    
    showToast(`${successCount} ürün başarıyla eklendi.`, 'success');
    closeSmartAdd();
    refreshMaterialList();
    btn.disabled = false;
}
</script>
