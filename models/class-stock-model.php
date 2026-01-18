<?php
/**
 * Stock Model - Business logic for stock management
 */

if (!defined('ABSPATH')) exit;

class POSQ_Stock_Model {

    /**
     * Log stock change
     */
    public static function log_change($product_id, $outlet_id, $operation, $quantity, $from_outlet = null, $to_outlet = null, $transaction_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_stock_logs';
        
        return $wpdb->insert($table, [
            'product_id' => $product_id,
            'outlet_id' => $outlet_id,
            'operation' => $operation,
            'quantity' => $quantity,
            'from_outlet_id' => $from_outlet,
            'to_outlet_id' => $to_outlet,
            'user_id' => get_current_user_id(),
            'reference_transaction_id' => $transaction_id
        ]);
    }

    /**
     * Get stock logs
     */
    public static function get_logs($outlet_id = null, $limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_stock_logs';
        
        $query = "
            SELECT sl.*, p.name as product_name, o.name as outlet_name
            FROM $table sl
            LEFT JOIN {$wpdb->prefix}posq_products p ON sl.product_id = p.id
            LEFT JOIN {$wpdb->prefix}posq_outlets o ON sl.outlet_id = o.id
        ";

        if ($outlet_id) {
            $query .= $wpdb->prepare(" WHERE sl.outlet_id = %d", $outlet_id);
        }

        $query .= " ORDER BY sl.timestamp DESC LIMIT " . (int) $limit;

        return $wpdb->get_results($query);
    }

    /**
     * Add stock to product
     */
    public static function add_stock($product_id, $quantity) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $product_id
        ));

        if (!$product) {
            return false;
        }

        $new_stock = $product->stock + $quantity;
        $result = $wpdb->update($table, ['stock' => $new_stock], ['id' => $product_id]);

        if ($result !== false) {
            self::log_change($product_id, $product->outlet_id, 'add', $quantity);
            return true;
        }

        return false;
    }

    /**
     * Reduce stock from product
     */
    public static function reduce_stock($product_id, $quantity) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $product_id
        ));

        if (!$product) {
            return false;
        }

        if ($product->stock < $quantity) {
            return false; // Insufficient stock
        }

        $new_stock = $product->stock - $quantity;
        $result = $wpdb->update($table, ['stock' => $new_stock], ['id' => $product_id]);

        if ($result !== false) {
            self::log_change($product_id, $product->outlet_id, 'reduce', $quantity);
            return true;
        }

        return false;
    }

    /**
     * Transfer stock between outlets
     */
    public static function transfer_stock($product_id, $to_outlet_id, $quantity) {
        global $wpdb;
        $products_table = $wpdb->prefix . 'posq_products';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Get source product
            $product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $products_table WHERE id = %d AND is_deleted = 0",
                $product_id
            ));

            if (!$product || $product->stock < $quantity) {
                $wpdb->query('ROLLBACK');
                return false;
            }

            // Check if product exists in target outlet
            $target_product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $products_table 
                 WHERE name = %s AND outlet_id = %d AND is_deleted = 0",
                $product->name,
                $to_outlet_id
            ));

            if ($target_product) {
                // Update existing product
                $wpdb->update(
                    $products_table,
                    ['stock' => $target_product->stock + $quantity],
                    ['id' => $target_product->id]
                );
                
                self::log_change(
                    $target_product->id, 
                    $to_outlet_id, 
                    'transfer_in', 
                    $quantity,
                    $product->outlet_id,
                    $to_outlet_id
                );
            } else {
                // Create new product in target outlet
                $wpdb->insert($products_table, [
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $quantity,
                    'outlet_id' => $to_outlet_id,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'description' => $product->description,
                    'image_url' => $product->image_url,
                    'is_deleted' => 0
                ]);
                
                $new_product_id = $wpdb->insert_id;
                
                self::log_change(
                    $new_product_id, 
                    $to_outlet_id, 
                    'transfer_in', 
                    $quantity,
                    $product->outlet_id,
                    $to_outlet_id
                );
            }

            // Reduce stock from source product
            $wpdb->update(
                $products_table,
                ['stock' => $product->stock - $quantity],
                ['id' => $product->id]
            );

            self::log_change(
                $product->id, 
                $product->outlet_id, 
                'transfer_out', 
                $quantity,
                $product->outlet_id,
                $to_outlet_id
            );

            $wpdb->query('COMMIT');
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    /**
     * Format stock log for API response
     */
    public static function format($log) {
        return [
            'id' => (int) $log->id,
            'product_id' => (int) $log->product_id,
            'product_name' => $log->product_name,
            'outlet_id' => (int) $log->outlet_id,
            'outlet_name' => $log->outlet_name,
            'operation' => $log->operation,
            'quantity' => (int) $log->quantity,
            'from_outlet_id' => $log->from_outlet_id ? (int) $log->from_outlet_id : null,
            'to_outlet_id' => $log->to_outlet_id ? (int) $log->to_outlet_id : null,
            'user_id' => (int) $log->user_id,
            'timestamp' => $log->timestamp,
            'reference_transaction_id' => $log->reference_transaction_id ? (int) $log->reference_transaction_id : null
        ];
    }
}
