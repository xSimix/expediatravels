<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioResenasCircuitos;

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido. Usa POST para enviar tu reseña.',
    ]);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$nombre = trim((string) ($payload['nombre'] ?? ''));
$correo = trim((string) ($payload['correo'] ?? ''));
$comentario = trim((string) ($payload['comentario'] ?? ''));
$rating = (int) ($payload['rating'] ?? 5);
$slug = trim((string) ($payload['slug'] ?? ''));
$titulo = trim((string) ($payload['titulo'] ?? ''));

$errores = [];

if ($nombre === '') {
    $errores['nombre'] = 'Ingresa tu nombre para publicar la reseña.';
}

if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores['correo'] = 'Ingresa un correo válido o déjalo en blanco.';
}

if ($slug === '') {
    $errores['slug'] = 'No se pudo identificar el circuito seleccionado.';
}

if ($rating < 1 || $rating > 5) {
    $errores['rating'] = 'Selecciona una calificación entre 1 y 5 estrellas.';
}

if ($comentario === '') {
    $errores['comentario'] = 'Comparte tu experiencia en al menos una frase.';
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

$repositorio = new RepositorioResenasCircuitos();
$identificador = $repositorio->crear([
    'slug' => $slug,
    'titulo' => $titulo !== '' ? $titulo : 'Circuito',
    'nombre' => $nombre,
    'correo' => $correo,
    'rating' => $rating,
    'comentario' => $comentario,
]);

if ($identificador <= 0) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No pudimos registrar tu reseña. Inténtalo nuevamente en unos minutos.',
    ]);
    exit;
}

$reviews = $repositorio->obtenerPorCircuito($slug);

http_response_code(201);
echo json_encode([
    'ok' => true,
    'message' => '¡Gracias por compartir tu reseña!',
    'id' => $identificador,
    'payload' => $reviews,
]);
