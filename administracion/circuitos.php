<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';

$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($archivoDestinos, $destinosPredeterminados, $errores);
$circuitos = cargarCircuitos($archivoCircuitos, $circuitosPredeterminados, $destinosDisponibles, $errores);

$categoriasPermitidas = [
    'naturaleza' => 'Naturaleza y aire libre',
    'cultural' => 'Cultural e histórico',
    'aventura' => 'Aventura y adrenalina',
    'gastronomico' => 'Gastronómico',
    'bienestar' => 'Bienestar y relajación',
];

$dificultadesPermitidas = [
    'relajado' => 'Relajado',
    'moderado' => 'Moderado',
    'intenso' => 'Intenso',
];

$estadosPermitidos = [
    'activo' => 'Activo',
    'borrador' => 'Borrador',
    'inactivo' => 'Inactivo',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'create') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $destinoId = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
        $destinoPersonalizado = trim((string) ($_POST['destino_personalizado'] ?? ''));
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $categoria = strtolower(trim((string) ($_POST['categoria'] ?? 'naturaleza')));
        $dificultad = strtolower(trim((string) ($_POST['dificultad'] ?? 'relajado')));
        $frecuencia = trim((string) ($_POST['frecuencia'] ?? ''));
        $estado = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $puntosInteres = convertirListado($_POST['puntos_interes'] ?? '');
        $serviciosIncluidos = convertirListado($_POST['servicios'] ?? '');

        if ($nombre === '') {
            $errores[] = 'Debes indicar el nombre del circuito.';
        }

        if ($destinoId <= 0 && $destinoPersonalizado === '') {
            $errores[] = 'Selecciona un destino o escribe uno personalizado.';
        }

        if ($duracion === '') {
            $errores[] = 'Define la duración del circuito (por ejemplo: Full day, 2 días / 1 noche).';
        }

        if (!array_key_exists($categoria, $categoriasPermitidas)) {
            $errores[] = 'La categoría seleccionada no es válida.';
        }

        if (!array_key_exists($dificultad, $dificultadesPermitidas)) {
            $errores[] = 'La dificultad seleccionada no es válida.';
        }

        if (!array_key_exists($estado, $estadosPermitidos)) {
            $errores[] = 'El estado seleccionado no es válido.';
        }

        $destinoNombre = $destinoPersonalizado;
        $destinoRegion = '';
        if ($destinoId > 0) {
            $destinoNombre = $destinosDisponibles[$destinoId]['nombre'] ?? $destinoNombre;
            $destinoRegion = $destinosDisponibles[$destinoId]['region'] ?? '';
        }

        if (empty($errores)) {
            $nuevoCircuito = [
                'id' => obtenerSiguienteId($circuitos),
                'nombre' => $nombre,
                'destino' => [
                    'id' => $destinoId > 0 ? $destinoId : null,
                    'nombre' => $destinoNombre,
                    'personalizado' => $destinoPersonalizado,
                    'region' => $destinoRegion,
                ],
                'duracion' => $duracion,
                'categoria' => $categoria,
                'dificultad' => $dificultad,
                'frecuencia' => $frecuencia,
                'descripcion' => $descripcion,
                'puntos_interes' => $puntosInteres,
                'servicios' => $serviciosIncluidos,
                'estado' => $estado,
                'actualizado_en' => date('c'),
            ];

            $circuitos[] = normalizarCircuito($nuevoCircuito, $destinosDisponibles);
            $circuitos = ordenarCircuitos($circuitos);

            try {
                ServicioAlmacenamientoJson::guardar($archivoCircuitos, $circuitos);
                $mensajeExito = 'Circuito creado correctamente.';
            } catch (\RuntimeException $exception) {
                $errores[] = 'El circuito se creó, pero no se pudo guardar en almacenamiento.';
            }
        }
    } elseif ($accion === 'update') {
        $circuitoId = isset($_POST['circuito_id']) ? (int) $_POST['circuito_id'] : 0;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $destinoId = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
        $destinoPersonalizado = trim((string) ($_POST['destino_personalizado'] ?? ''));
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $categoria = strtolower(trim((string) ($_POST['categoria'] ?? 'naturaleza')));
        $dificultad = strtolower(trim((string) ($_POST['dificultad'] ?? 'relajado')));
        $frecuencia = trim((string) ($_POST['frecuencia'] ?? ''));
        $estado = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $puntosInteres = convertirListado($_POST['puntos_interes'] ?? '');
        $serviciosIncluidos = convertirListado($_POST['servicios'] ?? '');

        if ($circuitoId <= 0) {
            $errores[] = 'No se pudo identificar el circuito a actualizar.';
        }

        if ($nombre === '') {
            $errores[] = 'El nombre del circuito es obligatorio.';
        }

        if ($destinoId <= 0 && $destinoPersonalizado === '') {
            $errores[] = 'Selecciona un destino o indica uno personalizado.';
        }

        if ($duracion === '') {
            $errores[] = 'La duración es obligatoria.';
        }

        if (!array_key_exists($categoria, $categoriasPermitidas)) {
            $errores[] = 'La categoría indicada no es válida.';
        }

        if (!array_key_exists($dificultad, $dificultadesPermitidas)) {
            $errores[] = 'La dificultad indicada no es válida.';
        }

        if (!array_key_exists($estado, $estadosPermitidos)) {
            $errores[] = 'El estado indicado no es válido.';
        }

        $destinoNombre = $destinoPersonalizado;
        $destinoRegion = '';
        if ($destinoId > 0) {
            $destinoNombre = $destinosDisponibles[$destinoId]['nombre'] ?? $destinoNombre;
            $destinoRegion = $destinosDisponibles[$destinoId]['region'] ?? '';
        }

        if (empty($errores)) {
            $actualizado = false;
            foreach ($circuitos as &$circuito) {
                if ($circuito['id'] === $circuitoId) {
                    $circuito['nombre'] = $nombre;
                    $circuito['destino'] = [
                        'id' => $destinoId > 0 ? $destinoId : null,
                        'nombre' => $destinoNombre,
                        'personalizado' => $destinoPersonalizado,
                        'region' => $destinoRegion,
                    ];
                    $circuito['duracion'] = $duracion;
                    $circuito['categoria'] = $categoria;
                    $circuito['dificultad'] = $dificultad;
                    $circuito['frecuencia'] = $frecuencia;
                    $circuito['descripcion'] = $descripcion;
                    $circuito['puntos_interes'] = $puntosInteres;
                    $circuito['servicios'] = $serviciosIncluidos;
                    $circuito['estado'] = $estado;
                    $circuito['actualizado_en'] = date('c');
                    $actualizado = true;
                    break;
                }
            }
            unset($circuito);

            if ($actualizado) {
                $circuitos = ordenarCircuitos($circuitos);
                try {
                    ServicioAlmacenamientoJson::guardar($archivoCircuitos, $circuitos);
                    $mensajeExito = 'Circuito actualizado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'Los cambios se aplicaron, pero no se pudieron guardar.';
                }
            } else {
                $errores[] = 'No se encontró el circuito indicado.';
            }
        }
    } elseif ($accion === 'delete') {
        $circuitoId = isset($_POST['circuito_id']) ? (int) $_POST['circuito_id'] : 0;
        if ($circuitoId <= 0) {
            $errores[] = 'No se pudo identificar el circuito a eliminar.';
        } else {
            $cantidadInicial = count($circuitos);
            $circuitos = array_values(array_filter(
                $circuitos,
                static fn (array $circuito): bool => $circuito['id'] !== $circuitoId
            ));

            if ($cantidadInicial === count($circuitos)) {
                $errores[] = 'El circuito indicado ya no existe.';
            } else {
                try {
                    ServicioAlmacenamientoJson::guardar($archivoCircuitos, $circuitos);
                    $mensajeExito = 'Circuito eliminado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'El circuito se eliminó, pero no se pudo actualizar el almacenamiento.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if ($mensajeExito === null && empty($errores) && !is_file($archivoCircuitos)) {
    $mensajeInfo = 'Aún no tienes circuitos guardados. Empieza creando el primero para agrupar tus experiencias.';
}

$paginaActiva = 'circuitos';
$tituloPagina = 'Circuitos — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Gestión de circuitos</h1>
        <p>Diseña rutas temáticas dentro de cada destino para reutilizarlas en paquetes y campañas.</p>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>Detectamos algunos problemas:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($mensajeExito !== null): ?>
        <div class="admin-alert">
            <?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php elseif ($mensajeInfo !== null): ?>
        <div class="admin-alert">
            <?= htmlspecialchars($mensajeInfo, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Crear nuevo circuito</h2>
        <form method="post" class="admin-grid">
            <input type="hidden" name="action" value="create" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del circuito *</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Circuito Oxapampa Clásico" required />
                </div>
                <div class="admin-field">
                    <label for="destino_id">Destino asociado *</label>
                    <select id="destino_id" name="destino_id">
                        <option value="0">Multi-destino o personalizado</option>
                        <?php foreach ($destinosDisponibles as $destino): ?>
                            <option value="<?= (int) $destino['id']; ?>"><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="admin-field">
                <label for="destino_personalizado">Destino personalizado</label>
                <input type="text" id="destino_personalizado" name="destino_personalizado" placeholder="Selva Central - Ruta cafetalera" />
                <p class="admin-help">Utilízalo cuando el circuito abarque varios destinos o sea una zona especial.</p>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="duracion">Duración *</label>
                    <input type="text" id="duracion" name="duracion" placeholder="Full day" required />
                </div>
                <div class="admin-field">
                    <label for="frecuencia">Frecuencia o días de salida</label>
                    <input type="text" id="frecuencia" name="frecuencia" placeholder="Salidas diarias" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categoriasPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="dificultad">Dificultad</label>
                    <select id="dificultad" name="dificultad">
                        <?php foreach ($dificultadesPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="admin-field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Resume el concepto del circuito, actividades y público objetivo."></textarea>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="puntos_interes">Puntos de interés</label>
                    <textarea id="puntos_interes" name="puntos_interes" rows="4" placeholder="Tunqui Cueva&#10;El Wharapo&#10;Catarata Río Tigre"></textarea>
                    <p class="admin-help">Escribe un punto por línea.</p>
                </div>
                <div class="admin-field">
                    <label for="servicios">Servicios incluidos</label>
                    <textarea id="servicios" name="servicios" rows="4" placeholder="Transporte turístico&#10;Guía oficial&#10;Entradas"></textarea>
                    <p class="admin-help">Incluye los servicios estándar que se repiten en el circuito.</p>
                </div>
            </div>
            <div class="admin-field">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                        <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar circuito</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Circuitos registrados</h2>
        <?php if (empty($circuitos)): ?>
            <p class="admin-help">Aún no hay circuitos registrados. Los circuitos se reutilizan en paquetes y catálogos temáticos.</p>
        <?php else: ?>
            <div class="admin-grid" style="gap: 1.25rem;">
                <?php foreach ($circuitos as $circuito): ?>
                    <article class="admin-circuit">
                        <header class="admin-circuit__header">
                            <div>
                                <h3><?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="admin-circuit__meta">
                                    <?= htmlspecialchars(obtenerNombreDestinoCircuito($circuito), ENT_QUOTES, 'UTF-8'); ?> ·
                                    <?= htmlspecialchars(mostrarDuracionCircuito($circuito['duracion']), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <span class="admin-circuit__badge admin-circuit__badge--<?= htmlspecialchars($circuito['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= estadoCircuitoEtiqueta($circuito['estado']); ?>
                            </span>
                        </header>
                        <ul class="admin-circuit__tags">
                            <li><?= htmlspecialchars(categoriaCircuitoEtiqueta($circuito['categoria']), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?= htmlspecialchars(dificultadCircuitoEtiqueta($circuito['dificultad']), ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php if ($circuito['frecuencia'] !== ''): ?>
                                <li><?= htmlspecialchars($circuito['frecuencia'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($circuito['descripcion'] !== ''): ?>
                            <p class="admin-circuit__description"><?= nl2br(htmlspecialchars($circuito['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php endif; ?>
                        <div class="admin-circuit__grid">
                            <?php if (!empty($circuito['puntos_interes'])): ?>
                                <section>
                                    <h4>Puntos de interés</h4>
                                    <ul>
                                        <?php foreach ($circuito['puntos_interes'] as $punto): ?>
                                            <li><?= htmlspecialchars($punto, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endif; ?>
                            <?php if (!empty($circuito['servicios'])): ?>
                                <section>
                                    <h4>Servicios incluidos</h4>
                                    <ul>
                                        <?php foreach ($circuito['servicios'] as $servicio): ?>
                                            <li><?= htmlspecialchars($servicio, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endif; ?>
                        </div>
                        <p class="admin-circuit__timestamp">Actualizado <?= htmlspecialchars(formatearMarcaTiempo($circuito['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></p>

                        <details class="admin-circuit__editor">
                            <summary>Editar circuito</summary>
                            <form method="post" class="admin-grid">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="circuito_id" value="<?= (int) $circuito['id']; ?>" />
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Nombre</label>
                                        <input type="text" name="nombre" required value="<?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Destino</label>
                                        <select name="destino_id">
                                            <option value="0" <?= $circuito['destino']['id'] === null ? 'selected' : ''; ?>>Multi-destino o personalizado</option>
                                            <?php foreach ($destinosDisponibles as $destino): ?>
                                                <option value="<?= (int) $destino['id']; ?>" <?= $circuito['destino']['id'] === (int) $destino['id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="admin-field">
                                    <label>Destino personalizado</label>
                                    <input type="text" name="destino_personalizado" value="<?= htmlspecialchars($circuito['destino']['personalizado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Duración</label>
                                        <input type="text" name="duracion" value="<?= htmlspecialchars($circuito['duracion'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Frecuencia</label>
                                        <input type="text" name="frecuencia" value="<?= htmlspecialchars($circuito['frecuencia'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Categoría</label>
                                        <select name="categoria">
                                            <?php foreach ($categoriasPermitidas as $clave => $etiqueta): ?>
                                                <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $circuito['categoria'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="admin-field">
                                        <label>Dificultad</label>
                                        <select name="dificultad">
                                            <?php foreach ($dificultadesPermitidas as $clave => $etiqueta): ?>
                                                <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $circuito['dificultad'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="admin-field">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="4"><?= htmlspecialchars($circuito['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Puntos de interés</label>
                                        <textarea name="puntos_interes" rows="4"><?= htmlspecialchars(implode(PHP_EOL, $circuito['puntos_interes']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="admin-field">
                                        <label>Servicios incluidos</label>
                                        <textarea name="servicios" rows="4"><?= htmlspecialchars(implode(PHP_EOL, $circuito['servicios']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                                <div class="admin-field">
                                    <label>Estado</label>
                                    <select name="estado">
                                        <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $circuito['estado'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="admin-actions">
                                    <button type="submit" class="admin-button">Actualizar circuito</button>
                                </div>
                            </form>
                        </details>

                        <form method="post" class="admin-circuit__delete" onsubmit="return confirm('¿Eliminar el circuito <?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?>?');">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="circuito_id" value="<?= (int) $circuito['id']; ?>" />
                            <button type="submit" class="admin-button secondary">Eliminar</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/plantilla/pie.php'; ?>

<?php
/**
 * @param string $archivoDestinos
 * @param array<int, array<string, mixed>> $predeterminados
 * @param array<int, string> $errores
 * @return array<int, array<string, string|int>>
 */
function cargarDestinosDisponibles(string $archivoDestinos, array $predeterminados, array &$errores): array
{
    try {
        $destinos = ServicioAlmacenamientoJson::leer($archivoDestinos, $predeterminados);
    } catch (\RuntimeException $exception) {
        $errores[] = 'No se pudo cargar la lista de destinos guardados. Se usarán los valores de referencia.';
        $destinos = $predeterminados;
    }

    $resultado = [];
    foreach ($destinos as $destino) {
        $id = (int) ($destino['id'] ?? 0);
        $nombre = trim((string) ($destino['nombre'] ?? ''));
        if ($id <= 0 || $nombre === '') {
            continue;
        }

        $resultado[$id] = [
            'id' => $id,
            'nombre' => $nombre,
            'region' => trim((string) ($destino['region'] ?? '')),
        ];
    }

    ksort($resultado);

    return $resultado;
}

/**
 * @param string $archivo
 * @param array<int, array<string, mixed>> $predeterminados
 * @param array<int, array<string, string|int>> $destinosDisponibles
 * @param array<int, string> $errores
 * @return array<int, array<string, mixed>>
 */
function cargarCircuitos(string $archivo, array $predeterminados, array $destinosDisponibles, array &$errores): array
{
    try {
        $circuitos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (\RuntimeException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de circuitos. Se muestran los circuitos de referencia.';
        $circuitos = $predeterminados;
    }

    $normalizados = array_map(
        static fn (array $circuito): array => normalizarCircuito($circuito, $destinosDisponibles),
        $circuitos
    );

    return ordenarCircuitos($normalizados);
}

/**
 * @param array<string, mixed> $circuito
 * @param array<int, array<string, string|int>> $destinos
 * @return array<string, mixed>
 */
function normalizarCircuito(array $circuito, array $destinos): array
{
    $destino = $circuito['destino'] ?? [];
    $destinoId = isset($destino['id']) ? (int) $destino['id'] : (isset($circuito['destino_id']) ? (int) $circuito['destino_id'] : 0);
    $destinoNombre = trim((string) ($destino['nombre'] ?? ($circuito['destino_nombre'] ?? '')));
    $destinoPersonalizado = trim((string) ($destino['personalizado'] ?? ($circuito['destino_personalizado'] ?? '')));
    $destinoRegion = trim((string) ($destino['region'] ?? ''));

    if ($destinoId > 0) {
        $destinoNombre = $destinos[$destinoId]['nombre'] ?? ($destinoNombre !== '' ? $destinoNombre : '');
        if ($destinoRegion === '') {
            $destinoRegion = $destinos[$destinoId]['region'] ?? '';
        }
    }

    $categoria = strtolower(trim((string) ($circuito['categoria'] ?? 'naturaleza')));
    $dificultad = strtolower(trim((string) ($circuito['dificultad'] ?? 'relajado')));
    $estado = strtolower(trim((string) ($circuito['estado'] ?? 'borrador')));

    return [
        'id' => (int) ($circuito['id'] ?? 0),
        'nombre' => trim((string) ($circuito['nombre'] ?? '')),
        'destino' => [
            'id' => $destinoId > 0 ? $destinoId : null,
            'nombre' => $destinoNombre,
            'personalizado' => $destinoPersonalizado,
            'region' => $destinoRegion,
        ],
        'duracion' => trim((string) ($circuito['duracion'] ?? '')),
        'categoria' => $categoria,
        'dificultad' => $dificultad,
        'frecuencia' => trim((string) ($circuito['frecuencia'] ?? '')),
        'descripcion' => trim((string) ($circuito['descripcion'] ?? '')),
        'puntos_interes' => array_values(array_filter(array_map('trim', (array) ($circuito['puntos_interes'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'servicios' => array_values(array_filter(array_map('trim', (array) ($circuito['servicios'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'estado' => in_array($estado, ['activo', 'borrador', 'inactivo'], true) ? $estado : 'borrador',
        'actualizado_en' => $circuito['actualizado_en'] ?? null,
    ];
}

/**
 * @param array<int, array<string, mixed>> $circuitos
 * @return array<int, array<string, mixed>>
 */
function ordenarCircuitos(array $circuitos): array
{
    usort($circuitos, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($circuitos);
}

/**
 * @param array<int, array<string, mixed>> $circuitos
 */
function obtenerSiguienteId(array $circuitos): int
{
    $maximo = 0;
    foreach ($circuitos as $circuito) {
        $maximo = max($maximo, (int) ($circuito['id'] ?? 0));
    }

    return $maximo + 1;
}

/**
 * @param mixed $texto
 * @return array<int, string>
 */
function convertirListado($texto): array
{
    if (!is_string($texto)) {
        return [];
    }

    $lineas = preg_split('/\r\n|\r|\n/', $texto);
    if (!is_array($lineas)) {
        return [];
    }

    $resultado = [];
    foreach ($lineas as $linea) {
        $limpio = trim($linea);
        if ($limpio !== '') {
            $resultado[] = $limpio;
        }
    }

    return $resultado;
}

function obtenerNombreDestinoCircuito(array $circuito): string
{
    if (!empty($circuito['destino']['nombre'])) {
        return (string) $circuito['destino']['nombre'];
    }

    if (!empty($circuito['destino']['personalizado'])) {
        return (string) $circuito['destino']['personalizado'];
    }

    return 'Destino no definido';
}

function mostrarDuracionCircuito(string $duracion): string
{
    return $duracion !== '' ? $duracion : 'Duración no registrada';
}

function categoriaCircuitoEtiqueta(string $categoria): string
{
    return match ($categoria) {
        'cultural' => 'Cultural',
        'aventura' => 'Aventura',
        'gastronomico' => 'Gastronómico',
        'bienestar' => 'Bienestar',
        default => 'Naturaleza',
    };
}

function dificultadCircuitoEtiqueta(string $dificultad): string
{
    return match ($dificultad) {
        'moderado' => 'Dificultad moderada',
        'intenso' => 'Dificultad alta',
        default => 'Dificultad relajada',
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
