<?php
/**
 * Outlet Model - Business logic for outlets
 */

if (!defined('ABSPATH')) exit;

class POSQ_Outlet_Model {

    /**
     * Get all active outlets
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY id DESC");
    }

    /**
     * Get outlet by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $id
        ));
    }

    /**
     * Create new outlet
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        $result = $wpdb->insert($table, [
            'name' => sanitize_text_field($data['name']),
            'address' => sanitize_textarea_field($data['address']),
            'is_active' => 1
        ]);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update outlet
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        $update_data = [];
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['address'])) {
            $update_data['address'] = sanitize_textarea_field($data['address']);
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
     * Delete outlet
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_outlets';
        
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Format outlet data for API response
     */
    public static function format($outlet) {
        return [
            'id' => (int) $outlet->id,
            'name' => $outlet->name,
            'address' => $outlet->address,
            'created_at' => $outlet->created_at,
            'is_active' => (bool) $outlet->is_active
        ];
    }
}
