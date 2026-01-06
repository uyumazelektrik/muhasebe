<?php include __DIR__ . '/layout/header.php'; ?>
<main class="flex-1 flex flex-col items-center justify-center p-6 text-center">
    <h1 class="text-6xl font-black text-slate-900 dark:text-white mb-4">404</h1>
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Sayfa Bulunamadı</h2>
    <p class="text-text-secondary mb-8">Aradığınız sayfa mevcut değil veya taşınmış olabilir.</p>
    <a href="<?php echo public_url(); ?>" class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-600 transition-colors">
        Ana Sayfaya Dön
    </a>
</main>
<?php include __DIR__ . '/layout/footer.php'; ?>
