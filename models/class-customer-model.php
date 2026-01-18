<?php
/**
 * Customer Model - Business logic for customers
 */

if (!defined('ABSPATH')) exit;

class POSQ_Customer_Model {

    /**
     * Get all active customers
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY name ASC");
    }

    /**
     * Get customer by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $id
        ));
    }

    /**
     * Create new customer
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        $result = $wpdb->insert($table, [
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'is_active' => 1
        ]);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update customer
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['email'])) $update_data['email'] = sanitize_email($data['email']);
        if (isset($data['phone'])) $update_data['phone'] = sanitize_text_field($data['phone']);
        if (isset($data['address'])) $update_data['address'] = sanitize_textarea_field($data['address']);

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete customer (soft delete)
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_customers';
        
        return $wpdb->update($table, ['is_active' => 0], ['id' => $id]);
    }

    /**
     * Format customer data for API response
     */
    public static function format($customer) {
        return [
            'id' => (string) $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'created_at' => $customer->created_at
        ];
    }
}
