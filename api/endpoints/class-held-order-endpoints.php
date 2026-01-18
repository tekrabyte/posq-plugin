<?php
/**
 * Held Order Endpoints - Handle held orders (saved carts)
 */

if (!defined('ABSPATH')) exit;

class POSQ_Held_Order_Endpoints {

    /**
     * Get all held orders for current user's outlet
     */
    public static function get_held_orders($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Get user's outlet
        $user_outlet = get_user_meta($user_id, 'posq_outlet_id', true);
        
        $table = $wpdb->prefix . 'posq_held_orders';
        
        if ($user_outlet) {
            $held_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE outlet_id = %d ORDER BY created_at DESC",
                $user_outlet
            ));
        } else {
            // For admin/owner, show all held orders
            $held_orders = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        }
        
        $result = [];
        foreach ($held_orders as $order) {
            $result[] = [
                'id' => $order->id,
                'userId' => $order->user_id,
                'outletId' => $order->outlet_id,
                'cart' => json_decode($order->cart_data, true),
                'paymentMethods' => json_decode($order->payment_methods_data, true),
                'customerNote' => $order->customer_note,
                'orderType' => $order->order_type,
                'timestamp' => strtotime($order->created_at) * 1000 // Convert to milliseconds
            ];
        }
        
        return $result;
    }

    /**
     * Create a held order
     */
    public static function create_held_order($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        
        $outlet_id = !empty($data['outletId']) ? intval($data['outletId']) : null;
        $cart = !empty($data['cart']) ? $data['cart'] : [];
        $payment_methods = !empty($data['paymentMethods']) ? $data['paymentMethods'] : [];
        $customer_note = !empty($data['customerNote']) ? sanitize_textarea_field($data['customerNote']) : null;
        $order_type = !empty($data['orderType']) ? sanitize_text_field($data['orderType']) : null;
        
        if (!$outlet_id || empty($cart)) {
            return new WP_Error('invalid_data', 'Outlet ID and cart are required', ['status' => 400]);
        }
        
        $table = $wpdb->prefix . 'posq_held_orders';
        
        $result = $wpdb->insert($table, [
            'user_id' => $user_id,
            'outlet_id' => $outlet_id,
            'cart_data' => json_encode($cart),
            'payment_methods_data' => json_encode($payment_methods),
            'customer_note' => $customer_note,
            'order_type' => $order_type,
            'created_at' => current_time('mysql')
        ]);
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to create held order', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'id' => $wpdb->insert_id
        ];
    }

    /**
     * Delete a held order
     */
    public static function delete_held_order($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $table = $wpdb->prefix . 'posq_held_orders';
        
        $result = $wpdb->delete($table, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete held order', ['status' => 500]);
        }
        
        return ['success' => true];
    }
}
