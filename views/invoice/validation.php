<?php
// views/invoice/validation.php
$pageTitle = "Fatura Doğrulama";
include __DIR__ . '/../../views/layout/header.php';

// Data passed from controller: $invoiceData, $suppliers, $invoiceId, $units
$units = $units ?? ['Adet', 'Metre', 'Kg', 'Litre', 'Paket', 'Koli', 'M'];
$suppliers = $suppliers ?? [];
?>

<div class="container mx-auto px-4 py-8 pb-32">
    <!-- Breadcrumb & Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <nav class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                <a href="<?php echo public_url('dashboard'); ?>" class="hover:text-primary transition-colors">Ana Sayfa</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-white">Fatura İşlemleri</span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white shadow-lg shadow-blue-500/30">
                    <span class="material-symbols-outlined text-[28px]">fact_check</span>
                </span>
                Fatura Doğrulama
            </h1>
        </div>
    </div>

    <!-- Debug Section: Raw AI Response (Top) -->
    <?php if (isset($invoiceData['_raw_response'])): ?>
    <div class="mb-8">
        <div class="bg-gray-900 rounded-xl shadow-xl border border-gray-800 overflow-hidden">
            <button type="button" onclick="document.getElementById('debugPanel').classList.toggle('hidden')" 
                    class="w-full px-6 py-3 flex items-center justify-between text-gray-400 hover:bg-gray-800/50 transition-all active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-500">bug_report</span>
                    <span class="font-bold text-xs uppercase tracking-widest">Yapay Zeka Analiz Detayı (Debug)</span>
                    <span class="px-2 py-0.5 bg-gray-800 rounded text-[10px] text-gray-500 border border-gray-700">Model: <?php echo $invoiceData['_model_used'] ?? 'Bilinmiyor'; ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-500 italic">Okunan satır sayısı: <?php echo count($invoiceData['items'] ?? []); ?></span>
                    <span class="material-symbols-outlined text-[20px]">expand_more</span>
                </div>
            </button>
            <div id="debugPanel" class="hidden border-t border-gray-800 bg-black/20">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] text-gray-500 uppercase font-bold tracking-tight">Ham JSON Çıktısı</span>
                        <button type="button" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText)" class="text-[10px] text-blue-500 hover:underline">Metni Kopyala</button>
                    </div>
                    <pre class="text-[11px] font-mono text-green-400 bg-black/40 p-4 rounded-lg overflow-x-auto border border-gray-800 leading-relaxed shadow-inner"><code><?php echo htmlspecialchars(json_encode(json_decode($invoiceData['_raw_response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <form action="<?php echo public_url('invoice/store'); ?>" method="POST" id="invoiceForm">
        <input type="hidden" name="folder_id" value="<?php echo $invoiceData['id'] ?? ''; ?>">
        
        <!-- Top Bar: Invoice Meta & Type Selection -->
        <div class="bg-white dark:bg-card-dark rounded-xl shadow-lg border border-gray-100 dark:border-gray-700/50 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-6 mb-6 pb-6 border-b border-gray-100 dark:border-gray-800">
                <div class="flex-shrink-0">
                    <label class="block text-xs font-black text-gray-500 uppercase mb-2 tracking-widest">İşlem Türü</label>
                    <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="invoice_type" value="ALIS" class="peer hidden" checked onchange="updateTypeStyles('ALIS')">
                            <div class="px-6 py-2 rounded-lg text-sm font-black transition-all peer-checked:bg-white dark:peer-checked:bg-gray-700 peer-checked:text-emerald-600 peer-checked:shadow-sm text-gray-500">
                                <span class="material-symbols-outlined align-middle text-[18px] mr-1">download</span>
                                ALIŞ FATURASI
                            </div>
                        </label>
                        <label class="relative cursor-pointer ml-1">
                            <input type="radio" name="invoice_type" value="SATIS" class="peer hidden" onchange="updateTypeStyles('SATIS')">
                            <div class="px-6 py-2 rounded-lg text-sm font-black transition-all peer-checked:bg-white dark:peer-checked:bg-gray-700 peer-checked:text-rose-600 peer-checked:shadow-sm text-gray-500">
                                <span class="material-symbols-outlined align-middle text-[18px] mr-1">upload</span>
                                SATIŞ FATURASI
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex-1">
                    <div id="typeInfo" class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 flex items-center gap-3">
                        <span class="p-1.5 bg-emerald-500 rounded-lg text-white material-symbols-outlined text-[18px]">inventory_2</span>
                        <div>
                            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-400" id="typeTitle">Alış Faturası Modu</p>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-500" id="typeDesc">Ürünler stoğa eklenecek ve maliyet ortalaması güncellenecektir.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                
                <!-- Supplier Info -->
                <div class="md:col-span-4" id="supplierSection">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tedarikçi / Cari</label>
                        <button type="button" id="staffModeBtn" onclick="toggleStaffMode()" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
                            <span class="material-symbols-outlined text-[14px] mr-1">badge</span>
                            Personel Masrafı mı?
                        </button>
                    </div>
                    
                    <!-- Supplier Input Group -->
                    <div id="supplierInputGroup" class="relative group">
                        <span class="absolute left-3 top-2.5 text-gray-400 material-symbols-outlined text-[20px]">store</span>
                        <input type="text" name="supplier_name" id="supplierNameInput" 
                               class="w-full pl-10 pr-4 py-2.5 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-medium transition-all shadow-sm group-hover:border-gray-300 dark:group-hover:border-gray-600" 
                               value="<?php echo htmlspecialchars($invoiceData['supplier_name'] ?? ''); ?>" required placeholder="Veya yeni cari adı yazın...">
                        
                        <!-- Datalist for autocomplete -->
                        <datalist id="suppliersList">
                            <?php foreach($suppliers as $sup): ?>
                                <option value="<?php echo htmlspecialchars($sup['name']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <input type="hidden" name="supplier_tax_id" value="<?php echo htmlspecialchars($invoiceData['supplier_tax_id'] ?? ''); ?>">
                        
                        <input type="hidden" name="entity_type" id="entityTypeInput" value="supplier">
                    </div>

                    <!-- Staff Input Group (Hidden by default) -->
                    <div id="staffInputGroup" class="relative group hidden">
                        <span class="absolute left-3 top-2.5 text-gray-400 material-symbols-outlined text-[20px]">person</span>
                        <select name="staff_id" id="staffSelect" onchange="selectStaff(this)" class="w-full pl-10 pr-4 py-2.5 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-medium shadow-sm appearance-none">
                            <option value="">Personel Seçiniz...</option>
                            <?php 
                            // $suppliers contains all entities, filter for staff or rely on controller to pass staff list
                             foreach($suppliers as $sup): 
                                 if($sup['type'] == 'staff'):
                            ?>
                                <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                            <?php 
                                 endif;
                            endforeach; 
                            ?>
                        </select>
                        <span class="absolute right-3 top-3 text-gray-400 material-symbols-outlined text-[18px] pointer-events-none">expand_more</span>
                    </div>
                </div>

                <!-- Invoice Meta Grid -->
                <div class="md:col-span-8 grid grid-cols-1 md:grid-cols-8 gap-4">
                    
                    <!-- Tarih (Col 2) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Fatura Tarihi</label>
                        <input type="date" name="invoice_date" class="w-full px-3 py-2.5 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all shadow-sm" 
                               value="<?php echo htmlspecialchars($invoiceData['invoice_date'] ?? date('Y-m-d')); ?>" required>
                    </div>

                    <!-- Fatura No (Col 2) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Fatura No</label>
                        <input type="text" name="invoice_no" class="w-full px-3 py-2.5 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-mono tracking-wide transition-all shadow-sm" 
                               value="<?php echo htmlspecialchars($invoiceData['invoice_no'] ?? ''); ?>" placeholder="ABC2024..." required>
                    </div>

                    <!-- Tutar (Col 2) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Genel Toplam</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="total_amount" id="totalAmountInput" class="w-full px-3 py-2.5 border dark:border-border-dark rounded-lg font-mono font-bold bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm text-right pr-8 transition-all shadow-sm" 
                                   value="<?php echo htmlspecialchars($invoiceData['total_amount'] ?? '0.00'); ?>" required readonly>
                            <span class="absolute right-3 top-2.5 text-gray-500 dark:text-gray-400 text-sm font-bold">₺</span>
                        </div>
                    </div>

                    <!-- Ödeme Durumu (Col 2) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Ödeme Yöntemi</label>
                        <select name="payment_source" onchange="togglePaymentExtra(this)" class="w-full px-3 py-2.5 border dark:border-border-dark rounded-lg bg-white dark:bg-input-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm shadow-sm font-bold">
                            <option value="unpaid" selected>Cariye İşle (Açık)</option>
                            <?php if(!empty($wallets)): ?>
                                <optgroup label="Hesaplar / Kartlar">
                                    <?php foreach($wallets as $w): ?>
                                        <option value="wallet_<?php echo $w['id']; ?>" data-type="wallet">
                                            <?php echo htmlspecialchars($w['name'] . ' (' . $w['owner_name'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <option value="transfer">Virman (Başka Cari Ödedi)</option>
                        </select>

                        <!-- Virman İçin Cari Seçimi -->
                        <div id="transferExtra" class="mt-2 hidden">
                            <label class="block text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">Ödeyen Cariyi Yazın / Seçin</label>
                            <input type="text" name="transfer_entity_name" list="entitiesList" 
                                   class="w-full px-3 py-2 border dark:border-gray-700 rounded-lg bg-blue-50/50 dark:bg-blue-900/20 text-gray-900 dark:text-white text-xs font-bold focus:ring-1 focus:ring-blue-500 outline-none" 
                                   placeholder="Ödemeyi yapan firma/kişi adı...">
                            
                            <datalist id="entitiesList">
                                <?php foreach($suppliers as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['name']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white dark:bg-card-dark rounded-xl shadow-lg border border-gray-100 dark:border-gray-700/50 p-6 mb-8">
             <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">list</span>
                    Fatura Kalemleri
                </h3>
                <div class="text-xs text-gray-500 bg-blue-50 dark:bg-blue-900/10 px-3 py-1 rounded-full border border-blue-100 dark:border-blue-800/30">
                    <span class="font-bold text-blue-600 dark:text-blue-400">İpucu:</span> Birim fiyat iskontolu net fiyat olmalıdır.
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border dark:border-gray-700 shadow-sm">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-surface-dark text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            <th class="p-3 border-b dark:border-border-dark w-[8%]">Tür</th>
                            <th class="p-3 border-b dark:border-border-dark w-[22%]">Kalem Adı (OCR)</th>
                            <th class="p-3 border-b dark:border-border-dark w-[20%]">Eşleşen Stok/Gider</th>
                            <th class="p-3 border-b dark:border-border-dark text-right w-[8%]">Miktar</th>
                            <th class="p-3 border-b dark:border-border-dark text-center w-[8%]">Birim</th>
                            <th class="p-3 border-b dark:border-border-dark text-right w-[10%]">Net B.Fiyat</th>
                            <th class="p-3 border-b dark:border-border-dark text-center w-[8%]">KDV %</th>
                            <th class="p-3 border-b dark:border-border-dark text-right w-[10%]">Satır Toplamı</th>
                            <th class="p-3 border-b dark:border-border-dark w-[4%]"></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceItems" class="text-gray-800 dark:text-gray-200 text-sm">
                        <?php if(isset($invoiceData['items'])): ?>
                            <?php foreach($invoiceData['items'] as $index => $item): ?>
                            <tr class="border-b dark:border-border-dark hover:bg-blue-50 dark:hover:bg-blue-900/10 group transition-colors item-row">
                                <td class="p-2">
                                    <select name="items[<?php echo $index; ?>][type]" class="bg-transparent border border-gray-200 dark:border-gray-700 rounded px-1 py-1.5 text-xs font-bold <?php echo $item['type'] === 'GIDER' ? 'text-red-600' : 'text-green-600'; ?> focus:ring-1 focus:border-primary cursor-pointer w-full">
                                        <option value="STOK" <?php echo $item['type'] === 'STOK' ? 'selected' : ''; ?>>STOK</option>
                                        <option value="GIDER" <?php echo $item['type'] === 'GIDER' ? 'selected' : ''; ?>>GİDER</option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="text" name="items[<?php echo $index; ?>][raw_name]" 
                                           class="w-full bg-transparent border-b border-dashed border-gray-300 dark:border-gray-600 dark:text-gray-100 focus:border-primary outline-none px-1 py-1.5" 
                                           value="<?php echo htmlspecialchars($item['name']); ?>">
                                </td>
                                <td class="p-2">
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" name="items[<?php echo $index; ?>][mapped_id]" class="mapped-id" value="<?php echo $item['mapped_id'] ?? ''; ?>">
                                        
                                        <?php if(isset($item['mapped_id'])): ?>
                                            <span class="mapped-name font-semibold text-primary truncate block w-full"><?php echo htmlspecialchars($item['mapped_name']); ?></span>
                                            <button type="button" class="btn-map text-xs text-gray-400 hover:text-blue-500 hidden hover:underline" onclick="openMappingModal(this)" title="Değiştir">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-map w-full py-1.5 border border-dashed border-blue-300 text-blue-500 rounded hover:bg-blue-50 hover:border-blue-500 transition-colors flex items-center justify-center gap-1 text-xs font-medium" onclick="openMappingModal(this)">
                                                <span class="material-symbols-outlined text-[14px]">link</span>
                                                Eşleştir
                                            </button>
                                            <span class="mapped-name font-semibold text-primary truncate block w-full hidden"></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" name="items[<?php echo $index; ?>][quantity]" 
                                           class="row-qty w-full text-right bg-transparent border border-gray-200 dark:border-gray-700 rounded px-2 py-1.5 focus:border-primary focus:ring-1 outline-none" 
                                           value="<?php echo $item['quantity']; ?>" onchange="calculateTotals()">
                                </td>
                                <td class="p-2 text-center relative group-td">
                                    <select name="items[<?php echo $index; ?>][unit]" class="w-full bg-transparent border border-gray-200 dark:border-gray-700 rounded px-1 py-1.5 text-xs focus:ring-1 focus:border-primary">
                                        <?php foreach ($units as $u): ?>
                                            <option value="<?php echo $u; ?>" <?php echo ((isset($item['unit']) && mb_strtolower($item['unit']) == mb_strtolower($u)) ? 'selected' : ''); ?>>
                                                <?php echo $u; ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php 
                                            // Handle dynamically added unit if not in array
                                            $u_lower = array_map('mb_strtolower', $units);
                                            if(!empty($item['unit']) && !in_array(mb_strtolower($item['unit']), $u_lower)):
                                        ?>
                                             <option value="<?php echo $item['unit']; ?>" selected><?php echo $item['unit']; ?></option>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td class="p-2 relative">
                                    <input type="number" step="0.01" name="items[<?php echo $index; ?>][unit_price]" 
                                           class="row-price w-full text-right bg-transparent border border-gray-200 dark:border-gray-700 rounded px-2 py-1.5 focus:border-primary focus:ring-1 outline-none font-bold" 
                                           value="<?php echo $item['unit_price']; ?>" onchange="calculateTotals()">
                                     <span class="absolute right-8 top-2 text-xs text-gray-400 pointer-events-none">₺</span>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="1" min="0" max="100" name="items[<?php echo $index; ?>][tax_rate]" 
                                           class="row-tax w-full text-center bg-transparent border border-gray-200 dark:border-gray-700 rounded px-2 py-1.5 focus:border-primary focus:ring-1 outline-none text-xs" 
                                           value="<?php echo $item['tax_rate'] ?? 20; ?>" onchange="calculateTotals()">
                                </td>
                                <td class="p-2 text-right font-medium row-total">
                                    <?php echo number_format($item['total_price'] ?? ($item['quantity'] * $item['unit_price']), 2, ',', '.'); ?> ₺
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeRow(this)" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals Section -->
            <div class="flex justify-end mt-4 pt-4 border-t dark:border-gray-700">
                <div class="w-full md:w-1/3 space-y-3">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Ara Toplam (KDV Hariç):</span>
                        <span id="subTotalDisplay" class="font-mono">0.00 ₺</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Toplam KDV:</span>
                        <span id="totalTaxDisplay" class="font-mono text-red-600 dark:text-red-400">0.00 ₺</span>
                    </div>
                     <div class="flex justify-between items-center pt-3 border-t dark:border-gray-700">
                        <span class="font-bold text-lg text-gray-800 dark:text-white">Genel Toplam:</span>
                        <span id="grandTotalDisplay" class="font-bold text-2xl text-primary font-mono">0.00 ₺</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4 bg-white dark:bg-card-dark p-4 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700/50 sticky bottom-6 z-30">
             <a href="<?php echo public_url('dashboard'); ?>" class="w-full md:w-auto px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-bold transition-all text-center">
                İptal Et
            </a>
            <div class="flex gap-4 w-full md:w-auto">
                 <button type="button" class="hidden md:flex items-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-bold transition-all">
                    <span class="material-symbols-outlined">add</span>
                    Satır Ekle
                 </button>
                <button type="submit" class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:shadow-lg hover:shadow-blue-500/30 font-bold transition-all flex items-center justify-center gap-2 transform active:scale-95">
                    <span class="material-symbols-outlined">save</span>
                    Kaydet ve Stoklara İşle
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Stock Mapping Modal -->
<div id="mappingModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-lg transform transition-all scale-100 p-6">
        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary" id="modalIcon">find_in_page</span>
            <span id="modalTitleText">Ürün Eşleştirme</span>
        </h3>
        <p class="text-sm text-gray-500 mb-6">
             "<span id="modalRawName" class="font-bold text-gray-800 dark:text-gray-200"></span>" için <span id="modalTargetText">stok kartı</span> seçin:
        </p>
        
        <div class="mb-6">
            <select id="stockSelector" class="w-full p-3 border rounded-lg select2-enable"></select>
        </div>

        <div id="newCategoryArea" class="mb-6 hidden">
            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase mb-2">Veya Yeni Gider Kategorisi Ekle</label>
            <div class="flex gap-2">
                <input type="text" id="newCategoryName" class="flex-1 px-3 py-2 border rounded-lg dark:bg-input-dark dark:border-border-dark dark:text-white outline-none focus:ring-1 focus:ring-primary" placeholder="Kategori adı...">
                <button type="button" onclick="saveQuickCategory()" class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">Ekle</button>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
            <button onclick="closeModal()" class="px-5 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg font-semibold transition-colors">Vazgeç</button>
            <button onclick="confirmMapping()" class="px-6 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-blue-700 hover:shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">check</span>
                Seçimi Onayla
            </button>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let activeBtn = null;

$(document).ready(function() {
    // Initial calculation
    calculateTotals();

    $('.select2-enable').select2({
        dropdownParent: $('#mappingModal'),
        width: '100%',
        minimumInputLength: 2,
        placeholder: 'Ürün adı veya barkod ile arayın...',
        ajax: {
            url: '<?php echo public_url('api/search-stock'); ?>', 
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) {
                if (data.status === 'success') {
                    return {
                        results: data.items.map(function(item) {
                            return {
                                id: item.id,
                                text: item.urun_adi + ' (' + (item.barcode || '-') + ') - Stok: ' + item.miktar + ' ' + item.birim,
                                additional: { unit: item.birim, stock: item.miktar }
                            };
                        })
                    };
                }
                return { results: [] };
            },
            cache: true
        }
    });
});

function calculateTotals() {
    let subTotal = 0;
    let totalTax = 0;
    let grandTotal = 0;

    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
        const price = parseFloat(row.querySelector('.row-price').value) || 0;
        const taxRate = parseFloat(row.querySelector('.row-tax').value) || 0;
        
        // Row total (Net)
        const rowNet = qty * price;
        const rowTax = rowNet * (taxRate / 100);
        const rowTotal = rowNet + rowTax;
        
        // Update row total display (Net Amount usually shown, but maybe Total is better)
        // Let's show Row Total (Inc. Tax) or Net? Usually Invoice Lines show Net Total.
        // But the input asked for Discounted Price.
        // Let's go with: Price is Net. Total is Net. Tax is calculated globally or per line.
        // Let's display Net Total in row for clarity.
        
        row.querySelector('.row-total').textContent = rowNet.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
        
        subTotal += rowNet;
        totalTax += rowTax;
    });

    grandTotal = subTotal + totalTax;

    document.getElementById('subTotalDisplay').textContent = subTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('totalTaxDisplay').textContent = totalTax.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('grandTotalDisplay').textContent = grandTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    
    // Update hidden input for form submission
    document.getElementById('totalAmountInput').value = grandTotal.toFixed(2);
}

function removeRow(btn) {
    if(document.querySelectorAll('.item-row').length > 1) {
        btn.closest('tr').remove();
        calculateTotals();
    } else {
        alert('En az bir kalem kalmalıdır.');
    }
}

function openMappingModal(btn) {
    activeBtn = btn;
    const row = btn.closest('tr');
    const type = row.querySelector('select[name*="[type]"]').value || 'STOK';
    const rawName = row.querySelector('input[name*="[raw_name]"]').value;
    
    document.getElementById('modalRawName').innerText = rawName;
    
    const selector = $('#stockSelector');
    if (type === 'GIDER') {
        document.getElementById('modalTitleText').innerText = 'Gider Kategorisi Eşleştir';
        document.getElementById('modalTargetText').innerText = 'gider kategorisi';
        document.getElementById('modalIcon').innerText = 'account_tree';
        document.getElementById('newCategoryArea').classList.remove('hidden');
        
        selector.select2({
            dropdownParent: $('#mappingModal'),
            width: '100%',
            placeholder: 'Gider kategorisi arayın...',
            ajax: {
                url: '<?php echo public_url('api/search-expense-categories'); ?>',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data.items.map(i => ({id: i.id, text: i.name})) };
                }
            }
        });
    } else {
        document.getElementById('modalTitleText').innerText = 'Ürün Eşleştirme';
        document.getElementById('modalTargetText').innerText = 'stok kartı';
        document.getElementById('modalIcon').innerText = 'find_in_page';
        document.getElementById('newCategoryArea').classList.add('hidden');
        
        selector.select2({
            dropdownParent: $('#mappingModal'),
            width: '100%',
            placeholder: 'Ürün adı veya barkod ile arayın...',
            ajax: {
                url: '<?php echo public_url('api/search-stock'); ?>',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data.items.map(i => ({id: i.id, text: i.urun_adi + ' (' + (i.barcode || '-') + ') - Stok: ' + i.miktar + ' ' + i.birim, additional: { unit: i.birim }})) };
                }
            }
        });
    }

    document.getElementById('mappingModal').classList.remove('hidden');
    selector.val(null).trigger('change');
    setTimeout(() => { selector.select2('open'); }, 100);
}

async function saveQuickCategory() {
    const name = document.getElementById('newCategoryName').value;
    if (!name) return;
    
    try {
        const res = await fetch('<?php echo public_url('api/save-expense-category'); ?>', {
            method: 'POST',
            body: JSON.stringify({ name: name })
        });
        const data = await res.json();
        if (data.status === 'success') {
            document.getElementById('newCategoryName').value = '';
            $('#stockSelector').val(null).trigger('change');
            alert('Kategori eklendi, şimdi listeden arayıp seçebilirsiniz.');
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert('Hata: ' + e.message);
    }
}

function closeModal() {
    document.getElementById('mappingModal').classList.add('hidden');
    activeBtn = null;
}

function confirmMapping() {
    if (!activeBtn) return;
    const selectedId = $('#stockSelector').val();
    const data = $('#stockSelector').select2('data')[0];
    
    if (selectedId && data) {
        const container = activeBtn.closest('td');
        const row = activeBtn.closest('tr');
        
        container.querySelector('.mapped-id').value = selectedId;
        const mappedNameEl = container.querySelector('.mapped-name');
        mappedNameEl.innerHTML = '<span class="material-symbols-outlined text-sm align-middle mr-1">link</span>' + data.text.split(' - ')[0]; 
        mappedNameEl.classList.remove('hidden');
        activeBtn.classList.add('hidden');
        
        if (data.additional && data.additional.unit) {
             const unitSelect = row.querySelector('select[name*="[unit]"]');
             if(unitSelect) { // Basic unit selection logic 
                 // ... existing logic ...
                 let found = false;
                 Array.from(unitSelect.options).forEach((opt, i) => {
                     if(opt.value.toLowerCase() === data.additional.unit.toLowerCase()) {
                         unitSelect.selectedIndex = i; found = true;
                     }
                 });
             }
        }
    }
    closeModal();
}

function toggleStaffMode() {
    const supplierGroup = document.getElementById('supplierInputGroup');
    const staffGroup = document.getElementById('staffInputGroup');
    const typeInput = document.getElementById('entityTypeInput');
    const btn = document.getElementById('staffModeBtn');
    
    if (staffGroup.classList.contains('hidden')) {
        supplierGroup.classList.add('hidden');
        staffGroup.classList.remove('hidden');
        typeInput.value = 'staff';
        btn.innerHTML = '<span class="material-symbols-outlined text-[14px] mr-1">business</span>Normal Tedarikçi Faturası';
    } else {
        staffGroup.classList.add('hidden');
        supplierGroup.classList.remove('hidden');
        typeInput.value = 'supplier';
        btn.innerHTML = '<span class="material-symbols-outlined text-[14px] mr-1">badge</span>Personel Masrafı mı?';
    }
}

function selectStaff(select) {
    const name = select.selectedOptions[0].text.trim();
    document.getElementById('supplierNameInput').value = name;
}

function togglePaymentExtra(select) {
    const transferExtra = document.getElementById('transferExtra');
    if (select.value === 'transfer') {
        transferExtra.classList.remove('hidden');
    } else {
        transferExtra.classList.add('hidden');
    }
}

function updateTypeStyles(type) {
    const infoBox = document.getElementById('typeInfo');
    const title = document.getElementById('typeTitle');
    const desc = document.getElementById('typeDesc');
    const icon = infoBox.querySelector('.material-symbols-outlined');
    const saveBtn = document.querySelector('button[type="submit"]');

    if (type === 'SATIS') {
        infoBox.className = 'p-3 rounded-xl bg-rose-50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-800/30 flex items-center gap-3';
        title.className = 'text-xs font-bold text-rose-800 dark:text-rose-400';
        title.innerText = 'Satış Faturası Modu';
        desc.className = 'text-[10px] text-rose-600 dark:text-rose-500';
        desc.innerText = 'Ürünler stoktan çıkarılacak, cari alacaklandırılacaktır.';
        icon.innerText = 'outbox';
        icon.className = 'p-1.5 bg-rose-500 rounded-lg text-white material-symbols-outlined text-[18px]';
        saveBtn.className = 'w-full md:w-auto px-8 py-3 bg-gradient-to-r from-rose-600 to-pink-600 text-white rounded-xl hover:shadow-lg hover:shadow-rose-500/30 font-bold transition-all flex items-center justify-center gap-2 transform active:scale-95';
        saveBtn.innerHTML = '<span class="material-symbols-outlined">save</span> Kaydet ve Stoktan Düş';
    } else {
        infoBox.className = 'p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 flex items-center gap-3';
        title.className = 'text-xs font-bold text-emerald-800 dark:text-emerald-400';
        title.innerText = 'Alış Faturası Modu';
        desc.className = 'text-[10px] text-emerald-600 dark:text-emerald-500';
        desc.innerText = 'Ürünler stoğa eklenecek ve maliyet ortalaması güncellenecektir.';
        icon.innerText = 'inventory_2';
        icon.className = 'p-1.5 bg-emerald-500 rounded-lg text-white material-symbols-outlined text-[18px]';
        saveBtn.className = 'w-full md:w-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:shadow-lg hover:shadow-blue-500/30 font-bold transition-all flex items-center justify-center gap-2 transform active:scale-95';
        saveBtn.innerHTML = '<span class="material-symbols-outlined">save</span> Kaydet ve Stoklara İşle';
    }
}
</script>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
