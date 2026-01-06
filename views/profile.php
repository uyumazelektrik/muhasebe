<?php
$pageTitle = "Profilim";
include __DIR__ . '/layout/header.php';
?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1 mb-2">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Profilim</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Kişisel bilgileriniz ve ödeme dökümünüz.</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Sol Kolon: Bilgiler -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-8 flex flex-col items-center text-center">
                <div class="size-24 rounded-3xl bg-primary flex items-center justify-center text-white text-4xl font-black mb-4 shadow-xl shadow-primary/20">
                    <?php echo mb_substr($user['full_name'], 0, 1, 'UTF-8'); ?>
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <span class="mt-1 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                    <?php echo $user['role'] === 'admin' ? 'Yönetici' : 'Personel'; ?>
                </span>

                <div class="w-full mt-8 space-y-4 text-left">
                    <div class="flex justify-between items-center py-3 border-b border-slate-50 dark:border-slate-800/50">
                        <span class="text-xs font-bold text-slate-400 uppercase">Kullanıcı Adı</span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">@<?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50 dark:border-slate-800/50">
                        <span class="text-xs font-bold text-slate-400 uppercase">İşe Giriş</span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <?php if (current_role() === 'admin'): ?>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-xs font-bold text-slate-400 uppercase">Saatlik Ücret</span>
                        <span class="text-sm font-bold text-emerald-500"><?php echo number_format($user['hourly_rate'], 2); ?> ₺</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-200 dark:shadow-none">
                <h4 class="text-xs font-bold uppercase opacity-60 mb-2">Bilgilendirme</h4>
                <p class="text-xs leading-relaxed opacity-90">
                    Maaş ve mesai ödemeleriniz ayın <b><?php echo $user['salary_day']; ?>.</b> günü sisteme yansıtılır. Yanlışlık olduğunu düşünüyorsanız yöneticiye başvurunuz.
                </p>
            </div>
        </div>

        <!-- Sağ Kolon: Finansal Geçmiş -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Maaş & Ödeme Geçmişi</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-4 px-8 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tarih</th>
                                <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-wider">İşlem Türü</th>
                                <th class="py-4 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Açıklama</th>
                                <th class="py-4 px-8 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Tutar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="4" class="py-20 text-center text-slate-400 italic text-sm">Finansal hareket bulunamadı.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $tx): ?>
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-5 px-8 text-sm font-bold text-slate-700 dark:text-slate-300">
                                        <?php echo date('d.m.Y', strtotime($tx['date'])); ?>
                                    </td>
                                    <td class="py-5 px-6">
                                        <?php 
                                            $typeMap = [
                                                'salary_accrual' => ['Maaş Tahakkuku', 'text-blue-600 dark:text-blue-400 bg-blue-500/10'],
                                                'payment' => ['Maaş Ödemesi', 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10'],
                                                'advance' => ['Avans', 'text-amber-600 dark:text-amber-400 bg-amber-500/10'],
                                                'expense' => ['Masraf Geri Ödeme', 'text-indigo-600 dark:text-indigo-400 bg-indigo-500/10']
                                            ];
                                            $t = $typeMap[$tx['type']] ?? [$tx['type'], 'text-slate-600 bg-slate-500/10'];
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $t[1]; ?>">
                                            <?php echo $t[0]; ?>
                                        </span>
                                    </td>
                                    <td class="py-5 px-6 text-xs text-slate-500 italic max-w-xs truncate">
                                        <?php echo htmlspecialchars($tx['description']); ?>
                                    </td>
                                    <td class="py-5 px-8 text-right font-black text-sm text-slate-900 dark:text-white">
                                        <?php echo number_format($tx['amount'], 2); ?> ₺
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
