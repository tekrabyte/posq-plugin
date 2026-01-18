<?php
/**
 * Held Order Endpoints
 * Handles held orders (cart save/resume)
 */

if (!defined('ABSPATH')) exit;

class POSQ_Held_Order_Endpoints {

    public static function get_held_orders($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Get user's outlet
        $profile = $wpdb->get_row($wpdb->prepare(
            "SELECT outlet_id FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
            $user_id
        ));
        
        $table = $wpdb->prefix . 'posq_held_orders';
        
        if ($profile && $profile->outlet_id) {
            $held_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE outlet_id = %d ORDER BY timestamp DESC",
                $profile->outlet_id
            ));
        } else {
            // For admin/owner, show all held orders
            $held_orders = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC");
        }
        
        $result = [];
        foreach ($held_orders as $order) {
            $result[] = [
                'id' => (int) $order->id,
                'userId' => (int) $order->user_id,
                'outletId' => (int) $order->outlet_id,
                'cart' => json_decode($order->cart_data, true),
                'paymentMethods' => json_decode($order->payment_methods_data, true),
                'orderType' => $order->order_type,
                'tableNumber' => $order->table_number,
                'customerName' => $order->customer_name,
                'notes' => $order->notes,
                'timestamp' => strtotime($order->timestamp) * 1000
            ];
        }
        
        return $result;
    }

    public static function create_held_order($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        
        $outlet_id = !empty($data['outletId']) ? intval($data['outletId']) : null;
        $cart = !empty($data['cart']) ? $data['cart'] : [];
        $payment_methods = !empty($data['paymentMethods']) ? $data['paymentMethods'] : [];
        $order_type = !empty($data['orderType']) ? sanitize_text_field($data['orderType']) : null;
        $table_number = !empty($data['tableNumber']) ? sanitize_text_field($data['tableNumber']) : null;
        $customer_name = !empty($data['customerName']) ? sanitize_text_field($data['customerName']) : null;
        $notes = !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null;
        
        if (!$outlet_id || empty($cart)) {
            return new WP_Error('invalid_data', 'Outlet ID and cart are required', ['status' => 400]);
        }
        
        $table = $wpdb->prefix . 'posq_held_orders';
        
        $result = $wpdb->insert($table, [
            'user_id' => $user_id,
            'outlet_id' => $outlet_id,
            'cart_data' => json_encode($cart),
            'payment_methods_data' => json_encode($payment_methods),
            'order_type' => $order_type,
            'table_number' => $table_number,
            'customer_name' => $customer_name,
            'notes' => $notes
        ]);
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to create held order', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'id' => $wpdb->insert_id
        ];
    }

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
