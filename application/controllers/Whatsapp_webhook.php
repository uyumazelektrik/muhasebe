<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load necessary models
        $this->load->model('Job_model', 'job');
        $this->load->model('Product_model', 'product');
        // Disable CSRF for webhooks if enabled globally, or handle it via route exceptions
    }

    /**
     * Main Endpoint to receive messages
     * URL: /whatsapp-webhook/receive
     */
    public function receive() {
        // Read the incoming JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Log raw data for debugging
        log_message('info', 'WhatsApp Incoming: ' . $json);

        if (!$data) {
            $this->output->set_status_header(400);
            echo 'No data received';
            return;
        }

        // ----------------------------------------------------------------------
        // ADAPTER LAYER
        // Different providers (Twilio, Meta, Ultramsg, WppConnect) send different JSON structures.
        // You need to adapt this part based on which service you use.
        // ----------------------------------------------------------------------
        
        // Example for a generic structure:
        // $sender = $data['from'];
        // $message = $data['body'];
        // $group_id = $data['group_id'] ?? null;
        
        // Let's assume we extract the text successfully:
        $message_text = ""; 
        $sender_number = "";
        
        // MOCK LOGIC for demonstration
        // $message_text = "4 adet bant armatür değişti";
        
        if (empty($message_text)) {
            echo 'Ignored (No text)';
            return;
        }

        // ----------------------------------------------------------------------
        // PROCESS LOGIC
        // 1. Identify which Job/Customer this belongs to
        //    (Maybe based on sender number or a keyword like "#JOB123")
        // ----------------------------------------------------------------------

        // 2. Parse the text using our existing logic
        $parsed_items = $this->_parse_text_internal($message_text);

        if (empty($parsed_items)) {
             // Maybe autoreply: "I couldn't understand any items."
             return;
        }

        // 3. Save to database (Auto-add)
        // Note: For safety, you might want to save these as "Pending Suggestions" 
        // instead of directly inserting into the official job list.
        
        // Example: $this->job->add_material_suggestion($parsed_items);

        echo json_encode(['status' => 'success']);
    }

    /**
     * Reuses the parsing logic from Jobs controller
     */
    private function _parse_text_internal($text) {
        $lines = explode("\n", $text);
        $results = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Basic parsing regex
            $qty = 1;
            $search_term = $line;

            if (preg_match('/^(\d+([\.,]\d+)?)\s*(adet|tane|x|\s)?\s*(.*)$/iu', $line, $matches)) {
                $qty = floatval(str_replace(',', '.', $matches[1]));
                $search_term = trim($matches[4]);
            }

            if (strlen($search_term) < 2) continue;

            // Search product
            $matches_db = $this->product->get_all($search_term, 1);
            
            if (!empty($matches_db)) {
                $results[] = [
                    'product_id' => $matches_db[0]['id'],
                    'quantity' => $qty,
                    'raw_text' => $line
                ];
            }
        }
        return $results;
    }
}
