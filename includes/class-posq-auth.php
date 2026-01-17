<?php
/**
 * Authentication Handler
 * 
 * Manages user authentication, CORS, and token management
 */

if (!defined('ABSPATH')) exit;

class POSQ_Auth {
    
    /**
     * Handle CORS for API requests
     */
    public static function handle_cors() {
        $origin = get_http_origin();
        $allowed = [
            'http://localhost:3000',
            'http://192.168.1.7:3000',
            'http://localhost:5173',
            'http://localhost:5174',
            'https://erpos.tekrabyte.id',
        ];
        
        if ($origin && in_array($origin, $allowed, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Posq-Token");
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit;
        }
    }
    
    /**
     * Login endpoint
     */
    public static function login($request) {
        $data = $request->get_json_params();
        
        if (empty($data['username']) || empty($data['password'])) {
            return new WP_Error('bad_request', 'Missing credentials', ['status' => 400]);
        }
        
        $user = wp_authenticate($data['username'], $data['password']);
        
        if (is_wp_error($user)) {
            return new WP_Error('invalid_login', 'Invalid credentials', ['status' => 401]);
        }
        
        $token = bin2hex(random_bytes(32));
        update_user_meta($user->ID, 'posq_api_token', $token);
        
        return [
            'success' => true,
            'token' => $token,
            'user' => posq_format_user_data($user)
        ];
    }
    
    /**
     * Get current user info
     */
    public static function auth_me() {
        $user = wp_get_current_user();
        return [
            'success' => true,
            'user' => posq_format_user_data($user)
        ];
    }
    
    /**
     * Check if current user is admin
     */
    public static function is_admin() {
        return ['isAdmin' => POSQ_Permissions::is_owner()];
    }
    
    /**
     * Check authentication from request token
     */
    public static function check_auth($request) {
        $token = posq_get_token_from_request($request);
        if (!$token) return false;
        
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'posq_api_token' AND meta_value = %s LIMIT 1",
            $token
        ));
        
        if ($user_id) {
            wp_set_current_user($user_id);
            return true;
        }
        return false;
    }
}
