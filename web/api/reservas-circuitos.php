<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioReservasCircuitos;

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido. Usa POST para registrar una reserva.',
    ]);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$nombre = trim((string) ($payload['nombre'] ?? ''));
$correo = trim((string) ($payload['correo'] ?? ''));
$telefono = trim((string) ($payload['telefono'] ?? ''));
$fechaSalida = trim((string) ($payload['fecha_salida'] ?? ''));
$cantidadPersonas = (int) ($payload['cantidad_personas'] ?? 1);
$mensaje = trim((string) ($payload['mensaje'] ?? ''));
$slug = trim((string) ($payload['slug'] ?? ''));
$titulo = trim((string) ($payload['titulo'] ?? ''));

$errores = [];

if ($nombre === '') {
    $errores['nombre'] = 'Ingresa tu nombre completo.';
}

if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores['correo'] = 'Ingresa un correo electrónico válido.';
}

if ($slug === '') {
    $errores['slug'] = 'No se pudo identificar el circuito seleccionado.';
}

if ($cantidadPersonas <= 0) {
    $errores['cantidad_personas'] = 'La cantidad de personas debe ser mayor a cero.';
}

if (!empty($fechaSalida) && strtotime($fechaSalida) === false) {
    $errores['fecha_salida'] = 'Selecciona una fecha válida para la salida.';
}

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Revisa los campos marcados para continuar.',
        'errors' => $errores,
    ]);
    exit;
}

$repositorio = new RepositorioReservasCircuitos();
$identificador = $repositorio->crear([
    'slug' => $slug,
    'titulo' => $titulo !== '' ? $titulo : 'Circuito',
    'nombre' => $nombre,
    'correo' => $correo,
    'telefono' => $telefono,
    'fecha_salida' => $fechaSalida,
    'cantidad_personas' => $cantidadPersonas,
    'mensaje' => $mensaje,
]);

if ($identificador <= 0) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No pudimos registrar tu reserva en este momento. Inténtalo en unos minutos.',
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => '¡Gracias! Registramos tu solicitud y un asesor se comunicará contigo en breve.',
    'id' => $identificador,
]);
