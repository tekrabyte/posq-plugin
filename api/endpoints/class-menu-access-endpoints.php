<?php
/**
 * Menu Access Endpoints - Handle menu access configuration
 */

if (!defined('ABSPATH')) exit;

class POSQ_Menu_Access_Endpoints {

    /**
     * Get all menu access configuration
     */
    public static function get_menu_access() {
        global $wpdb;
        
        $menus = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posq_menu_access ORDER BY role, menu");
        
        $config = [
            'cashier' => [],
            'manager' => [],
            'owner' => []
        ];

        foreach ($menus as $menu) {
            $config[$menu->role][] = [
                'menu' => $menu->menu,
                'is_accessible' => (bool) $menu->is_accessible
            ];
        }

        return $config;
    }

    /**
     * Save menu access configuration
     */
    public static function save_menu_access($request) {
        $data = $request->get_json_params();

        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';

        foreach (['cashier', 'manager'] as $role) {
            if (isset($data[$role])) {
                foreach ($data[$role] as $menu_item) {
                    $wpdb->replace($table, [
                        'role' => $role,
                        'menu' => $menu_item['menu'],
                        'is_accessible' => $menu_item['is_accessible'] ? 1 : 0
                    ]);
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Get menu access for current user's role
     */
    public static function get_role_menu_access() {
        $role = POSQ_Permissions::get_user_role();
        
        global $wpdb;
        $menus = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}posq_menu_access WHERE role = %s",
            $role
        ));

        $data = [];
        foreach ($menus as $menu) {
            $data[] = [
                'menu' => $menu->menu,
                'is_accessible' => (bool) $menu->is_accessible
            ];
        }

        return $data;
    }
}
