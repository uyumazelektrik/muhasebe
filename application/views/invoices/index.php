<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Fatura/Fiş Yönetimi</h2>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Alış ve satış faturalarını yönetin</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo site_url('invoice/upload'); ?>" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold transition-all inline-flex items-center gap-2 shadow-lg shadow-purple-600/30">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    AI Fatura Ekle
                </a>
                <a href="<?php echo site_url('invoices/create'); ?>" class="px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all inline-flex items-center gap-2 shadow-lg shadow-primary/30">
                    <span class="material-symbols-outlined">add</span>
                    Yeni Fatura
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <!-- Filters -->
        <!-- Unified Filter Bar -->
        <div class="bg-white dark:bg-card-dark p-3 rounded-2xl border border-slate-200 dark:border-slate-800 mb-6 shadow-sm">
            <div class="flex flex-col lg:flex-row items-center gap-3">
                
                <!-- Search Elements Group -->
                <div class="flex flex-1 items-center gap-2 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl p-1.5 w-full">
                    <!-- Fatura No -->
                    <div class="relative flex-none w-40">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm">tag</span>
                        <input type="text" id="filter_invoice_no" placeholder="Fatura No..." 
                               class="w-full pl-9 pr-3 py-1.5 bg-transparent border-none text-sm focus:ring-0 text-slate-900 dark:text-white font-bold">
                    </div>
                    
                    <div class="w-px h-5 bg-slate-200 dark:bg-white/10 hidden sm:block"></div>

                    <!-- Cari Arama (Custom Searchable) -->
                    <div class="relative searchable-select flex-1 z-50">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm">person_search</span>
                            <input type="text" class="search-input w-full pl-9 pr-3 py-1.5 bg-transparent border-none text-sm focus:ring-0 text-slate-900 dark:text-white font-bold" 
                                   placeholder="Cari Ara..." autocomplete="off">
                            <input type="hidden" id="entity_id" name="entity_id" class="hidden-input">
                        </div>
                        <div class="options-list absolute w-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                            <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-400 border-b border-slate-100 dark:border-white/5" data-value="">Tüm Cariler</div>
                            <?php foreach($entities as $e): ?>
                                <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                    <span><?php echo htmlspecialchars($e['name']); ?></span>
                                    <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="w-px h-5 bg-slate-200 dark:bg-white/10 hidden lg:block"></div>

                    <!-- Tür Select -->
                    <div class="flex-none w-32 hidden sm:block">
                        <select id="filter_type" class="w-full bg-transparent border-none text-sm focus:ring-0 text-slate-600 dark:text-slate-400 font-bold cursor-pointer">
                            <option value="">İşlem Türü</option>
                            <option value="purchase">Alış Faturası</option>
                            <option value="sale">Satış Faturası</option>
                        </select>
                    </div>
                </div>

                <!-- Dates & Actions Group -->
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <div class="flex items-center gap-1 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl px-3 py-1 flex-1 lg:flex-none">
                        <input type="date" id="filter_date_from" class="bg-transparent border-none text-[11px] focus:ring-0 p-0 text-slate-600 dark:text-slate-400 font-bold h-8">
                        <span class="text-slate-300">/</span>
                        <input type="date" id="filter_date_to" class="bg-transparent border-none text-[11px] focus:ring-0 p-0 text-slate-600 dark:text-slate-400 font-bold h-8">
                    </div>
                    
                    <button onclick="applyFilters()" class="size-10 flex items-center justify-center bg-primary hover:bg-blue-600 text-white rounded-xl transition-all shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </button>
                    <button onclick="clearFilters()" class="size-10 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-white/5 text-slate-500 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Count and Per Page -->
        <div class="flex justify-between items-center mb-4">
            <div id="results_count" class="text-sm text-slate-500 font-medium"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase">Sayfa Başına:</label>
                <select id="per_page" onchange="applyFilters()" class="px-3 py-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>
        </div>

        <!-- Invoice List -->
        <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Fatura No</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Cari</th>
                            <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider">Tür</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Tutar</th>

                            <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                        <!-- Table rows will be populated via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <div id="empty_state" class="hidden">
                <div class="flex flex-col items-center justify-center py-16 px-4">
                    <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-800/50 flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-5xl text-slate-400">receipt_long</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Fatura Bulunamadı</h3>
                    <p class="text-sm text-slate-500 mb-6 text-center max-w-sm">Henüz kayıtlı fatura bulunmuyor veya arama kriterlerinize uygun sonuç yok.</p>
                    <a href="<?php echo site_url('invoices/create'); ?>" class="px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span>
                        İlk Faturayı Oluştur
                    </a>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination_container" class="mt-6"></div>
    </main>
</div>

<script>
var currentPage = 1;

function loadInvoices(page = 1) {
    currentPage = page;
    const filters = {
        invoice_no: document.getElementById('filter_invoice_no').value,
        entity_id: document.getElementById('entity_id').value, // Changed from filter_entity
        type: document.getElementById('filter_type').value,
        date_from: document.getElementById('filter_date_from').value,
        date_to: document.getElementById('filter_date_to').value,
        limit: document.getElementById('per_page').value,
        page: page
    };
    
    const params = new URLSearchParams(filters);
    params.append('_t', new Date().getTime()); 
    
    fetch('<?php echo site_url('api/get-invoices'); ?>?' + params)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderInvoices(data.data);
                renderPagination(data.pagination);
                updateResultsCount(data.pagination);
            } else {
                console.error('API error:', data);
                document.getElementById('invoice_tbody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Hata: ' + (data.message || 'Bilinmeyen hata') + '</td></tr>';
            }
        })
        .catch(err => {
            document.getElementById('invoice_tbody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Bağlantı hatası: ' + err.message + '</td></tr>';
        });
}

function renderInvoices(invoices) {
    const tbody = document.getElementById('invoice_tbody');
    const emptyState = document.getElementById('empty_state');
    
    if (invoices.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    tbody.innerHTML = invoices.map(inv => {
        const amount = parseFloat(inv.amount);
        const isSale = amount >= 0;
        const typeLabel = inv.type === 'tahsilat' ? 'Tahsilat' : (isSale ? 'Satış Faturası' : 'Alış Faturası');
        
        let badgeClass = '';
        if (inv.type === 'tahsilat') badgeClass = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
        else if (isSale) badgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        else badgeClass = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';

        // Filter out explicit 'tahsilat' if model filter failed for some reason, 
        // OR show it with correct label if user wants to see it (but user asked to hide it).
        // User strictly asked: "tahsilat bilgileri görünmemeli".
        // So we can client-side filter it as a fail-safe.
        if (inv.type === 'tahsilat' || inv.type === 'odeme') return '';

        return `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">${inv.transaction_date}</td>
                <td class="px-6 py-4">
                    <a href="<?php echo site_url('invoices/detail/'); ?>${inv.id}" class="font-bold text-slate-900 dark:text-white hover:text-primary transition-colors">${inv.document_no || 'Belge #' + inv.id}</a>
                </td>
                <td class="px-6 py-4">
                    ${inv.entity_id ? `<a href="<?php echo site_url('customers/detail/'); ?>${inv.entity_id}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-primary transition-colors">${inv.entity_name}</a>` : '<span class="text-sm text-slate-500">Cari Yok</span>'}
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="px-3 py-1 text-xs font-bold rounded-full whitespace-nowrap ${badgeClass}">${typeLabel}</span>
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white whitespace-nowrap">${parseFloat(Math.abs(inv.amount)).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>

                <td class="px-6 py-4">
                    <div class="flex justify-center gap-2">
                        <a href="<?php echo site_url('invoices/detail/'); ?>${inv.id}?edit=true" class="p-2 text-slate-500 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Düzenle">
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </a>
                        <button onclick="deleteInvoice(${inv.id})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Sil">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination_container');
    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let pages = [];
    for (let i = 1; i <= pagination.total_pages; i++) {
        pages.push(i);
    }
    
    container.innerHTML = `
        <div class="flex justify-center items-center gap-2">
            <button onclick="loadInvoices(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''} 
                    class="px-4 py-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700 rounded-lg font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Önceki
            </button>
            ${pages.map(p => `
                <button onclick="loadInvoices(${p})" 
                        class="px-4 py-2 ${p === pagination.current_page ? 'bg-primary text-white' : 'bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700'} rounded-lg font-bold text-sm hover:bg-primary hover:text-white transition-colors">
                    ${p}
                </button>
            `).join('')}
            <button onclick="loadInvoices(${pagination.current_page + 1})" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''} 
                    class="px-4 py-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700 rounded-lg font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Sonraki
            </button>
        </div>
    `;
}

function updateResultsCount(pagination) {
    document.getElementById('results_count').textContent = 
        `Toplam ${pagination.total} kayıt bulundu (Sayfa ${pagination.current_page}/${pagination.total_pages})`;
}

function applyFilters() {
    loadInvoices(1);
}

function clearFilters() {
    document.getElementById('filter_invoice_no').value = '';
    document.getElementById('filter_type').value = '';
    document.getElementById('filter_date_to').value = '';
    const cariContainer = document.querySelector('.searchable-select');
    cariContainer.querySelector('.search-input').value = '';
    cariContainer.querySelector('.hidden-input').value = '';
    loadInvoices(1);
}

async function deleteInvoice(id) {
    const confirmed = await showConfirm({
        title: 'Faturayı Sil',
        message: 'Bu faturayı silmek istediğinizden emin misiniz? Tüm stok hareketleri geri alınacak ve işlem geri döndürülemez.',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });

    if (!confirmed) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const res = await fetch('<?php echo site_url('api/delete-invoice'); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            showToast('Fatura başarıyla silindi', 'success');
            loadInvoices(currentPage);
        } else {
            showToast('Hata: ' + data.message, 'error');
        }
    } catch (err) {
        showToast('İşlem başarısız: ' + err.message, 'error');
    }
}

// Custom Searchable Select Logic
function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        });

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

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
                
                // If it's the filter page, maybe we want to trigger applyFilters?
                // Let's keep it manual trigger for now or based on page requirements.
            });
        });

        document.addEventListener('click', (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        });
    });
}

// Load on page load
function initInvoicesPage() {
    if (typeof setupSearchableSelects === 'function') {
        setupSearchableSelects();
        loadInvoices(1);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInvoicesPage);
} else {
    initInvoicesPage();
}
</script>
