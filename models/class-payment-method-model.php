<?php
/**
 * Payment Method Model - Business logic for payment method configuration
 */

if (!defined('ABSPATH')) exit;

class POSQ_Payment_Method_Model {

    /**
     * Get all payment methods
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY is_default DESC, created_at ASC");
    }

    /**
     * Get payment method by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %s",
            $id
        ));
    }

    /**
     * Get all enabled payment methods
     */
    public static function get_enabled() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        return $wpdb->get_results("SELECT * FROM $table WHERE enabled = 1 ORDER BY is_default DESC, created_at ASC");
    }

    /**
     * Create custom payment method
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        // Generate unique ID
        $id = 'custom-' . time() . '-' . wp_generate_password(4, false);
        
        $insert_data = [
            'id' => $id,
            'name' => sanitize_text_field($data['name']),
            'category' => sanitize_text_field($data['category']),
            'sub_category' => !empty($data['subCategory']) ? sanitize_text_field($data['subCategory']) : null,
            'enabled' => !empty($data['enabled']) ? 1 : 0,
            'icon' => !empty($data['icon']) ? sanitize_text_field($data['icon']) : 'CreditCard',
            'color' => !empty($data['color']) ? sanitize_text_field($data['color']) : 'bg-gray-500',
            'is_default' => 0,
            'fee' => isset($data['fee']) ? (float) $data['fee'] : 0,
            'fee_type' => !empty($data['feeType']) ? sanitize_text_field($data['feeType']) : 'percentage',
            'config_data' => !empty($data['config']) ? json_encode($data['config']) : null
        ];

        $result = $wpdb->insert($table, $insert_data);
        return $result ? $id : false;
    }

    /**
     * Update payment method
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['enabled'])) $update_data['enabled'] = (int) $data['enabled'];
        if (isset($data['fee'])) $update_data['fee'] = (float) $data['fee'];
        if (isset($data['feeType'])) $update_data['fee_type'] = sanitize_text_field($data['feeType']);
        if (isset($data['config'])) $update_data['config_data'] = json_encode($data['config']);

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete custom payment method
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        // Check if it's a default method
        $method = self::get_by_id($id);
        
        if (!$method) {
            return false;
        }
        
        if ($method->is_default) {
            return false; // Cannot delete default payment method
        }
        
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Format payment method data for API response
     */
    public static function format($method) {
        $config = null;
        if ($method->config_data) {
            $config = json_decode($method->config_data, true);
        }
        
        return [
            'id' => $method->id,
            'name' => $method->name,
            'category' => $method->category,
            'subCategory' => $method->sub_category,
            'enabled' => (bool) $method->enabled,
            'icon' => $method->icon,
            'color' => $method->color,
            'isDefault' => (bool) $method->is_default,
            'fee' => $method->fee ? (float) $method->fee : 0,
            'feeType' => $method->fee_type,
            'config' => $config
        ];
    }

    /**
     * Calculate fee for amount
     */
    public static function calculate_fee($method_id, $amount) {
        $method = self::get_by_id($method_id);
        
        if (!$method || !$method->enabled) {
            return 0;
        }
        
        if ($method->fee_type === 'percentage') {
            return ($amount * $method->fee) / 100;
        }
        
        return (float) $method->fee;
    }
}
