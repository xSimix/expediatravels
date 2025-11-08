<?php

declare(strict_types=1);

use Aplicacion\BaseDatos\Conexion;

function cargarDestinosDisponibles(array $predeterminados, array &$errores): array
{
    $destinos = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query('SELECT id, nombre, region FROM destinos ORDER BY nombre');
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $destino) {
            $destinos[(int) $destino['id']] = [
                'id' => (int) $destino['id'],
                'nombre' => trim((string) $destino['nombre']),
                'region' => trim((string) $destino['region']),
            ];
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudieron cargar los destinos. Se usarán los de referencia.';
    }

    if (empty($destinos)) {
        foreach ($predeterminados as $destino) {
            $normalizado = normalizarDestinoCircuito($destino);
            $destinos[$normalizado['id']] = $normalizado;
        }
    }

    ksort($destinos);

    return $destinos;
}

function normalizarDestinoCircuito(array $destino): array
{
    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
    ];
}

function cargarCircuitos(array $predeterminados, array $destinosDisponibles, array &$errores): array
{
    $circuitos = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query(
            'SELECT c.*, d.nombre AS destino_nombre, d.region AS destino_region
             FROM circuitos c
             LEFT JOIN destinos d ON d.id = c.destino_id
             ORDER BY c.nombre'
        );
        $circuitos = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de circuitos. Se muestran los circuitos de referencia.';
    }

    if (!empty($circuitos)) {
        $normalizados = array_map(static fn (array $circuito): array => normalizarCircuito($circuito, $destinosDisponibles), $circuitos);

        return ordenarCircuitos($normalizados);
    }

    $predeterminadosNormalizados = array_map(static function (array $circuito) use ($destinosDisponibles): array {
        $circuito['es_predeterminado'] = true;
        return normalizarCircuito($circuito, $destinosDisponibles);
    }, $predeterminados);

    return ordenarCircuitos($predeterminadosNormalizados);
}

function normalizarCircuito(array $circuito, array $destinos): array
{
    $destinoDatos = $circuito['destino'] ?? [];
    $destinoId = $circuito['destino_id'] ?? $destinoDatos['id'] ?? null;
    $destinoPersonalizado = trim((string) ($circuito['destino_personalizado'] ?? $destinoDatos['personalizado'] ?? ''));

    if ($destinoId !== null) {
        $destinoId = (int) $destinoId;
    }

    if ($destinoId !== null && isset($destinos[$destinoId])) {
        $destinoNombre = $destinos[$destinoId]['nombre'];
        $destinoRegion = $destinos[$destinoId]['region'];
    } else {
        $destinoNombre = trim((string) ($destinoDatos['nombre'] ?? $circuito['destino_nombre'] ?? ''));
        $destinoRegion = trim((string) ($destinoDatos['region'] ?? $circuito['destino_region'] ?? ''));
    }

    $puntosInteres = prepararArrayCircuito($circuito['puntos_interes'] ?? []);
    $servicios = prepararArrayCircuito($circuito['servicios'] ?? []);
    $galeria = prepararArrayCircuito($circuito['galeria'] ?? []);
    $categoria = strtolower(trim((string) ($circuito['categoria'] ?? 'naturaleza')));
    $dificultad = strtolower(trim((string) ($circuito['dificultad'] ?? 'relajado')));
    $estado = strtolower(trim((string) ($circuito['estado'] ?? 'borrador')));

    $resultado = [
        'id' => (int) ($circuito['id'] ?? 0),
        'nombre' => trim((string) ($circuito['nombre'] ?? '')),
        'destino' => [
            'id' => $destinoId,
            'nombre' => $destinoNombre,
            'personalizado' => $destinoPersonalizado,
            'region' => $destinoRegion,
        ],
        'duracion' => trim((string) ($circuito['duracion'] ?? '')),
        'categoria' => $categoria,
        'dificultad' => $dificultad,
        'frecuencia' => trim((string) ($circuito['frecuencia'] ?? '')),
        'descripcion' => trim((string) ($circuito['descripcion'] ?? '')),
        'imagen_portada' => trim((string) ($circuito['imagen_portada'] ?? '')),
        'imagen_destacada' => trim((string) ($circuito['imagen_destacada'] ?? '')),
        'galeria' => $galeria,
        'video_destacado_url' => trim((string) ($circuito['video_destacado_url'] ?? $circuito['video_destacado'] ?? '')),
        'puntos_interes' => $puntosInteres,
        'servicios' => $servicios,
        'estado' => $estado,
        'actualizado_en' => $circuito['actualizado_en'] ?? null,
    ];

    if (array_key_exists('es_predeterminado', $circuito)) {
        $resultado['es_predeterminado'] = (bool) $circuito['es_predeterminado'];
    }

    return $resultado;
}

function ordenarCircuitos(array $circuitos): array
{
    usort($circuitos, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($circuitos);
}

function crearCircuitoCatalogo(array $circuito, array &$errores): ?int
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'INSERT INTO circuitos (destino_id, destino_personalizado, nombre, duracion, categoria, dificultad, frecuencia, estado, descripcion, puntos_interes, servicios, imagen_portada, imagen_destacada, galeria, video_destacado_url)
             VALUES (:destino_id, :destino_personalizado, :nombre, :duracion, :categoria, :dificultad, :frecuencia, :estado, :descripcion, :puntos_interes, :servicios, :imagen_portada, :imagen_destacada, :galeria, :video)'
        );
        $statement->execute([
            ':destino_id' => $circuito['destino_id'] > 0 ? $circuito['destino_id'] : null,
            ':destino_personalizado' => $circuito['destino_personalizado'] !== '' ? $circuito['destino_personalizado'] : null,
            ':nombre' => $circuito['nombre'],
            ':duracion' => $circuito['duracion'],
            ':categoria' => $circuito['categoria'],
            ':dificultad' => $circuito['dificultad'],
            ':frecuencia' => $circuito['frecuencia'] !== '' ? $circuito['frecuencia'] : null,
            ':estado' => $circuito['estado'],
            ':descripcion' => $circuito['descripcion'] !== '' ? $circuito['descripcion'] : null,
            ':puntos_interes' => prepararJsonListaCircuito($circuito['puntos_interes']),
            ':servicios' => prepararJsonListaCircuito($circuito['servicios']),
            ':imagen_portada' => $circuito['imagen_portada'] !== '' ? $circuito['imagen_portada'] : null,
            ':imagen_destacada' => $circuito['imagen_destacada'] !== '' ? $circuito['imagen_destacada'] : null,
            ':galeria' => prepararJsonListaCircuito($circuito['galeria']),
            ':video' => $circuito['video_destacado_url'] !== '' ? $circuito['video_destacado_url'] : null,
        ]);

        return (int) $pdo->lastInsertId();
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo guardar el circuito en la base de datos.';
    }

    return null;
}

function actualizarCircuitoCatalogo(int $circuitoId, array $circuito, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'UPDATE circuitos
             SET destino_id = :destino_id,
                 destino_personalizado = :destino_personalizado,
                 nombre = :nombre,
                 duracion = :duracion,
                 categoria = :categoria,
                 dificultad = :dificultad,
                 frecuencia = :frecuencia,
                 estado = :estado,
                 descripcion = :descripcion,
                 puntos_interes = :puntos_interes,
                 servicios = :servicios,
                 imagen_portada = :imagen_portada,
                 imagen_destacada = :imagen_destacada,
                 galeria = :galeria,
                 video_destacado_url = :video
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $circuitoId,
            ':destino_id' => $circuito['destino_id'] > 0 ? $circuito['destino_id'] : null,
            ':destino_personalizado' => $circuito['destino_personalizado'] !== '' ? $circuito['destino_personalizado'] : null,
            ':nombre' => $circuito['nombre'],
            ':duracion' => $circuito['duracion'],
            ':categoria' => $circuito['categoria'],
            ':dificultad' => $circuito['dificultad'],
            ':frecuencia' => $circuito['frecuencia'] !== '' ? $circuito['frecuencia'] : null,
            ':estado' => $circuito['estado'],
            ':descripcion' => $circuito['descripcion'] !== '' ? $circuito['descripcion'] : null,
            ':puntos_interes' => prepararJsonListaCircuito($circuito['puntos_interes']),
            ':servicios' => prepararJsonListaCircuito($circuito['servicios']),
            ':imagen_portada' => $circuito['imagen_portada'] !== '' ? $circuito['imagen_portada'] : null,
            ':imagen_destacada' => $circuito['imagen_destacada'] !== '' ? $circuito['imagen_destacada'] : null,
            ':galeria' => prepararJsonListaCircuito($circuito['galeria']),
            ':video' => $circuito['video_destacado_url'] !== '' ? $circuito['video_destacado_url'] : null,
        ]);

        return $statement->rowCount() > 0;
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo actualizar el circuito en la base de datos.';
    }

    return false;
}

function eliminarCircuitoCatalogo(int $circuitoId, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare('DELETE FROM circuitos WHERE id = :id');
        $statement->execute([':id' => $circuitoId]);

        return $statement->rowCount() > 0;
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo eliminar el circuito en la base de datos.';
    }

    return false;
}

function obtenerCircuitoPorId(int $circuitoId, array $destinosDisponibles, array $predeterminados, array &$errores): ?array
{
    if ($circuitoId <= 0) {
        return null;
    }

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'SELECT c.*, d.nombre AS destino_nombre, d.region AS destino_region
             FROM circuitos c
             LEFT JOIN destinos d ON d.id = c.destino_id
             WHERE c.id = :id'
        );
        $statement->execute([':id' => $circuitoId]);
        $circuito = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($circuito !== false) {
            $normalizado = normalizarCircuito($circuito, $destinosDisponibles);
            $normalizado['es_predeterminado'] = false;

            return $normalizado;
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo obtener el circuito desde la base de datos.';
    }

    foreach ($predeterminados as $predeterminado) {
        if ((int) ($predeterminado['id'] ?? 0) === $circuitoId) {
            $predeterminado['es_predeterminado'] = true;

            return normalizarCircuito($predeterminado, $destinosDisponibles);
        }
    }

    return null;
}

function prepararArrayCircuito($valor): array
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

function prepararJsonListaCircuito(array $valores): ?string
{
    if (empty($valores)) {
        return null;
    }

    return json_encode(array_values($valores), JSON_UNESCAPED_UNICODE);
}

if (!function_exists('convertirListado')) {
    function convertirListado($texto): array
    {
        if (is_array($texto)) {
            $items = $texto;
        } else {
            $items = preg_split('/\r?\n/', (string) $texto) ?: [];
        }

        return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
    }
}

function obtenerNombreDestinoCircuito(array $circuito): string
{
    if (!empty($circuito['destino']['personalizado'])) {
        return $circuito['destino']['personalizado'];
    }

    if (!empty($circuito['destino']['nombre'])) {
        return $circuito['destino']['nombre'];
    }

    return 'Sin destino asignado';
}

function mostrarDuracionCircuito(string $duracion): string
{
    return $duracion !== '' ? $duracion : '—';
}

function categoriaCircuitoEtiqueta(string $categoria): string
{
    return match ($categoria) {
        'cultural' => 'Cultural e histórico',
        'aventura' => 'Aventura y adrenalina',
        'gastronomico' => 'Gastronómico',
        'bienestar' => 'Bienestar y relajación',
        default => 'Naturaleza y aire libre',
    };
}

function dificultadCircuitoEtiqueta(string $dificultad): string
{
    return match ($dificultad) {
        'moderado' => 'Moderado',
        'intenso' => 'Intenso',
        default => 'Relajado',
    };
}

function estadoCircuitoEtiqueta(string $estado): string
{
    return match ($estado) {
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        default => 'Borrador',
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
