<?php
// views/inventory/mapping_list.php
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Eşleştirme Yönetimi</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-sm text-gray-600 uppercase">
                        <th class="p-3 border-b">OCR Adı (Ham)</th>
                        <th class="p-3 border-b">Eşleşen Stok Kartı</th>
                        <th class="p-3 border-b">Güven Skoru</th>
                        <th class="p-3 border-b">Tarih</th>
                        <th class="p-3 border-b">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($mappings as $map): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-mono text-sm"><?php echo htmlspecialchars($map['raw_name']); ?></td>
                        <td class="p-3 font-bold text-gray-800"><?php echo htmlspecialchars($map['product_name']); ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                %<?php echo $map['confidence_score']; ?>
                            </span>
                        </td>
                        <td class="p-3 text-sm text-gray-500"><?php echo date('d.m.Y H:i', strtotime($map['created_at'])); ?></td>
                        <td class="p-3">
                            <button class="text-red-500 hover:text-red-700 text-sm">Sil / Düzenle</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
