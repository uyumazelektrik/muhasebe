<?php
$dimmed = isset($dimmed) && $dimmed;
$status_color = match($job['status']) {
    'Pending' => 'amber',
    'In Progress' => 'primary',
    'Completed' => 'emerald',
    'Cancelled' => 'rose',
    default => 'slate'
};
?>

<div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 hover:shadow-lg transition-all group relative <?php echo $dimmed ? 'opacity-60' : ''; ?>">
    <div class="flex justify-between items-start mb-3">
        <div class="flex flex-col">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">#<?php echo $job['id']; ?></span>
            <h3 class="text-base font-black text-slate-900 dark:text-white line-clamp-1"><?php echo $job['customer_name'] ?: $job['customer_name_text']; ?></h3>
        </div>
        <div class="flex items-center gap-2">
            <?php if(($job['invoice_status'] ?? '') === 'Kesildi'): ?>
                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[9px] font-black uppercase rounded-lg border border-emerald-500/20">Faturalandı</span>
            <?php endif; ?>
            <a href="<?php echo site_url('jobs/detail/'.$job['id']); ?>" class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-primary rounded-xl transition-all">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </a>
        </div>
    </div>

    <p class="text-slate-500 dark:text-slate-400 text-xs mb-4 line-clamp-2 min-h-[32px]"><?php echo $job['description']; ?></p>

    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
        <div class="flex flex-col">
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">Tarih</span>
            <span class="text-xs font-black text-slate-700 dark:text-slate-200"><?php echo date('d.m.Y', strtotime($job['job_date'])); ?></span>
        </div>
        <div class="flex flex-col text-right">
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">Tutar</span>
            <span class="text-xs font-black text-primary font-mono"><?php echo number_format($job['total_amount'], 2); ?> ₺</span>
        </div>
    </div>

    <?php if($job['status'] !== 'Completed' && $job['status'] !== 'Cancelled'): ?>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <?php if ($job['status'] === 'Pending'): ?>
                <button onclick="updateStatus(<?php echo $job['id']; ?>, 'In Progress')" class="flex items-center justify-center gap-1.5 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-xl text-[10px] font-black transition-all">
                    <span class="material-symbols-outlined text-[16px]">play_arrow</span>
                    Başlat
                </button>
            <?php elseif ($job['status'] === 'In Progress'): ?>
                <button onclick="updateStatus(<?php echo $job['id']; ?>, 'Completed')" class="flex items-center justify-center gap-1.5 py-2 bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-xl text-[10px] font-black transition-all">
                    <span class="material-symbols-outlined text-[16px]">check</span>
                    Bitir
                </button>
            <?php endif; ?>
            
            <button onclick="updateStatus(<?php echo $job['id']; ?>, 'Cancelled')" class="flex items-center justify-center gap-1.5 py-2 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl text-[10px] font-black transition-all">
                <span class="material-symbols-outlined text-[16px]">close</span>
                İptal
            </button>
        </div>
    <?php endif; ?>

    <?php if(current_role() === 'admin'): ?>
        <div class="mt-2 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="w-full flex items-center justify-center gap-1.5 py-2 bg-gray-100 text-gray-500 hover:bg-rose-500 hover:text-white rounded-xl text-[10px] font-black transition-all">
                <span class="material-symbols-outlined text-[16px]">delete</span>
                Sil
            </button>
        </div>
    <?php endif; ?>
</div>
