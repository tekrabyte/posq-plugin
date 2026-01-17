<?php
/**
 * POSQ Database Management
 * 
 * @package POSQ_Backend
 * @version 3.1.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_Database {

    const DB_VERSION = '3.1.0';

    /**
     * Plugin Activation - Create all database tables
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Add missing columns to existing tables
        self::migrate_existing_tables();

        // Create all tables
        self::create_outlets_table($charset_collate);
        self::create_categories_table($charset_collate);
        self::create_brands_table($charset_collate);
        self::create_products_table($charset_collate);
        self::create_packages_table($charset_collate);
        self::create_package_components_table($charset_collate);
        self::create_bundles_table($charset_collate);
        self::create_bundle_items_table($charset_collate);
        self::create_transactions_table($charset_collate);
        self::create_transaction_items_table($charset_collate);
        self::create_payment_methods_table($charset_collate);
        self::create_stock_logs_table($charset_collate);
        self::create_expenses_table($charset_collate);
        self::create_cashflow_categories_table($charset_collate);
        self::create_user_profiles_table($charset_collate);
        self::create_customers_table($charset_collate);
        self::create_menu_access_table($charset_collate);
        self::create_payment_methods_config_table($charset_collate);
        self::create_standalone_promos_table($charset_collate);
        self::create_held_orders_table($charset_collate);
        self::create_kitchen_orders_table($charset_collate);

        // Insert default data
        self::insert_default_menu_access();
        self::insert_default_payment_methods();

        update_option('posq_db_version', self::DB_VERSION);
    }

    /**
     * Migrate existing tables - add missing columns
     */
    private static function migrate_existing_tables() {
        global $wpdb;

        $tables_to_check = [
            'posq_packages' => ['image_url', 'category_id'],
            'posq_bundles' => ['image_url', 'category_id'],
            'posq_products' => ['category_id'],
            'posq_expenses' => ['type', 'payment_method', 'image_url'],
            'posq_transactions' => ['order_type', 'table_number', 'customer_name', 'estimated_ready_time', 'notes', 'status']
        ];

        foreach ($tables_to_check as $table_name => $columns) {
            $table = $wpdb->prefix . $table_name;
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
                continue;
            }

            $existing_columns = $wpdb->get_col("DESCRIBE {$table}");

            foreach ($columns as $column) {
                if (!in_array($column, $existing_columns)) {
                    self::add_missing_column($table, $column);
                }
            }
        }

        // Add promo columns to products, packages, bundles
        self::add_promo_columns('posq_products');
        self::add_promo_columns('posq_packages');
        self::add_promo_columns('posq_bundles');
    }

    /**
     * Add missing column based on name
     */
    private static function add_missing_column($table, $column) {
        global $wpdb;

        $column_definitions = [
            'image_url' => 'varchar(500)',
            'category_id' => 'bigint(20) UNSIGNED NULL',
            'type' => 'varchar(20) DEFAULT \'expense\'',
            'payment_method' => 'varchar(100)',
            'order_type' => 'varchar(50)',
            'table_number' => 'varchar(50)',
            'customer_name' => 'varchar(255)',
            'estimated_ready_time' => 'int(11)',
            'notes' => 'text',
            'status' => 'varchar(50) DEFAULT \'pending\''
        ];

        if (isset($column_definitions[$column])) {
            $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$column} {$column_definitions[$column]}");
        }
    }

    /**
     * Add promo columns to a table
     */
    private static function add_promo_columns($table_name) {
        global $wpdb;
        $table = $wpdb->prefix . $table_name;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
            return;
        }

        $columns = $wpdb->get_col("DESCRIBE {$table}");
        
        $promo_columns = [
            'promo_enabled' => 'tinyint(1) DEFAULT 0',
            'promo_type' => 'varchar(20) DEFAULT \'fixed\'',
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

        foreach ($promo_columns as $col => $definition) {
            if (!in_array($col, $columns)) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$col} {$definition}");
            }
        }
    }

    // Table creation methods
    private static function create_outlets_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_outlets (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            address text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_categories_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_categories (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_brands_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_brands (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_products_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_products (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            price bigint(20) NOT NULL DEFAULT 0,
            stock bigint(20) NOT NULL DEFAULT 0,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            category_id bigint(20) UNSIGNED NULL,
            brand_id bigint(20) UNSIGNED NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_deleted tinyint(1) DEFAULT 0,
            description text,
            image_url varchar(500),
            PRIMARY KEY (id),
            KEY outlet_id (outlet_id),
            KEY category_id (category_id),
            KEY brand_id (brand_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_packages_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_packages (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            price bigint(20) NOT NULL,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            category_id bigint(20) UNSIGNED NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            image_url varchar(500),
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_package_components_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_package_components (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            package_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY package_id (package_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_bundles_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_bundles (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            price bigint(20) NOT NULL,
            outlet_id bigint(20) UNSIGNED NULL,
            category_id bigint(20) UNSIGNED NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            manual_stock_enabled tinyint(1) DEFAULT 0,
            manual_stock bigint(20) NULL,
            image_url varchar(500),
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_bundle_items_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_bundle_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            bundle_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NULL,
            package_id bigint(20) UNSIGNED NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            is_package tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY bundle_id (bundle_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_transactions_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_transactions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            total bigint(20) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            order_type varchar(50),
            table_number varchar(50),
            customer_name varchar(255),
            estimated_ready_time int(11),
            notes text,
            status varchar(50) DEFAULT 'pending',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY outlet_id (outlet_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_transaction_items_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_transaction_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL,
            price bigint(20) NOT NULL,
            is_package tinyint(1) DEFAULT 0,
            is_bundle tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_payment_methods_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_payment_methods (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) UNSIGNED NOT NULL,
            category varchar(50) NOT NULL,
            sub_category varchar(50),
            method_name varchar(255) NOT NULL,
            amount bigint(20) NOT NULL,
            PRIMARY KEY (id),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_stock_logs_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_stock_logs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            operation varchar(50) NOT NULL,
            quantity int(11) NOT NULL,
            from_outlet_id bigint(20) UNSIGNED NULL,
            to_outlet_id bigint(20) UNSIGNED NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            reference_transaction_id bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY outlet_id (outlet_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_expenses_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_expenses (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            amount bigint(20) NOT NULL,
            category varchar(100),
            type varchar(20) DEFAULT 'expense',
            payment_method varchar(100),
            image_url varchar(500),
            outlet_id bigint(20) UNSIGNED NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP,
            note text,
            PRIMARY KEY (id),
            KEY outlet_id (outlet_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_cashflow_categories_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_cashflow_categories (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(20) DEFAULT 'expense',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_user_profiles_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_user_profiles (
            user_id bigint(20) UNSIGNED NOT NULL,
            name varchar(255) NOT NULL,
            outlet_id bigint(20) UNSIGNED NULL,
            role varchar(50) NOT NULL DEFAULT 'cashier',
            PRIMARY KEY (user_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_customers_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_customers (
              id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              name varchar(255) NOT NULL,
              email varchar(100),
              phone varchar(20),
              address text,
              created_at datetime DEFAULT CURRENT_TIMESTAMP,
              is_active tinyint(1) DEFAULT 1,
              PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_menu_access_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_menu_access (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            role varchar(50) NOT NULL,
            menu varchar(100) NOT NULL,
            is_accessible tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY role_menu (role, menu)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_payment_methods_config_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_payment_methods_config (
            id varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            category varchar(50) NOT NULL,
            sub_category varchar(50),
            enabled tinyint(1) DEFAULT 0,
            icon varchar(50),
            color varchar(50),
            is_default tinyint(1) DEFAULT 0,
            fee decimal(10,2) DEFAULT 0,
            fee_type varchar(20) DEFAULT 'percentage',
            config_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_standalone_promos_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_standalone_promos (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            promo_type varchar(20) DEFAULT 'fixed',
            promo_value decimal(10,2) DEFAULT 0,
            promo_days text,
            promo_start_time time,
            promo_end_time time,
            promo_start_date date,
            promo_end_date date,
            promo_min_purchase decimal(10,2),
            promo_description text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_held_orders_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_held_orders (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            cart_data text NOT NULL,
            payment_methods_data text,
            order_type varchar(50),
            table_number varchar(50),
            customer_name varchar(255),
            notes text,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            customer_note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY outlet_id (outlet_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_kitchen_orders_table($charset_collate) {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}posq_kitchen_orders (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            transaction_id bigint(20) UNSIGNED,
            outlet_id bigint(20) UNSIGNED NOT NULL,
            order_type varchar(50) NOT NULL,
            table_number varchar(50),
            customer_name varchar(255),
            items_data text NOT NULL,
            total bigint(20) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            estimated_ready_time int(11),
            actual_ready_time datetime,
            notes text,
            source varchar(20) DEFAULT 'pos',
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY outlet_id (outlet_id),
            KEY status (status),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    /**
     * Insert default menu access configuration
     */
    private static function insert_default_menu_access() {
        global $wpdb;
        $table = $wpdb->prefix . 'posq_menu_access';

        $defaults = [
            ['cashier', 'dashboard', 1],
            ['cashier', 'pos', 1],
            ['cashier', 'products', 1],
            ['cashier', 'reports', 0],
            ['cashier', 'stock', 0],
            ['cashier', 'staff', 0],
            ['cashier', 'outlets', 0],
            ['cashier', 'categories', 0],
            ['cashier', 'settings', 0],
            ['manager', 'dashboard', 1],
            ['manager', 'pos', 0],
            ['manager', 'products', 1],
            ['manager', 'reports', 1],
            ['manager', 'stock', 1],
            ['manager', 'staff', 1],
            ['manager', 'outlets', 0],
            ['manager', 'categories', 1],
            ['manager', 'settings', 0],
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

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;

        $defaults = [
            ['id' => 'cash', 'name' => 'Cash', 'category' => 'offline', 'sub_category' => 'cash', 'enabled' => 1, 'icon' => 'Banknote', 'color' => 'bg-green-500', 'is_default' => 1, 'fee' => 0, 'fee_type' => 'flat'],
            ['id' => 'qris-static', 'name' => 'QRIS Statis', 'category' => 'online', 'sub_category' => 'qris', 'enabled' => 0, 'icon' => 'QrCode', 'color' => 'bg-blue-500', 'is_default' => 1, 'fee' => 0.7, 'fee_type' => 'percentage'],
            ['id' => 'bank-transfer', 'name' => 'Transfer Bank', 'category' => 'online', 'sub_category' => 'transfer', 'enabled' => 0, 'icon' => 'Building2', 'color' => 'bg-purple-500', 'is_default' => 1, 'fee' => 0, 'fee_type' => 'flat'],
            ['id' => 'debit', 'name' => 'Kartu Debit', 'category' => 'offline', 'sub_category' => 'debit', 'enabled' => 0, 'icon' => 'CreditCard', 'color' => 'bg-indigo-500', 'is_default' => 1, 'fee' => 1.5, 'fee_type' => 'percentage'],
            ['id' => 'credit', 'name' => 'Kartu Kredit', 'category' => 'offline', 'sub_category' => 'credit', 'enabled' => 0, 'icon' => 'CreditCard', 'color' => 'bg-pink-500', 'is_default' => 1, 'fee' => 2.5, 'fee_type' => 'percentage'],
            ['id' => 'ewallet', 'name' => 'E-Wallet', 'category' => 'online', 'sub_category' => 'eWallet', 'enabled' => 0, 'icon' => 'Smartphone', 'color' => 'bg-teal-500', 'is_default' => 1, 'fee' => 1.0, 'fee_type' => 'percentage'],
            ['id' => 'gofood', 'name' => 'GoFood', 'category' => 'foodDelivery', 'sub_category' => 'goFood', 'enabled' => 0, 'icon' => 'UtensilsCrossed', 'color' => 'bg-green-600', 'is_default' => 1, 'fee' => 20, 'fee_type' => 'percentage'],
            ['id' => 'grabfood', 'name' => 'GrabFood', 'category' => 'foodDelivery', 'sub_category' => 'grabFood', 'enabled' => 0, 'icon' => 'UtensilsCrossed', 'color' => 'bg-emerald-600', 'is_default' => 1, 'fee' => 20, 'fee_type' => 'percentage'],
            ['id' => 'shopeefood', 'name' => 'ShopeeFood', 'category' => 'foodDelivery', 'sub_category' => 'shopeeFood', 'enabled' => 0, 'icon' => 'UtensilsCrossed', 'color' => 'bg-orange-600', 'is_default' => 1, 'fee' => 20, 'fee_type' => 'percentage']
        ];

        foreach ($defaults as $method) {
            $method['config_data'] = isset($method['config_data']) ? $method['config_data'] : null;
            $wpdb->replace($table, $method);
        }
    }
}
