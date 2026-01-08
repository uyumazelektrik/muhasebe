<!DOCTYPE html>
<html class="dark overflow-x-hidden" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $pageTitle ?? 'Personel Takip Sistemi'; ?></title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen flex font-display overflow-x-hidden w-full relative">

<!-- Progress/Overlay for Mobile Sidebar -->
<div id="sidebar-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<aside id="main-sidebar" class="w-64 bg-sidebar-dark border-r border-slate-200 dark:border-slate-800 flex-col fixed inset-y-0 left-0 z-50 -translate-x-full md:translate-x-0 md:static md:flex shrink-0 transition-transform duration-300 h-full">
    <div class="p-6 pb-2 flex items-center justify-between">
        <div class="flex flex-col">
            <h1 class="text-white text-xl font-bold leading-normal tracking-tight">PersonelTakip</h1>
            <p class="text-[#9da6b9] text-xs font-normal leading-normal mt-1"><?php echo current_role() === 'admin' ? 'Yönetici Paneli' : 'Personel Paneli'; ?></p>
        </div>
        <button onclick="toggleMobileSidebar()" class="md:hidden text-white p-1 hover:bg-white/10 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-4 py-4 flex flex-col gap-1">
        
        <!-- Ana Menü -->
        <div class="px-3 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Ana Menü</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('dashboard'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-white transition-colors">dashboard</span>
            <span class="text-sm font-medium leading-normal">Panel</span>
        </a>

        <!-- Personel Takip Grubu -->
        <div class="px-3 mt-4 mb-2">
            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Personel Takip</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('attendance-logs'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-blue-400 transition-colors">timer</span>
            <span class="text-sm font-medium leading-normal">Giriş-Çıkış Kayıtları</span>
        </a>
        <?php if (current_role() === 'admin'): ?>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('payroll'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-green-500 transition-colors">payments</span>
            <span class="text-sm font-medium leading-normal">Maaş & Hakediş</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('users'); ?>">
            <span class="material-symbols-outlined text-[24px] fill-1 group-hover:text-white transition-colors">group</span>
            <span class="text-sm font-medium leading-normal">Personel Yönetimi</span>
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
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#9da6b9] hover:bg-white/5 hover:text-white transition-colors group" href="<?php echo public_url('invoice/upload'); ?>">
            <span class="material-symbols-outlined text-[24px] group-hover:text-amber-500 transition-colors">receipt_long</span>
            <span class="text-sm font-medium leading-normal">Fatura İşle</span>
        </a>
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
<div class="flex-1 w-full min-w-0 flex flex-col min-h-screen relative overflow-x-hidden">
    
    <!-- Mobile Navigation Bar -->
    <header class="md:hidden flex items-center justify-between px-4 py-4 bg-sidebar-dark text-white border-b border-white/10 shrink-0 sticky top-0 z-30">
        <div class="flex flex-col">
            <h1 class="text-lg font-bold">PersonelTakip</h1>
        </div>
        <button onclick="toggleMobileSidebar()" class="p-2 -mr-2 text-[#9da6b9] hover:text-white transition-colors" aria-label="Menü">
            <span class="material-symbols-outlined text-3xl">menu</span>
        </button>
    </header>
