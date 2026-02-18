<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="flex h-[calc(100dvh-80px)] md:h-screen flex-col md:flex-row overflow-hidden bg-gray-50 dark:bg-[#101622]">
    
    <!-- LEFT SIDE: PRODUCTS -->
    <div id="panel-products" class="w-full md:w-2/3 flex flex-col h-full border-r border-gray-200 dark:border-gray-800">
        
        <!-- Search Bar -->
        <div class="p-4 bg-white dark:bg-[#151a25] border-b border-gray-200 dark:border-gray-800 flex gap-4">
            <div class="relative flex-1">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">search</span>
                <input type="text" id="searchInput" placeholder="Ürün adı, barkod veya kod ile arayın..." 
                       class="w-full pl-12 pr-4 py-3 bg-gray-100 dark:bg-[#1c2433] border-none rounded-xl text-gray-800 dark:text-gray-200 placeholder-gray-500 focus:ring-2 focus:ring-primary outline-none font-medium h-[50px]">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                     <button onclick="refreshProducts()" class="p-2 text-gray-400 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5" title="Ürün Listesini Yenile">
                        <span class="material-symbols-outlined" id="refreshIcon">sync</span>
                     </button>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar" id="productGridContainer">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="productGrid">
                <!-- Products will be loaded via JS -->
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: CART & CHECKOUT -->
    <div id="panel-cart" class="w-full md:w-1/3 bg-white dark:bg-[#151a25] h-full hidden md:flex flex-col shadow-2xl relative z-10">
        <!-- Mobile Back Button -->
        <div class="md:hidden p-2 bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-gray-800">
            <button onclick="toggleMobileView('products')" class="flex items-center gap-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-bold p-2 w-full">
                <span class="material-symbols-outlined">arrow_back</span>
                Ürünlere Dön
            </button>
        </div>
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
            <h2 class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">shopping_cart</span>
                Sepet
            </h2>
            <button onclick="clearCart()" class="text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Temizle</button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" id="cartItemsContainer">
            <div id="emptyCartMessage" class="flex flex-col items-center justify-center h-full text-gray-300 dark:text-gray-600">
                <span class="material-symbols-outlined text-6xl mb-2">production_quantity_limits</span>
                <p class="text-sm font-medium">Sepetiniz boş</p>
            </div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-[#1c2433] border-t border-gray-100 dark:border-gray-700">
            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Cari Seçimi</label>
            <div class="relative searchable-select z-20">
                <?php
                // Varsayılan olarak 'Peşin Müşteri' seçili gelsin
                $default_id = 0;
                $default_name = 'Peşin Müşteri';
                $pm_found = false;
                
                if (isset($entities)) {
                    foreach($entities as $e) {
                        if (mb_strtolower($e['name'], 'UTF-8') == 'peşin müşteri') {
                            $default_id = $e['id'];
                            $default_name = $e['name'];
                            $pm_found = true;
                            break;
                        }
                    }
                }
                ?>
                <div class="relative">
                    <input type="text" class="search-input w-full h-10 bg-white dark:bg-[#151a25] border border-gray-200 dark:border-gray-700 rounded-lg px-3 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-primary outline-none transition-all" 
                           placeholder="Cari aramak için yazın..." 
                           value="<?php echo htmlspecialchars($default_name); ?>" 
                           autocomplete="off">
                    <input type="hidden" id="customerSelect" class="hidden-input" value="<?php echo $default_id; ?>">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 pointer-events-none text-lg">expand_more</span>
                </div>
                <div class="options-list absolute bottom-full mb-1 w-full bg-white dark:bg-[#151a25] border border-gray-100 dark:border-gray-800 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                    <?php if (!$pm_found): ?>
                    <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-800 last:border-0 transition-colors flex justify-between items-center" data-value="0">
                        <span>Peşin Müşteri</span>
                    </div>
                    <?php endif; ?>
                    <?php foreach($entities as $entity): ?>
                        <div class="option-item p-3 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-800 last:border-0 transition-colors flex justify-between items-center" data-value="<?php echo $entity['id']; ?>">
                            <span><?php echo htmlspecialchars($entity['name']); ?></span>
                            <span class="text-xs text-gray-400 ml-1 block sm:inline">(<?php echo number_format($entity['balance'], 2); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-[#151a25] border-t border-gray-100 dark:border-gray-800">
            <div class="flex justify-between items-end mb-4">
                <span class="text-sm font-bold text-gray-500">Genel Toplam</span>
                <span class="text-3xl font-black text-gray-900 dark:text-white" id="grandTotal">0.00 ₺</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button onclick="openPaymentModal('CASH')" class="bg-emerald-500 hover:bg-emerald-600 text-white p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-1 transition-colors">
                    <span class="material-symbols-outlined">payments</span>
                    <span>NAKİT</span>
                </button>
                <button onclick="openPaymentModal('CREDIT_CARD')" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-1 transition-colors">
                    <span class="material-symbols-outlined">credit_card</span>
                    <span>KART</span>
                </button>
                <button onclick="openPaymentModal('BANK_TRANSFER')" class="bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-1 transition-colors">
                    <span class="material-symbols-outlined">account_balance</span>
                    <span>HAVALE / EFT</span>
                </button>
                <button onclick="openPaymentModal('CREDIT')" class="bg-slate-700 hover:bg-slate-800 text-white p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-1 transition-colors">
                    <span class="material-symbols-outlined">pending_actions</span>
                    <span>VERESİYE</span>
                </button>
            </div>
        </div>
    </div>
    </div>
    <!-- ... (Mobile Bottom Bar and Modals remain same) ... -->

<script>
    // ... (toggleMobileView remains same) ...
    window.toggleMobileView = function(view) {
        const products = document.getElementById('panel-products');
        const cart = document.getElementById('panel-cart');
        const bar = document.getElementById('mobile-bottom-bar');
        
        if(view === 'cart') {
            products.classList.add('hidden');
            cart.classList.remove('hidden');
            cart.classList.add('flex');
            bar.classList.add('hidden'); 
        } else {
            products.classList.remove('hidden');
            cart.classList.add('hidden');
            cart.classList.remove('flex');
            bar.classList.remove('hidden');
        }
    };

    // Initialize with PHP data but allow updates
    // Use var to allow re-declaration in SPA/Ajax environments
    var allProducts = {}; 
    var initialProducts = <?php echo !empty($products) ? json_encode($products, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]'; ?>;
    var base_url = '<?php echo base_url(); ?>';
    var wallets = <?php echo !empty($wallets) ? json_encode($wallets, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]'; ?>;
    var cart = [];

    // Product Search Optimization
    var productCache = null;

    // Convert array to object keyed by ID
    function normalizeProducts(prodArray) {
        const obj = {};
        if (Array.isArray(prodArray)) {
            prodArray.forEach(p => { obj[p.id] = p; });
        }
        return obj;
    }



    window.refreshProducts = async function() {
        const icon = document.getElementById('refreshIcon');
        icon.classList.add('animate-spin');
        
        try {
            const res = await fetch('<?php echo site_url('sales/api_get_products'); ?>');
            const data = await res.json();
            
            if (data.status === 'success') {
                allProducts = normalizeProducts(data.products);
                renderProductGrid(data.products);
                
                // Re-apply filter if search input has value
                const currentSearch = document.getElementById('searchInput').value;
                if (currentSearch) {
                    window.filterProducts();
                }
                showToast('Ürün listesi güncellendi', 'success');
            } else {
                showToast('Hata: ' + data.message, 'error');
            }
        } catch (e) {
            showToast('Bağlantı hatası', 'error');
            console.error(e);
        } finally {
            icon.classList.remove('animate-spin');
        }
    };

    window.renderProductGrid = function(products) {
        if (!Array.isArray(products)) {
            console.error('Render error: products is not an array', products);
            products = [];
        }

        const grid = document.getElementById('productGrid');
        if (!grid) return;
        
        grid.innerHTML = '';
        
        products.forEach(p => {
             const name = (p.name || '').toLowerCase();
             const barcode = (p.barcode || '').toLowerCase();
             const match = (p.match_names || '').toLowerCase();
             const stock = parseFloat(p.stock_quantity || 0);
             const price = parseFloat(p.satis_fiyat || 0).toFixed(2);
             
             const imgHtml = p.gorsel 
                ? `<img src="${base_url}${p.gorsel}" class="max-h-full max-w-full object-contain mix-blend-multiply dark:mix-blend-normal hover:scale-110 transition-transform duration-300 cursor-zoom-in" onclick="event.stopPropagation(); openImageModal(this.src, '${p.name}')">` 
                : `<span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600">inventory_2</span>`;

             const div = document.createElement('div');
             div.className = 'product-card group bg-white dark:bg-[#151a25] rounded-2xl p-3 cursor-pointer border border-gray-100 dark:border-gray-800 hover:border-primary dark:hover:border-primary hover:shadow-lg hover:shadow-primary/10 transition-all relative overflow-hidden flex flex-col h-[240px]';
             div.setAttribute('onclick', `addToCart(${p.id})`);
             div.setAttribute('data-name', name);
             div.setAttribute('data-barcode', barcode);
             div.setAttribute('data-match', match);
             
             div.innerHTML = `
                <div class="absolute top-2 right-2 z-10">
                    <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[9px] font-black rounded-md shadow-sm">
                        Stok: ${stock}
                    </span>
                </div>

                <div class="flex-1 flex items-center justify-center p-2 min-h-0 bg-gray-50/50 dark:bg-black/5 rounded-xl">
                    ${imgHtml}
                </div>
                
                <div class="mt-2 text-center flex flex-col gap-1 shrink-0">
                    <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 line-clamp-2 leading-tight min-h-[2em]">
                        ${p.name}
                    </h3>
                    <p class="text-primary font-black text-lg leading-none">
                        ${price} ₺
                    </p>
                </div>
             `;
             grid.appendChild(div);
        });
        
        // Rebuild cache immediately after rendering
        window.buildProductCache();
    };

    window.buildProductCache = function() {
        productCache = Array.from(document.querySelectorAll('.product-card')).map(card => ({
            el: card,
            searchStr: (
                (card.getAttribute('data-name') || "") + " " + 
                (card.getAttribute('data-barcode') || "") + " " + 
                (card.getAttribute('data-match') || "")
            ).toLocaleLowerCase("tr-TR")
        }));
    };



    window.filterProducts = function() {
        const query = document.getElementById('searchInput').value.toLocaleLowerCase("tr-TR").trim();
        
        if (!productCache) window.buildProductCache();

        // Use requestAnimationFrame for smoother UI during heavy filtering
        requestAnimationFrame(() => {
            productCache.forEach(item => {
                const isMatch = !query || item.searchStr.includes(query);
                // Minimize layout trashing by only changing if needed
                if (item.el.style.display !== (isMatch ? 'flex' : 'none')) {
                    item.el.style.display = isMatch ? 'flex' : 'none';
                }
            });
        });
    };
    
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        let debounceTimer;
        // Search on 'input' (covers typing, pasting, clearing)
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(window.filterProducts, 50);
        });
        
        // Also keep keyup for immediate feedback on specific keys if needed, but input is usually enough.
        // Focusing the input builds the cache if empty
        searchInput.addEventListener('focus', () => {
            if (!productCache) window.buildProductCache();
        });
    }

    window.addToCart = function(id) {
        // Ensure id is integer for comparison
        id = parseInt(id); 
        const p = allProducts[id];
        
        // Check if item already exists
        const existingIndex = cart.findIndex(i => parseInt(i.id) === id);
        
        if (existingIndex !== -1) {
            cart[existingIndex].qty++;
        } else {
            cart.push({id: p.id, name: p.name, price: parseFloat(p.satis_fiyat), qty: 1});
        }
        renderCart();
    };

    window.updateQty = function(index, delta) {
        if (cart[index]) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }
    };

    window.updateQtyInput = function(index, val) {
        const newQty = parseFloat(val);
        if (cart[index]) {
            if (newQty > 0) {
                cart[index].qty = newQty;
            }
            renderCart();
        }
    };
    
    window.removeItem = function(index) {
        cart.splice(index, 1);
        renderCart();
    };

    window.clearCart = function() {
        cart = [];
        renderCart();
    };

    window.renderCart = function() {
        const container = document.getElementById('cartItemsContainer');
        const emptyMsg = document.getElementById('emptyCartMessage');
        if (emptyMsg) emptyMsg.style.display = cart.length ? 'none' : 'flex';
        
        container.querySelectorAll('div:not(#emptyCartMessage)').forEach(el => el.remove());
        
        let total = 0;
        cart.forEach((item, index) => {
            total += item.price * item.qty;
            const div = document.createElement('div');
            div.className = 'bg-white dark:bg-[#1c2433] p-3 rounded-xl border border-gray-100 dark:border-gray-800 flex justify-between items-center group';
            
            div.innerHTML = `
                <div class="flex-1 min-w-0 mr-2">
                    <h4 class="text-sm font-bold dark:text-white truncate">${item.name}</h4>
                    <p class="text-primary font-bold text-xs">${item.price.toFixed(2)} ₺</p>
                </div>
                
                <div class="flex items-center gap-1 bg-gray-50 dark:bg-black/20 rounded-lg p-1">
                    <button onclick="updateQty(${index}, -1)" class="size-6 flex items-center justify-center bg-white dark:bg-[#151a25] rounded-md shadow-sm hover:text-red-500 font-bold text-gray-500">-</button>
                    <input type="number" step="1" min="1" value="${item.qty}" onchange="updateQtyInput(${index}, this.value)" 
                           class="w-12 text-center bg-transparent border-none p-0 text-sm font-bold text-gray-800 dark:text-white focus:ring-0">
                    <button onclick="updateQty(${index}, 1)" class="size-6 flex items-center justify-center bg-white dark:bg-[#151a25] rounded-md shadow-sm hover:text-green-500 font-bold text-gray-500">+</button>
                </div>
            `;
            container.appendChild(div);
        });
        const grandTotalEl = document.getElementById('grandTotal');
        if (grandTotalEl) grandTotalEl.innerText = total.toFixed(2) + ' ₺';
        
        const mobileTotalEl = document.getElementById('mobileGrandTotal');
        if (mobileTotalEl) mobileTotalEl.innerText = total.toFixed(2) + ' ₺';
        
        // Update bar visibility based on cart content if needed, but stick to view logic
        const bar = document.getElementById('mobile-bottom-bar');
        const cartPanel = document.getElementById('panel-cart');
        
        if (bar && cartPanel) {
            if (cart.length > 0 && cartPanel.classList.contains('hidden')) {
                 bar.classList.remove('translate-y-full', 'opacity-0');
            } else if (cart.length === 0) {
                 // bar.classList.add('translate-y-full', 'opacity-0'); // Optional: hide if empty
            }
        }
    };

    window.openPaymentModal = function(type) {
        if (!cart.length) return showToast('Sepet boş!', 'error');
        
        const customerId = document.getElementById('customerSelect').value;
        if (type === 'CREDIT' && (customerId === '0' || !customerId)) {
            return showToast('Veresiye işlemi için lütfen bir Cariye seçiniz!', 'alert');
        }

        document.getElementById('paymentModalAmount').innerText = document.getElementById('grandTotal').innerText;
        document.getElementById('paymentType').value = type;
        
        const walletContainer = document.getElementById('walletSelectContainer');
        const creditWarning = document.getElementById('creditWarning');
        const title = document.getElementById('paymentModalTitle');

        if (type === 'CREDIT') {
            walletContainer.classList.add('hidden');
            creditWarning.classList.remove('hidden');
            title.innerText = 'Veresiye Satış Onayı';
        } else {
            walletContainer.classList.remove('hidden');
            creditWarning.classList.add('hidden');
            title.innerText = 'Ödeme Onayı';
            
            const select = document.getElementById('walletSelect');
            select.innerHTML = '';
            
            // Filter wallets: If CASH type, show only Cash wallets. If CREDIT_CARD, show only POS/Bank wallets.
            const filteredWallets = wallets.filter(w => {
                if (type === 'CASH') return w.wallet_type === 'CASH';
                if (type === 'CREDIT_CARD' || type === 'BANK_TRANSFER') return w.wallet_type !== 'CASH';
                return true; 
            });
            
            // Fallback if no specific wallet type found, show all or show error
            if (filteredWallets.length === 0 && wallets.length > 0) {
                 // If strict filtering returns nothing, maybe just show all to avoid blocking user?
                 // or just show matching ones. Let's show filtered.
            }

            filteredWallets.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.text = w.name;
                select.appendChild(opt);
            });
        }
        
        document.getElementById('paymentModal').classList.remove('hidden');
    };

    window.closePaymentModal = function() { document.getElementById('paymentModal').classList.add('hidden'); };

    window.processSale = async function() {
        const type = document.getElementById('paymentType').value;
        const walletId = document.getElementById('walletSelect').value;
        
        const payload = {
            wallet_id: type === 'CREDIT' ? null : walletId,
            payment_type: type,
            customer_id: document.getElementById('customerSelect').value,
            items: cart
        };
        
        if (type !== 'CREDIT' && !payload.wallet_id) {
            return showToast('Lütfen bir kasa/hesap seçiniz!', 'error');
        }

        try {
            const res = await fetch('<?php echo site_url('sales/api_save_sale'); ?>', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                showToast('Satış başarıyla tamamlandı.');
                setTimeout(() => location.reload(), 1500);
            } else showToast('Hata: ' + result.message, 'error');
        } catch (e) { showToast('Hata: ' + e.message, 'error'); }
    };

    window.setupSearchableSelects = function() {
        document.querySelectorAll('.searchable-select').forEach(container => {
            const input = container.querySelector('.search-input');
            const hiddenInput = container.querySelector('.hidden-input');
            const list = container.querySelector('.options-list');
            const options = list.querySelectorAll('.option-item');

            const toggleList = (show) => {
                if(show) list.classList.remove('hidden');
                else list.classList.add('hidden');
            };

            input.addEventListener('click', (e) => {
                e.stopPropagation();
                input.value = '';
                toggleList(true);
                options.forEach(opt => opt.classList.remove('hidden'));
            });

            input.addEventListener('keyup', (e) => {
                const val = e.target.value.toLocaleLowerCase("tr-TR");
                toggleList(true);
                options.forEach(opt => {
                    const text = opt.textContent.toLocaleLowerCase("tr-TR");
                    if(text.includes(val)) opt.classList.remove('hidden');
                    else opt.classList.add('hidden');
                });
            });

            options.forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = opt.dataset.value;
                    const nameSpan = opt.querySelector('span:first-child');
                    const text = nameSpan ? nameSpan.textContent : opt.textContent;
                    
                    input.value = text.trim();
                    hiddenInput.value = value;
                    toggleList(false);
                });
            });

            document.addEventListener('click', (e) => {
                if(!container.contains(e.target)) toggleList(false);
            });
        });
    };

    // Initial Load Logic (Moved to end to ensure all functions are defined)
    var initPosPage = () => {
        // Double check renderProductGrid availability
        if (typeof window.renderProductGrid !== 'function') {
            console.warn('renderProductGrid not ready, retrying...');
            setTimeout(initPosPage, 50);
            return;
        }

        const productsToLoad = Array.isArray(initialProducts) ? initialProducts : [];
        allProducts = normalizeProducts(productsToLoad);
        renderProductGrid(productsToLoad);
        if(window.setupSearchableSelects) window.setupSearchableSelects();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPosPage);
    } else {
        initPosPage();
    }
    
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) initPosPage();
    });
</script>
