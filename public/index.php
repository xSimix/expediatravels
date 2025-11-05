<?php
// Expediatravels landing page entry point.
require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\HomeController;

$controller = new HomeController();
$controller->index();
