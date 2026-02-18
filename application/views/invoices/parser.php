<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <a href="<?php echo site_url('invoices'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">PDF Fatura Oku</h2>
                </div>
                <p class="text-[#9da6b9] text-sm sm:text-base font-normal ml-9">PDF faturayı yükle, otomatik işle</p>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        
        <!-- Adım 1: PDF Yükleme -->
        <div id="step1_upload" class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">upload_file</span>
                1. PDF Fatura Yükle
            </h3>
            
            <div id="drop_zone" class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all group min-h-[200px] flex items-center justify-center">
                <input type="file" id="pdf_input" accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                
                <div id="upload_placeholder" class="flex flex-col items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500/20 to-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-4xl text-purple-500">picture_as_pdf</span>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">PDF faturayı sürükleyin</p>
                        <p class="text-sm text-slate-500">veya tıklayarak dosya seçin</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">PDF</span>
                        <span class="text-slate-300">|</span>
                        <span>Max 10MB</span>
                    </div>
                </div>
                
                <div id="file_preview" class="hidden flex flex-col items-center gap-3">
                    <span class="material-symbols-outlined text-6xl text-red-500">picture_as_pdf</span>
                    <span id="file_name" class="text-sm font-bold text-slate-700">fatura.pdf</span>
                    <button type="button" onclick="clearFile()" class="text-sm text-red-500 hover:text-red-600">Kaldır</button>
                </div>
            </div>
            
            <button type="button" id="parse_btn" onclick="parseInvoice()" disabled
                    class="w-full mt-4 px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined">auto_awesome</span>
                <span id="parse_btn_text">Faturayı Oku</span>
            </button>
        </div>

        <!-- Adım 2: İşlem Durumu -->
        <div id="step2_processing" class="hidden bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex items-center justify-center gap-4 py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Fatura İşleniyor...</h3>
                    <p class="text-sm text-slate-500" id="processing_status">PDF analiz ediliyor</p>
                </div>
            </div>
        </div>

        <!-- Adım 3: Sonuç ve Düzeltme -->
        <div id="step3_result" class="hidden space-y-6">
            
            <!-- Uyarılar -->
            <div id="validation_warnings" class="hidden bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-500">warning</span>
                    <div>
                        <h4 class="font-bold text-amber-800 dark:text-amber-200">Dikkat Edilmesi Gerekenler</h4>
                        <ul id="warnings_list" class="mt-2 text-sm text-amber-700 dark:text-amber-300 space-y-1"></ul>
                    </div>
                </div>
            </div>

            <!-- Fatura Bilgileri -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        2. Fatura Bilgileri
                    </h3>
                    <span id="parser_badge" class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-bold rounded-full">
                        Template Parser
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Fatura No</label>
                        <input type="text" id="result_invoice_no" 
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-mono font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Tarih</label>
                        <input type="date" id="result_invoice_date"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Cari Adı</label>
                        <input type="text" id="result_entity_name"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Vergi No</label>
                        <input type="text" id="result_entity_tax_id"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                    
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Vergi Dairesi</label>
                        <input type="text" id="result_entity_tax_office"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Ürün/Hizmet Tablosu -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500">receipt_long</span>
                        3. Fatura Kalemleri
                    </h3>
                    <button type="button" onclick="addItem()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Kalem Ekle
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-500 uppercase">Ürün</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-24">Miktar</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-24">Birim</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-slate-500 uppercase w-32">B.Fiyat</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-20">KDV</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-slate-500 uppercase w-32">Toplam</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody" class="divide-y divide-slate-200 dark:divide-slate-800">
                            <!-- Kalemler JS ile doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Toplamlar -->
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-500">calculate</span>
                    4. Toplamlar
                </h3>
                
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Not</label>
                        <textarea id="notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary transition-colors"></textarea>
                    </div>
                    
                    <div class="w-full md:w-80 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Ara Toplam</span>
                            <span id="subtotal_display" class="font-medium text-slate-700 dark:text-slate-300">0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">KDV</span>
                            <span id="tax_total_display" class="font-medium text-slate-700 dark:text-slate-300">0,00 ₺</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-t border-slate-200 dark:border-slate-700 pt-3">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500">Genel İndirim</span>
                                <input type="number" id="result_discount" value="0" step="0.01" onchange="updateTotals()"
                                       class="w-20 px-2 py-1 bg-slate-50 dark:bg-input-dark border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-right focus:border-primary transition-colors">
                            </div>
                            <span id="discount_display" class="font-medium text-orange-500">-0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-slate-200 dark:border-slate-700 pt-3">
                            <span class="text-slate-900 dark:text-white">Genel Toplam</span>
                            <span id="grand_total_display" class="text-primary">0,00 ₺</span>
                        </div>
                    </div>
                </div>
                
                <div id="calculation_error" class="hidden mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined">error</span>
                        <span id="calculation_error_text"></span>
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button" onclick="location.reload()" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors">
                    İptal
                </button>
                <button type="button" onclick="saveInvoice()" class="px-8 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Faturayı Kaydet
                </button>
            </div>
        </div>
    </main>
</div>

<script>
let currentFile = null;
let parsedData = null;

// Dosya seçildiğinde
document.getElementById('pdf_input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type === 'application/pdf') {
        currentFile = file;
        showFilePreview(file);
    }
});

// Sürükle bırak
document.getElementById('drop_zone').addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('border-primary', 'bg-primary/5');
});

document.getElementById('drop_zone').addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('border-primary', 'bg-primary/5');
});

document.getElementById('drop_zone').addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('border-primary', 'bg-primary/5');
    
    const file = e.dataTransfer.files[0];
    if (file && file.type === 'application/pdf') {
        currentFile = file;
        showFilePreview(file);
    }
});

function showFilePreview(file) {
    document.getElementById('upload_placeholder').classList.add('hidden');
    document.getElementById('file_preview').classList.remove('hidden');
    document.getElementById('file_name').textContent = file.name;
    document.getElementById('parse_btn').disabled = false;
}

function clearFile() {
    currentFile = null;
    document.getElementById('pdf_input').value = '';
    document.getElementById('upload_placeholder').classList.remove('hidden');
    document.getElementById('file_preview').classList.add('hidden');
    document.getElementById('parse_btn').disabled = true;
}

async function parseInvoice() {
    if (!currentFile) return;
    
    document.getElementById('step1_upload').classList.add('hidden');
    document.getElementById('step2_processing').classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('pdf_file', currentFile);
    
    try {
        const response = await fetch('<?php echo site_url('invoice_parser/api_parse'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            parsedData = result.data;
            showResults(result);
        } else {
            throw new Error(result.message || 'Fatura okunamadı');
        }
        
    } catch (error) {
        showToast('Hata: ' + error.message, 'error');
        document.getElementById('step2_processing').classList.add('hidden');
        document.getElementById('step1_upload').classList.remove('hidden');
    }
}

function showResults(result) {
    document.getElementById('step2_processing').classList.add('hidden');
    document.getElementById('step3_result').classList.remove('hidden');
    
    const data = result.data;
    
    const badge = document.getElementById('parser_badge');
    if (result.parser_used === 'ai') {
        badge.textContent = 'AI Parser';
        badge.className = 'px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold rounded-full';
    }
    
    document.getElementById('result_invoice_no').value = data.invoice_no || '';
    document.getElementById('result_invoice_date').value = data.invoice_date || '';
    document.getElementById('result_entity_name').value = data.entity_name || '';
    document.getElementById('result_entity_tax_id').value = data.entity_tax_id || '';
    document.getElementById('result_entity_tax_office').value = data.entity_tax_office || '';
    
    renderItems(data.items || []);
    updateTotals();
    
    if (result.warnings && result.warnings.length > 0) {
        document.getElementById('validation_warnings').classList.remove('hidden');
        const list = document.getElementById('warnings_list');
        list.innerHTML = result.warnings.map(w => `<li>• ${w}</li>`).join('');
    }
}

function renderItems(items) {
    const tbody = document.getElementById('items_tbody');
    tbody.innerHTML = items.map((item, index) => `
        <tr data-index="${index}">
            <td class="px-3 py-3">
                <input type="text" value="${item.product_name}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white" 
                       onchange="updateItem(${index}, 'product_name', this.value)">
            </td>
            <td class="px-3 py-3">
                <input type="number" value="${item.quantity}" step="0.0001" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-center focus:border-primary transition-all outline-none text-slate-900 dark:text-white" 
                       onchange="updateItem(${index}, 'quantity', this.value)">
            </td>
            <td class="px-3 py-3">
                <select class="w-full px-3 py-2 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white cursor-pointer" 
                        onchange="updateItem(${index}, 'unit', this.value)">
                    <option value="Adet" ${item.unit === 'Adet' ? 'selected' : ''}>Adet</option>
                    <option value="Kg" ${item.unit === 'Kg' ? 'selected' : ''}>Kg</option>
                    <option value="Litre" ${item.unit === 'Litre' ? 'selected' : ''}>Lt</option>
                    <option value="m" ${item.unit === 'm' ? 'selected' : ''}>m</option>
                </select>
            </td>
            <td class="px-3 py-3">
                <input type="number" value="${item.unit_price}" step="0.0001" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-right focus:border-primary transition-all outline-none text-slate-900 dark:text-white font-mono" 
                       onchange="updateItem(${index}, 'unit_price', this.value)">
            </td>
            <td class="px-3 py-3">
                <input type="number" value="${item.tax_rate}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold text-center focus:border-primary transition-all outline-none text-slate-900 dark:text-white" 
                       onchange="updateItem(${index}, 'tax_rate', this.value)">
            </td>
            <td class="px-3 py-3 text-right font-mono text-sm font-bold text-slate-900 dark:text-white">
                ${(item.quantity * item.unit_price).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ₺
            </td>
            <td class="px-3 py-3 text-center">
                <button type="button" onclick="removeItem(${index})" class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

function updateItem(index, field, value) {
    if (!parsedData.items[index]) return;
    
    if (field === 'product_name' || field === 'unit') {
        parsedData.items[index][field] = value;
    } else {
        parsedData.items[index][field] = parseFloat(value) || 0;
    }
    
    renderItems(parsedData.items);
    updateTotals();
}

function removeItem(index) {
    parsedData.items.splice(index, 1);
    renderItems(parsedData.items);
    updateTotals();
}

function addItem() {
    parsedData.items.push({
        product_name: '',
        quantity: 1,
        unit: 'Adet',
        unit_price: 0,
        tax_rate: 20,
        total: 0
    });
    renderItems(parsedData.items);
    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    let taxTotal = 0;
    
    parsedData.items.forEach(item => {
        const lineTotal = item.quantity * item.unit_price;
        subtotal += lineTotal;
        taxTotal += lineTotal * (item.tax_rate / 100);
    });
    
    const discount = parseFloat(document.getElementById('result_discount').value) || 0;
    const grandTotal = subtotal + taxTotal - discount;
    
    document.getElementById('subtotal_display').textContent = subtotal.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺';
    document.getElementById('tax_total_display').textContent = taxTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺';
    document.getElementById('discount_display').textContent = '-' + discount.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺';
    document.getElementById('grand_total_display').textContent = grandTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺';
    
    validateCalculation();
}

function validateCalculation() {
    let itemsSubtotal = 0;
    let itemsTax = 0;
    
    parsedData.items.forEach(item => {
        const lineTotal = item.quantity * item.unit_price;
        itemsSubtotal += lineTotal;
        itemsTax += lineTotal * (item.tax_rate / 100);
    });
    
    const discount = parseFloat(document.getElementById('result_discount').value) || 0;
    const calculatedGrand = itemsSubtotal + itemsTax - discount;
    
    const errorDiv = document.getElementById('calculation_error');
    errorDiv.classList.add('hidden');
}

async function saveInvoice() {
    const invoiceData = {
        invoice_no: document.getElementById('result_invoice_no').value,
        invoice_date: document.getElementById('result_invoice_date').value,
        entity_name: document.getElementById('result_entity_name').value,
        entity_tax_id: document.getElementById('result_entity_tax_id').value,
        entity_tax_office: document.getElementById('result_entity_tax_office').value,
        items: parsedData.items,
        subtotal: parseFloat(document.getElementById('subtotal_display').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0,
        tax_total: parseFloat(document.getElementById('tax_total_display').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0,
        discount_total: parseFloat(document.getElementById('result_discount').value) || 0,
        grand_total: parseFloat(document.getElementById('grand_total_display').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0
    };
    
    try {
        const response = await fetch('<?php echo site_url('invoice_parser/api_save'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'invoice_data=' + encodeURIComponent(JSON.stringify(invoiceData))
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            window.location.href = '<?php echo site_url('invoices/detail/'); ?>' + result.invoice_id;
        } else {
            throw new Error(result.message);
        }
        
    } catch (error) {
        showToast('Kaydetme hatası: ' + error.message, 'error');
    }
}
</script>
