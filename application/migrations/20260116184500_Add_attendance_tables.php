<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_attendance_tables extends CI_Migration {

    public function up() {
        // Attendance Table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11
            ],
            'shift_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ],
            'date' => [
                'type' => 'DATE'
            ],
            'clock_in' => [
                'type' => 'TIME',
                'null' => TRUE
            ],
            'clock_out' => [
                'type' => 'TIME',
                'null' => TRUE
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'absent'
            ],
            'overtime_hours' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
                 'default' => NULL // CI3 sometimes has issues with CURRENT_TIMESTAMP in add_field on some drivers, usually standard SQL works but keeping simple
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('date');
        $this->dbforge->create_table('attendance', TRUE);

        // Shifts Table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'start_time' => [
                'type' => 'TIME'
            ],
            'end_time' => [
                'type' => 'TIME'
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('shifts', TRUE);

        // Insert Default Shift
        $data = [
            'name' => 'Normal Vardiya',
            'start_time' => '08:00:00',
            'end_time' => '18:00:00'
        ];
        $this->db->insert('shifts', $data);
        
        // Ensure Users has salary related columns if missing
        if (!$this->db->field_exists('hourly_rate', 'users')) {
            $this->dbforge->add_column('users', [
                'hourly_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00
                ]
            ]);
        }
        if (!$this->db->field_exists('salary_day', 'users')) {
            $this->dbforge->add_column('users', [
                'salary_day' => [
                    'type' => 'TINYINT',
                    'constraint' => 2,
                    'default' => 1
                ]
            ]);
        }
        if (!$this->db->field_exists('annual_leave_days', 'users')) {
             $this->dbforge->add_column('users', [
                'annual_leave_days' => [
                    'type' => 'TINYINT',
                    'constraint' => 3,
                    'default' => 14
                ]
            ]);
        }
    }

    public function down() {
        $this->dbforge->drop_table('attendance', TRUE);
        $this->dbforge->drop_table('shifts', TRUE);
    }
}
