<?php
require_once __DIR__ . '/../models/Database.php';

class TestController {
    public function checkConnection() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->query("SELECT 1 AS test");
            $result = $stmt->fetch();
            
            return ['status' => 'success', 'data' => $result];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}