<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Geçersiz veya eksik erişim kodu (token).");
}

try {
    // 1. Müşteriyi bul
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE access_token = ?");
    $stmt->execute([$token]);
    $customer = $stmt->fetch();

    if (!$customer) {
        die("Bu erişim koduna ait müşteri bulunamadı.");
    }

    // 2. Müşteriye ait işleri çek
    $stmt = $pdo->prepare("
        SELECT i.*, 
               (SELECT GROUP_CONCAT(CONCAT(st.urun_adi, ' (', s.kullanilan_miktar, ' ', st.birim, ')') SEPARATOR ', ') 
                FROM is_sarfiyat s 
                JOIN stoklar st ON s.stok_id = st.id 
                WHERE s.is_id = i.id) as malzemeler
        FROM isler i 
        WHERE i.customer_id = ? 
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$customer['id']]);
    $jobs = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Sistem Hatası: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="tr" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($customer['name']); ?> - Cari Detay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <span class="material-symbols-outlined text-4xl">account_balance</span>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900"><?php echo htmlspecialchars($customer['name']); ?></h1>
                    <p class="text-sm text-slate-500 italic">Müşteri Cari Hesap Detayı</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Son Güncelleme</p>
                <p class="text-sm font-bold text-slate-700"><?php echo date('d.m.Y H:i'); ?></p>
            </div>
        </div>

        <!-- Job Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100">
                            <th class="py-5 px-6 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Tarih</th>
                            <th class="py-5 px-6 text-[11px] font-bold text-slate-400 uppercase tracking-wider">İş Tanımı & Malzemeler</th>
                            <th class="py-5 px-6 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Tutar (KDV Dahil)</th>
                            <th class="py-5 px-6 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="4" class="py-20 text-center text-slate-400">Henüz kayıtlı bir işlem bulunmuyor.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <?php $grandTotal = calculate_total_with_tax($job['toplam_tutar'], $job['tax_rate']); ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-6 px-6 text-center">
                                        <p class="text-sm font-bold text-slate-700"><?php echo date('d.m.Y', strtotime($job['created_at'])); ?></p>
                                        <p class="text-[10px] text-slate-400"><?php echo date('H:i', strtotime($job['created_at'])); ?></p>
                                    </td>
                                    <td class="py-6 px-6">
                                        <h4 class="text-sm font-bold text-slate-900 mb-1"><?php echo htmlspecialchars($job['is_tanimi']); ?></h4>
                                        <p class="text-xs text-slate-500 leading-relaxed"><?php echo htmlspecialchars($job['malzemeler'] ?? 'Malzeme kullanılmadı'); ?></p>
                                    </td>
                                    <td class="py-6 px-6 text-right">
                                        <p class="text-lg font-black text-indigo-600"><?php echo number_format($grandTotal, 2); ?> ₺</p>
                                        <p class="text-[10px] text-slate-400">Matrah: <?php echo number_format($job['toplam_tutar'], 2); ?> ₺ + %<?php echo number_format($job['tax_rate'], 0); ?> KDV</p>
                                    </td>
                                    <td class="py-6 px-6 text-center">
                                        <?php if ($job['invoice_status'] === 'Kesildi'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">
                                                Faturalandırıldı
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider">
                                                Fatura Bekliyor
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">
            Bu sayfa güvenli erişim kodu ile paylaşılmıştır. Alış fiyatları ve kar marjları gizlidir.
        </p>
    </div>
</body>
</html>
