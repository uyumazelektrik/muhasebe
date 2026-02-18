<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-6 sm:px-8 shrink-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div class="flex flex-col gap-1">
            <h2 class="text-slate-900 dark:text-white text-3xl font-black leading-tight tracking-tight">Maaş Hak Edişim</h2>
            <p class="text-[#9da6b9] text-base font-normal">Bu ayki çalışma detaylarınız ve kazanç özetiniz.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="month" onchange="this.form.submit()" class="h-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-card-dark px-3 text-slate-900 dark:text-white focus:border-primary">
                <?php
                $months = [
                    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
                    7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
                ];
                foreach ($months as $k => $m) {
                    $selected = $k == $month ? 'selected' : '';
                    echo "<option value='$k' $selected>$m</option>";
                }
                ?>
            </select>
            <select name="year" onchange="this.form.submit()" class="h-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-card-dark px-3 text-slate-900 dark:text-white focus:border-primary">
                <?php foreach($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</header>


<div class="flex flex-1 overflow-hidden p-6 sm:px-8 gap-6 flex-col">
    <!-- Maaş Detay Tablosu -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-slate-800/50 sticky top-0 md:bg-gray-50 md:dark:bg-slate-800/50">
                    <tr class="hidden md:table-row">
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-left">Tarih</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-left">Vardiya</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Normal Kazanç</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Mesai (+1.5x)</th>
                         <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Resmi Tatil (+2x)</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Geç Kalma / Ceza</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Toplam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-card-dark">
                    <?php if (empty($details)): ?>
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">Bu ay için kayıt bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php 
                        $statusMap = [
                            'present' => ['label' => 'Normal', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
                            'holiday' => ['label' => 'Resmi Tatil', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                            'excused' => ['label' => 'İzinli (Mazeretli)', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'],
                            'excused_late' => ['label' => 'Mazeretli Geç', 'class' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
                            'paid_leave' => ['label' => 'Ücretli İzin', 'class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
                            'weekly_leave' => ['label' => 'Haftalık İzin', 'class' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300'],
                            'unpaid_leave' => ['label' => 'Ücretsiz İzin', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'],
                            'annual_leave' => ['label' => 'Yıllık İzin', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
                            'absent' => ['label' => 'Devamsız', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300']
                        ];

                        $totalEarnings = 0;

                        foreach ($details as $row): 
                            $statusInfo = $statusMap[$row['status']] ?? ['label' => $row['status'], 'class' => 'bg-gray-100 text-gray-800'];
                            $timeDisplay = ($row['clock_in'] && $row['clock_in'] !== '00:00' && $row['clock_in'] !== '-') ? 
                                substr($row['clock_in'], 0, 5) . ' - ' . substr($row['clock_out'], 0, 5) : 
                                '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium '.$statusInfo['class'].'">'.$statusInfo['label'].'</span>';
                            
                            $totalEarnings += floatval(str_replace(',', '', $row['financials']['total']));
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors flex flex-col md:table-row py-4 md:py-0">
                            <!-- Mobile View Header -->
                            <td class="px-6 py-2 md:py-4 text-sm font-medium text-slate-700 dark:text-gray-300 md:hidden bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/10 mb-2">
                                <?php echo date('d.m.Y', strtotime($row['date'])); ?> - <?php echo $row['day_name']; ?>
                            </td>

                            <td class="px-6 py-1 md:py-4 text-sm font-medium text-slate-700 dark:text-gray-300 hidden md:table-cell">
                                <?php echo date('d', strtotime($row['date'])); ?> <span class="text-xs font-normal text-slate-400"><?php echo $row['day_name']; ?></span>
                            </td>
                            <td class="px-6 py-1 md:py-4 text-sm text-slate-600 dark:text-gray-400 font-mono">
                                <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-400">Vardiya/Saat:</span>
                                    <span>
                                        <?php echo $timeDisplay; ?>
                                        <?php if (!empty($row['shift_name'])): ?>
                                            <span class="text-[10px] text-gray-400 block"><?php echo $row['shift_name']; ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if($row['note']): ?>
                                    <div class="mt-1 text-xs text-orange-500"><span class="font-bold">Not:</span> <?php echo htmlspecialchars($row['note']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-1 md:py-4 text-sm md:text-right text-slate-700 dark:text-gray-300">
                                <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-400">Normal:</span>
                                    <span>
                                        <?php echo $row['financials']['normal_pay']; ?> ₺
                                        <?php if($row['hours']['normal'] > 0): ?>
                                        <div class="text-[10px] text-slate-400"><?php echo number_format($row['hours']['normal'], 1); ?> sa</div>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-1 md:py-4 text-sm md:text-right text-blue-600 dark:text-blue-400">
                                <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-400">Mesai:</span>
                                    <span>
                                        <?php echo floatval($row['financials']['overtime_pay']) > 0 ? '+' . $row['financials']['overtime_pay'] : '-'; ?> ₺
                                        <?php if($row['hours']['overtime'] > 0): ?>
                                        <div class="text-[10px] text-blue-400"><?php echo number_format($row['hours']['overtime'], 1); ?> sa</div>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-1 md:py-4 text-sm md:text-right text-purple-600 dark:text-purple-400">
                                <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-400">Tatil:</span>
                                    <span>
                                        <?php echo floatval($row['financials']['holiday_pay']) > 0 ? '+' . $row['financials']['holiday_pay'] : '-'; ?> ₺
                                        <?php if($row['hours']['holiday'] > 0): ?>
                                        <div class="text-[10px] text-purple-400"><?php echo number_format($row['hours']['holiday'], 1); ?> sa</div>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-1 md:py-4 text-sm md:text-right text-red-600 dark:text-red-400">
                                 <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-400">Ceza:</span>
                                    <span>
                                        <?php echo floatval($row['financials']['penalty_deduction']) > 0 ? '-' . $row['financials']['penalty_deduction'] : '-'; ?> ₺
                                        <?php if($row['hours']['penalty'] > 0): ?>
                                        <div class="text-[10px] text-red-400"><?php echo number_format($row['hours']['penalty'], 1); ?> sa</div>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-2 md:py-4 text-sm md:text-right font-bold text-slate-900 dark:text-white border-t md:border-t-0 border-gray-50 dark:border-white/5 mt-2 md:mt-0 pt-2 md:pt-4">
                                <div class="flex justify-between md:block">
                                    <span class="md:hidden font-bold text-gray-900 dark:text-white">TOPLAM:</span>
                                    <span><?php echo $row['financials']['total']; ?> ₺</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                         <!-- Footer Total Row -->
                        <tr class="bg-gray-100 dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700">
                             <td colspan="6" class="px-6 py-4 text-right font-black text-slate-900 dark:text-white uppercase tracking-wider hidden md:table-cell">
                                 Toplam Hakediş
                             </td>
                             <td class="px-6 py-4 text-right font-black text-xl text-emerald-600 dark:text-emerald-400">
                                 <div class="flex justify-between md:block items-center">
                                     <span class="md:hidden font-black text-slate-900 dark:text-white text-sm">TOPLAM HAKEDİŞ</span>
                                     <span><?php echo number_format($totalEarnings, 2); ?> ₺</span>
                                 </div>
                             </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/30">
            <p class="text-sm text-slate-500 text-center">
                * Bu hesaplamalar anlık değerlerdir. Resmi bordro yerine geçmez. Kesinleşmiş maaş ay sonunda hesaplanır.
            </p>
        </div>
    </div>
</div>
