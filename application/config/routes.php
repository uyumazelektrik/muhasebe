<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes with
| underscores in the controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Auth';
$route['dashboard'] = 'Dashboard/index';
$route['translate_uri_dashes'] = FALSE;

$route['sales/pos'] = 'Sales/pos';
$route['api/save-sale'] = 'Sales/api_save_sale';

$route['personnel'] = 'Personnel/index';

$route['entities'] = 'Customers/index';
$route['customers/detail/(:num)'] = 'Customers/detail/$1';
$route['customers/api_update'] = 'Customers/api_update';
$route['customers/api_create'] = 'Customers/api_create';
$route['customers/api_delete'] = 'Customers/api_delete';
$route['customers/api_transfer'] = 'Customers/api_transfer';
$route['customers/api_add_transaction'] = 'Customers/api_add_transaction';
$route['users'] = 'Users';
$route['api/add-user'] = 'Users/api_add';
$route['api/edit-user'] = 'Users/api_edit';
$route['api/delete-user'] = 'Users/api_delete';
$route['inventory'] = 'Inventory/index';
$route['inventory/detail/(:num)'] = 'Inventory/detail/$1';
$route['inventory-check'] = 'Inventory_check/index';
$route['inventory_check/api_search_stock'] = 'Inventory_check/api_search_stock';
$route['inventory_check/api_gemini_search'] = 'Inventory_check/api_gemini_search';

$route['finance/wallets'] = 'Finance/wallets';
$route['finance/wallet-detail/(:num)'] = 'Finance/wallet_detail/$1';
$route['finance/api_add_wallet'] = 'Finance/api_add_wallet';
$route['finance/api_edit_wallet'] = 'Finance/api_edit_wallet';
$route['finance/api_delete_wallet'] = 'Finance/api_delete_wallet';
$route['finance/api_add_wallet_transaction'] = 'Finance/api_add_wallet_transaction';
$route['finance/api_edit_wallet_transaction'] = 'Finance/api_edit_wallet_transaction';
$route['finance/api_wallet_transfer'] = 'Finance/api_wallet_transfer';
$route['finance/api_external_transaction'] = 'Finance/api_external_transaction';
$route['finance/expense-categories'] = 'Finance/expense_categories';
$route['finance/api_add_expense_category'] = 'Finance/api_add_expense_category';
$route['finance/api_edit_expense_category'] = 'Finance/api_edit_expense_category';
$route['finance/api_delete_expense_category'] = 'Finance/api_delete_expense_category';
$route['attendance-logs'] = 'Attendance/logs';
$route['api/delete-attendance'] = 'Attendance/api_delete_attendance';
$route['api/save-attendance'] = 'Attendance/api_save_attendance';
$route['api/get-attendance-logs-html'] = 'Attendance/api_get_logs_html';
$route['api/edit-attendance'] = 'Attendance/api_update_attendance';
$route['api/get-attendance-logs'] = 'Personnel/api_get_logs';
$route['api/delete-transaction'] = 'Sales/api_delete_transaction';
$route['api/delete_transaction'] = 'Api/delete_transaction';

$route['finance/user-transactions'] = 'Finance/user_transactions';
$route['api/add-staff-transaction'] = 'Finance/api_add_transaction';
$route['api/edit-staff-transaction'] = 'Finance/api_edit_transaction';
$route['api/delete-staff-transaction'] = 'Finance/api_delete_transaction';

$route['payroll'] = 'Payroll';
$route['payroll/my-payroll'] = 'Payroll/my_payroll';
$route['api/get-user-payroll'] = 'Payroll/api_get_user_payroll';

$route['invoices'] = 'Invoices/index';
$route['invoices/create'] = 'Invoices/create';
$route['invoices/detail/(:num)'] = 'Invoices/detail/$1';
$route['invoice/upload'] = 'Invoices/upload';
$route['invoice/api-analyze'] = 'Invoices/api_analyze_invoice';
$route['api/get-invoices'] = 'Api/get_invoices';
$route['api/save-invoice'] = 'Invoices/api_save_invoice';
$route['api/delete-invoice'] = 'Invoices/api_delete_invoice';
$route['api/update-invoice-item'] = 'Invoices/api_update_item';
$route['api/delete-invoice-item'] = 'Invoices/api_delete_item';

$route['api/create-entity'] = 'Customers/api_create';

$route['login'] = 'Auth/index';
$route['logout'] = 'Auth/logout';
$route['auth/login'] = 'Auth/login';

$route['settings'] = 'Settings/index';
$route['migrate'] = 'Migrate/index';
$route['finance-reports'] = 'FinanceReports/index';
$route['reports'] = 'Reports/index';
