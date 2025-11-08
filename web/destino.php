<?php
require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Controladores\ControladorDestinos;

$controlador = new ControladorDestinos();
$controlador->show();
