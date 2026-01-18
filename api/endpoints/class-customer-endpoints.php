<?php
/**
 * Customer Endpoints
 * Handles customer management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-customer-model.php';

class POSQ_Customer_Endpoints {

    public static function get_customers() {
        return POSQ_Customer_Model::get_all();
    }

    public static function create_customer($request) {
        return POSQ_Customer_Model::create($request);
    }

    public static function update_customer($request) {
        return POSQ_Customer_Model::update($request);
    }

    public static function delete_customer($request) {
        return POSQ_Customer_Model::delete($request);
    }
}
