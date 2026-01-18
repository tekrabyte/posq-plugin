<?php
/**
 * Permission & Authorization Handler
 */

if (!defined('ABSPATH')) exit;

class POSQ_Permissions {

    /**
     * Check authentication
     */
    public static function check_auth($request) {
        $token = POSQ_Auth::get_token_from_request($request);
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
     * Check if user is owner
     */
    public static function check_owner($request) {
        if (!self::check_auth($request)) return false;
        return posq_is_owner();
    }

    /**
     * Check if user is manager or above
     */
    public static function check_manager($request) {
        if (!self::check_auth($request)) return false;
        $role = posq_get_user_role();
        return in_array($role, ['owner', 'manager']);
    }
}
