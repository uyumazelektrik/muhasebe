<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Kontrol Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="size-16 rounded-2xl bg-primary flex items-center justify-center text-white shadow-xl shadow-primary/20 bg-blue-600">
                    <span class="material-symbols-outlined text-4xl">engineering</span>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-black tracking-tight text-slate-900">Hesabınıza Giriş Yapın</h2>
            <p class="mt-2 text-center text-sm text-slate-600 italic">Personel ve Stok Takip Sistemi</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-8 py-10 shadow-2xl shadow-slate-200/50 rounded-3xl border border-slate-100">
                
                <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3 text-red-600 text-sm font-bold">
                    <span class="material-symbols-outlined">error</span>
                    <span>Hatalı kullanıcı adı veya şifre!</span>
                </div>
                <?php endif; ?>

                <form class="space-y-6" action="<?php echo public_url('api/login'); ?>" method="POST">
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-500 uppercase tracking-widest px-1 mb-2">Kullanıcı Adı</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">person</span>
                            </span>
                            <input id="username" name="username" type="text" autocomplete="username" required 
                                class="block w-full rounded-xl border-slate-200 pl-10 h-12 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 border transition-all"
                                placeholder="kullanıcı_adi">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-widest px-1 mb-2">Şifre</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="block w-full rounded-xl border-slate-200 pl-10 h-12 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 border transition-all"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="remember-me" class="ml-2 block text-sm text-slate-600 font-medium">Beni hatırla</label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="flex w-full justify-center rounded-xl bg-blue-600 px-3 py-3 text-sm font-bold text-white shadow-xl shadow-blue-500/30 hover:bg-blue-700 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                            Giriş Yap
                        </button>
                    </div>
                </form>
            </div>
            
            <p class="mt-8 text-center text-xs text-slate-400 font-medium">
                Sistem Yönetimi &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>
