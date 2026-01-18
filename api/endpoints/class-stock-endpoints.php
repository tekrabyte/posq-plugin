<?php
/**
 * Stock Endpoints
 * Handles stock management operations
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-stock-model.php';

class POSQ_Stock_Endpoints {

    public static function add_stock($request) {
        return POSQ_Stock_Model::add_stock($request);
    }

    public static function reduce_stock($request) {
        return POSQ_Stock_Model::reduce_stock($request);
    }

    public static function transfer_stock($request) {
        return POSQ_Stock_Model::transfer_stock($request);
    }

    public static function get_stock_logs($request) {
        return POSQ_Stock_Model::get_logs($request);
    }
}
