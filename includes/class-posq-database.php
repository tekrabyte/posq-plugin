<?php
/**
 * Database Setup & Migrations
 */

if (!defined('ABSPATH')) exit;

class POSQ_Database {

    const DB_VERSION = '3.1.0';

    /**
     * Plugin Activation - Create all database tables
     */
    public static function activate() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create all tables from schema
        $schemas = POSQ_Schema::get_schemas();
        foreach ($schemas as $table_name => $sql) {
            dbDelta($sql);
        }

        // Run migrations for existing tables
        self::run_migrations();

        // Insert default data
        self::insert_default_menu_access();
        self::insert_default_payment_methods();

        update_option('posq_db_version', self::DB_VERSION);
    }

    /**
     * Run database migrations for existing installations
     */
    private static function run_migrations() {
        global $wpdb;

        // Migration: Add image_url to packages
        $packages_table = $wpdb->prefix . 'posq_packages';
        $packages_columns = $wpdb->get_col("DESCRIBE {$packages_table}");
        if (!in_array('image_url', $packages_columns)) {
            $wpdb->query("ALTER TABLE {$packages_table} ADD COLUMN image_url varchar(500) AFTER is_active");
        }
        
        // Migration: Add image_url to bundles
        $bundles_table = $wpdb->prefix . 'posq_bundles';
        $bundles_columns = $wpdb->get_col("DESCRIBE {$bundles_table}");
        if (!in_array('image_url', $bundles_columns)) {
            $wpdb->query("ALTER TABLE {$bundles_table} ADD COLUMN image_url varchar(500) AFTER manual_stock");
        }
        
        // Migration: Add category_id to packages
        if (!in_array('category_id', $packages_columns)) {
            $wpdb->query("ALTER TABLE {$packages_table} ADD COLUMN category_id bigint(20) UNSIGNED NULL AFTER outlet_id");
        }
        
        // Migration: Add category_id to bundles
        if (!in_array('category_id', $bundles_columns)) {
            $wpdb->query("ALTER TABLE {$bundles_table} ADD COLUMN category_id bigint(20) UNSIGNED NULL AFTER outlet_id");
        }

        // Migration: Add promo columns to products
        $products_table = $wpdb->prefix . 'posq_products';
        $products_columns = $wpdb->get_col("DESCRIBE {$products_table}");
        
        $promo_columns = [
            'promo_enabled' => 'tinyint(1) DEFAULT 0',
            'promo_type' => "varchar(20) DEFAULT 'fixed'",
            'promo_value' => 'decimal(10,2) DEFAULT 0',
            'promo_days' => 'text',
            'promo_start_time' => 'time',
            'promo_end_time' => 'time',
            'promo_start_date' => 'date',
            'promo_end_date' => 'date',
            'promo_min_purchase' => 'decimal(10,2)',
            'promo_description' => 'text',
            'applied_promo_id' => 'bigint(20) UNSIGNED NULL'
        ];

        foreach ($promo_columns as $column => $definition) {
            if (!in_array($column, $products_columns)) {
                $wpdb->query("ALTER TABLE {$products_table} ADD COLUMN {$column} {$definition}");
            }
        }

        // Add same promo columns to packages and bundles
        foreach ($promo_columns as $column => $definition) {
            if (!in_array($column, $packages_columns)) {
                $wpdb->query("ALTER TABLE {$packages_table} ADD COLUMN {$column} {$definition}");
            }
            if (!in_array($column, $bundles_columns)) {
                $wpdb->query("ALTER TABLE {$bundles_table} ADD COLUMN {$column} {$definition}");
            }
        }

        // Migration: Update expenses table
        $expenses_table = $wpdb->prefix . 'posq_expenses';
        $expenses_columns = $wpdb->get_col("DESCRIBE {$expenses_table}");
        
        if (!in_array('type', $expenses_columns)) {
            $wpdb->query("ALTER TABLE {$expenses_table} ADD COLUMN type varchar(20) DEFAULT 'expense' AFTER category");
        }
        if (!in_array('payment_method', $expenses_columns)) {
            $wpdb->query("ALTER TABLE {$expenses_table} ADD COLUMN payment_method varchar(100) AFTER type");
        }
        if (!in_array('image_url', $expenses_columns)) {
            $wpdb->query("ALTER TABLE {$expenses_table} ADD COLUMN image_url varchar(500) AFTER payment_method");
        }

        // Migration: Add order tracking fields to transactions
        $transactions_table = $wpdb->prefix . 'posq_transactions';
        $transactions_columns = $wpdb->get_col("DESCRIBE {$transactions_table}");
        
        $order_columns = [
            'order_type' => 'varchar(50)',
            'table_number' => 'varchar(50)',
            'customer_name' => 'varchar(255)',
            'estimated_ready_time' => 'int(11)',
            'notes' => 'text',
            'status' => "varchar(50) DEFAULT 'pending'"
        ];

        foreach ($order_columns as $column => $definition) {
            if (!in_array($column, $transactions_columns)) {
                $wpdb->query("ALTER TABLE {$transactions_table} ADD COLUMN {$column} {$definition}");
            }
        }

        // Migration: Allow NULL for outlet_id in bundles (factory bundles)
        $wpdb->query("ALTER TABLE {$wpdb->prefix}posq_bundles MODIFY COLUMN outlet_id bigint(20) UNSIGNED NULL");
    }

    /**
     * Insert default menu access configuration
     */
    private static function insert_default_menu_access() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';

        $defaults = [
            // Cashier
            ['cashier', 'dashboard', 1],
            ['cashier', 'pos', 1],
            ['cashier', 'products', 1],
            ['cashier', 'reports', 0],
            ['cashier', 'stock', 0],
            ['cashier', 'staff', 0],
            ['cashier', 'outlets', 0],
            ['cashier', 'categories', 0],
            ['cashier', 'settings', 0],
            // Manager
            ['manager', 'dashboard', 1],
            ['manager', 'pos', 0],
            ['manager', 'products', 1],
            ['manager', 'reports', 1],
            ['manager', 'stock', 1],
            ['manager', 'staff', 1],
            ['manager', 'outlets', 0],
            ['manager', 'categories', 1],
            ['manager', 'settings', 0],
            // Owner
            ['owner', 'dashboard', 1],
            ['owner', 'pos', 1],
            ['owner', 'products', 1],
            ['owner', 'reports', 1],
            ['owner', 'stock', 1],
            ['owner', 'staff', 1],
            ['owner', 'outlets', 1],
            ['owner', 'categories', 1],
            ['owner', 'settings', 1],
        ];

        foreach ($defaults as $row) {
            $wpdb->replace($table, [
                'role' => $row[0],
                'menu' => $row[1],
                'is_accessible' => $row[2]
            ]);
        }
    }

    /**
     * Insert default payment methods configuration
     */
    private static function insert_default_payment_methods() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_payment_methods_config';

        // Check if already populated
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;

        $defaults = [
            [
                'id' => 'cash',
                'name' => 'Cash',
                'category' => 'offline',
                'sub_category' => 'cash',
                'enabled' => 1,
                'icon' => 'Banknote',
                'color' => 'bg-green-500',
                'is_default' => 1,
                'fee' => 0,
                'fee_type' => 'flat',
                'config_data' => null
            ],
            [
                'id' => 'qris-static',
                'name' => 'QRIS Statis',
                'category' => 'online',
                'sub_category' => 'qris',
                'enabled' => 0,
                'icon' => 'QrCode',
                'color' => 'bg-blue-500',
                'is_default' => 1,
                'fee' => 0.7,
                'fee_type' => 'percentage',
                'config_data' => '{}'
            ],
            [
                'id' => 'bank-transfer',
                'name' => 'Transfer Bank',
                'category' => 'online',
                'sub_category' => 'transfer',
                'enabled' => 0,
                'icon' => 'Building2',
                'color' => 'bg-purple-500',
                'is_default' => 1,
                'fee' => 0,
                'fee_type' => 'flat',
                'config_data' => '{}'
            ],
            [
                'id' => 'debit',
                'name' => 'Kartu Debit',
                'category' => 'offline',
                'sub_category' => 'debit',
                'enabled' => 0,
                'icon' => 'CreditCard',
                'color' => 'bg-indigo-500',
                'is_default' => 1,
                'fee' => 1.5,
                'fee_type' => 'percentage',
                'config_data' => null
            ],
            [
                'id' => 'credit',
                'name' => 'Kartu Kredit',
                'category' => 'offline',
                'sub_category' => 'credit',
                'enabled' => 0,
                'icon' => 'CreditCard',
                'color' => 'bg-pink-500',
                'is_default' => 1,
                'fee' => 2.5,
                'fee_type' => 'percentage',
                'config_data' => null
            ],
            [
                'id' => 'ewallet',
                'name' => 'E-Wallet',
                'category' => 'online',
                'sub_category' => 'eWallet',
                'enabled' => 0,
                'icon' => 'Smartphone',
                'color' => 'bg-teal-500',
                'is_default' => 1,
                'fee' => 1.0,
                'fee_type' => 'percentage',
                'config_data' => null
            ],
            [
                'id' => 'gofood',
                'name' => 'GoFood',
                'category' => 'foodDelivery',
                'sub_category' => 'goFood',
                'enabled' => 0,
                'icon' => 'UtensilsCrossed',
                'color' => 'bg-green-600',
                'is_default' => 1,
                'fee' => 20,
                'fee_type' => 'percentage',
                'config_data' => null
            ],
            [
                'id' => 'grabfood',
                'name' => 'GrabFood',
                'category' => 'foodDelivery',
                'sub_category' => 'grabFood',
                'enabled' => 0,
                'icon' => 'UtensilsCrossed',
                'color' => 'bg-emerald-600',
                'is_default' => 1,
                'fee' => 20,
                'fee_type' => 'percentage',
                'config_data' => null
            ],
            [
                'id' => 'shopeefood',
                'name' => 'ShopeeFood',
                'category' => 'foodDelivery',
                'sub_category' => 'shopeeFood',
                'enabled' => 0,
                'icon' => 'UtensilsCrossed',
                'color' => 'bg-orange-600',
                'is_default' => 1,
                'fee' => 20,
                'fee_type' => 'percentage',
                'config_data' => null
            ]
        ];

        foreach ($defaults as $method) {
            $wpdb->replace($table, $method);
        }
    }
}
