<?php
/**
 * Helper Functions
 */

if (!defined('ABSPATH')) exit;

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
 * Check if user is owner/administrator
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
 * Check if user can access outlet
 */
function posq_can_access_outlet($outlet_id) {
    $user_id = get_current_user_id();
    if (posq_is_owner($user_id)) return true;

    global $wpdb;
    $profile = $wpdb->get_row($wpdb->prepare(
        "SELECT outlet_id FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
        $user_id
    ));

    return $profile && $profile->outlet_id == $outlet_id;
}

/**
 * Log stock change
 */
function posq_log_stock_change($product_id, $outlet_id, $operation, $quantity, $from_outlet = null, $to_outlet = null, $transaction_id = null) {
    global $wpdb;
    
    $wpdb->insert($wpdb->prefix . 'posq_stock_logs', [
        'product_id' => $product_id,
        'outlet_id' => $outlet_id,
        'operation' => $operation,
        'quantity' => $quantity,
        'from_outlet_id' => $from_outlet,
        'to_outlet_id' => $to_outlet,
        'user_id' => get_current_user_id(),
        'reference_transaction_id' => $transaction_id
    ]);
}

/**
 * Crop image to 1:1 square ratio
 */
function posq_crop_image_to_square($file_path) {
    $image_editor = wp_get_image_editor($file_path);
    
    if (is_wp_error($image_editor)) {
        return $image_editor;
    }

    $size = $image_editor->get_size();
    $width = $size['width'];
    $height = $size['height'];

    // Calculate crop dimensions for 1:1 ratio
    $new_size = min($width, $height);
    $x = ($width - $new_size) / 2;
    $y = ($height - $new_size) / 2;

    // Crop to square
    $image_editor->crop($x, $y, $new_size, $new_size);

    // Save cropped image
    $saved = $image_editor->save($file_path);
    
    if (is_wp_error($saved)) {
        return $saved;
    }

    return $saved['path'];
}
