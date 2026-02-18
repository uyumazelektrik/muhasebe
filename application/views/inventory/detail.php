<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <a href="<?php echo site_url('inventory'); ?>" class="p-2 bg-white dark:bg-card-dark rounded-xl border border-gray-100 dark:border-border-dark text-gray-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="text-xs font-medium text-gray-500 mt-1">Ürün Detayları ve Stok Bilgisi</p>
            </div>
        </div>
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0 no-scrollbar">
            <a href="<?php echo site_url('inventory/edit/'.$product['id']); ?>" class="whitespace-nowrap bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-indigo-100 transition-all">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Düzenle
            </a>
            <button onclick="triggerAIAnalysis()" class="whitespace-nowrap bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-purple-100 transition-all">
                <span class="material-symbols-outlined text-[18px]">auto_awesome</span>
                AI Analiz
            </button>
            <input type="file" id="aiImageInput" class="hidden" accept="image/*" onchange="handleAIImageSelect(this)">
            <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="whitespace-nowrap bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-red-100 transition-all">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Sil
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Temel Bilgiler
                </h3>
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Product Image -->
                    <div class="w-full md:w-1/3 shrink-0">
                        <div class="aspect-square rounded-xl overflow-hidden bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-border-dark flex items-center justify-center group relative">
                            <?php if(!empty($product['gorsel'])): ?>
                                <img src="<?php echo base_url($product['gorsel']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-zoom-in"
                                     onclick="openImageModal(this.src, '<?php echo htmlspecialchars($product['name']); ?>')">
                                <div onclick="openImageModal('<?php echo base_url($product['gorsel']); ?>', '<?php echo htmlspecialchars($product['name']); ?>')" class="absolute inset-0 z-10 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-zoom-in">
                                    <span class="material-symbols-outlined text-white drop-shadow-md">zoom_in</span>
                                </div>
                                <div class="absolute inset-0 ring-1 ring-inset ring-black/5 dark:ring-white/5 rounded-xl pointer-events-none"></div>
                            <?php else: ?>
                                <div class="flex flex-col items-center justify-center text-gray-300 dark:text-gray-600 gap-2">
                                    <span class="material-symbols-outlined text-6xl">image_not_supported</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Görsel Yok</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <div class="col-span-1 sm:col-span-2">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ürün Adı</span>
                                <div class="text-gray-900 dark:text-white font-medium text-lg leading-snug">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </div>
                            </div>
                            
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Barkod</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono bg-gray-100 dark:bg-white/5 px-3 py-1.5 rounded-lg text-gray-600 dark:text-gray-300 font-bold">
                                        <?php echo !empty($product['barcode']) ? htmlspecialchars($product['barcode']) : '---'; ?>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Birim</span>
                                <div class="text-gray-900 dark:text-white font-medium">
                                    <?php echo htmlspecialchars($product['unit'] ?? '-'); ?>
                                </div>
                            </div>

                            <?php if(!empty($product['match_names'])): ?>
                            <div class="col-span-1 sm:col-span-2">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Eşleşen İsimler (Alias)</span>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $aliases = explode(',', $product['match_names']);
                                    foreach($aliases as $alias): 
                                        $alias = trim($alias);
                                        if(empty($alias)) continue;
                                    ?>
                                        <span class="px-3 py-1 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/5">
                                            <?php echo htmlspecialchars($alias); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-500">history</span>
                        Stok Hareketleri
                    </h3>
                </div>
                
                <?php if(empty($stock_history)): ?>
                    <div class="text-center py-8 text-gray-400 text-sm">
                        Bu ürün için henüz stok hareketi bulunmuyor.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400 font-bold border-b border-gray-100 dark:border-border-dark">
                                    <th class="text-left py-3 px-2">Tarih</th>
                                    <th class="text-left py-3 px-2">İşlem / Cari</th>
                                    <th class="text-left py-3 px-2">Evrak No</th>
                                    <th class="text-right py-3 px-2">Miktar</th>
                                    <th class="text-right py-3 px-2">Birim Fiyat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-border-dark">
                                <?php foreach($stock_history as $hist): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-2 text-gray-500">
                                        <?php echo !empty($hist['movement_date']) ? date('d.m.Y', strtotime($hist['movement_date'])) : '-'; ?>
                                    </td>
                                    <td class="py-3 px-2">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            <?php if(!empty($hist['entity_id'])): ?>
                                                <a href="<?php echo site_url('customers/detail/'.$hist['entity_id']); ?>" class="hover:text-primary">
                                                    <?php echo $hist['entity_name']; ?>
                                                </a>
                                            <?php else: ?>
                                                Perakende
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            <?php echo $hist['description'] ?? ($hist['type'] === 'purchase' ? 'Alış Faturası' : 'Satış Faturası'); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-2 font-mono text-xs text-gray-500">
                                        <?php if(!empty($hist['document_no'])): ?>
                                            <a href="<?php echo site_url('invoices/detail/'.$hist['invoice_id']); ?>" class="hover:text-primary">
                                                <?php echo $hist['document_no']; ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-2 text-right font-black <?php echo ($hist['qty_change'] ?? $hist['quantity']) >= 0 ? 'text-green-600' : 'text-red-500'; ?>">
                                        <?php echo number_format($hist['qty_change'] ?? $hist['quantity'], 2); ?>
                                    </td>
                                    <td class="py-3 px-2 text-right text-gray-500">
                                        <?php echo number_format($hist['unit_price'] ?? 0, 2); ?> ₺
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side: Stats -->
        <div class="space-y-6">
            <!-- Stock Status -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Stok Durumu</h3>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="size-16 rounded-2xl <?php echo ($product['stock_quantity'] <= 5) ? 'bg-red-50 text-red-600 dark:bg-red-500/10' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10'; ?> flex items-center justify-center">
                        <span class="material-symbols-outlined text-[32px]">inventory_2</span>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white">
                            <?php echo number_format($product['stock_quantity'], 0); ?>
                        </div>
                        <div class="text-sm font-medium text-gray-500">
                            <?php echo $product['unit'] ?? 'Adet'; ?> Mevcut
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Kritik Stok Seviyesi</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">5</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                        <?php 
                        $percentage = min(100, max(0, ($product['stock_quantity'] / 50) * 100)); // Assuming 50 is a 'full' reference for visual bar
                        $colorClass = ($product['stock_quantity'] <= 5) ? 'bg-red-500' : 'bg-emerald-500';
                        ?>
                        <div class="<?php echo $colorClass; ?> h-2 rounded-full transition-all duration-500" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Fiyatlandırma</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-orange-50/50 dark:bg-orange-500/5 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-500/10 flex items-center justify-center border border-orange-200 dark:border-orange-500/20">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-orange-600/60 dark:text-orange-400/60 uppercase">En Yüksek</span>
                                <span class="block text-sm font-bold text-gray-700 dark:text-gray-200">Alış Fiyatı</span>
                            </div>
                        </div>
                        <span class="text-lg font-black text-gray-900 dark:text-white">
                            <?php echo number_format($product['last_buy_price'] ?? 0, 2); ?> ₺
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50/50 dark:bg-blue-500/5 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-500/10 flex items-center justify-center border border-blue-200 dark:border-blue-500/20">
                                <span class="material-symbols-outlined text-[20px]">sell</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-blue-600/60 dark:text-blue-400/60 uppercase">En Yüksek</span>
                                <span class="block text-sm font-bold text-gray-700 dark:text-gray-200">Satış Fiyatı</span>
                            </div>
                        </div>
                        <span class="text-lg font-black text-gray-900 dark:text-white">
                            <?php echo number_format($product['satis_fiyat'] ?? 0, 2); ?> ₺
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-border-dark">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-400">Tahmini Kar Marjı</span>
                        <?php 
                            $buy_p = (float)($product['last_buy_price'] ?? 1);
                            $sell_p = (float)($product['satis_fiyat'] ?? 0);
                            $profit = $sell_p - $buy_p;
                            $margin = ($sell_p > 0) ? ($profit / $sell_p) * 100 : 0;
                        ?>
                        <span class="text-sm font-black <?php echo $profit >= 0 ? 'text-emerald-500' : 'text-red-500'; ?>">
                            %<?php echo number_format($margin, 1); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Price Charts -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-border-dark">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-500">show_chart</span>
                        Fiyat Analizi
                    </h3>
                    
                    <div class="flex bg-gray-100 dark:bg-white/5 p-1 rounded-xl w-fit">
                        <button onclick="loadChart('1m')" class="range-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all" data-range="1m">1A</button>
                        <button onclick="loadChart('3m')" class="range-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all" data-range="3m">3A</button>
                        <button onclick="loadChart('6m')" class="range-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all" data-range="6m">6A</button>
                        <button onclick="loadChart('1y')" class="range-btn bg-white dark:bg-white/10 shadow-sm px-3 py-1.5 text-xs font-bold rounded-lg transition-all" data-range="1y">1Y</button>
                    </div>
                </div>

                <div class="min-h-[250px] relative">
                    <canvas id="priceHistoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Result Modal -->
<div id="aiResultModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
                AI Analiz Sonucu
            </h3>
            <button onclick="closeAIModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="p-4 bg-purple-50 dark:bg-purple-500/10 rounded-xl">
                <p class="text-sm text-purple-800 dark:text-purple-300">
                    Görsel analiz edildi. Aşağıdaki bilgileri doğrulayıp ürünü güncelleyebilirsiniz.
                </p>
            </div>
            
            <form id="aiUpdateForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tespit Edilen İsim</label>
                    <input type="text" name="ai_name" id="ai_name" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Tespit Edilen Barkod</label>
                    <input type="text" name="ai_barcode" id="ai_barcode" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Açıklama</label>
                    <textarea name="ai_description" id="ai_description" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="update_image_check" checked class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700">
                    <label for="update_image_check" class="text-sm text-gray-700 dark:text-gray-300 font-medium select-none">Yüklenen görseli ürün resmi yap</label>
                </div>
            </form>
        </div>
        <div class="p-5 border-t border-gray-100 dark:border-border-dark flex gap-3">
            <button onclick="closeAIModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                İptal
            </button>
            <button onclick="applyAIUpdate()" class="flex-1 py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-colors shadow-lg shadow-purple-500/30">
                Güncelle
            </button>
        </div>
    </div>
</div>

<script src="<?php echo base_url('assets/vendor/js/chart.min.js'); ?>"></script>
<script>
var priceChart = null;

function loadChart(range = '1y') {
    // Update UI active state
    document.querySelectorAll('.range-btn').forEach(btn => {
        if (btn.getAttribute('data-range') === range) {
            btn.classList.add('bg-white', 'dark:bg-white/10', 'shadow-sm');
        } else {
            btn.classList.remove('bg-white', 'dark:bg-white/10', 'shadow-sm');
        }
    });

    fetch(`<?php echo site_url('inventory/api_price_history/'.$product['id']); ?>?range=${range}`)
        .then(res => res.json())
        .then(resp => {
            if (resp.status === 'success') {
                renderChart(resp.data);
            }
        });
}

function renderChart(data) {
    const ctx = document.getElementById('priceHistoryChart').getContext('2d');
    
    // Process data for Chart.js
    const labels = [...new Set([...data.purchases.map(p => p.date), ...data.sales.map(s => s.date)])].sort();
    
    const purchaseData = labels.map(label => {
        const found = data.purchases.find(p => p.date === label);
        return found ? found.price : null;
    });

    const salesData = labels.map(label => {
        const found = data.sales.find(s => s.date === label);
        return found ? found.price : null;
    });

    if (priceChart) priceChart.destroy();

    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.map(l => new Date(l).toLocaleDateString('tr-TR')),
            datasets: [
                {
                    label: 'Alış Fiyatı',
                    data: purchaseData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4,
                    fill: true,
                    spanGaps: true
                },
                {
                    label: 'Satış Fiyatı',
                    data: salesData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    spanGaps: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        color: (typeof tailwind !== 'undefined' && tailwind.config?.theme?.extend?.colors?.['text-secondary']) || '#9da6b9',
                        font: { weight: 'bold', size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: '#151a25',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#282e39',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString('tr-TR') + ' ₺';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#9da6b9', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9da6b9', font: { size: 10 } }
                }
            }
        }
    });
}

// Initial load
document.addEventListener('DOMContentLoaded', () => loadChart('1y'));

async function deleteProduct(id) {
    const confirmed = await showConfirm({
        title: 'Ürünü Sil',
        message: 'Bu ürünü silmek (pasife almak) istediğinizden emin misiniz? Fatura geçmişi korunacaktır.',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });

    if (!confirmed) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo site_url("inventory/api_delete"); ?>', {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();
        
        if(res.status == 'success') {
            showToast('Ürün başarıyla silindi (pasife alındı).', 'success');
            setTimeout(() => {
                window.location.href = '<?php echo site_url("inventory"); ?>';
            }, 1000);
        } else {
            showToast('Hata: ' + res.message, 'error');
        }
    } catch (err) {
        showToast('Bir hata oluştu: ' + err, 'error');
    }
}


// Expose current product data
const currentProduct = <?php echo json_encode($product); ?>;
let selectedAIFile = null;

function triggerAIAnalysis() {
    document.getElementById('aiImageInput').click();
}

function handleAIImageSelect(input) {
    if (input.files && input.files[0]) {
        selectedAIFile = input.files[0];
        
        // Show loading toast
        showToast('Görsel analiz ediliyor, lütfen bekleyin...', 'info');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64 = e.target.result.split(',')[1];
            const mimeType = selectedAIFile.type;
            
            // Call API
            const formData = new FormData();
            formData.append('image_data', base64);
            formData.append('mime_type', mimeType);
            
            fetch('<?php echo site_url("inventory/api_analyze_image"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showAIModal(data.data);
                } else {
                    showToast('Analiz hatası: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Bir hata oluştu: ' + err, 'error');
            });
        };
        reader.readAsDataURL(selectedAIFile);
    }
    // Reset inputs
    input.value = '';
}

function showAIModal(data) {
    const modal = document.getElementById('aiResultModal');
    document.getElementById('ai_name').value = data.product_name || currentProduct.name;
    document.getElementById('ai_barcode').value = data.barcode || currentProduct.barcode || '';
    document.getElementById('ai_description').value = data.description || '';
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAIModal() {
    const modal = document.getElementById('aiResultModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    selectedAIFile = null;
}

function applyAIUpdate() {
    const name = document.getElementById('ai_name').value;
    const barcode = document.getElementById('ai_barcode').value;
    const updateImage = document.getElementById('update_image_check').checked;
    
    const formData = new FormData();
    formData.append('id', currentProduct.id);
    formData.append('name', name);
    formData.append('barcode', barcode);
    
    // Existing fields required by api_update
    formData.append('match_names', currentProduct.match_names || '');
    formData.append('unit', currentProduct.unit || 'Adet');
    formData.append('buying_price', currentProduct.last_buy_price || 0);
    formData.append('satis_fiyat', currentProduct.satis_fiyat || 0);
    formData.append('tax_rate', currentProduct.tax_rate || 18);
    formData.append('critical_level', currentProduct.critical_level || 5);
    
    if (updateImage && selectedAIFile) {
        formData.append('gorsel', selectedAIFile);
    }
    
    // Show saving
    const btn = document.querySelector('#aiResultModal button[onclick="applyAIUpdate()"]');
    const originalText = btn.textContent;
    btn.textContent = 'Güncelleniyor...';
    btn.disabled = true;
    
    fetch('<?php echo site_url("inventory/api_update"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Ürün başarıyla güncellendi', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Hata: ' + data.message, 'error');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        showToast('Bağlantı hatası: ' + err, 'error');
        btn.textContent = originalText;
        btn.disabled = false;
    });
}
</script>
