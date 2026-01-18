<?php
/**
 * Payment Method Configuration Endpoints
 * Handles payment method settings
 */

if (!defined('ABSPATH')) exit;

class POSQ_Payment_Method_Endpoints {

    public static function get_payment_methods() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        $methods = $wpdb->get_results("SELECT * FROM $table ORDER BY is_default DESC, created_at ASC");
        
        $data = [];
        foreach ($methods as $method) {
            $config = null;
            if ($method->config_data) {
                $config = json_decode($method->config_data, true);
            }
            
            $data[] = [
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
        
        return $data;
    }

    public static function update_payment_method($request) {
        $id = sanitize_text_field($request['id']);
        $data = $request->get_json_params();

        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        $update_data = [];
        
        if (isset($data['enabled'])) {
            $update_data['enabled'] = (int) $data['enabled'];
        }
        
        if (isset($data['fee'])) {
            $update_data['fee'] = (float) $data['fee'];
        }
        
        if (isset($data['feeType'])) {
            $update_data['fee_type'] = sanitize_text_field($data['feeType']);
        }
        
        if (isset($data['config'])) {
            $update_data['config_data'] = json_encode($data['config']);
        }

        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }

        $result = $wpdb->update(
            $table,
            $update_data,
            ['id' => $id]
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update payment method', ['status' => 500]);
        }

        return ['success' => true];
    }

    public static function create_custom_payment_method($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name']) || empty($data['category'])) {
            return new WP_Error('missing_fields', 'Name and category are required', ['status' => 400]);
        }

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

        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to create payment method', ['status' => 500]);
        }

        return [
            'success' => true,
            'id' => $id
        ];
    }

    public static function delete_custom_payment_method($request) {
        $id = sanitize_text_field($request['id']);
        
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';
        
        // Check if it's a default method
        $method = $wpdb->get_row($wpdb->prepare(
            "SELECT is_default FROM $table WHERE id = %s",
            $id
        ));
        
        if (!$method) {
            return new WP_Error('not_found', 'Payment method not found', ['status' => 404]);
        }
        
        if ($method->is_default) {
            return new WP_Error('cannot_delete', 'Cannot delete default payment method', ['status' => 400]);
        }
        
        $result = $wpdb->delete($table, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete payment method', ['status' => 500]);
        }
        
        return ['success' => true];
    }
}
