<?php
// Expediatravels landing page entry point.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../aplicacion/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorInicio;

$controlador = new ControladorInicio();
$controlador->index();
