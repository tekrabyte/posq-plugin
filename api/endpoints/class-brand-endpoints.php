<?php
/**
 * Brand Management Endpoints
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Brand_Endpoints {

    public static function register_routes($namespace) {
        register_rest_route($namespace, '/brands', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_brands'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_brand'],
                'permission_callback' => [POSQ_Permissions::class, 'check_owner_permission']
            ]
        ]);

        register_rest_route($namespace, '/brands/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [self::class, 'update_brand'],
                'permission_callback' => [POSQ_Permissions::class, 'check_owner_permission']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'delete_brand'],
                'permission_callback' => [POSQ_Permissions::class, 'check_owner_permission']
            ]
        ]);
    }

    public static function get_brands() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_brands';
        
        $brands = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        
        $data = [];
        foreach ($brands as $brand) {
            $data[] = [
                'id' => (int) $brand->id,
                'name' => $brand->name,
                'description' => $brand->description,
                'created_at' => $brand->created_at,
                'is_active' => (bool) $brand->is_active
            ];
        }
        
        return $data;
    }

    public static function create_brand($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name'])) {
            return new WP_Error('missing_fields', 'Name required', ['status' => 400]);
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_brands', [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        ]);

        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public static function update_brand($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        global $wpdb;
        $update_data = [];
        
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];

        $wpdb->update(
            $wpdb->prefix . 'posq_brands',
            $update_data,
            ['id' => $id]
        );

        return ['success' => true];
    }

    public static function delete_brand($request) {
        $id = (int) $request['id'];
        
        global $wpdb;
        
        // Check if any products use this brand
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}posq_products WHERE brand_id = %d AND is_deleted = 0",
            $id
        ));
        
        if ($count > 0) {
            return new WP_Error('in_use', 'Cannot delete brand: products are using it', ['status' => 400]);
        }

        $wpdb->delete($wpdb->prefix . 'posq_brands', ['id' => $id]);

        return ['success' => true];
    }
}
