<?php
/**
 * Cashflow Category Endpoints
 * Handles cashflow category management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-cashflow-model.php';

class POSQ_Cashflow_Endpoints {

    public static function get_cashflow_categories() {
        $categories = POSQ_Cashflow_Model::get_all();
        
        $data = [];
        foreach ($categories as $cat) {
            $data[] = POSQ_Cashflow_Model::format($cat);
        }
        
        return $data;
    }

    public static function create_cashflow_category($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name']) || empty($data['type'])) {
            return new WP_Error('missing_fields', 'Name and type required', ['status' => 400]);
        }

        $id = POSQ_Cashflow_Model::create($data);
        
        if (!$id) {
            return new WP_Error('insert_failed', 'Failed to create category', ['status' => 500]);
        }

        return ['success' => true, 'id' => $id];
    }

    public static function update_cashflow_category($request) {
        $id = (int) $request['id'];
        $data = $request->get_json_params();

        $result = POSQ_Cashflow_Model::update($id, $data);
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update category', ['status' => 500]);
        }

        return ['success' => true];
    }

    public static function delete_cashflow_category($request) {
        $id = (int) $request['id'];
        
        $result = POSQ_Cashflow_Model::delete($id);
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Cannot delete category in use', ['status' => 400]);
        }

        return ['success' => true];
    }
}
