<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-6 sm:px-8 shrink-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div class="flex flex-col gap-1">
            <h2 class="text-slate-900 dark:text-white text-3xl font-black leading-tight tracking-tight">Maaş Yönetimi</h2>
            <p class="text-[#9da6b9] text-base font-normal">Personel maaş hesaplamaları ve bordro önizleme.</p>
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
    <!-- Maaş Tablosu -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-slate-800/50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider">Personel</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Saat Ücreti</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Çalışma (Saat)</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Ek Kazançlar</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-center">Kesintiler</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#9da6b9] uppercase tracking-wider text-right">Net Maaş</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-card-dark">
                    <?php if (empty($payrollData)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Bu ay için kayıt bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payrollData as $row): ?>
                        <tr onclick="openPayrollDetail(<?php echo $row['user']['id']; ?>, <?php echo $month; ?>, <?php echo $year; ?>)" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group">
                            <td class="px-6 py-4">
                                <span class="block font-bold text-slate-900 dark:text-white text-base group-hover:text-primary transition-colors"><?php echo htmlspecialchars($row['user']['full_name']); ?></span>
                                <span class="text-xs text-slate-500"><?php echo $row['stats']['days_worked']; ?> gün çalışma, <?php echo $row['stats']['days_leave']; ?> gün izin</span>
                                <span class="text-[10px] text-blue-500 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Detaylar için tıkla</span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-slate-600 dark:text-gray-300">
                                <?php echo number_format($row['user']['hourly_rate'], 2); ?> ₺
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-1 items-center">
                                    <span class="text-sm font-medium text-slate-700 dark:text-gray-200">
                                        <?php echo format_hours($row['stats']['normal_hours']); ?> Normal
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        <?php echo number_format($row['financials']['normal_pay'], 2); ?> ₺
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-2">
                                    <?php if ($row['stats']['overtime_hours'] > 0): ?>
                                    <div class="flex justify-between items-center bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded text-xs gap-2">
                                        <span class="text-blue-700 dark:text-blue-300 font-bold">+<?php echo format_hours($row['stats']['overtime_hours']); ?> Mesai</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-mono"><?php echo number_format($row['financials']['overtime_pay'], 2); ?> ₺</span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['stats']['holiday_hours'] > 0): ?>
                                    <div class="flex justify-between items-center bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded text-xs gap-2">
                                        <span class="text-purple-700 dark:text-purple-300 font-bold">+<?php echo format_hours($row['stats']['holiday_hours']); ?> Tatil</span>
                                        <span class="text-purple-600 dark:text-purple-400 font-mono"><?php echo number_format($row['financials']['holiday_pay'], 2); ?> ₺</span>
                                    </div>
                                    <?php endif; ?>
 
                                    <?php if ($row['stats']['overtime_hours'] == 0 && $row['stats']['holiday_hours'] == 0): ?>
                                        <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($row['stats']['penalty_hours'] > 0): ?>
                                <div class="flex justify-between items-center bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded text-xs mx-auto max-w-[140px] gap-2">
                                    <span class="text-red-700 dark:text-red-300 font-bold"><?php echo format_hours($row['stats']['penalty_hours']); ?> Geç</span>
                                    <span class="text-red-600 dark:text-red-400 font-mono">-<?php echo number_format($row['financials']['penalty_deduction'], 2); ?> ₺</span>
                                </div>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="block text-lg font-black text-emerald-600 dark:text-emerald-400">
                                    <?php echo number_format($row['financials']['net_salary'], 2); ?> ₺
                                </span>
                                <span class="text-xs text-slate-400">Brüt: <?php echo number_format($row['financials']['gross_salary'], 2); ?> ₺</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/30">
            <p class="text-sm text-slate-500 text-center">
                * Bu hesaplamalar tahmini değerlerdir. Resmi bordro yerine geçmez.
                <br>Hesaplama Formülü: (Normal Saat x Ücret) + (Mesai x 1.5) + (Tatil x 2.0) - (Geç Kalma Süresi x 2.0)
            </p>
        </div>
    </div>
</div>

<!-- Modal: Maaş Detayları -->
<div id="payrollDetailModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-card-dark rounded-xl w-full max-w-7xl shadow-2xl border border-gray-200 dark:border-border-dark flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center">
            <div>
                 <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modalUserName">Personel Adı</h3>
                 <p class="text-sm text-slate-500" id="modalPeriod">Dönem Detayları</p>
            </div>
            <button onclick="document.getElementById('payrollDetailModal').classList.add('hidden')" class="p-2 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-full transition-colors text-slate-500">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-0">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-slate-800/50 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-left">Tarih</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-left">Giriş/Çıkış</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Günlük Kazanç</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Mesai (+1.5x)</th>
                         <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Resmi Tatil (+2x)</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Geç Kalma / Ceza</th>
                        <th class="px-6 py-3 text-xs font-bold text-[#9da6b9] uppercase text-right">Toplam</th>
                    </tr>
                </thead>
                <tbody id="payrollDetailsBody" class="divide-y divide-gray-100 dark:divide-slate-800">
                    <!-- JS ile doldurulacak -->
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900/50 text-right">
            <button onclick="document.getElementById('payrollDetailModal').classList.add('hidden')" class="px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold rounded-lg hover:opacity-90 transition-opacity">Kapat</button>
        </div>
    </div>
</div>

<script>
    async function openPayrollDetail(userId, month, year) {
        const modal = document.getElementById('payrollDetailModal');
        const tbody = document.getElementById('payrollDetailsBody');
        const nameEl = document.getElementById('modalUserName');
        
        // Modal aç, loading göster
        tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-slate-500 flex flex-col gap-2 items-center"><span class="material-symbols-outlined animate-spin">progress_activity</span> Yükleniyor...</td></tr>';
        modal.classList.remove('hidden');
        
        try {
            const response = await fetch(`<?php echo site_url('api/get-user-payroll'); ?>?user_id=${userId}&month=${month}&year=${year}`);
            const data = await response.json();
            
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-red-500">${data.error}</td></tr>`;
                return;
            }
            
            nameEl.textContent = data.user.full_name;
            
            let html = '';
            if (data.details.length === 0) {
                 html = `<tr><td colspan="7" class="p-8 text-center text-slate-500">Bu dönemde kayıt bulunamadı.</td></tr>`;
            } else {
                const statusMap = {
                    'present': { label: 'Normal', class: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' },
                    'holiday': { label: 'Resmi Tatil', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' },
                    'excused': { label: 'İzinli (Mazeretli)', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' },
                    'excused_late': { label: 'Mazeretli Geç', class: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300' },
                    'paid_leave': { label: 'Ücretli İzin', class: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' },
                    'weekly_leave': { label: 'Haftalık İzin', class: 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300' },
                    'unpaid_leave': { label: 'Ücretsiz İzin', class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' },
                    'annual_leave': { label: 'Yıllık İzin', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' },
                    'absent': { label: 'Devamsız', class: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }
                };

                const formatHours = (h) => {
                    const hours = parseFloat(h);
                    if (isNaN(hours) || hours === 0) return '';
                    const wholeHours = Math.floor(hours);
                    const minutes = Math.round((hours - wholeHours) * 60);
                    if (wholeHours > 0 && minutes > 0) return `(${wholeHours} sa ${minutes} dk)`;
                    if (wholeHours > 0) return `(${wholeHours} sa)`;
                    return `(${minutes} dk)`;
                };

                data.details.forEach(item => {
                    const statusInfo = statusMap[item.status] || { label: item.status, class: 'bg-gray-100 text-gray-800' };
                    let timeDisplay = `${item.clock_in} - ${item.clock_out}`;
                    
                    if ((!item.clock_in || item.clock_in === '00:00') && (!item.clock_out || item.clock_out === '00:00')) {
                        timeDisplay = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${statusInfo.class}">${statusInfo.label}</span>`;
                    } else {
                         timeDisplay += ` <br><span class="inline-flex items-center px-1.5 py-0.5 rounded-[4px] text-[10px] font-medium mt-1 ${statusInfo.class}">${statusInfo.label}</span>`;
                    }

                    html += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/30 transition-colors border-b border-gray-50 dark:border-slate-800/50">
                            <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-gray-300">
                                ${item.date} <span class="text-xs font-normal text-slate-400 block">${item.day_name || ''}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-gray-400 font-mono">
                                ${timeDisplay}
                                ${item.note ? `<div class="mt-1"><span class="material-symbols-outlined text-[14px] text-slate-400 dark:text-gray-500 cursor-help" title="${item.note}">description</span></div>` : ''}
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-slate-700 dark:text-gray-300">
                                ${item.financials.normal_pay} ₺
                                <div class="text-[10px] text-slate-400">${formatHours(item.hours.normal)}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-blue-600 dark:text-blue-400">
                                ${parseFloat(item.financials.overtime_pay) > 0 ? '+' + item.financials.overtime_pay + ' ₺' : '-'}
                                <div class="text-[10px] text-blue-400">${formatHours(item.hours.overtime)}</div>
                            </td>
                             <td class="px-6 py-3 text-sm text-right text-purple-600 dark:text-purple-400">
                                ${parseFloat(item.financials.holiday_pay) > 0 ? '+' + item.financials.holiday_pay + ' ₺' : '-'}
                                <div class="text-[10px] text-purple-400">${formatHours(item.hours.holiday)}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-red-600 dark:text-red-400">
                                ${parseFloat(item.financials.penalty_deduction) > 0 ? '-' + item.financials.penalty_deduction + ' ₺' : '-'}
                                <div class="text-[10px] text-red-400">${formatHours(item.hours.penalty)}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-slate-900 dark:text-white">
                                ${item.financials.total} ₺
                            </td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;
            
        } catch (e) {
            console.error(e);
            tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-red-500">Bir hata oluştu.</td></tr>`;
        }
    }
    
    // Dışarı tıklayınca kapat
    window.onclick = function(event) {
        const modal = document.getElementById('payrollDetailModal');
        if (event.target == modal) {
            modal.classList.add('hidden');
        }
    }
</script>
