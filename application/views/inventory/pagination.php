<p class="text-xs font-bold text-gray-400 uppercase">
    Sayfa <?php echo $page; ?> / <?php echo ceil($total_count / $limit); ?> (Toplam <?php echo $total_count; ?> ürün)
</p>
<div class="flex gap-2">
    <?php if($page > 1): ?>
        <a href="<?php echo site_url('inventory?page='.($page-1).'&search='.$search.'&limit='.$limit); ?>" class="px-4 py-2 bg-gray-50 dark:bg-white/5 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300">Geri</a>
    <?php endif; ?>
    <?php if($page < ceil($total_count / $limit)): ?>
        <a href="<?php echo site_url('inventory?page='.($page+1).'&search='.$search.'&limit='.$limit); ?>" class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-500/20">İleri</a>
    <?php endif; ?>
</div>
