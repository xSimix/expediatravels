<?php
require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\ExploreController;

$controller = new ExploreController();
$controller->index();
