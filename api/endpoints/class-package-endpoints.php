<?php
/**
 * Package Endpoints
 * Handles package management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-package-model.php';

class POSQ_Package_Endpoints {

    public static function get_packages() {
        return POSQ_Package_Model::get_all();
    }

    public static function create_package($request) {
        return POSQ_Package_Model::create($request);
    }

    public static function update_package($request) {
        return POSQ_Package_Model::update($request);
    }

    public static function delete_package($request) {
        return POSQ_Package_Model::delete($request);
    }
}
