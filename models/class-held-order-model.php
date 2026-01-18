<?php
/**
 * Held Order Model - Business logic for held orders (cart save/resume)
 */

if (!defined('ABSPATH')) exit;

class POSQ_Held_Order_Model {

    /**
     * Get all held orders
     */
    public static function get_all($outlet_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        if ($outlet_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE outlet_id = %d ORDER BY timestamp DESC",
                $outlet_id
            ));
        }
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC");
    }

    /**
     * Get held order by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Get held orders by user
     */
    public static function get_by_user($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY timestamp DESC",
            $user_id
        ));
    }

    /**
     * Get held orders by outlet
     */
    public static function get_by_outlet($outlet_id) {
        return self::get_all($outlet_id);
    }

    /**
     * Create new held order
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        $insert_data = [
            'user_id' => (int) $data['userId'],
            'outlet_id' => (int) $data['outletId'],
            'cart_data' => json_encode($data['cart']),
            'payment_methods_data' => !empty($data['paymentMethods']) ? json_encode($data['paymentMethods']) : null,
            'order_type' => !empty($data['orderType']) ? sanitize_text_field($data['orderType']) : null,
            'table_number' => !empty($data['tableNumber']) ? sanitize_text_field($data['tableNumber']) : null,
            'customer_name' => !empty($data['customerName']) ? sanitize_text_field($data['customerName']) : null,
            'notes' => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null
        ];
        
        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update held order
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        $update_data = [];
        
        if (isset($data['cart'])) $update_data['cart_data'] = json_encode($data['cart']);
        if (isset($data['paymentMethods'])) $update_data['payment_methods_data'] = json_encode($data['paymentMethods']);
        if (isset($data['orderType'])) $update_data['order_type'] = sanitize_text_field($data['orderType']);
        if (isset($data['tableNumber'])) $update_data['table_number'] = sanitize_text_field($data['tableNumber']);
        if (isset($data['customerName'])) $update_data['customer_name'] = sanitize_text_field($data['customerName']);
        if (isset($data['notes'])) $update_data['notes'] = sanitize_textarea_field($data['notes']);
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete held order
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Format held order data for API response
     */
    public static function format($order) {
        return [
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

    /**
     * Clean old held orders (older than X days)
     */
    public static function clean_old($days = 7) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_held_orders';
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
}
