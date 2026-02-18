<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="flex flex-col flex-1 w-full min-w-0">
    <header class="w-full bg-background-light dark:bg-background-dark border-b border-slate-200 dark:border-slate-800/50 pt-6 pb-2 px-4 sm:px-8 shrink-0">
        <div class="flex flex-col gap-1 mb-2">
            <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Fiyat Sorgula</h2>
            <p class="text-[#9da6b9] text-sm sm:text-base font-normal">Barkod okutun veya ürün adı arayın.</p>
        </div>
    </header>

    <main class="flex-1 p-4 sm:px-8 w-full min-w-0 flex flex-col items-center justify-start pt-12">
        
        <div class="w-full max-w-2xl bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl p-8">
            <div class="relative mb-8">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                    <span class="material-symbols-outlined text-[32px]">barcode_scanner</span>
                </span>
                <input type="text" id="searchInput" 
                       class="w-full h-16 pl-14 pr-32 rounded-2xl bg-slate-50 dark:bg-input-dark border-2 border-slate-100 dark:border-slate-700 text-xl font-bold focus:border-primary transition-all text-slate-900 dark:text-white"
                       placeholder="Barkod okutun veya isim yazın...">
                
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-2">
                    <label for="fileInput" class="w-12 h-12 flex items-center justify-center rounded-xl bg-primary text-white hover:bg-blue-600 transition-colors shadow-lg shadow-primary/20 cursor-pointer">
                        <span class="material-symbols-outlined">photo_camera</span>
                    </label>
                </div>
            </div>

            <div id="aiSearchArea" class="hidden mb-8 p-6 bg-primary/5 border border-primary/20 rounded-3xl animate-in fade-in zoom-in duration-300">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary relative">
                        <span class="material-symbols-outlined text-[32px]">neurology</span>
                        <div class="absolute inset-0 rounded-full border-2 border-primary/20 border-t-primary animate-spin"></div>
                    </div>
                    <div>
                        <p class="font-black text-primary text-base tracking-tight">Gemini AI Analiz Ediyor...</p>
                        <p class="text-xs text-slate-500 font-medium">Görseldeki ürün tanımlanıyor, lütfen bekleyin.</p>
                    </div>
                </div>
            </div>

            <div id="resultArea" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div id="multipleResults" class="hidden mb-8">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">list</span> Benzer Ürünler Bulundu
                    </p>
                    <div id="resultsList" class="grid grid-cols-1 gap-3">
                        <!-- Benzer ürünler buraya gelecek -->
                    </div>
                </div>

                <div id="singleResult">
                    <div id="aiBadge" class="hidden mb-6 flex justify-center">
                    <span class="px-4 py-1.5 bg-gradient-to-r from-primary/20 to-blue-500/20 text-primary text-[10px] font-black uppercase tracking-[0.2em] rounded-full flex items-center gap-2 border border-primary/20 shadow-sm">
                        <span class="material-symbols-outlined text-sm animate-pulse">auto_awesome</span> Gemini AI ile Tanımlandı
                    </span>
                </div>

                <div class="flex flex-col items-center text-center">
                    <!-- Ürün Görseli Container -->
                    <div class="relative mb-8 group">
                        <div class="absolute inset-0 bg-primary/20 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative w-48 h-48 rounded-[2rem] overflow-hidden border-4 border-white dark:border-slate-800 shadow-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center">
                            <img id="resImage" src="" alt="Ürün" class="w-full h-full object-cover hidden">
                            <div id="resImagePlaceholder" class="flex flex-col items-center gap-3 text-slate-300 dark:text-slate-700">
                                <span class="material-symbols-outlined text-[64px]">inventory_2</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest">Görsel Yok</span>
                            </div>
                        </div>
                    </div>

                    <h3 id="resName" class="text-3xl font-black text-slate-900 dark:text-white mb-2 leading-tight tracking-tight">---</h3>
                    <p id="resBarcode" class="text-xs text-slate-400 font-black tracking-[0.3em] mb-10 bg-slate-100 dark:bg-slate-800/50 px-4 py-1.5 rounded-full">---</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                        <!-- Satış Fiyatı Kartı -->
                        <div class="relative overflow-hidden p-6 rounded-[2.5rem] bg-gradient-to-br from-primary to-blue-600 text-white shadow-xl shadow-primary/30 border border-white/10 group">
                            <div class="absolute top-0 right-0 p-4 opacity-10 transform translate-x-2 -translate-y-2">
                                <span class="material-symbols-outlined text-[80px]">payments</span>
                            </div>
                            <p class="relative text-[10px] font-black uppercase tracking-[0.2em] mb-4 text-white/70">Satış Fiyatı</p>
                            <div class="relative flex items-end justify-center gap-2">
                                <h4 id="resPrice" class="text-4xl font-black tracking-tighter">0.00</h4>
                                <span class="text-2xl font-black mb-1 opacity-80">₺</span>
                            </div>
                        </div>

                        <!-- Kalan Stok Kartı -->
                        <div class="relative overflow-hidden p-6 rounded-[2.5rem] bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50 shadow-sm group">
                            <div class="absolute top-0 right-0 p-4 opacity-5 dark:opacity-10 transform translate-x-2 -translate-y-2">
                                <span class="material-symbols-outlined text-[80px]">inventory</span>
                            </div>
                            <p class="relative text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-4">Mevcut Stok</p>
                            <div class="relative flex items-end justify-center gap-2">
                                <h4 id="resStock" class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">0</h4>
                                <span id="resUnit" class="text-sm font-bold mb-2 text-slate-500 dark:text-slate-400 uppercase tracking-widest">Adet</span>
                            </div>
                        </div>
                    </div>

                    <div id="resCritical" class="hidden mt-8 w-full px-6 py-4 bg-red-500/10 border border-red-500/20 rounded-3xl text-red-500 text-[11px] font-black uppercase tracking-[0.2em] animate-pulse flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">warning</span>
                        KRİTİK STOK SEVİYESİNDE!
                    </div>
                </div>
            </div>
            </div> <!-- Close resultArea -->

            <!-- No Product Found (AI Identified but not in DB) -->
            <div id="noProductArea" class="hidden animate-in fade-in zoom-in duration-500">
                <div class="flex flex-col items-center text-center p-6 bg-slate-50 dark:bg-slate-800/40 rounded-[2.5rem] border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <div class="size-20 rounded-3xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 mb-6">
                        <span class="material-symbols-outlined text-[40px]">inventory_2</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">Ürün Stokta Bulunamadı</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-8 max-w-[250px] mx-auto">AI ürünü <span id="identifiedName" class="font-bold text-slate-900 dark:text-white">---</span> olarak tanımladı ancak kayıtlı bir stok kartı bulunamadı.</p>
                    
                    <a id="aiCreateBtn" href="#" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-sm flex items-center justify-center gap-3 shadow-lg shadow-emerald-500/20 transition-all">
                        <span class="material-symbols-outlined">add_circle</span>
                        Yeni Stok Kartı Oluştur
                    </a>
                </div>
            </div>

            <div id="emptyArea" class="flex flex-col items-center py-16 text-slate-300 dark:text-slate-700">
                <div class="w-24 h-24 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[48px] opacity-20">search_check</span>
                </div>
                <p class="font-bold text-sm text-slate-400 dark:text-slate-500">Barkod okutun veya yazmaya başlayın.</p>
            </div>
        </div>

    </main>
</div>

<!-- Camera fallback input -->
<input type="file" id="fileInput" accept="image/*" capture="environment" class="hidden">

<canvas id="captureCanvas" class="hidden"></canvas>

<script>
(function() {
    const searchInput = document.getElementById('searchInput');
    const resultArea = document.getElementById('resultArea');
    const emptyArea = document.getElementById('emptyArea');
    const aiSearchArea = document.getElementById('aiSearchArea');
    const aiBadge = document.getElementById('aiBadge');
    const fileInput = document.getElementById('fileInput');

    const GEMINI_API_URL = '<?php echo site_url('inventory_check/api_gemini_search'); ?>';
    const SEARCH_STOCK_API_URL = '<?php echo site_url('inventory_check/api_search_stock'); ?>';

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        aiSearchArea.classList.remove('hidden');
        emptyArea.classList.add('hidden');
        resultArea.classList.add('hidden');

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const MAX_SIZE = 1024;

                if (width > height) {
                    if (width > MAX_SIZE) {
                        height *= MAX_SIZE / width;
                        width = MAX_SIZE;
                    }
                } else {
                    if (height > MAX_SIZE) {
                        width *= MAX_SIZE / height;
                        height = MAX_SIZE;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                const compressedData = canvas.toDataURL('image/jpeg', 0.7);
                // Save to sessionStorage for create page
                sessionStorage.setItem('pendingAIImage', compressedData);
                performAiSearch(compressedData);
            };
            img.onerror = function() {
                aiSearchArea.classList.add('hidden');
                emptyArea.classList.remove('hidden');
                alert('Görsel işlenemedi.');
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    function showNoMatch(aiData) {
        const noProductArea = document.getElementById('noProductArea');
        const identifiedName = document.getElementById('identifiedName');
        const aiCreateBtn = document.getElementById('aiCreateBtn');

        identifiedName.innerText = aiData.name;
        
        const createUrl = '<?php echo site_url("inventory/create"); ?>' + 
                         '?new_search=' + encodeURIComponent(aiData.name) + 
                         '&barcode=' + (aiData.barcode || '');
        
        aiCreateBtn.setAttribute('href', createUrl);

        noProductArea.classList.remove('hidden');
        resultArea.classList.add('hidden');
        emptyArea.classList.add('hidden');
    }

    function performAiSearch(imageData) {
        fetch(GEMINI_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: imageData })
        })
        .then(res => res.json())
        .then(data => {
            console.log('AI Search Response:', data);
            aiSearchArea.classList.add('hidden');
            
            if (data.status === 'success') {
                if (data.items && data.items.length > 0) {
                    if (data.items.length === 1) {
                        showResult(data.items[0], true);
                    } else {
                        showMultipleResults(data.items, true);
                    }
                } else if (data.ai_data && data.ai_data.name) {
                    // AI Identified but no match in DB
                    showNoMatch(data.ai_data);
                } else {
                    alert('Ürün tanımlanamadı veya kategorize edilemedi.');
                    emptyArea.classList.remove('hidden');
                }
            } else {
                alert(data.message || 'Bir hata oluştu.');
                emptyArea.classList.remove('hidden');
            }
        })
        .catch(err => {
            aiSearchArea.classList.add('hidden');
            alert('Bağlantı hatası: ' + err.message);
            emptyArea.classList.remove('hidden');
        });
    }

    function showMultipleResults(items, isAi = false) {
        const resultsList = document.getElementById('resultsList');
        const multipleResults = document.getElementById('multipleResults');
        const singleResult = document.getElementById('singleResult');
        const noProductArea = document.getElementById('noProductArea');
        
        resultsList.innerHTML = '';
        noProductArea.classList.add('hidden');
        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50 rounded-2xl cursor-pointer hover:border-primary transition-all group';
            div.onclick = () => showResult(item, isAi);
            
            let imgSrc = '';
            if (item.gorsel) {
                if (item.gorsel.startsWith('data:')) {
                    imgSrc = item.gorsel;
                } else {
                    const path = item.gorsel.startsWith('/') ? item.gorsel.substring(1) : item.gorsel;
                    imgSrc = '<?php echo base_url(); ?>' + path;
                }
            }

            const imgHtml = imgSrc 
                ? `<img src="${imgSrc}" class="w-12 h-12 object-cover rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm cursor-zoom-in" onclick="event.stopPropagation(); openImageModal(this.src, '${item.urun_adi.replace(/'/g, "\\'")}')">`
                : `<div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-sm">inventory_2</span>
                   </div>`;
            
            div.innerHTML = `
                ${imgHtml}
                <div class="flex-1 text-left">
                    <p class="text-sm font-black text-slate-900 dark:text-white line-clamp-1 group-hover:text-primary transition-colors">${item.urun_adi}</p>
                    <p class="text-[10px] text-slate-500 font-mono tracking-wider">${item.barcode || 'Barkodsuz'}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black text-primary">${parseFloat(item.satis_fiyat).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺</p>
                    <p class="text-[10px] text-slate-400">${item.miktar} ${item.birim || 'Adet'}</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 group-hover:translate-x-1 transition-transform">chevron_right</span>
            `;
            resultsList.appendChild(div);
        });

        multipleResults.classList.remove('hidden');
        singleResult.classList.add('hidden');
        resultArea.classList.remove('hidden');
        emptyArea.classList.add('hidden');
    }

    function showResult(item, isAi = false) {
        document.getElementById('multipleResults').classList.add('hidden');
        document.getElementById('singleResult').classList.remove('hidden');
        if (document.getElementById('noProductArea')) {
            document.getElementById('noProductArea').classList.add('hidden');
        }
        
        document.getElementById('resName').innerText = item.urun_adi;
        document.getElementById('resBarcode').innerText = item.barcode || 'Barkod Yok';
        document.getElementById('resPrice').innerText = parseFloat(item.satis_fiyat).toLocaleString('tr-TR', { minimumFractionDigits: 2 });
        document.getElementById('resStock').innerText = parseFloat(item.miktar);
        document.getElementById('resUnit').innerText = item.birim || 'Adet';
        
        const resImage = document.getElementById('resImage');
        const resPlaceholder = document.getElementById('resImagePlaceholder');
        
        if (item.gorsel) {
            // Check if gorsel is base64 or a path
            if (item.gorsel.startsWith('data:')) {
                resImage.src = item.gorsel;
            } else {
                const path = item.gorsel.startsWith('/') ? item.gorsel.substring(1) : item.gorsel;
                resImage.src = '<?php echo base_url(); ?>' + path;
            }
            resImage.classList.remove('hidden');
            resPlaceholder.classList.add('hidden');
            resImage.onclick = () => openImageModal(resImage.src, item.urun_adi);
            resImage.style.cursor = 'zoom-in';
        } else {
            resImage.classList.add('hidden');
            resPlaceholder.classList.remove('hidden');
            resImage.onclick = null;
        }

        if (parseFloat(item.miktar) <= parseFloat(item.kritik_esik || 0)) {
            document.getElementById('resCritical').classList.remove('hidden');
        } else {
            document.getElementById('resCritical').classList.add('hidden');
        }

        if (isAi) aiBadge.classList.remove('hidden');
        else aiBadge.classList.add('hidden');

        resultArea.classList.remove('hidden');
        emptyArea.classList.add('hidden');
    }

    let searchTimeout;
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = e.target.value.trim();
            if (query.length < 2) return;
            
            clearTimeout(searchTimeout);
            fetch(SEARCH_STOCK_API_URL + '?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.items && data.items.length > 0) {
                        if (data.items.length === 1) {
                            showResult(data.items[0], false);
                        } else {
                            showMultipleResults(data.items, false);
                        }
                    } else {
                        resultArea.classList.add('hidden');
                        if (document.getElementById('noProductArea')) {
                            document.getElementById('noProductArea').classList.add('hidden');
                        }
                        emptyArea.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                    alert('Arama sırasında bir hata oluştu.');
                });
        }
    });

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        if (query.length < 2) {
            resultArea.classList.add('hidden');
            emptyArea.classList.remove('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(SEARCH_STOCK_API_URL + '?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.items && data.items.length > 0) {
                        if (data.items.length === 1) {
                            showResult(data.items[0], false);
                        } else {
                            showMultipleResults(data.items, false);
                        }
                    } else {
                        resultArea.classList.add('hidden');
                        if (document.getElementById('noProductArea')) {
                            document.getElementById('noProductArea').classList.add('hidden');
                        }
                        emptyArea.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    console.error('Input search error:', err);
                });
        }, 300);
    });

    // Manual focus for SPA support
    setTimeout(() => searchInput.focus(), 100);
})();
</script>
