<?php
/**
 * Bundle Endpoints
 * Handles bundle management with manual stock support
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-bundle-model.php';

class POSQ_Bundle_Endpoints {

    public static function get_bundles() {
        return POSQ_Bundle_Model::get_all();
    }

    public static function create_bundle($request) {
        return POSQ_Bundle_Model::create($request);
    }

    public static function update_bundle($request) {
        return POSQ_Bundle_Model::update($request);
    }

    public static function delete_bundle($request) {
        return POSQ_Bundle_Model::delete($request);
    }
}
