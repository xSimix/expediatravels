<?php

require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Controllers\ProfileController;

$controller = new ProfileController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $controller->destroy();
    } else {
        $controller->update();
    }

    return;
}

$controller->show();
