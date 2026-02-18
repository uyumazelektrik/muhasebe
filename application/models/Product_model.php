<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {



    public function get_all($search = '', $limit = 50, $offset = 0, $status = 'active') {
        $this->db->select('inv_products.*');
        // Select max buying price from past purchase invoices
        $this->db->select('(SELECT MAX(ii.unit_price) FROM inv_invoice_items ii 
                            JOIN inv_invoices i ON i.id = ii.invoice_id 
                            WHERE ii.product_id = inv_products.id AND i.type = "purchase") as max_buy_price');
        $this->db->from('inv_products');
        
        if ($status === 'active') {
            $this->db->where('is_active', 1);
        } elseif ($status === 'passive') {
            $this->db->where('is_active', 0);
        }
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->where("name COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->or_where("barcode COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->or_where("match_names COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->group_end();
        }
        $this->db->order_by('name', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }
    
    public function count_all($search = '', $status = 'active') {
        $this->db->from('inv_products');
        if ($status === 'active') {
            $this->db->where('is_active', 1);
        } elseif ($status === 'passive') {
            $this->db->where('is_active', 0);
        }
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->where("name COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->or_where("barcode COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->or_where("match_names COLLATE utf8mb4_turkish_ci LIKE '%".$this->db->escape_like_str($search)."%' ESCAPE '!'", NULL, FALSE);
            $this->db->group_end();
        }
        return $this->db->count_all_results();
    }
    
    public function soft_delete($id) {
        $this->db->where('id', $id);
        return $this->db->update('inv_products', ['is_active' => 0]);
    }
    
    public function restore($id) {
        $this->db->where('id', $id);
        return $this->db->update('inv_products', ['is_active' => 1]);
    }

    public function get_by_id($id) {
        return $this->db->get_where('inv_products', array('id' => $id))->row_array();
    }

    public function create($data) {
        return $this->db->insert('inv_products', $data);
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_products', $data);
    }

    public function get_active_for_pos() {
        return $this->db->where('is_active', 1)
                        ->order_by('name', 'ASC')
                        ->get('inv_products')
                        ->result_array();
    }

    public function get_stock_history($product_id, $limit = 50) {
        // Get stock history from inv_invoice_items (new system)
        // Using FALSE to prevent CodeIgniter from escaping CASE WHEN expressions
        $this->db->select('
            ii.id,
            ii.quantity,
            ii.unit_price,
            ii.tax_rate,
            ii.tax_amount,
            ii.total_amount,
            i.id as invoice_id,
            i.invoice_no as document_no,
            i.invoice_date as movement_date,
            i.type,
            CASE WHEN i.type = "purchase" THEN ii.quantity ELSE -ii.quantity END as qty_change,
            e.name as entity_name,
            e.id as entity_id,
            CASE WHEN i.type = "purchase" THEN "Alış Faturası" ELSE "Satış Faturası" END as description
        ', FALSE);
        $this->db->from('inv_invoice_items ii');
        $this->db->join('inv_invoices i', 'ii.invoice_id = i.id');
        $this->db->join('inv_entities e', 'i.entity_id = e.id', 'left');
        $this->db->where('ii.product_id', $product_id);
        $this->db->where('ii.item_type', 'stok');
        $this->db->order_by('i.invoice_date', 'DESC');
        $this->db->order_by('ii.id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }
}
