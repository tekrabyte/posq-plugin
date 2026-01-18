<?php
/**
 * Kitchen Order Model - Business logic for kitchen order tracking
 */

if (!defined('ABSPATH')) exit;

class POSQ_Kitchen_Order_Model {

    /**
     * Get all kitchen orders (active orders)
     */
    public static function get_all($outlet_id = null, $status = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        $where_clauses = ["status != 'completed'", "status != 'canceled'"];
        $params = [];
        
        if ($outlet_id) {
            $where_clauses[] = "outlet_id = %d";
            $params[] = $outlet_id;
        }
        
        if ($status) {
            $where_clauses[] = "status = %s";
            $params[] = $status;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE $where_sql ORDER BY timestamp DESC",
                ...$params
            ));
        }
        
        return $wpdb->get_results("SELECT * FROM $table WHERE $where_sql ORDER BY timestamp DESC");
    }

    /**
     * Get kitchen order by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Get order items
     */
    public static function get_order_items($transaction_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transaction_items';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT ti.*, p.name as product_name
            FROM $table ti
            LEFT JOIN {$wpdb->prefix}posq_products p ON ti.product_id = p.id
            WHERE ti.transaction_id = %d
        ", $transaction_id));
    }

    /**
     * Update order status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        $allowed_statuses = ['pending', 'processing', 'ready', 'completed', 'canceled'];
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }
        
        return $wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Get orders by status
     */
    public static function get_by_status($status, $outlet_id = null) {
        return self::get_all($outlet_id, $status);
    }

    /**
     * Get pending orders count
     */
    public static function get_pending_count($outlet_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        if ($outlet_id) {
            return (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE status = 'pending' AND outlet_id = %d",
                $outlet_id
            ));
        }
        
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status = 'pending'"
        );
    }

    /**
     * Format kitchen order data for API response
     */
    public static function format($order) {
        $items = self::get_order_items($order->id);
        
        $formatted_items = [];
        foreach ($items as $item) {
            $formatted_items[] = [
                'productId' => (int) $item->product_id,
                'productName' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
                'isPackage' => (bool) $item->is_package,
                'isBundle' => (bool) $item->is_bundle
            ];
        }
        
        return [
            'id' => (int) $order->id,
            'userId' => (int) $order->user_id,
            'outletId' => (int) $order->outlet_id,
            'total' => (int) $order->total,
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

    /**
     * Get orders summary by status
     */
    public static function get_status_summary($outlet_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        $where = '';
        if ($outlet_id) {
            $where = $wpdb->prepare(" AND outlet_id = %d", $outlet_id);
        }
        
        $query = "SELECT status, COUNT(*) as count 
                  FROM $table 
                  WHERE status != 'completed' AND status != 'canceled' $where
                  GROUP BY status";
        
        $results = $wpdb->get_results($query);
        
        $summary = [
            'pending' => 0,
            'processing' => 0,
            'ready' => 0
        ];
        
        foreach ($results as $row) {
            if (isset($summary[$row->status])) {
                $summary[$row->status] = (int) $row->count;
            }
        }
        
        return $summary;
    }
}
