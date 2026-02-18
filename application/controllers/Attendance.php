<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model', 'attendance');
    }

    public function logs() {
        $page = $this->input->get('page') ? max(1, intval($this->input->get('page'))) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date')
        ];

        if (current_role() === 'personel') {
            $filters['user_id'] = $_SESSION['user_id'];
        }

        $data['logs'] = $this->attendance->get_logs($filters, $limit, $offset);
        $data['totalLogs'] = $this->attendance->count_logs($filters);
        $data['totalPages'] = ceil($data['totalLogs'] / $limit);
        $data['page'] = $page;
        $data['startDate'] = $filters['start_date'];
        $data['endDate'] = $filters['end_date'];

        $data['all_users'] = [];
        if (current_role() === 'admin') {
            $data['all_users'] = $this->attendance->get_all_users();
        }

        $data['page_title'] = "Giriş-Çıkış Kayıtları";

        $this->load->view('layout/header', $data);
        $this->load->view('attendance_logs', $data);
        $this->load->view('layout/footer');
    }
    public function api_delete_attendance() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? 0;

        if ($id) {
            $this->db->where('id', $id);
            if ($this->db->delete('attendance')) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme işlemi başarısız.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID.']);
        }
    }

    public function api_save_attendance() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!empty($data['user_id']) && !empty($data['date'])) {
            $shift_id = $data['shift_id'] ?? 1;
            $clock_in = $data['clock_in'] ?? '09:00';
            $clock_out = $data['clock_out'] ?? '18:00';
            $status = $data['status'] ?? 'present';
            
            // Get shift info
            $shift = $this->db->where('id', $shift_id)->get('shifts')->row_array();
            if (!$shift) {
                // Get default shift (first one)
                $shift = $this->db->order_by('id', 'ASC')->limit(1)->get('shifts')->row_array();
            }
            if (!$shift) {
                $shift = ['start_time' => '09:00:00', 'end_time' => '18:00:00'];
            }
            
            // Calculate is_late (15 minutes tolerance)
            $todayStr = date('Y-m-d');
            $shiftStart = strtotime("$todayStr " . $shift['start_time']);
            $shiftEnd = strtotime("$todayStr " . $shift['end_time']);
            if ($shiftEnd < $shiftStart) $shiftEnd += 24 * 3600;
            
            $userClockIn = strtotime("$todayStr $clock_in");
            $userClockOut = strtotime("$todayStr $clock_out");
            if ($userClockOut < $userClockIn) $userClockOut += 24 * 3600;
            
            // Mark as late if clock_in is after shift start
            $is_late = ($status == 'present' && $userClockIn > $shiftStart) ? 1 : 0;
            
            // Calculate overtime
            $overtime = 0;
            if ($status == 'weekly_leave') {
                $overtime = ($userClockOut - $userClockIn) / 3600;
            } elseif ($userClockOut > $shiftEnd) {
                $overtime = ($userClockOut - max($userClockIn, $shiftEnd)) / 3600;
            }
            
            $insertData = [
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'status' => $status,
                'note' => $data['note'] ?? '',
                'clock_in' => $clock_in,
                'clock_out' => $clock_out,
                'shift_id' => $shift_id,
                'is_late' => $is_late,
                'overtime_hours' => $overtime
            ];
            
            if ($this->db->insert('attendance', $insertData)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kaydetme işlemi başarısız.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Eksik veriler var.']);
        }
    }
    public function api_update_attendance() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? 0;

        if ($id && !empty($data['date'])) {
            $shift_id = $data['shift_id'] ?? 1;
            $clock_in = $data['clock_in'] ?? '09:00';
            $clock_out = $data['clock_out'] ?? '17:00';
            $status = $data['status'] ?? 'present';
            
            // Get shift info
            $shift = $this->db->where('id', $shift_id)->get('shifts')->row_array();
            if (!$shift) $shift = ['start_time' => '09:00:00', 'end_time' => '17:00:00'];

            $todayStr = date('Y-m-d');
            $sStart = strtotime("$todayStr " . $shift['start_time']);
            $sEnd = strtotime("$todayStr " . $shift['end_time']);
            if ($sEnd < $sStart) $sEnd += 24*3600;

            $uIn = strtotime("$todayStr $clock_in");
            $uOut = strtotime("$todayStr $clock_out");
            if ($uOut < $uIn) $uOut += 24*3600;

            $is_late = ($status == 'present' && $uIn > $sStart) ? 1 : 0;
            
            $overtime = 0;
            if ($status == 'weekly_leave') {
                $overtime = ($uOut - $uIn) / 3600;
            } elseif ($uOut > $sEnd) {
                $overtime = ($uOut - max($uIn, $sEnd)) / 3600;
            }

            $updateData = [
                'date' => $data['date'],
                'shift_id' => $shift_id,
                'clock_in' => $clock_in,
                'clock_out' => $clock_out,
                'status' => $status,
                'is_late' => $is_late,
                'overtime_hours' => $overtime,
                'note' => $data['note'] ?? ''
            ];

            $this->db->where('id', $id);
            if ($this->db->update('attendance', $updateData)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Güncelleme başarısız.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Eksik veriler.']);
        }
    }

    public function api_get_logs_html() {
        $page = $this->input->get('page') ? max(1, intval($this->input->get('page'))) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date')
        ];

        if (current_role() === 'personel') {
            $filters['user_id'] = $_SESSION['user_id'];
        }

        $data['logs'] = $this->attendance->get_logs($filters, $limit, $offset);
        $this->load->view('attendance/_logs_full_list', $data);
    }
}
