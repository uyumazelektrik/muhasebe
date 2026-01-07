<?php
// views/entities/edit.php
$pageTitle = "Cari Düzenle";
include __DIR__ . '/../../views/layout/header.php';

require_once __DIR__ . '/../../src/Models/EntityModel.php';
$entityModel = new EntityModel($pdo);

$entityId = $_GET['id'] ?? 0;
$entity = $entityModel->find($entityId);

if (!$entity) {
    echo "<div class='container mx-auto px-4 py-8'><div class='bg-red-100 text-red-700 p-4 rounded-lg'>Cari bulunamadı.</div></div>";
    include __DIR__ . '/../../views/layout/footer.php';
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">edit</span>
                Cari Düzenle
            </h2>
            <a href="<?php echo public_url('entities'); ?>" 
               class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                <span class="material-symbols-outlined">close</span>
            </a>
        </div>

        <form action="<?php echo public_url('entity/save'); ?>" method="POST" class="space-y-6">
            <input type="hidden" name="id" value="<?php echo $entity['id']; ?>">
            
            <!-- Cari Tipi -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Cari Tipi <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="supplier" class="w-4 h-4 text-primary focus:ring-primary" 
                               <?php echo $entity['type'] === 'supplier' ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tedarikçi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="customer" class="w-4 h-4 text-primary focus:ring-primary"
                               <?php echo $entity['type'] === 'customer' ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Müşteri</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="both" class="w-4 h-4 text-primary focus:ring-primary"
                               <?php echo $entity['type'] === 'both' ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Her İkisi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="staff" class="w-4 h-4 text-primary focus:ring-primary"
                               <?php echo $entity['type'] === 'staff' ? 'checked' : ''; ?>>
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
                       value="<?php echo htmlspecialchars($entity['name']); ?>"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
            </div>

            <!-- VKN/TCKN -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    VKN / TCKN
                </label>
                <input type="text" name="tax_id" maxlength="11"
                       value="<?php echo htmlspecialchars($entity['tax_id'] ?? ''); ?>"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary font-mono">
            </div>

            <!-- Telefon -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Telefon
                </label>
                <input type="tel" name="phone"
                       value="<?php echo htmlspecialchars($entity['phone'] ?? ''); ?>"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
            </div>

            <!-- E-posta -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    E-posta
                </label>
                <input type="email" name="email"
                       value="<?php echo htmlspecialchars($entity['email'] ?? ''); ?>"
                       class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
            </div>

            <!-- Adres -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Adres
                </label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($entity['address'] ?? ''); ?></textarea>
            </div>

            <!-- Mevcut Bakiye (Sadece Gösterim) -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Mevcut Bakiye (Değiştirilemez)
                </label>
                <div class="text-2xl font-bold font-mono <?php echo $entity['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo number_format($entity['balance'], 2); ?> ₺
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    Bakiye değişikliği için cari ekstresinden işlem yapın.
                </p>
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
                    Güncelle
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
