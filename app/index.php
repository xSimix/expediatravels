<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorInicioMovil;

$controlador = new ControladorInicioMovil();
$controlador->index();
