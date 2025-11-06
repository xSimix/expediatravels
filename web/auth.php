<?php

require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\AuthController;

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$controller = new AuthController();

switch ($action) {
    case 'register':
        $controller->register();
        break;
    case 'login':
        $controller->login();
        break;
    case 'verify':
        $controller->verify();
        break;
    case 'resend-pin':
        $controller->resendPin();
        break;
    case 'forgot-password':
        $controller->forgotPassword();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Acción no encontrada.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
