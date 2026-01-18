<?php
/**
 * Package Model - Business logic for packages
 */

if (!defined('ABSPATH')) exit;

class POSQ_Package_Model {

    /**
     * Get all packages with components
     */
    public static function get_all() {
        global $wpdb;
        $packages_table = $wpdb->prefix . 'posq_packages';
        
        return $wpdb->get_results("SELECT * FROM $packages_table WHERE is_active = 1 ORDER BY id DESC");
    }

    /**
     * Get package by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_packages';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $id
        ));
    }

    /**
     * Get package components
     */
    public static function get_components($package_id) {
        global $wpdb;
        $components_table = $wpdb->prefix . 'posq_package_components';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT pc.*, p.name as product_name
            FROM $components_table pc
            LEFT JOIN {$wpdb->prefix}posq_products p ON pc.product_id = p.id
            WHERE pc.package_id = %d",
            $package_id
        ));
    }

    /**
     * Create new package
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_packages';
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'price' => (int) $data['price'],
            'outlet_id' => (int) $data['outletId'],
            'category_id' => !empty($data['categoryId']) && $data['categoryId'] !== 'none' ? (int) $data['categoryId'] : null,
            'is_active' => 1,
            'image_url' => !empty($data['image_url']) ? esc_url_raw($data['image_url']) : null
        ];

        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Add components to package
     */
    public static function add_components($package_id, $components) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_package_components';
        
        foreach ($components as $comp) {
            $wpdb->insert($table, [
                'package_id' => $package_id,
                'product_id' => (int) $comp['productId'],
                'quantity' => (int) $comp['quantity']
            ]);
        }
    }

    /**
     * Update package
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_packages';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['price'])) $update_data['price'] = (int) $data['price'];
        if (isset($data['categoryId'])) {
            $update_data['category_id'] = ($data['categoryId'] === 'none' || !$data['categoryId']) ? null : (int) $data['categoryId'];
        }
        if (isset($data['image_url'])) {
            $update_data['image_url'] = !empty($data['image_url']) ? esc_url_raw($data['image_url']) : null;
        }

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Update package components
     */
    public static function update_components($package_id, $components) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_package_components';
        
        // Delete old components
        $wpdb->delete($table, ['package_id' => $package_id]);
        
        // Insert new components
        self::add_components($package_id, $components);
    }

    /**
     * Delete package
     */
    public static function delete($id) {
        global $wpdb;
        
        // Delete components first
        $wpdb->delete($wpdb->prefix . 'posq_package_components', ['package_id' => $id]);
        
        // Delete package
        return $wpdb->delete($wpdb->prefix . 'posq_packages', ['id' => $id]);
    }

    /**
     * Format package data for API response
     */
    public static function format($package, $components = null) {
        $result = [
            'id' => (string) $package->id,
            'name' => $package->name,
            'price' => (int) $package->price,
            'outlet_id' => (string) $package->outlet_id,
            'category_id' => $package->category_id ? (string) $package->category_id : null,
            'created_at' => $package->created_at,
            'is_active' => (bool) $package->is_active,
            'image_url' => $package->image_url ?? null
        ];

        if ($components !== null) {
            $items = [];
            foreach ($components as $comp) {
                $items[] = [
                    'product_id' => (int) $comp->product_id,
                    'product_name' => $comp->product_name,
                    'quantity' => (int) $comp->quantity
                ];
            }
            $result['components'] = $items;
        }

        return $result;
    }
}
