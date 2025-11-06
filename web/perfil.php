<?php

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorPerfil;

$controlador = new ControladorPerfil();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $controlador->destroy();
    } else {
        $controlador->update();
    }

    return;
}

$controlador->show();
