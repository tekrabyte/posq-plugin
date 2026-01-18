<?php
/**
 * Payment Method Configuration Endpoints
 * Handles payment method settings
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-payment-method-model.php';

class POSQ_Payment_Method_Endpoints {

    public static function get_payment_methods() {
        $methods = POSQ_Payment_Method_Model::get_all();
        
        $data = [];
        foreach ($methods as $method) {
            $data[] = POSQ_Payment_Method_Model::format($method);
        }
        
        return $data;
    }

    public static function update_payment_method($request) {
        $id = sanitize_text_field($request['id']);
        $data = $request->get_json_params();

        $update_data = [];
        
        if (isset($data['enabled'])) {
            $update_data['enabled'] = (int) $data['enabled'];
        }
        
        if (isset($data['fee'])) {
            $update_data['fee'] = (float) $data['fee'];
        }
        
        if (isset($data['feeType'])) {
            $update_data['feeType'] = sanitize_text_field($data['feeType']);
        }
        
        if (isset($data['config'])) {
            $update_data['config'] = $data['config'];
        }

        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }

        $result = POSQ_Payment_Method_Model::update($id, $update_data);

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

        $id = POSQ_Payment_Method_Model::create($data);

        if (!$id) {
            return new WP_Error('insert_failed', 'Failed to create payment method', ['status' => 500]);
        }

        return [
            'success' => true,
            'id' => $id
        ];
    }

    public static function delete_custom_payment_method($request) {
        $id = sanitize_text_field($request['id']);
        
        $result = POSQ_Payment_Method_Model::delete($id);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Cannot delete default payment method or method not found', ['status' => 400]);
        }
        
        return ['success' => true];
    }
}
