<?php

declare(strict_types=1);

use Aplicacion\BaseDatos\Conexion;
use DateTimeImmutable;
use Exception;

require_once __DIR__ . '/slug_util.php';

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

function cargarServiciosDisponibles(array $predeterminados, array &$errores): array
{
    $servicios = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query('SELECT id, nombre, icono, descripcion FROM servicios_catalogo WHERE activo = 1 ORDER BY nombre');
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $servicio) {
            $id = (int) $servicio['id'];
            if ($id <= 0) {
                continue;
            }
            $servicios[$id] = [
                'id' => $id,
                'nombre' => trim((string) $servicio['nombre']),
                'icono' => trim((string) ($servicio['icono'] ?? '')),
                'descripcion' => trim((string) ($servicio['descripcion'] ?? '')),
            ];
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo cargar la lista de servicios desde la base de datos. Se usarán los servicios de referencia.';
    }

    if (empty($servicios)) {
        foreach ($predeterminados as $servicio) {
            $id = (int) ($servicio['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $servicios[$id] = [
                'id' => $id,
                'nombre' => trim((string) ($servicio['nombre'] ?? '')),
                'icono' => trim((string) ($servicio['icono'] ?? '')),
                'descripcion' => trim((string) ($servicio['descripcion'] ?? '')),
            ];
        }
    }

    ksort($servicios);

    return $servicios;
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
        if (!empty($circuitos)) {
            $relaciones = circuitosCargarRelaciones($pdo);
            foreach ($circuitos as &$circuito) {
                $identificador = (int) ($circuito['id'] ?? 0);
                $circuito['destinos_relacionados'] = $relaciones[$identificador] ?? [];
            }
            unset($circuito);
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de circuitos. Se muestran los circuitos de referencia.';
    }

    if (!empty($circuitos)) {
        $normalizados = array_map(static fn (array $circuito): array => normalizarCircuito($circuito, $destinosDisponibles), $circuitos);
        $completos = array_map(static function (array $circuito) use (&$errores): array {
            return adjuntarRelacionesCircuito($circuito, $errores);
        }, $normalizados);

        return ordenarCircuitos($completos);
    }

    $predeterminadosNormalizados = array_map(static function (array $circuito) use ($destinosDisponibles): array {
        $circuito['es_predeterminado'] = true;
        if (!isset($circuito['destinos_relacionados']) && isset($circuito['destinos'])) {
            $circuito['destinos_relacionados'] = (array) $circuito['destinos'];
        }

        return normalizarCircuito($circuito, $destinosDisponibles);
    }, $predeterminados);

    return ordenarCircuitos($predeterminadosNormalizados);
}

function normalizarCircuito(array $circuito, array $destinos): array
{
    $destinoDatos = $circuito['destino'] ?? [];
    $destinoPersonalizado = trim((string) ($circuito['destino_personalizado'] ?? $destinoDatos['personalizado'] ?? ''));

    $destinosRelacionados = $circuito['destinos_relacionados'] ?? $circuito['destinos'] ?? [];
    if (!is_array($destinosRelacionados)) {
        $destinosRelacionados = [$destinosRelacionados];
    }

    if (isset($circuito['destino_id'])) {
        $destinosRelacionados[] = (int) $circuito['destino_id'];
    } elseif (isset($destinoDatos['id'])) {
        $destinosRelacionados[] = (int) $destinoDatos['id'];
    }

    $destinosRelacionados = array_values(array_unique(array_filter(array_map('intval', $destinosRelacionados), static fn (int $valor): bool => $valor > 0)));

    $destinoPrincipalId = $destinosRelacionados[0] ?? null;
    $destinoNombre = '';
    $destinoRegion = '';

    if ($destinoPrincipalId !== null && isset($destinos[$destinoPrincipalId])) {
        $destinoNombre = $destinos[$destinoPrincipalId]['nombre'];
        $destinoRegion = $destinos[$destinoPrincipalId]['region'];
    } else {
        $destinoNombre = trim((string) ($destinoDatos['nombre'] ?? $circuito['destino_nombre'] ?? ''));
        $destinoRegion = trim((string) ($destinoDatos['region'] ?? $circuito['destino_region'] ?? ''));
    }

    $destinosNombres = [];
    foreach ($destinosRelacionados as $destinoId) {
        if (isset($destinos[$destinoId])) {
            $destinosNombres[] = $destinos[$destinoId]['nombre'];
            continue;
        }

        if ($destinoId === $destinoPrincipalId && $destinoNombre !== '') {
            $destinosNombres[] = $destinoNombre;
        }
    }

    if (empty($destinosNombres) && $destinoNombre !== '') {
        $destinosNombres[] = $destinoNombre;
    }

    $serviciosIncluidos = prepararArrayCircuito($circuito['servicios_incluidos'] ?? $circuito['servicios'] ?? []);
    $serviciosExcluidos = prepararArrayCircuito($circuito['servicios_excluidos'] ?? []);
    $galeria = prepararArrayCircuito($circuito['galeria'] ?? []);
    $precioBruto = $circuito['precio'] ?? $circuito['precio_desde'] ?? null;
    if (is_string($precioBruto)) {
        $precioSanitizado = preg_replace('/[^0-9,\.\-]/', '', $precioBruto);
        if ($precioSanitizado !== null && $precioSanitizado !== '') {
            $precioSanitizado = str_replace(',', '.', $precioSanitizado);
            if (is_numeric($precioSanitizado)) {
                $precioBruto = (float) $precioSanitizado;
            }
        }
    }
    $precio = is_numeric($precioBruto) ? (float) $precioBruto : null;
    $categoria = strtolower(trim((string) ($circuito['categoria'] ?? 'naturaleza')));
    $dificultad = strtolower(trim((string) ($circuito['dificultad'] ?? 'relajado')));
    $estado = strtolower(trim((string) ($circuito['estado'] ?? 'borrador')));
    $estadoPublicacion = strtolower(trim((string) ($circuito['estado_publicacion'] ?? 'borrador')));
    $visibilidad = strtolower(trim((string) ($circuito['visibilidad'] ?? 'publico')));

    $resultado = [
        'id' => (int) ($circuito['id'] ?? 0),
        'nombre' => trim((string) ($circuito['nombre'] ?? '')),
        'destino' => [
            'id' => $destinoPrincipalId,
            'nombre' => $destinoNombre,
            'personalizado' => $destinoPersonalizado,
            'region' => $destinoRegion,
        ],
        'destinos' => $destinosRelacionados,
        'destinos_nombres' => $destinosNombres,
        'duracion' => trim((string) ($circuito['duracion'] ?? '')),
        'precio' => $precio,
        'moneda' => strtoupper(trim((string) ($circuito['moneda'] ?? 'PEN'))),
        'categoria' => $categoria,
        'dificultad' => $dificultad,
        'frecuencia' => trim((string) ($circuito['frecuencia'] ?? '')),
        'descripcion' => trim((string) ($circuito['descripcion'] ?? '')),
        'imagen_portada' => trim((string) ($circuito['imagen_portada'] ?? '')),
        'imagen_destacada' => trim((string) ($circuito['imagen_destacada'] ?? '')),
        'galeria' => $galeria,
        'video_destacado_url' => trim((string) ($circuito['video_destacado_url'] ?? $circuito['video_destacado'] ?? '')),
        'servicios' => $serviciosIncluidos,
        'servicios_incluidos' => $serviciosIncluidos,
        'servicios_excluidos' => $serviciosExcluidos,
        'itinerario' => prepararItinerarioCircuito($circuito['itinerario'] ?? []),
        'estado' => $estado,
        'estado_publicacion' => $estadoPublicacion === 'publicado' ? 'publicado' : 'borrador',
        'visibilidad' => $visibilidad === 'privado' ? 'privado' : 'publico',
        'vigencia_desde' => $circuito['vigencia_desde'] ?? null,
        'vigencia_hasta' => $circuito['vigencia_hasta'] ?? null,
        'actualizado_en' => $circuito['actualizado_en'] ?? null,
    ];

    $essentials = [];
    if (!empty($serviciosIncluidos)) {
        $essentials[] = ['title' => 'Incluye', 'items' => $serviciosIncluidos];
    }
    if (!empty($serviciosExcluidos)) {
        $essentials[] = ['title' => 'No incluye', 'items' => $serviciosExcluidos];
    }
    if (!empty($essentials)) {
        $resultado['essentials'] = $essentials;
    }

    $resultado['vigencia_desde_form'] = circuitosFormatearFechaParaFormulario($resultado['vigencia_desde']);
    $resultado['vigencia_hasta_form'] = circuitosFormatearFechaParaFormulario($resultado['vigencia_hasta']);

    $slugFuente = trim((string) ($circuito['slug'] ?? ''));
    if ($slugFuente === '') {
        $slugFuente = $resultado['nombre'] !== ''
            ? $resultado['nombre']
            : (string) $resultado['id'];
    }

    $resultado['slug'] = adminGenerarSlug($slugFuente);

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
        $pdo->beginTransaction();

        $serviciosIncluidosIds = array_map('intval', $circuito['servicios_incluidos_ids'] ?? []);
        $serviciosIncluidosNombres = obtenerNombresServiciosPorIds($pdo, $serviciosIncluidosIds);
        $destinosSeleccionados = array_values(array_filter(array_map('intval', $circuito['destinos'] ?? []), static fn (int $valor): bool => $valor > 0));
        $destinoPrincipal = $destinosSeleccionados[0] ?? null;

        $statement = $pdo->prepare(
            'INSERT INTO circuitos (destino_id, destino_personalizado, nombre, duracion, precio, categoria, dificultad, frecuencia, estado, estado_publicacion, vigencia_desde, vigencia_hasta, visibilidad, descripcion, servicios, imagen_portada, imagen_destacada, galeria, video_destacado_url)
             VALUES (:destino_id, :destino_personalizado, :nombre, :duracion, :precio, :categoria, :dificultad, :frecuencia, :estado, :estado_publicacion, :vigencia_desde, :vigencia_hasta, :visibilidad, :descripcion, :servicios, :imagen_portada, :imagen_destacada, :galeria, :video)'
        );
        $statement->execute([
            ':destino_id' => $destinoPrincipal !== null ? $destinoPrincipal : null,
            ':destino_personalizado' => $circuito['destino_personalizado'] !== '' ? $circuito['destino_personalizado'] : null,
            ':nombre' => $circuito['nombre'],
            ':duracion' => $circuito['duracion'],
            ':precio' => $circuito['precio'] !== null ? $circuito['precio'] : null,
            ':categoria' => $circuito['categoria'],
            ':dificultad' => $circuito['dificultad'],
            ':frecuencia' => $circuito['frecuencia'] !== '' ? $circuito['frecuencia'] : null,
            ':estado' => $circuito['estado'],
            ':estado_publicacion' => $circuito['estado_publicacion'],
            ':vigencia_desde' => $circuito['vigencia_desde'] ?? null,
            ':vigencia_hasta' => $circuito['vigencia_hasta'] ?? null,
            ':visibilidad' => $circuito['visibilidad'],
            ':descripcion' => $circuito['descripcion'] !== '' ? $circuito['descripcion'] : null,
            ':servicios' => prepararJsonListaCircuito($serviciosIncluidosNombres),
            ':imagen_portada' => $circuito['imagen_portada'] !== '' ? $circuito['imagen_portada'] : null,
            ':imagen_destacada' => $circuito['imagen_destacada'] !== '' ? $circuito['imagen_destacada'] : null,
            ':galeria' => prepararJsonListaCircuito($circuito['galeria']),
            ':video' => $circuito['video_destacado_url'] !== '' ? $circuito['video_destacado_url'] : null,
        ]);

        $circuitoId = (int) $pdo->lastInsertId();

        sincronizarItinerarioCircuito($pdo, $circuitoId, $circuito['itinerario'] ?? []);
        sincronizarServiciosCircuito($pdo, $circuitoId, [
            'incluido' => $serviciosIncluidosIds,
            'excluido' => array_map('intval', $circuito['servicios_excluidos_ids'] ?? []),
        ]);
        circuitosSincronizarRelaciones($pdo, $circuitoId, $destinosSeleccionados);

        $pdo->commit();

        return $circuitoId;
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errores[] = 'No se pudo guardar el circuito en la base de datos.';
    }

    return null;
}

function actualizarCircuitoCatalogo(int $circuitoId, array $circuito, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        $serviciosIncluidosIds = array_map('intval', $circuito['servicios_incluidos_ids'] ?? []);
        $serviciosIncluidosNombres = obtenerNombresServiciosPorIds($pdo, $serviciosIncluidosIds);
        $destinosSeleccionados = array_values(array_filter(array_map('intval', $circuito['destinos'] ?? []), static fn (int $valor): bool => $valor > 0));
        $destinoPrincipal = $destinosSeleccionados[0] ?? null;

        $statement = $pdo->prepare(
            'UPDATE circuitos
             SET destino_id = :destino_id,
                 destino_personalizado = :destino_personalizado,
                 nombre = :nombre,
                 duracion = :duracion,
                 precio = :precio,
                 categoria = :categoria,
                 dificultad = :dificultad,
                 frecuencia = :frecuencia,
                 estado = :estado,
                 estado_publicacion = :estado_publicacion,
                 vigencia_desde = :vigencia_desde,
                 vigencia_hasta = :vigencia_hasta,
                 visibilidad = :visibilidad,
                 descripcion = :descripcion,
                 servicios = :servicios,
                 imagen_portada = :imagen_portada,
                 imagen_destacada = :imagen_destacada,
                 galeria = :galeria,
                 video_destacado_url = :video
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $circuitoId,
            ':destino_id' => $destinoPrincipal !== null ? $destinoPrincipal : null,
            ':destino_personalizado' => $circuito['destino_personalizado'] !== '' ? $circuito['destino_personalizado'] : null,
            ':nombre' => $circuito['nombre'],
            ':duracion' => $circuito['duracion'],
            ':precio' => $circuito['precio'] !== null ? $circuito['precio'] : null,
            ':categoria' => $circuito['categoria'],
            ':dificultad' => $circuito['dificultad'],
            ':frecuencia' => $circuito['frecuencia'] !== '' ? $circuito['frecuencia'] : null,
            ':estado' => $circuito['estado'],
            ':estado_publicacion' => $circuito['estado_publicacion'],
            ':vigencia_desde' => $circuito['vigencia_desde'] ?? null,
            ':vigencia_hasta' => $circuito['vigencia_hasta'] ?? null,
            ':visibilidad' => $circuito['visibilidad'],
            ':descripcion' => $circuito['descripcion'] !== '' ? $circuito['descripcion'] : null,
            ':servicios' => prepararJsonListaCircuito($serviciosIncluidosNombres),
            ':imagen_portada' => $circuito['imagen_portada'] !== '' ? $circuito['imagen_portada'] : null,
            ':imagen_destacada' => $circuito['imagen_destacada'] !== '' ? $circuito['imagen_destacada'] : null,
            ':galeria' => prepararJsonListaCircuito($circuito['galeria']),
            ':video' => $circuito['video_destacado_url'] !== '' ? $circuito['video_destacado_url'] : null,
        ]);

        sincronizarItinerarioCircuito($pdo, $circuitoId, $circuito['itinerario'] ?? []);
        sincronizarServiciosCircuito($pdo, $circuitoId, [
            'incluido' => $serviciosIncluidosIds,
            'excluido' => array_map('intval', $circuito['servicios_excluidos_ids'] ?? []),
        ]);
        circuitosSincronizarRelaciones($pdo, $circuitoId, $destinosSeleccionados);

        $pdo->commit();

        return $statement->rowCount() > 0;
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errores[] = 'No se pudo actualizar el circuito en la base de datos.';
    }

    return false;
}

function eliminarCircuitoCatalogo(int $circuitoId, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        $pdo->prepare('DELETE FROM circuito_destinos WHERE circuito_id = :id')->execute([':id' => $circuitoId]);

        $statement = $pdo->prepare('DELETE FROM circuitos WHERE id = :id');
        $statement->execute([':id' => $circuitoId]);

        if ($statement->rowCount() > 0) {
            $pdo->commit();

            return true;
        }

        $pdo->rollBack();
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
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
            $circuito['destinos_relacionados'] = circuitosObtenerRelacionesPorId(
                $pdo,
                $circuitoId,
                isset($circuito['destino_id']) ? (int) $circuito['destino_id'] : null
            );
            $normalizado = normalizarCircuito($circuito, $destinosDisponibles);
            $normalizado['es_predeterminado'] = false;

            return adjuntarRelacionesCircuito($normalizado, $errores);
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

function adjuntarRelacionesCircuito(array $circuito, array &$errores): array
{
    $circuitoId = (int) ($circuito['id'] ?? 0);
    if ($circuitoId <= 0) {
        return $circuito;
    }

    try {
        $pdo = Conexion::obtener();
        $destinosIds = circuitosObtenerRelacionesPorId(
            $pdo,
            $circuitoId,
            isset($circuito['destino']['id']) ? (int) $circuito['destino']['id'] : null
        );
        $circuito['destinos'] = $destinosIds;
        $circuito['destinos_relacionados'] = $destinosIds;
        $circuito['itinerario'] = obtenerItinerarioDesdeDb($pdo, $circuitoId);
        $servicios = obtenerServiciosDesdeDb($pdo, $circuitoId);
        $circuito['servicios_incluidos'] = $servicios['incluido']['nombres'];
        $circuito['servicios_excluidos'] = $servicios['excluido']['nombres'];
        $circuito['servicios_incluidos_detalles'] = $servicios['incluido']['detalles'];
        $circuito['servicios_excluidos_detalles'] = $servicios['excluido']['detalles'];
        $circuito['servicios_incluidos_ids'] = $servicios['incluido']['ids'];
        $circuito['servicios_excluidos_ids'] = $servicios['excluido']['ids'];
        $circuito['servicios'] = $servicios['incluido']['nombres'];
        $essentials = [];
        if (!empty($servicios['incluido']['detalles'])) {
            $essentials[] = ['title' => 'Incluye', 'items' => transformarServiciosParaPresentacion($servicios['incluido']['detalles'])];
        }
        if (!empty($servicios['excluido']['detalles'])) {
            $essentials[] = ['title' => 'No incluye', 'items' => transformarServiciosParaPresentacion($servicios['excluido']['detalles'])];
        }
        if (!empty($essentials)) {
            $circuito['essentials'] = $essentials;
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudieron cargar los detalles extendidos del circuito.';
    }

    return $circuito;
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
    $nombres = [];
    if (!empty($circuito['destinos_nombres']) && is_array($circuito['destinos_nombres'])) {
        $nombres = array_values(array_filter(array_map(static fn ($valor): string => trim((string) $valor), $circuito['destinos_nombres'])));
    }

    if (!empty($nombres)) {
        return implode(' · ', array_slice($nombres, 0, 3)) . (count($nombres) > 3 ? ' +' . (count($nombres) - 3) : '');
    }

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

function circuitosFormatearPrecio(?float $precio, string $moneda = 'PEN'): string
{
    if ($precio === null) {
        return '—';
    }

    $simbolos = [
        'USD' => '$',
        'EUR' => '€',
        'PEN' => 'S/',
    ];

    $moneda = strtoupper($moneda);
    $simbolo = $simbolos[$moneda] ?? $moneda;

    return sprintf('%s %.2f', $simbolo, $precio);
}

function circuitosParsearPrecio(string $valor, array &$errores): ?float
{
    $texto = trim($valor);
    if ($texto === '') {
        return null;
    }

    $sinSimbolos = str_replace(['S/', 's/', '$', '€', '£', 'USD', 'PEN'], '', $texto);
    $normalizado = str_replace(',', '.', preg_replace('/[^0-9,\.\-]/', '', $sinSimbolos));

    if ($normalizado === '' || !is_numeric($normalizado)) {
        $errores[] = 'El precio debe ser un número válido. Ejemplo: 150.00';

        return null;
    }

    $precio = round((float) $normalizado, 2);
    if ($precio < 0) {
        $errores[] = 'El precio debe ser mayor o igual a cero.';

        return null;
    }

    return $precio;
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

function estadoPublicacionCircuitoEtiqueta(string $estado): string
{
    return $estado === 'publicado' ? 'Publicado' : 'Borrador';
}

function visibilidadCircuitoEtiqueta(string $visibilidad): string
{
    return $visibilidad === 'privado' ? 'Privado' : 'Público';
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

function prepararItinerarioCircuito($valor): array
{
    if (!is_array($valor)) {
        return [];
    }

    $resultado = [];
    foreach ($valor as $item) {
        if (!is_array($item)) {
            continue;
        }
        $titulo = trim((string) ($item['titulo'] ?? ''));
        $descripcion = trim((string) ($item['descripcion'] ?? ''));
        $dia = trim((string) ($item['dia'] ?? ''));
        $hora = trim((string) ($item['hora'] ?? ''));
        $ubicacion = trim((string) ($item['ubicacion_maps'] ?? $item['ubicacion'] ?? ''));
        if ($titulo === '' && $descripcion === '') {
            continue;
        }
        $resultado[] = [
            'dia' => $dia,
            'hora' => $hora,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'ubicacion_maps' => $ubicacion,
        ];
    }

    return $resultado;
}

function procesarItinerarioFormulario(array $entrada): array
{
    $dias = isset($entrada['dia']) ? (array) $entrada['dia'] : [];
    $horas = isset($entrada['hora']) ? (array) $entrada['hora'] : [];
    $titulos = isset($entrada['titulo']) ? (array) $entrada['titulo'] : [];
    $descripciones = isset($entrada['descripcion']) ? (array) $entrada['descripcion'] : [];
    $ubicaciones = isset($entrada['ubicacion_maps']) ? (array) $entrada['ubicacion_maps'] : [];

    $total = max(count($dias), count($horas), count($titulos), count($descripciones), count($ubicaciones));
    $resultado = [];
    for ($i = 0; $i < $total; $i++) {
        $titulo = trim((string) ($titulos[$i] ?? ''));
        $descripcion = trim((string) ($descripciones[$i] ?? ''));
        $dia = trim((string) ($dias[$i] ?? ''));
        $hora = trim((string) ($horas[$i] ?? ''));
        $ubicacion = trim((string) ($ubicaciones[$i] ?? ''));
        if ($titulo === '' && $descripcion === '') {
            continue;
        }
        $resultado[] = [
            'dia' => $dia,
            'hora' => $hora,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'ubicacion_maps' => $ubicacion,
        ];
    }

    return $resultado;
}

function circuitosCargarRelaciones(\PDO $pdo): array
{
    $relaciones = [];
    $statement = $pdo->query('SELECT circuito_id, destino_id FROM circuito_destinos ORDER BY destino_id');
    foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $fila) {
        $circuitoId = (int) $fila['circuito_id'];
        $destinoId = (int) $fila['destino_id'];
        if ($circuitoId <= 0 || $destinoId <= 0) {
            continue;
        }

        $relaciones[$circuitoId][] = $destinoId;
    }

    foreach ($relaciones as &$destinos) {
        $destinos = array_values(array_unique($destinos));
    }
    unset($destinos);

    return $relaciones;
}

function circuitosObtenerRelacionesPorId(\PDO $pdo, int $circuitoId, ?int $fallback = null): array
{
    $statement = $pdo->prepare('SELECT destino_id FROM circuito_destinos WHERE circuito_id = :id ORDER BY destino_id');
    $statement->execute([':id' => $circuitoId]);
    $destinos = array_map(static fn (array $fila): int => (int) $fila['destino_id'], $statement->fetchAll(\PDO::FETCH_ASSOC) ?: []);

    if (empty($destinos) && $fallback !== null && $fallback > 0) {
        $destinos[] = $fallback;
    }

    return array_values(array_unique(array_filter($destinos, static fn (int $valor): bool => $valor > 0)));
}

function circuitosSincronizarRelaciones(\PDO $pdo, int $circuitoId, array $destinos): void
{
    $pdo->prepare('DELETE FROM circuito_destinos WHERE circuito_id = :id')->execute([':id' => $circuitoId]);

    if (empty($destinos)) {
        return;
    }

    $insert = $pdo->prepare('INSERT INTO circuito_destinos (circuito_id, destino_id) VALUES (:circuito_id, :destino_id)');
    foreach (array_unique(array_map('intval', $destinos)) as $destinoId) {
        if ($destinoId <= 0) {
            continue;
        }

        $insert->execute([
            ':circuito_id' => $circuitoId,
            ':destino_id' => $destinoId,
        ]);
    }
}

function circuitosNormalizarFecha(?string $valor): ?string
{
    $texto = trim((string) $valor);
    if ($texto === '') {
        return null;
    }

    try {
        $fecha = new DateTimeImmutable($texto);

        return $fecha->format('Y-m-d H:i:s');
    } catch (Exception $exception) {
        return null;
    }
}

function circuitosFormatearFechaParaFormulario(?string $valor): string
{
    if ($valor === null || $valor === '') {
        return '';
    }

    try {
        $fecha = new DateTimeImmutable($valor);

        return $fecha->format('Y-m-d\TH:i');
    } catch (Exception $exception) {
        return '';
    }
}

function filtrarServiciosSeleccionados(array $serviciosDisponibles, array $seleccion): array
{
    $validos = [];
    foreach ($seleccion as $id) {
        $id = (int) $id;
        if ($id > 0 && isset($serviciosDisponibles[$id])) {
            $validos[$id] = $id;
        }
    }

    return array_values($validos);
}

function obtenerItinerarioDesdeDb(\PDO $pdo, int $circuitoId): array
{
    $statement = $pdo->prepare('SELECT dia, hora, titulo, descripcion, ubicacion_maps FROM circuito_itinerarios WHERE circuito_id = :id ORDER BY orden, id');
    $statement->execute([':id' => $circuitoId]);
    $filas = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    $resultado = [];
    foreach ($filas as $fila) {
        $titulo = trim((string) ($fila['titulo'] ?? ''));
        $descripcion = trim((string) ($fila['descripcion'] ?? ''));
        if ($titulo === '' && $descripcion === '') {
            continue;
        }
        $resultado[] = [
            'dia' => trim((string) ($fila['dia'] ?? '')),
            'hora' => trim((string) ($fila['hora'] ?? '')),
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'ubicacion_maps' => trim((string) ($fila['ubicacion_maps'] ?? '')),
        ];
    }

    return $resultado;
}

function obtenerServiciosDesdeDb(\PDO $pdo, int $circuitoId): array
{
    $statement = $pdo->prepare(
        'SELECT cs.servicio_id, cs.tipo, sc.nombre, sc.icono, sc.descripcion
         FROM circuito_servicios cs
         JOIN servicios_catalogo sc ON sc.id = cs.servicio_id
         WHERE cs.circuito_id = :id
         ORDER BY cs.tipo, sc.nombre'
    );
    $statement->execute([':id' => $circuitoId]);
    $filas = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    $resultado = [
        'incluido' => ['ids' => [], 'nombres' => [], 'detalles' => []],
        'excluido' => ['ids' => [], 'nombres' => [], 'detalles' => []],
    ];

    foreach ($filas as $fila) {
        $tipo = ($fila['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido';
        $id = (int) ($fila['servicio_id'] ?? 0);
        $nombre = trim((string) ($fila['nombre'] ?? ''));
        $icono = trim((string) ($fila['icono'] ?? ''));
        $descripcion = trim((string) ($fila['descripcion'] ?? ''));
        if ($id <= 0 || $nombre === '') {
            continue;
        }
        $resultado[$tipo]['ids'][] = $id;
        $resultado[$tipo]['nombres'][] = $nombre;
        $resultado[$tipo]['detalles'][] = [
            'id' => $id,
            'nombre' => $nombre,
            'icono' => $icono,
            'descripcion' => $descripcion,
        ];
    }

    return $resultado;
}

/**
 * @param array<int, array<string, mixed>> $detalles
 * @return array<int, array<string, string>>
 */
function transformarServiciosParaPresentacion(array $detalles): array
{
    $resultado = [];

    foreach ($detalles as $detalle) {
        if (is_array($detalle)) {
            $nombre = trim((string) ($detalle['nombre'] ?? $detalle['label'] ?? ''));
            if ($nombre === '') {
                continue;
            }

            $resultado[] = [
                'label' => $nombre,
                'icon' => trim((string) ($detalle['icono'] ?? $detalle['icon'] ?? '')),
                'descripcion' => trim((string) ($detalle['descripcion'] ?? '')),
            ];
        } else {
            $nombre = trim((string) $detalle);
            if ($nombre === '') {
                continue;
            }

            $resultado[] = [
                'label' => $nombre,
                'icon' => '',
                'descripcion' => '',
            ];
        }
    }

    return $resultado;
}

function sincronizarItinerarioCircuito(\PDO $pdo, int $circuitoId, array $itinerario): void
{
    $pdo->prepare('DELETE FROM circuito_itinerarios WHERE circuito_id = :id')->execute([':id' => $circuitoId]);

    if (empty($itinerario)) {
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO circuito_itinerarios (circuito_id, orden, dia, hora, titulo, descripcion, ubicacion_maps)
         VALUES (:circuito_id, :orden, :dia, :hora, :titulo, :descripcion, :ubicacion)'
    );

    $orden = 1;
    foreach ($itinerario as $item) {
        $titulo = trim((string) ($item['titulo'] ?? ''));
        $descripcion = trim((string) ($item['descripcion'] ?? ''));
        if ($titulo === '' && $descripcion === '') {
            continue;
        }
        $insert->execute([
            ':circuito_id' => $circuitoId,
            ':orden' => $orden++,
            ':dia' => ($item['dia'] ?? '') !== '' ? $item['dia'] : null,
            ':hora' => ($item['hora'] ?? '') !== '' ? $item['hora'] : null,
            ':titulo' => $titulo,
            ':descripcion' => $descripcion !== '' ? $descripcion : null,
            ':ubicacion' => ($item['ubicacion_maps'] ?? '') !== '' ? $item['ubicacion_maps'] : null,
        ]);
    }
}

function sincronizarServiciosCircuito(\PDO $pdo, int $circuitoId, array $serviciosPorTipo): void
{
    $pdo->prepare('DELETE FROM circuito_servicios WHERE circuito_id = :id')->execute([':id' => $circuitoId]);

    $insert = $pdo->prepare(
        'INSERT INTO circuito_servicios (circuito_id, servicio_id, tipo)
         VALUES (:circuito_id, :servicio_id, :tipo)'
    );

    foreach (['incluido', 'excluido'] as $tipo) {
        $ids = array_values(array_unique(array_map('intval', $serviciosPorTipo[$tipo] ?? [])));
        foreach ($ids as $id) {
            if ($id <= 0) {
                continue;
            }
            $insert->execute([
                ':circuito_id' => $circuitoId,
                ':servicio_id' => $id,
                ':tipo' => $tipo,
            ]);
        }
    }
}

function obtenerNombresServiciosPorIds(\PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(', ', array_fill(0, count($ids), '?'));
    $statement = $pdo->prepare("SELECT id, nombre FROM servicios_catalogo WHERE id IN ($placeholders)");
    $statement->execute($ids);
    $filas = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    $mapa = [];
    foreach ($filas as $fila) {
        $mapa[(int) $fila['id']] = trim((string) ($fila['nombre'] ?? ''));
    }

    $resultado = [];
    foreach ($ids as $id) {
        if (($mapa[$id] ?? '') !== '') {
            $resultado[] = $mapa[$id];
        }
    }

    return $resultado;
}
