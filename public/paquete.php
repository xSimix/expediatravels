<?php
require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\PackageController;

$controller = new PackageController();
$controller->show();
