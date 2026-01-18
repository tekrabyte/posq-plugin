<?php
/**
 * Brand Endpoints
 * Handles brand management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-brand-model.php';

class POSQ_Brand_Endpoints {

    public static function get_brands() {
        return POSQ_Brand_Model::get_all();
    }

    public static function create_brand($request) {
        return POSQ_Brand_Model::create($request);
    }

    public static function update_brand($request) {
        return POSQ_Brand_Model::update($request);
    }

    public static function delete_brand($request) {
        return POSQ_Brand_Model::delete($request);
    }
}
