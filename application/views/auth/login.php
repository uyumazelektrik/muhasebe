<!DOCTYPE html>
<html class="dark" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Giriş Yap - Personel Takip</title>
    <link href="<?php echo base_url('assets/vendor/css/fonts.css'); ?>" rel="stylesheet"/>
    <script src="<?php echo base_url('assets/vendor/js/tailwind.js'); ?>"></script>
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-card-dark rounded-2xl shadow-2xl p-8 border border-slate-200 dark:border-slate-800">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Hoş Geldiniz</h1>
            <p class="text-text-secondary mt-2 text-sm">Devam etmek için lütfen giriş yapın</p>
        </div>

        <?php if($this->session->flashdata('error')): ?>
        <div class="bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl p-4 mb-6 text-sm font-bold flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <?php echo $this->session->flashdata('error'); ?>
        </div>
        <?php endif; ?>

        <?php echo form_open('auth/login', ['class' => 'space-y-5']); ?>
            <div>
                <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Kullanıcı Adı</label>
                <input type="text" name="username" required class="w-full bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors hover:border-slate-300 dark:hover:border-slate-600" placeholder="Kullanıcı adınızı girin">
            </div>

            <div>
                <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Şifre</label>
                <input type="password" name="password" required class="w-full bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors hover:border-slate-300 dark:hover:border-slate-600" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-3.5 rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-primary/20">
                Giriş Yap
            </button>
        <?php echo form_close(); ?>

        <div class="mt-8 text-center">
            <p class="text-xs text-text-secondary">
                Personel Takip Sistemi &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>
