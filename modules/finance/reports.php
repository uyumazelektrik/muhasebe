<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';

// Sadece Admin Erişebilir
require_admin();

// 1. Müşteri Bazlı Rapor
try {
    $stmt = $pdo->query("SELECT musteri_adi, 
                               COUNT(*) as is_sayisi, 
                               SUM(toplam_tutar) as toplam_tutar,
                               SUM(CASE WHEN durum = 'Tamamlandı' THEN toplam_tutar ELSE 0 END) as tamamlanan_tutar,
                               SUM(CASE WHEN durum != 'Tamamlandı' AND durum != 'İptal' THEN toplam_tutar ELSE 0 END) as bekleyen_tutar
                        FROM isler 
                        GROUP BY musteri_adi 
                        ORDER BY toplam_tutar DESC");
    $customerReports = $stmt->fetchAll();
} catch (PDOException $e) {
    $customerReports = [];
}

// 2. Stok Değeri Raporu
try {
    $stokOzeti = $pdo->query("SELECT 
        SUM(miktar * alis_fiyat) as toplam_maliyet, 
        SUM(miktar * satis_fiyat) as toplam_satis_degeri,
        COUNT(*) as urun_cesidi,
        SUM(miktar) as toplam_miktar
    FROM inv_products")->fetch();
} catch (PDOException $e) {
    $stokOzeti = ['toplam_maliyet' => 0, 'toplam_satis_degeri' => 0, 'urun_cesidi' => 0, 'toplam_miktar' => 0];
}

// 3. Genel İş Durumu
$isDurumOzeti = $pdo->query("SELECT durum, SUM(toplam_tutar) as tutar, COUNT(*) as adet FROM isler GROUP BY durum")->fetchAll();

// 4. Gider Analizi (Kategori Bazlı)
try {
    $expenseReports = $pdo->query("
        SELECT 
            c.name as kategori_adi,
            COUNT(*) as islem_sayisi,
            SUM(m.quantity * m.unit_price) as ara_toplam,
            SUM(m.tax_amount) as vergi_toplam,
            SUM(m.quantity * m.unit_price + m.tax_amount) as genel_toplam
        FROM inv_movements m
        JOIN inv_expense_categories c ON m.expense_category_id = c.id
        WHERE m.type = 'in_invoice' AND m.expense_category_id IS NOT NULL
        GROUP BY c.id
        ORDER BY genel_toplam DESC
    ")->fetchAll();
} catch (PDOException $e) {
    $expenseReports = [];
}

// 5. Aylık Gider Trendi (Son 12 Ay)
try {
    $monthlyExpenses = $pdo->query("
        SELECT 
            DATE_FORMAT(m.created_at, '%Y-%m') as ay,
            SUM(m.quantity * m.unit_price + m.tax_amount) as toplam
        FROM inv_movements m
        WHERE m.type = 'in_invoice' AND m.expense_category_id IS NOT NULL
        AND m.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY ay
        ORDER BY ay ASC
    ")->fetchAll();
    
    $chartLabels = [];
    $chartValues = [];
    foreach ($monthlyExpenses as $me) {
        $chartLabels[] = date('M Y', strtotime($me['ay'] . '-01'));
        $chartValues[] = (float)$me['toplam'];
    }
} catch (PDOException $e) {
    $chartLabels = [];
    $chartValues = [];
}

$pageTitle = "Finansal Raporlar";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1 mb-2">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Finansal Raporlar</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">İşletme karlılığı ve cari durum özeti.</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0">
        
        <!-- Üst Özet Kartları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Toplam Stok Maliyeti</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo number_format($stokOzeti['toplam_maliyet'], 2); ?> ₺</h3>
                <p class="text-[10px] text-slate-400 mt-2">Depodaki malların alış değeri</p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm border-l-4 border-l-primary">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Potansiyel Stok Satış</p>
                <h3 class="text-2xl font-black text-primary"><?php echo number_format($stokOzeti['toplam_satis_degeri'], 2); ?> ₺</h3>
                <p class="text-[10px] text-slate-400 mt-2">Güncel satış fiyatları üzerinden</p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Beklenen Brüt Kar</p>
                <h3 class="text-2xl font-black text-emerald-500"><?php echo number_format($stokOzeti['toplam_satis_degeri'] - $stokOzeti['toplam_maliyet'], 2); ?> ₺</h3>
                <p class="text-[10px] text-slate-400 mt-2">Stok satışından beklenen kar</p>
            </div>
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm bg-gradient-to-br from-primary/5 to-transparent">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Toplam İş Hacmi</p>
                <?php 
                    $totalVolume = 0;
                    foreach($isDurumOzeti as $d) if($d['durum'] != 'İptal') $totalVolume += $d['tutar'];
                ?>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo number_format($totalVolume, 2); ?> ₺</h3>
                <p class="text-[10px] text-slate-400 mt-2">İptal edilenler hariç tüm işler</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Müşteri Bazlı Rapor Tablosu -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Müşteri Bazlı Ciro Dağılımı</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider">Müşteri</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-center">İş Adeti</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">Toplam Ciro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach($customerReports as $cr): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($cr['musteri_adi']); ?></span>
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-medium text-slate-600 dark:text-slate-400">
                                    <?php echo $cr['is_sayisi']; ?>
                                </td>
                                <td class="py-4 px-6 text-right font-black text-sm text-primary">
                                    <?php echo number_format($cr['toplam_tutar'], 2); ?> ₺
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- İş Durumu ve Tahsilat Özeti -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-6">İş Durumu ve Tahsilat Özeti</h3>
                    <div class="space-y-4">
                        <?php foreach($isDurumOzeti as $do): ?>
                            <?php 
                                $color = 'bg-slate-400';
                                if($do['durum'] == 'Tamamlandı') $color = 'bg-green-500';
                                if($do['durum'] == 'Devam Ediyor') $color = 'bg-blue-500';
                                if($do['durum'] == 'Beklemede') $color = 'bg-amber-500';
                                if($do['durum'] == 'İptal') $color = 'bg-red-500';
                                
                                $percent = $totalVolume > 0 ? ($do['tutar'] / $totalVolume) * 100 : 0;
                            ?>
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-center text-xs font-bold uppercase">
                                    <span class="text-slate-500"><?php echo $do['durum']; ?> (<?php echo $do['adet']; ?>)</span>
                                    <span class="text-slate-900 dark:text-white"><?php echo number_format($do['tutar'], 2); ?> ₺</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="<?php echo $color; ?> h-full" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-emerald-500 rounded-2xl p-6 text-white shadow-xl shadow-emerald-500/20">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[28px]">account_balance_wallet</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase opacity-80">Gerçekleşen Ciro</p>
                            <?php 
                                $realized = 0;
                                foreach($isDurumOzeti as $d) if($d['durum'] == 'Tamamlandı') $realized = $d['tutar'];
                            ?>
                            <h4 class="text-3xl font-black"><?php echo number_format($realized, 2); ?> ₺</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gider Analizi Tablosu -->
        <div class="mt-8 bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Kategori Bazlı Gider Analizi</h3>
                <span class="px-3 py-1 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-[10px] font-bold rounded-full uppercase tracking-tighter">İşletme Giderleri</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider">Gider Kategorisi</th>
                            <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-center">İşlem Adeti</th>
                            <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">Net Tutar</th>
                            <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">KDV</th>
                            <th class="py-3 px-6 text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider text-right">Genel Toplam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php 
                        $totalExp = 0;
                        foreach(($expenseReports ?? []) as $er): 
                            $totalExp += $er['genel_toplam'];
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="py-4 px-6">
                                <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($er['kategori_adi']); ?></span>
                            </td>
                            <td class="py-4 px-6 text-center text-xs font-medium text-slate-600 dark:text-slate-400">
                                <?php echo $er['islem_sayisi']; ?>
                            </td>
                            <td class="py-4 px-6 text-right text-sm text-slate-600 dark:text-slate-400">
                                <?php echo number_format($er['ara_toplam'], 2); ?> ₺
                            </td>
                            <td class="py-4 px-6 text-right text-xs text-slate-500">
                                <?php echo number_format($er['vergi_toplam'], 2); ?> ₺
                            </td>
                            <td class="py-4 px-6 text-right font-black text-sm text-red-500">
                                <?php echo number_format($er['genel_toplam'], 2); ?> ₺
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($expenseReports)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic text-sm">Henüz kategorize edilmiş bir gider kaydı bulunmuyor.</td>
                        </tr>
                        <?php else: ?>
                        <tr class="bg-slate-50 dark:bg-slate-800/30">
                            <td colspan="4" class="py-4 px-6 text-right text-xs font-bold uppercase text-slate-500">Toplam İşletme Gideri:</td>
                            <td class="py-4 px-6 text-right font-black text-lg text-red-600">
                                <?php echo number_format($totalExp, 2); ?> ₺
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gider Analizi Grafiği -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Aylık Gider Trendi</h3>
                    <div class="flex items-center gap-2">
                        <span class="size-3 rounded-full bg-red-500"></span>
                        <span class="text-xs font-medium text-slate-500">Toplam Gider (₺)</span>
                    </div>
                </div>
                <div class="h-[300px] w-full">
                    <canvas id="expenseTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 flex flex-col">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-6">Gider Dağılımı</h3>
                <div class="flex-1 flex flex-col justify-center">
                    <div class="space-y-4">
                        <?php 
                        $maxExp = 0;
                        foreach($expenseReports as $er) if($er['genel_toplam'] > $maxExp) $maxExp = $er['genel_toplam'];
                        
                        foreach(array_slice($expenseReports, 0, 5) as $er): 
                            $perc = $totalExp > 0 ? ($er['genel_toplam'] / $totalExp) * 100 : 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($er['kategori_adi']); ?></span>
                                <span class="text-xs font-black text-slate-900 dark:text-white">%<?php echo number_format($perc, 1); ?></span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full rounded-full" style="width: <?php echo $perc; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">En Çok Harcama Yapılan 5 Kategori</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('expenseTrendChart').getContext('2d');
    
    // Gradient oluşturma
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
    gradient.addColorStop(1, 'rgba(239, 68, 68, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Aylık Gider',
                data: <?php echo json_encode($chartValues); ?>,
                borderColor: '#ef4444',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ef4444',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Toplam: ' + new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#94a3b8',
                        font: { size: 10, weight: '600' }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.1)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10, weight: '600' },
                        callback: function(value) {
                            if (value >= 1000) return (value / 1000) + 'k ₺';
                            return value + ' ₺';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
