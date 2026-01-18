<?php
/**
 * Product Model - Business logic for products
 */

if (!defined('ABSPATH')) exit;

class POSQ_Product_Model {

    /**
     * Get all products with category and brand info
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        return $wpdb->get_results("
            SELECT p.*, c.name as category_name, b.name as brand_name
            FROM $table p
            LEFT JOIN {$wpdb->prefix}posq_categories c ON p.category_id = c.id
            LEFT JOIN {$wpdb->prefix}posq_brands b ON p.brand_id = b.id
            WHERE p.is_deleted = 0
            ORDER BY p.id DESC
        ");
    }

    /**
     * Get product by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, c.name as category_name, b.name as brand_name
            FROM $table p
            LEFT JOIN {$wpdb->prefix}posq_categories c ON p.category_id = c.id
            LEFT JOIN {$wpdb->prefix}posq_brands b ON p.brand_id = b.id
            WHERE p.id = %d AND p.is_deleted = 0",
            $id
        ));
    }

    /**
     * Search products
     */
    public static function search($keyword = '', $outlet_id = null, $category_id = null, $brand_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        $where = ["p.is_deleted = 0"];
        $params = [];

        if (!empty($keyword)) {
            $where[] = "p.name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($keyword) . '%';
        }

        if ($outlet_id) {
            $where[] = "p.outlet_id = %d";
            $params[] = $outlet_id;
        }

        if ($category_id) {
            $where[] = "p.category_id = %d";
            $params[] = $category_id;
        }

        if ($brand_id) {
            $where[] = "p.brand_id = %d";
            $params[] = $brand_id;
        }

        $where_clause = implode(' AND ', $where);
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name
                  FROM $table p
                  LEFT JOIN {$wpdb->prefix}posq_categories c ON p.category_id = c.id
                  LEFT JOIN {$wpdb->prefix}posq_brands b ON p.brand_id = b.id
                  WHERE $where_clause
                  ORDER BY p.id DESC";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query);
    }

    /**
     * Create new product
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'price' => (int) $data['price'],
            'stock' => isset($data['stock']) ? (int) $data['stock'] : 0,
            'outlet_id' => (int) $data['outletId'],
            'category_id' => !empty($data['categoryId']) ? (int) $data['categoryId'] : null,
            'brand_id' => !empty($data['brandId']) ? (int) $data['brandId'] : null,
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'image_url' => esc_url_raw($data['image_url'] ?? ''),
            'is_deleted' => 0
        ];

        $result = $wpdb->insert($table, $insert_data);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update product
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        $update_data = [];
        
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['price'])) $update_data['price'] = (int) $data['price'];
        if (isset($data['stock'])) $update_data['stock'] = (int) $data['stock'];
        if (isset($data['outletId'])) $update_data['outlet_id'] = (int) $data['outletId'];
        if (isset($data['categoryId'])) $update_data['category_id'] = $data['categoryId'] ? (int) $data['categoryId'] : null;
        if (isset($data['brandId'])) $update_data['brand_id'] = $data['brandId'] ? (int) $data['brandId'] : null;
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['image_url'])) $update_data['image_url'] = esc_url_raw($data['image_url']);

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    /**
     * Delete product (soft delete)
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        return $wpdb->update($table, ['is_deleted' => 1], ['id' => $id]);
    }

    /**
     * Update stock
     */
    public static function update_stock($id, $new_stock) {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_products';
        
        return $wpdb->update($table, ['stock' => (int) $new_stock], ['id' => $id]);
    }

    /**
     * Format product data for API response
     */
    public static function format($product) {
        return [
            'id' => (string) $product->id,
            'name' => $product->name,
            'price' => (int) $product->price,
            'stock' => (int) $product->stock,
            'outlet_id' => (string) $product->outlet_id,
            'category_id' => $product->category_id ? (int) $product->category_id : null,
            'category_name' => $product->category_name ?? 'Uncategorized',
            'brand_id' => $product->brand_id ? (int) $product->brand_id : null,
            'brand_name' => $product->brand_name ?? null,
            'description' => $product->description,
            'image_url' => $product->image_url,
            'created_at' => $product->created_at,
            'is_active' => true
        ];
    }
}
