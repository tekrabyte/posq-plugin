<?php
/**
 * Expense Model - Business logic for expenses/income
 */

if (!defined('ABSPATH')) exit;

class POSQ_Expense_Model {

    /**
     * Get all expenses
     */
    public static function get_all($outlet_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_expenses';
        
        $query = "
            SELECT e.*, o.name as outlet_name
            FROM $table e
            LEFT JOIN {$wpdb->prefix}posq_outlets o ON e.outlet_id = o.id
        ";

        if ($outlet_id) {
            $query .= $wpdb->prepare(" WHERE e.outlet_id = %d", $outlet_id);
        }

        $query .= " ORDER BY e.date DESC";

        return $wpdb->get_results($query);
    }

    /**
     * Get expense by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_expenses';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Create new expense
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_expenses';
        
        $insert_data = [
            'title' => sanitize_text_field($data['title']),
            'amount' => (int) $data['amount'],
            'category' => sanitize_text_field($data['category'] ?? ''),
            'type' => sanitize_text_field($data['type'] ?? 'expense'),
            'outlet_id' => (int) $data['outletId'],
            'note' => sanitize_textarea_field($data['note'] ?? $data['description'] ?? '')
        ];
        
        // Handle timestamp
        if (!empty($data['timestamp'])) {
            $timestamp_seconds = (int)($data['timestamp'] / 1000);
            $insert_data['date'] = date('Y-m-d H:i:s', $timestamp_seconds);
        }
        
        if (!empty($data['paymentMethod'])) {
            $insert_data['payment_method'] = sanitize_text_field($data['paymentMethod']);
        }
        
        if (!empty($data['imageUrl'])) {
            $insert_data['image_url'] = esc_url_raw($data['imageUrl']);
        }
        
        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update expense
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_expenses';
        
        $update_data = [];
        
        if (isset($data['title'])) $update_data['title'] = sanitize_text_field($data['title']);
        if (isset($data['amount'])) $update_data['amount'] = (int) $data['amount'];
        if (isset($data['category'])) $update_data['category'] = sanitize_text_field($data['category']);
        if (isset($data['type'])) $update_data['type'] = sanitize_text_field($data['type']);
        if (isset($data['paymentMethod'])) $update_data['payment_method'] = sanitize_text_field($data['paymentMethod']);
        if (isset($data['imageUrl'])) $update_data['image_url'] = esc_url_raw($data['imageUrl']);
        if (isset($data['note'])) $update_data['note'] = sanitize_textarea_field($data['note']);
        if (isset($data['description'])) $update_data['note'] = sanitize_textarea_field($data['description']);
        
        // Handle timestamp
        if (!empty($data['timestamp'])) {
            $timestamp_seconds = (int)($data['timestamp'] / 1000);
            $update_data['date'] = date('Y-m-d H:i:s', $timestamp_seconds);
        }

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete expense
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_expenses';
        
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Format expense data for API response
     */
    public static function format($expense) {
        return [
            'id' => (string) $expense->id,
            'title' => $expense->title,
            'amount' => (int) $expense->amount,
            'category' => $expense->category,
            'type' => $expense->type ?? 'expense',
            'payment_method' => $expense->payment_method ?? null,
            'image_url' => $expense->image_url ?? null,
            'outlet_id' => (string) $expense->outlet_id,
            'outlet_name' => $expense->outlet_name ?? null,
            'date' => strtotime($expense->date) * 1000,
            'note' => $expense->note,
            'timestamp' => strtotime($expense->date) * 1000
        ];
    }
}
