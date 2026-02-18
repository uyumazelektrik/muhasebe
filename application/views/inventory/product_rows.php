<?php foreach($products as $p): 
    $lowStock = $p['stock_quantity'] <= 5;
?>
<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-100 dark:border-white/5">
                <?php if(!empty($p['gorsel'])): ?>
                    <img src="<?php echo base_url($p['gorsel']); ?>" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300 cursor-zoom-in" onclick="openImageModal(this.src, '<?php echo htmlspecialchars($p['name']); ?>')">
                <?php else: ?>
                    <span class="material-symbols-outlined text-xl">inventory_2</span>
                <?php endif; ?>
            </div>
            <div>
                <a href="<?php echo site_url('inventory/detail/'.$p['id']); ?>" class="group block">
                    <div class="font-bold text-gray-900 dark:text-white group-hover:text-primary transition-colors"><?php echo htmlspecialchars($p['name']); ?></div>
                </a>
                <div class="text-[10px] text-gray-400"><?php echo $p['unit'] ?? 'Birim Yok'; ?></div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        <span class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/5 px-2 py-1 rounded">
            <?php echo $p['barcode'] ?: '---'; ?>
        </span>
    </td>
    <td class="px-6 py-4 text-center">
        <span class="px-3 py-1 rounded-full text-xs font-black <?php echo $lowStock ? 'bg-red-100 text-red-600 dark:bg-red-500/10' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10'; ?>">
            <?php echo number_format($p['stock_quantity'], 0); ?> <?php echo $p['unit']; ?>
        </span>
    </td>
    <td class="px-6 py-4 text-right font-bold text-gray-500 transition-colors group-hover:text-primary">
        <?php echo number_format($p['last_buy_price'] ?? 0, 2); ?> ₺
    </td>
    <td class="px-6 py-4 text-right font-black text-gray-900 dark:text-white">
        <?php echo number_format($p['satis_fiyat'] ?? 0, 2); ?> ₺
    </td>
    <td class="px-6 py-4 text-center">
        <div class="flex items-center justify-center gap-1">
            <?php if($status == 'active'): ?>
            <a href="<?php echo site_url('inventory/edit/'.$p['id']); ?>" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 hover:text-blue-500 rounded-lg transition-colors" title="Düzenle">
                <span class="material-symbols-outlined text-[20px]">edit</span>
            </a>
            <?php else: ?>
            <button onclick="restoreProduct(<?php echo $p['id']; ?>)" class="p-2 hover:bg-green-100 dark:hover:bg-green-500/10 text-gray-400 hover:text-green-500 rounded-lg transition-colors" title="Geri Yükle">
                <span class="material-symbols-outlined text-[20px]">restore_from_trash</span>
            </button>
            <?php endif; ?>
        </div>
    </td>
</tr>
<?php endforeach; ?>
