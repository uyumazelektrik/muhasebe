<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll_model extends CI_Model {

    public function get_payroll_data($month, $year) {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where_in('role', ['personel', 'admin', 'editor']);
        $this->db->order_by('full_name', 'ASC');
        $users = $this->db->get()->result_array();

        // Multipliers (could be moved to settings later)
        $multipliers = [
            'overtime' => 1.5,
            'holiday' => 2.0,
            'late_penalty' => 2.0
        ];

        // Default shift if not specified
        $defaultShift = ['start_time' => '08:00:00', 'end_time' => '18:00:00'];

        $payrollData = [];

        foreach ($users as $user) {
            $salaryDay = $user['salary_day'] ?? 1;
            
            // Calculate Date Range
            if ($salaryDay == 1) {
                $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
                $endDate = date("Y-m-t", strtotime($startDate));
            } else {
                $prevMonth = $month - 1;
                $prevYear = $year;
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                
                $prevSalaryDate = "$prevYear-" . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
                $startDate = date("Y-m-d", strtotime($prevSalaryDate . " +1 day"));
                $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
            }

            // Fetch attendance logs
            $this->db->select('a.*, s.start_time as shift_start, s.end_time as shift_end');
            $this->db->from('attendance a');
            $this->db->join('shifts s', 'a.shift_id = s.id', 'left');
            $this->db->where('a.user_id', $user['id']);
            $this->db->where('a.date >=', $startDate);
            $this->db->where('a.date <=', $endDate);
            $logs = $this->db->get()->result_array();

            $totalNormalHours = 0;
            $totalOvertimeHours = 0;
            $totalHolidayHours = 0;
            $totalPenaltyHours = 0;
            $totalWeightedPenaltyHours = 0;
            $daysWorked = 0;
            $daysLeave = 0;

            $logsByDate = [];
            foreach ($logs as $l) {
                $logsByDate[$l['date']] = $l;
            }

            $currentDateTs = strtotime($startDate);
            $endDateTs = strtotime($endDate);

            while ($currentDateTs <= $endDateTs) {
                $dateStr = date('Y-m-d', $currentDateTs);
                $log = $logsByDate[$dateStr] ?? null;

                // Sunday weekly leave check
                if (!$log && date('N', $currentDateTs) == 7) {
                    $log = [
                        'date' => $dateStr,
                        'status' => 'weekly_leave',
                        'clock_in' => '-',
                        'clock_out' => '-',
                        'shift_start' => $defaultShift['start_time'],
                        'shift_end' => $defaultShift['end_time'],
                        'is_late' => 0,
                        'overtime_hours' => 0
                    ];
                }

                if (!$log) {
                    $currentDateTs = strtotime('+1 day', $currentDateTs);
                    continue;
                }

                $status = $log['status'];
                
                // Shift Duration
                $sStartStr = ($log['shift_start'] ?? '08:00:00');
                $sEndStr = ($log['shift_end'] ?? '18:00:00');
                $s1 = strtotime($dateStr . ' ' . $sStartStr);
                $s2 = strtotime($dateStr . ' ' . $sEndStr);
                if ($s2 < $s1) $s2 += 24*3600;
                $shiftHours = ($s2 - $s1) / 3600;

                // 1. Normal Gain
                if (in_array($status, ['present', 'holiday', 'excused_late', 'paid_leave', 'annual_leave', 'weekly_leave'])) {
                    $totalNormalHours += $shiftHours;
                    if (in_array($status, ['present', 'holiday', 'excused_late', 'annual_leave', 'paid_leave'])) {
                        $daysWorked++;
                    } else if ($status == 'weekly_leave') {
                        $daysLeave++;
                    }
                }

                // 2. Overtime
                if (in_array($status, ['present', 'holiday', 'excused_late', 'weekly_leave'])) {
                    $totalOvertimeHours += $log['overtime_hours'];
                }

                // 3. Late Penalty
                if (in_array($status, ['present', 'holiday', 'excused_late'])) {
                     if ($log['clock_in'] !== '-' && !empty($log['clock_in'])) {
                         $userInTs = strtotime($log['date'] . ' ' . $log['clock_in']);
                         if ($userInTs > ($s1 + 900)) {
                             $diff = $userInTs - $s1;
                             $pHours = $diff / 3600;
                             $totalPenaltyHours += $pHours; 
                             
                             $multiplier = ($status == 'excused_late') ? 1.0 : $multipliers['late_penalty'];
                             $totalWeightedPenaltyHours += ($pHours * $multiplier);
                         }
                     }
                }

                // 4. Holiday Difference
                if ($status == 'holiday') {
                    $totalHolidayHours += $shiftHours * ($multipliers['holiday'] - 1);
                }

                $currentDateTs = strtotime('+1 day', $currentDateTs);
            }

            // Calculations
            $hourlyRate = floatval($user['hourly_rate']);
            $normalPay = $totalNormalHours * $hourlyRate;
            $overtimePay = $totalOvertimeHours * $hourlyRate * $multipliers['overtime'];
            $holidayPay = $totalHolidayHours * $hourlyRate; 
            $penaltyDeduction = $totalWeightedPenaltyHours * $hourlyRate;
            
            $grossSalary = $normalPay + $overtimePay + $holidayPay;
            $netSalary = $grossSalary - $penaltyDeduction;

            $payrollData[] = [
                'user' => $user,
                'stats' => [
                    'normal_hours' => $totalNormalHours,
                    'overtime_hours' => $totalOvertimeHours,
                    'holiday_hours' => $totalHolidayHours,
                    'penalty_hours' => $totalPenaltyHours,
                    'days_worked' => $daysWorked,
                    'days_leave' => $daysLeave
                ],
                'financials' => [
                    'normal_pay' => $normalPay,
                    'overtime_pay' => $overtimePay,
                    'holiday_pay' => $holidayPay,
                    'penalty_deduction' => $penaltyDeduction,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary
                ]
            ];
        }

        return $payrollData;
    }

    public function get_user_payroll_details($userId, $month, $year) {
        $user = $this->db->where('id', $userId)->get('users')->row_array();
        if (!$user) return null;

        $multipliers = ['overtime' => 1.5, 'holiday' => 2.0, 'late_penalty' => 2.0];
        $defaultShift = ['start_time' => '08:00:00', 'end_time' => '18:00:00'];
        $salaryDay = $user['salary_day'] ?? 1;

        // Date range calculation
        if ($salaryDay == 1) {
            $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate = date("Y-m-t", strtotime($startDate));
        } else {
            $prevMonth = $month - 1; $prevYear = $year;
            if ($prevMonth == 0) { $prevMonth = 12; $prevYear--; }
            $prevSalaryDate = "$prevYear-" . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
            $startDate = date("Y-m-d", strtotime($prevSalaryDate . " +1 day"));
            $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
        }

        $this->db->select('a.*, s.start_time as shift_start, s.end_time as shift_end, s.name as shift_name');
        $this->db->from('attendance a');
        $this->db->join('shifts s', 'a.shift_id = s.id', 'left');
        $this->db->where('a.user_id', $userId);
        $this->db->where('a.date >=', $startDate);
        $this->db->where('a.date <=', $endDate);
        $this->db->order_by('a.date', 'ASC');
        $logs = $this->db->get()->result_array();

        $dailyDetails = [];
        $hourlyRate = floatval($user['hourly_rate']);
        $logsByDate = [];
        foreach ($logs as $l) { $logsByDate[$l['date']] = $l; }

        $currentDateTs = strtotime($startDate);
        $endDateTs = strtotime($endDate);

        while ($currentDateTs <= $endDateTs) {
            $dateStr = date('Y-m-d', $currentDateTs);
            $log = $logsByDate[$dateStr] ?? null;
            if (!$log && date('N', $currentDateTs) == 7) {
                $log = ['date' => $dateStr, 'status' => 'weekly_leave', 'clock_in' => '-', 'clock_out' => '-', 'shift_start' => $defaultShift['start_time'], 'shift_end' => $defaultShift['end_time'], 'is_late' => 0, 'overtime_hours' => 0, 'note' => ''];
            }
            if (!$log) { $currentDateTs = strtotime('+1 day', $currentDateTs); continue; }

            $status = $log['status'];
            $normalHours = 0; $overtimeHours = $log['overtime_hours'] ?? 0; $penaltyHours = 0; $holidayHours = 0;
            $overtimePay = 0; $penaltyDeduction = 0; $holidayPay = 0;

            $s1 = strtotime($dateStr . ' ' . ($log['shift_start'] ?? '09:00:00'));
            $s2 = strtotime($dateStr . ' ' . ($log['shift_end'] ?? '17:00:00'));
            if ($s2 < $s1) $s2 += 24*3600;
            $shiftHours = ($s2 - $s1) / 3600;

            if (in_array($status, ['present', 'holiday', 'excused_late', 'paid_leave', 'annual_leave', 'weekly_leave', 'sick_leave'])) {
                $normalHours = $shiftHours;
            }
            $normalPay = $normalHours * $hourlyRate;

            if (in_array($status, ['present', 'holiday', 'excused_late', 'weekly_leave'])) {
                $overtimePay = $overtimeHours * $hourlyRate * $multipliers['overtime'];
            }

            if (in_array($status, ['present', 'holiday', 'excused_late'])) {
                 if ($log['clock_in'] !== '-' && !empty($log['clock_in'])) {
                     $userInTs = strtotime($log['date'] . ' ' . $log['clock_in']);
                     if ($userInTs > ($s1 + 900)) {
                         $penaltyHours = ($userInTs - $s1) / 3600;
                         $multiplier = ($status == 'excused_late') ? 1.0 : $multipliers['late_penalty'];
                         $penaltyDeduction = $penaltyHours * $hourlyRate * $multiplier;
                     }
                 }
            }

            if ($status == 'holiday') {
                $holidayHours = $shiftHours;
                $holidayPay = $holidayHours * $hourlyRate * ($multipliers['holiday'] - 1);
            }

            $currentDateTs = strtotime($dateStr);
            $daysMap = ['Monday'=>'Pazartesi','Tuesday'=>'Salı','Wednesday'=>'Çarşamba','Thursday'=>'Perşembe','Friday'=>'Cuma','Saturday'=>'Cumartesi','Sunday'=>'Pazar'];
            $dayNameTr = $daysMap[date('l', $currentDateTs)] ?? date('l', $currentDateTs);

            $dailyDetails[] = [
                'date' => $dateStr, 'day_name' => $dayNameTr, 'status' => $status, 'note' => $log['note'] ?? '',
                'clock_in' => $log['clock_in'] ?? '-', 'clock_out' => $log['clock_out'] ?? '-',
                'shift_name' => $log['shift_name'] ?? '',
                'hours' => ['normal' => $normalHours, 'overtime' => $overtimeHours, 'holiday' => $holidayHours, 'penalty' => $penaltyHours],
                'financials' => [
                    'normal_pay' => number_format($normalPay, 2),
                    'overtime_pay' => number_format($overtimePay, 2),
                    'holiday_pay' => number_format($holidayPay, 2),
                    'penalty_deduction' => number_format($penaltyDeduction, 2),
                    'total' => number_format($normalPay + $overtimePay + $holidayPay - $penaltyDeduction, 2)
                ]
            ];
            $currentDateTs = strtotime('+1 day', $currentDateTs);
        }

        return ['user' => $user, 'details' => $dailyDetails];
    }
}
