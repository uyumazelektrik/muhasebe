<!DOCTYPE html>
<html class="dark overflow-x-hidden" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $pageTitle ?? 'Personel Takip Sistemi'; ?></title>
    <script>
        // Suppress Tailwind production warning (it's built into the script we downloaded)
        // This MUST be before the tailwind.js script loads
        const originalWarn = console.warn;
        console.warn = (...args) => {
            const msg = args.map(a => String(a)).join(' ');
            if (msg.includes('cdn.tailwindcss.com') || msg.includes('tailwind.js') || msg.includes('production')) return;
            originalWarn.apply(console, args);
        };
    </script>
    <link href="<?php echo base_url('assets/vendor/css/fonts.css'); ?>" rel="stylesheet"/>
    <script src="<?php echo base_url('assets/vendor/js/tailwind.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/js/jquery.min.js'); ?>"></script>
    <link href="<?php echo base_url('assets/vendor/css/select2.min.css'); ?>" rel="stylesheet" />
    <script src="<?php echo base_url('assets/vendor/js/select2.min.js'); ?>"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "sidebar-dark": "#111318",
                        "card-dark": "#151a25",
                        "input-dark": "#282e39",
                        "surface-dark": "#1c1f27",
                        "border-dark": "#282e39",
                        "text-secondary": "#9da6b9"
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #282e39; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #3a4250; }

        /* Select2 Dark Mode Fix */
        .select2-container--default .select2-selection--single {
            background-color: #282e39 !important;
            border: 1px solid #3f4859 !important;
            border-radius: 12px !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
            padding-left: 16px !important;
            font-size: 14px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9da6b9 !important;
        }
        .select2-dropdown {
            background-color: #151a25 !important;
            border: 1px solid #3f4859 !important;
            border-radius: 12px !important;
            color: #f1f5f9 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
            margin-top: 4px !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 8px !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #282e39 !important;
            border: 1px solid #3f4859 !important;
            color: #f1f5f9 !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #135bec !important;
            color: white !important;
        }
        .select2-results__option {
            padding: 10px 16px !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #1c2433 !important;
        }
        .select2-container--default .select2-results__option--selected {
             background-color: #1c2433 !important;
        }
        .select2-container {
            width: 100% !important;
        }
        /* Select2 Hidden Checkbox fix */
        .select2-container--default .select2-selection--multiple {
            background-color: #282e39 !important;
            border: 1px solid #3f4859 !important;
            border-radius: 12px !important;
        }

        /* Toast Styles */
        #toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
        }
        .toast {
            pointer-events: auto;
            min-width: 300px;
            max-width: 450px;
            background: rgba(21, 26, 37, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateX(120%);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast.success { border-left: 4px solid #10b981; }
        .toast.error { border-left: 4px solid #ef4444; }
        .toast.info { border-left: 4px solid #3b82f6; }
        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toast.success .toast-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .toast.error .toast-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .toast.info .toast-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

        /* Confirm Modal Styles */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .confirm-overlay.show { opacity: 1; }
        .confirm-content {
            background: #151a25;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            padding: 1.5rem;
            width: 100%;
            max-width: 400px;
            transform: scale(0.9);
            transition: transform 0.3s;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .confirm-overlay.show .confirm-content { transform: scale(1); }
        .confirm-btns {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .confirm-btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .confirm-btn-cancel {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9da6b9;
        }
        .confirm-btn-cancel:hover { background: rgba(255, 255, 255, 0.05); color: white; }
        .confirm-btn-danger {
            background: #ef4444;
            color: white;
            border: none;
        }
        .confirm-btn-danger:hover { background: #dc2626; box-shadow: 0 0 15px rgba(239, 68, 68, 0.3); }
        .confirm-btn-primary {
            background: #135bec;
            color: white;
            border: none;
        }
        .confirm-btn-primary:hover { background: #114ecb; box-shadow: 0 0 15px rgba(19, 91, 236, 0.3); }

        /* Global Image Modal Styles */
        .img-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            z-index: 11000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            opacity: 0;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .img-modal-overlay.show { opacity: 1; display: flex; }
        .img-modal-content {
            max-width: 90%;
            max-height: 90vh;
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .img-modal-overlay.show .img-modal-content { transform: scale(1); }
        .img-modal-img {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        .img-modal-close {
            position: absolute;
            top: -1.5rem;
            right: -1.5rem;
            width: 3rem;
            height: 3rem;
            background: #ef4444;
            color: white;
            border-radius: full;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);
            border: 4px solid #151a25;
            z-index: 10;
            transition: all 0.2s;
        }
        .img-modal-close:hover { transform: scale(1.1) rotate(90deg); background: #dc2626; }
        .img-modal-title {
            position: absolute;
            bottom: -3.5rem;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            font-weight: 800;
            font-size: 1.125rem;
            text-shadow: 0 4px 6px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen flex font-display overflow-x-hidden w-full relative">

<!-- Global Image Preview Modal -->
<div id="global-img-modal" class="img-modal-overlay" onclick="closeImageModal()">
    <div class="img-modal-content" onclick="event.stopPropagation()">
        <button class="img-modal-close rounded-full" onclick="closeImageModal()">
            <span class="material-symbols-outlined">close</span>
        </button>
        <img id="img-modal-target" src="" alt="Önizleme" class="img-modal-img">
        <div id="img-modal-title" class="img-modal-title"></div>
    </div>
</div>

<script>
    function openImageModal(src, title = 'Resim Önizleme') {
        const modal = document.getElementById('global-img-modal');
        const img = document.getElementById('img-modal-target');
        const titleEl = document.getElementById('img-modal-title');
        
        img.src = src;
        titleEl.textContent = title;
        modal.style.display = 'flex';
        // Force reflow
        modal.offsetHeight;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('global-img-modal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }
</script>
<!-- Progress/Overlay for Mobile Sidebar -->
<div id="sidebar-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<aside id="main-sidebar" class="w-64 bg-sidebar-dark border-r border-slate-200 dark:border-slate-800 flex flex-col fixed inset-y-0 left-0 z-50 -translate-x-full md:translate-x-0 shrink-0 transition-transform duration-300 h-[100dvh]">
    <div class="p-6 pb-2 flex items-center justify-between">
        <div class="flex flex-col">
            <a href="<?php echo public_url('dashboard'); ?>" class="text-white text-xl font-bold leading-normal tracking-tight hover:text-primary transition-colors">Uyumaz Elektrik</a>
            <p class="text-[#9da6b9] text-xs font-normal leading-normal mt-1"><?php echo current_role() === 'admin' ? 'Yönetici Paneli' : 'Personel Paneli'; ?></p>
        </div>
        <button onclick="toggleMobileSidebar()" class="md:hidden text-white p-1 hover:bg-white/10 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-4 py-4 pb-24 md:pb-4 flex flex-col gap-1">
        
        <!-- Ana Menü -->
        <div class="px-3 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Ana Menü</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('dashboard'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-white transition-colors">dashboard</span>
            <span class="text-sm font-medium leading-normal">Panel</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-emerald-400 hover:bg-emerald-500/10 transition-colors group" href="<?php echo public_url('sales/pos'); ?>">
            <span class="material-symbols-outlined text-[24px]">point_of_sale</span>
            <span class="text-sm font-black leading-normal italic">Hızlı Satış (POS)</span>
        </a>

        <!-- Personel Takip Grubu -->
        <?php if (current_role() === 'admin'): ?>
        <div class="px-3 mt-4 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Personel Takip</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('personnel'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-400 transition-colors">badge</span>
            <span class="text-sm font-medium leading-normal">Personel İşlemleri</span>
        </a>
        <?php endif; ?>

        <!-- Operasyon Grubu -->
        <div class="px-3 mt-4 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Operasyon</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('inventory-check'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-amber-500 transition-colors">barcode_scanner</span>
            <span class="text-sm font-medium leading-normal">Fiyat Sorgula</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('inventory'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-amber-500 transition-colors">inventory_2</span>
            <span class="text-sm font-medium leading-normal">Stok Takibi</span>
        </a>
        <?php if (current_role() === 'admin'): ?>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('invoices'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-500 transition-colors">description</span>
            <span class="text-sm font-medium leading-normal">Fatura/Fiş Yönetimi</span>
        </a>
        <?php endif; ?>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('jobs'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-500 transition-colors">engineering</span>
            <span class="text-sm font-medium leading-normal">İş Takibi</span>
        </a>

        <?php if (current_role() === 'admin'): ?>
        <div class="px-3 mt-4 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Finans</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('finance/wallets'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-400 transition-colors">payments</span>
            <span class="text-sm font-medium leading-normal">Kasa & Banka</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('finance/expense-categories'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-emerald-400 transition-colors">account_tree</span>
            <span class="text-sm font-medium leading-normal">Gider Kategorileri</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('entities'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-purple-500 transition-colors">account_balance_wallet</span>
            <span class="text-sm font-medium leading-normal">Cari Hesaplar</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('finance-reports'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-emerald-500 transition-colors">account_balance</span>
            <span class="text-sm font-medium leading-normal">Finansal Raporlar</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('reports'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-white transition-colors">analytics</span>
            <span class="text-sm font-medium leading-normal">Verim Analizi</span>
        </a>
        <?php endif; ?>

        <div class="my-4 border-t border-slate-800"></div>
        
        <?php if (current_role() === 'admin'): ?>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('settings'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-white transition-colors">settings</span>
            <span class="text-sm font-medium leading-normal">Ayarlar</span>
        </a>
        <?php endif; ?>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('profile'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-400 transition-colors">account_circle</span>
            <span class="text-sm font-bold leading-normal">Profilim & Maaş</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-400 hover:bg-red-500/10 transition-colors group mt-auto" href="<?php echo public_url('logout'); ?>">
            <span class="material-symbols-outlined text-[24px]">logout</span>
            <span class="text-sm font-medium leading-normal">Güvenli Çıkış</span>
        </a>
    </nav>
    <div class="p-4 border-t border-slate-800">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white font-black text-lg bg-blue-600">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'U', 0, 1, 'UTF-8'); ?>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Misafir'); ?></span>
                <span class="text-[10px] font-bold text-[#9da6b9] uppercase tracking-wider"><?php echo current_role() === 'admin' ? 'Yönetici' : 'Personel'; ?></span>
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>

<!-- Main Wrapper Starts -->
<div id="main-content-wrapper" class="flex-1 w-full min-w-0 flex flex-col min-h-screen relative overflow-x-hidden md:ml-64">
    <!-- Top Progress Bar (Visible during SPA navigation) -->
    <div id="spa-progress" class="fixed top-0 left-0 h-1 bg-primary z-[100] transition-all duration-300 opacity-0" style="width: 0%; left: 16rem;"></div>
    <style>
        #main-content-wrapper.loading { opacity: 0.6; pointer-events: none; transition: opacity 0.2s; }
        @media (max-width: 768px) { #spa-progress { left: 0 !important; } }
    </style>
    
    <!-- Mobile Navigation Bar -->
    <header class="md:hidden flex items-center justify-between px-4 py-4 bg-sidebar-dark text-white border-b border-white/10 shrink-0 sticky top-0 z-30">
        <div class="flex flex-col">
            <a href="<?php echo public_url('dashboard'); ?>" class="text-lg font-bold hover:text-primary transition-colors">Uyumaz Elektrik</a>
        </div>
        <button onclick="toggleMobileSidebar()" class="p-2 -mr-2 text-[#9da6b9] hover:text-white transition-colors" aria-label="Menü">
            <span class="material-symbols-outlined text-3xl">menu</span>
        </button>
    </header>
    <script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: 'check_circle',
            error: 'error',
            info: 'info'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">
                <span class="material-symbols-outlined">${icons[type]}</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-white">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                <p class="text-xs text-slate-400 mt-1">${message}</p>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Force reflow
        toast.offsetHeight;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, 4000);
    }

    function showConfirm({ title, message, confirmText = 'Evet, Sil', cancelText = 'İptal', type = 'danger' }) {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'confirm-overlay font-display';
            
            overlay.innerHTML = `
                <div class="confirm-content animate-in zoom-in duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${type === 'danger' ? 'bg-red-500/10 text-red-500' : 'bg-blue-500/10 text-blue-500'}">
                            <span class="material-symbols-outlined text-2xl">${type === 'danger' ? 'delete' : 'info'}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">${title}</h3>
                            <p class="text-xs text-slate-400 mt-1">${message}</p>
                        </div>
                    </div>
                    <div class="confirm-btns">
                        <button class="confirm-btn confirm-btn-cancel">${cancelText}</button>
                        <button class="confirm-btn confirm-btn-${type}">${confirmText}</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            requestAnimationFrame(() => overlay.classList.add('show'));

            const close = (result) => {
                overlay.classList.remove('show');
                setTimeout(() => {
                    overlay.remove();
                    resolve(result);
                }, 300);
            };

            overlay.querySelector('.confirm-btn-cancel').onclick = () => close(false);
            overlay.querySelector(`.confirm-btn-${type}`).onclick = () => close(true);
            overlay.onclick = (e) => { if(e.target === overlay) close(false); };
        });
    }
</script>

<!-- SPA Navigation Script -->
<script>
// SPA Navigation System
document.addEventListener('click', e => {
    const link = e.target.closest('a');
    if (!link) return;
    
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download')) return;
    
    const url = new URL(href, window.location.origin);
    if (url.origin !== window.location.origin) return;
    if (href.includes('logout') || href.includes('/uploads/') || href.includes('sales/pos')) return;

    e.preventDefault();
    navigateTo(href);
});

// Intercept Form Submits
document.addEventListener('submit', e => {
    const form = e.target;
    if (form.method.toLowerCase() !== 'get' || form.target === '_blank' || form.classList.contains('no-spa')) return;
    
    const url = new URL(form.action || window.location.href);
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    e.preventDefault();
    navigateTo(url.pathname + '?' + params.toString());
});

// Initialize previous URL on full page load
if (!sessionStorage.getItem('app_is_spa_nav')) {
    if (document.referrer && document.referrer.includes(window.location.host)) {
        sessionStorage.setItem('app_prev_url', document.referrer);
    } else {
        sessionStorage.removeItem('app_prev_url');
    }
}
sessionStorage.removeItem('app_is_spa_nav');

async function navigateTo(url) {
    if (url === window.location.href) return;
    
    // Save current as previous
    sessionStorage.setItem('app_prev_url', window.location.href);
    sessionStorage.setItem('app_is_spa_nav', 'true');
    
    history.pushState(null, '', url);
    await loadPage(url);
}

window.addEventListener('popstate', () => loadPage(window.location.href));

async function loadPage(url) {
    const wrapper = document.getElementById('main-content-wrapper');
    const progressBar = document.getElementById('spa-progress');
    
    wrapper.classList.add('loading');
    progressBar.style.opacity = '1';
    progressBar.style.width = '30%';
    
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('Yükleme başarısız');
        
        progressBar.style.width = '70%';
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const newMain = doc.getElementById('main-content-wrapper');
        if (!newMain) {
            window.location.href = url;
            return;
        }

        document.title = doc.title;
        wrapper.innerHTML = newMain.innerHTML;
        
        // Execute new scripts
        executeScripts(wrapper);
        updateSidebarActive(url);
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (typeof toggleMobileSidebar === 'function' && !document.getElementById('main-sidebar').classList.contains('-translate-x-full')) {
            toggleMobileSidebar();
        }
        
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => progressBar.style.width = '0%', 300);
        }, 200);

    } catch (err) {
        console.error('SPA Hatası:', err);
        // Prevent infinite reload loops by not forcing window.location.reload()
        if (typeof showToast === 'function') {
            showToast('Sayfa yüklenirken bir hata oluştu: ' + err.message, 'error');
        }
    } finally {
        wrapper.classList.remove('loading');
    }
}

function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        
        // Use .text instead of .innerHTML for better source preservation
        const code = oldScript.text || oldScript.textContent || oldScript.innerHTML;
        if (code.trim()) {
            newScript.textContent = code;
        }
        
        // Remove the old script tag from the DOM first
        const parent = oldScript.parentNode;
        parent.removeChild(oldScript);
        // Append the new one to the same parent to execute it
        parent.appendChild(newScript);
    });
}

function updateSidebarActive(currentUrl) {
    const navLinks = document.querySelectorAll('#main-sidebar nav a');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        // Link mevcut URL ile eşleşiyor mu (basit kontrol)
        if (currentUrl.endsWith(href) || (href !== '<?php echo site_url(); ?>' && currentUrl.includes(href))) {
            // Aktif stilini ekle (varsa)
            link.classList.add('bg-white/5', 'text-white');
            link.classList.remove('text-[#9da6b9]');
        } else {
            link.classList.remove('bg-white/5', 'text-white');
            link.classList.add('text-[#9da6b9]');
        }
    });
}
</script>

<!-- Toast Container -->
<div id="toast-container"></div>
