<?php
/**
 * Promo Endpoints - Handle standalone promotions
 */

if (!defined('ABSPATH')) exit;

class POSQ_Promo_Endpoints {

    /**
     * Get all standalone promos
     */
    public static function get_standalone_promos() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $promos = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY created_at DESC");
        
        $result = [];
        foreach ($promos as $promo) {
            $result[] = [
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
        
        return $result;
    }

    /**
     * Create standalone promo
     */
    public static function create_standalone_promo($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $params = $request->get_json_params();
        
        $wpdb->insert($table, [
            'name' => sanitize_text_field($params['name']),
            'promo_type' => sanitize_text_field($params['promo_type'] ?? 'fixed'),
            'promo_value' => floatval($params['promo_value'] ?? 0),
            'promo_days' => $params['promo_days'] ?? '[]',
            'promo_start_time' => $params['promo_start_time'] ?? null,
            'promo_end_time' => $params['promo_end_time'] ?? null,
            'promo_start_date' => $params['promo_start_date'] ?? null,
            'promo_end_date' => $params['promo_end_date'] ?? null,
            'promo_min_purchase' => isset($params['promo_min_purchase']) ? floatval($params['promo_min_purchase']) : null,
            'promo_description' => sanitize_textarea_field($params['promo_description'] ?? ''),
            'is_active' => 1
        ]);
        
        return ['success' => true, 'id' => (string) $wpdb->insert_id];
    }

    /**
     * Update standalone promo
     */
    public static function update_standalone_promo($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $id = $request['id'];
        $params = $request->get_json_params();
        
        $wpdb->update($table, [
            'name' => sanitize_text_field($params['name']),
            'promo_type' => sanitize_text_field($params['promo_type'] ?? 'fixed'),
            'promo_value' => floatval($params['promo_value'] ?? 0),
            'promo_days' => $params['promo_days'] ?? '[]',
            'promo_start_time' => $params['promo_start_time'] ?? null,
            'promo_end_time' => $params['promo_end_time'] ?? null,
            'promo_start_date' => $params['promo_start_date'] ?? null,
            'promo_end_date' => $params['promo_end_date'] ?? null,
            'promo_min_purchase' => isset($params['promo_min_purchase']) ? floatval($params['promo_min_purchase']) : null,
            'promo_description' => sanitize_textarea_field($params['promo_description'] ?? ''),
        ], ['id' => $id]);
        
        return ['success' => true, 'id' => (string) $id];
    }

    /**
     * Delete standalone promo (soft delete)
     */
    public static function delete_standalone_promo($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_standalone_promos';
        
        $id = $request['id'];
        
        $wpdb->update($table, ['is_active' => 0], ['id' => $id]);
        
        return ['success' => true, 'id' => (string) $id];
    }
}
