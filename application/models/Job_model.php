<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Job_model extends CI_Model {

    public function get_all($filters = [], $limit = 50, $offset = 0) {
        $this->db->select('jobs.*, inv_entities.name as customer_name');
        $this->db->from('jobs');
        $this->db->join('inv_entities', 'jobs.customer_id = inv_entities.id', 'left');
        
        if (!empty($filters['status'])) {
            $this->db->where('jobs.status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('jobs.customer_name_text', $filters['search']);
            $this->db->or_like('jobs.description', $filters['search']);
            $this->db->or_like('inv_entities.name', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('jobs.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function get_by_id($id) {
        $this->db->select('jobs.*, inv_entities.name as customer_name');
        $this->db->from('jobs');
        $this->db->join('inv_entities', 'jobs.customer_id = inv_entities.id', 'left');
        $this->db->where('jobs.id', $id);
        return $this->db->get()->row_array();
    }

    public function get_materials($job_id) {
        $this->db->select('job_items.*, inv_products.name as material_name, inv_products.unit');
        $this->db->from('job_items');
        $this->db->join('inv_products', 'job_items.product_id = inv_products.id');
        $this->db->where('job_items.job_id', $job_id);
        return $this->db->get()->result_array();
    }

    public function create($data) {
        $this->db->insert('jobs', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('jobs', $data);
    }

    public function add_material($data) {
        return $this->db->insert('job_items', $data);
    }

    public function remove_material($id) {
        $this->db->where('id', $id);
        return $this->db->delete('job_items');
    }

    public function update_total($job_id) {
        $this->db->select('SUM(quantity * unit_price) as total', FALSE);
        $this->db->where('job_id', $job_id);
        $total = $this->db->get('job_items')->row()->total;
        
        $this->db->where('id', $job_id);
        return $this->db->update('jobs', ['total_amount' => $total ?: 0]);
    }

    public function bill_job($job_id) {
        $job = $this->get_by_id($job_id);
        if (!$job || $job['invoice_status'] == 'Kesildi' || $job['status'] != 'Completed') {
            return ['status' => 'error', 'message' => 'İş faturalandırılmaya uygun değil.'];
        }

        $items = $this->get_materials($job_id);
        if (empty($items)) {
            return ['status' => 'error', 'message' => 'İş formunda hiç malzeme bulunmuyor.'];
        }

        $this->load->model('Invoice_model', 'invoice_model');
        
        $subtotal = 0;
        $tax_total = 0;
        $invoice_items = [];

        // Calculate totals and prepare items
        foreach ($items as $item) {
            $line_total = (float)$item['quantity'] * (float)$item['unit_price'];
            $line_tax = $line_total * (20 / 100); // 20% KDV as standard
            
            $subtotal += $line_total;
            $tax_total += $line_tax;

            $invoice_items[] = [
                'product_id' => $item['product_id'],
                'item_type' => 'stok',
                'description' => $item['material_name'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => 20,
                'tax_amount' => $line_tax,
                'total_amount' => $line_total + $line_tax
            ];
        }

        $invoice_data = [
            'invoice_no' => $this->invoice_model->get_next_invoice_no('JOB'),
            'entity_id' => $job['customer_id'],
            'type' => 'sale',
            'invoice_date' => date('Y-m-d'),
            'total_amount' => $subtotal,
            'tax_amount' => $tax_total,
            'net_amount' => $subtotal + $tax_total,
            'notes' => 'İş Formu #' . $job_id . ' faturalandırıldı. ' . $job['description'],
            'payment_status' => 'unpaid',
            'status' => 'finalized',
            'tax_included' => 0
        ];

        $this->db->trans_start();
        
        // Create Invoice Header
        $invoice_id = $this->invoice_model->create_invoice($invoice_data);
        
        if ($invoice_id) {
            foreach ($invoice_items as $inv_item) {
                $inv_item['invoice_id'] = $invoice_id;
                $this->invoice_model->add_invoice_item($inv_item);
                
                // Update Stock Quantities (Subtract since it's a sale)
                if (!empty($inv_item['product_id'])) {
                    $this->db->set('stock_quantity', 'stock_quantity - ' . (float)$inv_item['quantity'], FALSE);
                    $this->db->where('id', $inv_item['product_id']);
                    $this->db->update('inv_products');
                }
            }
            
            // Delete Job and Items (Remove from tracking after invoicing)
            $this->db->delete('job_items', ['job_id' => $job_id]);
            $this->db->delete('jobs', ['id' => $job_id]);
        }

        $this->db->trans_complete();
        
        if ($this->db->trans_status()) {
            return ['status' => 'success', 'message' => 'İş başarıyla faturalandırıldı.', 'invoice_id' => $invoice_id];
        } else {
            return ['status' => 'error', 'message' => 'Fatura oluşturulurken bir hata oluştu.'];
        }
    }
}
