<?php
// views/entities/statement.php
$pageTitle = "Cari Ekstre";
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

// Get date filters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$transactions = $entityModel->getStatement($entityId, $startDate, $endDate);
?>

<div class="container mx-auto px-4 py-8 pb-32">
    <!-- Entity Header -->
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-lg p-6 mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <span class="material-symbols-outlined text-9xl">receipt_long</span>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-start mb-4 relative z-10">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3 mb-2">
                    <span class="p-2 bg-primary/10 rounded-lg text-primary material-symbols-outlined">badge</span>
                    <?php echo htmlspecialchars($entity['name']); ?>
                </h2>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-1">
                    <?php if (!empty($entity['tax_id'])): ?>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[16px]">id_card</span> <strong>VKN/TCKN:</strong> <?php echo htmlspecialchars($entity['tax_id']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($entity['phone'])): ?>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[16px]">call</span> <strong>Telefon:</strong> <?php echo htmlspecialchars($entity['phone']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($entity['email'])): ?>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[16px]">mail</span> <strong>E-posta:</strong> <?php echo htmlspecialchars($entity['email']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($entity['address'])): ?>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[16px]">location_on</span> <strong>Adres:</strong> <?php echo htmlspecialchars($entity['address']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right mt-4 md:mt-0 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="text-xs font-bold text-gray-500 uppercase mb-1">Güncel Bakiye</div>
                <div class="text-3xl font-bold <?php echo $entity['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo number_format($entity['balance'], 2); ?> ₺
                </div>
                <div class="text-xs font-medium text-gray-400 mt-1">
                    <?php echo $entity['balance'] >= 0 ? 'Şirket Alacaklı' : 'Şirket Borçlu'; ?>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end mt-6 pt-6 border-t dark:border-gray-700/50">
            <input type="hidden" name="id" value="<?php echo $entityId; ?>">
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Başlangıç Tarihi</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 material-symbols-outlined text-sm">calendar_today</span>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate ?? ''); ?>" 
                           class="w-full pl-10 px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                </div>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bitiş Tarihi</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 material-symbols-outlined text-sm">calendar_today</span>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate ?? ''); ?>" 
                           class="w-full pl-10 px-4 py-2 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-6 py-2 bg-primary text-white font-bold rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30 transition-all flex-1 md:flex-none">
                    <span class="material-symbols-outlined">filter_alt</span>
                    Filtrele
                </button>
                <?php if ($startDate || $endDate): ?>
                    <a href="<?php echo public_url('entity/statement?id=' . $entityId); ?>" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center transition-colors">
                       <span class="material-symbols-outlined">close</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-400">history</span>
                Hesap Hareketleri
            </h3>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center gap-2 font-medium transition-colors">
                <span class="material-symbols-outlined">print</span>
                Yazdır
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border dark:border-gray-700">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="p-4 text-left border-b dark:border-gray-700">Tarih</th>
                        <th class="p-4 text-left border-b dark:border-gray-700">Evrak No</th>
                        <th class="p-4 text-left border-b dark:border-gray-700">İşlem Tipi</th>
                        <th class="p-4 text-left border-b dark:border-gray-700">Açıklama</th>
                        <th class="p-4 text-right border-b dark:border-gray-700">Borç</th>
                        <th class="p-4 text-right border-b dark:border-gray-700">Alacak</th>
                        <th class="p-4 text-right border-b dark:border-gray-700">Bakiye</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 dark:text-gray-300 text-sm">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-full">
                                        <span class="material-symbols-outlined text-4xl">inbox</span>
                                    </div>
                                    <p class="font-medium">Henüz işlem kaydı bulunmuyor</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $runningBalance = 0;
                        foreach ($transactions as $trans): 
                            $runningBalance += floatval($trans['amount']);
                            $isDebit = $trans['amount'] < 0; 
                            $borc = ($trans['amount'] > 0) ? $trans['amount'] : 0;
                            $alacak = ($trans['amount'] < 0) ? abs($trans['amount']) : 0;
                        ?>
                        <tr class="border-b dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/10 cursor-pointer transition-colors group" onclick="openDetail(<?php echo $trans['id']; ?>)">
                            <td class="p-4 font-mono text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                <?php echo date('d.m.Y', strtotime($trans['transaction_date'])); ?>
                            </td>
                            <td class="p-4 font-mono text-xs">
                                <?php echo htmlspecialchars($trans['document_no'] ?? '-'); ?>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border <?php 
                                    echo $trans['type'] === 'fatura' ? 'bg-orange-50 text-orange-600 border-orange-200' : 
                                         ($trans['type'] === 'tahsilat' ? 'bg-green-50 text-green-600 border-green-200' : 
                                          ($trans['type'] === 'odeme' ? 'bg-red-50 text-red-600 border-red-200' : 
                                           'bg-gray-50 text-gray-600 border-gray-200')); 
                                ?>">
                                    <?php echo strtoupper($trans['type']); ?>
                                </span>
                            </td>
                            <td class="p-4 max-w-xs truncate" title="<?php echo htmlspecialchars($trans['description']); ?>">
                                <?php echo htmlspecialchars($trans['description']); ?>
                            </td>
                            <td class="p-4 text-right font-mono text-gray-600 dark:text-gray-300">
                                <?php echo $borc > 0 ? number_format($borc, 2) : '-'; ?>
                            </td>
                            <td class="p-4 text-right font-mono text-gray-600 dark:text-gray-300">
                                <?php echo $alacak > 0 ? number_format($alacak, 2) : '-'; ?>
                            </td>
                            <td class="p-4 text-right font-mono font-bold <?php echo $runningBalance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo number_format($runningBalance, 2); ?> ₺
                                <span class="material-symbols-outlined text-[16px] align-middle ml-1 group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DETAIL MODAL -->
<div id="detailModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
        <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    İşlem Detayı <span id="modalDocId" class="text-xs text-gray-400 ml-2 font-normal"></span>
                </h3>
            </div>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1">
            <div id="modalLoading" class="flex flex-col items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                <p class="text-gray-500 mt-4 font-medium">Detaylar yükleniyor...</p>
            </div>
            
            <div id="modalBody" class="hidden space-y-6">
                
                <!-- VIEW MODE -->
                <div id="viewSection">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                        <div>
                            <div class="text-xs text-gray-500 uppercase font-bold">Tarih</div>
                            <div class="text-gray-900 dark:text-white font-medium" id="detDate"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase font-bold">İşlem Tipi</div>
                            <div class="text-gray-900 dark:text-white font-medium" id="detType"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase font-bold">Tutar</div>
                            <div class="text-gray-900 dark:text-white font-bold text-lg" id="detAmount"></div>
                        </div>
                         <div>
                            <div class="text-xs text-gray-500 uppercase font-bold">Evrak No</div>
                            <div class="text-gray-900 dark:text-white font-medium font-mono" id="detDoc"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Açıklama</h4>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded border dark:border-gray-700 text-gray-600 dark:text-gray-300 text-sm whitespace-pre-wrap leading-relaxed" id="detDesc"></div>
                    </div>
                </div>

                <!-- EDIT MODE -->
                <div id="editSection" class="hidden">
                     <form id="editForm" onsubmit="saveEdit(event)" class="space-y-4">
                         <input type="hidden" id="editId">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                             <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tarih</label>
                                <input type="date" id="editDateInput" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-input-dark dark:border-gray-700">
                             </div>
                             <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Evrak No</label>
                                <input type="text" id="editDocInput" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-input-dark dark:border-gray-700">
                             </div>
                         </div>
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Açıklama</label>
                            <textarea id="editDescInput" rows="3" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-input-dark dark:border-gray-700"></textarea>
                         </div>
                     </form>
                </div>

                <!-- Kalemler Tablosu -->
                <div id="itemsSection" class="hidden pt-4 border-t dark:border-gray-700">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">list</span>
                        Fatura Kalemleri / Detaylar
                    </h4>
                    <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-xs text-gray-500 uppercase font-bold">
                                <tr>
                                    <th class="p-3 text-left">Ürün/Hizmet</th>
                                    <th class="p-3 text-right">Miktar</th>
                                    <th class="p-3 text-center">Birim</th>
                                    <th class="p-3 text-right">Birim Fiyat</th>
                                    <th class="p-3 text-right">Toplam</th>
                                    <!-- Edit Mode Column -->
                                    <th class="p-3 text-center w-10 edit-col hidden">İşlem</th>
                                </tr>
                            </thead>
                            <tbody id="detItemsBody" class="divide-y dark:divide-gray-700 bg-white dark:bg-card-dark">
                                <!-- JS ile dolacak -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 flex justify-between items-center">
             <!-- Delete Button -->
             <div>
                <button type="button" onclick="deleteTransaction()" id="deleteBtn" class="px-4 py-2 border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 rounded-lg font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">delete</span> Sil
                 </button>
             </div>

             <div id="viewButtons" class="flex gap-2">
                 <button onclick="enableEditMode()" class="px-4 py-2 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-lg font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">edit</span> Düzenle
                 </button>
                 <button onclick="closeDetail()" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-bold transition-colors">
                    Kapat
                 </button>
             </div>
             
             <div id="editButtons" class="hidden flex gap-2">
                 <button onclick="cancelEdit()" class="px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-lg font-bold transition-colors">
                    Vazgeç
                 </button>
                 <button onclick="document.getElementById('editForm').dispatchEvent(new Event('submit'))" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">save</span> Kaydet
                 </button>
             </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, nav, footer, button, #detailModal {
        display: none !important;
    }
    body {
        background: white !important;
    }
}
</style>

<script>
let currentTransaction = null;
let currentItems = []; // Kalemleri düzenleme için tutacağız

function openDetail(id) {
    const modal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    const loading = document.getElementById('modalLoading');
    const body = document.getElementById('modalBody');
    const itemsSection = document.getElementById('itemsSection');
    
    // Reset view
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    // Reset to view mode
    cancelEdit();
    
    loading.classList.remove('hidden');
    body.classList.add('hidden');
    itemsSection.classList.add('hidden');
    
    // Fetch Data
    fetch('<?php echo public_url('api/transaction-detail?id='); ?>' + id)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                const tr = data.transaction;
                currentTransaction = tr; // Store for edit
                currentItems = data.items || [];
                
                // Fill Header
                document.getElementById('modalDocId').textContent = '#' + tr.id;
                document.getElementById('editId').value = tr.id;
                
                // View Mode Fields
                document.getElementById('detDate').textContent = new Date(tr.transaction_date).toLocaleDateString('tr-TR');
                document.getElementById('detType').innerHTML = '<span class="uppercase font-bold text-primary">' + tr.type + '</span>';
                document.getElementById('detAmount').textContent = parseFloat(tr.amount).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                document.getElementById('detAmount').className = 'text-lg font-bold ' + (tr.amount < 0 ? 'text-red-600' : 'text-green-600');
                document.getElementById('detDoc').textContent = tr.document_no || '-';
                document.getElementById('detDesc').textContent = tr.description || '-';
                
                // Edit Mode Fields
                document.getElementById('editDateInput').value = tr.transaction_date.split(' ')[0]; // YYYY-MM-DD
                document.getElementById('editDocInput').value = tr.document_no || '';
                document.getElementById('editDescInput').value = tr.description || '';
                
                // Render Items
                renderItems(currentItems, false);
                
                if (currentItems.length > 0) {
                    itemsSection.classList.remove('hidden');
                }
                
                loading.classList.add('hidden');
                body.classList.remove('hidden');
            } else {
                alert('Hata: ' + data.message);
                closeDetail();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Bir hata oluştu.');
            closeDetail();
        });
}

function renderItems(items, isEditMode) {
    const itemsBody = document.getElementById('detItemsBody');
    itemsBody.innerHTML = '';
    
    // Show/Hide Edit Column Header
    const editCols = document.querySelectorAll('.edit-col');
    editCols.forEach(col => {
        if(isEditMode) col.classList.remove('hidden');
        else col.classList.add('hidden');
    });

    items.forEach((item, index) => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors';
        
        let price = parseFloat(item.unit_price || 0);
        let qty = parseFloat(item.quantity || 0);
        let total = price * qty;
        
        if (isEditMode) {
            // EDIT MODE ROW
            row.innerHTML = `
                <td class="p-3">
                    <div class="font-bold text-gray-800 dark:text-gray-200">${item.product_name || 'Bilinmeyen Ürün'}</div>
                    <div class="text-xs text-gray-500 font-mono">${item.product_id ? 'ID: '+item.product_id : ''}</div>
                </td>
                <td class="p-3 text-right">
                    <input type="number" step="0.01" class="w-20 px-2 py-1 border rounded text-right dark:bg-input-dark dark:border-gray-700" 
                           value="${qty}" onchange="updateItem(${index}, 'quantity', this.value)">
                </td>
                <td class="p-3 text-center text-xs text-gray-500">${item.unit || '-'}</td>
                <td class="p-3 text-right">
                     <input type="number" step="0.01" class="w-24 px-2 py-1 border rounded text-right dark:bg-input-dark dark:border-gray-700" 
                           value="${price}" onchange="updateItem(${index}, 'unit_price', this.value)">
                </td>
                <td class="p-3 text-right font-bold text-gray-800 dark:text-gray-200" id="total-${index}">
                    ${total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺
                </td>
                <td class="p-3 text-center">
                    <button type="button" onclick="removeItem(${index})" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </td>
            `;
        } else {
            // VIEW MODE ROW
            row.innerHTML = `
                <td class="p-3">
                    <div class="font-bold text-gray-800 dark:text-gray-200">${item.product_name || 'Bilinmeyen Ürün'}</div>
                    <div class="text-xs text-gray-500 font-mono">${item.product_id ? 'ID: '+item.product_id : ''}</div>
                </td>
                <td class="p-3 text-right font-mono">${qty}</td>
                <td class="p-3 text-center text-xs text-gray-500">${item.unit || '-'}</td>
                <td class="p-3 text-right font-mono text-gray-600">${price.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                <td class="p-3 text-right font-bold text-gray-800 dark:text-gray-200">${total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                <td class="p-3 text-center hidden edit-col"></td>
            `;
        }
        itemsBody.appendChild(row);
    });
}

function updateItem(index, field, value) {
    currentItems[index][field] = parseFloat(value);
    
    // Update visual total
    let price = parseFloat(currentItems[index].unit_price || 0);
    let qty = parseFloat(currentItems[index].quantity || 0);
    let total = price * qty;
    
    const totalEl = document.getElementById('total-' + index);
    if(totalEl) {
        totalEl.textContent = total.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    }
}

function removeItem(index) {
    if(!confirm('Bu kalemi listeden kaldırmak istediğinize emin misiniz?')) return;
    currentItems.splice(index, 1);
    renderItems(currentItems, true);
}

function enableEditMode() {
    document.getElementById('viewSection').classList.add('hidden');
    document.getElementById('editSection').classList.remove('hidden');
    document.getElementById('viewButtons').classList.add('hidden');
    document.getElementById('editButtons').classList.remove('hidden');
    document.getElementById('deleteBtn').classList.add('hidden');
    
    // Render items in edit mode
    renderItems(currentItems, true);
}

function cancelEdit() {
    document.getElementById('viewSection').classList.remove('hidden');
    document.getElementById('editSection').classList.add('hidden');
    document.getElementById('viewButtons').classList.remove('hidden');
    document.getElementById('editButtons').classList.add('hidden');
    document.getElementById('deleteBtn').classList.remove('hidden');
    
    // Render items in view mode (revert changes basically if not saved - though currentItems is modified in memory... 
    // Ideally should clone deep but for simplicity relying on reload or user cancel logic)
    // Actually, to fully revert, we should re-fetch or use a backup. 
    // For now, let's just re-fetch to be safe on cancel.
    if(currentTransaction) {
        openDetail(currentTransaction.id); 
    }
}

function saveEdit(e) {
    e.preventDefault();
    if(!currentTransaction) return;
    
    const id = document.getElementById('editId').value;
    const date = document.getElementById('editDateInput').value;
    const docNo = document.getElementById('editDocInput').value;
    const desc = document.getElementById('editDescInput').value;
    
    const payload = {
        id, 
        transaction_date: date, 
        document_no: docNo, 
        description: desc,
        items: currentItems // Send updated items
    };
    
    fetch('<?php echo public_url('api/edit-entity-transaction'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            window.location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    })
    .catch(err => alert('Bir hata oluştu: ' + err));
}

function deleteTransaction() {
    if(!currentTransaction) return;
    if(!confirm('DİKKAT: Bu işlemi silmek cari bakiyesini de güncelleyecektir. Bu işlem geri alınamaz. Emin misiniz?')) return;
    
    fetch('<?php echo public_url('api/delete-entity-transaction'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentTransaction.id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            window.location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    })
    .catch(err => alert('Bir hata oluştu: ' + err));
}

function closeDetail() {
    const modal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
