<?php
/**
 * Cashflow Model - Business logic for cashflow categories
 */

if (!defined('ABSPATH')) exit;

class POSQ_Cashflow_Model {

    /**
     * Get all cashflow categories
     */
    public static function get_all($include_inactive = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        if ($include_inactive) {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        }
        
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY id DESC");
    }

    /**
     * Get cashflow category by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Get categories by type
     */
    public static function get_by_type($type) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE type = %s AND is_active = 1 ORDER BY id DESC",
            $type
        ));
    }

    /**
     * Get expense categories
     */
    public static function get_expense_categories() {
        return self::get_by_type('expense');
    }

    /**
     * Get income categories
     */
    public static function get_income_categories() {
        return self::get_by_type('income');
    }

    /**
     * Create new cashflow category
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'type' => sanitize_text_field($data['type']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        ];
        
        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update cashflow category
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['type'])) $update_data['type'] = sanitize_text_field($data['type']);
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete cashflow category
     */
    public static function delete($id) {
        global $wpdb;
        
        // Check if category is in use
        $in_use = self::is_in_use($id);
        if ($in_use) {
            return false; // Cannot delete if in use
        }
        
        $table = $wpdb->prefix . 'posq_cashflow_categories';
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Check if category is in use
     */
    public static function is_in_use($id) {
        global $wpdb;
        $category = self::get_by_id($id);
        
        if (!$category) {
            return false;
        }
        
        // Check in expenses table
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}posq_expenses WHERE category = %s",
            $category->name
        ));
        
        return $count > 0;
    }

    /**
     * Format cashflow category data for API response
     */
    public static function format($category) {
        return [
            'id' => (int) $category->id,
            'name' => $category->name,
            'type' => $category->type,
            'description' => $category->description,
            'created_at' => $category->created_at,
            'is_active' => (bool) $category->is_active
        ];
    }

    /**
     * Get cashflow summary by type and date range
     */
    public static function get_summary($type, $start_date = null, $end_date = null, $outlet_id = null) {
        global $wpdb;
        $expenses_table = $wpdb->prefix . 'posq_expenses';
        
        $where_clauses = ['type = %s'];
        $params = [$type];
        
        if ($start_date) {
            $where_clauses[] = 'date >= %s';
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where_clauses[] = 'date <= %s';
            $params[] = $end_date;
        }
        
        if ($outlet_id) {
            $where_clauses[] = 'outlet_id = %d';
            $params[] = $outlet_id;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        $query = "SELECT category, SUM(amount) as total, COUNT(*) as count 
                  FROM $expenses_table 
                  WHERE $where_sql 
                  GROUP BY category";
        
        return $wpdb->get_results($wpdb->prepare($query, ...$params));
    }
}
