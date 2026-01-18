<?php
/**
 * Bundle Model - Business logic for bundles
 */

if (!defined('ABSPATH')) exit;

class POSQ_Bundle_Model {

    /**
     * Get all bundles
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundles';
        
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY id DESC");
    }

    /**
     * Get bundle by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundles';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $id
        ));
    }

    /**
     * Get bundle items
     */
    public static function get_items($bundle_id) {
        global $wpdb;
        $items_table = $wpdb->prefix . 'posq_bundle_items';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT bi.*, p.name as product_name, pkg.name as package_name
            FROM $items_table bi
            LEFT JOIN {$wpdb->prefix}posq_products p ON bi.product_id = p.id
            LEFT JOIN {$wpdb->prefix}posq_packages pkg ON bi.package_id = pkg.id
            WHERE bi.bundle_id = %d",
            $bundle_id
        ));
    }

    /**
     * Create new bundle
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundles';
        
        // Handle outlet_id (0 for factory bundles)
        $outlet_id = 0;
        if (isset($data['outletId']) && $data['outletId'] !== '' && $data['outletId'] !== null && $data['outletId'] !== '0') {
            $outlet_id = (int) $data['outletId'];
        }
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'price' => (int) $data['price'],
            'outlet_id' => $outlet_id,
            'category_id' => !empty($data['categoryId']) && $data['categoryId'] !== 'none' ? (int) $data['categoryId'] : null,
            'is_active' => 1,
            'manual_stock_enabled' => !empty($data['manualStockEnabled']) ? 1 : 0,
            'manual_stock' => !empty($data['manualStock']) ? (int) $data['manualStock'] : null,
            'image_url' => !empty($data['image_url']) ? esc_url_raw($data['image_url']) : null
        ];

        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Add items to bundle
     */
    public static function add_items($bundle_id, $items) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundle_items';
        
        foreach ($items as $item) {
            $wpdb->insert($table, [
                'bundle_id' => $bundle_id,
                'product_id' => !empty($item['productId']) ? (int) $item['productId'] : null,
                'package_id' => !empty($item['packageId']) ? (int) $item['packageId'] : null,
                'quantity' => (int) $item['quantity'],
                'is_package' => !empty($item['isPackage']) ? 1 : 0
            ]);
        }
    }

    /**
     * Update bundle
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundles';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['price'])) $update_data['price'] = (int) $data['price'];
        
        if (isset($data['outletId'])) {
            if ($data['outletId'] === '' || $data['outletId'] === null || $data['outletId'] === '0') {
                $update_data['outlet_id'] = 0;
            } else {
                $update_data['outlet_id'] = (int) $data['outletId'];
            }
        }
        
        if (isset($data['categoryId'])) {
            $update_data['category_id'] = ($data['categoryId'] === 'none' || !$data['categoryId']) ? null : (int) $data['categoryId'];
        }
        
        if (isset($data['manualStockEnabled'])) {
            $update_data['manual_stock_enabled'] = !empty($data['manualStockEnabled']) ? 1 : 0;
            $update_data['manual_stock'] = $update_data['manual_stock_enabled'] && isset($data['manualStock']) 
                ? (int) $data['manualStock'] 
                : null;
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
     * Update bundle items
     */
    public static function update_items($bundle_id, $items) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_bundle_items';
        
        // Delete old items
        $wpdb->delete($table, ['bundle_id' => $bundle_id]);
        
        // Insert new items
        self::add_items($bundle_id, $items);
    }

    /**
     * Delete bundle
     */
    public static function delete($id) {
        global $wpdb;
        
        // Delete items first
        $wpdb->delete($wpdb->prefix . 'posq_bundle_items', ['bundle_id' => $id]);
        
        // Delete bundle
        return $wpdb->delete($wpdb->prefix . 'posq_bundles', ['id' => $id]);
    }

    /**
     * Format bundle data for API response
     */
    public static function format($bundle, $items = null) {
        $result = [
            'id' => (string) $bundle->id,
            'name' => $bundle->name,
            'price' => (int) $bundle->price,
            'outlet_id' => $bundle->outlet_id !== null ? (string) $bundle->outlet_id : '0',
            'category_id' => $bundle->category_id ? (string) $bundle->category_id : null,
            'created_at' => $bundle->created_at,
            'is_active' => (bool) $bundle->is_active,
            'manual_stock_enabled' => (bool) $bundle->manual_stock_enabled,
            'manual_stock' => $bundle->manual_stock !== null ? (int) $bundle->manual_stock : null,
            'image_url' => $bundle->image_url ?? null
        ];

        if ($items !== null) {
            $bundle_items = [];
            foreach ($items as $item) {
                $bundle_items[] = [
                    'product_id' => $item->product_id ? (int) $item->product_id : null,
                    'package_id' => $item->package_id ? (int) $item->package_id : null,
                    'quantity' => (int) $item->quantity,
                    'is_package' => (bool) $item->is_package,
                    'name' => $item->is_package ? $item->package_name : $item->product_name
                ];
            }
            $result['items'] = $bundle_items;
        }

        return $result;
    }
}
