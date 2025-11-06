<?php
require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorPaquetes;

$controlador = new ControladorPaquetes();
$controlador->show();
