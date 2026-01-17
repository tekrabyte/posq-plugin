<?php
/**
 * Plugin Name: POSQ Backend API
 * Description: Modular POSQ REST API System
 * Version: 3.2.0
 * Author: TB
 * Text Domain: posq-backend
 */

if (!defined('ABSPATH')) exit;

// Constants
define('POSQ_VERSION', '3.2.0');
define('POSQ_PATH', plugin_dir_path(__FILE__));
define('POSQ_URL', plugin_dir_url(__FILE__));

// 1. Load Configurations & Helpers
require_once POSQ_PATH . 'config/database-schema.php';
require_once POSQ_PATH . 'includes/helpers.php';

// 2. Load Core Classes
require_once POSQ_PATH . 'includes/class-posq-database.php';
require_once POSQ_PATH . 'includes/class-posq-auth.php';
require_once POSQ_PATH . 'includes/class-posq-permissions.php';

// 3. Load Models (Autoload all models)
foreach (glob(POSQ_PATH . 'models/*.php') as $filename) {
    require_once $filename;
}

// 4. Load API Layer
require_once POSQ_PATH . 'api/class-posq-api-router.php';

// Load all endpoint classes
foreach (glob(POSQ_PATH . 'api/endpoints/*.php') as $filename) {
    require_once $filename;
}

// Activation Hook
register_activation_hook(__FILE__, ['POSQ_Database', 'activate']);

// Initialize Router
add_action('plugins_loaded', function() {
    POSQ_API_Router::init();
});