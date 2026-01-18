<?php
/**
 * Promo Endpoints
 * Handles standalone promo management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-promo-model.php';

class POSQ_Promo_Endpoints {

    public static function get_standalone_promos() {
        $promos = POSQ_Promo_Model::get_all();
        
        $result = [];
        foreach ($promos as $promo) {
            $result[] = POSQ_Promo_Model::format($promo);
        }
        
        return $result;
    }

    public static function create_standalone_promo($request) {
        $params = $request->get_json_params();
        
        if (empty($params['name'])) {
            return new WP_Error('missing_fields', 'Name is required', ['status' => 400]);
        }
        
        $id = POSQ_Promo_Model::create($params);
        
        if (!$id) {
            return new WP_Error('insert_failed', 'Failed to create promo', ['status' => 500]);
        }
        
        return ['success' => true, 'id' => (string) $id];
    }

    public static function update_standalone_promo($request) {
        $id = $request['id'];
        $params = $request->get_json_params();
        
        $result = POSQ_Promo_Model::update($id, $params);
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update promo', ['status' => 500]);
        }
        
        return ['success' => true, 'id' => (string) $id];
    }

    public static function delete_standalone_promo($request) {
        $id = $request['id'];
        
        $result = POSQ_Promo_Model::delete($id);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete promo', ['status' => 500]);
        }
        
        return ['success' => true, 'id' => (string) $id];
    }
}
