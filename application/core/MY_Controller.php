<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // List of controllers/methods that don't require login
        $allowed_controllers = ['auth', 'whatsapp_webhook', 'statement'];
        
        $current_controller = strtolower($this->router->class);
        
        if (!in_array($current_controller, $allowed_controllers)) {
            if (!$this->session->userdata('logged_in')) {
                redirect('login');
            }
        }
    }
}
