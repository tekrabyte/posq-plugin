<?php
/**
 * Transaction Endpoints
 * 
 * Handles transaction endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Transaction_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/transactions', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_transactions'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_transaction'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ]
        ]);
    }
    
    public static function get_transactions() {
        $model = new POSQ_Transaction_Model();
        return $model->get_all();
    }
    
    public static function create_transaction($request) {
        $model = new POSQ_Transaction_Model();
        return $model->create($request);
    }
}
