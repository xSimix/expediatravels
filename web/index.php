<?php
// Expediatravels landing page entry point.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\HomeController;

$controller = new HomeController();
$controller->index();
