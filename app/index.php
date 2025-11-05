<?php
require_once __DIR__ . '/config/bootstrap.php';

use App\Controllers\MobileHomeController;

$controller = new MobileHomeController();
$controller->index();
