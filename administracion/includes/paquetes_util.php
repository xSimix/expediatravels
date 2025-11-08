<?php

declare(strict_types=1);

use Aplicacion\BaseDatos\Conexion;

function paquetesCargarDestinos(array $predeterminados, array &$errores): array
{
    $destinos = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query('SELECT id, nombre, region FROM destinos ORDER BY nombre');
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $destino) {
            $destinos[(int) $destino['id']] = paquetesNormalizarDestino($destino);
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudieron cargar los destinos asociados. Se usarán los de referencia.';
    }

    if (empty($destinos)) {
        foreach ($predeterminados as $destino) {
            $normalizado = paquetesNormalizarDestino($destino);
            $destinos[$normalizado['id']] = $normalizado;
        }
    }

    ksort($destinos);

    return $destinos;
}

function paquetesNormalizarDestino(array $destino): array
{
    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
    ];
}

function paquetesCargarCircuitos(array $predeterminados, array $destinos, array &$errores): array
{
    $circuitos = [];

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query('SELECT id, nombre, duracion, destino_id FROM circuitos ORDER BY nombre');
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $circuito) {
            $id = (int) ($circuito['id'] ?? 0);
            $destinoId = isset($circuito['destino_id']) ? (int) $circuito['destino_id'] : null;
            $circuitos[$id] = [
                'id' => $id,
                'nombre' => trim((string) ($circuito['nombre'] ?? '')),
                'duracion' => trim((string) ($circuito['duracion'] ?? '')),
                'destino_id' => $destinoId,
                'destino_nombre' => $destinoId !== null ? ($destinos[$destinoId]['nombre'] ?? '') : '',
            ];
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudieron cargar los circuitos. Se usarán los de referencia.';
    }

    if (empty($circuitos)) {
        foreach ($predeterminados as $circuito) {
            $id = (int) ($circuito['id'] ?? 0);
            $destinoId = isset($circuito['destino_id']) ? (int) $circuito['destino_id'] : null;
            $circuitos[$id] = [
                'id' => $id,
                'nombre' => trim((string) ($circuito['nombre'] ?? '')),
                'duracion' => trim((string) ($circuito['duracion'] ?? '')),
                'destino_id' => $destinoId,
                'destino_nombre' => $destinoId !== null ? ($destinos[$destinoId]['nombre'] ?? '') : '',
            ];
        }
    }

    ksort($circuitos);

    return $circuitos;
}

function paquetesCargarPaquetes(array $predeterminados, array &$errores): array
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->query('SELECT * FROM paquetes ORDER BY nombre');
        $paquetes = $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        if (empty($paquetes)) {
            $predeterminadosNormalizados = array_map(static function (array $paquete): array {
                $paquete['es_predeterminado'] = true;
                return paquetesNormalizarPaquete($paquete);
            }, $predeterminados);

            return paquetesOrdenarPaquetes($predeterminadosNormalizados);
        }

        $destinosRelacionados = paquetesCargarRelaciones($pdo, 'paquete_destinos', 'destino_id');
        $circuitosRelacionados = paquetesCargarRelaciones($pdo, 'paquete_circuitos', 'circuito_id');

        $normalizados = array_map(
            static function (array $paquete) use ($destinosRelacionados, $circuitosRelacionados): array {
                $identificador = (int) ($paquete['id'] ?? 0);
                $paquete['destinos'] = $destinosRelacionados[$identificador] ?? (
                    isset($paquete['destino_id']) ? [(int) $paquete['destino_id']] : []
                );
                $paquete['circuitos'] = $circuitosRelacionados[$identificador] ?? [];

                return paquetesNormalizarPaquete($paquete);
            },
            $paquetes
        );

        return paquetesOrdenarPaquetes($normalizados);
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de paquetes. Se muestran los paquetes de referencia.';
    }

    $predeterminadosNormalizados = array_map(static function (array $paquete): array {
        $paquete['es_predeterminado'] = true;
        return paquetesNormalizarPaquete($paquete);
    }, $predeterminados);

    return paquetesOrdenarPaquetes($predeterminadosNormalizados);
}

function paquetesNormalizarPaquete(array $paquete): array
{
    $estado = strtolower(trim((string) ($paquete['estado'] ?? 'borrador')));
    if (!in_array($estado, ['publicado', 'borrador', 'agotado', 'inactivo'], true)) {
        $estado = 'borrador';
    }

    $galeria = paquetesDecodificarLista($paquete['galeria'] ?? []);
    $beneficios = paquetesDecodificarLista($paquete['beneficios'] ?? []);
    $incluye = paquetesDecodificarLista($paquete['incluye'] ?? []);
    $noIncluye = paquetesDecodificarLista($paquete['no_incluye'] ?? []);
    $salidas = paquetesDecodificarLista($paquete['salidas'] ?? []);

    $resultado = [
        'id' => (int) ($paquete['id'] ?? 0),
        'nombre' => trim((string) ($paquete['nombre'] ?? '')),
        'estado' => $estado,
        'duracion' => trim((string) ($paquete['duracion'] ?? '')),
        'precio_desde' => isset($paquete['precio']) ? (float) $paquete['precio'] : null,
        'moneda' => strtoupper(trim((string) ($paquete['moneda'] ?? 'PEN'))),
        'descripcion_breve' => trim((string) ($paquete['resumen'] ?? $paquete['descripcion_breve'] ?? '')),
        'descripcion_detallada' => trim((string) ($paquete['itinerario'] ?? $paquete['descripcion_detallada'] ?? '')),
        'beneficios' => $beneficios,
        'incluye' => $incluye,
        'no_incluye' => $noIncluye,
        'salidas' => $salidas,
        'cupos_min' => isset($paquete['cupos_min']) ? (int) $paquete['cupos_min'] : null,
        'cupos_max' => isset($paquete['cupos_max']) ? (int) $paquete['cupos_max'] : null,
        'destinos' => array_values(array_map('intval', (array) ($paquete['destinos'] ?? []))),
        'circuitos' => array_values(array_map('intval', (array) ($paquete['circuitos'] ?? []))),
        'imagen_portada' => trim((string) ($paquete['imagen_portada'] ?? '')),
        'imagen_destacada' => trim((string) ($paquete['imagen_destacada'] ?? '')),
        'galeria' => $galeria,
        'video_destacado_url' => trim((string) ($paquete['video_destacado_url'] ?? $paquete['video_destacado'] ?? '')),
        'actualizado_en' => $paquete['actualizado_en'] ?? null,
    ];

    if (array_key_exists('es_predeterminado', $paquete)) {
        $resultado['es_predeterminado'] = (bool) $paquete['es_predeterminado'];
    }

    return $resultado;
}

function paquetesOrdenarPaquetes(array $paquetes): array
{
    usort($paquetes, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($paquetes);
}

function paquetesCrearPaquete(array $paquete, array &$errores): ?int
{
    try {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO paquetes (destino_id, nombre, resumen, itinerario, duracion, precio, moneda, estado, imagen_portada, imagen_destacada, galeria, video_destacado_url, beneficios, incluye, no_incluye, salidas, cupos_min, cupos_max)
             VALUES (:destino_id, :nombre, :resumen, :itinerario, :duracion, :precio, :moneda, :estado, :imagen_portada, :imagen_destacada, :galeria, :video, :beneficios, :incluye, :no_incluye, :salidas, :cupos_min, :cupos_max)'
        );
        $destinoPrincipal = $paquete['destinos'][0] ?? null;
        $statement->execute([
            ':destino_id' => $destinoPrincipal !== null ? $destinoPrincipal : null,
            ':nombre' => $paquete['nombre'],
            ':resumen' => $paquete['descripcion_breve'],
            ':itinerario' => $paquete['descripcion_detallada'],
            ':duracion' => $paquete['duracion'],
            ':precio' => $paquete['precio_desde'],
            ':moneda' => $paquete['moneda'],
            ':estado' => $paquete['estado'],
            ':imagen_portada' => $paquete['imagen_portada'] !== '' ? $paquete['imagen_portada'] : null,
            ':imagen_destacada' => $paquete['imagen_destacada'] !== '' ? $paquete['imagen_destacada'] : null,
            ':galeria' => paquetesPrepararJsonLista($paquete['galeria']),
            ':video' => $paquete['video_destacado_url'] !== '' ? $paquete['video_destacado_url'] : null,
            ':beneficios' => paquetesPrepararJsonLista($paquete['beneficios']),
            ':incluye' => paquetesPrepararJsonLista($paquete['incluye']),
            ':no_incluye' => paquetesPrepararJsonLista($paquete['no_incluye']),
            ':salidas' => paquetesPrepararJsonLista($paquete['salidas']),
            ':cupos_min' => $paquete['cupos_min'],
            ':cupos_max' => $paquete['cupos_max'],
        ]);

        $paqueteId = (int) $pdo->lastInsertId();
        paquetesSincronizarRelaciones($pdo, $paqueteId, 'paquete_destinos', 'destino_id', $paquete['destinos']);
        paquetesSincronizarRelaciones($pdo, $paqueteId, 'paquete_circuitos', 'circuito_id', $paquete['circuitos']);

        $pdo->commit();

        return $paqueteId;
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errores[] = 'No se pudo guardar el paquete en la base de datos.';
    }

    return null;
}

function paquetesActualizarPaquete(int $paqueteId, array $paquete, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'UPDATE paquetes
             SET destino_id = :destino_id,
                 nombre = :nombre,
                 resumen = :resumen,
                 itinerario = :itinerario,
                 duracion = :duracion,
                 precio = :precio,
                 moneda = :moneda,
                 estado = :estado,
                 imagen_portada = :imagen_portada,
                 imagen_destacada = :imagen_destacada,
                 galeria = :galeria,
                 video_destacado_url = :video,
                 beneficios = :beneficios,
                 incluye = :incluye,
                 no_incluye = :no_incluye,
                 salidas = :salidas,
                 cupos_min = :cupos_min,
                 cupos_max = :cupos_max
             WHERE id = :id'
        );
        $destinoPrincipal = $paquete['destinos'][0] ?? null;
        $statement->execute([
            ':id' => $paqueteId,
            ':destino_id' => $destinoPrincipal !== null ? $destinoPrincipal : null,
            ':nombre' => $paquete['nombre'],
            ':resumen' => $paquete['descripcion_breve'],
            ':itinerario' => $paquete['descripcion_detallada'],
            ':duracion' => $paquete['duracion'],
            ':precio' => $paquete['precio_desde'],
            ':moneda' => $paquete['moneda'],
            ':estado' => $paquete['estado'],
            ':imagen_portada' => $paquete['imagen_portada'] !== '' ? $paquete['imagen_portada'] : null,
            ':imagen_destacada' => $paquete['imagen_destacada'] !== '' ? $paquete['imagen_destacada'] : null,
            ':galeria' => paquetesPrepararJsonLista($paquete['galeria']),
            ':video' => $paquete['video_destacado_url'] !== '' ? $paquete['video_destacado_url'] : null,
            ':beneficios' => paquetesPrepararJsonLista($paquete['beneficios']),
            ':incluye' => paquetesPrepararJsonLista($paquete['incluye']),
            ':no_incluye' => paquetesPrepararJsonLista($paquete['no_incluye']),
            ':salidas' => paquetesPrepararJsonLista($paquete['salidas']),
            ':cupos_min' => $paquete['cupos_min'],
            ':cupos_max' => $paquete['cupos_max'],
        ]);

        paquetesSincronizarRelaciones($pdo, $paqueteId, 'paquete_destinos', 'destino_id', $paquete['destinos']);
        paquetesSincronizarRelaciones($pdo, $paqueteId, 'paquete_circuitos', 'circuito_id', $paquete['circuitos']);

        $pdo->commit();

        return true;
    } catch (\PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errores[] = 'No se pudo actualizar el paquete en la base de datos.';
    }

    return false;
}

function paquetesEliminarPaquete(int $paqueteId, array &$errores): bool
{
    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare('DELETE FROM paquetes WHERE id = :id');
        $statement->execute([':id' => $paqueteId]);

        return $statement->rowCount() > 0;
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo eliminar el paquete en la base de datos.';
    }

    return false;
}

function paquetesObtenerPorId(int $paqueteId, array $predeterminados, array &$errores): ?array
{
    if ($paqueteId <= 0) {
        return null;
    }

    try {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare('SELECT * FROM paquetes WHERE id = :id');
        $statement->execute([':id' => $paqueteId]);
        $paquete = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($paquete !== false) {
            $paquete['destinos'] = paquetesObtenerRelacionesPorId($pdo, 'paquete_destinos', 'destino_id', $paqueteId, isset($paquete['destino_id']) ? (int) $paquete['destino_id'] : null);
            $paquete['circuitos'] = paquetesObtenerRelacionesPorId($pdo, 'paquete_circuitos', 'circuito_id', $paqueteId);
            $normalizado = paquetesNormalizarPaquete($paquete);
            $normalizado['es_predeterminado'] = false;

            return $normalizado;
        }
    } catch (\PDOException $exception) {
        $errores[] = 'No se pudo obtener el paquete desde la base de datos.';
    }

    foreach ($predeterminados as $predeterminado) {
        if ((int) ($predeterminado['id'] ?? 0) === $paqueteId) {
            $predeterminado['es_predeterminado'] = true;

            return paquetesNormalizarPaquete($predeterminado);
        }
    }

    return null;
}

function paquetesCargarRelaciones(\PDO $pdo, string $tabla, string $campo): array
{
    $relaciones = [];
    $statement = $pdo->query("SELECT paquete_id, {$campo} AS valor FROM {$tabla}");
    foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $fila) {
        $paqueteId = (int) $fila['paquete_id'];
        $relaciones[$paqueteId][] = (int) $fila['valor'];
    }

    return $relaciones;
}

function paquetesObtenerRelacionesPorId(\PDO $pdo, string $tabla, string $campo, int $paqueteId, ?int $fallback = null): array
{
    $statement = $pdo->prepare("SELECT {$campo} AS valor FROM {$tabla} WHERE paquete_id = :paquete_id");
    $statement->execute([':paquete_id' => $paqueteId]);
    $valores = array_map(static fn (array $fila): int => (int) $fila['valor'], $statement->fetchAll(\PDO::FETCH_ASSOC) ?: []);

    if (empty($valores) && $fallback !== null) {
        $valores[] = $fallback;
    }

    return array_values($valores);
}

function paquetesSincronizarRelaciones(\PDO $pdo, int $paqueteId, string $tabla, string $campo, array $valores): void
{
    $pdo->prepare("DELETE FROM {$tabla} WHERE paquete_id = :paquete_id")->execute([':paquete_id' => $paqueteId]);

    if (empty($valores)) {
        return;
    }

    $insert = $pdo->prepare("INSERT INTO {$tabla} (paquete_id, {$campo}) VALUES (:paquete_id, :valor)");
    foreach ($valores as $valor) {
        $insert->execute([
            ':paquete_id' => $paqueteId,
            ':valor' => $valor,
        ]);
    }
}

function paquetesDecodificarLista($valor): array
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

function paquetesPrepararJsonLista(array $valores): ?string
{
    if (empty($valores)) {
        return null;
    }

    return json_encode(array_values($valores), JSON_UNESCAPED_UNICODE);
}

function paquetesParsearPrecio($valor, array &$errores): ?float
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_string($valor)) {
        $limpio = str_replace(',', '.', trim($valor));
        if ($limpio === '') {
            return null;
        }
        if (!is_numeric($limpio)) {
            $errores[] = 'El precio debe ser un número válido. Ejemplo: 599.00';
            return null;
        }
        return (float) $limpio;
    }

    if (is_numeric($valor)) {
        return (float) $valor;
    }

    $errores[] = 'El precio debe ser un número válido.';

    return null;
}

function paquetesParsearEntero($valor): ?int
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        return (int) $valor;
    }

    return null;
}

function paquetesConvertirIds($valor): array
{
    if (is_array($valor)) {
        $items = $valor;
    } else {
        $items = preg_split('/[,\s]+/', (string) $valor) ?: [];
    }

    return array_values(array_filter(array_map('intval', $items), static fn (int $id): bool => $id > 0));
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

function estadoPaqueteEtiqueta(string $estado): string
{
    return match ($estado) {
        'publicado' => 'Publicado',
        'agotado' => 'Agotado',
        'inactivo' => 'Inactivo',
        default => 'Borrador',
    };
}

function paquetesFormatearPrecioDesde(?float $precio, string $moneda, array $monedasPermitidas): string
{
    if ($precio === null) {
        return 'A consultar';
    }

    $simbolo = $monedasPermitidas[$moneda] ?? $moneda;

    return sprintf('%s %.2f', $simbolo, $precio);
}

function paquetesFormatearCupos(?int $minimo, ?int $maximo): string
{
    if ($minimo === null && $maximo === null) {
        return '—';
    }

    if ($minimo !== null && $maximo !== null) {
        return sprintf('%d - %d viajeros', $minimo, $maximo);
    }

    if ($minimo !== null) {
        return sprintf('Mín. %d viajeros', $minimo);
    }

    return sprintf('Máx. %d viajeros', $maximo);
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
