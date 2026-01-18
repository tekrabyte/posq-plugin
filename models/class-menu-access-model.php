<?php
/**
 * Menu Access Model - Business logic for menu access control
 */

if (!defined('ABSPATH')) exit;

class POSQ_Menu_Access_Model {

    /**
     * Get all menu access settings
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY role, menu");
    }

    /**
     * Get menu access by role
     */
    public static function get_by_role($role) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE role = %s ORDER BY menu",
            $role
        ));
    }

    /**
     * Check if role has access to menu
     */
    public static function has_access($role, $menu) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT is_accessible FROM $table WHERE role = %s AND menu = %s",
            $role,
            $menu
        ));
        
        return (bool) $result;
    }

    /**
     * Update menu access for role
     */
    public static function update($role, $menu, $is_accessible) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        return $wpdb->replace($table, [
            'role' => sanitize_text_field($role),
            'menu' => sanitize_text_field($menu),
            'is_accessible' => (int) $is_accessible
        ]);
    }

    /**
     * Bulk update menu access
     */
    public static function bulk_update($config) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        foreach (['cashier', 'manager'] as $role) {
            if (isset($config[$role])) {
                foreach ($config[$role] as $menu_item) {
                    $wpdb->replace($table, [
                        'role' => $role,
                        'menu' => $menu_item['menu'],
                        'is_accessible' => $menu_item['is_accessible'] ? 1 : 0
                    ]);
                }
            }
        }
        
        return true;
    }

    /**
     * Format menu access data for API response
     */
    public static function format($access) {
        return [
            'role' => $access->role,
            'menu' => $access->menu,
            'is_accessible' => (bool) $access->is_accessible
        ];
    }

    /**
     * Format all access data grouped by role
     */
    public static function format_grouped() {
        $all_access = self::get_all();
        
        $config = [
            'cashier' => [],
            'manager' => [],
            'owner' => []
        ];
        
        foreach ($all_access as $access) {
            $config[$access->role][] = [
                'menu' => $access->menu,
                'is_accessible' => (bool) $access->is_accessible
            ];
        }
        
        return $config;
    }
}
