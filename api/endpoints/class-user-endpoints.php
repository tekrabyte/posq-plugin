<?php
/**
 * User Endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_User_Endpoints {

    public static function get_users() {
        $users = get_users();
        $data = [];
        foreach ($users as $user) {
            $data[] = posq_format_user_data($user);
        }
        return $data;
    }

    public static function create_user($request) {
        $data = $request->get_json_params();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return new WP_Error('missing_fields', 'Required fields missing', ['status' => 400]);
        }

        $user_id = wp_create_user($data['username'], $data['password'], $data['email']);
        
        if (is_wp_error($user_id)) {
            return new WP_Error('create_failed', $user_id->get_error_message(), ['status' => 500]);
        }

        // Create user profile
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'posq_user_profiles', [
            'user_id' => $user_id,
            'name' => !empty($data['name']) ? sanitize_text_field($data['name']) : $data['username'],
            'outlet_id' => !empty($data['outletId']) ? intval($data['outletId']) : null,
            'role' => !empty($data['role']) ? sanitize_text_field($data['role']) : 'cashier'
        ]);

        return ['success' => true, 'id' => $user_id];
    }

    public static function update_user($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        global $wpdb;
        
        $update_data = [];
        if (!empty($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['outletId'])) $update_data['outlet_id'] = $data['outletId'] ? intval($data['outletId']) : null;
        if (!empty($data['role'])) $update_data['role'] = sanitize_text_field($data['role']);

        if (!empty($update_data)) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}posq_user_profiles WHERE user_id = %d",
                $id
            ));

            if ($exists) {
                $wpdb->update(
                    $wpdb->prefix . 'posq_user_profiles',
                    $update_data,
                    ['user_id' => $id]
                );
            } else {
                $update_data['user_id'] = $id;
                if (!isset($update_data['name'])) $update_data['name'] = get_userdata($id)->display_name;
                if (!isset($update_data['role'])) $update_data['role'] = 'cashier';
                $wpdb->insert($wpdb->prefix . 'posq_user_profiles', $update_data);
            }
        }

        if (!empty($data['password'])) {
            wp_update_user(['ID' => $id, 'user_pass' => $data['password']]);
        }

        return ['success' => true];
    }

    public static function delete_user($request) {
        $id = (int) $request['id'];
        
        if ($id === get_current_user_id()) {
            return new WP_Error('delete_self', 'Cannot delete self', ['status' => 400]);
        }

        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'posq_user_profiles', ['user_id' => $id]);

        require_once(ABSPATH . 'wp-admin/includes/user.php');
        $result = wp_delete_user($id, get_current_user_id());

        return $result ? ['success' => true] : new WP_Error('failed', 'Delete failed');
    }
}
