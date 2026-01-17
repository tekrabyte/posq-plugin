<?php
/**
 * Expense Endpoints
 * 
 * Handles expense management endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Expense_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/expenses', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_expenses'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_expense'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ]
        ]);
        
        register_rest_route($namespace, '/expenses/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [__CLASS__, 'update_expense'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_expense'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ]
        ]);
    }
    
    public static function get_expenses() {
        $model = new POSQ_Expense_Model();
        return $model->get_all();
    }
    
    public static function create_expense($request) {
        $model = new POSQ_Expense_Model();
        return $model->create($request);
    }
    
    public static function update_expense($request) {
        $model = new POSQ_Expense_Model();
        return $model->update($request);
    }
    
    public static function delete_expense($request) {
        $model = new POSQ_Expense_Model();
        return $model->delete($request);
    }
}
