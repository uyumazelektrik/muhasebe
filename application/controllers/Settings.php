<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Settings_model', 'settings_model');
        
        // Only admin can access settings
        if (current_role() !== 'admin') {
            redirect('dashboard');
        }
    }

    public function index() {
        $data['page_title'] = 'Sistem Ayarları';
        $data['settings'] = $this->settings_model->get_all_settings();
        $data['shifts'] = $this->settings_model->get_all_shifts();
        
        $this->load->view('layout/header', $data);
        $this->load->view('settings/index', $data);
        $this->load->view('layout/footer');
    }

    // ==================== SETTINGS API ====================
    
    public function api_save_setting() {
        header('Content-Type: application/json');
        
        $key = $this->input->post('key');
        $value = $this->input->post('value');
        
        if (empty($key)) {
            echo json_encode(['status' => 'error', 'message' => 'Ayar anahtarı gerekli']);
            return;
        }
        
        if ($this->settings_model->update_setting($key, $value)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kaydetme başarısız']);
        }
    }
    
    public function api_delete_setting() {
        header('Content-Type: application/json');
        
        $key = $this->input->post('key');
        
        if ($this->settings_model->delete_setting($key)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Silme başarısız']);
        }
    }

    // ==================== SHIFTS API ====================
    
    public function api_get_shifts() {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $this->settings_model->get_all_shifts()
        ]);
    }
    
    public function api_save_shift() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        $data = [
            'name' => $this->input->post('name'),
            'start_time' => $this->input->post('start_time'),
            'end_time' => $this->input->post('end_time')
        ];
        
        if (empty($data['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vardiya adı gerekli']);
            return;
        }
        
        if ($id) {
            // Update
            if ($this->settings_model->update_shift($id, $data)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Güncelleme başarısız']);
            }
        } else {
            // Create
            $new_id = $this->settings_model->create_shift($data);
            if ($new_id) {
                echo json_encode(['status' => 'success', 'id' => $new_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Oluşturma başarısız']);
            }
        }
    }
    
    public function api_delete_shift() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        
        $result = $this->settings_model->delete_shift($id);
        
        if ($result === false) {
            echo json_encode(['status' => 'error', 'message' => 'Bu vardiya kullanımda olduğu için silinemez']);
        } elseif ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Silme başarısız']);
        }
    }

    public function api_get_shifts_html() {
        $data['shifts'] = $this->settings_model->get_all_shifts();
        $this->load->view('settings/_shifts_list', $data);
    }

    public function api_get_settings_html() {
        $data['settings'] = $this->settings_model->get_all_settings();
        $this->load->view('settings/_settings_list', $data);
    }

    // ==================== DATABASE OPERATIONS API ====================

    public function api_db_check_connection() {
        header('Content-Type: application/json');
        
        try {
            // Force reconnect to check functionality
            $this->db->reconnect();
            
            // Simple query
            $query = $this->db->query("SELECT 1");
            
            if ($query && $query->num_rows() > 0) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Veritabanı bağlantısı başarılı. (Host: ' . $this->db->hostname . ')'
                ]);
            } else {
                throw new Exception("Sorgu yanıt vermedi.");
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ]);
        }
    }

    public function api_db_check_health() {
        header('Content-Type: application/json');
        
        $this->load->dbutil();
        
        try {
            // Get all tables
            $tables = $this->db->list_tables();
            $report = [];
            $has_error = false;
            
            foreach ($tables as $table) {
                if ($this->dbutil->optimize_table($table)) {
                    // Optimized or OK
                } else {
                    $report[] = "$table: Optimizasyon başarısız";
                    $has_error = true;
                }
            }
            
            $db_info = "Veritabanı: " . $this->db->database . "\n";
            $db_info .= "Platform: " . $this->db->platform() . "\n";
            $db_info .= "Version: " . $this->db->version() . "\n";
            $db_info .= "Tablo Sayısı: " . count($tables);
            
            if (!$has_error) {
                echo json_encode(['status' => 'success', 'message' => "Veritabanı sağlıklı.\n" . $db_info]);
            } else {
                echo json_encode(['status' => 'warning', 'message' => "Bazı tablolarda sorun olabilir.\n" . implode("\n", $report)]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Sağlık kontrolü hatası: ' . $e->getMessage()]);
        }
    }

    public function api_get_tables() {
        header('Content-Type: application/json');
        $tables = $this->db->list_tables();
        echo json_encode(['status' => 'success', 'tables' => $tables]);
    }

    public function api_db_backup() {
        // This is a direct download, not JSON response usually
        $this->load->dbutil();
        $this->load->helper('download');

        $tables_param = $this->input->get('tables');
        
        $prefs = array(
            'format'      => 'txt',             // gzip, zip, txt
            'filename'    => 'backup.sql',      // File name - NEEDED ONLY WITH ZIP FILES
            'add_drop'    => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
            'newline'     => "\n"               // Newline character used in backup file
        );

        $filename_prefix = 'full_backup_';

        // If specific tables are requested
        if (!empty($tables_param)) {
            $selected_tables = explode(',', $tables_param);
            // Verify tables exist to prevent errors
            $all_tables = $this->db->list_tables();
            $valid_tables = array_intersect($selected_tables, $all_tables);
            
            if (!empty($valid_tables)) {
                $prefs['tables'] = $valid_tables;
                $filename_prefix = 'partial_backup_';
            }
        }

        $backup = $this->dbutil->backup($prefs);
        $db_name = $filename_prefix . date("Y-m-d_H-i-s") . '.sql';

        force_download($db_name, $backup);
    }

    public function api_db_restore() {
        // Prevent any unexpected output from breaking JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');

        if (!isset($_FILES['backup_file']['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dosya yüklenmedi.']);
            return;
        }

        // Increase limits for large backups
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '512M');

        $sql_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        
        if (trim($sql_content) == '') {
            echo json_encode(['status' => 'error', 'message' => 'Yüklenen dosya boş.']);
            return;
        }

        // Use the underlying mysqli connection
        $mysqli = $this->db->conn_id;

        if (!$mysqli || !($mysqli instanceof mysqli)) {
             echo json_encode(['status' => 'error', 'message' => 'Veritabanı sürücüsü uyumsuz (MySQLi gerekli).']);
             return;
        }

        // Try to increase max_allowed_packet for this session
        $mysqli->query("SET SESSION max_allowed_packet = 104857600"); // 100MB
        $mysqli->query("SET SESSION wait_timeout = 600");
        $mysqli->query("SET SESSION interactive_timeout = 600");

        // Disable foreign key checks
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Split SQL content into individual queries
        // Use a more robust way to split SQL while ignoring comments
        $queries = explode(";\n", $sql_content);
        if (count($queries) <= 1) {
            $queries = explode(";\r\n", $sql_content);
        }
        
        $success_count = 0;
        $error_count = 0;
        $last_error = '';

        foreach ($queries as $query) {
            $query = trim($query);
            if (empty($query) || strpos($query, '--') === 0 || strpos($query, '/*') === 0) continue;

            if ($mysqli->query($query)) {
                $success_count++;
            } else {
                // If the connection is lost during a query
                if ($mysqli->errno == 2006 || $mysqli->errno == 2013) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'MySQL bağlantısı koptu (Server gone away).'
                    ]);
                    return;
                }
                $error_count++;
                $last_error = $mysqli->error . " (Query: " . substr($query, 0, 100) . "...)";
            }
        }
        
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
        
        if ($success_count == 0 && $error_count == 0) {
             echo json_encode(['status' => 'error', 'message' => 'Dosya içerisinde geçerli SQL komutu bulunamadı. Lütfen SQL dosyasını kontrol edin.']);
        } elseif ($error_count > 0 && $success_count == 0) {
            echo json_encode(['status' => 'error', 'message' => 'SQL Çalıştırma Hatası: ' . $last_error]);
        } else {
            $status = ($error_count > 0) ? 'warning' : 'success';
            $msg = "İşlem tamamlandı. Başarılı: $success_count";
            if ($error_count > 0) {
                $msg .= ", Hata: $error_count (Son hata: $last_error)";
            }
            echo json_encode(['status' => $status, 'message' => $msg]);
        }
    }

    public function api_db_clear() {
        header('Content-Type: application/json');
        
        // Security check: Only admin
        if (current_role() !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem.']);
            return;
        }

        $tables = $this->db->list_tables();
        $keep_tables = ['users', 'settings', 'migrations']; // Tables to KEEP
        
        $cleared = [];
        
        $this->db->trans_start();
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            if (!in_array($table, $keep_tables)) {
                if ($this->db->truncate($table)) {
                    $cleared[] = $table;
                }
            }
        }
        
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
             echo json_encode(['status' => 'error', 'message' => 'Temizleme sırasında hata oluştu.']);
        } else {
             echo json_encode([
                 'status' => 'success', 
                 'message' => 'Veritabanı temizlendi. (Kullanıcılar ve ayarlar korundu)',
                 'cleared_tables' => $cleared
             ]);
        }

    }
}
