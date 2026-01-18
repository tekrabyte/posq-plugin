<?php
/**
 * Plugin Name: POSQ Backend API - Fixed with Manual Stock for Bundles
 * Description: Complete POSQ REST API with manual stock support for bundles
 * Version: 3.1.0
 * Author: TB
 * Text Domain: posq-backend
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('POSQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POSQ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POSQ_VERSION', '3.1.0');

// Require core files
require_once POSQ_PLUGIN_DIR . 'includes/helpers.php';
require_once POSQ_PLUGIN_DIR . 'includes/class-posq-database.php';
require_once POSQ_PLUGIN_DIR . 'includes/class-posq-auth.php';
require_once POSQ_PLUGIN_DIR . 'includes/class-posq-permissions.php';
require_once POSQ_PLUGIN_DIR . 'config/database-schema.php';

// Require all models
require_once POSQ_PLUGIN_DIR . 'models/class-outlet-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-category-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-brand-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-product-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-package-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-bundle-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-transaction-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-stock-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-expense-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-customer-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-user-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-payment-method-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-menu-access-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-held-order-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-kitchen-order-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-promo-model.php';
require_once POSQ_PLUGIN_DIR . 'models/class-cashflow-model.php';

// Require API Router
require_once POSQ_PLUGIN_DIR . 'api/class-posq-api-router.php';

/**
 * Main Plugin Class
 */
class POSQ_Backend {

    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // CORS Handling
        add_action('init', ['POSQ_Auth', 'handle_cors'], 0);

        // REST API Routes
        add_action('rest_api_init', ['POSQ_API_Router', 'register_routes']);
    }

    /**
     * Plugin Activation
     */
    public static function activate() {
        POSQ_Database::activate();
    }
}

// Register activation hook
register_activation_hook(__FILE__, ['POSQ_Backend', 'activate']);

// Initialize plugin
add_action('plugins_loaded', function() {
    POSQ_Backend::get_instance();
});
