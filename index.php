<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/helpers.php';

// Basit Router
$request = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$dir_name = dirname($script_name);

// Alt klasörde çalışıyorsak path'i temizle
$path = str_replace($dir_name, '', $request);
$path = parse_url($path, PHP_URL_PATH);
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
        
    default:
        http_response_code(404);
        view('404');
        break;
}
?>
