<?php
/**
 * API Router
 * 
 * Central router that registers all REST API endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Api_Router {
    
    private static $namespace = 'posq/v1';
    
    /**
     * Register all REST API routes
     */
    public static function register_all_routes() {
        // Auth routes
        POSQ_Auth_Endpoints::register_routes(self::$namespace);
        
        // Resource routes
        POSQ_User_Endpoints::register_routes(self::$namespace);
        POSQ_Outlet_Endpoints::register_routes(self::$namespace);
        POSQ_Category_Endpoints::register_routes(self::$namespace);
        POSQ_Brand_Endpoints::register_routes(self::$namespace);
        POSQ_Product_Endpoints::register_routes(self::$namespace);
        POSQ_Package_Endpoints::register_routes(self::$namespace);
        POSQ_Bundle_Endpoints::register_routes(self::$namespace);
        POSQ_Transaction_Endpoints::register_routes(self::$namespace);
        POSQ_Stock_Endpoints::register_routes(self::$namespace);
        POSQ_Expense_Endpoints::register_routes(self::$namespace);
        POSQ_Customer_Endpoints::register_routes(self::$namespace);
        POSQ_Cashflow_Endpoints::register_routes(self::$namespace);
        POSQ_Report_Endpoints::register_routes(self::$namespace);
        POSQ_Payment_Method_Endpoints::register_routes(self::$namespace);
        POSQ_Menu_Access_Endpoints::register_routes(self::$namespace);
        POSQ_Held_Order_Endpoints::register_routes(self::$namespace);
        POSQ_Kitchen_Order_Endpoints::register_routes(self::$namespace);
        POSQ_Promo_Endpoints::register_routes(self::$namespace);
        POSQ_Upload_Endpoints::register_routes(self::$namespace);
    }
}
