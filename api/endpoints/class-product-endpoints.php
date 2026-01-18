<?php
/**
 * Product Endpoints
 * Handles product management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-product-model.php';

class POSQ_Product_Endpoints {

    public static function get_products() {
        return POSQ_Product_Model::get_all();
    }

    public static function get_product($request) {
        return POSQ_Product_Model::get_by_id($request);
    }

    public static function search_products($request) {
        return POSQ_Product_Model::search($request);
    }

    public static function create_product($request) {
        return POSQ_Product_Model::create($request);
    }

    public static function update_product($request) {
        return POSQ_Product_Model::update($request);
    }

    public static function delete_product($request) {
        return POSQ_Product_Model::delete($request);
    }
}
