<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="p-6">
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Yönetici'); ?></h2>
        <p class="text-xs font-medium text-gray-500 mt-1">İşte işletmenizin bugünkü özeti</p>
    </div>

    <!-- Quick Stats for Admin -->
    <?php if(current_role() === 'admin'): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
            <div class="size-10 rounded-xl bg-blue-100 dark:bg-blue-500/10 text-blue-600 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kasa Toplamı</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo number_format($total_wallets ?? 0, 2); ?> ₺</h3>
        </div>
        <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
            <div class="size-10 rounded-xl bg-orange-100 dark:bg-orange-500/10 text-orange-600 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined">trending_down</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Toplam Alacak</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo number_format($stats['total_receivables'] ?? 0, 2); ?> ₺</h3>
        </div>
        <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
            <div class="size-10 rounded-xl bg-red-100 dark:bg-red-500/10 text-red-600 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined">trending_up</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Toplam Borç</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo number_format($stats['total_debt'] ?? 0, 2); ?> ₺</h3>
        </div>
        <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
            <div class="size-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ürün Çeşidi</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $product_count; ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-card-dark rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 dark:border-border-dark flex items-center justify-between">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Son İşlemler</h4>
                    <a href="<?php echo site_url('finance-reports'); ?>" class="text-xs font-bold text-primary transition-all">Tümünü Gör</a>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-border-dark">
                    <?php if(empty($recent_transactions)): ?>
                        <div class="p-8 text-center text-gray-400 text-sm">Henüz işlem kaydı yok.</div>
                    <?php else: ?>
                        <?php foreach($recent_transactions as $t): ?>
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="size-10 rounded-xl <?php echo in_array($t['type'], ['invoice', 'payment']) ? 'bg-red-50 dark:bg-red-500/10 text-red-500' : 'bg-green-50 dark:bg-green-500/10 text-green-500'; ?> flex items-center justify-center">
                                    <span class="material-symbols-outlined">
                                        <?php echo in_array($t['type'], ['invoice', 'payment']) ? 'arrow_upward' : 'arrow_downward'; ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($t['description']); ?></div>
                                    <div class="text-[10px] text-gray-400"><?php echo htmlspecialchars($t['entity_name'] ?? 'Genel İşlem'); ?> • <?php echo date('d.m.Y', strtotime($t['transaction_date'])); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-sm <?php echo in_array($t['type'], ['invoice', 'payment']) ? 'text-red-500' : 'text-green-500'; ?>">
                                    <?php echo in_array($t['type'], ['invoice', 'payment']) ? '-' : '+'; ?>
                                    <?php echo number_format($t['amount'], 2); ?> ₺
                                </div>
                                <div class="text-[9px] text-gray-400 font-bold uppercase"><?php echo $t['type']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Practical Actions -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gradient-to-br from-primary to-blue-700 p-8 rounded-3xl text-white shadow-xl shadow-blue-500/20">
                <h4 class="text-xl font-black mb-2">Hızlı Satış</h4>
                <p class="text-white/70 text-sm mb-6 font-medium leading-relaxed">Yeni bir satış yapmak ve stokları güncellemek için POS ekranını kullanın.</p>
                <a href="<?php echo site_url('sales/pos'); ?>" class="inline-flex items-center gap-2 bg-white text-primary px-6 py-3 rounded-2xl font-black text-sm hover:scale-105 transition-all active:scale-95 shadow-lg">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    SATIŞ EKRANI
                </a>
            </div>
            
            <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4">Hızlı Menü</h4>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?php echo site_url('entities'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-primary">groups</span>
                        <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Cariler</span>
                    </a>
                    <a href="<?php echo site_url('inventory'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-amber-500">inventory_2</span>
                        <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Stoklar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: // Staff Dashboard Layout ?>
    
    <!-- Top: Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gradient-to-br from-primary to-blue-700 p-8 rounded-3xl text-white shadow-xl shadow-blue-500/20">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xl font-black">İş Takibi</h4>
                <div class="bg-white/20 p-2 rounded-xl">
                    <span class="material-symbols-outlined">engineering</span>
                </div>
            </div>
            <p class="text-white/70 text-sm mb-6 font-medium leading-relaxed">Atanan işleri ve durumlarını yönetmek için tıklayın.</p>
            <a href="<?php echo site_url('jobs'); ?>" class="w-full inline-flex items-center justify-center gap-2 bg-white text-primary px-6 py-3 rounded-2xl font-black text-sm hover:scale-[1.02] transition-all active:scale-[0.98] shadow-lg">
                İŞ LİSTESİ
            </a>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-8 rounded-3xl text-white shadow-xl shadow-amber-500/20">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xl font-black">Fiyat Sorgula</h4>
                <div class="bg-white/20 p-2 rounded-xl">
                    <span class="material-symbols-outlined">barcode_scanner</span>
                </div>
            </div>
            <p class="text-white/70 text-sm mb-6 font-medium leading-relaxed">Ürün fiyatını ve stok durumunu kontrol edin.</p>
            <a href="<?php echo site_url('inventory-check'); ?>" class="w-full inline-flex items-center justify-center gap-2 bg-white text-amber-600 px-6 py-3 rounded-2xl font-black text-sm hover:scale-[1.02] transition-all active:scale-[0.98] shadow-lg">
                SORGULA
            </a>
        </div>
    </div>

    <!-- Middle: Quick Menu -->
    <div class="mb-8">
        <div class="bg-white dark:bg-card-dark p-6 rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm">
            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4">Hızlı Menü</h4>
            <div class="grid grid-cols-2 gap-4">
                <a href="<?php echo site_url('payroll/my-payroll'); ?>" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-primary/5 hover:border-primary/20 border border-transparent transition-all group">
                    <div class="size-12 rounded-xl bg-green-100 dark:bg-green-500/10 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                    <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Maaş Hak Ediş</h5>
                        <p class="text-[10px] font-medium text-gray-400">Finansal hareketlerinizi görüntüleyin</p>
                    </div>
                </a>
                <a href="<?php echo site_url('sales/pos'); ?>" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-primary/5 hover:border-primary/20 border border-transparent transition-all group">
                    <div class="size-12 rounded-xl bg-blue-100 dark:bg-blue-500/10 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">point_of_sale</span>
                    </div>
                    <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Hızlı Satış</h5>
                        <p class="text-[10px] font-medium text-gray-400">Yeni satış işlemi başlatın</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom: Attendance Logs -->
    <div class="w-full">
        <div class="bg-white dark:bg-card-dark rounded-3xl border border-gray-100 dark:border-border-dark shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-border-dark flex items-center justify-between">
                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Son Giriş-Çıkış Kayıtlarım</h4>
                <a href="<?php echo site_url('profile'); ?>" class="text-xs font-bold text-primary transition-all">Tümünü Gör</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-border-dark">
                <?php if(empty($attendance_logs)): ?>
                    <div class="p-8 text-center text-gray-400 text-sm">Henüz kayıt yok.</div>
                <?php else: ?>
                    <?php foreach($attendance_logs as $log): ?>
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl <?php echo $log['status'] == 'present' || $log['status'] == 'late' ? 'bg-green-50 dark:bg-green-500/10 text-green-500' : 'bg-red-50 dark:bg-red-500/10 text-red-500'; ?> flex items-center justify-center">
                                <span class="material-symbols-outlined">
                                    <?php echo $log['status'] == 'present' || $log['status'] == 'late' ? 'login' : 'logout'; ?>
                                </span>
                            </div>
                            <div>
                                <div class="font-bold text-sm text-gray-900 dark:text-white"><?php echo date('d.m.Y', strtotime($log['date'])); ?></div>
                                <div class="text-[10px] text-gray-400">
                                    Giriş: <?php echo $log['clock_in'] ? substr($log['clock_in'], 0, 5) : '-'; ?> • 
                                    Çıkış: <?php echo $log['clock_out'] ? substr($log['clock_out'], 0, 5) : '-'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-sm text-gray-900 dark:text-white">
                                <?php 
                                    $statusMap = [
                                        'present' => 'Normal Mesai',
                                        'absent' => 'Devamsız',
                                        'late' => 'Geç Kaldı',
                                        'excused_late' => 'Mazeretli Geç',
                                        'leave' => 'İzinli',
                                        'paid_leave' => 'Ücretli İzin',
                                        'unpaid_leave' => 'Ücretsiz İzin',
                                        'annual_leave' => 'Yıllık İzin',
                                        'sick_leave' => 'Raporlu',
                                        'weekly_leave' => 'Haftalık İzin',
                                        'holiday' => 'Resmi Tatil',
                                        'public_holiday' => 'Resmi Tatil'
                                    ];
                                    echo $statusMap[$log['status']] ?? $log['status'];
                                ?>
                            </div>
                            <div class="text-[9px] text-gray-400 font-bold uppercase"><?php echo htmlspecialchars($log['shift_name'] ?? ''); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>
