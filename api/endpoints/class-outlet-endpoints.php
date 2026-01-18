<?php
/**
 * Outlet Endpoints
 * Handles outlet management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-outlet-model.php';

class POSQ_Outlet_Endpoints {

    public static function get_outlets() {
        return POSQ_Outlet_Model::get_all();
    }

    public static function create_outlet($request) {
        return POSQ_Outlet_Model::create($request);
    }

    public static function update_outlet($request) {
        return POSQ_Outlet_Model::update($request);
    }

    public static function delete_outlet($request) {
        return POSQ_Outlet_Model::delete($request);
    }
}
