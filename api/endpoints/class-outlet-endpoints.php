<?php
/**
 * Outlet Management Endpoints
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Outlet_Endpoints {

    public static function register_routes($namespace) {
        register_rest_route($namespace, '/outlets', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_outlets'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_outlet'],
                'permission_callback' => [POSQ_Permissions::class, 'check_owner_permission']
            ]
        ]);

        register_rest_route($namespace, '/outlets/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [self::class, 'update_outlet'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'delete_outlet'],
                'permission_callback' => [POSQ_Permissions::class, 'check_owner_permission']
            ]
        ]);
    }

    public static function get_outlets() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        $outlets = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY id DESC");
        
        $data = [];
        foreach ($outlets as $outlet) {
            $data[] = [
                'id' => (int) $outlet->id,
                'name' => $outlet->name,
                'address' => $outlet->address,
                'created_at' => $outlet->created_at,
                'is_active' => (bool) $outlet->is_active
            ];
        }
        
        return $data;
    }

    public static function create_outlet($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name']) || empty($data['address'])) {
            return new WP_Error('missing_fields', 'Name and address required', ['status' => 400]);
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_outlets', [
            'name' => sanitize_text_field($data['name']),
            'address' => sanitize_textarea_field($data['address']),
            'is_active' => 1
        ]);

        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public static function update_outlet($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        global $wpdb;
        $update_data = [];
        
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (!empty($data['address'])) $update_data['address'] = sanitize_textarea_field($data['address']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];

        $wpdb->update(
            $wpdb->prefix . 'posq_outlets',
            $update_data,
            ['id' => $id]
        );

        return ['success' => true];
    }

    public static function delete_outlet($request) {
        $id = (int) $request['id'];
        
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'posq_outlets', ['id' => $id]);

        return ['success' => true];
    }
}
