<?php
require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorCircuitos;

$controlador = new ControladorCircuitos();
$controlador->index();
