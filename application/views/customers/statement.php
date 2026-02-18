<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Ekstresi - <?php echo htmlspecialchars($entity['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            .shadow-xl, .shadow-2xl, .shadow-lg { box-shadow: none !important; }
            .print-break { page-break-inside: avoid; }
            .bg-slate-50 { background: #f8fafc !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 sm:px-6 lg:px-8 print:p-0 print:bg-white">

    <!-- Print/Action Bar -->
    <div class="max-w-5xl mx-auto mb-8 flex justify-between items-center no-print">
        <a href="javascript:history.back()" class="group flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors font-medium">
            <span class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:border-slate-400 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </span>
            <span>Geri Dön</span>
        </a>
        <button onclick="window.print()" class="bg-indigo-600 text-white pl-4 pr-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/20 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Yazdır / PDF İndir
        </button>
    </div>

    <!-- Main Card -->
    <div class="max-w-5xl mx-auto bg-white shadow-xl shadow-slate-200/50 rounded-2xl overflow-hidden border border-slate-100 print:border-none print:shadow-none print:rounded-none">
        
        <!-- Header Section -->
        <div class="bg-slate-50/50 p-8 sm:p-10 border-b border-slate-100 print:p-6 print:bg-white print:border-b-2">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
                <!-- Branding -->
                <div>
                     <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">U</div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">UYUMAZ ELEKTRİK</h2>
                    </div>
                    <p class="text-slate-500 text-sm ml-13">Muhasebe ve Finans Departmanı</p>
                </div>
                
                <!-- Statement Details -->
                <div class="text-left sm:text-right">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-1">HESAP EKSTRESİ</h1>
                    <p class="text-slate-500 font-medium"><?php echo $type === 'summary' ? 'Özet Bakiye Raporu' : 'Detaylı İşlem Dökümü'; ?></p>
                    <p class="text-xs text-slate-400 mt-2 font-mono">Ref: <?php echo strtoupper(substr(md5($entity['id'] . time()), 0, 8)); ?></p>
                </div>
            </div>

            <!-- Client Info Card -->
            <div class="mt-8 bg-white border border-slate-200 rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 print:border print:border-slate-300 print:mt-4 print:p-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Hesap Bilgileri</label>
                    <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($entity['name']); ?></h3>
                   <div class="flex flex-col gap-0.5 mt-1">
                        <?php if($entity['tax_id']): ?><span class="text-sm text-slate-500">Vergi No: <?php echo htmlspecialchars($entity['tax_id']); ?></span><?php endif; ?>
                        <?php if($entity['address']): ?><span class="text-sm text-slate-500 max-w-md"><?php echo htmlspecialchars($entity['address']); ?></span><?php endif; ?>
                   </div>
                </div>
                <div class="md:text-right">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Rapor Dönemi</label>
                    <form method="GET" class="flex flex-col sm:flex-row items-center gap-2 md:justify-end no-print">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                        <div class="relative">
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                                class="px-3 py-1 bg-slate-50 rounded-lg text-sm font-bold text-slate-700 border border-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-32 sm:w-auto">
                        </div>
                        <span class="text-slate-400 hidden sm:inline">-</span>
                        <div class="relative">
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                                class="px-3 py-1 bg-slate-50 rounded-lg text-sm font-bold text-slate-700 border border-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-32 sm:w-auto">
                        </div>
                        <button type="submit" class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors" title="Filtrele">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </button>
                    </form>
                    <div class="hidden print:flex items-center gap-2 justify-end">
                        <span class="px-3 py-1 bg-slate-100 rounded-lg text-sm font-bold text-slate-700 border border-slate-200">
                            <?php echo date('d.m.Y', strtotime($start_date)); ?>
                        </span>
                        <span class="text-slate-400">-</span>
                        <span class="px-3 py-1 bg-slate-100 rounded-lg text-sm font-bold text-slate-700 border border-slate-200">
                            <?php echo date('d.m.Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="print:leading-tight overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider print:bg-slate-100">
                        <th class="py-4 pl-8 sm:pl-10 w-32 print:pl-4 print:py-2 whitespace-nowrap">Tarih</th>
                        <th class="py-4 px-4 w-32 print:py-2 whitespace-nowrap">İşlem</th>
                        <th class="py-4 px-4 print:py-2 min-w-[200px]">Açıklama</th>
                        <th class="py-4 px-4 text-right w-32 print:py-2 whitespace-nowrap">Borç</th>
                        <th class="py-4 px-4 text-right w-32 print:py-2 whitespace-nowrap">Alacak</th>
                        <th class="py-4 pr-8 sm:pr-10 text-right w-40 print:pr-4 print:py-2 whitespace-nowrap">Bakiye</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                     <!-- Opening Balance Row -->
                     <tr class="bg-slate-50/30 print:bg-transparent">
                        <td class="py-4 pl-8 sm:pl-10 font-bold text-slate-700 print:pl-4 print:py-2 whitespace-nowrap"><?php echo date('d.m.Y', strtotime($start_date)); ?></td>
                        <td class="py-4 px-4 print:py-2"><span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">DEVİR</span></td>
                        <td class="py-4 px-4 text-slate-500 font-medium print:py-2">Dönem Başı Devir Bakiyesi</td>
                        <td class="py-4 px-4 text-right print:py-2 whitespace-nowrap">
                             <?php if($opening_balance > 0): ?>
                                <span class="text-red-500 font-bold text-sm"><?php echo number_format($opening_balance, 2); ?> ₺</span>
                             <?php else: ?>
                                <span class="text-slate-300">-</span>
                             <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right print:py-2 whitespace-nowrap">
                             <?php if($opening_balance < 0): ?>
                                <span class="text-emerald-600 font-bold text-sm"><?php echo number_format(abs($opening_balance), 2); ?> ₺</span>
                             <?php else: ?>
                                <span class="text-slate-300">-</span>
                             <?php endif; ?>
                        </td>
                        <td class="py-4 pr-8 sm:pr-10 text-right print:pr-4 print:py-2 whitespace-nowrap">
                            <div class="flex flex-col items-end">
                                <span class="font-bold text-slate-900"><?php echo number_format(abs($opening_balance), 2); ?> ₺</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider <?php echo $opening_balance <= 0 ? 'text-emerald-500' : 'text-red-400'; ?>">
                                    <?php echo $opening_balance <= 0 ? 'Alacaklı' : 'Borçlu'; ?>
                                </span>
                            </div>
                        </td>
                    </tr>

                    <?php 
                    $balance = $opening_balance;
                    $total_debt = 0;
                    $total_credit = 0;

                    foreach($transactions as $t): 
                        $balance += $t['amount'];
                        if($t['amount'] > 0) $total_debt += $t['amount'];
                        else $total_credit += abs($t['amount']);
                        
                        $debt = $t['amount'] > 0 ? $t['amount'] : 0;
                        $credit = $t['amount'] < 0 ? abs($t['amount']) : 0;
                        
                        // Style selection
                        $badgeStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                        if(in_array($t['type'], ['fatura', 'invoice', 'sale'])) $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-100';
                        if(in_array($t['type'], ['purchase'])) $badgeStyle = 'bg-orange-50 text-orange-700 border-orange-100';
                        if(in_array($t['type'], ['tahsilat'])) $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        if(in_array($t['type'], ['odeme'])) $badgeStyle = 'bg-red-50 text-red-700 border-red-100';
                        
                        // Row Style
                        $rowClass = 'hover:bg-slate-50 transition-colors print:break-inside-avoid';
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td class="py-4 pl-8 sm:pl-10 text-sm font-medium text-slate-600 print:pl-4 print:py-2 whitespace-nowrap">
                            <?php echo date('d.m.Y', strtotime($t['transaction_date'])); ?>
                        </td>
                        <td class="py-4 px-4 print:py-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border whitespace-nowrap <?php echo $badgeStyle; ?>">
                                <?php echo strtoupper($t['type']); ?>
                            </span>
                        </td>
                        <td class="py-4 px-4 print:py-2">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($t['description']); ?></span>
                                <?php if(!empty($t['document_no'])): ?>
                                    <span class="text-[10px] text-slate-400 font-mono mt-0.5">#<?php echo $t['document_no']; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-right print:py-2 whitespace-nowrap">
                            <?php if($debt > 0): ?>
                                <span class="text-red-500/90 font-bold text-sm"><?php echo number_format($debt, 2); ?> ₺</span>
                            <?php else: ?>
                                <span class="text-slate-200">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right print:py-2 whitespace-nowrap">
                             <?php if($credit > 0): ?>
                                <span class="text-emerald-600/90 font-bold text-sm"><?php echo number_format($credit, 2); ?> ₺</span>
                            <?php else: ?>
                                <span class="text-slate-200">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 pr-8 sm:pr-10 text-right print:pr-4 print:py-2 whitespace-nowrap">
                            <div class="flex flex-col items-end">
                                <span class="font-bold text-slate-900"><?php echo number_format(abs($balance), 2); ?> ₺</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider <?php echo $balance <= 0 ? 'text-emerald-500' : 'text-red-400'; ?>">
                                    <?php echo $balance <= 0 ? 'Alacaklı' : 'Borçlu'; ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Items Detail -->
                    <?php if(!empty($t['items'])): ?>
                    <tr class="print:break-inside-avoid">
                        <td colspan="6" class="p-0 border-none">
                            <div class="bg-slate-50/50 border-y border-slate-100 mx-4 sm:mx-10 rounded-lg my-2 p-4 print:mx-4 print:border overflow-x-auto">
                                <table class="w-full text-xs min-w-[600px]">
                                    <thead>
                                        <tr class="text-slate-400 font-semibold border-b border-slate-200/60">
                                            <th class="pb-2 pl-2 text-left">Kalem / Ürün</th>
                                            <th class="pb-2 text-right">Birim Fiyat</th>
                                            <th class="pb-2 text-right">Miktar</th>
                                            <th class="pb-2 text-center w-20">KDV %</th>
                                            <th class="pb-2 text-right">KDV Tutarı</th>
                                            <th class="pb-2 pr-2 text-right">Toplam</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-600">
                                        <?php foreach($t['items'] as $item): ?>
                                        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-100/50">
                                            <td class="py-1.5 pl-2 font-medium"><?php echo htmlspecialchars($item['product_name'] ?? 'Ürün/Hizmet'); ?></td>
                                            <td class="py-1.5 text-right font-mono whitespace-nowrap"><?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td class="py-1.5 text-right font-mono whitespace-nowrap"><?php echo number_format($item['quantity'], 2); ?> <?php echo $item['unit'] ?? ''; ?></td>
                                            <td class="py-1.5 text-center font-mono text-slate-500">%<?php echo number_format($item['tax_rate'], 0); ?></td>
                                            <td class="py-1.5 text-right font-mono text-slate-500"><?php echo number_format($item['tax_amount'], 2); ?></td>
                                            <td class="py-1.5 pr-2 text-right font-bold text-slate-800 font-mono whitespace-nowrap"><?php echo number_format($item['total_amount'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                     <tr class="bg-slate-50 border-t-2 border-slate-200 print:bg-slate-100 print:border-t-2 print:border-slate-800">
                        <td colspan="3" class="py-6 pl-8 sm:pl-10 text-right whitespace-nowrap">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest mr-4">Genel Toplamlar</span>
                        </td>
                        <td class="py-6 px-4 text-right whitespace-nowrap">
                             <?php $final_debt_total = $total_debt + ($opening_balance > 0 ? $opening_balance : 0); 
                                   if($final_debt_total > 0): ?>
                             <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Toplam Borç</span>
                                <span class="text-red-500 font-bold"><?php echo number_format($final_debt_total, 2); ?> ₺</span>
                             </div>
                             <?php endif; ?>
                        </td>
                        <td class="py-6 px-4 text-right whitespace-nowrap">
                             <?php $final_credit_total = $total_credit + ($opening_balance < 0 ? abs($opening_balance) : 0); 
                                   if($final_credit_total > 0): ?>
                             <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Toplam Alacak</span>
                                <span class="text-emerald-600 font-bold"><?php echo number_format($final_credit_total, 2); ?> ₺</span>
                             </div>
                             <?php endif; ?>
                        </td>
                        <td class="py-6 pr-8 sm:pr-10 text-right whitespace-nowrap">
                            <div class="p-4 bg-white border border-slate-200 rounded-xl inline-block shadow-sm min-w-[160px] print:shadow-none print:border-slate-800">
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1 text-center">Genel Bakiye</p>
                                <div class="text-2xl font-black text-center <?php echo $balance <= 0 ? 'text-emerald-600' : 'text-red-500'; ?>">
                                    <?php echo number_format(abs($balance), 2); ?> ₺
                                </div>
                                <div class="text-center mt-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?php echo $balance <= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'; ?>">
                                        <?php echo $balance <= 0 ? 'ALACAKLI' : 'BORÇLU'; ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="bg-white p-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400 print:bg-white print:p-4 print:border-t-2">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <p>Bu hesap ekstresi <?php echo date('d.m.Y H:i'); ?> tarihinde sistemden otomatik oluşturulmuştur.</p>
            </div>
            <p class="font-bold text-slate-300 print:text-slate-500">Uyumaz Elektrik v1.0</p>
        </div>
    </div>

</body>
</html>
