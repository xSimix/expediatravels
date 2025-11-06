<?php
require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorExplorar;

$controlador = new ControladorExplorar();
$controlador->index();
