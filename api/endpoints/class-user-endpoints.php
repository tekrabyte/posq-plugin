<?php
/**
 * User Endpoints
 * Handles user management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-user-model.php';

class POSQ_User_Endpoints {

    public static function get_users() {
        return POSQ_User_Model::get_all();
    }

    public static function create_user($request) {
        return POSQ_User_Model::create($request);
    }

    public static function update_user($request) {
        return POSQ_User_Model::update($request);
    }

    public static function delete_user($request) {
        return POSQ_User_Model::delete($request);
    }
}
