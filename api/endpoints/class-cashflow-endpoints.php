<?php
/**
 * Cashflow Category Endpoints
 * Handles cashflow category management
 */

if (!defined('ABSPATH')) exit;

class POSQ_Cashflow_Endpoints {

    public static function get_cashflow_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        $categories = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        
        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id' => (int) $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'description' => $cat->description,
                'created_at' => $cat->created_at,
                'is_active' => (bool) $cat->is_active
            ];
        }
        
        return $data;
    }

    public static function create_cashflow_category($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name']) || empty($data['type'])) {
            return new WP_Error('missing_fields', 'Name and type required', ['status' => 400]);
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_cashflow_categories', [
            'name' => sanitize_text_field($data['name']),
            'type' => sanitize_text_field($data['type']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        ]);

        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public static function update_cashflow_category($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        global $wpdb;
        $update_data = [];
        
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (!empty($data['type'])) $update_data['type'] = sanitize_text_field($data['type']);
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];

        $wpdb->update(
            $wpdb->prefix . 'posq_cashflow_categories',
            $update_data,
            ['id' => $id]
        );

        return ['success' => true];
    }

    public static function delete_cashflow_category($request) {
        $id = (int) $request['id'];
        
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'posq_cashflow_categories', ['id' => $id]);

        return ['success' => true];
    }
}
