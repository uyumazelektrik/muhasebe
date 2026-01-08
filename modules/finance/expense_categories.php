<?php
// modules/finance/expense_categories.php
$pageTitle = "Gider Kategorileri Yönetimi";
require_once __DIR__ . '/../../config/db.php';

// Kategorileri getir
$categories = $pdo->query("SELECT * FROM inv_expense_categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                <span class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-500 material-symbols-outlined text-3xl">account_tree</span>
                Gider Kategorileri
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1 ml-1 font-medium">İşletme giderlerini gruplandırmak ve analiz etmek için kullanılır.</p>
        </div>
        <button onclick="openModal()" 
                class="px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 shadow-lg shadow-primary/20 transition-all font-bold">
            <span class="material-symbols-outlined">add</span>
            Yeni Kategori Ekle
        </button>
    </div>

    <!-- Kategoriler Tablosu -->
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-800">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 dark:bg-surface-dark border-b dark:border-gray-800 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                    <th class="px-6 py-4">Kategori Adı</th>
                    <th class="px-6 py-4">Oluşturulma Tarihi</th>
                    <th class="px-6 py-4 text-center">Aksiyon</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($cat['name']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo date('d.m.Y H:i', strtotime($cat['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)" 
                                    class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors" title="Düzenle">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" 
                                    class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors" title="Sil">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($categories)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-gray-500 italic">Henüz bir kategori tanımlanmamış.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Kategori Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md transform transition-all scale-100 overflow-hidden">
        <div class="p-6 border-b dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-surface-dark">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span id="modalIcon" class="material-symbols-outlined text-primary">add_circle</span>
                <span id="modalTitle">Yeni Gider Kategorisi</span>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="categoryForm" onsubmit="saveCategory(event)" class="p-6 space-y-4">
            <input type="hidden" id="category_id" value="">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Kategori Adı</label>
                <input type="text" id="category_name" required 
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-input-dark border border-gray-200 dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-gray-900 dark:text-white font-medium transition-all"
                       placeholder="Örn: Elektrik Giderleri">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 font-bold transition-all">
                    Vazgeç
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-primary/20 font-bold transition-all transform active:scale-95">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('modalTitle').innerText = 'Yeni Gider Kategorisi';
    document.getElementById('modalIcon').innerText = 'add_circle';
    document.getElementById('categoryModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('category_name').focus(), 100);
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

function editCategory(cat) {
    document.getElementById('category_id').value = cat.id;
    document.getElementById('category_name').value = cat.name;
    document.getElementById('modalTitle').innerText = 'Kategoriyi Düzenle';
    document.getElementById('modalIcon').innerText = 'edit';
    document.getElementById('categoryModal').classList.remove('hidden');
}

async function saveCategory(e) {
    e.preventDefault();
    const id = document.getElementById('category_id').value;
    const name = document.getElementById('category_name').value;
    
    try {
        const response = await fetch('<?php echo public_url('api/save-expense-category'); ?>', {
            method: 'POST',
            body: JSON.stringify({ id: id, name: name })
        });
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}

async function deleteCategory(id) {
    if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) return;
    
    try {
        const response = await fetch('<?php echo public_url('api/delete-expense-category'); ?>', {
            method: 'POST',
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Hata oluştu: ' + e.message);
    }
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
