<?php
/**
 * Brand Model - Business logic for brands
 */

if (!defined('ABSPATH')) exit;

class POSQ_Brand_Model {

    /**
     * Get all brands
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_brands';
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
    }

    /**
     * Get brand by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_brands';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Create new brand
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_brands';
        
        $result = $wpdb->insert($table, [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        ]);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update brand
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_brands';
        
        $update_data = [];
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }
        if (isset($data['is_active'])) {
            $update_data['is_active'] = (int) $data['is_active'];
        }

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete brand
     */
    public static function delete($id) {
        global $wpdb;
        
        // Check if any products use this brand
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}posq_products WHERE brand_id = %d AND is_deleted = 0",
            $id
        ));
        
        if ($count > 0) {
            return false; // Cannot delete if in use
        }

        $table = $wpdb->prefix . 'posq_brands';
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Format brand data for API response
     */
    public static function format($brand) {
        return [
            'id' => (int) $brand->id,
            'name' => $brand->name,
            'description' => $brand->description,
            'created_at' => $brand->created_at,
            'is_active' => (bool) $brand->is_active
        ];
    }
}
