<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
function get_status_label($status) {
    return match($status) {
        'Pending' => 'Beklemede',
        'In Progress' => 'Devam Ediyor',
        'Completed' => 'Tamamlandı',
        'Cancelled' => 'İptal',
        default => $status
    };
}
?>

<div class="flex flex-col flex-1 w-full min-w-0 bg-background-light dark:bg-background-dark h-screen overflow-hidden">
    <header class="w-full bg-white dark:bg-card-dark border-b border-slate-200 dark:border-slate-800/50 pt-4 pb-3 px-4 sm:px-6 shrink-0 z-20">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl sm:text-2xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">İş Takibi</h2>
                <div class="hidden sm:block text-slate-500 dark:text-slate-400 text-xs font-medium">Süreçleri ve malzeme takibini yönetin</div>
            </div>
            <a href="<?php echo site_url('jobs/create'); ?>" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span class="hidden sm:inline">Yeni İş</span>
            </a>
        </div>
    </header>

    <!-- Mobile Navigation (Status Tabs) -->
    <div class="sm:hidden flex bg-white dark:bg-card-dark border-b border-slate-200 dark:border-slate-800 shrink-0">
        <button onclick="switchTab('Pending')" id="tab-Pending" class="flex-1 py-3 text-xs font-black uppercase text-slate-400 border-b-2 border-transparent transition-all active-tab" data-status="Pending">Beklemede</button>
        <button onclick="switchTab('In Progress')" id="tab-In-Progress" class="flex-1 py-3 text-xs font-black uppercase text-slate-400 border-b-2 border-transparent transition-all" data-status="In Progress">Devam</button>
        <button onclick="switchTab('Completed')" id="tab-Completed" class="flex-1 py-3 text-xs font-black uppercase text-slate-400 border-b-2 border-transparent transition-all" data-status="Completed">Bitti</button>
        <button onclick="switchTab('Cancelled')" id="tab-Cancelled" class="flex-1 py-3 text-xs font-black uppercase text-slate-400 border-b-2 border-transparent transition-all" data-status="Cancelled">İptal</button>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-0 sm:p-6 custom-scrollbar">
        <!-- Kanban / List Container -->
        <div class="flex h-full min-w-full sm:min-w-0 sm:gap-6">
            
            <!-- Column 1: Pending -->
            <div data-status="Pending" class="kanban-column flex-col shrink-0 w-full sm:w-[320px] lg:w-[380px] h-full flex">
                <div class="hidden sm:flex items-center justify-between mb-4 px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></span>
                        <h3 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Beklemede</h3>
                    </div>
                    <span class="bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-lg text-[10px] font-black text-slate-500"><?php echo count(array_filter($jobs, fn($j) => $j['status'] === 'Pending')); ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto px-4 sm:px-1 space-y-4 pb-20 custom-scrollbar">
                    <?php foreach(array_filter($jobs, fn($j) => $j['status'] === 'Pending') as $job): ?>
                        <?php $this->load->view('jobs/card_item', ['job' => $job]); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 2: In Progress -->
            <div data-status="In Progress" class="kanban-column hidden sm:flex flex-col shrink-0 w-full sm:w-[320px] lg:w-[380px] h-full">
                <div class="hidden sm:flex items-center justify-between mb-4 px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary shadow-[0_0_8px_rgba(19,91,236,0.5)]"></span>
                        <h3 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Devam Ediyor</h3>
                    </div>
                    <span class="bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-lg text-[10px] font-black text-slate-500"><?php echo count(array_filter($jobs, fn($j) => $j['status'] === 'In Progress')); ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto px-4 sm:px-1 space-y-4 pb-20 custom-scrollbar">
                    <?php foreach(array_filter($jobs, fn($j) => $j['status'] === 'In Progress') as $job): ?>
                        <?php $this->load->view('jobs/card_item', ['job' => $job]); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 3: Completed -->
            <div data-status="Completed" class="kanban-column hidden sm:flex flex-col shrink-0 w-full sm:w-[320px] lg:w-[380px] h-full">
                <div class="hidden sm:flex items-center justify-between mb-4 px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <h3 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Tamamlandı</h3>
                    </div>
                    <span class="bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-lg text-[10px] font-black text-slate-500"><?php echo count(array_filter($jobs, fn($j) => $j['status'] === 'Completed')); ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto px-4 sm:px-1 space-y-4 pb-20 custom-scrollbar">
                    <?php foreach(array_filter($jobs, fn($j) => $j['status'] === 'Completed') as $job): ?>
                        <?php $this->load->view('jobs/card_item', ['job' => $job]); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 4: Cancelled -->
            <div data-status="Cancelled" class="kanban-column hidden sm:flex flex-col shrink-0 w-full sm:w-[320px] lg:w-[380px] h-full opacity-60">
                <div class="hidden sm:flex items-center justify-between mb-4 px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <h3 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">İptal Edilenler</h3>
                    </div>
                    <span class="bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-lg text-[10px] font-black text-slate-500"><?php echo count(array_filter($jobs, fn($j) => $j['status'] === 'Cancelled')); ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto px-4 sm:px-1 space-y-4 pb-20 custom-scrollbar">
                    <?php foreach(array_filter($jobs, fn($j) => $j['status'] === 'Cancelled') as $job): ?>
                        <?php $this->load->view('jobs/card_item', ['job' => $job, 'dimmed' => true]); ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<style>
/* Custom Scrollbar for better desktop Kanban feel */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.2); border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.4); }

/* Switch Tabs on Mobile */
.active-tab {
    color: #135bec !important;
    border-bottom-color: #135bec !important;
}
</style>

<script>
function switchTab(status) {
    // Buttons
    document.querySelectorAll('[data-status]').forEach(el => el.classList.remove('active-tab'));
    const tabId = status === 'In Progress' ? 'tab-In-Progress' : 'tab-' + status;
    document.getElementById(tabId)?.classList.add('active-tab');

    // Columns
    document.querySelectorAll('.kanban-column').forEach(el => {
        if (el.dataset.status === status) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        } else {
            el.classList.remove('flex');
            el.classList.add('hidden');
        }
    });
}

function updateStatus(id, status) {
    const title = status === 'In Progress' ? 'İşi Başlat' : (status === 'Completed' ? 'İşi Bitir' : 'İşi İptal Et');
    const message = 'İş durumu güncellenecektir. Emin misiniz?';
    
    showConfirm({ 
        title: title, 
        message: message, 
        confirmText: 'Evet, Güncelle', 
        type: status === 'Cancelled' ? 'danger' : 'primary' 
    }).then(async (confirmed) => {
        if (!confirmed) return;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);

        const res = await fetch('<?php echo site_url("jobs/api_update_status"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function deleteJob(id) {
    showConfirm({ 
        title: 'İşi Tamamen Sil', 
        message: 'Bu işlem geri alınamaz! İptal edilen iş kaydı ve bağlı malzemeler kalıcı olarak silinecek.', 
        confirmText: 'Kalıcı Olarak Sil', 
        type: 'danger' 
    }).then(async (confirmed) => {
        if (!confirmed) return;

        const formData = new FormData();
        formData.append('id', id);

        const res = await fetch('<?php echo site_url("jobs/api_delete_job"); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message, 'error');
        }
    });
}
</script>
