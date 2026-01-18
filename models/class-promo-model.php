<?php
/**
 * Promo Model - Business logic for standalone promos
 */

if (!defined('ABSPATH')) exit;

class POSQ_Promo_Model {

    /**
     * Get all active promos
     */
    public static function get_all($include_inactive = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        if ($include_inactive) {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        }
        
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY created_at DESC");
    }

    /**
     * Get promo by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Create new promo
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'promo_type' => sanitize_text_field($data['promo_type'] ?? 'fixed'),
            'promo_value' => (float) ($data['promo_value'] ?? 0),
            'promo_days' => isset($data['promo_days']) ? json_encode($data['promo_days']) : '[]',
            'promo_start_time' => $data['promo_start_time'] ?? null,
            'promo_end_time' => $data['promo_end_time'] ?? null,
            'promo_start_date' => $data['promo_start_date'] ?? null,
            'promo_end_date' => $data['promo_end_date'] ?? null,
            'promo_min_purchase' => isset($data['promo_min_purchase']) ? (float) $data['promo_min_purchase'] : null,
            'promo_description' => sanitize_textarea_field($data['promo_description'] ?? ''),
            'is_active' => 1
        ];
        
        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update promo
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['promo_type'])) $update_data['promo_type'] = sanitize_text_field($data['promo_type']);
        if (isset($data['promo_value'])) $update_data['promo_value'] = (float) $data['promo_value'];
        if (isset($data['promo_days'])) $update_data['promo_days'] = json_encode($data['promo_days']);
        if (isset($data['promo_start_time'])) $update_data['promo_start_time'] = $data['promo_start_time'];
        if (isset($data['promo_end_time'])) $update_data['promo_end_time'] = $data['promo_end_time'];
        if (isset($data['promo_start_date'])) $update_data['promo_start_date'] = $data['promo_start_date'];
        if (isset($data['promo_end_date'])) $update_data['promo_end_date'] = $data['promo_end_date'];
        if (isset($data['promo_min_purchase'])) $update_data['promo_min_purchase'] = (float) $data['promo_min_purchase'];
        if (isset($data['promo_description'])) $update_data['promo_description'] = sanitize_textarea_field($data['promo_description']);
        if (isset($data['is_active'])) $update_data['is_active'] = (int) $data['is_active'];
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete promo (soft delete)
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        return $wpdb->update($table, ['is_active' => 0], ['id' => $id]);
    }

    /**
     * Hard delete promo
     */
    public static function hard_delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Check if promo is currently active
     */
    public static function is_promo_active($promo, $current_date = null, $current_time = null, $current_day = null) {
        if (!$promo->is_active) {
            return false;
        }
        
        $now = current_time('timestamp');
        $current_date = $current_date ?: date('Y-m-d', $now);
        $current_time = $current_time ?: date('H:i:s', $now);
        $current_day = $current_day ?: strtolower(date('l', $now));
        
        // Check date range
        if ($promo->promo_start_date && $current_date < $promo->promo_start_date) {
            return false;
        }
        if ($promo->promo_end_date && $current_date > $promo->promo_end_date) {
            return false;
        }
        
        // Check time range
        if ($promo->promo_start_time && $current_time < $promo->promo_start_time) {
            return false;
        }
        if ($promo->promo_end_time && $current_time > $promo->promo_end_time) {
            return false;
        }
        
        // Check days
        if ($promo->promo_days) {
            $allowed_days = json_decode($promo->promo_days, true);
            if (!empty($allowed_days) && !in_array($current_day, $allowed_days)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get active promos for current time
     */
    public static function get_active_promos() {
        $all_promos = self::get_all();
        $active_promos = [];
        
        foreach ($all_promos as $promo) {
            if (self::is_promo_active($promo)) {
                $active_promos[] = $promo;
            }
        }
        
        return $active_promos;
    }

    /**
     * Calculate discount amount
     */
    public static function calculate_discount($promo, $amount) {
        if (!self::is_promo_active($promo)) {
            return 0;
        }
        
        // Check minimum purchase
        if ($promo->promo_min_purchase && $amount < $promo->promo_min_purchase) {
            return 0;
        }
        
        if ($promo->promo_type === 'percentage') {
            return ($amount * $promo->promo_value) / 100;
        }
        
        return (float) $promo->promo_value;
    }

    /**
     * Format promo data for API response
     */
    public static function format($promo) {
        return [
            'id' => (string) $promo->id,
            'name' => $promo->name,
            'promoType' => $promo->promo_type,
            'promoValue' => (float) $promo->promo_value,
            'promoDays' => json_decode($promo->promo_days ?: '[]'),
            'promoStartTime' => $promo->promo_start_time,
            'promoEndTime' => $promo->promo_end_time,
            'promoStartDate' => $promo->promo_start_date,
            'promoEndDate' => $promo->promo_end_date,
            'promoMinPurchase' => $promo->promo_min_purchase ? (float) $promo->promo_min_purchase : null,
            'promoDescription' => $promo->promo_description,
            'isActive' => (bool) $promo->is_active,
            'createdAt' => $promo->created_at
        ];
    }
}
