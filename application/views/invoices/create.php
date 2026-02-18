<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <a href="<?php echo site_url('invoices'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Yeni Fatura Oluştur</h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal ml-9">Alış veya satış faturası ekleyin</p>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <form id="invoice_form" method="POST" class="w-full">
            
            <!-- Fatura Bilgileri -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Fatura Bilgileri
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Fatura No</label>
                        <input type="text" id="invoice_no" name="invoice_no" value="<?php echo $next_invoice_no; ?>" 
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-mono font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tarih</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Vade Tarihi</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tür</label>
                        <select id="invoice_type" name="type" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="purchase">Alış Faturası</option>
                            <option value="sale">Satış Faturası</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">KDV Durumu</label>
                        <select id="tax_included" name="tax_included" onchange="updateTotals()"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="0">KDV Hariç</option>
                            <option value="1">KDV Dahil</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Cari</label>
                        <div class="relative searchable-select z-[60]">
                             <div class="relative">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm pointer-events-none">expand_more</span>
                                <input type="text" class="search-input w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm focus:border-primary transition-all font-bold outline-none text-slate-900 dark:text-white" 
                                       placeholder="Cari Ara..." autocomplete="off">
                                <input type="hidden" name="entity_id" id="entity_id" class="hidden-input">
                            </div>
                            <div class="options-list absolute w-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                <?php foreach($entities as $e): ?>
                                    <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                        <span><?php echo htmlspecialchars($e['name']); ?></span>
                                        <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div id="dekont_amount_div" class="hidden">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tutar</label>
                        <input type="number" id="dekont_amount" min="0" step="0.01" placeholder="0.00"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Ödeme Durumu</label>
                        <select id="payment_status" name="payment_status" onchange="togglePaymentMethod()"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="unpaid">Ödenmedi (Açık)</option>
                            <option value="paid">Ödendi</option>
                        </select>
                    </div>

                    <div id="payment_type_div" class="hidden">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Ödeme Yöntemi</label>
                        <select id="payment_type" name="payment_type" onchange="togglePaymentType()"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="cash_bank">Kasa / Banka</option>
                        </select>
                    </div>

                    <div id="wallet_select_div" class="hidden">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Kasa/Banka</label>
                        <select id="wallet_id" name="wallet_id" 
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer appearance-none">
                            <option value="">Seçiniz...</option>
                            <?php foreach($wallets as $wallet): ?>
                                <option value="<?php echo $wallet['id']; ?>"><?php echo htmlspecialchars($wallet['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="virman_select_div" class="hidden">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Karşı Cari</label>
                        <div class="relative searchable-select z-[60]">
                            <div class="relative">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-sm pointer-events-none">expand_more</span>
                                <input type="text" class="search-input w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm focus:border-primary transition-all font-bold outline-none text-slate-900 dark:text-white" 
                                       placeholder="Cari Ara..." autocomplete="off">
                                <input type="hidden" name="transfer_entity_id" id="transfer_entity_id" class="hidden-input">
                            </div>
                            <div class="options-list absolute w-full mt-2 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-h-64 overflow-y-auto hidden">
                                <?php foreach($entities as $e): ?>
                                    <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" data-value="<?php echo $e['id']; ?>">
                                        <span><?php echo htmlspecialchars($e['name']); ?></span>
                                        <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ürün/Hizmet Ekleme -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-500">inventory_2</span>
                    Kalemler
                </h3>
                
                <!-- Add Item Row -->
                <div class="grid grid-cols-12 gap-2 mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                    <!-- Row 1 -->
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
                                        data-match="<?php echo $product['match_names'] ?? ''; ?>"
                                        data-gorsel="<?php echo $product['gorsel'] ?? ''; ?>">
                                    <?php 
                                        echo htmlspecialchars($product['name']); 
                                        $buyPrice = $product['max_buy_price'] ?? ($product['buying_price'] ?? 0);
                                        if($buyPrice > 0) {
                                            echo ' (Son Alış: ' . number_format($buyPrice, 2, ',', '.') . ' ₺)';
                                        }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                            
                            <?php if(!empty($expense_categories)): ?>
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
                            <?php else: ?>
                                <option value="" disabled data-type="gider" data-price="0" data-unit="Adet">Tanımlı gider kategorisi bulunamadı</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Miktar</label>
                        <input type="number" id="add_quantity" value="1" min="0.01" step="0.01" onfocus="this.select()"
                               class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-center focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Birim</label>
                        <select id="add_unit" class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="Adet">Adet</option>
                            <option value="Kg">Kg</option>
                            <option value="Gram">Gram</option>
                            <option value="Litre">Litre</option>
                            <option value="Metre">Metre</option>
                            <option value="M²">M²</option>
                            <option value="M³">M³</option>
                            <option value="Paket">Paket</option>
                            <option value="Kutu">Kutu</option>
                            <option value="Saat">Saat</option>
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Birim Fiyat</label>
                        <input type="number" id="add_price" value="0" min="0" step="0.01" onfocus="this.select()"
                               class="w-full px-2 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-right focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">İndirim %</label>
                        <input type="number" id="add_discount" value="0" min="0" max="100" onfocus="this.select()"
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
                    <div class="col-span-4 md:col-span-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Satır Tutar</label>
                        <div id="add_line_total" class="w-full px-2 py-3 bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-sm text-right font-black text-slate-700 dark:text-slate-300">0,00 ₺</div>
                    </div>
                    <div class="col-span-4 md:col-span-1 flex items-end">
                        <button type="button" onclick="addItem()" class="w-full px-3 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black transition-all shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Ekle
                        </button>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
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
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr id="no_items_row">
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">Henüz kalem eklenmedi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Toplam -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">İşlem Notu</label>
                        <textarea id="notes" name="notes" rows="5" placeholder="İşlem ile ilgili not ekleyin..."
                                  class="w-full px-6 py-5 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-3xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white leading-relaxed min-h-[150px]"></textarea>
                    </div>
                    
                    <div class="w-full md:w-80 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Ara Toplam</span>
                            <span id="subtotal" class="font-medium text-slate-700 dark:text-slate-300">0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Satır İndirimleri</span>
                            <span id="line_discount_total" class="font-medium text-orange-500">-0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">KDV</span>
                            <span id="tax_total" class="font-medium text-slate-700 dark:text-slate-300">0,00 ₺</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-t border-slate-200 dark:border-slate-700 pt-3">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500">Genel İndirim</span>
                                <input type="number" id="general_discount" value="0" min="0" step="0.01" onchange="updateTotals()" oninput="updateTotals()"
                                       class="w-20 px-2 py-1 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-right focus:border-primary transition-colors">
                                <span class="text-slate-400 text-xs">₺</span>
                            </div>
                            <span id="general_discount_display" class="font-medium text-orange-500">-0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-slate-200 dark:border-slate-700 pt-3">
                            <span class="text-slate-900 dark:text-white">Genel Toplam</span>
                            <span id="grand_total" class="text-primary">0,00 ₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="<?php echo site_url('invoices'); ?>" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors text-center">
                    İptal
                </a>
                <button type="submit" class="px-8 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Faturayı Kaydet
                </button>
            </div>
        </form>
    </main>
</div>

<script>
var invoiceItems = [];

function togglePaymentMethod() {
    const status = document.getElementById('payment_status').value;
    const typeDiv = document.getElementById('payment_type_div');
    
    if (status === 'paid') {
        typeDiv.classList.remove('hidden');
        togglePaymentType();
    } else {
        typeDiv.classList.add('hidden');
        document.getElementById('wallet_select_div').classList.add('hidden');
        document.getElementById('virman_select_div').classList.add('hidden');
    }
}

function togglePaymentType() {
    const type = document.getElementById('payment_type').value;
    const walletDiv = document.getElementById('wallet_select_div');
    const virmanDiv = document.getElementById('virman_select_div');
    
    if (type === 'cash_bank') {
        walletDiv.classList.remove('hidden');
        virmanDiv.classList.add('hidden');
    } else {
        walletDiv.classList.add('hidden');
        virmanDiv.classList.remove('hidden');
    }
}

// Initialize Select2 and event listeners
// Custom Searchable Select Logic
function setupSearchableSelects() {
    document.querySelectorAll('.searchable-select').forEach(container => {
        const input = container.querySelector('.search-input');
        const hiddenInput = container.querySelector('.hidden-input');
        const list = container.querySelector('.options-list');
        const options = list.querySelectorAll('.option-item');

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.options-list').forEach(l => {
                if(l !== list) l.classList.add('hidden');
            });
            list.classList.toggle('hidden');
        });

        input.addEventListener('keyup', (e) => {
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
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = opt.dataset.value;
                const text = opt.querySelector('span') ? opt.querySelector('span').textContent : opt.textContent;
                
                input.value = text.trim();
                hiddenInput.value = value;
                list.classList.add('hidden');
                
                // Trigger any change listeners on the hidden input if needed
                $(hiddenInput).trigger('change');
            });
        });

        document.addEventListener('click', (e) => {
            if(!container.contains(e.target)) {
                list.classList.add('hidden');
            }
        });
    });
}


function initCreatePage() {
    setupSearchableSelects();
    
    // Store all original options HTML for filtering
    let allProductOptions = [];
    
    // Capture options once on load
    $('#add_product option').each(function() {
        if ($(this).val() !== '') { // Skip placeholder
            allProductOptions.push({
                type: $(this).data('type'),
                html: $(this)[0].outerHTML // Store HTML string directly
            });
        }
    });

    const productSelect2Options = {
        placeholder: 'Ürün ara veya yeni ürün yaz...',
        allowClear: true,
        width: '100%',
        tags: true,
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term,
                text: term,
                newTag: true
            }
        },
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const $element = $(data.element);
            const matchNames = ($element.data('match') || '').toString().toLocaleLowerCase("tr-TR");
            const barcode = ($element.data('barcode') || '').toString().toLocaleLowerCase("tr-TR");

            if (text.toLocaleLowerCase("tr-TR").indexOf(term) > -1 || barcode.indexOf(term) > -1 || matchNames.indexOf(term) > -1 || data.newTag) {
                return data;
            }
            return null;
        }
    };

    $('#add_product').select2(productSelect2Options);
    
    // Filter products when type changes
    $('#add_item_type').on('change', function() {
        const selectedType = $(this).val();
        
        // Use setTimeout to allow UI to update immediately
        setTimeout(() => {
            const productSelect = $('#add_product');
            
            // Destroy select2 to manipulate DOM safely
            if (productSelect.hasClass("select2-hidden-accessible")) {
                productSelect.select2('destroy');
            }
            
            // Clear current options completely
            productSelect.empty();
            productSelect.append('<option value="">Ürün ara...</option>');
            
            // Append filtered options from HTML strings
            allProductOptions.forEach(function(opt) {
                // Check type match
                if (opt.type === selectedType) {
                    productSelect.append(opt.html);
                }
            });
            
            // Re-initialize Select2
            productSelect.select2(productSelect2Options);
            
            // Reset selection
            productSelect.val('').trigger('change');
        }, 100);
    });
    
    // Trigger initial filter (after a slight delay to ensure Select2 is ready if needed, but sync is fine)
    setTimeout(() => {
        const val = $('#add_item_type').val() || 'stok'; // Default fallback
        $('#add_item_type').val(val).trigger('change');
    }, 100);
    
    // When product selected, fill price, unit, type and calculate line total
    $('#add_product').on('change', function() {
        const val = $(this).val();
        if (!val) return; // Ignore if selection is cleared (prevent type reset)

        const selected = $(this).find(':selected');
        const price = selected.data('price') || 0;
        const unit = selected.data('unit') || 'Adet';
        const type = selected.data('type') || 'stok';
        
        document.getElementById('add_price').value = price;
        document.getElementById('add_unit').value = unit;
        document.getElementById('add_item_type').value = type;
        calculateLineTotal();
    });
    
    // Add event listeners for live line total calculation
    ['add_quantity', 'add_price', 'add_discount'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculateLineTotal);
    });
    
    // For select elements use change event
    document.getElementById('add_tax').addEventListener('change', calculateLineTotal);
    document.getElementById('tax_included').addEventListener('change', calculateLineTotal);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCreatePage);
} else {
    initCreatePage();
}

function calculateLineTotal() {
    const quantity = parseFloat(document.getElementById('add_quantity').value) || 0;
    const price = parseFloat(document.getElementById('add_price').value) || 0;
    const discountRate = parseFloat(document.getElementById('add_discount').value) || 0;
    const taxRate = parseFloat(document.getElementById('add_tax').value) || 0;
    const isTaxIncluded = document.getElementById('tax_included').value == '1';
    
    const subtotal = quantity * price;
    const discountAmount = subtotal * (discountRate / 100);
    const afterDiscount = subtotal - discountAmount;
    
    let taxAmount, total;

    if (isTaxIncluded) {
        // Price includes tax
        total = afterDiscount;
        // taxAmount = Total - (Total / 1.taxRate)
        taxAmount = total - (total / (1 + (taxRate / 100)));
    } else {
        // Price excludes tax
        taxAmount = afterDiscount * (taxRate / 100);
        total = afterDiscount + taxAmount;
    }
    
    document.getElementById('add_line_total').textContent = total.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
}

function addItem() {
    const productSelect = document.getElementById('add_product');
    let productId = productSelect.value;
    
    // Handle Expense ID prefix
    if (productId && typeof productId === 'string' && productId.startsWith('EXP_')) {
        productId = productId.substring(4);
    }
    
    // Get text from Select2 for both existing and new tags
    // Get text from Select2 for both existing and new tags
    const selectedOption = $(productSelect).find(':selected');
    const productName = selectedOption.text() || '';
    const gorsel = selectedOption.data('gorsel') || '';
    const itemType = document.getElementById('add_item_type').value;
    const quantity = parseFloat(document.getElementById('add_quantity').value) || 0;
    const unit = document.getElementById('add_unit').value;
    const price = parseFloat(document.getElementById('add_price').value) || 0;
    const discountRate = parseFloat(document.getElementById('add_discount').value) || 0;
    const taxRate = parseFloat(document.getElementById('add_tax').value) || 0;
    
    if (!productId || quantity <= 0) {
        showToast('Lütfen ürün seçin ve miktar girin', 'info');
        return;
    }
    
    const lineSubtotal = quantity * price;
    const discountAmount = lineSubtotal * (discountRate / 100);
    const afterDiscount = lineSubtotal - discountAmount;
    
    const isTaxIncluded = document.getElementById('tax_included').value == '1';
    let taxAmount, total;

    if (isTaxIncluded) {
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
    
    // Reset form
    $('#add_product').val('').trigger('change');
    document.getElementById('add_quantity').value = 1;
    document.getElementById('add_price').value = 0;
    document.getElementById('add_discount').value = 0;
    document.getElementById('add_tax').value = '20';
    document.getElementById('add_unit').value = 'Adet';
    document.getElementById('add_item_type').value = 'stok';
    document.getElementById('add_line_total').textContent = '0,00 ₺';
}

function removeItem(index) {
    invoiceItems.splice(index, 1);
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('items_tbody');
    
    if (invoiceItems.length === 0) {
        tbody.innerHTML = '<tr id="no_items_row"><td colspan="9" class="px-4 py-8 text-center text-slate-500">Henüz kalem eklenmedi</td></tr>';
        updateTotals();
        return;
    }
    
    tbody.innerHTML = invoiceItems.map((item, index) => `
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
                <span class="px-2 py-1 text-xs font-bold rounded ${item.item_type === 'stok' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'}">
                    ${item.item_type === 'stok' ? 'STOK' : 'GİDER'}
                </span>
            </td>
            <td class="px-3 py-2 text-sm font-medium text-slate-900 dark:text-white">${item.product_name}</td>
            <td class="px-3 py-2 text-sm text-center text-slate-600 dark:text-slate-400">${item.quantity}</td>
            <td class="px-3 py-2 text-sm text-center text-slate-500">${item.unit}</td>
            <td class="px-3 py-2 text-sm text-right text-slate-600 dark:text-slate-400">${item.price.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
            <td class="px-3 py-2 text-sm text-center ${item.discount_rate > 0 ? 'text-orange-500 font-medium' : 'text-slate-400'}">
                ${item.discount_rate > 0 ? '%' + item.discount_rate : '-'}
            </td>
            <td class="px-3 py-2 text-sm text-center text-slate-600 dark:text-slate-400">%${item.tax_rate}</td>
            <td class="px-3 py-2 text-sm text-right font-medium text-slate-900 dark:text-white">${item.total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
            <td class="px-3 py-2 text-center">
                <button type="button" onclick="removeItem(${index})" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
    
    updateTotals();
}

function updateTotals() {
    const taxIncluded = document.getElementById('tax_included').value === '1';
    const generalDiscount = parseFloat(document.getElementById('general_discount').value) || 0;
    
    let subtotal = 0; // Toplam Brüt (İndirimsiz, KDV'li/siz fiyat toplamı)
    let lineDiscountTotal = 0;
    let taxTotal = 0;
    
    invoiceItems.forEach(item => {
        const lineBrut = item.quantity * item.price;
        subtotal += lineBrut;
        lineDiscountTotal += item.discount_amount;
    });

    // Ara Toplam (Satır indirimleri sonrası)
    let afterLineDiscounts = subtotal - lineDiscountTotal;
    
    // Genel indirimi satırlara oransal dağıtarak KDV hesaplayacağız (En doğru muhasebe yöntemi)
    if (afterLineDiscounts > 0) {
        const generalDiscountRatio = generalDiscount / afterLineDiscounts;
        
        invoiceItems.forEach(item => {
            const itemNetAfterLine = (item.quantity * item.price) - item.discount_amount;
            const itemShareOfGeneralDiscount = itemNetAfterLine * generalDiscountRatio;
            const itemNetFinal = itemNetAfterLine - itemShareOfGeneralDiscount;
            
            let itemTax = 0;
            if (taxIncluded) {
                // KDV Dahil fiyattan iç yüzde ile KDV ayır
                itemTax = itemNetFinal - (itemNetFinal / (1 + (item.tax_rate / 100)));
            } else {
                // KDV Hariç fiyata KDV ekle
                itemTax = itemNetFinal * (item.tax_rate / 100);
            }
            taxTotal += itemTax;
        });
    }

    let grandTotal = 0;
    if (taxIncluded) {
        grandTotal = afterLineDiscounts - generalDiscount;
    } else {
        grandTotal = (afterLineDiscounts - generalDiscount) + taxTotal;
    }
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('line_discount_total').textContent = '-' + lineDiscountTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('tax_total').textContent = taxTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('general_discount_display').textContent = '-' + generalDiscount.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('grand_total').textContent = grandTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
}

// Form Submit
// Form Submit
document.getElementById('invoice_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (invoiceItems.length === 0) {
        showToast('Lütfen en az bir kalem ekleyin', 'info');
        return;
    }
    
    const taxIncluded = document.getElementById('tax_included').value === '1';
    const generalDiscount = parseFloat(document.getElementById('general_discount').value) || 0;
    
    let subtotal = 0;
    let lineDiscountTotal = 0;
    let taxTotal = 0;
    
    invoiceItems.forEach(item => {
        subtotal += (item.quantity * item.price);
        lineDiscountTotal += item.discount_amount;
    });

    let afterLineDiscounts = subtotal - lineDiscountTotal;
    
    // Submit öncesi son KDV hesaplaması (Oransal dağıtım ile)
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

    const formData = new FormData();
    formData.append('invoice_no', document.getElementById('invoice_no').value);
    formData.append('invoice_date', document.getElementById('invoice_date').value);
    formData.append('due_date', document.getElementById('due_date').value);
    formData.append('type', document.getElementById('invoice_type').value);
    formData.append('entity_id', document.getElementById('entity_id').value);
    formData.append('tax_included', taxIncluded ? 1 : 0);
    
    formData.append('total_amount', subtotal); // Brüt
    formData.append('discount_amount', lineDiscountTotal + generalDiscount); // Toplam İndirim
    formData.append('tax_amount', taxTotal);
    formData.append('net_amount', grandTotal);
    formData.append('notes', document.getElementById('notes').value);
    formData.append('payment_status', document.getElementById('payment_status').value);
    formData.append('payment_type', document.getElementById('payment_type').value);
    formData.append('wallet_id', document.getElementById('wallet_id').value);
    formData.append('transfer_entity_id', document.getElementById('transfer_entity_id').value);
    
    // Add extra fields implicitly in items json
    formData.append('items', JSON.stringify(invoiceItems));
    
    try {
        const response = await fetch('<?php echo site_url('api/save-invoice'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Fatura başarıyla kaydedildi');
            setTimeout(() => {
                const targetUrl = '<?php echo site_url('invoices/detail/'); ?>' + result.invoice_id;
                if (typeof navigateTo === 'function') {
                    navigateTo(targetUrl);
                } else {
                    window.location.href = targetUrl;
                }
            }, 1000);
        } else {
            showToast('Hata: ' + (result.message || 'Bir sorun oluştu'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});
</script>
