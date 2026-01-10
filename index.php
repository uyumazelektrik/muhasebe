<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/helpers.php';

// Basit Router
$request = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$dir_name = dirname($script_name);

// Windows uyumluluğu: Ters slaşları düzelt
$dir_name = str_replace('\\', '/', $dir_name);

// Alt klasörde çalışıyorsak path'i temizle
// Eğer proje root'da değilse (örn: /proje), bu kısmı request'ten çıkar
if ($dir_name !== '/') {
    $request = str_replace($dir_name, '', $request);
}

$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

// Rota tanımları
switch ($path) {
    case 'login':
        require __DIR__ . '/views/login.php';
        break;

    case 'api/login':
        require __DIR__ . '/src/controllers/api/login.php';
        break;

    case 'logout':
        require __DIR__ . '/src/controllers/api/logout.php';
        break;

    case '':
    case 'index.php':
    case 'dashboard':
        require_login();
        require __DIR__ . '/src/controllers/dashboard.php';
        break;
        
    case 'users':
        require_admin();
        require __DIR__ . '/src/controllers/users.php';
        break;

    case 'inventory':
        require_login();
        require __DIR__ . '/modules/inventory/list.php';
        break;

    case 'jobs':
        require_login();
        require __DIR__ . '/modules/jobs/index.php';
        break;

    case 'job-detail':
        require_login();
        require __DIR__ . '/modules/jobs/job_detail.php';
        break;

    case 'profile':
        require_login();
        require __DIR__ . '/src/controllers/profile.php';
        break;

    case 'finance-reports':
        require_admin();
        require __DIR__ . '/modules/finance/reports.php';
        break;

    case 'inventory-check':
        require_login();
        require __DIR__ . '/modules/inventory/check_price.php';
        break;

    case 'api/search-stock':
        require_login();
        require __DIR__ . '/src/controllers/api/search_stock.php';
        break;

    case 'api/gemini-search':
        require_login();
        require __DIR__ . '/src/controllers/api/gemini_search.php';
        break;

    case 'reports':
        require_admin();
        require __DIR__ . '/src/controllers/reports.php';
        break;
        
    case 'payroll':
        require_admin();
        require __DIR__ . '/src/controllers/payroll.php';
        break;

    case 'settings':
        require_admin();
        require __DIR__ . '/src/controllers/settings.php';
        break;
        
    case 'user-transactions':
        require_login();
        require __DIR__ . '/src/controllers/user_transactions.php';
        break;

    case 'attendance-logs':
        require_login();
        require __DIR__ . '/src/controllers/attendance_logs.php';
        break;
        
    case 'api/clock-in':
        require_login();
        require __DIR__ . '/src/controllers/api/clock_in.php';
        break;

    case 'api/add-user':
        require_admin();
        require __DIR__ . '/src/controllers/api/add_user.php';
        break;
        
    case 'api/delete-log':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_log.php';
        break;

    case 'api/delete-user':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_user.php';
        break;

    case 'api/edit-user':
        require_admin();
        require __DIR__ . '/src/controllers/api/edit_user.php';
        break;

    case 'api/search-expense-categories':
        require_login();
        require __DIR__ . '/src/controllers/api/search_expense_categories.php';
        break;

    case 'api/save-expense-category':
        require_admin();
        require __DIR__ . '/src/controllers/api/save_expense_category.php';
        break;

    case 'api/delete-expense-category':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_expense_category.php';
        break;

    case 'api/update-shifts':
        require_admin();
        require __DIR__ . '/src/controllers/api/update_shifts.php';
        break;

    case 'api/update-gen-settings':
        require_admin();
        require __DIR__ . '/src/controllers/api/update_gen_settings.php';
        break;

    case 'api/add-shift':
        require_admin();
        require __DIR__ . '/src/controllers/api/add_shift.php';
        break;
    
    case 'api/edit-attendance':
        require_admin();
        require __DIR__ . '/src/controllers/api/edit_attendance.php';
        break;

    case 'api/get-user-payroll':
        require_login();
        require __DIR__ . '/src/controllers/api/get_user_payroll.php';
        break;

    case 'api/delete-shift':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_shift.php';
        break;

    case 'api/edit-shift':
        require_admin();
        require __DIR__ . '/src/controllers/api/edit_shift.php';
        break;

    case 'api/add-transaction':
        require_admin();
        require __DIR__ . '/src/controllers/api/add_transaction.php';
        break;

    case 'api/delete-transaction':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_transaction.php';
        break;

    case 'api/save-wallet':
        require_admin();
        require __DIR__ . '/src/controllers/api/save_wallet.php';
        break;

    case 'api/delete-wallet':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_wallet.php';
        break;

    case 'api/delete-entity':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_entity.php';
        break;

    case 'api/wallet-transfer':
        require_admin();
        require __DIR__ . '/src/controllers/api/wallet_transfer.php';
        break;

    case 'api/wallet-transaction':
        require_admin();
        require __DIR__ . '/src/controllers/api/wallet_transaction.php';
        break;

    case 'api/save-entity-transaction':
        require_admin();
        require __DIR__ . '/src/controllers/api/save_entity_transaction.php';
        break;

    case 'api/search-entities':
        require_login();
        require __DIR__ . '/src/controllers/api/search_entities.php';
        break;
        
    case 'api/edit-transaction':
        require_admin();
        require __DIR__ . '/src/controllers/api/edit_transaction.php';
        break;
        
    case 'api/export-transactions-csv':
        require_admin();
        require __DIR__ . '/src/controllers/api/export_transactions_csv.php';
        break;

    case 'api/add-stock':
        require_admin();
        require __DIR__ . '/src/controllers/api/add_stock.php';
        break;

    case 'api/edit-stock':
        require_admin();
        require __DIR__ . '/src/controllers/api/edit_stock.php';
        break;

    case 'api/delete-stock':
        require_admin();
        require __DIR__ . '/src/controllers/api/delete_stock.php';
        break;

    case 'api/update-job-finance':
        require_admin();
        require __DIR__ . '/src/controllers/api/update_job_finance.php';
        break;

    case 'api/add-job':
        require_login();
        require __DIR__ . '/src/controllers/api/add_job.php';
        break;

    case 'api/add-job-material':
        require_login();
        require __DIR__ . '/src/controllers/api/add_job_material.php';
        break;

    case 'api/update-job-status':
        require_login();
        require __DIR__ . '/src/controllers/api/update_job_status.php';
        break;

    case 'api/post-job-to-finance':
        require_login();
        require __DIR__ . '/src/controllers/api/post_job_to_finance.php';
        break;

    case 'api/delete-job':
        require_login();
        require __DIR__ . '/src/controllers/api/delete_job.php';
        break;

    case 'api/edit-job':
        require_login();
        require __DIR__ . '/src/controllers/api/edit_job.php';
        break;

    case 'api/delete-job-material':
        require_login();
        require __DIR__ . '/src/controllers/api/delete_job_material.php';
        break;

    case 'api/edit-job-material':
        require_login();
        require __DIR__ . '/src/controllers/api/edit_job_material.php';
        break;

    case 'api/analyze-job-material-image':
        require_login();
        require __DIR__ . '/src/controllers/api/analyze_job_material_image.php';
        break;

    // --- FAZ 2-5: Akıllı Stok Yönetimi Rotaları ---
    
    case 'invoice/upload':
        require_login();
        require __DIR__ . '/views/invoice/upload.php';
        break;

    case 'invoice/analyze':
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['invoice_image'])) {
            require_once __DIR__ . '/src/Services/GeminiService.php';
            require_once __DIR__ . '/src/Models/EntityModel.php';
            
            $apiKey = $_ENV['GEMINI_API_KEY'] ?? ''; 
            $service = new GeminiService($apiKey);
            
            $tmpPath = $_FILES['invoice_image']['tmp_name'];
            try {
                $invoiceData = $service->processInvoice($tmpPath);

                if ($invoiceData) {
                    // Enrich data with DB matches
                    require_once __DIR__ . '/src/Models/ProductModel.php';
                    $productModel = new ProductModel($pdo);
                    $entityModel = new EntityModel($pdo);

                    foreach ($invoiceData['items'] as &$item) {
                        if ($item['type'] === 'STOK') {
                            $match = $productModel->findBestMatch($item['name']);
                            if ($match) {
                                $item['mapped_id'] = $match['id'];
                                $item['mapped_name'] = $match['name'];
                                $item['current_stock-unit'] = $match['unit'];
                                $item['current_avg_cost'] = $match['avg_cost'];
                            }
                        }
                    }
                    unset($item); // Break reference

                    // --- FAZ 3.1: Cari Bilgi Paneli ---
                    // Get or create entity to show current balance
                    $entity = $entityModel->findOrCreate(
                        $invoiceData['supplier_name'],
                        $invoiceData['supplier_tax_id'] ?? null,
                        'supplier'
                    );
                    $invoiceData['entity_id'] = $entity['id'];
                    $invoiceData['current_balance'] = $entity['balance'];
                    
                    // Calculate projected balance (if unpaid)
                    $invoiceData['projected_balance'] = $entity['balance'] - floatval($invoiceData['total_amount']);

                    // View expects $invoiceData, $suppliers, $units, $wallets
                    require_once __DIR__ . '/src/Models/WalletModel.php';
                    $walletModel = new WalletModel($pdo);
                    $wallets = $walletModel->getAllActive();
                    
                    $suppliers = $entityModel->getAll(); // Get all entities (suppliers + staff)
                    $units = ['Adet', 'Metre', 'Kg', 'Litre', 'Paket', 'Koli', 'M']; // Common units
                    
                    require __DIR__ . '/views/invoice/validation.php';
                } else {
                     throw new Exception("Boş veri döndü.");
                }
            } catch (Exception $e) {
                redirect_with_message(public_url('invoice/upload'), 'error', 'Hata: ' . $e->getMessage());
            }
        } else {
            redirect('invoice/upload');
        }
        break;

    case 'invoice/store':
        require_login();
        require_once __DIR__ . '/src/Controllers/InvoiceController.php';
        $controller = new InvoiceController($pdo); // $pdo config/db.php'den gelir
        $controller->store();
        break;

    case 'inventory/detail':
        require_login();
        $id = $_GET['id'] ?? 0;
        require_once __DIR__ . '/src/Controllers/InventoryController.php';
        $controller = new InventoryController($pdo);
        $controller->detail($id);
        break;

    case 'inventory/mapping':
        require_login();
        require_once __DIR__ . '/src/Controllers/InventoryController.php';
        $controller = new InventoryController($pdo);
        $controller->mappingList();
        break;

    // --- FAZ 5: Cari Yönetimi Rotaları ---
    
    case 'finance/expense-categories':
        require_admin();
        require __DIR__ . '/modules/finance/expense_categories.php';
        break;

    case 'finance/wallets':
        require_admin();
        require __DIR__ . '/modules/finance/wallets.php';
        break;

    case 'entities':
    case 'entity/list':
        require_login();
        require __DIR__ . '/views/entities/list.php';
        break;

    case 'entity/statement':
        require_login();
        require __DIR__ . '/views/entities/statement.php';
        break;

    case 'entity/add':
        require_admin();
        require __DIR__ . '/views/entities/add.php';
        break;

    case 'entity/save':
        require_admin();
        require __DIR__ . '/src/controllers/api/save_entity.php';
        break;

    case 'api/transaction-detail':
        require_login();
        require __DIR__ . '/src/controllers/api/get_transaction_detail.php';
        break;

    case 'api/edit-entity-transaction':
        require_login();
        require __DIR__ . '/src/controllers/api/edit_entity_transaction.php';
        break;

    case 'api/delete-entity-transaction':
        require_login();
        require __DIR__ . '/src/controllers/api/delete_entity_transaction.php';
        break;

    case 'entity/edit':
        require_admin();
        require __DIR__ . '/views/entities/edit.php';
        break;
        
    default:
        http_response_code(404);
        view('404');
        break;
}
?>
