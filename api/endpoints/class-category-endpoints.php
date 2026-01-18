<?php
/**
 * Category Endpoints
 * Handles category management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-category-model.php';

class POSQ_Category_Endpoints {

    public static function get_categories() {
        return POSQ_Category_Model::get_all();
    }

    public static function create_category($request) {
        return POSQ_Category_Model::create($request);
    }

    public static function update_category($request) {
        return POSQ_Category_Model::update($request);
    }

    public static function delete_category($request) {
        return POSQ_Category_Model::delete($request);
    }
}
