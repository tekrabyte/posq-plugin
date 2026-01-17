<?php
/**
 * Cashflow Category Endpoints
 * 
 * Handles cashflow category endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Cashflow_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/cashflow-categories', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_categories'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_category'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ]
        ]);
        
        register_rest_route($namespace, '/cashflow-categories/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [__CLASS__, 'update_category'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_category'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ]
        ]);
    }
    
    public static function get_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        $categories = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY name ASC");
        
        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id' => (string) $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'description' => $cat->description,
                'is_active' => (bool) $cat->is_active,
                'created_at' => $cat->created_at
            ];
        }
        return $data;
    }
    
    public static function create_category($request) {
        $data = $request->get_json_params();
        if (empty($data['name'])) {
            return new WP_Error('missing_fields', 'Name required', ['status' => 400]);
        }
        
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_cashflow_categories', [
            'name' => sanitize_text_field($data['name']),
            'type' => sanitize_text_field($data['type'] ?? 'expense'),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        ]);
        
        return ['success' => true, 'id' => $wpdb->insert_id];
    }
    
    public static function update_category($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();
        
        global $wpdb;
        $update_data = [];
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['type'])) $update_data['type'] = sanitize_text_field($data['type']);
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        
        $wpdb->update($wpdb->prefix . 'posq_cashflow_categories', $update_data, ['id' => $id]);
        return ['success' => true];
    }
    
    public static function delete_category($request) {
        $id = (int) $request['id'];
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'posq_cashflow_categories', ['is_active' => 0], ['id' => $id]);
        return ['success' => true];
    }
}
