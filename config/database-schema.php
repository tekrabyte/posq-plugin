<?php
if (!defined('ABSPATH')) exit;

class POSQ_Schema {
    public static function get_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        return [
            "CREATE TABLE {$wpdb->prefix}posq_outlets (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                address text NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_categories (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_brands (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_products (
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
                promo_enabled tinyint(1) DEFAULT 0,
                promo_type varchar(20) DEFAULT 'fixed',
                promo_value decimal(10,2) DEFAULT 0,
                promo_days text,
                promo_start_time time,
                promo_end_time time,
                promo_start_date date,
                promo_end_date date,
                promo_min_purchase decimal(10,2),
                promo_description text,
                applied_promo_id bigint(20) UNSIGNED NULL,
                PRIMARY KEY (id),
                KEY outlet_id (outlet_id),
                KEY category_id (category_id),
                KEY brand_id (brand_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_packages (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                price bigint(20) NOT NULL,
                outlet_id bigint(20) UNSIGNED NOT NULL,
                category_id bigint(20) UNSIGNED NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                image_url varchar(500),
                promo_enabled tinyint(1) DEFAULT 0,
                promo_type varchar(20) DEFAULT 'fixed',
                promo_value decimal(10,2) DEFAULT 0,
                promo_days text,
                promo_start_time time,
                promo_end_time time,
                promo_start_date date,
                promo_end_date date,
                promo_min_purchase decimal(10,2),
                promo_description text,
                applied_promo_id bigint(20) UNSIGNED NULL,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_package_components (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                package_id bigint(20) UNSIGNED NOT NULL,
                product_id bigint(20) UNSIGNED NOT NULL,
                quantity int(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                KEY package_id (package_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_bundles (
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
                promo_enabled tinyint(1) DEFAULT 0,
                promo_type varchar(20) DEFAULT 'fixed',
                promo_value decimal(10,2) DEFAULT 0,
                promo_days text,
                promo_start_time time,
                promo_end_time time,
                promo_start_date date,
                promo_end_date date,
                promo_min_purchase decimal(10,2),
                promo_description text,
                applied_promo_id bigint(20) UNSIGNED NULL,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_bundle_items (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                bundle_id bigint(20) UNSIGNED NOT NULL,
                product_id bigint(20) UNSIGNED NULL,
                package_id bigint(20) UNSIGNED NULL,
                quantity int(11) NOT NULL DEFAULT 1,
                is_package tinyint(1) DEFAULT 0,
                PRIMARY KEY (id),
                KEY bundle_id (bundle_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_transactions (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                outlet_id bigint(20) UNSIGNED NOT NULL,
                total bigint(20) NOT NULL,
                order_type varchar(50),
                table_number varchar(50),
                customer_name varchar(255),
                estimated_ready_time int(11),
                notes text,
                status varchar(50) DEFAULT 'pending',
                timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY outlet_id (outlet_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_transaction_items (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                transaction_id bigint(20) UNSIGNED NOT NULL,
                product_id bigint(20) UNSIGNED NOT NULL,
                quantity int(11) NOT NULL,
                price bigint(20) NOT NULL,
                is_package tinyint(1) DEFAULT 0,
                is_bundle tinyint(1) DEFAULT 0,
                PRIMARY KEY (id),
                KEY transaction_id (transaction_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_payment_methods (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                transaction_id bigint(20) UNSIGNED NOT NULL,
                category varchar(50) NOT NULL,
                sub_category varchar(50),
                method_name varchar(255) NOT NULL,
                amount bigint(20) NOT NULL,
                PRIMARY KEY (id),
                KEY transaction_id (transaction_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_stock_logs (
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
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_expenses (
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
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_user_profiles (
                user_id bigint(20) UNSIGNED NOT NULL,
                name varchar(255) NOT NULL,
                outlet_id bigint(20) UNSIGNED NULL,
                role varchar(50) NOT NULL DEFAULT 'cashier',
                PRIMARY KEY (user_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_customers (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                email varchar(100),
                phone varchar(20),
                address text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_menu_access (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                role varchar(50) NOT NULL,
                menu varchar(100) NOT NULL,
                is_accessible tinyint(1) DEFAULT 1,
                PRIMARY KEY (id),
                UNIQUE KEY role_menu (role, menu)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_payment_methods_config (
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
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}posq_standalone_promos (
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
            ) $charset_collate;",
            
            "CREATE TABLE {$wpdb->prefix}posq_held_orders (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                outlet_id bigint(20) UNSIGNED NOT NULL,
                user_id bigint(20) UNSIGNED NOT NULL,
                cart_data text NOT NULL,
                payment_methods_data text,
                order_type varchar(50),
                table_number varchar(50),
                customer_name varchar(255),
                customer_note text,
                notes text,
                timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY outlet_id (outlet_id),
                KEY user_id (user_id)
            ) $charset_collate;",
            
            "CREATE TABLE {$wpdb->prefix}posq_cashflow_categories (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                type varchar(20) DEFAULT 'expense',
                description text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_active tinyint(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;",
            
            "CREATE TABLE {$wpdb->prefix}posq_kitchen_orders (
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
            ) $charset_collate;"
        ];
    }
}