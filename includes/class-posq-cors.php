<?php
/**
 * POSQ CORS Handler
 * 
 * @package POSQ_Backend
 * @version 3.1.0
 */

if (!defined('ABSPATH')) exit;

class POSQ_CORS {

    /**
     * Handle CORS headers
     */
    public static function handle() {
        $origin = get_http_origin();
        $allowed = [
            'http://localhost:3000',
            'http://192.168.1.7:3000',
            'http://localhost:5173',
            'http://localhost:5174',
            'https://erpos.tekrabyte.id',
        ];

        if ($origin && in_array($origin, $allowed, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Posq-Token");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit;
        }
    }
}
