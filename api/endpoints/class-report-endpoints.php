<?php
/**
 * Report Endpoints
 * Handles various business reports
 */

if (!defined('ABSPATH')) exit;

class POSQ_Report_Endpoints {

    public static function report_top_outlets($request) {
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

    public static function report_daily_summary($request) {
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

    public static function report_overall_summary($request) {
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

    public static function report_best_sellers($request) {
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

    public static function report_cashflow($request) {
        global $wpdb;
        
        $period = $request->get_param('period') ?: 'monthly';
        
        // Get income from transactions
        $income_result = $wpdb->get_row("
            SELECT SUM(total) as total
            FROM {$wpdb->prefix}posq_transactions
            WHERE DATE(timestamp) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        // Get expenses
        $expense_result = $wpdb->get_row("
            SELECT SUM(amount) as total
            FROM {$wpdb->prefix}posq_expenses
            WHERE DATE(date) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        $total_income = (int) ($income_result->total ?? 0);
        $total_expense = (int) ($expense_result->total ?? 0);

        // Chart data
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
            'period' => $period,
            'chart_data' => $chart_data
        ];
    }
}
