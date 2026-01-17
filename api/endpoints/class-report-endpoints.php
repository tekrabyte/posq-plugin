<?php
/**
 * Report Endpoints
 * 
 * Handles reporting endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Report_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/reports/top-outlets', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'top_outlets'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
        
        register_rest_route($namespace, '/reports/daily-summary', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'daily_summary'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
        
        register_rest_route($namespace, '/reports/overall-summary', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'overall_summary'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
        
        register_rest_route($namespace, '/reports/best-sellers', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'best_sellers'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
        
        register_rest_route($namespace, '/reports/cashflow', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'cashflow'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
    }
    
    public static function top_outlets() {
        global $wpdb;
        $results = $wpdb->get_results("
            SELECT outlet_id, SUM(total) as revenue
            FROM {$wpdb->prefix}posq_transactions
            GROUP BY outlet_id
            ORDER BY revenue DESC
            LIMIT 10
        ");
        
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'outlet_id' => (string) $row->outlet_id,
                'revenue' => (int) $row->revenue
            ];
        }
        return $data;
    }
    
    public static function daily_summary() {
        global $wpdb;
        $today = date('Y-m-d');
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT COUNT(*) as count, SUM(total) as revenue
            FROM {$wpdb->prefix}posq_transactions
            WHERE DATE(timestamp) = %s
        ", $today));
        
        return [
            'transaction_count' => (int) $result->count,
            'total_revenue' => (int) $result->revenue
        ];
    }
    
    public static function overall_summary() {
        global $wpdb;
        $result = $wpdb->get_row("
            SELECT COUNT(*) as count, SUM(total) as revenue
            FROM {$wpdb->prefix}posq_transactions
        ");
        
        return [
            'transaction_count' => (int) $result->count,
            'total_revenue' => (int) $result->revenue
        ];
    }
    
    public static function best_sellers() {
        global $wpdb;
        $results = $wpdb->get_results("
            SELECT product_id, SUM(quantity) as total_quantity
            FROM {$wpdb->prefix}posq_transaction_items
            WHERE is_package = 0 AND is_bundle = 0
            GROUP BY product_id
            ORDER BY total_quantity DESC
            LIMIT 10
        ");
        
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'product_id' => (string) $row->product_id,
                'quantity' => (int) $row->total_quantity
            ];
        }
        return $data;
    }
    
    public static function cashflow() {
        global $wpdb;
        $income_result = $wpdb->get_row("
            SELECT SUM(total) as total
            FROM {$wpdb->prefix}posq_transactions
            WHERE DATE(timestamp) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $expense_result = $wpdb->get_row("
            SELECT SUM(amount) as total
            FROM {$wpdb->prefix}posq_expenses
            WHERE DATE(date) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $total_income = (int) ($income_result->total ?? 0);
        $total_expense = (int) ($expense_result->total ?? 0);
        
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            
            $day_income = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(total) FROM {$wpdb->prefix}posq_transactions WHERE DATE(timestamp) = %s
            ", $date));
            
            $day_expense = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(amount) FROM {$wpdb->prefix}posq_expenses WHERE DATE(date) = %s
            ", $date));
            
            $chart_data[] = [
                'date' => $date,
                'income' => (int) ($day_income ?? 0),
                'expense' => (int) ($day_expense ?? 0)
            ];
        }
        
        return [
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'net_profit' => $total_income - $total_expense,
            'period' => 'monthly',
            'chart_data' => $chart_data
        ];
    }
}
