<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <a id="back_btn" href="<?php echo site_url('invoices'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    
                    <script>
                    (function() {
                        try {
                            // Use our custom SPA history tracker
                            const prevUrl = sessionStorage.getItem('app_prev_url');
                            const currentHost = window.location.host;
                            const currentPath = window.location.pathname;
                            
                            if (prevUrl && prevUrl.includes(currentHost)) {
                                // Ensure we don't link back to the exact same page (loop prevention)
                                if (!prevUrl.includes(currentPath) && !prevUrl.includes('/api/')) {
                                    document.getElementById('back_btn').href = prevUrl;
                                }
                            }
                        } catch(e) { console.log('Back nav error', e); }
                    })();
                    </script>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">
                        <?php 
                            $type_titles = [
                                'fatura' => 'Fatura Detayı',
                                'fis' => 'Fiş Detayı',
                                'purchase' => 'Alış Faturası',
                                'sale' => 'Satış Faturası',
                                'tahsilat' => 'Tahsilat Makbuzu',
                                'odeme' => 'Ödeme Detayı',
                                'virman' => 'Virman Detayı',
                                'borc_dekontu' => 'Borç Dekontu',
                                'alacak_dekontu' => 'Alacak Dekontu'
                            ];
                            echo $type_titles[$invoice['type']] ?? 'İşlem Detayı';
                        ?>
                    </h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal ml-9">
                    <?php echo $invoice['document_no'] ?: 'Belge #' . $invoice['id']; ?>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="edit_toggle_btn" onclick="toggleEditMode()" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-amber-500/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </button>
                <div id="edit_actions" class="hidden flex items-center gap-3">
                    <button type="button" onclick="cancelEditMode()" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">close</span>
                        İptal
                    </button>
                    <button type="button" onclick="saveInvoice()" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-green-500/30 flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Kaydet
                    </button>
                </div>
                <button type="button" onclick="deleteInvoice()" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-red-500/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">delete</span>
                    Sil
                </button>
            </div>
        </div>
    </header>
    <style>
        /* Custom styles for detail page */
        .edit-mode input[type="number"]::-webkit-inner-spin-button,
        .edit-mode input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .edit-mode input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <form id="invoice_form" class="w-full" method="POST">
            <input type="hidden" name="id" value="<?php echo isset($invoice['transaction_id']) ? $invoice['transaction_id'] : $invoice['id']; ?>">
            
            <!-- Bilgi Alanları -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Fatura Bilgileri
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Fatura No</label>
                        <input type="text" id="invoice_no" name="invoice_no" value="<?php echo $invoice['document_no']; ?>" readonly
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-mono font-bold focus:border-primary transition-all opacity-70 outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tarih</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="<?php echo $invoice['transaction_date']; ?>" required disabled
                               class="editable w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Vade Tarihi</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo $invoice['due_date'] ?? $invoice['transaction_date']; ?>" disabled
                               class="editable w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tür</label>
                        <select id="invoice_type" name="type" required disabled
                                class="editable w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="fatura" <?php echo $invoice['type'] == 'fatura' ? 'selected' : ''; ?>>Fatura</option>
                            <option value="fis" <?php echo $invoice['type'] == 'fis' ? 'selected' : ''; ?>>Fiş / Perakende Satış</option>
                            <option value="purchase" <?php echo $invoice['type'] == 'purchase' ? 'selected' : ''; ?>>Alış Faturası</option>
                            <option value="sale" <?php echo $invoice['type'] == 'sale' ? 'selected' : ''; ?>>Satış Faturası</option>
                            <option value="tahsilat" <?php echo $invoice['type'] == 'tahsilat' ? 'selected' : ''; ?>>Tahsilat (Para Girişi)</option>
                            <option value="odeme" <?php echo $invoice['type'] == 'odeme' ? 'selected' : ''; ?>>Ödeme (Para Çıkışı)</option>
                            <option value="virman" <?php echo $invoice['type'] == 'virman' ? 'selected' : ''; ?>>Virman</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">KDV Durumu</label>
                        <select id="tax_included" name="tax_included" onchange="updateTotals()" disabled
                                class="editable w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="0" <?php echo ($invoice['tax_included'] ?? 0) == 0 ? 'selected' : ''; ?>>KDV Hariç</option>
                            <option value="1" <?php echo ($invoice['tax_included'] ?? 0) == 1 ? 'selected' : ''; ?>>KDV Dahil</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Cari</label>
                        <div class="relative searchable-select z-[60] text-left">
                            <div class="relative">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm pointer-events-none">expand_more</span>
                                <input type="text" class="search-input w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white" 
                                       placeholder="Cari Ara..." autocomplete="off" value="<?php echo htmlspecialchars($invoice['entity_name']); ?>">
                                <input type="hidden" name="entity_id" value="<?php echo $invoice['entity_id']; ?>" class="hidden-input">
                            </div>
                            <div class="options-list absolute w-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                <?php foreach($entities as $e): ?>
                                    <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                        <span><?php echo htmlspecialchars($e['name']); ?></span>
                                        <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 4); ?> ₺</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div id="dekont_amount_div" class="<?php echo !in_array($invoice['type'], ['borc_dekontu', 'alacak_dekontu']) ? 'hidden' : ''; ?>">
                         <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tutar</label>
                         <input type="number" id="dekont_amount" value="<?php echo $invoice['amount']; ?>" step="0.0001" disabled
                                class="editable w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <!-- Payment Fields (Disabled by default for invoice view but can be enabled) -->
                    <input type="hidden" name="payment_status" value="<?php echo $invoice['payment_status'] ?? 'unpaid'; ?>">
                    <input type="hidden" name="payment_type" value="<?php echo $invoice['payment_type'] ?? 'cash_bank'; ?>">
                    <input type="hidden" name="wallet_id" value="<?php echo $invoice['wallet_id'] ?? ''; ?>">
                    <input type="hidden" name="transfer_entity_id" value="<?php echo $invoice['transfer_entity_id'] ?? ''; ?>">
                </div>
            </div>

            <!-- Ürün/Hizmet Ekleme (Hidden initially, shown in Edit mode) -->
            <div id="item_add_section" class="hidden <?php echo !in_array($invoice['type'], ['fatura', 'fis', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu']) ? 'hidden-important' : ''; ?> bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-500">inventory_2</span>
                    Yeni Kalem Ekle
                </h3>
                
                <div class="grid grid-cols-12 gap-2 mb-4">
                    <div class="col-span-6 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tip</label>
                        <select id="add_item_type" class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="stok">STOK</option>
                            <option value="gider">GİDER</option>
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Ürün/Hizmet</label>
                        <select id="add_product" class="w-full select2-product">
                            <option value="">Ürün ara...</option>
                            <?php foreach($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" 
                                        data-price="<?php echo $product['price'] ?? 0; ?>"
                                        data-unit="<?php echo $product['unit'] ?? 'Adet'; ?>"
                                        data-type="stok"
                                        data-barcode="<?php echo $product['barcode'] ?? ''; ?>"
                                        data-match="<?php echo $product['match_name'] ?? ''; ?>"
                                        data-gorsel="<?php echo $product['gorsel'] ?? ''; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                            
                            <?php foreach($expense_categories as $expense): ?>
                                <option value="EXP_<?php echo $expense['id']; ?>" 
                                        data-price="0" 
                                        data-unit="Adet" 
                                        data-type="gider"
                                        data-barcode=""
                                        data-match="">
                                    <?php echo htmlspecialchars($expense['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Miktar</label>
                        <input type="number" id="add_quantity" value="1" min="0.0001" step="0.0001"
                               class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-center focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Birim</label>
                        <select id="add_unit" class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="Adet">Adet</option>
                            <option value="Kg">Kg</option>
                            <option value="Litre">Litre</option>
                            <option value="Paket">Paket</option>
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Birim Fiyat</label>
                        <input type="number" id="add_price" value="0" min="0" step="0.0001"
                               class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-right focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">İnd. %</label>
                        <input type="number" id="add_discount" value="0" min="0" max="100"
                               class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-center focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">KDV %</label>
                        <select id="add_tax" class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="0">%0</option>
                            <option value="1">%1</option>
                            <option value="10">%10</option>
                            <option value="20" selected>%20</option>
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-2 flex items-end">
                        <button type="button" onclick="addItem()" class="w-full px-3 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black transition-all shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Ekle
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ürün/Hizmet Tablosu -->
            <div id="items_table_container" class="<?php echo !in_array($invoice['type'], ['fatura', 'fis', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu']) ? 'hidden' : ''; ?> bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-500">receipt_long</span>
                    Fatura Kalemleri
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-12">Görsel</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase">Tip</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-500 uppercase">Ürün</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase">Miktar</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase">Birim</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-slate-500 uppercase">B.Fiyat</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase">İnd.</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase">KDV</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-slate-500 uppercase">Toplam</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-12 action-col hidden"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                            <!-- Items populated by JS -->
                        </tbody>
                    </table>
            </div>

            <!-- Toplam Alanı -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Not</label>
                        <textarea id="notes_bottom" name="notes" rows="2" disabled
                                  class="editable w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors"><?php echo ($invoice['notes'] ?? ($invoice['description'] ?? '')); ?></textarea>
                    </div>
                    
                    <div class="w-full md:w-80 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Ara Toplam</span>
                            <span id="subtotal" class="font-medium text-slate-700 dark:text-slate-300">0,0000 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">İndirimler</span>
                            <span id="line_discount_total" class="font-medium text-orange-500">-0,0000 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">KDV</span>
                            <span id="tax_total" class="font-medium text-slate-700 dark:text-slate-300">0,0000 ₺</span>
                        </div>
                        <div id="general_discount_section" class="<?php echo !in_array($invoice['type'], ['fatura', 'fis', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu']) ? 'hidden' : ''; ?> flex justify-between items-center text-sm border-t border-slate-200 dark:border-slate-700 pt-3">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500">Genel İndirim</span>
                                <input type="number" id="general_discount" name="general_discount" value="<?php echo abs($invoice['general_discount'] ?? 0); ?>" step="0.0001" onchange="updateTotals()" disabled
                                       class="editable w-20 px-2 py-1 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-right focus:border-primary transition-colors">
                            </div>
                            <span id="general_discount_display" class="font-medium text-orange-500">-0,0000 ₺</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-slate-200 dark:border-slate-700 pt-3">
                            <span class="text-slate-900 dark:text-white">Genel Toplam</span>
                            <span id="grand_total" class="text-primary">0,0000 ₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions (Edit mode only) -->
            <div id="edit_actions" class="hidden flex flex-col sm:flex-row gap-3 justify-end">
                <button type="button" onclick="cancelEditMode()" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors text-center">
                    Vazgeç
                </button>
                <button type="submit" class="px-8 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </main>
</div>

<script>
var invoiceItems = [];
var isEditMode = false;

// Initial data load - use IIFE for SPA compatibility

function initInvoiceDetailPage() {
    // Clear existing items to prevent duplicates on re-entry via SPA
    invoiceItems = [];

    // Populate items array
    // Populate items array securely using JSON
    const dbItems = <?php echo json_encode($items ?? []); ?>;
    
    invoiceItems = dbItems.map(item => {
        // Handle ID generation logic similar to previous PHP loop
        let productId = item.product_id;
        if (!productId && item.expense_category_id) {
            productId = 'EXP_' + item.expense_category_id;
        }
        
        // Handle Name resolution
        let productName = item.product_name;
        if (!productName && item.expense_category_name) productName = item.expense_category_name;
        if (!productName && item.description) productName = item.description;
        
        return {
            product_id: productId,
            product_name: productName || '',
            item_type: item.item_type || 'stok',
            quantity: parseFloat(item.quantity) || 0,
            unit: item.unit || 'Adet',
            price: parseFloat(item.unit_price) || 0,
            discount_rate: parseFloat(item.discount_rate) || 0,
            discount_amount: parseFloat(item.discount_amount) || 0,
            tax_rate: parseFloat(item.tax_rate) || 0,
            tax_amount: parseFloat(item.tax_amount) || 0,
            total: parseFloat(item.total_amount) || 0,
            gorsel: item.gorsel || ''
        };
    });
    
    // Ensure render happens after array population
     setTimeout(renderItems, 50);
    
    // Initialize Select2 (with slight delay for SPA)
    $('.select2-entity').select2({ width: '100%' });

    // --- Product Filtering logic ---
    let allProductOptions = [];
    $('#add_product option').each(function() {
        if ($(this).val() !== '') {
            allProductOptions.push({
                type: $(this).data('type'),
                html: $(this)[0].outerHTML
            });
        }
    });

    const productSelect2Options = {
        placeholder: 'Ürün ara...',
        allowClear: true,
        width: '100%',
        tags: true,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const $element = $(data.element);
            if (text.indexOf(term) > -1 || ($element.data('barcode') || '').toString().toLowerCase().indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    };

    $('#add_product').select2(productSelect2Options);

    $('#add_item_type').off('change').on('change', function() {
        const selectedType = $(this).val();
        const productSelect = $('#add_product');
        if (productSelect.hasClass("select2-hidden-accessible")) productSelect.select2('destroy');
        productSelect.empty().append('<option value="">Ürün ara...</option>');
        allProductOptions.forEach(opt => {
            if (opt.type === selectedType) productSelect.append(opt.html);
        });
        productSelect.select2(productSelect2Options).val('').trigger('change');
    });

    // Handle product selection
    $('#add_product').off('change').on('change', function() {
        const selected = $(this).find(':selected');
        if (!selected.val()) return;
        document.getElementById('add_price').value = selected.data('price') || 0;
        document.getElementById('add_unit').value = selected.data('unit') || 'Adet';
        document.getElementById('add_item_type').value = selected.data('type') || 'stok';
    });
}

// Execution block moved to end of file

window.toggleEditMode = function() {
    isEditMode = true;
    document.querySelectorAll('.editable').forEach(el => el.disabled = false);
    
    // Toggle view/edit mode visibility
    document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
    
    // Initialize Searchable Selects
    setupSearchableSelects();

    // Only show item add section for fatura/fis/purchase/sale/dekont
    const type = '<?php echo $invoice['type']; ?>';
    if (['fatura', 'fis', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu'].includes(type)) {
        document.getElementById('item_add_section').classList.remove('hidden');
        document.querySelectorAll('.action-col').forEach(el => el.classList.remove('hidden'));
    }
    
    document.getElementById('edit_actions').classList.remove('hidden');
    document.getElementById('edit_toggle_btn').classList.add('hidden');
    
    renderItems(); // Re-render to show delete buttons
    
    // Dekont handling in Edit Mode
    const isDekont = ['borc_dekontu', 'alacak_dekontu'].includes(type);
    if (isDekont) {
        document.getElementById('dekont_amount_div').classList.remove('hidden');
        document.getElementById('items_table_container').classList.add('hidden'); // Ensure table is hidden
        document.getElementById('general_discount_section').classList.add('hidden'); // Ensure disocunt is hidden
        // Tax inputs hide
         const taxDiv = document.getElementById('tax_included').parentElement;
         if(taxDiv) taxDiv.classList.add('hidden');
         
         // Hide totals right side
         const totalSection = document.getElementById('subtotal').closest('.rounded-2xl');
         if(totalSection) {
             const totalsRightSide = totalSection.querySelector('.w-full.md\\:w-80');
             if(totalsRightSide) totalsRightSide.classList.add('hidden');
         }
    }
}

// Custom Searchable Select Logic
function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.onclick = (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        };

        input.onkeyup = (e) => {
            const val = e.target.value.toLocaleLowerCase("tr-TR");
            list.classList.remove('hidden');
            options.forEach(opt => {
                const text = opt.textContent.toLocaleLowerCase("tr-TR");
                if(text.includes(val)) {
                    opt.classList.remove('hidden');
                } else {
                    opt.classList.add('hidden');
                }
            });
        };

        options.forEach(opt => {
            opt.onclick = (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
            };
        });

        document.onclick = (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        };
    });
}

function cancelEditMode() {
    location.reload();
}

window.addItem = function() {
    const productSelect = document.getElementById('add_product');
    const productId = productSelect.value;
    const selectedOption = $(productSelect).find(':selected');
    const productName = selectedOption.text();
    const gorsel = selectedOption.data('gorsel') || '';
    const itemType = document.getElementById('add_item_type').value;
    const quantity = parseFloat(document.getElementById('add_quantity').value) || 0;
    const unit = document.getElementById('add_unit').value;
    const price = parseFloat(document.getElementById('add_price').value) || 0;
    const discountRate = parseFloat(document.getElementById('add_discount').value) || 0;
    const taxRate = parseFloat(document.getElementById('add_tax').value) || 0;
    
    if (!productId || quantity <= 0) return;
    
    const lineSubtotal = quantity * price;
    const discountAmount = lineSubtotal * (discountRate / 100);
    const afterDiscount = lineSubtotal - discountAmount;
    const taxIncluded = document.getElementById('tax_included').value == '1';
    
    let taxAmount, total;
    if (taxIncluded) {
        total = afterDiscount;
        taxAmount = total - (total / (1 + (taxRate / 100)));
    } else {
        taxAmount = afterDiscount * (taxRate / 100);
        total = afterDiscount + taxAmount;
    }
    
    invoiceItems.push({
        product_id: productId,
        product_name: productName,
        item_type: itemType,
        quantity: quantity,
        unit: unit,
        price: price,
        discount_rate: discountRate,
        discount_amount: discountAmount,
        tax_rate: taxRate,
        tax_amount: taxAmount,
        total: total,
        gorsel: gorsel
    });
    
    renderItems();
    // Reset inputs
    $('#add_product').val('').trigger('change');
    document.getElementById('add_quantity').value = 1;
    document.getElementById('add_price').value = 0;
}

window.removeItem = function(index) {
    invoiceItems.splice(index, 1);
    renderItems();
};

window.renderItems = function() {
    const tbody = document.getElementById('items_tbody');
    tbody.innerHTML = invoiceItems.map((item, index) => {
        if (!isEditMode) {
            return `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 group">
                <td class="px-3 py-2 text-center">
                    <div class="size-10 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 overflow-hidden border border-slate-100 dark:border-white/5">
                        ${item.gorsel 
                            ? `<img src="${'<?php echo base_url(); ?>' + item.gorsel}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300 cursor-zoom-in" onclick="openImageModal(this.src, '${item.product_name.replace(/'/g, "\\'")}')" onerror="this.src='<?php echo base_url('assets/img/no-image.png'); ?>'; this.onerror=null;">`
                            : `<span class="material-symbols-outlined text-xl">inventory_2</span>`
                        }
                    </div>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-1 text-xs font-bold rounded ${item.item_type === 'stok' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}">
                        ${item.item_type.toUpperCase()}
                    </span>
                </td>
                <td class="px-3 py-2 text-sm font-medium text-slate-900 dark:text-white">${item.product_name}</td>
                <td class="px-3 py-2 text-sm text-center">${item.quantity}</td>
                <td class="px-3 py-2 text-sm text-center">${item.unit}</td>
                <td class="px-3 py-2 text-sm text-right font-mono">${item.price.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4})} ₺</td>
                <td class="px-3 py-2 text-sm text-center text-orange-500">${item.discount_rate > 0 ? '%' + item.discount_rate : '-'}</td>
                <td class="px-3 py-2 text-sm text-center">%${item.tax_rate}</td>
                <td class="px-3 py-2 text-sm text-right font-bold text-slate-900 dark:text-white">${item.total.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4})} ₺</td>
                <td class="px-3 py-2 text-center action-col hidden"></td>
            </tr>`;
        } else {
            return `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 group bg-slate-50/50 dark:bg-white/5">
                <td class="px-3 py-2 text-center">
                    <div class="size-10 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 overflow-hidden border border-slate-100 dark:border-white/5">
                         <span class="material-symbols-outlined text-xl">edit</span>
                    </div>
                </td>
                <td class="px-3 py-2 text-center">
                     <span class="px-2 py-1 text-xs font-bold rounded ${item.item_type === 'stok' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}">
                        ${item.item_type.toUpperCase()}
                    </span>
                </td>
                <td class="px-3 py-2 text-sm font-medium text-slate-900 dark:text-white">
                    <input type="text" value="${item.product_name}" oninput="updateItem(${index}, 'product_name', this.value)" class="w-full bg-transparent border-b border-dashed border-slate-300 focus:border-primary outline-none px-1 text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" value="${item.quantity}" min="0.0001" step="0.0001" oninput="updateItem(${index}, 'quantity', this.value)" class="w-16 bg-white dark:bg-black/20 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs text-center font-bold">
                </td>
                <td class="px-3 py-2 text-center">
                    <select onchange="updateItem(${index}, 'unit', this.value)" class="w-16 bg-white dark:bg-black/20 border border-slate-200 dark:border-slate-700 rounded-lg px-1 py-1 text-[10px] font-bold">
                        <option value="Adet" ${item.unit === 'Adet' ? 'selected' : ''}>Adet</option>
                        <option value="Kg" ${item.unit === 'Kg' ? 'selected' : ''}>Kg</option>
                        <option value="Litre" ${item.unit === 'Litre' ? 'selected' : ''}>Lt</option>
                        <option value="Paket" ${item.unit === 'Paket' ? 'selected' : ''}>Pkt</option>
                    </select>
                </td>
                <td class="px-3 py-2 text-right">
                    <input type="number" value="${item.price}" min="0" step="0.0001" oninput="updateItem(${index}, 'price', this.value)" class="w-20 bg-white dark:bg-black/20 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs text-right font-mono">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" value="${item.discount_rate}" min="0" max="100" step="1" oninput="updateItem(${index}, 'discount_rate', this.value)" class="w-12 bg-white dark:bg-black/20 border border-slate-200 dark:border-slate-700 rounded-lg px-1 py-1 text-xs text-center text-orange-500 font-bold">
                </td>
                <td class="px-3 py-2 text-center">
                     <select onchange="updateItem(${index}, 'tax_rate', this.value)" class="w-14 bg-white dark:bg-black/20 border border-slate-200 dark:border-slate-700 rounded-lg px-1 py-1 text-[10px] font-bold">
                        <option value="0" ${item.tax_rate == 0 ? 'selected' : ''}>%0</option>
                        <option value="1" ${item.tax_rate == 1 ? 'selected' : ''}>%1</option>
                        <option value="10" ${item.tax_rate == 10 ? 'selected' : ''}>%10</option>
                        <option value="20" ${item.tax_rate == 20 ? 'selected' : ''}>%20</option>
                    </select>
                </td>
                <td class="px-3 py-2 text-sm text-right font-bold text-slate-900 dark:text-white" id="row_total_${index}">
                    ${item.total.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4})} ₺
                </td>
                <td class="px-3 py-2 text-center action-col">
                    <button type="button" onclick="removeItem(${index})" class="p-1 text-red-500 hover:bg-red-50 rounded-lg">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </td>
            </tr>`;
        }
    }).join('');
    updateTotals();
};

window.updateItem = function(index, field, value) {
    const item = invoiceItems[index];
    
    if (field === 'product_name' || field === 'unit') {
        item[field] = value;
    } else {
        item[field] = parseFloat(value) || 0;
    }
    
    // Recalculate Logic
    const lineSubtotal = item.quantity * item.price;
    item.discount_amount = lineSubtotal * (item.discount_rate / 100);
    const afterDiscount = lineSubtotal - item.discount_amount;
    
    const taxIncluded = document.getElementById('tax_included').value == '1';
    
    if (taxIncluded) {
        item.total = afterDiscount;
        item.tax_amount = item.total - (item.total / (1 + (item.tax_rate / 100)));
    } else {
        item.tax_amount = afterDiscount * (item.tax_rate / 100);
        item.total = afterDiscount + item.tax_amount;
    }
    
    // Update Row Total Text
    const rowTotalEl = document.getElementById(`row_total_${index}`);
    if(rowTotalEl) {
        rowTotalEl.innerText = item.total.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
    }
    
    // Update Global Totals
    updateTotals();
};

window.updateTotals = function() {
    // Safely get tax_included
    const taxEl = document.getElementById('tax_included');
    const taxIncluded = taxEl ? (taxEl.value === '1') : false;
    
    // Safely get general_discount
    const discEl = document.getElementById('general_discount');
    const generalDiscount = discEl ? (parseFloat(discEl.value) || 0) : 0;
    
    let subtotal = 0;
    let lineDiscountTotal = 0;
    let taxTotal = 0;
    
    invoiceItems.forEach(item => {
        subtotal += (item.quantity * item.price);
        lineDiscountTotal += item.discount_amount;
    });

    let afterLineDiscounts = subtotal - lineDiscountTotal;
    
    if (afterLineDiscounts > 0) {
        const generalDiscountRatio = generalDiscount / afterLineDiscounts;
        invoiceItems.forEach(item => {
            const itemNetAfterLine = (item.quantity * item.price) - item.discount_amount;
            const itemNetFinal = itemNetAfterLine - (itemNetAfterLine * generalDiscountRatio);
            if (taxIncluded) {
                taxTotal += itemNetFinal - (itemNetFinal / (1 + (item.tax_rate / 100)));
            } else {
                taxTotal += itemNetFinal * (item.tax_rate / 100);
            }
        });
    }

    const grandTotal = taxIncluded 
        ? afterLineDiscounts - generalDiscount 
        : (afterLineDiscounts - generalDiscount) + taxTotal;
    
    // If no items, fallback to the invoice's original amount
    // If no items, fallback to the invoice's original amount
    // If no items, fallback to the transaction amount field or original amount
    const txnAmountEl = document.getElementById('txn_amount');
    const finalDisplayGrandTotal = (invoiceItems.length === 0) 
        ? (txnAmountEl ? (parseFloat(txnAmountEl.value) || 0) : Math.abs(<?php echo (float)$invoice['amount']; ?>)) 
        : grandTotal;

    const subtotalEl = document.getElementById('subtotal');
    if (subtotalEl) subtotalEl.textContent = subtotal.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
    
    const lineDiscEl = document.getElementById('line_discount_total');
    if (lineDiscEl) lineDiscEl.textContent = '-' + lineDiscountTotal.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
    
    const taxTotalEl = document.getElementById('tax_total');
    if (taxTotalEl) taxTotalEl.textContent = taxTotal.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
    
    const gdElem = document.getElementById('general_discount_display');
    if (gdElem) gdElem.textContent = '-' + generalDiscount.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
    
    const grandTotalEl = document.getElementById('grand_total');
    if (grandTotalEl) grandTotalEl.textContent = finalDisplayGrandTotal.toLocaleString('tr-TR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + ' ₺';
};

window.saveInvoice = function() {
    // Manually trigger the form submit handler
    document.getElementById('invoice_form').requestSubmit();
};

document.getElementById('invoice_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Dekont ise items'ı güncelle
    const type = '<?php echo $invoice['type']; ?>';
    if (['borc_dekontu', 'alacak_dekontu'].includes(type)) {
        const amt = parseFloat(document.getElementById('dekont_amount').value) || 0;
        // Mevcut items var mı? Varsa ilkini güncelle, yoksa yeni oluştur
        if (invoiceItems.length > 0) {
            invoiceItems[0].price = amt;
            invoiceItems[0].total = amt;
            invoiceItems[0].quantity = 1;
        } else {
             invoiceItems = [{
                product_id: null,
                product_name: type === 'borc_dekontu' ? 'Borç Dekontu' : 'Alacak Dekontu',
                item_type: 'stok', 
                quantity: 1,
                unit: 'Adet',
                price: amt,
                discount_rate: 0,
                discount_amount: 0,
                tax_rate: 0,
                tax_amount: 0,
                total: amt,
                gorsel: ''
            }];
        }
    }
    
    formData.append('items', JSON.stringify(invoiceItems));
    
    // Add calc totals to formData
    const taxEl = document.getElementById('tax_included');
    const taxIncluded = taxEl ? (taxEl.value === '1') : false;
    
    const discEl = document.getElementById('general_discount');
    const generalDiscount = discEl ? (parseFloat(discEl.value) || 0) : 0;
    let subtotal = 0;
    let lineDiscountTotal = 0;
    invoiceItems.forEach(item => {
        subtotal += (item.quantity * item.price);
        lineDiscountTotal += item.discount_amount;
    });
    
    // Quick Re-calc for net_amount (simplified for submission)
    // Quick Re-calc for net_amount (simplified for submission)
    const grandTotalEl = document.getElementById('grand_total');
    let grandTotal = 0;
    if (grandTotalEl) {
        grandTotal = parseFloat(grandTotalEl.textContent.replace(/[^\d,-]/g, '').replace(',', '.'));
    } else {
        const txnAmountEl = document.getElementById('txn_amount');
        grandTotal = txnAmountEl ? parseFloat(txnAmountEl.value) : 0;
    }

    formData.append('total_amount', subtotal);
    formData.append('discount_amount', lineDiscountTotal + generalDiscount);
    formData.append('general_discount', generalDiscount);
    formData.append('net_amount', grandTotal);
    
    try {
        const response = await fetch('<?php echo site_url('api/save-invoice'); ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.status === 'success') {
            showToast('Fatura başarıyla güncellendi', 'success');
            setTimeout(() => {
                location.reload(); // ID değişmediği için sayfayı yenilemek yeterli
            }, 1000);
        } else {
            showToast('Hata: ' + result.message, 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});

window.deleteInvoice = async function() {
    const confirmed = await showConfirm({
        title: 'Faturayı Sil',
        message: 'Bu faturayı tamamen silmek istediğinizden emin misiniz?',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });
    
    if (!confirmed) return;

    const formData = new FormData();
    formData.append('id', <?php echo $invoice['id']; ?>);
    
    try {
        const response = await fetch('<?php echo site_url('api/delete-invoice'); ?>', { method: 'POST', body: formData });
        const res = await response.json();
        
        if (res.status === 'success') {
            showToast('Fatura başarıyla silindi', 'success');
            setTimeout(() => window.location.href = '<?php echo site_url('invoices'); ?>', 1000);
        } else {
            showToast(res.message, 'error');
        }
    } catch(err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

// Initialize page after all functions are defined
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInvoiceDetailPage);
} else {
    initInvoiceDetailPage();
    
    // Check for edit mode parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit') === 'true') {
        // Slight delay to ensure everything is rendered
        setTimeout(toggleEditMode, 100);
    }
}
</script>
