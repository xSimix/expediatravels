<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/bootstrap.php';

use App\Controllers\MobileHomeController;

$controller = new MobileHomeController();
$controller->index();
