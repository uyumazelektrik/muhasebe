<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex items-center gap-4">
            <a href="<?php echo site_url('jobs'); ?>" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Yeni İş Formu</h2>
        </div>
    </header>

    <main class="flex-1 p-4 sm:p-8 overflow-auto">
        <form id="createJobForm" class="max-w-2xl mx-auto space-y-6">
            <div class="bg-white dark:bg-card-dark rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-8 shadow-xl">
                <div class="space-y-6">
                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Müşteri Seçimi</label>
                        <div class="relative searchable-select z-[60]">
                            <div class="relative">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm pointer-events-none">expand_more</span>
                                <input type="text" class="search-input w-full px-5 py-4 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-base font-bold outline-none focus:border-primary transition-all text-slate-900 dark:text-white" 
                                       placeholder="Müşteri Ara..." autocomplete="off" required>
                                <input type="hidden" name="customer_id" id="customer_id" class="hidden-input">
                            </div>
                            <div class="options-list absolute w-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-h-64 overflow-y-auto hidden z-50">
                                <?php foreach($customers as $customer): ?>
                                    <div class="option-item p-4 hover:bg-primary/10 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $customer['id']; ?>">
                                        <span><?php echo htmlspecialchars($customer['name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Job Date -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">İş Tarihi</label>
                        <input type="date" name="job_date" value="<?php echo date('Y-m-d'); ?>" required
                               class="w-full px-5 py-4 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-base font-bold outline-none focus:border-primary transition-all">
                    </div>

                    <!-- Job Description -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Yapılacak İş / Arıza Tanımı</label>
                        <textarea name="description" rows="4" required placeholder="İşin detaylarını buraya yazın..."
                                  class="w-full px-5 py-4 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-2xl text-base font-medium outline-none focus:border-primary transition-all"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="<?php echo site_url('jobs'); ?>" class="flex-1 py-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-[1.5rem] font-black text-sm text-center transition-all">
                    İptal
                </a>
                <button type="submit" class="flex-[2] py-4 bg-primary hover:bg-primary-dark text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-primary/30 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    İş Formunu Oluştur ve Devam Et
                </button>
            </div>
        </form>
    </main>
</div>

<script>
// Searchable Select Logic
function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        });

        input.addEventListener('keyup', (e) => {
            const val = e.target.value.toLocaleLowerCase("tr-TR");
            list.classList.remove('hidden');
            options.forEach(opt => {
                const text = opt.textContent.toLocaleLowerCase("tr-TR");
                if(text.includes(val)) {
                    opt.classList.remove('hidden');
                } else {
                    opt.classList.add('hidden');
                }
            });
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
                
                $(hiddenInput).trigger('change');
            });
        });

        document.addEventListener('click', (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        });
    });
}

setupSearchableSelects();

document.getElementById('createJobForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    try {
        const res = await fetch('<?php echo site_url("jobs/api_create"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => {
                location.href = '<?php echo site_url("jobs/detail/"); ?>' + data.job_id;
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        showToast('Bir hata oluştu.', 'error');
    }
});
</script>
