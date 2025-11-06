<?php
require_once __DIR__ . '/../aplicacion/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorExplorar;

$controlador = new ControladorExplorar();
$controlador->index();
