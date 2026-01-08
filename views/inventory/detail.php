<?php
// views/inventory/detail.php
$pageTitle = $product['name'] . " - Analiz";
include __DIR__ . '/../../views/layout/header.php';

// Prepare chart data js
$labels = [];
$buyPrices = [];
$salePrices = [];
$stocks = [];

foreach ($chartData as $data) {
    $labels[] = date('d.m.Y', strtotime($data['movement_date']));
    if ($data['type'] === 'out_invoice') {
        $salePrices[] = floatval($data['unit_price']);
        $buyPrices[] = null; 
    } else {
        $buyPrices[] = floatval($data['unit_price']);
        $salePrices[] = floatval($product['satis_fiyat']); // Referans için güncel fiyatı tutalım
    }
    $stocks[] = $data['new_stock'];
}

// Kar Marjı Hesaplama
$profitMargin = 0;
if (floatval($product['satis_fiyat']) > 0) {
    $profitMargin = ((floatval($product['satis_fiyat']) - floatval($product['avg_cost'])) / floatval($product['satis_fiyat'])) * 100;
}
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <!-- Header -->
    <header class="w-full bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-6 px-8 shrink-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="<?php echo public_url('inventory'); ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-primary transition-all">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="text-slate-500 text-sm font-medium">Barkod: <?php echo htmlspecialchars($product['barcode'] ?: '-'); ?> | Birim: <?php echo htmlspecialchars($product['unit']); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">GÜNCEL SATIŞ FİYATI</p>
                    <p class="text-2xl font-black text-primary"><?php echo number_format($product['satis_fiyat'], 2); ?> ₺</p>
                </div>
                <div class="w-px h-10 bg-slate-200 dark:bg-slate-700 mx-2"></div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ORT. MALİYET</p>
                    <p class="text-2xl font-black text-slate-700 dark:text-slate-300"><?php echo number_format($product['avg_cost'], 2); ?> ₺</p>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 p-8 w-full">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mevcut Stok</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white"><?php echo number_format($product['stock_quantity'], 2); ?> <span class="text-xs font-bold text-slate-400"><?php echo $product['unit']; ?></span></p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Son Alış Fiyatı</p>
                <p class="text-2xl font-black text-emerald-500"><?php echo number_format($product['last_buy_price'], 2); ?> ₺</p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ortalama Kar Marjı</p>
                <p class="text-2xl font-black <?php echo $profitMargin >= 0 ? 'text-blue-500' : 'text-rose-500'; ?>">
                    %<?php echo number_format($profitMargin, 1); ?>
                </p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">KDV Oranı</p>
                <p class="text-2xl font-black text-indigo-500">%<?php echo number_format($product['tax_rate'], 0); ?></p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kritik Limit</p>
                <p class="text-2xl font-black text-rose-500"><?php echo number_format($product['critical_level'], 2); ?></p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Buy Price History Chart -->
                <div class="bg-white dark:bg-card-dark p-8 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500 material-symbols-outlined">payments</span>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">Alış Fiyat Geçmişi</h3>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="buyPriceChart"></canvas>
                    </div>
                </div>

                <!-- Sale Price History Chart -->
                <div class="bg-white dark:bg-card-dark p-8 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-xl bg-blue-500/10 text-blue-500 material-symbols-outlined">sell</span>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">Satış Fiyat Geçmişi</h3>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="salePriceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Stock Movement Chart -->
            <div class="bg-white dark:bg-card-dark p-8 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
                <div class="flex items-center gap-3 mb-8">
                    <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500 material-symbols-outlined">inventory</span>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Stok Seviyesi Trendi</h3>
                </div>
                <div class="h-[300px]">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?php echo json_encode($labels); ?>;
const buyPrices = <?php echo json_encode($buyPrices); ?>;
const salePrices = <?php echo json_encode($salePrices); ?>;
const stocks = <?php echo json_encode($stocks); ?>;

const isDark = document.documentElement.classList.contains('dark');
const textColor = isDark ? '#94a3b8' : '#64748b';
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

// Global Chart Defaults
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = textColor;

// Buy Price Chart
new Chart(document.getElementById('buyPriceChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Alış Fiyatı',
            data: buyPrices,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 4,
            pointRadius: 4,
            pointBackgroundColor: '#10b981',
            tension: 0.4,
            spanGaps: true,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: gridColor }, ticks: { callback: v => v + ' ₺' } },
            x: { grid: { display: false } }
        }
    }
});

// Sale Price Chart
new Chart(document.getElementById('salePriceChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Satış Fiyatı',
            data: salePrices,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 4,
            pointRadius: 4,
            pointBackgroundColor: '#3b82f6',
            tension: 0.4,
            spanGaps: true,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: gridColor }, ticks: { callback: v => v + ' ₺' } },
            x: { grid: { display: false } }
        }
    }
});

// Stock Level Chart
new Chart(document.getElementById('stockChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Stok Seviyesi',
            data: stocks,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            borderWidth: 4,
            pointRadius: 6,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 4,
            pointBorderColor: '#f59e0b',
            tension: 0.2,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: gridColor } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
