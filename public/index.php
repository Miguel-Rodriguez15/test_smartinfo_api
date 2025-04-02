<?php
declare(strict_types=1);
ini_set('display_errors', '0');  
error_reporting(E_ALL);

// ==================== CONFIGURACIÓN DE SEGURIDAD ====================
// Headers de seguridad HTTP
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");

// ==================== CONFIGURACIÓN CORS SEGURA ====================
$allowedOrigins = [
    'http://localhost:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: null");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 204 No Content');
    exit();
}

// ==================== PROTECCIÓN CONTRA INYECCIÓN ====================
// Validar el path solicitado
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!preg_match('/^\/api\/[a-zA-Z0-9\/_-]+$/', $requestPath)) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Ruta inválida']));
}

// ==================== CONFIGURACIÓN DE CABECERAS ====================
header('Content-Type: application/json; charset=utf-8');

// ==================== INYECCIÓN DE DEPENDENCIAS SEGURA ====================
try {
    // Cargar dependencias de forma segura
    $routesPath = __DIR__ . '/../api/routes.php';
    if (!file_exists($routesPath) || !is_readable($routesPath)) {
        throw new RuntimeException('Archivo de rutas no accesible');
    }
    
    // Sanitizar posibles outputs
    function cleanOutput($data) {
        if (is_array($data)) {
            return array_map('cleanOutput', $data);
        }
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    
    require_once $routesPath;
    
} catch (Throwable $e) {
    error_log('Error de seguridad: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor',
        'debug' => (ENVIRONMENT === 'development') ? cleanOutput($e->getMessage()) : null
    ]);
    exit;
}