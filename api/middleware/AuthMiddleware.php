<?php
require_once __DIR__ . '/../utils/JWTGenerator.php';

class AuthMiddleware {
    public static function verifyToken() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        if (!$token) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token no proporcionado']);
            exit;
        }

        try {
            return JWTGenerator::validateToken(str_replace('Bearer ', '', $token));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token inválido: ' . $e->getMessage()]);
            exit;
        }
    }
}