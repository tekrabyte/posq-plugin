<?php
/**
 * Kitchen Order Endpoints
 * Handles kitchen order tracking and status updates
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-kitchen-order-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-user-model.php';

class POSQ_Kitchen_Order_Endpoints {

    public static function get_kitchen_orders($request) {
        $user_id = get_current_user_id();
        
        // Get user's outlet
        $profile = POSQ_User_Model::get_profile($user_id);
        
        $params = $request->get_params();
        $status = !empty($params['status']) ? sanitize_text_field($params['status']) : null;
        
        $outlet_id = ($profile && $profile->outlet_id) ? $profile->outlet_id : null;
        $orders = POSQ_Kitchen_Order_Model::get_all($outlet_id, $status);
        
        $result = [];
        foreach ($orders as $order) {
            $result[] = POSQ_Kitchen_Order_Model::format($order);
        }
        
        return $result;
    }

    public static function update_kitchen_order($request) {
        $id = intval($request['id']);
        $data = $request->get_json_params();
        
        $status = !empty($data['status']) ? sanitize_text_field($data['status']) : null;
        
        if (!$status) {
            return new WP_Error('invalid_data', 'Status is required', ['status' => 400]);
        }
        
        $allowed_statuses = ['pending', 'processing', 'ready', 'completed', 'canceled'];
        if (!in_array($status, $allowed_statuses)) {
            return new WP_Error('invalid_status', 'Invalid status value', ['status' => 400]);
        }
        
        $result = POSQ_Kitchen_Order_Model::update_status($id, $status);
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update order status', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'id' => $id,
            'status' => $status
        ];
    }
}
