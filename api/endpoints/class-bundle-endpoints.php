<?php
/**
 * Bundle Endpoints
 * 
 * Handles bundle management endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Bundle_Endpoints {
    
    public static function register_routes($namespace) {
        register_rest_route($namespace, '/bundles', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_bundles'],
                'permission_callback' => ['POSQ_Auth', 'check_auth']
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_bundle'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
        
        register_rest_route($namespace, '/bundles/(?P<id>\\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [__CLASS__, 'update_bundle'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_bundle'],
                'permission_callback' => ['POSQ_Permissions', 'check_owner']
            ]
        ]);
    }
    
    public static function get_bundles() {
        $model = new POSQ_Bundle_Model();
        return $model->get_all();
    }
    
    public static function create_bundle($request) {
        $model = new POSQ_Bundle_Model();
        return $model->create($request);
    }
    
    public static function update_bundle($request) {
        $model = new POSQ_Bundle_Model();
        return $model->update($request);
    }
    
    public static function delete_bundle($request) {
        $model = new POSQ_Bundle_Model();
        return $model->delete($request);
    }
}
