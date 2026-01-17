<?php
/**
 * Database Management
 * 
 * Handles database setup, migrations, and schema updates
 */

if (!defined('ABSPATH')) exit;

class POSQ_Database {
    
    const DB_VERSION = '4.0.0';
    
    /**
     * Plugin activation - Create all database tables
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Load schema definitions
        $schemas = require POSQ_PLUGIN_DIR . 'config/database-schema.php';
        
        // Create tables
        foreach ($schemas as $table_name => $sql) {
            $sql = str_replace('{prefix}', $wpdb->prefix, $sql);
            $sql = str_replace('{charset_collate}', $charset_collate, $sql);
            dbDelta($sql);
        }
        
        // Run migrations
        self::run_migrations();
        
        // Insert default data
        self::insert_default_menu_access();
        self::insert_default_payment_methods();
        
        // Update DB version
        update_option('posq_db_version', self::DB_VERSION);
    }
    
    /**
     * Run database migrations for existing tables
     */
    private static function run_migrations() {
        global $wpdb;
        
        // Migration: Add columns if they don't exist
        $migrations = [
            'posq_packages' => ['image_url', 'category_id', 'promo_enabled', 'applied_promo_id'],
            'posq_bundles' => ['image_url', 'category_id', 'promo_enabled', 'applied_promo_id'],
            'posq_products' => ['promo_enabled', 'promo_type', 'promo_value', 'applied_promo_id'],
            'posq_expenses' => ['type', 'payment_method', 'image_url'],
            'posq_transactions' => ['order_type', 'table_number', 'customer_name', 'status']
        ];
        
        foreach ($migrations as $table => $columns) {
            $table_name = $wpdb->prefix . $table;
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            if (!$table_exists) continue;
            
            $existing_columns = $wpdb->get_col("DESCRIBE {$table_name}");
            
            foreach ($columns as $column) {
                if (!in_array($column, $existing_columns)) {
                    self::add_missing_column($table_name, $column);
                }
            }
        }
    }
    
    /**
     * Add missing column based on column name
     */
    private static function add_missing_column($table, $column) {
        global $wpdb;
        
        $column_definitions = [
            'image_url' => 'varchar(500)',
            'category_id' => 'bigint(20) UNSIGNED NULL',
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
            'applied_promo_id' => 'bigint(20) UNSIGNED NULL',
            'type' => "varchar(20) DEFAULT 'expense'",
            'payment_method' => 'varchar(100)',
            'order_type' => 'varchar(50)',
            'table_number' => 'varchar(50)',
            'customer_name' => 'varchar(255)',
            'estimated_ready_time' => 'int(11)',
            'notes' => 'text',
            'status' => "varchar(50) DEFAULT 'pending'"
        ];
        
        if (isset($column_definitions[$column])) {
            $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$column_definitions[$column]}";
            $wpdb->query($sql);
        }
    }
    
    /**
     * Insert default menu access configuration
     */
    private static function insert_default_menu_access() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';
        
        // Check if already populated
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;
        
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
     * Insert default payment methods
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
            ]
        ];
        
        foreach ($defaults as $method) {
            $wpdb->replace($table, $method);
        }
    }
}
