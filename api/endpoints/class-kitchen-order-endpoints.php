<?php
/**
 * Kitchen Order Endpoints - Handle kitchen order tracking
 */

if (!defined('ABSPATH')) exit;

class POSQ_Kitchen_Order_Endpoints {

    /**
     * Get kitchen orders (transactions with kitchen-relevant statuses)
     */
    public static function get_kitchen_orders($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Get user's outlet
        $user_outlet = get_user_meta($user_id, 'posq_outlet_id', true);
        
        $params = $request->get_params();
        $status = !empty($params['status']) ? sanitize_text_field($params['status']) : null;
        
        $table = $wpdb->prefix . 'posq_transactions';
        
        // Build query
        $where_clauses = ["status != 'completed'", "status != 'canceled'"];
        $query_params = [];
        
        if ($user_outlet) {
            $where_clauses[] = "outlet_id = %d";
            $query_params[] = $user_outlet;
        }
        
        if ($status) {
            $where_clauses[] = "status = %s";
            $query_params[] = $status;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        if (!empty($query_params)) {
            $orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE $where_sql ORDER BY timestamp DESC",
                ...$query_params
            ));
        } else {
            $orders = $wpdb->get_results("SELECT * FROM $table WHERE $where_sql ORDER BY timestamp DESC");
        }
        
        // Get order items for each transaction
        $result = [];
        foreach ($orders as $order) {
            $items_table = $wpdb->prefix . 'posq_transaction_items';
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $items_table WHERE transaction_id = %d",
                $order->id
            ));
            
            $formatted_items = [];
            foreach ($items as $item) {
                $formatted_items[] = [
                    'productId' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'isPackage' => (bool) $item->is_package,
                    'isBundle' => (bool) $item->is_bundle
                ];
            }
            
            $result[] = [
                'id' => $order->id,
                'userId' => $order->user_id,
                'outletId' => $order->outlet_id,
                'total' => $order->total,
                'orderType' => $order->order_type,
                'tableNumber' => $order->table_number,
                'customerName' => $order->customer_name,
                'estimatedReadyTime' => $order->estimated_ready_time,
                'notes' => $order->notes,
                'status' => $order->status ?: 'pending',
                'timestamp' => strtotime($order->timestamp) * 1000,
                'items' => $formatted_items
            ];
        }
        
        return $result;
    }

    /**
     * Update kitchen order status
     */
    public static function update_kitchen_order($request) {
        global $wpdb;
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
        
        $table = $wpdb->prefix . 'posq_transactions';
        
        $result = $wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
        
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
