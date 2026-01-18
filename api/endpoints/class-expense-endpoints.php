<?php
/**
 * Expense Endpoints
 * Handles expense management
 */

if (!defined('ABSPATH')) exit;

require_once POSQ_PLUGIN_DIR . 'models/class-expense-model.php';

class POSQ_Expense_Endpoints {

    public static function get_expenses($request) {
        return POSQ_Expense_Model::get_all($request);
    }

    public static function create_expense($request) {
        return POSQ_Expense_Model::create($request);
    }

    public static function update_expense($request) {
        return POSQ_Expense_Model::update($request);
    }

    public static function delete_expense($request) {
        return POSQ_Expense_Model::delete($request);
    }
}
