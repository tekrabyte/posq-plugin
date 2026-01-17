<?php
/**
 * POSQ Authentication Handler
 * 
 * @package POSQ_Backend
 * @version 3.1.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Auth {

    /**
     * Get token from request
     */
    public static function get_token_from_request($request) {
        $auth_header = $request->get_header('authorization');
        if ($auth_header && preg_match('/Bearer\s+(\S+)/i', $auth_header, $matches)) {
            return trim($matches[1]);
        }
        $x_token = $request->get_header('x-posq-token');
        return $x_token ? trim($x_token) : null;
    }

    /**
     * Check if user is authenticated
     */
    public static function check_auth($request) {
        $token = self::get_token_from_request($request);
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

    /**
     * Check if user is owner (admin)
     */
    public static function check_owner($request) {
        if (!self::check_auth($request)) return false;
        return posq_is_owner();
    }

    /**
     * Check if user is manager or owner
     */
    public static function check_manager($request) {
        if (!self::check_auth($request)) return false;
        $role = posq_get_user_role();
        return in_array($role, ['owner', 'manager']);
    }

    /**
     * Check if user can access outlet
     */
    public static function can_access_outlet($outlet_id) {
        $user_id = get_current_user_id();
        if (posq_is_owner($user_id)) return true;

        global $wpdb;
        $profile = $wpdb->get_row($wpdb->prepare(
            "SELECT outlet_id FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
            $user_id
        ));

        return $profile && $profile->outlet_id == $outlet_id;
    }
}
