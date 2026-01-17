<?php
/**
 * Package Endpoints
 * 
 * Handles package management endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Package_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/packages', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_packages'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_package'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
        
        register_rest_route($namespace, '/packages/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [__CLASS__, 'update_package'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_package'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
    }
    
    public static function get_packages() {
        $model = new POSQ_Package_Model();
        return $model->get_all();
    }
    
    public static function create_package($request) {
        $model = new POSQ_Package_Model();
        return $model->create($request);
    }
    
    public static function update_package($request) {
        $model = new POSQ_Package_Model();
        return $model->update($request);
    }
    
    public static function delete_package($request) {
        $model = new POSQ_Package_Model();
        return $model->delete($request);
    }
}
