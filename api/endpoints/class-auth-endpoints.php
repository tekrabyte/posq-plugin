<?php
/**
 * Authentication Endpoints
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Auth_Endpoints {

    public static function register_routes($namespace) {
        register_rest_route($namespace, '/auth/login', [
            'methods' => 'POST',
            'callback' => [self::class, 'login'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route($namespace, '/auth/me', [
            'methods' => 'GET',
            'callback' => [self::class, 'auth_me'],
            'permission_callback' => [POSQ_Auth::class, 'check_auth']
        ]);

        register_rest_route($namespace, '/auth/is-admin', [
            'methods' => 'GET',
            'callback' => [self::class, 'is_admin'],
            'permission_callback' => [POSQ_Auth::class, 'check_auth']
        ]);
    }

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

    public static function auth_me() {
        $user = wp_get_current_user();
        return [
            'success' => true,
            'user' => posq_format_user_data($user)
        ];
    }

    public static function is_admin() {
        return ['isAdmin' => POSQ_Permissions::is_owner()];
    }
}
