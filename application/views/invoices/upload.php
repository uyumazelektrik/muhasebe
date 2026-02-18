<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <a href="<?php echo site_url('invoices'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
                        AI Fatura Yükle
                    </h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal ml-9">Fatura görselini yükleyin, AI otomatik analiz edecek</p>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <form id="invoice_form" method="POST" class="w-full">
            
            <!-- AI Fatura Yükleme Alanı -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-500">cloud_upload</span>
                    Fatura Görseli Yükle
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div id="drop_zone" class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all group min-h-[200px] flex items-center justify-center">
                            <input type="file" id="file_input" accept="image/*,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            
                            <div id="upload_placeholder" class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500/20 to-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-4xl text-purple-500">upload_file</span>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Fatura görselini sürükleyin</p>
                                    <p class="text-sm text-slate-500">veya tıklayarak dosya seçin</p>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">JPG</span>
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">PNG</span>
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">PDF</span>
                                    <span class="text-slate-300">|</span>
                                    <span>Max 10MB</span>
                                </div>
                            </div>
                            
                            <div id="image_preview_container" class="hidden relative w-full">
                                <div id="pdf_preview" class="hidden flex flex-col items-center gap-3 p-6 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-800 mx-auto max-w-[200px]">
                                    <span class="material-symbols-outlined text-6xl text-red-500">picture_as_pdf</span>
                                    <span id="pdf_name" class="text-xs font-bold text-slate-600 dark:text-slate-400 truncate w-full px-2 text-center">fatura.pdf</span>
                                </div>
                                <img id="image_preview" src="" alt="Fatura Önizleme" class="max-h-52 mx-auto rounded-xl shadow-lg hidden">
                                <button type="button" onclick="clearImage(event)" class="absolute -top-2 -right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-colors z-20">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        </div>
                        
                        <button type="button" id="analyze_btn" onclick="analyzeInvoice()" disabled
                                class="w-full mt-4 px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-purple-600/30 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                            <span class="material-symbols-outlined">auto_awesome</span>
                            <span id="analyze_btn_text">AI ile Analiz Et</span>
                        </button>
                    </div>
                    
                    <div id="ai_status_panel" class="hidden">
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 h-full">
                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-500 animate-pulse text-lg" id="status_icon">psychology</span>
                                <span id="status_title">AI Analiz Ediyor...</span>
                            </h4>
                            <div id="ai_log" class="space-y-1 text-xs font-mono text-slate-600 dark:text-slate-400 max-h-40 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fatura Bilgileri (create.php ile birebir aynı) -->
            <div id="invoice_details_panel" class="hidden">
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
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tür</label>
                            <select id="invoice_type" name="type" required onchange="updateCounterpartyFromType()"
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
                                        <div class="option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center" 
                                             data-value="<?php echo $e['id']; ?>"
                                             data-tax="<?php echo htmlspecialchars($e['tax_id'] ?? ''); ?>">
                                            <span><?php echo htmlspecialchars($e['name']); ?></span>
                                            <span class="text-[10px] opacity-50"><?php echo number_format($e['balance'], 2); ?> ₺</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
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
                                <option value="virman">Virman</option>
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
                    
                    <!-- AI Algılanan Bilgi -->
                    <div id="ai_detected_info" class="hidden mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
                        <div class="flex items-center gap-2 text-sm text-purple-700 dark:text-purple-300">
                            <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
                            <span class="font-bold">AI Algıladı:</span>
                            <span id="ai_detected_text"></span>
                            <button type="button" onclick="autoMatchEntity()" class="ml-auto px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-colors">
                                Cari Eşleştir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ürün/Hizmet Ekleme (create.php ile birebir aynı) -->
                <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500">inventory_2</span>
                        Kalemler
                    </h3>
                    
                    <!-- Add Item Row -->
                    <div class="grid grid-cols-12 gap-2 mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
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
                    <button type="button" onclick="clearAll()" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors text-center">
                        Baştan Başla
                    </button>
                    <button type="submit" class="px-8 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Faturayı Kaydet
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
(function() {
let invoiceItems = [];
let uploadedImageData = null;
let uploadedMimeType = null;
let detectedCounterparty = '';

// ===== PAYMENT METHODS (create.php ile aynı) =====
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

// ===== SEARCHABLE SELECT (create.php ile aynı) =====
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
                opt.classList.toggle('hidden', !text.includes(val));
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
                $(hiddenInput).trigger('change');
            });
        });

        document.addEventListener('click', (e) => {
            if(!container.contains(e.target)) list.classList.add('hidden');
        });
    });
}

// ===== AI FUNCTIONS =====
function handleFile(file) {
    if (file.size > 10 * 1024 * 1024) {
        showToast('Dosya boyutu çok büyük (max 10MB)', 'error');
        return;
    }
    
    const analyzeBtn = document.getElementById('analyze_btn');
    const placeholder = document.getElementById('upload_placeholder');
    const previewContainer = document.getElementById('image_preview_container');
    const previewImg = document.getElementById('image_preview');
    const pdfPreview = document.getElementById('pdf_preview');
    const pdfName = document.getElementById('pdf_name');

    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImageData = e.target.result.split(',')[1];
        uploadedMimeType = file.type;
        
        // UI Updates
        placeholder.classList.add('hidden');
        previewContainer.classList.remove('hidden');
        
        if (file.type === 'application/pdf') {
            previewImg.classList.add('hidden');
            pdfPreview.classList.remove('hidden');
            pdfName.textContent = file.name;
        } else {
            pdfPreview.classList.add('hidden');
            previewImg.classList.remove('hidden');
            previewImg.src = e.target.result;
        }
        
        // ENABLE BUTTON
        analyzeBtn.disabled = false;
        analyzeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    };
    reader.readAsDataURL(file);
}

function clearImage(e) {
    if(e) e.stopPropagation();
    uploadedImageData = null;
    uploadedMimeType = null;
    document.getElementById('upload_placeholder').classList.remove('hidden');
    document.getElementById('image_preview_container').classList.add('hidden');
    document.getElementById('image_preview').classList.add('hidden');
    document.getElementById('pdf_preview').classList.add('hidden');
    document.getElementById('file_input').value = '';
    
    const analyzeBtn = document.getElementById('analyze_btn');
    analyzeBtn.disabled = true;
    analyzeBtn.classList.add('opacity-50', 'cursor-not-allowed');
}

function addLog(msg, type = 'info') {
    const colors = {info: 'text-slate-500', success: 'text-emerald-500', error: 'text-red-500'};
    const icons = {info: '🔄', success: '✅', error: '❌'};
    document.getElementById('ai_log').innerHTML += `<div class="${colors[type]}">${icons[type]} ${msg}</div>`;
}

async function analyzeInvoice() {
    if (!uploadedImageData) return;
    
    document.getElementById('ai_status_panel').classList.remove('hidden');
    document.getElementById('ai_log').innerHTML = '';
    document.getElementById('analyze_btn').disabled = true;
    document.getElementById('analyze_btn_text').innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Analiz Ediliyor...';
    
    addLog('Fatura görseli işleniyor...');
    
    try {
        addLog('AI modeline gönderiliyor...');
        const formData = new FormData();
        formData.append('image_data', uploadedImageData);
        formData.append('mime_type', uploadedMimeType);
        
        const response = await fetch('<?php echo site_url('invoice/api-analyze'); ?>', {method: 'POST', body: formData});
        const result = await response.json();
        
        if (result.status === 'success') {
            addLog('Analiz tamamlandı!', 'success');
            document.getElementById('status_icon').textContent = 'check_circle';
            document.getElementById('status_icon').classList.remove('animate-pulse');
            document.getElementById('status_title').textContent = 'Analiz Tamamlandı';
            
            populateResults(result.data);
            document.getElementById('invoice_details_panel').classList.remove('hidden');
        } else {
            addLog('Hata: ' + result.message, 'error');
            document.getElementById('status_icon').textContent = 'error';
            document.getElementById('status_title').textContent = 'Başarısız';
        }
    } catch (err) {
        addLog('Bağlantı hatası: ' + err.message, 'error');
    }
    
    document.getElementById('analyze_btn').disabled = false;
    document.getElementById('analyze_btn_text').textContent = 'AI ile Analiz Et';
}

function populateResults(data) {
    // Hem satıcı hem alıcı bilgilerini sakla (tür değişince kullanılacak)
    window.aiDetectedSeller = data.seller || {};
    window.aiDetectedBuyer = data.buyer || {};
    
    // AI'dan gelen türü belirle (Alış/Satış)
    let invoiceType = 'purchase';
    if (data.type) {
        const typeStr = String(data.type).toLocaleLowerCase("tr-TR");
        if (typeStr.includes('satış')) invoiceType = 'sale';
        else if (typeStr.includes('alış')) invoiceType = 'purchase';
    }
    
    // Ekstra Kontrol:
    // 1. Eğer faturayı kesen (seller) Mustafa Uyumaz ise bu bir Satış faturasıdır.
    // 2. Eğer faturayı alan (buyer) Mustafa Uyumaz ise bu bir Alış faturasıdır.
    const sellerName = String(data.seller?.name || '').toLocaleLowerCase("tr-TR");
    const buyerName = String(data.buyer?.name || '').toLocaleLowerCase("tr-TR");
    
    if (sellerName.includes('mustafa uyumaz')) {
        invoiceType = 'sale';
    } else if (buyerName.includes('mustafa uyumaz')) {
        invoiceType = 'purchase';
    }
    
    window.aiDetectedType = invoiceType;
    
    // Form alanlarını doldur
    document.getElementById('invoice_no').value = data.invoice_no || '<?php echo $next_invoice_no; ?>';
    document.getElementById('invoice_date').value = data.invoice_date || '<?php echo date('Y-m-d'); ?>';
    document.getElementById('invoice_type').value = invoiceType;
    document.getElementById('tax_included').value = data.tax_included ? '1' : '0';
    
    const typeLabel = invoiceType === 'sale' ? 'Satış Faturası' : 'Alış Faturası';
    addLog('Algılanan Tür: ' + typeLabel, 'info');
    if (data.seller?.name) addLog('Faturayı Kesen (Satıcı): ' + data.seller.name, 'success');
    if (data.buyer?.name) addLog('Alıcı/Müşteri: ' + data.buyer.name, 'info');
    
    // Kalemleri doldur
    invoiceItems = [];
    if (data.items && data.items.length > 0) {
        data.items.forEach(item => {
            const q = parseFloat(item.quantity) || 1;
            const p = parseFloat(item.unit_price) || 0;
            const t = parseFloat(item.tax_rate) || 20;
            const inc = data.tax_included;
            let total = parseFloat(item.total) || (q * p * (inc ? 1 : (1 + t/100)));
            let taxAmt = inc ? (total - total/(1+t/100)) : (q*p*t/100);
            
            const match = findProductMatch(item.name);
            
            invoiceItems.push({
                product_id: match ? match.id : null,
                product_name: match ? match.name : item.name,
                item_type: 'stok',
                quantity: q,
                unit: match ? match.unit : (item.unit || 'Adet'),
                price: p,
                discount_rate: 0,
                discount_amount: 0,
                tax_rate: t,
                tax_amount: taxAmt,
                total: total,
                gorsel: match ? match.gorsel : ''
            });
        });
        renderItems();
        addLog(invoiceItems.length + ' kalem algılandı', 'success');
    }
    
    // Karşı tarafı güncelle (mevcut türe göre)
    updateCounterpartyFromType();
}

// Fatura türü değiştiğinde karşı tarafı güncelle
function updateCounterpartyFromType() {
    const type = document.getElementById('invoice_type').value;
    
    // Alış faturasında karşı taraf = Satıcı (seller)
    // Satış faturasında karşı taraf = Alıcı (buyer)
    const counterparty = type === 'sale' ? window.aiDetectedBuyer : window.aiDetectedSeller;
    
    if (!counterparty || !counterparty.name) {
        // AI verisi yoksa panel gizle
        document.getElementById('ai_detected_info').classList.add('hidden');
        return;
    }
    
    detectedCounterparty = counterparty.name;
    detectedCounterpartyData = counterparty;
    
    // AI bilgi panelini göster (Template'i sıfırla çünkü autoMatchEntity innerHTML'i değiştiriyor)
    const infoDiv = document.getElementById('ai_detected_info');
    infoDiv.innerHTML = `
        <div class="flex items-center gap-2 text-sm text-purple-700 dark:text-purple-300">
            <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
            <span class="font-bold">AI Algıladı:</span>
            <span id="ai_detected_text"></span>
            <button type="button" onclick="autoMatchEntity()" class="ml-auto px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-colors">
                Cari Eşleştir
            </button>
        </div>
    `;
    
    let infoHtml = detectedCounterparty;
    if (counterparty.tax_no) infoHtml += ` (VKN: ${counterparty.tax_no})`;
    document.getElementById('ai_detected_text').textContent = infoHtml;
    infoDiv.classList.remove('hidden');
    
    // Cari arama inputuna yaz
    const entityInput = document.querySelector('#entity_id').closest('.searchable-select').querySelector('.search-input');
    entityInput.value = detectedCounterparty;
    document.getElementById('entity_id').value = ''; // Hidden input'u temizle
    
    // Panel durumunu sıfırla (eşleşme bekleniyor)
    document.getElementById('ai_detected_info').className = 'mt-4 p-3 bg-slate-50 dark:bg-slate-900/20 border border-slate-200 dark:border-slate-800 rounded-xl';
    
    // Otomatik eşleştirmeyi dene
    setTimeout(() => autoMatchEntity(), 200);
}

function findProductMatch(name) {
    if (!name) return null;
    const search = String(name).toLocaleLowerCase("tr-TR").trim();
    const searchNormalized = search.replace(/\s+/g, '');
    let bestMatch = null;
    
    $('#add_product option').each(function() {
        if (bestMatch) return;
        const val = $(this).val();
        if (!val || val.startsWith('EXP_')) return;
        
        const pName = $(this).text().toLocaleLowerCase("tr-TR").trim();
        const pNameNormalized = pName.replace(/\s+/g, '');
        const pMatch = String($(this).data('match') || '').toLocaleLowerCase("tr-TR").trim();
        const pBarcode = String($(this).data('barcode') || '').toLocaleLowerCase("tr-TR").trim();
        
        // 1. Normalize edilmiş isim eşleşmesi (Boşluk duyarsız)
        if (pNameNormalized === searchNormalized) {
            bestMatch = { id: val, name: $(this).text(), unit: $(this).data('unit'), gorsel: $(this).data('gorsel') };
            return;
        }
        
        // 2. Alias (Eşleşen İsimler) kontrolü
        if (pMatch) {
            const aliases = pMatch.split(',').map(a => a.trim().toLocaleLowerCase("tr-TR"));
            if (aliases.some(a => a === search || a.replace(/\s+/g, '') === searchNormalized)) {
                bestMatch = { id: val, name: $(this).text(), unit: $(this).data('unit'), gorsel: $(this).data('gorsel') };
                return;
            }
        }
        
        // 3. Barkod kontrolü
        if (pBarcode && pBarcode === searchNormalized) {
            bestMatch = { id: val, name: $(this).text(), unit: $(this).data('unit'), gorsel: $(this).data('gorsel') };
            return;
        }
    });
    
    return bestMatch;
}

let detectedCounterpartyData = {};

function autoMatchEntity() {
    if (!detectedCounterparty) return;
    const search = String(detectedCounterparty).toLocaleLowerCase("tr-TR").trim();
    const container = document.querySelector('#entity_id').closest('.searchable-select');
    const options = container.querySelectorAll('.option-item');
    const infoDiv = document.getElementById('ai_detected_info');
    let matched = false;
    let matchedName = '';
    
    options.forEach(opt => {
        if (matched) return; 
        const name = String(opt.querySelector('span')?.textContent || '').toLocaleLowerCase("tr-TR").trim();
        const taxId = String(opt.dataset.tax || '').toLocaleLowerCase("tr-TR").trim();
        const aiTax = String(detectedCounterpartyData.tax_no || '').toLocaleLowerCase("tr-TR").trim();
        
        // 1. Önce VKN (Tax ID) üzerinden tam eşleşme dene (En güvenilir)
        if (aiTax && taxId && aiTax.replace(/\s/g, '') === taxId.replace(/\s/g, '')) {
            opt.click();
            matched = true;
            matchedName = opt.querySelector('span').textContent;
            return;
        }

        // Mustafa Uyumaz'ın faturadaki rolü (kendi firmamız ise eşleştirme)
        if (name.includes('mustafa uyumaz')) return;

        // 2. Tam isim eşleşmesi
        if (name === search) {
            opt.click();
            matched = true;
            matchedName = opt.querySelector('span').textContent;
            return;
        }

        // 3. Daha sıkı bir fuzzy match. 
        // Sadece "Enerji", "Elektrik" gibi genel kelimelerle eşleşmeyi önle.
        const commonWords = [
            'enerji', 'enerjı', 'elektrik', 'elektrık', 'solar', 'gunes', 'güneş',
            'insaat', 'ınsaat', 'inşaat', 'ins.', 'san.', 'tic.', 'ltd.', 'sti.', 'stı.', 'şirketi', 'sirketi', 
            'limited', 'anonim', 'as.', 'a.s.', 'baskani', 'baskanligi', 'belediyesi', 'sanayi', 'ticaret'
        ];
        
        // Kelimeleri ayırırken noktalama işaretlerini temizle
        const cleanWords = (str) => str.split(/[\s,.*-]+/).filter(w => w.length > 2 && !commonWords.includes(w));
        
        const searchWords = cleanWords(search);
        const nameWords = cleanWords(name);

        // Eğer AI'dan gelen ismin en az bir ÖNEMLİ kelimesi caride varsa
        // VE carinin ismindeki önemli kelimelerden biri AI isminde varsa (Çift yönlü doğrulama)
        if (searchWords.length > 0 && searchWords.some(sw => name.includes(sw))) {
            if (nameWords.some(nw => search.includes(nw))) {
                opt.click();
                matched = true;
                matchedName = opt.querySelector('span').textContent;
            }
        }
    });
    
    if (matched) {
        // Eşleşme bulundu - Yeşil başarı paneli göster
        infoDiv.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                <span class="font-bold">Cari Eşleşti:</span>
                <span>${matchedName}</span>
            </div>
        `;
        infoDiv.className = 'mt-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl';
    } else {
        // Eşleşme bulunamadı - Yeni cari oluşturma seçeneği sun
        infoDiv.className = 'mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl';
        infoDiv.innerHTML = `
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-sm text-purple-700 dark:text-purple-300">
                    <span class="material-symbols-outlined text-purple-500">auto_awesome</span>
                    <span class="font-bold">AI Algıladı:</span>
                    <span>${detectedCounterparty}</span>
                </div>
                <div class="text-xs text-purple-600 dark:text-purple-400 space-y-1">
                    ${detectedCounterpartyData.tax_no ? `<div><strong>VKN:</strong> ${detectedCounterpartyData.tax_no}</div>` : ''}
                    ${detectedCounterpartyData.tax_office ? `<div><strong>V.Dairesi:</strong> ${detectedCounterpartyData.tax_office}</div>` : ''}
                    ${detectedCounterpartyData.phone ? `<div><strong>Tel:</strong> ${detectedCounterpartyData.phone}</div>` : ''}
                    ${detectedCounterpartyData.email ? `<div><strong>E-posta:</strong> ${detectedCounterpartyData.email}</div>` : ''}
                    ${detectedCounterpartyData.address ? `<div><strong>Adres:</strong> ${detectedCounterpartyData.address}</div>` : ''}
                </div>
                <div class="flex gap-2 mt-1">
                    <button type="button" onclick="createNewEntity()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">person_add</span>
                        Yeni Cari Oluştur
                    </button>
                    <button type="button" onclick="document.querySelector('#entity_id').closest('.searchable-select').querySelector('.search-input').click()" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-xs font-bold transition-colors">
                        Manuel Seç
                    </button>
                </div>
            </div>
        `;
    }
}

async function createNewEntity() {
    if (!detectedCounterparty) {
        showToast('Cari adı bulunamadı', 'error');
        return;
    }
    
    const entityData = {
        name: detectedCounterparty,
        tax_no: detectedCounterpartyData.tax_no || '',
        tax_office: detectedCounterpartyData.tax_office || '',
        phone: detectedCounterpartyData.phone || '',
        email: detectedCounterpartyData.email || '',
        address: detectedCounterpartyData.address || '',
        type: 'customer'
    };
    
    try {
        const formData = new FormData();
        Object.keys(entityData).forEach(key => formData.append(key, entityData[key]));
        
        const response = await fetch('<?php echo site_url('api/create-entity'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseErr) {
            console.error('JSON Parse Error:', text);
            showToast('Sunucu hatası: ' + text.substring(0, 100), 'error');
            return;
        }
        if (result.status === 'success') {
            showToast('Yeni cari oluşturuldu: ' + detectedCounterparty, 'success');
            
            // Yeni cariyi seçenek listesine ekle ve seç
            const container = document.querySelector('#entity_id').closest('.searchable-select');
            const optionsList = container.querySelector('.options-list');
            const entityInput = container.querySelector('.search-input');
            const hiddenInput = container.querySelector('.hidden-input');
            
            // Yeni option ekle
            const newOption = document.createElement('div');
            newOption.className = 'option-item p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5 last:border-0 flex justify-between items-center';
            newOption.dataset.value = result.entity_id;
            newOption.innerHTML = `<span>${detectedCounterparty}</span><span class="text-[10px] opacity-50">0,00 ₺</span>`;
            optionsList.prepend(newOption);
            
            // Click event ekle
            newOption.addEventListener('click', (e) => {
                e.stopPropagation();
                entityInput.value = detectedCounterparty;
                hiddenInput.value = result.entity_id;
                optionsList.classList.add('hidden');
            });
            
            // Seç
            entityInput.value = detectedCounterparty;
            hiddenInput.value = result.entity_id;
            
            // Bilgi panelini güncelle
            document.getElementById('ai_detected_info').innerHTML = `
                <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <span class="font-bold">Cari Oluşturuldu:</span>
                    <span>${detectedCounterparty}</span>
                </div>
            `;
        } else {
            showToast('Cari oluşturulamadı: ' + (result.message || 'Hata'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
}

async function clearAll() {
    const confirmed = await showConfirm({
        title: 'Verileri Temizle',
        message: 'Tüm verileri silmek istiyor musunuz?',
        confirmText: 'Evet, Sil',
        type: 'danger'
    });
    
    if (!confirmed) return;
    clearImage();
    invoiceItems = [];
    document.getElementById('invoice_details_panel').classList.add('hidden');
    document.getElementById('ai_status_panel').classList.add('hidden');
    document.getElementById('ai_detected_info').classList.add('hidden');
    renderItems();
}

// ===== ITEM MANAGEMENT (create.php ile aynı) =====
function initUploadPage() {
    setupSearchableSelects();
    
    // File upload
    const dropZone = document.getElementById('drop_zone');
    const fileInput = document.getElementById('file_input');
    
    if (dropZone && fileInput) {
        ['dragenter','dragover','dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }));
        dropZone.addEventListener('drop', e => { if(e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]); });
        fileInput.addEventListener('change', e => { if(e.target.files.length) handleFile(e.target.files[0]); });
    }
    
    // Select2 setup
    if ($('#add_product').length) {
        let allProductOptions = [];
        $('#add_product option').each(function() {
            if ($(this).val() !== '') allProductOptions.push({type: $(this).data('type'), html: $(this)[0].outerHTML});
        });

        const productSelect2Options = {
            placeholder: 'Ürün ara veya yeni ürün yaz...',
            allowClear: true,
            width: '100%',
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') return null;
                return {id: term, text: term, newTag: true};
            },
            matcher: function(params, data) {
                if ($.trim(params.term) === '') return data;
                if (typeof data.text === 'undefined') return null;
                const term = params.term.toLowerCase();
                const text = data.text.toLowerCase();
                const $el = $(data.element);
                const barcode = ($el.data('barcode') || '').toString().toLowerCase();
                const matchNames = ($el.data('match') || '').toString().toLocaleLowerCase("tr-TR");
                if (text.toLocaleLowerCase("tr-TR").indexOf(term) > -1 || barcode.indexOf(term) > -1 || matchNames.indexOf(term) > -1 || data.newTag) return data;
                return null;
            }
        };

        $('#add_product').select2(productSelect2Options);
        
        $('#add_item_type').on('change', function() {
            const selectedType = $(this).val();
            setTimeout(() => {
                const productSelect = $('#add_product');
                if (productSelect.hasClass("select2-hidden-accessible")) productSelect.select2('destroy');
                productSelect.empty().append('<option value="">Ürün ara...</option>');
                allProductOptions.forEach(opt => { if (opt.type === selectedType) productSelect.append(opt.html); });
                productSelect.select2(productSelect2Options).val('').trigger('change');
            }, 100);
        });
        
        setTimeout(() => { $('#add_item_type').trigger('change'); }, 100);
        
        $('#add_product').on('change', function() {
            const val = $(this).val();
            if (!val) return;
            const selected = $(this).find(':selected');
            document.getElementById('add_price').value = selected.data('price') || 0;
            document.getElementById('add_unit').value = selected.data('unit') || 'Adet';
            document.getElementById('add_item_type').value = selected.data('type') || 'stok';
            // gorsel is implicitly handled in addItem via selecting the product
            calculateLineTotal();
        });
    }
    
    ['add_quantity', 'add_price', 'add_discount'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('input', calculateLineTotal);
    });
    
    const addTax = document.getElementById('add_tax');
    if(addTax) addTax.addEventListener('change', calculateLineTotal);
    
    const taxInc = document.getElementById('tax_included');
    if(taxInc) taxInc.addEventListener('change', calculateLineTotal);
}

// Initialize based on ready state (supports both SPA and Full Page Load)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUploadPage);
} else {
    initUploadPage();
}

function calculateLineTotal() {
    const q = parseFloat(document.getElementById('add_quantity').value) || 0;
    const p = parseFloat(document.getElementById('add_price').value) || 0;
    const d = parseFloat(document.getElementById('add_discount').value) || 0;
    const t = parseFloat(document.getElementById('add_tax').value) || 0;
    const inc = document.getElementById('tax_included').value == '1';
    
    const sub = q * p;
    const disc = sub * (d / 100);
    const after = sub - disc;
    let total = inc ? after : after + (after * t / 100);
    
    document.getElementById('add_line_total').textContent = total.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
}

function addItem() {
    const productSelect = document.getElementById('add_product');
    let productId = productSelect.value;
    if (productId && productId.startsWith('EXP_')) productId = productId.substring(4);
    
    const selectedOption = $(productSelect).find(':selected');
    const productName = selectedOption.text() || '';
    const gorsel = selectedOption.data('gorsel') || '';
    const itemType = document.getElementById('add_item_type').value;
    const q = parseFloat(document.getElementById('add_quantity').value) || 0;
    const unit = document.getElementById('add_unit').value;
    const p = parseFloat(document.getElementById('add_price').value) || 0;
    const dr = parseFloat(document.getElementById('add_discount').value) || 0;
    const tr = parseFloat(document.getElementById('add_tax').value) || 0;
    
    if (!productId || q <= 0) { showToast('Lütfen ürün seçin ve miktar girin', 'info'); return; }
    
    const sub = q * p;
    const discAmt = sub * (dr / 100);
    const after = sub - discAmt;
    const inc = document.getElementById('tax_included').value == '1';
    let taxAmt, total;
    if (inc) { total = after; taxAmt = total - (total / (1 + tr/100)); }
    else { taxAmt = after * tr / 100; total = after + taxAmt; }
    
    invoiceItems.push({product_id: productId, product_name: productName, item_type: itemType, quantity: q, unit: unit, price: p, discount_rate: dr, discount_amount: discAmt, tax_rate: tr, tax_amount: taxAmt, total: total, gorsel: gorsel});
    renderItems();
    
    $('#add_product').val('').trigger('change');
    document.getElementById('add_quantity').value = 1;
    document.getElementById('add_price').value = 0;
    document.getElementById('add_discount').value = 0;
    document.getElementById('add_tax').value = '20';
    document.getElementById('add_unit').value = 'Adet';
    document.getElementById('add_item_type').value = 'stok';
    document.getElementById('add_line_total').textContent = '0,00 ₺';
}

function removeItem(index) { invoiceItems.splice(index, 1); renderItems(); }

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
                <select onchange="updateItemType(${index}, this.value)" 
                        class="px-2 py-1 text-xs font-bold rounded cursor-pointer border-none outline-none ${item.item_type === 'stok' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'}">
                    <option value="stok" ${item.item_type === 'stok' ? 'selected' : ''}>STOK</option>
                    <option value="gider" ${item.item_type === 'gider' ? 'selected' : ''}>GİDER</option>
                </select>
            </td>
            <td class="px-3 py-2 text-sm font-medium text-slate-900 dark:text-white">${item.product_name}</td>
            <td class="px-3 py-2 text-sm text-center text-slate-600 dark:text-slate-400">${item.quantity}</td>
            <td class="px-3 py-2 text-sm text-center text-slate-500">${item.unit}</td>
            <td class="px-3 py-2 text-sm text-right text-slate-600 dark:text-slate-400">${item.price.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
            <td class="px-3 py-2 text-sm text-center ${item.discount_rate > 0 ? 'text-orange-500 font-medium' : 'text-slate-400'}">${item.discount_rate > 0 ? '%' + item.discount_rate : '-'}</td>
            <td class="px-3 py-2 text-sm text-center text-slate-600 dark:text-slate-400">%${item.tax_rate}</td>
            <td class="px-3 py-2 text-sm text-right font-medium text-slate-900 dark:text-white">${item.total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
            <td class="px-3 py-2 text-center"><button type="button" onclick="removeItem(${index})" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"><span class="material-symbols-outlined text-sm">delete</span></button></td>
        </tr>
    `).join('');
    updateTotals();
}

function updateItemType(index, newType) {
    invoiceItems[index].item_type = newType;
    renderItems(); // Re-render to update colors
}

function updateTotals() {
    const taxIncluded = document.getElementById('tax_included').value === '1';
    const generalDiscount = parseFloat(document.getElementById('general_discount').value) || 0;
    
    let subtotal = 0, lineDiscountTotal = 0, taxTotal = 0;
    invoiceItems.forEach(item => { subtotal += item.quantity * item.price; lineDiscountTotal += item.discount_amount; });

    let afterLineDiscounts = subtotal - lineDiscountTotal;
    if (afterLineDiscounts > 0) {
        const generalDiscountRatio = generalDiscount / afterLineDiscounts;
        invoiceItems.forEach(item => {
            const itemNetAfterLine = (item.quantity * item.price) - item.discount_amount;
            const itemNetFinal = itemNetAfterLine - (itemNetAfterLine * generalDiscountRatio);
            taxTotal += taxIncluded ? (itemNetFinal - itemNetFinal / (1 + item.tax_rate / 100)) : (itemNetFinal * item.tax_rate / 100);
        });
    }

    let grandTotal = taxIncluded ? (afterLineDiscounts - generalDiscount) : ((afterLineDiscounts - generalDiscount) + taxTotal);
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('line_discount_total').textContent = '-' + lineDiscountTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('tax_total').textContent = taxTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('general_discount_display').textContent = '-' + generalDiscount.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    document.getElementById('grand_total').textContent = grandTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
}

// ===== FORM SUBMIT (create.php ile aynı) =====
document.getElementById('invoice_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (invoiceItems.length === 0) { showToast('Lütfen en az bir kalem ekleyin', 'info'); return; }
    
    const taxIncluded = document.getElementById('tax_included').value === '1';
    const generalDiscount = parseFloat(document.getElementById('general_discount').value) || 0;
    
    let subtotal = 0, lineDiscountTotal = 0, taxTotal = 0;
    invoiceItems.forEach(item => { subtotal += (item.quantity * item.price); lineDiscountTotal += item.discount_amount; });

    let afterLineDiscounts = subtotal - lineDiscountTotal;
    if (afterLineDiscounts > 0) {
        const generalDiscountRatio = generalDiscount / afterLineDiscounts;
        invoiceItems.forEach(item => {
            const itemNetAfterLine = (item.quantity * item.price) - item.discount_amount;
            const itemNetFinal = itemNetAfterLine - (itemNetAfterLine * generalDiscountRatio);
            taxTotal += taxIncluded ? (itemNetFinal - itemNetFinal / (1 + item.tax_rate / 100)) : (itemNetFinal * item.tax_rate / 100);
        });
    }

    const grandTotal = taxIncluded ? (afterLineDiscounts - generalDiscount) : ((afterLineDiscounts - generalDiscount) + taxTotal);

    const formData = new FormData();
    formData.append('invoice_no', document.getElementById('invoice_no').value);
    formData.append('invoice_date', document.getElementById('invoice_date').value);
    formData.append('type', document.getElementById('invoice_type').value);
    formData.append('entity_id', document.getElementById('entity_id').value);
    formData.append('tax_included', taxIncluded ? 1 : 0);
    formData.append('total_amount', subtotal);
    formData.append('discount_amount', lineDiscountTotal + generalDiscount);
    formData.append('tax_amount', taxTotal);
    formData.append('net_amount', grandTotal);
    formData.append('notes', document.getElementById('notes').value);
    formData.append('payment_status', document.getElementById('payment_status').value);
    formData.append('payment_type', document.getElementById('payment_type').value);
    formData.append('wallet_id', document.getElementById('wallet_id').value);
    formData.append('transfer_entity_id', document.getElementById('transfer_entity_id').value);
    formData.append('items', JSON.stringify(invoiceItems));
    
    try {
        const response = await fetch('<?php echo site_url('api/save-invoice'); ?>', {method: 'POST', body: formData});
        const text = await response.text();
        console.log('Server response:', text);
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseErr) {
            console.error('JSON Parse Error. Raw response:', text);
            showToast('Sunucu hatası: ' + text.substring(0, 200), 'error');
            return;
        }
        
        if (result.status === 'success') {
            showToast('Fatura başarıyla kaydedildi');
            setTimeout(() => { 
                const targetUrl = '<?php echo site_url('invoices/detail/'); ?>' + result.invoice_id;
                if (typeof navigateTo === 'function') {
                    navigateTo(targetUrl);
                } else {
                    window.location.href = targetUrl;
                }
            }, 500);
        } else {
            showToast('Hata: ' + (result.message || 'Bir sorun oluştu'), 'error');
        }
    } catch (err) {
        console.error('Fetch error:', err);
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});
// Export functions for HTML event handlers
window.togglePaymentMethod = togglePaymentMethod;
window.togglePaymentType = togglePaymentType;
window.clearImage = clearImage;
window.analyzeInvoice = analyzeInvoice;
window.autoMatchEntity = autoMatchEntity;
window.createNewEntity = createNewEntity;
window.clearAll = clearAll;
window.addItem = addItem;
window.removeItem = removeItem;
window.updateItemType = updateItemType;
window.updateTotals = updateTotals;
window.updateCounterpartyFromType = updateCounterpartyFromType;

})();
</script>
