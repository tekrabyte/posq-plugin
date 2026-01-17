<?php
/**
 * POSQ API Router
 * 
 * @package POSQ_Backend
 * @version 3.2.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_API_Router {

    /**
     * Initialize API Routes
     */
    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Register all REST API routes
     */
    public static function register_routes() {
        $namespace = 'posq/v1';

        // Register endpoints from each endpoint class
        POSQ_Auth_Endpoints::register_routes($namespace);
        POSQ_User_Endpoints::register_routes($namespace);
        POSQ_Outlet_Endpoints::register_routes($namespace);
        POSQ_Category_Endpoints::register_routes($namespace);
        POSQ_Brand_Endpoints::register_routes($namespace);
        POSQ_Product_Endpoints::register_routes($namespace);
        POSQ_Package_Endpoints::register_routes($namespace);
        POSQ_Bundle_Endpoints::register_routes($namespace);
        POSQ_Transaction_Endpoints::register_routes($namespace);
        POSQ_Stock_Endpoints::register_routes($namespace);
        POSQ_Expense_Endpoints::register_routes($namespace);
        POSQ_Customer_Endpoints::register_routes($namespace);
        POSQ_Cashflow_Endpoints::register_routes($namespace);
        POSQ_Report_Endpoints::register_routes($namespace);
        POSQ_Payment_Method_Endpoints::register_routes($namespace);
        POSQ_Menu_Access_Endpoints::register_routes($namespace);
        POSQ_Held_Order_Endpoints::register_routes($namespace);
        POSQ_Kitchen_Order_Endpoints::register_routes($namespace);
        POSQ_Promo_Endpoints::register_routes($namespace);
        POSQ_Upload_Endpoints::register_routes($namespace);
    }
}
