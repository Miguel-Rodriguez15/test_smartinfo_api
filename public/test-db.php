<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../api/controllers/TestController.php';

$controller = new TestController();
echo json_encode($controller->checkConnection());