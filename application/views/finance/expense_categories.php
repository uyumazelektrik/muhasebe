<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white">Gider Kategorileri</h2>
            <p class="text-xs font-medium text-gray-500 mt-1">Giderleri raporlamak için kullanılan kategoriler</p>
        </div>
        <button class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20" onclick="openAddModal()">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            Yeni Kategori
        </button>
    </div>

    <div class="bg-white dark:bg-card-dark rounded-3xl shadow-sm border border-gray-100 dark:border-border-dark overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-surface-dark/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <th class="px-6 py-4 text-left">Kategori İsmi</th>
                    <th class="px-6 py-4 text-center">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-border-dark">
                <?php if(empty($categories)): ?>
                    <tr>
                        <td colspan="2" class="px-6 py-12 text-center text-gray-400">Henüz kategori eklenmemiş.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($categories as $c): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                                    <span class="material-symbols-outlined text-sm">category</span>
                                </div>
                                <span class="font-bold text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($c['name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 rounded-lg transition-colors" onclick='openEditModal(<?php echo json_encode($c); ?>)'>
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button class="p-2 hover:bg-red-50 dark:hover:bg-red-500/10 text-red-400 rounded-lg transition-colors" onclick="deleteCategory(<?php echo $c['id']; ?>)">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Yeni Kategori Ekle</h3>
            <button onclick="closeAddModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="addForm" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kategori Adı</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    placeholder="Örn: Kira, Mutfak Gideri vb.">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">İptal</button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-card-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 dark:border-border-dark">
        <div class="p-5 border-b border-gray-100 dark:border-border-dark flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Kategori Düzenle</h3>
            <button onclick="closeEditModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editForm" class="p-5 space-y-4">
            <input type="hidden" name="id">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Kategori Adı</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-surface-dark text-gray-900 dark:text-white font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">İptal</button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addModal').classList.remove('flex');
    }

    function openEditModal(category) {
        const modal = document.getElementById('editModal');
        modal.querySelector('input[name="id"]').value = category.id;
        modal.querySelector('input[name="name"]').value = category.name;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    // Add Submit
    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        
        fetch('<?php echo site_url("finance/api_add_expense_category"); ?>', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message, 'error');
                btn.disabled = false;
            }
        });
    });

    // Edit Submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        
        fetch('<?php echo site_url("finance/api_edit_expense_category"); ?>', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message, 'error');
                btn.disabled = false;
            }
        });
    });

    function deleteCategory(id) {
        if(!confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) return;
        
        const fd = new FormData();
        fd.append('id', id);

        fetch('<?php echo site_url("finance/api_delete_expense_category"); ?>', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message, 'error');
            }
        });
    }
</script>
