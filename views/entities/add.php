<?php
// views/entities/add.php
$pageTitle = "Yeni Cari Ekle";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_add</span>
                Yeni Cari Ekle
            </h2>
            <a href="<?php echo public_url('entities'); ?>" 
               class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                <span class="material-symbols-outlined">close</span>
            </a>
        </div>

        <?php if (isset($_GET['error']) && isset($_GET['message'])): ?>
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border-l-4 border-red-600 rounded-lg">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-red-600">error</span>
                <p class="text-sm text-red-700 dark:text-red-300"><?php echo htmlspecialchars($_GET['message']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <form action="<?php echo public_url('entity/save'); ?>" method="POST" class="space-y-6">
            <!-- Cari Tipi -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Cari Tipi <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="supplier" class="w-4 h-4 text-primary focus:ring-primary" checked>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tedarikçi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="customer" class="w-4 h-4 text-primary focus:ring-primary">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Müşteri</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="both" class="w-4 h-4 text-primary focus:ring-primary">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Her İkisi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="staff" class="w-4 h-4 text-primary focus:ring-primary">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Personel</span>
                    </label>
                </div>
            </div>

            <!-- Cari Adı -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Cari Adı <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                       placeholder="Örn: ABC Elektrik Ltd. Şti.">
            </div>

            <!-- VKN/TCKN -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    VKN / TCKN
                </label>
                <input type="text" name="tax_id" maxlength="11"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary font-mono"
                       placeholder="1234567890">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">10 haneli VKN veya 11 haneli TCKN</p>
            </div>

            <!-- Telefon -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Telefon
                </label>
                <input type="tel" name="phone"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                       placeholder="0555 123 45 67">
            </div>

            <!-- E-posta -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    E-posta
                </label>
                <input type="email" name="email"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                       placeholder="info@firma.com">
            </div>

            <!-- Adres -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Adres
                </label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                          placeholder="Tam adres..."></textarea>
            </div>

            <!-- Başlangıç Bakiyesi -->
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Başlangıç Bakiyesi (Opsiyonel)
                </label>
                <input type="number" step="0.01" name="initial_balance" value="0"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary font-mono">
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    <strong>Pozitif (+):</strong> Bize Borçlu &nbsp;&nbsp;
                    <strong>Negatif (-):</strong> Bizden Alacaklı
                </p>
            </div>

            <!-- Personel Bilgileri (Sadece tip 'staff' ise görünür) -->
            <div id="staff_fields" class="hidden space-y-6 border-t dark:border-border-dark pt-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    Sistem Giriş Bilgileri
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Kullanıcı Adı <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" id="username"
                               class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Şifre <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Yetki <span class="text-red-500">*</span>
                        </label>
                        <select name="role" class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                            <option value="personel">Personel</option>
                            <option value="admin">Yönetici</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Saatlik Ücret
                        </label>
                        <input type="number" step="0.01" name="hourly_rate" value="0.00"
                               class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary font-mono">
                    </div>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="flex justify-end gap-4 pt-6 border-t dark:border-border-dark">
                <a href="<?php echo public_url('entities'); ?>" 
                   class="px-6 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    İptal
                </a>
                <button type="submit" 
                        class="px-8 py-2 rounded-lg bg-primary text-white font-bold hover:bg-blue-700 shadow-md flex items-center gap-2 transition-all">
                    <span class="material-symbols-outlined">save</span>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const staffFields = document.getElementById('staff_fields');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        if (this.value === 'staff') {
            staffFields.classList.remove('hidden');
            usernameInput.setAttribute('required', 'required');
            passwordInput.setAttribute('required', 'required');
        } else {
            staffFields.classList.add('hidden');
            usernameInput.removeAttribute('required');
            passwordInput.removeAttribute('required');
        }
    });
});
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
