<?php
/**
 * Category Endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Category_Endpoints {

    public static function get_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_categories';
        
        $categories = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        
        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id' => (int) $cat->id,
                'name' => $cat->name,
                'description' => $cat->description,
                'created_at' => $cat->created_at,
                'is_active' => (bool) $cat->is_active
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
        $wpdb->insert($wpdb->prefix . 'posq_categories', [
            'name' => sanitize_text_field($data['name']),
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
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];

        $wpdb->update(
            $wpdb->prefix . 'posq_categories',
            $update_data,
            ['id' => $id]
        );

        return ['success' => true];
    }

    public static function delete_category($request) {
        $id = (int) $request['id'];
        
        global $wpdb;
        
        // Check if any products use this category
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}posq_products WHERE category_id = %d AND is_deleted = 0",
            $id
        ));
        
        if ($count > 0) {
            return new WP_Error('in_use', 'Cannot delete category: products are using it', ['status' => 400]);
        }

        $wpdb->delete($wpdb->prefix . 'posq_categories', ['id' => $id]);

        return ['success' => true];
    }
}
