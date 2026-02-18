<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-4 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo $product['id'] > 0 ? site_url('inventory/detail/'.$product['id']) : site_url('inventory'); ?>" class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white"><?php echo $product['id'] > 0 ? 'Stok Düzenle' : 'Yeni Ürün Ekle'; ?></h2>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 overflow-auto">
        <form id="edit_product_form" method="POST" class="max-w-4xl mx-auto">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            
            <div class="bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">edit_note</span>
                    Ürün Bilgileri
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Image Upload -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Ürün Görseli</label>
                        <div class="flex items-center gap-6">
                            <div class="relative group size-24 rounded-xl bg-slate-100 dark:bg-white/5 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden">
                                <?php if(!empty($product['gorsel'])): ?>
                                    <img src="<?php echo base_url($product['gorsel']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                <?php endif; ?>
                                <img id="preview_img" class="absolute inset-0 w-full h-full object-cover hidden">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="gorsel" id="gorsel_input" accept="image/*" class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-primary/10 file:text-primary
                                  hover:file:bg-primary/20
                                " onchange="previewImage(this)">
                                <p class="mt-1 text-xs text-slate-400">PNG, JPG, WEBP (Max 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Ürün Adı</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Eşleşen İsimler (Alias)</label>
                            <span class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-bold">Önerilen: Virgül ile ayırın</span>
                        </div>
                        <textarea name="match_names" rows="2" placeholder="Örn: 18W Led, Tavan Lambası, ACK Armatür..."
                                  class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-medium focus:border-primary transition-all outline-none text-slate-900 dark:text-white"><?php echo htmlspecialchars($product['match_names'] ?? ''); ?></textarea>
                        <p class="mt-1 text-[10px] text-slate-400">Bu ürünü ararken kullanılabilecek alternatif isimler. Personel bu isimlerden herhangi birini yazdığında bu ürüne ulaşabilecektir.</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Barkod</label>
                        <div class="relative">
                            <input type="text" name="barcode" id="barcode_input" value="<?php echo htmlspecialchars($product['barcode']); ?>"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white pr-10">
                            <button type="button" onclick="generateBarcode()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-primary hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors" title="Rastgele Barkod Oluştur">
                                <span class="material-symbols-outlined text-[20px]">autorenew</span>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Birim</label>
                        <select name="unit" class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <?php 
                            $units = ['Adet', 'Kg', 'Gram', 'Litre', 'Metre', 'M²', 'M³', 'Paket', 'Kutu', 'Saat'];
                            foreach($units as $u) {
                                $selected = ($product['unit'] == $u) ? 'selected' : '';
                                echo "<option value='$u' $selected>$u</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Alış Fiyatı (Son)</label>
                        <div class="relative">
                            <input type="number" name="buying_price" value="<?php echo $product['last_buy_price']; ?>" step="any" min="0"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white pr-10">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₺</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Satış Fiyatı</label>
                        <div class="relative">
                            <input type="number" name="satis_fiyat" value="<?php echo $product['satis_fiyat']; ?>" step="any" min="0"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white pr-10">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₺</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">KDV Oranı (%)</label>
                        <select name="tax_rate" class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="0" <?php echo $product['tax_rate'] == 0 ? 'selected' : ''; ?>>%0</option>
                            <option value="1" <?php echo $product['tax_rate'] == 1 ? 'selected' : ''; ?>>%1</option>
                            <option value="10" <?php echo $product['tax_rate'] == 10 ? 'selected' : ''; ?>>%10</option>
                            <option value="20" <?php echo $product['tax_rate'] == 20 ? 'selected' : ''; ?>>%20</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Kritik Stok Seviyesi</label>
                        <input type="number" name="critical_level" value="<?php echo $product['critical_level']; ?>" step="any" min="0"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Mevcut Stok Miktarı</label>
                        <input type="number" name="stock_quantity" value="<?php echo isset($product['stock_quantity']) ? $product['stock_quantity'] : 0; ?>" step="any"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-800 rounded-xl text-sm font-bold focus:border-primary transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="<?php echo $product['id'] > 0 ? site_url('inventory/detail/'.$product['id']) : site_url('inventory'); ?>" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl font-bold transition-colors text-center">
                    İptal
                </a>
                <button type="submit" class="px-8 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </main>
</div>

</script>
<script>
(function() {
    const initImageData = () => {
        const pendingImage = sessionStorage.getItem('pendingAIImage');
        if (pendingImage && <?php echo $product['id']; ?> == 0) {
            const preview = document.getElementById('preview_img');
            const placeholder = document.querySelector('.relative.group.size-24 span.material-symbols-outlined');
            if (preview) {
                preview.src = pendingImage;
                preview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
            if (window.showToast) showToast('Analiz edilen görsel otomatik yüklendi', 'info');
        }
    };

    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', initImageData);
    } else {
        initImageData();
    }
})();
</script>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('preview_img').classList.remove('hidden');
            const placeholder = document.querySelector('.relative.group.size-24 span.material-symbols-outlined');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function generateBarcode() {
    // Basic EAN-13 like generator (not valid checksum, just random 13 digits)
    let result = '';
    for (let i = 0; i < 13; i++) {
        result += Math.floor(Math.random() * 10);
    }
    document.getElementById('barcode_input').value = result;
}

document.getElementById('edit_product_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Check for pending AI image if no new file selected
    const pendingImage = sessionStorage.getItem('pendingAIImage');
    if (pendingImage && !document.getElementById('gorsel_input').files.length && <?php echo $product['id']; ?> == 0) {
        formData.append('ai_image_base64', pendingImage);
    }
    
    try {
        const response = await fetch('<?php echo site_url('inventory/api_update'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast(result.message || 'Başarılı', 'success');
            sessionStorage.removeItem('pendingAIImage');
            setTimeout(() => {
                window.location.href = '<?php echo site_url('inventory/detail/'); ?>' + result.id;
            }, 1000);
        } else {
            showToast('Hata: ' + (result.message || 'Bir sorun oluştu'), 'error');
        }
    } catch (err) {
        showToast('Bağlantı hatası: ' + err.message, 'error');
    }
});
</script>
