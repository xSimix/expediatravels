<?php

declare(strict_types=1);

use Aplicacion\BaseDatos\Conexion;

require_once __DIR__ . '/slug_util.php';

/**
 * @return array<int, array<string, mixed>>
 */
function cargarDestinosCatalogo(array $predeterminados, array &$errores): array
{
    $destinos = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query(
            'SELECT id, nombre, descripcion, tagline, lat, lon, imagen, imagen_destacada, region, galeria, video_destacado_url, tags, estado, mostrar_en_buscador, mostrar_en_explorador, actualizado_en
             FROM destinos
             ORDER BY nombre'
        );
        $destinos = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo conectar con la base de datos. Se muestran los destinos de referencia.';
    }

    if (!empty($destinos)) {
        $normalizados = array_map(static fn (array $destino): array => normalizarDestino($destino), $destinos);

        return ordenarDestinos($normalizados);
    }

    $destinosPredeterminados = array_map(static function (array $destino): array {
        $destino['es_predeterminado'] = true;
        return normalizarDestino($destino);
    }, $predeterminados);

    return ordenarDestinos($destinosPredeterminados);
}

function obtenerDestinosPredeterminados(): array
{
    return require __DIR__ . '/../../app/configuracion/destinos_predeterminados.php';
}

function normalizarDestino(array $destino): array
{
    $latitud = null;
    foreach (['latitud', 'lat'] as $campoLat) {
        if (isset($destino[$campoLat]) && is_numeric((string) $destino[$campoLat])) {
            $latitud = (float) $destino[$campoLat];
            break;
        }
    }

    $longitud = null;
    foreach (['longitud', 'lon'] as $campoLon) {
        if (isset($destino[$campoLon]) && is_numeric((string) $destino[$campoLon])) {
            $longitud = (float) $destino[$campoLon];
            break;
        }
    }

    $imagenPortada = trim((string) ($destino['imagen_portada'] ?? $destino['imagen'] ?? ''));
    $imagenDestacada = trim((string) ($destino['imagen_destacada'] ?? ''));
    $videoDestacado = trim((string) ($destino['video_destacado_url'] ?? $destino['video_destacado'] ?? ''));
    $galeria = prepararArrayDesdeEntrada($destino['galeria'] ?? []);
    $tags = prepararArrayDesdeEntrada($destino['tags'] ?? []);

    $resultado = [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
        'descripcion' => trim((string) ($destino['descripcion'] ?? '')),
        'tagline' => trim((string) ($destino['tagline'] ?? '')),
        'latitud' => $latitud,
        'longitud' => $longitud,
        'imagen_portada' => $imagenPortada,
        'imagen' => $imagenPortada,
        'imagen_destacada' => $imagenDestacada,
        'galeria' => $galeria,
        'video_destacado_url' => $videoDestacado,
        'tags' => $tags,
        'estado' => normalizarEstado($destino['estado'] ?? 'activo'),
        'mostrar_en_buscador' => normalizarBanderaVisibilidad($destino['mostrar_en_buscador'] ?? $destino['mostrarEnBuscador'] ?? true),
        'mostrar_en_explorador' => normalizarBanderaVisibilidad($destino['mostrar_en_explorador'] ?? $destino['mostrarEnExplorador'] ?? true),
        'actualizado_en' => $destino['actualizado_en'] ?? null,
    ];

    $slugFuente = trim((string) ($destino['slug'] ?? ''));
    if ($slugFuente === '') {
        $slugFuente = $resultado['nombre'] !== ''
            ? $resultado['nombre']
            : (string) $resultado['id'];
    }

    $resultado['slug'] = adminGenerarSlug($slugFuente);

    if (array_key_exists('es_predeterminado', $destino)) {
        $resultado['es_predeterminado'] = (bool) $destino['es_predeterminado'];
    }

    return $resultado;
}

function ordenarDestinos(array $destinos): array
{
    usort($destinos, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($destinos);
}

function crearDestinoCatalogo(array $destino, array &$errores): ?int
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'INSERT INTO destinos (nombre, descripcion, tagline, lat, lon, imagen, imagen_destacada, region, galeria, video_destacado_url, tags, estado, mostrar_en_buscador, mostrar_en_explorador)
             VALUES (:nombre, :descripcion, :tagline, :lat, :lon, :imagen, :imagen_destacada, :region, :galeria, :video, :tags, :estado, :mostrar_buscador, :mostrar_explorador)'
        );
        $statement->execute([
            ':nombre' => $destino['nombre'],
            ':descripcion' => $destino['descripcion'],
            ':tagline' => $destino['tagline'],
            ':lat' => $destino['latitud'],
            ':lon' => $destino['longitud'],
            ':imagen' => $destino['imagen'] !== '' ? $destino['imagen'] : null,
            ':imagen_destacada' => $destino['imagen_destacada'] !== '' ? $destino['imagen_destacada'] : null,
            ':region' => $destino['region'],
            ':galeria' => prepararJsonLista($destino['galeria']),
            ':video' => $destino['video_destacado_url'] !== '' ? $destino['video_destacado_url'] : null,
            ':tags' => prepararJsonLista($destino['tags']),
            ':estado' => $destino['estado'],
            ':mostrar_buscador' => $destino['mostrar_en_buscador'] ? 1 : 0,
            ':mostrar_explorador' => $destino['mostrar_en_explorador'] ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo guardar el destino en la base de datos.';
    }

    return null;
}

function actualizarDestinoCatalogo(int $destinoId, array $destino, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'UPDATE destinos
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 tagline = :tagline,
                 lat = :lat,
                 lon = :lon,
                 imagen = :imagen,
                 imagen_destacada = :imagen_destacada,
                 region = :region,
                 galeria = :galeria,
                 video_destacado_url = :video,
                 tags = :tags,
                 estado = :estado,
                 mostrar_en_buscador = :mostrar_buscador,
                 mostrar_en_explorador = :mostrar_explorador
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $destinoId,
            ':nombre' => $destino['nombre'],
            ':descripcion' => $destino['descripcion'],
            ':tagline' => $destino['tagline'],
            ':lat' => $destino['latitud'],
            ':lon' => $destino['longitud'],
            ':imagen' => $destino['imagen'] !== '' ? $destino['imagen'] : null,
            ':imagen_destacada' => $destino['imagen_destacada'] !== '' ? $destino['imagen_destacada'] : null,
            ':region' => $destino['region'],
            ':galeria' => prepararJsonLista($destino['galeria']),
            ':video' => $destino['video_destacado_url'] !== '' ? $destino['video_destacado_url'] : null,
            ':tags' => prepararJsonLista($destino['tags']),
            ':estado' => $destino['estado'],
            ':mostrar_buscador' => $destino['mostrar_en_buscador'] ? 1 : 0,
            ':mostrar_explorador' => $destino['mostrar_en_explorador'] ? 1 : 0,
        ]);

        return $statement->rowCount() > 0;
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo actualizar el destino en la base de datos.';
    }

    return false;
}

function eliminarDestinoCatalogo(int $destinoId, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        $pdo->prepare('DELETE FROM paquete_destinos WHERE destino_id = :id')->execute([':id' => $destinoId]);
        $pdo->prepare('UPDATE paquetes SET destino_id = NULL WHERE destino_id = :id')->execute([':id' => $destinoId]);
        $pdo->prepare('UPDATE circuitos SET destino_id = NULL WHERE destino_id = :id')->execute([':id' => $destinoId]);

        $statement = $pdo->prepare('DELETE FROM destinos WHERE id = :id');
        $statement->execute([':id' => $destinoId]);

        if ($statement->rowCount() > 0) {
            $pdo->commit();

            return true;
        }

        $pdo->rollBack();
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errores[] = 'No se pudo eliminar el destino en la base de datos.';
    }

    return false;
}

function obtenerDestinoPorId(int $destinoId, array $predeterminados, array &$errores): ?array
{
    if ($destinoId <= 0) {
        return null;
    }

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'SELECT id, nombre, descripcion, tagline, lat, lon, imagen, imagen_destacada, region, galeria, video_destacado_url, tags, estado, mostrar_en_buscador, mostrar_en_explorador, actualizado_en
             FROM destinos
             WHERE id = :id'
        );
        $statement->execute([':id' => $destinoId]);
        $destino = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($destino !== false) {
            $normalizado = normalizarDestino($destino);
            $normalizado['es_predeterminado'] = false;

            return $normalizado;
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo obtener el destino desde la base de datos.';
    }

    foreach ($predeterminados as $predeterminado) {
        if ((int) ($predeterminado['id'] ?? 0) === $destinoId) {
            $predeterminado['es_predeterminado'] = true;

            return normalizarDestino($predeterminado);
        }
    }

    return null;
}

function prepararArrayDesdeEntrada($valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodificado)) {
            $valor = $decodificado;
        }
    }

    if (!is_array($valor)) {
        $valor = [];
    }

    $resultado = [];
    foreach ($valor as $item) {
        $texto = trim((string) $item);
        if ($texto !== '') {
            $resultado[] = $texto;
        }
    }

    return array_values(array_unique($resultado));
}

function prepararJsonLista(array $valores): ?string
{
    if (empty($valores)) {
        return null;
    }

    return json_encode(array_values($valores), JSON_UNESCAPED_UNICODE);
}

function convertirEtiquetas($valor): array
{
    if (is_array($valor)) {
        $items = $valor;
    } else {
        $items = preg_split('/[,\n]/', (string) $valor) ?: [];
    }

    return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
}

function normalizarBanderaVisibilidad($valor): bool
{
    if (is_bool($valor)) {
        return $valor;
    }

    if (is_numeric($valor)) {
        return (int) $valor === 1;
    }

    $texto = strtolower(trim((string) $valor));
    if ($texto === '') {
        return false;
    }

    return in_array($texto, ['1', 'true', 'si', 'sí', 'on', 'activo', 'visible'], true);
}

function normalizarEstado($valor): string
{
    $estado = strtolower(trim((string) $valor));
    $permitidos = ['activo', 'inactivo'];

    return in_array($estado, $permitidos, true) ? $estado : 'activo';
}

if (!function_exists('obtenerSiguienteId')) {
    function obtenerSiguienteId(array $elementos): int
    {
        $maximo = 0;
        foreach ($elementos as $elemento) {
            $maximo = max($maximo, (int) ($elemento['id'] ?? 0));
        }

        return $maximo + 1;
    }
}

function normalizarCoordenada($valor, string $campo, array &$errores): ?float
{
    if ($valor === null || trim((string) $valor) === '') {
        return null;
    }

    $valor = str_replace(',', '.', trim((string) $valor));
    if (!is_numeric($valor)) {
        $errores[] = sprintf('El campo %s debe contener un número válido.', $campo);
        return null;
    }

    return (float) $valor;
}

function estadoDestinoEtiqueta(string $estado): string
{
    return match ($estado) {
        'inactivo' => 'Inactivo',
        default => 'Activo',
    };
}

if (!function_exists('formatearMarcaTiempo')) {
    function formatearMarcaTiempo(?string $marca): string
    {
        if ($marca === null || $marca === '') {
            return '—';
        }

        try {
            $fecha = new DateTimeImmutable($marca);
            return $fecha->format('d/m/Y H:i');
        } catch (Exception $exception) {
            return $marca;
        }
    }
}
