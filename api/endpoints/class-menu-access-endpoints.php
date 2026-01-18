<?php
/**
 * Menu Access Endpoints
 * Handles menu access configuration
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-menu-access-model.php';

class POSQ_Menu_Access_Endpoints {

    public static function get_menu_access() {
        return POSQ_Menu_Access_Model::format_grouped();
    }

    public static function save_menu_access($request) {
        $data = $request->get_json_params();

        $result = POSQ_Menu_Access_Model::bulk_update($data);

        if (!$result) {
            return new WP_Error('update_failed', 'Failed to update menu access', ['status' => 500]);
        }

        return ['success' => true];
    }

    public static function get_role_menu_access() {
        $role = posq_get_user_role();
        
        $menus = POSQ_Menu_Access_Model::get_by_role($role);

        $data = [];
        foreach ($menus as $menu) {
            $data[] = POSQ_Menu_Access_Model::format($menu);
        }

        return $data;
    }
}
