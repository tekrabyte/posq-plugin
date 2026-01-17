<?php
/**
 * Stock Management Endpoints
 * 
 * Handles stock operations
 */

if (!defined('ABSPATH')) exit;

class POSQ_Stock_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/stock/add', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'add_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);
        
        register_rest_route($namespace, '/stock/reduce', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'reduce_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);
        
        register_rest_route($namespace, '/stock/transfer', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'transfer_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);
        
        register_rest_route($namespace, '/stock/logs', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_stock_logs'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
    }
    
    public static function add_stock($request) {
        $model = new POSQ_Stock_Model();
        return $model->add_stock($request);
    }
    
    public static function reduce_stock($request) {
        $model = new POSQ_Stock_Model();
        return $model->reduce_stock($request);
    }
    
    public static function transfer_stock($request) {
        $model = new POSQ_Stock_Model();
        return $model->transfer_stock($request);
    }
    
    public static function get_stock_logs() {
        $model = new POSQ_Stock_Model();
        return $model->get_logs();
    }
}
