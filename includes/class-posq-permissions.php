<?php
/**
 * POSQ Permissions & Authorization
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Permissions {

    /**
     * Check if user is owner (admin)
     */
    public static function is_owner($user_id = null) {
        if (!$user_id) $user_id = get_current_user_id();
        $user = new WP_User($user_id);
        return in_array('administrator', (array) $user->roles);
    }

    /**
     * Get user role (owner, manager, cashier)
     */
    public static function get_user_role($user_id = null) {
        if (!$user_id) $user_id = get_current_user_id();
        
        if (self::is_owner($user_id)) {
            return 'owner';
        }

        global $wpdb;
        $profile = $wpdb->get_row($wpdb->prepare(
            "SELECT role FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
            $user_id
        ));

        return $profile ? $profile->role : 'cashier';
    }

    /**
     * Check if user can access outlet
     */
    public static function can_access_outlet($outlet_id, $user_id = null) {
        if (!$user_id) $user_id = get_current_user_id();
        if (self::is_owner($user_id)) return true;

        global $wpdb;
        $profile = $wpdb->get_row($wpdb->prepare(
            "SELECT outlet_id FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
            $user_id
        ));

        return $profile && $profile->outlet_id == $outlet_id;
    }

    /**
     * Check if user has manager or owner role
     */
    public static function is_manager_or_owner($user_id = null) {
        $role = self::get_user_role($user_id);
        return in_array($role, ['owner', 'manager']);
    }

    /**
     * Check permission callback for REST API - Owner only
     */
    public static function check_owner_permission($request) {
        if (!POSQ_Auth::check_auth($request)) return false;
        return self::is_owner();
    }

    /**
     * Check permission callback for REST API - Manager or Owner
     */
    public static function check_manager_permission($request) {
        if (!POSQ_Auth::check_auth($request)) return false;
        return self::is_manager_or_owner();
    }
}
