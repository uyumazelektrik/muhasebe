<?php include __DIR__ . '/layout/header.php'; ?>

<!-- Top Header -->
<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Genel Bakış</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Sistem özetini ve personel takibini buradan yönetin.</p>
        </div>
        
        <?php if (current_role() === 'personel'): ?>
        <!-- Personel İçin Hızlı Saat İşlemi -->
        <div class="flex items-center gap-4 bg-white dark:bg-card-dark p-2 px-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:border-primary/30">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Bugünkü Durum</span>
                <span class="text-xs font-black <?php echo $todayLog ? 'text-green-500' : 'text-amber-500'; ?>">
                    <?php 
                        if (!$todayLog) echo "Henüz Giriş Yapılmadı";
                        else {
                            $statusMap = [
                                'present' => 'İş Başında',
                                'late' => 'Gecikmeli Giriş',
                                'holiday' => 'Resmi Tatil',
                                'sick_leave' => 'Raporlu',
                                'annual_leave' => 'Yıllık İzinde',
                                'paid_leave' => 'Ücretli İzinde',
                                'excused' => 'Mazeretli'
                            ];
                            echo $statusMap[$todayLog['status']] ?? 'Kayıtlı';
                        }
                    ?>
                </span>
            </div>
            
            <div class="h-8 w-px bg-slate-100 dark:bg-slate-800"></div>

            <?php if (!$todayLog || ($todayLog['clock_in'] && !$todayLog['clock_out'] && $todayLog['clock_out'] == '00:00:00')): ?>
            <form action="<?php echo public_url('api/clock-in'); ?>" method="POST">
                <input type="hidden" name="user_id" value="<?php echo current_user_id(); ?>">
                <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                <?php if (!$todayLog): ?>
                    <input type="hidden" name="status" value="present">
                    <button type="submit" class="h-10 px-6 rounded-xl bg-primary text-white text-xs font-bold hover:bg-blue-600 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        İş Başı Yap
                    </button>
                <?php else: ?>
                    <button type="submit" class="h-10 px-6 rounded-xl bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        Mesaiyi Bitir
                    </button>
                <?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</header>

<div class="flex-1 p-4 sm:p-6 lg:p-8 w-full min-w-0 flex flex-col gap-8">
    
    <!-- KRİTİK STOK UYARISI (Sadece Admin) -->
    <?php if (current_role() === 'admin' && !empty($kritikStoklar)): ?>
    <div class="animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400">
                    <span class="material-symbols-outlined shrink-0">warning</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">Kritik Stok Uyarısı</h4>
                    <p class="text-xs text-red-500/70">Aşağıdaki ürünler tükenmek üzere!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach(array_slice($kritikStoklar, 0, 5) as $ks): ?>
                    <span class="px-3 py-1 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-lg text-[10px] font-bold text-red-500">
                        <?php echo htmlspecialchars($ks['urun_adi']); ?> (<?php echo number_format($ks['miktar'], 0); ?>)
                    </span>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo public_url('inventory'); ?>" class="text-xs font-bold text-red-600 dark:text-red-400 underline">Yönet</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ÜST STATS KARTLARI (Role göre değişir) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <?php if (current_role() === 'admin'): ?>
        <div class="p-6 rounded-2xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-1 border-l-4 border-l-blue-500">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bekleyen Alacak</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo number_format($jobStats['bekleyen'], 2); ?> ₺</h3>
            <p class="text-[10px] text-slate-400 mt-2">Tüm açık işler</p>
        </div>
        <div class="p-6 rounded-2xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-1 border-l-4 border-l-green-500">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tamamlanan İşler</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo number_format($jobStats['tamamlanan'], 2); ?> ₺</h3>
            <p class="text-[10px] text-slate-400 mt-2">Bu ayın gerçekleşen cirosu</p>
        </div>
        <div class="p-6 rounded-2xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-1 border-l-4 border-l-amber-500">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bugünlük Katılım</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo $presentToday; ?> / <?php echo count($users); ?></h3>
            <p class="text-[10px] text-slate-400 mt-2">Şu an iş başında olanlar</p>
        </div>
        <div class="p-6 rounded-2xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-1 border-l-4 border-l-pink-500">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sarfiyat Kalemi</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo count($topSarfiyat); ?></h3>
            <p class="text-[10px] text-slate-400 mt-2">En çok kullanılan ürün çeşidi</p>
        </div>
        <?php else: ?>
        <!-- Personel sadece izin durumunu görür -->
        <div class="p-6 rounded-2xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-1 border-l-4 border-l-purple-500 col-span-1 sm:col-span-2 lg:col-span-4">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kalan Yıllık İzin</span>
            <?php $user = $users[0]; ?>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo $user['annual_leave_days']; ?> Gün</h3>
            <p class="text-[10px] text-slate-400 mt-2">Hakedilen toplam izin bakiyesi</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- ORTA BÖLÜM -->
    <div class="grid grid-cols-1 <?php echo current_role() === 'admin' ? 'lg:grid-cols-2' : ''; ?> gap-8">
        
        <?php if (current_role() === 'admin'): ?>
        <!-- En Çok Sarf Edilen Malzemeler (Sadece Admin) -->
        <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Harcanan Malzemeler (Top 5)</h3>
            </div>
            <div class="flex-1">
                <?php if (empty($topSarfiyat)): ?>
                    <div class="p-8 text-center text-slate-400 italic text-sm">Veri bulunamadı.</div>
                <?php else: ?>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach($topSarfiyat as $ts): ?>
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($ts['urun_adi']); ?></span>
                            <span class="text-sm font-black text-primary"><?php echo number_format($ts['toplam'], 2); ?> <?php echo $ts['birim']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Son Giriş-Çıkış Kayıtları (Herkes görür ama personel sadece kendisini) -->
        <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col <?php echo current_role() === 'personel' ? 'w-full' : ''; ?>">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Son Giriş-Çıkış Hareketleri</h3>
                <a href="<?php echo public_url('attendance-logs'); ?>" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest">Tümünü Gör</a>
            </div>
            <div class="flex-1">
                <?php if (empty($logs)): ?>
                    <div class="p-12 text-center text-slate-400 italic text-sm">Gösterilecek kayıt bulunamadı.</div>
                <?php else: ?>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach($logs as $l): ?>
                        <div class="px-6 py-5 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                            <div class="flex flex-col gap-1">
                                <span class="font-bold text-slate-800 dark:text-slate-200"><?php echo htmlspecialchars($l['full_name']); ?></span>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px] text-slate-400">calendar_today</span>
                                    <span class="text-[11px] text-slate-500"><?php echo date('d.m.Y', strtotime($l['date'])); ?></span>
                                </div>
                            </div>
                            <div class="flex flex-col text-right gap-1">
                                <div class="flex items-center gap-2 justify-end">
                                    <span class="material-symbols-outlined text-[16px] text-primary">schedule</span>
                                    <span class="font-mono text-primary font-bold text-sm"><?php echo substr($l['clock_in'], 0, 5); ?> - <?php echo substr($l['clock_out'], 0, 5); ?></span>
                                </div>
                                <?php 
                                    $s = $l['status'];
                                    $sClass = 'text-slate-400';
                                    if(in_array($s, ['present','late'])) $sClass = 'text-green-500';
                                    if(in_array($s, ['annual_leave','sick_leave','paid_leave'])) $sClass = 'text-purple-500';
                                ?>
                                <span class="text-[9px] uppercase font-black <?php echo $sClass; ?> tracking-widest"><?php echo $s; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
