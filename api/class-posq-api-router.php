<?php
/**
 * API Router - Register all REST API routes
 */

if (!defined('ABSPATH')) exit;

class POSQ_API_Router {

    /**
     * Register all REST API routes
     */
    public static function register_routes() {
        $namespace = 'posq/v1';

        // Load all endpoint files
        self::load_endpoints();

        // Auth routes
        register_rest_route($namespace, '/auth/login', [
            'methods' => 'POST',
            'callback' => ['POSQ_Auth', 'login'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route($namespace, '/auth/me', [
            'methods' => 'GET',
            'callback' => ['POSQ_Auth', 'auth_me'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/auth/is-admin', [
            'methods' => 'GET',
            'callback' => ['POSQ_Auth', 'is_admin'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        // Users
        register_rest_route($namespace, '/users', [
            ['methods' => 'GET', 'callback' => ['POSQ_User_Endpoints', 'get_users'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_User_Endpoints', 'create_user'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/users/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_User_Endpoints', 'update_user'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_User_Endpoints', 'delete_user'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Outlets
        register_rest_route($namespace, '/outlets', [
            ['methods' => 'GET', 'callback' => ['POSQ_Outlet_Endpoints', 'get_outlets'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Outlet_Endpoints', 'create_outlet'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/outlets/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Outlet_Endpoints', 'update_outlet'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Outlet_Endpoints', 'delete_outlet'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Categories
        register_rest_route($namespace, '/categories', [
            ['methods' => 'GET', 'callback' => ['POSQ_Category_Endpoints', 'get_categories'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Category_Endpoints', 'create_category'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/categories/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Category_Endpoints', 'update_category'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Category_Endpoints', 'delete_category'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Brands
        register_rest_route($namespace, '/brands', [
            ['methods' => 'GET', 'callback' => ['POSQ_Brand_Endpoints', 'get_brands'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Brand_Endpoints', 'create_brand'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/brands/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Brand_Endpoints', 'update_brand'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Brand_Endpoints', 'delete_brand'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Products
        register_rest_route($namespace, '/products', [
            ['methods' => 'GET', 'callback' => ['POSQ_Product_Endpoints', 'get_products'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Product_Endpoints', 'create_product'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/products/search', [
            'methods' => 'GET',
            'callback' => ['POSQ_Product_Endpoints', 'search_products'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/products/(?P<id>\d+)', [
            ['methods' => 'GET', 'callback' => ['POSQ_Product_Endpoints', 'get_product'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'PUT', 'callback' => ['POSQ_Product_Endpoints', 'update_product'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Product_Endpoints', 'delete_product'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Packages
        register_rest_route($namespace, '/packages', [
            ['methods' => 'GET', 'callback' => ['POSQ_Package_Endpoints', 'get_packages'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Package_Endpoints', 'create_package'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/packages/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Package_Endpoints', 'update_package'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Package_Endpoints', 'delete_package'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Bundles
        register_rest_route($namespace, '/bundles', [
            ['methods' => 'GET', 'callback' => ['POSQ_Bundle_Endpoints', 'get_bundles'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Bundle_Endpoints', 'create_bundle'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/bundles/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Bundle_Endpoints', 'update_bundle'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Bundle_Endpoints', 'delete_bundle'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Stock Management
        register_rest_route($namespace, '/stock/add', [
            'methods' => 'POST',
            'callback' => ['POSQ_Stock_Endpoints', 'add_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);

        register_rest_route($namespace, '/stock/reduce', [
            'methods' => 'POST',
            'callback' => ['POSQ_Stock_Endpoints', 'reduce_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);

        register_rest_route($namespace, '/stock/transfer', [
            'methods' => 'POST',
            'callback' => ['POSQ_Stock_Endpoints', 'transfer_stock'],
            'permission_callback' => ['POSQ_Permissions', 'check_manager']
        ]);

        register_rest_route($namespace, '/stock/logs', [
            'methods' => 'GET',
            'callback' => ['POSQ_Stock_Endpoints', 'get_stock_logs'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        // Transactions
        register_rest_route($namespace, '/transactions', [
            ['methods' => 'GET', 'callback' => ['POSQ_Transaction_Endpoints', 'get_transactions'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Transaction_Endpoints', 'create_transaction'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Expenses
        register_rest_route($namespace, '/expenses', [
            ['methods' => 'GET', 'callback' => ['POSQ_Expense_Endpoints', 'get_expenses'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Expense_Endpoints', 'create_expense'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        register_rest_route($namespace, '/expenses/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Expense_Endpoints', 'update_expense'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Expense_Endpoints', 'delete_expense'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Customers
        register_rest_route($namespace, '/customers', [
            ['methods' => 'GET', 'callback' => ['POSQ_Customer_Endpoints', 'get_customers'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Customer_Endpoints', 'create_customer'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        register_rest_route($namespace, '/customers/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Customer_Endpoints', 'update_customer'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Customer_Endpoints', 'delete_customer'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Cashflow Categories
        register_rest_route($namespace, '/cashflow-categories', [
            ['methods' => 'GET', 'callback' => ['POSQ_Cashflow_Endpoints', 'get_cashflow_categories'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Cashflow_Endpoints', 'create_cashflow_category'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        register_rest_route($namespace, '/cashflow-categories/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Cashflow_Endpoints', 'update_cashflow_category'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Cashflow_Endpoints', 'delete_cashflow_category'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Reports
        register_rest_route($namespace, '/reports/top-outlets', [
            'methods' => 'GET',
            'callback' => ['POSQ_Report_Endpoints', 'report_top_outlets'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/reports/daily-summary', [
            'methods' => 'GET',
            'callback' => ['POSQ_Report_Endpoints', 'report_daily_summary'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/reports/overall-summary', [
            'methods' => 'GET',
            'callback' => ['POSQ_Report_Endpoints', 'report_overall_summary'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/reports/best-sellers', [
            'methods' => 'GET',
            'callback' => ['POSQ_Report_Endpoints', 'report_best_sellers'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        register_rest_route($namespace, '/reports/cashflow', [
            'methods' => 'GET',
            'callback' => ['POSQ_Report_Endpoints', 'report_cashflow'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        // Payment Methods Configuration
        register_rest_route($namespace, '/payment-methods', [
            ['methods' => 'GET', 'callback' => ['POSQ_Payment_Method_Endpoints', 'get_payment_methods'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
        ]);

        register_rest_route($namespace, '/payment-methods/(?P<id>[a-zA-Z0-9_-]+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Payment_Method_Endpoints', 'update_payment_method'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
        ]);

        register_rest_route($namespace, '/payment-methods/custom', [
            ['methods' => 'POST', 'callback' => ['POSQ_Payment_Method_Endpoints', 'create_custom_payment_method'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
        ]);

        register_rest_route($namespace, '/payment-methods/custom/(?P<id>[a-zA-Z0-9_-]+)', [
            ['methods' => 'DELETE', 'callback' => ['POSQ_Payment_Method_Endpoints', 'delete_custom_payment_method'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
        ]);

        // Menu Access
        register_rest_route($namespace, '/settings/menu-access', [
            ['methods' => 'GET', 'callback' => ['POSQ_Menu_Access_Endpoints', 'get_menu_access'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Menu_Access_Endpoints', 'save_menu_access'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/settings/role-menu-access', [
            'methods' => 'GET',
            'callback' => ['POSQ_Menu_Access_Endpoints', 'get_role_menu_access'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);

        // Held Orders
        register_rest_route($namespace, '/held-orders', [
            ['methods' => 'GET', 'callback' => ['POSQ_Held_Order_Endpoints', 'get_held_orders'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Held_Order_Endpoints', 'create_held_order'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        register_rest_route($namespace, '/held-orders/(?P<id>\d+)', [
            ['methods' => 'DELETE', 'callback' => ['POSQ_Held_Order_Endpoints', 'delete_held_order'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Kitchen Orders
        register_rest_route($namespace, '/kitchen-orders', [
            ['methods' => 'GET', 'callback' => ['POSQ_Kitchen_Order_Endpoints', 'get_kitchen_orders'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
        ]);

        register_rest_route($namespace, '/kitchen-orders/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Kitchen_Order_Endpoints', 'update_kitchen_order'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']]
        ]);

        // Standalone Promos
        register_rest_route($namespace, '/standalone-promos', [
            ['methods' => 'GET', 'callback' => ['POSQ_Promo_Endpoints', 'get_standalone_promos'], 'permission_callback' => ['POSQ_Permissions', 'check_auth']],
            ['methods' => 'POST', 'callback' => ['POSQ_Promo_Endpoints', 'create_standalone_promo'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        register_rest_route($namespace, '/standalone-promos/(?P<id>\d+)', [
            ['methods' => 'PUT', 'callback' => ['POSQ_Promo_Endpoints', 'update_standalone_promo'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']],
            ['methods' => 'DELETE', 'callback' => ['POSQ_Promo_Endpoints', 'delete_standalone_promo'], 'permission_callback' => ['POSQ_Permissions', 'check_owner']]
        ]);

        // Image Upload
        register_rest_route($namespace, '/upload-image', [
            'methods' => 'POST',
            'callback' => ['POSQ_Upload_Endpoints', 'upload_image'],
            'permission_callback' => ['POSQ_Permissions', 'check_auth']
        ]);
    }

    /**
     * Load all endpoint files
     */
    private static function load_endpoints() {
        $endpoint_files = [
            'class-auth-endpoints.php',
            'class-user-endpoints.php',
            'class-outlet-endpoints.php',
            'class-category-endpoints.php',
            'class-brand-endpoints.php',
            'class-product-endpoints.php',
            'class-package-endpoints.php',
            'class-bundle-endpoints.php',
            'class-transaction-endpoints.php',
            'class-stock-endpoints.php',
            'class-expense-endpoints.php',
            'class-customer-endpoints.php',
            'class-cashflow-endpoints.php',
            'class-report-endpoints.php',
            'class-payment-method-endpoints.php',
            'class-menu-access-endpoints.php',
            'class-held-order-endpoints.php',
            'class-kitchen-order-endpoints.php',
            'class-promo-endpoints.php',
            'class-upload-endpoints.php'
        ];

        foreach ($endpoint_files as $file) {
            $path = POSQ_PLUGIN_DIR . 'api/endpoints/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}
