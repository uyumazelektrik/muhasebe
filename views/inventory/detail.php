<?php
// views/inventory/detail.php
include __DIR__ . '/../../views/layout/header.php';

// Prepare chart data js
$labels = [];
$prices = [];
$stocks = [];

foreach ($chartData as $data) {
    $labels[] = date('d.m H:i', strtotime($data['created_at']));
    $prices[] = $data['unit_price'];
    $stocks[] = $data['new_stock'];
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-gray-500 text-sm mt-1">Barkod: <?php echo htmlspecialchars($product['barcode']); ?></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Güncel Maliyet</p>
                <p class="text-3xl font-bold text-primary"><?php echo number_format($product['avg_cost'], 2); ?> ₺</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-blue-600 font-bold text-sm">Stok Miktarı</p>
                <p class="text-2xl font-bold"><?php echo number_format($product['stock_quantity'], 2); ?> <?php echo $product['unit']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-green-600 font-bold text-sm">Son Alış</p>
                <p class="text-2xl font-bold"><?php echo number_format($product['last_buy_price'], 2); ?> ₺</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-red-600 font-bold text-sm">Kritik Seviye</p>
                <p class="text-2xl font-bold"><?php echo number_format($product['critical_level'], 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Price Trend -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-gray-700 mb-4">Fiyat Trendi</h3>
            <canvas id="priceChart"></canvas>
        </div>
        
        <!-- Stock Level -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-gray-700 mb-4">Stok Değişimi</h3>
            <canvas id="stockChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?php echo json_encode($labels); ?>;
const prices = <?php echo json_encode($prices); ?>;
const stocks = <?php echo json_encode($stocks); ?>;

// Price Chart
new Chart(document.getElementById('priceChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Birim Fiyat (₺)',
            data: prices,
            borderColor: 'rgb(79, 70, 229)',
            tension: 0.1
        }]
    }
});

// Stock Chart
new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Stok Seviyesi',
            data: stocks,
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 1
        }]
    }
});
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
