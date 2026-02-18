<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Finansal Raporlar 📊</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">İşletmenizin finansal durum analizi</p>
        </div>

        <!-- Date Filter -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <form action="" method="GET" class="flex items-center gap-2">
                 <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                        class="form-input bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-500 rounded-md text-sm shadow-sm placeholder-slate-400 dark:placeholder-slate-500" />
                 <span class="text-slate-400">-</span>
                 <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                        class="form-input bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-500 rounded-md text-sm shadow-sm placeholder-slate-400 dark:placeholder-slate-500" />
                 <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white rounded-md px-4 py-2 text-sm font-medium transition-colors">
                     Filtrele
                 </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        
        <!-- Satışlar -->
        <div class="flex flex-col bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">Toplam Satış (Brüt)</h2>
                <div class="p-2 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-500">
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mr-2">
                <?php echo number_format($summary['sales_gross'], 2, ',', '.'); ?> ₺
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Net: <?php echo number_format($summary['sales_net'], 2, ',', '.'); ?> ₺
            </div>
        </div>

        <!-- Alışlar -->
        <div class="flex flex-col bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">Toplam Alış (Brüt)</h2>
                <div class="p-2 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-500">
                    <span class="material-symbols-outlined">shopping_cart</span>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mr-2">
                <?php echo number_format($summary['purchases_gross'], 2, ',', '.'); ?> ₺
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Net: <?php echo number_format($summary['purchases_net'], 2, ',', '.'); ?> ₺
            </div>
        </div>

        <!-- Tahsilat -->
        <div class="flex flex-col bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">Tahsilat</h2>
                <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-500/10 text-blue-500">
                    <span class="material-symbols-outlined">payments</span>
                </div>
            </div>
            <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mr-2">
                <?php echo number_format($summary['collections'], 2, ',', '.'); ?> ₺
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kasaya Giren Nakit
            </div>
        </div>

        <!-- KDV Durumu -->
        <div class="flex flex-col bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-5">
             <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">KDV Dengesi</h2>
                <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-500/10 text-purple-500">
                    <span class="material-symbols-outlined">percent</span>
                </div>
            </div>
            <?php 
                $vat_balance = $vat_report['balance'];
                $is_payable = $vat_balance > 0;
            ?>
            <div class="text-3xl font-bold <?php echo $is_payable ? 'text-red-500' : 'text-emerald-500'; ?> mr-2">
                <?php echo number_format(abs($vat_balance), 2, ',', '.'); ?> ₺
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-bold">
                <?php echo $is_payable ? 'ÖDENECEK KDV' : 'DEVREDEN KDV'; ?>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- KDV Detay Tablosu -->
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4">KDV Analizi</h3>
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-slate-300">
                    <thead class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/20 border-t border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">Tür</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">Tutar</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700">
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></div>
                                    <div class="font-medium text-slate-800 dark:text-slate-100">Hesaplanan KDV (Satışlardan)</div>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 text-right font-medium text-emerald-500">
                                +<?php echo number_format($vat_report['collected'], 2, ',', '.'); ?> ₺
                            </td>
                        </tr>
                         <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full bg-amber-500 mr-2"></div>
                                    <div class="font-medium text-slate-800 dark:text-slate-100">İndirilecek KDV (Alışlardan)</div>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 text-right font-medium text-amber-500">
                                -<?php echo number_format($vat_report['paid'], 2, ',', '.'); ?> ₺
                            </td>
                        </tr>
                        <tr class="bg-slate-50 dark:bg-slate-900/20 font-bold">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 text-right">NET DURUM</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 text-right <?php echo $is_payable ? 'text-red-500' : 'text-emerald-500'; ?>">
                                <?php echo number_format($vat_balance, 2, ',', '.'); ?> ₺
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nakit Akış Özeti (Basit) -->
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4">Nakit Akış Özeti</h3>
            <div class="space-y-4">
                <!-- Tahsilat Bar -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-800 dark:text-slate-100">Tahsilatlar (Giriş)</span>
                        <span class="font-medium text-blue-500"><?php echo number_format($summary['collections'], 2, ',', '.'); ?> ₺</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Ödeme Bar -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-800 dark:text-slate-100">Ödemeler (Çıkış)</span>
                        <span class="font-medium text-red-500"><?php echo number_format($summary['payments'], 2, ',', '.'); ?> ₺</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                        <?php 
                           $max_val = max($summary['collections'], $summary['payments']); 
                           $pay_width = ($max_val > 0) ? ($summary['payments'] / $max_val * 100) : 0;
                        ?>
                        <div class="bg-red-500 h-2.5 rounded-full" style="width: <?php echo $pay_width; ?>%"></div>
                    </div>
                </div>

                <!-- Giderler -->
                <?php if ($income_expense['operational_expense'] > 0): ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-800 dark:text-slate-100">Operasyonel Giderler</span>
                        <span class="font-medium text-orange-500"><?php echo number_format($income_expense['operational_expense'], 2, ',', '.'); ?> ₺</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                         <div class="bg-orange-500 h-2.5 rounded-full" style="width: 50%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="pt-4 border-t border-slate-200 dark:border-slate-700 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-800 dark:text-slate-100">Dönem Sonu Nakit Akış Dengesi</span>
                        <?php 
                            $cash_flow = $summary['collections'] - $summary['payments'] - $income_expense['operational_expense'];
                        ?>
                        <span class="font-bold text-xl <?php echo $cash_flow >= 0 ? 'text-emerald-500' : 'text-red-500'; ?>">
                            <?php echo ($cash_flow >= 0 ? '+' : '') . number_format($cash_flow, 2, ',', '.'); ?> ₺
                        </span>
                    </div>
                </div>
            </div>
        </div>
    <!-- Aging Analysis (Vade Analizi) -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-4">Vade / Yaşlandırma Analizi ⏳</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- ALACAKLAR (Receivables) -->
            <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-emerald-500">Tahsil Edilecek (Alacaklar)</h3>
                    <span class="text-xs text-slate-400">Detay için satırlara tıklayın</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full dark:text-slate-300">
                        <thead class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="text-left py-2">Vade Durumu</th>
                                <th class="text-right py-2">Tutar</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700 cursor-pointer">
                            <tr onclick="showAgingDetails('receivables', 'future_90_plus')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-emerald-300 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                                        Vadesi Gelecek (90+ Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['future_90_plus'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', 'future_61_90')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-emerald-400 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        Vadesi Gelecek (61-90 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['future_61_90'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', 'future_31_60')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-emerald-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Vadesi Gelecek (31-60 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['future_31_60'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', 'future_0_30')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-emerald-600 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        Vadesi Gelecek (0-30 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['future_0_30'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', 'vadesi_gelen')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-yellow-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                        Vadesi Gelen (Bugün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['vadesi_gelen'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', '0-30')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-orange-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                        0 - 30 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['0-30'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', '31-60')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-orange-600 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                        31 - 60 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['31-60'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', '61-90')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                        61 - 90 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['receivables']['61-90'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('receivables', '90+')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 text-red-600 font-bold group-hover:underline">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                        90+ Gün Geçmiş (Kritik)
                                    </span>
                                </td>
                                <td class="text-right font-bold text-red-600"><?php echo number_format($aging['receivables']['90+'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr class="bg-slate-50 dark:bg-slate-900/20 font-bold border-t border-slate-300 dark:border-slate-600 pointer-events-none">
                                <td class="py-3">TOPLAM ALACAK</td>
                                <td class="text-right py-3 text-emerald-500"><?php echo number_format($aging['receivables']['total'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BORÇLAR (Payables) -->
            <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-red-500">Ödenecek (Borçlar)</h3>
                    <span class="text-xs text-slate-400">Detay için satırlara tıklayın</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full dark:text-slate-300">
                        <thead class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="text-left py-2">Vade Durumu</th>
                                <th class="text-right py-2">Tutar</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700 cursor-pointer">
                            <tr onclick="showAgingDetails('payables', 'future_90_plus')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                                        Vadesi Gelecek (90+ Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['future_90_plus'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', 'future_61_90')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        Vadesi Gelecek (61-90 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['future_61_90'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', 'future_31_60')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Vadesi Gelecek (31-60 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['future_31_60'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', 'future_0_30')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        Vadesi Gelecek (0-30 Gün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['future_0_30'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', 'vadesi_gelen')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-yellow-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                        Vadesi Gelen (Bugün)
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['vadesi_gelen'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', '0-30')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-orange-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                        0 - 30 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['0-30'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', '31-60')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-orange-600 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                        31 - 60 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['31-60'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', '61-90')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 group-hover:text-red-500 transition-colors">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                        61 - 90 Gün Geçmiş
                                    </span>
                                </td>
                                <td class="text-right font-medium"><?php echo number_format($aging['payables']['61-90'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr onclick="showAgingDetails('payables', '90+')" class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                <td class="py-2 text-red-600 font-bold group-hover:underline">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                        90+ Gün Geçmiş (Kritik)
                                    </span>
                                </td>
                                <td class="text-right font-bold text-red-600"><?php echo number_format($aging['payables']['90+'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                             <tr class="bg-slate-50 dark:bg-slate-900/20 font-bold border-t border-slate-300 dark:border-slate-600 pointer-events-none">
                                <td class="py-3">TOPLAM BORÇ</td>
                                <td class="text-right py-3 text-red-500"><?php echo number_format($aging['payables']['total'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DETAY MODAL -->
<div id="aging-modal" class="fixed inset-0 bg-black/60 z-[9999] hidden items-center justify-center p-4 backdrop-blur-sm transition-opacity opacity-0" onclick="closeAgingModal()">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-[95%] max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center shrink-0">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100" id="modal-title">Detaylar</h3>
                <p class="text-xs text-slate-500" id="modal-subtitle">Yükleniyor...</p>
            </div>
            <button onclick="closeAgingModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4" id="modal-content">
            <div class="flex justify-center items-center py-10">
                <span class="material-symbols-outlined animate-spin text-4xl text-indigo-500">progress_activity</span>
            </div>
        </div>
    </div>
</div>

<script>
    function showAgingDetails(type, bucket) {
        const modal = document.getElementById('aging-modal');
        const content = document.getElementById('modal-content');
        const title = document.getElementById('modal-title');
        const subtitle = document.getElementById('modal-subtitle');
        
        // Modal'ı Aç (Hidden kaldır, Flex ekle)
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Reflow for transition
        modal.offsetHeight; 
        modal.classList.remove('opacity-0');
        modal.children[0].classList.remove('scale-95');
        document.body.style.overflow = 'hidden';

        // Başlık Ayarla
        const typeLabel = (type === 'receivables') ? 'Alacaklar (Satış)' : 'Borçlar (Alış)';
        let bucketLabel = '';
        
        switch(bucket) {
            case 'future_0_30':
                bucketLabel = 'Vadesi Gelecek (0-30)';
                break;
            case 'future_31_60':
                bucketLabel = 'Vadesi Gelecek (31-60)';
                break;
            case 'future_61_90':
                bucketLabel = 'Vadesi Gelecek (61-90)';
                break;
            case 'future_90_plus':
                bucketLabel = 'Vadesi Gelecek (90+)';
                break;
            case 'vadesi_gelen':
                bucketLabel = 'Vadesi Gelen (Bugün)';
                break;
            case '0-30':
                bucketLabel = '0-30 Gün Geçmiş';
                break;
            case '31-60':
                bucketLabel = '31-60 Gün Geçmiş';
                break;
            case '61-90':
                bucketLabel = '61-90 Gün Geçmiş';
                break;
            case '90+':
                bucketLabel = '90+ Gün Geçmiş';
                break;
            default:
                bucketLabel = bucket;
        }
        
        title.textContent = `${typeLabel} - ${bucketLabel}`;
        subtitle.textContent = "Veriler yükleniyor...";
        
        // Loading Göster
        content.innerHTML = `
            <div class="flex flex-col justify-center items-center py-12 text-slate-400">
                <span class="material-symbols-outlined animate-spin text-4xl text-indigo-500 mb-3">progress_activity</span>
                <p>Faturalar getiriliyor...</p>
            </div>
        `;

        // AJAX İsteği
        $.ajax({
            url: '<?php echo base_url("FinanceReports/api_get_aging_details"); ?>',
            type: 'POST',
            data: { type: type, bucket: bucket },
            dataType: 'json',
            success: function(res) {
                if (res.error) {
                    content.innerHTML = `<div class="p-4 bg-red-100 text-red-600 rounded-lg">${res.error}</div>`;
                } else {
                    content.innerHTML = res.html;
                    subtitle.textContent = "Liste yüklendi.";
                }
            },
            error: function() {
                content.innerHTML = `<div class="p-4 bg-red-100 text-red-600 rounded-lg">Veri çekilemedi. Bağlantınızı kontrol edin.</div>`;
            }
        });
    }

    function closeAgingModal() {
        const modal = document.getElementById('aging-modal');
        modal.classList.add('opacity-0');
        modal.children[0].classList.add('scale-95');
        
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>
