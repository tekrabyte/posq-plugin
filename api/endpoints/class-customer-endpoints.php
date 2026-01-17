<?php
/**
 * Customer Management Endpoints
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Customer_Endpoints {

    public static function register_routes($namespace) {
        register_rest_route($namespace, '/customers', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_customers'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_customer'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ]
        ]);

        register_rest_route($namespace, '/customers/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [self::class, 'update_customer'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'delete_customer'],
                'permission_callback' => [POSQ_Auth::class, 'check_auth']
            ]
        ]);
    }

    public static function get_customers() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        $customers = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY name ASC");
        
        $data = [];
        foreach ($customers as $c) {
            $data[] = [
                'id' => (string) $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'address' => $c->address,
                'created_at' => $c->created_at
            ];
        }
        return $data;
    }

    public static function create_customer($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name'])) {
            return new WP_Error('missing_fields', 'Name required', ['status' => 400]);
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_customers', [
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'is_active' => 1
        ]);

        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public static function update_customer($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        global $wpdb;
        $update_data = [];
        
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['email'])) $update_data['email'] = sanitize_email($data['email']);
        if (isset($data['phone'])) $update_data['phone'] = sanitize_text_field($data['phone']);
        if (isset($data['address'])) $update_data['address'] = sanitize_textarea_field($data['address']);

        $wpdb->update(
            $wpdb->prefix . 'posq_customers',
            $update_data,
            ['id' => $id]
        );

        return ['success' => true];
    }

    public static function delete_customer($request) {
        $id = (int) $request['id'];
        global $wpdb;
        // Soft delete
        $wpdb->update(
            $wpdb->prefix . 'posq_customers',
            ['is_active' => 0],
            ['id' => $id]
        );
        return ['success' => true];
    }
}
