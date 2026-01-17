<?php
/**
 * Product Endpoints
 * 
 * Handles product management endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Product_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/products', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_products'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_product'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
        
        register_rest_route($namespace, '/products/search', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'search_products'],
            'permission_callback' => ['POSQ_Auth', 'check_auth']
        ]);
        
        register_rest_route($namespace, '/products/(?P<id>\\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_product'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'PUT',
                'callback' => [__CLASS__, 'update_product'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_product'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
    }
    
    public static function get_products() {
        $model = new POSQ_Product_Model();
        return $model->get_all();
    }
    
    public static function get_product($request) {
        $model = new POSQ_Product_Model();
        return $model->get_by_id($request);
    }
    
    public static function search_products($request) {
        $model = new POSQ_Product_Model();
        return $model->search($request);
    }
    
    public static function create_product($request) {
        $model = new POSQ_Product_Model();
        return $model->create($request);
    }
    
    public static function update_product($request) {
        $model = new POSQ_Product_Model();
        return $model->update($request);
    }
    
    public static function delete_product($request) {
        $model = new POSQ_Product_Model();
        return $model->delete($request);
    }
}
