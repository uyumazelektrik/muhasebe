<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InspectProduct extends CI_Controller {
    public function index() {
        $fields = $this->db->list_fields('inv_products');
        echo "<pre>";
        print_r($fields);
        echo "</pre>";
    }
}
