<?php
/**
 * Held Order Endpoints
 * Handles held orders (cart save/resume)
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-held-order-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-user-model.php';

class POSQ_Held_Order_Endpoints {

    public static function get_held_orders($request) {
        $user_id = get_current_user_id();
        
        // Get user's outlet
        $profile = POSQ_User_Model::get_profile($user_id);
        
        if ($profile && $profile->outlet_id) {
            $held_orders = POSQ_Held_Order_Model::get_by_outlet($profile->outlet_id);
        } else {
            // For admin/owner, show all held orders
            $held_orders = POSQ_Held_Order_Model::get_all();
        }
        
        $result = [];
        foreach ($held_orders as $order) {
            $result[] = POSQ_Held_Order_Model::format($order);
        }
        
        return $result;
    }

    public static function create_held_order($request) {
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        
        $outlet_id = !empty($data['outletId']) ? intval($data['outletId']) : null;
        $cart = !empty($data['cart']) ? $data['cart'] : [];
        
        if (!$outlet_id || empty($cart)) {
            return new WP_Error('invalid_data', 'Outlet ID and cart are required', ['status' => 400]);
        }
        
        $data['userId'] = $user_id;
        $id = POSQ_Held_Order_Model::create($data);
        
        if (!$id) {
            return new WP_Error('insert_failed', 'Failed to create held order', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'id' => $id
        ];
    }

    public static function delete_held_order($request) {
        $id = intval($request['id']);
        
        $result = POSQ_Held_Order_Model::delete($id);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete held order', ['status' => 500]);
        }
        
        return ['success' => true];
    }
}
