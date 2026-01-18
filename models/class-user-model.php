<?php
/**
 * User Model - Business logic for user management
 */

if (!defined('ABSPATH')) exit;

class POSQ_User_Model {

    /**
     * Get all users with profiles
     */
    public static function get_all() {
        global $wpdb;
        
        $users = $wpdb->get_results("
            SELECT u.ID, u.user_login, u.user_email, u.user_registered,
                   p.name, p.outlet_id, p.role,
                   o.name as outlet_name
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->prefix}posq_user_profiles p ON u.ID = p.user_id
            LEFT JOIN {$wpdb->prefix}posq_outlets o ON p.outlet_id = o.id
            ORDER BY u.ID DESC
        ");
        
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => (int) $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'name' => $user->name,
                'outletId' => $user->outlet_id ? (int) $user->outlet_id : null,
                'outletName' => $user->outlet_name,
                'role' => $user->role ?: 'cashier',
                'registered' => $user->user_registered
            ];
        }
        
        return $result;
    }

    /**
     * Get user by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        
        $user = $wpdb->get_row($wpdb->prepare("
            SELECT u.ID, u.user_login, u.user_email, u.user_registered,
                   p.name, p.outlet_id, p.role,
                   o.name as outlet_name
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->prefix}posq_user_profiles p ON u.ID = p.user_id
            LEFT JOIN {$wpdb->prefix}posq_outlets o ON p.outlet_id = o.id
            WHERE u.ID = %d
        ", $id));
        
        if (!$user) return null;
        
        return [
            'id' => (int) $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'name' => $user->name,
            'outletId' => $user->outlet_id ? (int) $user->outlet_id : null,
            'outletName' => $user->outlet_name,
            'role' => $user->role ?: 'cashier',
            'registered' => $user->user_registered
        ];
    }

    /**
     * Create new user
     */
    public static function create($request) {
        $data = $request->get_json_params();
        
        if (empty($data['username']) || empty($data['password']) || empty($data['name'])) {
            return new WP_Error('missing_fields', 'Username, password, and name are required', ['status' => 400]);
        }
        
        // Check if username exists
        if (username_exists($data['username'])) {
            return new WP_Error('username_exists', 'Username already exists', ['status' => 400]);
        }
        
        // Create WordPress user
        $user_id = wp_create_user(
            sanitize_text_field($data['username']),
            $data['password'],
            sanitize_email($data['email'] ?? '')
        );
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Create user profile
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_user_profiles', [
            'user_id' => $user_id,
            'name' => sanitize_text_field($data['name']),
            'outlet_id' => !empty($data['outletId']) ? (int) $data['outletId'] : null,
            'role' => !empty($data['role']) ? sanitize_text_field($data['role']) : 'cashier'
        ]);
        
        return [
            'success' => true,
            'id' => $user_id
        ];
    }

    /**
     * Update user
     */
    public static function update($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();
        
        global $wpdb;
        
        // Update WordPress user if email provided
        if (!empty($data['email'])) {
            wp_update_user([
                'ID' => $id,
                'user_email' => sanitize_email($data['email'])
            ]);
        }
        
        // Update password if provided
        if (!empty($data['password'])) {
            wp_set_password($data['password'], $id);
        }
        
        // Update profile
        $update_data = [];
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['outletId'])) $update_data['outlet_id'] = $data['outletId'] ? (int) $data['outletId'] : null;
        if (isset($data['role'])) $update_data['role'] = sanitize_text_field($data['role']);
        
        if (!empty($update_data)) {
            $wpdb->update(
                $wpdb->prefix . 'posq_user_profiles',
                $update_data,
                ['user_id' => $id]
            );
        }
        
        return ['success' => true];
    }

    /**
     * Delete user
     */
    public static function delete($request) {
        $id = (int) $request['id'];
        
        // Don't allow deleting current user
        if ($id === get_current_user_id()) {
            return new WP_Error('cannot_delete_self', 'Cannot delete your own account', ['status' => 400]);
        }
        
        global $wpdb;
        
        // Delete profile first
        $wpdb->delete($wpdb->prefix . 'posq_user_profiles', ['user_id' => $id]);
        
        // Delete WordPress user
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        $result = wp_delete_user($id);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete user', ['status' => 500]);
        }
        
        return ['success' => true];
    }

    /**
     * Get user profile
     */
    public static function get_profile($user_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
            $user_id
        ));
    }

    /**
     * Get user role
     */
    public static function get_role($user_id) {
        $profile = self::get_profile($user_id);
        return $profile ? $profile->role : 'cashier';
    }
}
