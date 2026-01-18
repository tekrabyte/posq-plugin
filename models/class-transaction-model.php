<?php
/**
 * Transaction Model - Business logic for transactions
 */

if (!defined('ABSPATH')) exit;

class POSQ_Transaction_Model {

    /**
     * Get transactions with filters
     */
    public static function get_all($user_id = null, $role = 'owner', $outlet_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        $query = "
            SELECT t.*, o.name as outlet_name
            FROM $table t
            LEFT JOIN {$wpdb->prefix}posq_outlets o ON t.outlet_id = o.id
        ";

        $where = [];

        // Filter based on role
        if ($role === 'cashier' && $user_id) {
            $where[] = $wpdb->prepare("t.user_id = %d", $user_id);
        } elseif ($role === 'manager' && $outlet_id) {
            $where[] = $wpdb->prepare("t.outlet_id = %d", $outlet_id);
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        $query .= " ORDER BY t.timestamp DESC LIMIT 100";

        return $wpdb->get_results($query);
    }

    /**
     * Get transaction by ID
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
     * Get transaction items
     */
    public static function get_items($transaction_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transaction_items';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT ti.*, p.name as product_name
            FROM $table ti
            LEFT JOIN {$wpdb->prefix}posq_products p ON ti.product_id = p.id
            WHERE ti.transaction_id = %d",
            $transaction_id
        ));
    }

    /**
     * Get payment methods for transaction
     */
    public static function get_payment_methods($transaction_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE transaction_id = %d",
            $transaction_id
        ));
    }

    /**
     * Create new transaction
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        $transaction_data = [
            'user_id' => get_current_user_id(),
            'outlet_id' => (int) $data['outletId'],
            'total' => $data['total'],
            'status' => 'pending'
        ];
        
        // Add optional fields
        if (!empty($data['orderType'])) {
            $transaction_data['order_type'] = sanitize_text_field($data['orderType']);
        }
        if (!empty($data['tableNumber'])) {
            $transaction_data['table_number'] = sanitize_text_field($data['tableNumber']);
        }
        if (!empty($data['customerName'])) {
            $transaction_data['customer_name'] = sanitize_text_field($data['customerName']);
        }
        if (!empty($data['notes'])) {
            $transaction_data['notes'] = sanitize_textarea_field($data['notes']);
        }
        
        $result = $wpdb->insert($table, $transaction_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Add transaction items
     */
    public static function add_items($transaction_id, $items) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transaction_items';
        
        foreach ($items as $item) {
            $wpdb->insert($table, [
                'transaction_id' => $transaction_id,
                'product_id' => (int) $item['productId'],
                'quantity' => (int) $item['quantity'],
                'price' => (int) $item['price'],
                'is_package' => !empty($item['isPackage']) ? 1 : 0,
                'is_bundle' => !empty($item['isBundle']) ? 1 : 0
            ]);
        }
    }

    /**
     * Add payment methods
     */
    public static function add_payment_methods($transaction_id, $payment_methods) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods';
        
        foreach ($payment_methods as $payment) {
            $wpdb->insert($table, [
                'transaction_id' => $transaction_id,
                'category' => sanitize_text_field($payment['category']),
                'sub_category' => !empty($payment['subCategory']) ? sanitize_text_field($payment['subCategory']) : null,
                'method_name' => sanitize_text_field($payment['methodName']),
                'amount' => (int) $payment['amount']
            ]);
        }
    }

    /**
     * Update transaction status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_transactions';
        
        return $wpdb->update($table, ['status' => $status], ['id' => $id]);
    }

    /**
     * Format transaction data for API response
     */
    public static function format($transaction, $items = null, $payment_methods = null) {
        $result = [
            'id' => (int) $transaction->id,
            'user_id' => (int) $transaction->user_id,
            'outlet_id' => (int) $transaction->outlet_id,
            'outlet_name' => $transaction->outlet_name ?? null,
            'total' => (int) $transaction->total,
            'timestamp' => $transaction->timestamp
        ];

        if ($items !== null) {
            $trans_items = [];
            foreach ($items as $item) {
                $trans_items[] = [
                    'product_id' => (int) $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'price' => (int) $item->price,
                    'is_package' => (bool) $item->is_package,
                    'is_bundle' => (bool) $item->is_bundle
                ];
            }
            $result['items'] = $trans_items;
        }

        if ($payment_methods !== null) {
            $payments = [];
            foreach ($payment_methods as $payment) {
                $payments[] = [
                    'category' => $payment->category,
                    'sub_category' => $payment->sub_category,
                    'method_name' => $payment->method_name,
                    'amount' => (int) $payment->amount
                ];
            }
            $result['payment_methods'] = $payments;
        }

        return $result;
    }
}
