<?php
// views/invoice/upload.php
$pageTitle = "Fatura Yükle";
include __DIR__ . '/../../views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">upload_file</span>
            Fatura / Fiş Yükle
        </h2>
        
        <form action="<?php echo public_url('invoice/analyze'); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <span class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Fatura yüklemek için tıklayın</span> veya sürükleyin</p>
                        <p class="text-xs text-gray-500">PDF, PNG, JPG, WEBP (MAX. 5MB)</p>
                    </div>
                    <input id="dropzone-file" type="file" name="invoice_image" class="hidden" accept="image/*,application/pdf" required />
                </label>
            </div>

            <button type="submit" class="w-full text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">auto_awesome</span>
                Gemini AI ile Analiz Et
            </button>
            
            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400">VEYA</span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>

            <button type="button" onclick="startScanner()" class="w-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-3 text-center flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">qr_code_scanner</span>
                Barkod Tara (Hızlı Eşleşme)
            </button>
        </form>
    </div>
</div>

<!-- Scanner Modal -->
<div id="scannerModal" class="fixed inset-0 bg-black/80 hidden z-50 flex flex-col items-center justify-center">
    <div class="relative bg-white p-4 rounded-lg shadow-lg w-full max-w-md">
        <button onclick="stopScanner()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500">
            <span class="material-symbols-outlined">close</span>
        </button>
        <h3 class="text-lg font-bold mb-4 text-center">Barkod Tara</h3>
        <div id="reader" class="w-full h-64 bg-black"></div>
        <p class="text-xs text-center text-gray-500 mt-2">Kamerayı barkoda tutun.</p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrcodeScanner = null;

function startScanner() {
    document.getElementById('scannerModal').classList.remove('hidden');
    
    html5QrcodeScanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess);
}

function onScanSuccess(decodedText, decodedResult) {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            document.getElementById('scannerModal').classList.add('hidden');
            // Redirect to inventory detail to show stock
            // Need to find ID first? Or search page?
            // Let's go to search api logic or handle it. 
            // Better: Go to a quick view page.
            window.location.href = "<?php echo public_url('inventory-check'); ?>?barcode=" + decodedText;
        }).catch(err => console.error(err));
    }
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
             document.getElementById('scannerModal').classList.add('hidden');
        }).catch(err => console.error(err));
    }
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
