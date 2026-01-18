<?php
/**
 * Transaction Endpoints
 * Handles transaction operations
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-transaction-model.php';

class POSQ_Transaction_Endpoints {

    public static function get_transactions($request) {
        return POSQ_Transaction_Model::get_all($request);
    }

    public static function create_transaction($request) {
        return POSQ_Transaction_Model::create($request);
    }
}
