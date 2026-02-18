<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="p-6 sm:px-8 flex flex-col gap-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sol Kolon: Profil ve Maaş -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Profil Kartı -->
            <div class="bg-white dark:bg-card-dark rounded-3xl border border-gray-100 dark:border-slate-800 p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-3 opacity-5">
                    <span class="material-symbols-outlined text-[120px]">account_circle</span>
                </div>
                
                <div class="size-24 rounded-full bg-primary/10 flex items-center justify-center shrink-0 border-4 border-white dark:border-card-dark shadow-xl text-primary text-4xl font-black">
                    <?php echo mb_substr($user['full_name'] ?? 'U', 0, 1); ?>
                </div>
                
                <div class="flex-1 text-center sm:text-left z-10">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="flex items-center justify-center sm:justify-start gap-2 mb-4">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-bold uppercase tracking-wide">
                            <?php echo $user['role'] === 'admin' ? 'Yönetici' : 'Personel'; ?>
                        </span>
                        <span class="px-3 py-1 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-lg text-xs font-mono">
                            @<?php echo htmlspecialchars($user['username']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 max-w-md">
                        <div class="p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-gray-100 dark:border-slate-700">
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Saatlik Ücret</div>
                            <div class="text-lg font-black text-slate-900 dark:text-white"><?php echo number_format($user['hourly_rate'] ?? 0, 2); ?> ₺</div>
                        </div>
                         <div class="p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-gray-100 dark:border-slate-700">
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Maaş Günü</div>
                            <div class="text-lg font-black text-slate-900 dark:text-white">Her Ayın <?php echo $user['salary_day'] ?? 1; ?>. Günü</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maaş & Bordro Kartı -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl p-8 text-white shadow-xl shadow-emerald-500/20 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/10 translate-x-[100%] group-hover:translate-x-[-100%] transition-transform duration-1000 skew-x-12"></div>
                
                <div class="relative z-10">
                    <h3 class="text-2xl font-black mb-2">Maaş ve Hakediş Detayları</h3>
                    <p class="text-emerald-100 font-medium max-w-md text-sm leading-relaxed">
                        Aylık çalışma saatlerinizi, mesai ücretlerinizi ve toplam hakedişinizi detaylı olarak görüntüleyin.
                    </p>
                </div>
                <a href="<?php echo site_url('payroll/my-payroll'); ?>" class="relative z-10 bg-white text-emerald-600 px-8 py-4 rounded-2xl font-black shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined">payments</span>
                    BORDRO GÖRÜNTÜLE
                </a>
            </div>

             <!-- Son Hareketler -->
             <div class="bg-white dark:bg-card-dark rounded-3xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 dark:border-slate-800 flex items-center gap-3">
                    <div class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-xl">history</span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Son Giriş-Çıkış Kayıtları</h3>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-slate-800">
                    <?php if(empty($recent_attendance)): ?>
                        <div class="p-8 text-center text-gray-400 text-sm">Henüz kayıt yok.</div>
                    <?php else: ?>
                        <?php foreach($recent_attendance as $log): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="size-10 rounded-xl <?php echo $log['status'] == 'present' || $log['status'] == 'late' ? 'bg-green-50 dark:bg-green-500/10 text-green-600' : 'bg-red-50 dark:bg-red-500/10 text-red-600'; ?> flex items-center justify-center font-bold text-xs">
                                    <?php echo date('d', strtotime($log['date'])); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                                        <?php 
                                            $statusMap = ['present'=>'Geldi', 'absent'=>'Gelmedi', 'late'=>'Geç Kaldı', 'leave'=>'İzinli', 'holiday'=>'Tatil'];
                                            echo $statusMap[$log['status']] ?? $log['status'];
                                        ?>
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        <?php echo date('d.m.Y', strtotime($log['date'])); ?>
                                    </div>
                                </div>
                            </div>
                             <div class="text-right">
                                <span class="bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1 rounded text-xs font-mono font-medium">
                                    <?php echo $log['clock_in'] ? substr($log['clock_in'], 0, 5) : '--:--'; ?> - 
                                    <?php echo $log['clock_out'] ? substr($log['clock_out'], 0, 5) : '--:--'; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sağ Kolon: Şifre Değiştir -->
        <div class="flex flex-col gap-6">
            <div class="bg-white dark:bg-card-dark rounded-3xl border border-gray-100 dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                     <div class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-xl">lock_reset</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">Şifre Değiştir</h3>
                        <p class="text-xs text-slate-400">Güvenliğiniz için şifrenizi güncelleyin.</p>
                    </div>
                </div>

                <form id="passwordForm" action="<?php echo site_url('profile/update_password'); ?>" method="POST" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Mevcut Şifre</label>
                        <input type="password" name="current_password" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors">
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Yeni Şifre</label>
                        <input type="password" name="new_password" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors">
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="confirm_password" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-colors shadow-lg shadow-indigo-500/20 mt-2">
                        ŞİFREYİ GÜNCELLE
                    </button>
                </form>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/10 rounded-3xl p-6 border border-blue-100 dark:border-blue-900/20">
                <div class="flex gap-4">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
                    <div>
                        <h4 class="font-bold text-blue-800 dark:text-blue-300 text-sm mb-1">Bilgilendirme</h4>
                        <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                            Kişisel bilgilerinizde (Ad, Email vb.) bir değişiklik yapılması gerekiyorsa lütfen yönetici ile iletişime geçin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#passwordForm').on('submit', async function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.text();
        
        btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-sm">progress_activity</span>');
        
        try {
            const formData = new FormData(this);
            const response = await fetch(form.attr('action'), {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.status === 'success') {
                showToast(result.message, 'success');
                form[0].reset();
            } else {
                showToast(result.message, 'error');
            }
        } catch (err) {
            showToast('Bir hata oluştu.', 'error');
            console.error(err);
        } finally {
            btn.prop('disabled', false).text(originalText);
        }
    });
</script>
