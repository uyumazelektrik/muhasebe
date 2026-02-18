<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->dbforge();
    }

    /**
     * Migration: Add due_date column to inv_invoices
     * URL: /muhasebe/migrate/add_due_date
     */
    public function add_due_date() {
        // Security: Only allow in development or with specific key
        $allowed_key = 'migration2026'; // Change this to a secure key
        $provided_key = $this->input->get('key');
        
        if (ENVIRONMENT === 'production' && $provided_key !== $allowed_key) {
            show_error('Unauthorized access. Please provide valid migration key.', 403);
            return;
        }

        $output = [];
        $output[] = "=== Migration: Add due_date Column ===";
        $output[] = "Started at: " . date('Y-m-d H:i:s');
        $output[] = "";

        try {
            // Check if column already exists
            if ($this->db->field_exists('due_date', 'inv_invoices')) {
                $output[] = "✓ Column 'due_date' already exists in 'inv_invoices' table.";
                $output[] = "  Skipping column creation.";
            } else {
                // Add the column
                $fields = [
                    'due_date' => [
                        'type' => 'DATE',
                        'null' => TRUE,
                        'after' => 'invoice_date'
                    ]
                ];
                
                $this->dbforge->add_column('inv_invoices', $fields);
                $output[] = "✓ Column 'due_date' added successfully to 'inv_invoices' table.";
            }

            // Update existing records
            $output[] = "";
            $output[] = "Updating existing records...";
            
            $this->db->query("UPDATE `inv_invoices` SET `due_date` = `invoice_date` WHERE `due_date` IS NULL");
            $affected_rows = $this->db->affected_rows();
            
            $output[] = "✓ Updated {$affected_rows} records (set due_date = invoice_date).";

            // Verification
            $output[] = "";
            $output[] = "=== Verification ===";
            
            $stats = $this->db->query("
                SELECT 
                    COUNT(*) as total_invoices,
                    COUNT(due_date) as invoices_with_due_date,
                    COUNT(*) - COUNT(due_date) as invoices_without_due_date
                FROM `inv_invoices`
            ")->row();

            $output[] = "Total Invoices: " . $stats->total_invoices;
            $output[] = "Invoices with due_date: " . $stats->invoices_with_due_date;
            $output[] = "Invoices without due_date: " . $stats->invoices_without_due_date;

            // Check column details
            $column_info = $this->db->query("SHOW COLUMNS FROM `inv_invoices` LIKE 'due_date'")->row();
            if ($column_info) {
                $output[] = "";
                $output[] = "Column Details:";
                $output[] = "  - Field: " . $column_info->Field;
                $output[] = "  - Type: " . $column_info->Type;
                $output[] = "  - Null: " . $column_info->Null;
                $output[] = "  - Default: " . ($column_info->Default ?: 'NULL');
            }

            $output[] = "";
            $output[] = "=== Migration Completed Successfully ===";
            $output[] = "Finished at: " . date('Y-m-d H:i:s');

        } catch (Exception $e) {
            $output[] = "";
            $output[] = "✗ ERROR: " . $e->getMessage();
            $output[] = "";
            $output[] = "=== Migration Failed ===";
        }

        // Display output
        header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $output);
    }

    /**
     * Rollback: Remove due_date column
     * URL: /muhasebe/migrate/rollback_due_date
     */
    public function rollback_due_date() {
        // Security: Only allow in development or with specific key
        $allowed_key = 'migration2026'; // Change this to a secure key
        $provided_key = $this->input->get('key');
        
        if (ENVIRONMENT === 'production' && $provided_key !== $allowed_key) {
            show_error('Unauthorized access. Please provide valid migration key.', 403);
            return;
        }

        $output = [];
        $output[] = "=== Rollback: Remove due_date Column ===";
        $output[] = "Started at: " . date('Y-m-d H:i:s');
        $output[] = "";
        $output[] = "⚠️  WARNING: This will permanently delete all due_date data!";
        $output[] = "";

        try {
            if (!$this->db->field_exists('due_date', 'inv_invoices')) {
                $output[] = "✓ Column 'due_date' does not exist. Nothing to rollback.";
            } else {
                $this->dbforge->drop_column('inv_invoices', 'due_date');
                $output[] = "✓ Column 'due_date' removed successfully from 'inv_invoices' table.";
            }

            $output[] = "";
            $output[] = "=== Rollback Completed Successfully ===";
            $output[] = "Finished at: " . date('Y-m-d H:i:s');

        } catch (Exception $e) {
            $output[] = "";
            $output[] = "✗ ERROR: " . $e->getMessage();
            $output[] = "";
            $output[] = "=== Rollback Failed ===";
        }

        header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $output);
    }

    /**
     * List all available migrations
     * URL: /muhasebe/migrate
     */
    public function index() {
        $output = [];
        $output[] = "=== Available Migrations ===";
        $output[] = "";
        $output[] = "1. Add due_date column:";
        $output[] = "   URL: " . base_url('migrate/add_due_date');
        if (ENVIRONMENT === 'production') {
            $output[] = "   (Requires ?key=migration2026)";
        }
        $output[] = "";
        $output[] = "2. Rollback due_date column:";
        $output[] = "   URL: " . base_url('migrate/rollback_due_date');
        if (ENVIRONMENT === 'production') {
            $output[] = "   (Requires ?key=migration2026)";
        }
        $output[] = "";
        $output[] = "Environment: " . ENVIRONMENT;
        $output[] = "";

        header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $output);
    }
}
