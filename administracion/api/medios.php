<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioMedios;
use Aplicacion\Servicios\GestorMedios;

header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($metodo === 'GET') {
    responderJson(['ok' => true, 'items' => mapearMedios((new RepositorioMedios())->listar())]);
}

if ($metodo === 'POST') {
    procesarSubida();
}

http_response_code(405);
responderJson(['ok' => false, 'error' => 'Método no permitido.']);

function responderJson(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function mapearMedios(array $medios): array
{
    return array_map(static function (array $medio): array {
        $ruta = (string) ($medio['ruta'] ?? '');
        $url = '/' . ltrim($ruta, '/');

        return [
            'id' => (int) ($medio['id'] ?? 0),
            'titulo' => (string) ($medio['titulo'] ?? ''),
            'descripcion' => $medio['descripcion'] ?? null,
            'texto_alternativo' => $medio['texto_alternativo'] ?? null,
            'creditos' => $medio['creditos'] ?? null,
            'ruta' => $ruta,
            'url' => $url,
            'ancho' => isset($medio['ancho']) ? ($medio['ancho'] !== null ? (int) $medio['ancho'] : null) : null,
            'alto' => isset($medio['alto']) ? ($medio['alto'] !== null ? (int) $medio['alto'] : null) : null,
            'tamano_bytes' => isset($medio['tamano_bytes']) ? (int) $medio['tamano_bytes'] : null,
            'nombre_original' => $medio['nombre_original'] ?? null,
            'tipo_mime' => $medio['tipo_mime'] ?? null,
        ];
    }, $medios);
}

function procesarSubida(): void
{
    if (!isset($_FILES['archivo'])) {
        http_response_code(400);
        responderJson(['ok' => false, 'error' => 'Selecciona un archivo de imagen.']);
    }

    $archivo = $_FILES['archivo'];
    if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        http_response_code(400);
        responderJson(['ok' => false, 'error' => 'Selecciona un archivo de imagen válido.']);
    }

    $rutaTemporal = $archivo['tmp_name'] ?? '';
    if (!is_string($rutaTemporal) || $rutaTemporal === '' || !is_file($rutaTemporal)) {
        http_response_code(400);
        responderJson(['ok' => false, 'error' => 'El archivo cargado no es válido.']);
    }

    $hash = sha1_file($rutaTemporal) ?: null;
    if ($hash === null) {
        http_response_code(500);
        responderJson(['ok' => false, 'error' => 'No pudimos procesar el archivo.']);
    }

    $repositorio = new RepositorioMedios();
    $existente = $repositorio->buscarPorHash($hash);
    if ($existente !== null) {
        responderJson(['ok' => true, 'duplicado' => true, 'item' => mapearMedios([$existente])[0]]);
    }

    $gestor = new GestorMedios();

    try {
        $meta = $gestor->guardarArchivo($archivo, $hash);
    } catch (RuntimeException $exception) {
        http_response_code(400);
        responderJson(['ok' => false, 'error' => $exception->getMessage()]);
    }

    $titulo = isset($_POST['titulo']) ? trim((string) $_POST['titulo']) : '';
    if ($titulo === '') {
        $titulo = pathinfo($meta['nombre_original'], PATHINFO_FILENAME) ?: 'Imagen sin título';
    }

    $datos = [
        'titulo' => $titulo,
        'descripcion' => obtenerTextoOpcional($_POST['descripcion'] ?? null),
        'texto_alternativo' => obtenerTextoOpcional($_POST['texto_alternativo'] ?? null),
        'creditos' => obtenerTextoOpcional($_POST['creditos'] ?? null),
        'ruta' => $meta['ruta_relativa'],
        'nombre_archivo' => $meta['nombre_archivo'],
        'nombre_original' => $meta['nombre_original'],
        'tipo_mime' => $meta['mime_type'],
        'extension' => $meta['extension'],
        'tamano_bytes' => $meta['tamano_bytes'],
        'ancho' => $meta['ancho'],
        'alto' => $meta['alto'],
        'sha1_hash' => $meta['sha1_hash'],
    ];

    $id = $repositorio->crear($datos);
    if ($id <= 0) {
        $rutaGuardada = dirname(__DIR__, 2) . '/' . ltrim($meta['ruta_relativa'], '/');
        if (is_file($rutaGuardada)) {
            @unlink($rutaGuardada);
        }
        http_response_code(500);
        responderJson(['ok' => false, 'error' => 'No se pudo registrar la imagen en la biblioteca.']);
    }

    $registro = $repositorio->obtenerPorId($id);
    if ($registro === null) {
        $registro = $datos;
        $registro['id'] = $id;
    }

    responderJson(['ok' => true, 'item' => mapearMedios([$registro])[0]]);
}

function obtenerTextoOpcional(mixed $valor): ?string
{
    if (!is_string($valor)) {
        return null;
    }

    $texto = trim($valor);

    return $texto !== '' ? $texto : null;
}
