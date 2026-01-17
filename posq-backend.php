<?php
/**
 * Plugin Name: POSQ Backend API - Modular Structure
 * Description: Complete POSQ REST API with clean architecture
 * Version: 4.0.0
 * Author: TB
 * Text Domain: posq-backend
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('POSQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POSQ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POSQ_VERSION', '4.0.0');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Only autoload our classes
    if (strpos($class, 'POSQ_') !== 0) {
        return;
    }
    
    // Convert class name to file path
    $class = str_replace('POSQ_', '', $class);
    $class = str_replace('_', '-', strtolower($class));
    
    // Try different directories
    $paths = [
        POSQ_PLUGIN_DIR . 'includes/class-' . $class . '.php',
        POSQ_PLUGIN_DIR . 'api/class-' . $class . '.php',
        POSQ_PLUGIN_DIR . 'api/endpoints/class-' . $class . '.php',
        POSQ_PLUGIN_DIR . 'models/class-' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load helper functions
require_once POSQ_PLUGIN_DIR . 'includes/helpers.php';

// Register activation hook
register_activation_hook(__FILE__, ['POSQ_Database', 'activate']);

// Initialize plugin
add_action('init', 'posq_init_plugin', 0);

function posq_init_plugin() {
    // Handle CORS
    POSQ_Auth::handle_cors();
    
    // Register REST API routes
    add_action('rest_api_init', ['POSQ_Api_Router', 'register_all_routes']);
}
