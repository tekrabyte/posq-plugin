<?php
/**
 * Helper Functions for POSQ Backend
 * 
 * @package POSQ_Backend
 * @version 3.1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Get plugin base path
 */
function posq_get_plugin_path() {
    return plugin_dir_path(dirname(__FILE__));
}

/**
 * Get plugin base URL
 */
function posq_get_plugin_url() {
    return plugin_dir_url(dirname(__FILE__));
}

/**
 * Sanitize array recursively
 */
function posq_sanitize_array($array) {
    if (!is_array($array)) {
        return sanitize_text_field($array);
    }
    
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = posq_sanitize_array($value);
        } else {
            $array[$key] = sanitize_text_field($value);
        }
    }
    
    return $array;
}

/**
 * Check if user is owner/admin
 */
function posq_is_owner($user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    $user = new WP_User($user_id);
    return in_array('administrator', (array) $user->roles);
}

/**
 * Get user role (owner, manager, cashier)
 */
function posq_get_user_role($user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    
    if (posq_is_owner($user_id)) {
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
 * Format user data for API response
 */
function posq_format_user_data($user) {
    global $wpdb;
    
    $profile = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
        $user->ID
    ));

    $role = posq_get_user_role($user->ID);

    return [
        'id' => (string) $user->ID,
        'name' => $profile ? $profile->name : $user->display_name,
        'email' => $user->user_email,
        'username' => $user->user_login,
        'role' => $role,
        'outletId' => $profile && $profile->outlet_id ? (string) $profile->outlet_id : null,
        'status' => 'active',
        'avatar' => get_avatar_url($user->ID),
        'is_admin' => posq_is_owner($user->ID)
    ];
}

/**
 * Log error to debug log
 */
function posq_log_error($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        error_log('[POSQ Backend] ' . $message);
        if ($data !== null) {
            error_log(print_r($data, true));
        }
    }
}
