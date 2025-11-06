<?php

require_once __DIR__ . '/../aplicacion/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorAutenticacion;

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$controlador = new ControladorAutenticacion();

switch ($action) {
    case 'register':
        $controlador->register();
        break;
    case 'login':
        $controlador->login();
        break;
    case 'verify':
        $controlador->verify();
        break;
    case 'resend-pin':
        $controlador->resendPin();
        break;
    case 'forgot-password':
        $controlador->forgotPassword();
        break;
    case 'logout':
        $controlador->logout();
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Acción no encontrada.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
