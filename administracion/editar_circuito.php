<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/circuitos_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';

$errores = [];
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

$circuitoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $circuitoId = isset($_POST['circuito_id']) ? (int) $_POST['circuito_id'] : $circuitoId;
}

$circuitoSeleccionado = null;
foreach ($circuitos as $circuito) {
    if ($circuito['id'] === $circuitoId) {
        $circuitoSeleccionado = $circuito;
        break;
    }
}

if ($circuitoSeleccionado === null) {
    http_response_code(404);
    $errores[] = 'No se encontró el circuito solicitado.';
    $circuitoSeleccionado = [
        'id' => $circuitoId,
        'nombre' => '',
        'destino' => ['id' => null, 'nombre' => '', 'personalizado' => '', 'region' => ''],
        'duracion' => '',
        'categoria' => 'naturaleza',
        'dificultad' => 'relajado',
        'frecuencia' => '',
        'estado' => 'borrador',
        'descripcion' => '',
        'puntos_interes' => [],
        'servicios' => [],
        'imagen_portada' => '',
        'imagen_destacada' => '',
        'galeria' => [],
        'video_destacado_url' => '',
    ];
}

$datos = [
    'nombre' => $circuitoSeleccionado['nombre'] ?? '',
    'destino_id' => $circuitoSeleccionado['destino']['id'] ?? 0,
    'destino_personalizado' => $circuitoSeleccionado['destino']['personalizado'] ?? '',
    'duracion' => $circuitoSeleccionado['duracion'] ?? '',
    'categoria' => $circuitoSeleccionado['categoria'] ?? 'naturaleza',
    'dificultad' => $circuitoSeleccionado['dificultad'] ?? 'relajado',
    'frecuencia' => $circuitoSeleccionado['frecuencia'] ?? '',
    'estado' => $circuitoSeleccionado['estado'] ?? 'borrador',
    'descripcion' => $circuitoSeleccionado['descripcion'] ?? '',
    'puntos_interes' => implode(PHP_EOL, $circuitoSeleccionado['puntos_interes'] ?? []),
    'servicios' => implode(PHP_EOL, $circuitoSeleccionado['servicios'] ?? []),
    'imagen_portada' => $circuitoSeleccionado['imagen_portada'] ?? '',
    'imagen_destacada' => $circuitoSeleccionado['imagen_destacada'] ?? '',
    'galeria' => $circuitoSeleccionado['galeria'] ?? [],
    'video_destacado_url' => $circuitoSeleccionado['video_destacado_url'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores)) {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? $datos['nombre']));
    $datos['destino_id'] = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : $datos['destino_id'];
    $datos['destino_personalizado'] = trim((string) ($_POST['destino_personalizado'] ?? $datos['destino_personalizado']));
    $datos['duracion'] = trim((string) ($_POST['duracion'] ?? $datos['duracion']));
    $datos['categoria'] = strtolower(trim((string) ($_POST['categoria'] ?? $datos['categoria'])));
    $datos['dificultad'] = strtolower(trim((string) ($_POST['dificultad'] ?? $datos['dificultad'])));
    $datos['frecuencia'] = trim((string) ($_POST['frecuencia'] ?? $datos['frecuencia']));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? $datos['estado'])));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? $datos['descripcion']));
    $datos['puntos_interes'] = trim((string) ($_POST['puntos_interes'] ?? $datos['puntos_interes']));
    $datos['servicios'] = trim((string) ($_POST['servicios'] ?? $datos['servicios']));
    $datos['imagen_portada'] = trim((string) ($_POST['imagen_portada'] ?? $datos['imagen_portada']));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? $datos['imagen_destacada']));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? $datos['video_destacado_url']));

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre del circuito es obligatorio.';
    }

    if ($datos['destino_id'] <= 0 && $datos['destino_personalizado'] === '') {
        $errores[] = 'Selecciona un destino o indica uno personalizado.';
    }

    if ($datos['duracion'] === '') {
        $errores[] = 'La duración es obligatoria.';
    }

    if (!array_key_exists($datos['categoria'], $categoriasPermitidas)) {
        $errores[] = 'La categoría indicada no es válida.';
    }

    if (!array_key_exists($datos['dificultad'], $dificultadesPermitidas)) {
        $errores[] = 'La dificultad indicada no es válida.';
    }

    if (!array_key_exists($datos['estado'], $estadosPermitidos)) {
        $errores[] = 'El estado indicado no es válido.';
    }

    $puntosInteres = convertirListado($datos['puntos_interes']);
    $serviciosIncluidos = convertirListado($datos['servicios']);

    $destinoNombre = $datos['destino_personalizado'];
    $destinoRegion = '';
    if ($datos['destino_id'] > 0) {
        $destinoNombre = $destinosDisponibles[$datos['destino_id']]['nombre'] ?? $destinoNombre;
        $destinoRegion = $destinosDisponibles[$datos['destino_id']]['region'] ?? '';
    }

    if (empty($errores)) {
        foreach ($circuitos as &$circuito) {
            if ($circuito['id'] === $circuitoId) {
                $circuito['nombre'] = $datos['nombre'];
                $circuito['destino'] = [
                    'id' => $datos['destino_id'] > 0 ? $datos['destino_id'] : null,
                    'nombre' => $destinoNombre,
                    'personalizado' => $datos['destino_personalizado'],
                    'region' => $destinoRegion,
                ];
                $circuito['duracion'] = $datos['duracion'];
                $circuito['categoria'] = $datos['categoria'];
                $circuito['dificultad'] = $datos['dificultad'];
                $circuito['frecuencia'] = $datos['frecuencia'];
                $circuito['descripcion'] = $datos['descripcion'];
                $circuito['imagen_portada'] = $datos['imagen_portada'];
                $circuito['imagen_destacada'] = $datos['imagen_destacada'];
                $circuito['galeria'] = $datos['galeria'];
                $circuito['video_destacado_url'] = $datos['video_destacado_url'];
                $circuito['puntos_interes'] = $puntosInteres;
                $circuito['servicios'] = $serviciosIncluidos;
                $circuito['estado'] = $datos['estado'];
                $circuito['actualizado_en'] = date('c');
                break;
            }
        }
        unset($circuito);

        $circuitos = ordenarCircuitos($circuitos);

        try {
            ServicioAlmacenamientoJson::guardar($archivoCircuitos, $circuitos);
            header('Location: circuitos.php?actualizado=1');
            exit;
        } catch (RuntimeException $exception) {
            $errores[] = 'Los cambios se aplicaron, pero no se pudieron guardar.';
        }
    }
}

$paginaActiva = 'circuitos_editar';
$tituloPagina = 'Editar circuito — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Editar circuito</h1>
            <p>Ajusta los detalles operativos y comerciales del circuito seleccionado.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="circuitos.php">← Volver al listado</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>No pudimos actualizar el circuito:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-grid">
            <input type="hidden" name="circuito_id" value="<?= (int) $circuitoId; ?>" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del circuito *</label>
                    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="admin-field">
                    <label for="duracion">Duración *</label>
                    <input type="text" id="duracion" name="duracion" required value="<?= htmlspecialchars($datos['duracion'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="destino_id">Destino asociado</label>
                    <select id="destino_id" name="destino_id">
                        <option value="0">Selecciona un destino del catálogo</option>
                        <?php foreach ($destinosDisponibles as $destinoId => $destino): ?>
                            <option value="<?= (int) $destinoId; ?>" <?= $datos['destino_id'] === (int) $destinoId ? 'selected' : ''; ?>><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="destino_personalizado">Destino personalizado</label>
                    <input type="text" id="destino_personalizado" name="destino_personalizado" value="<?= htmlspecialchars($datos['destino_personalizado'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <div class="admin-grid three-columns">
                <div class="admin-field">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categoriasPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['categoria'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="dificultad">Dificultad</label>
                    <select id="dificultad" name="dificultad">
                        <?php foreach ($dificultadesPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['dificultad'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['estado'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="frecuencia">Frecuencia de salida</label>
                    <input type="text" id="frecuencia" name="frecuencia" value="<?= htmlspecialchars($datos['frecuencia'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="admin-field">
                    <label for="video_destacado_url">URL de video destacado</label>
                    <input type="url" id="video_destacado_url" name="video_destacado_url" value="<?= htmlspecialchars($datos['video_destacado_url'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <div class="admin-field">
                <label for="descripcion">Descripción comercial</label>
                <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field media-picker" data-media-picker data-multiple="false">
                    <label for="imagen_portada">Imagen de portada</label>
                    <div class="media-picker__input">
                        <input type="text" id="imagen_portada" name="imagen_portada" value="<?= htmlspecialchars($datos['imagen_portada'], ENT_QUOTES, 'UTF-8'); ?>" data-media-input />
                        <button type="button" class="admin-button secondary" data-media-open>Seleccionar de la biblioteca</button>
                        <label class="admin-button secondary">
                            <span>Subir nueva</span>
                            <input type="file" accept="image/*" data-media-upload hidden />
                        </label>
                    </div>
                    <div class="media-picker__preview" data-media-preview data-empty-text="Sin imagen de portada" data-empty="<?= $datos['imagen_portada'] === '' ? 'true' : 'false'; ?>">
                        <?php if ($datos['imagen_portada'] !== ''): ?>
                            <img src="<?= htmlspecialchars($datos['imagen_portada'], ENT_QUOTES, 'UTF-8'); ?>" alt="Previsualización de portada" />
                        <?php else: ?>
                            Sin imagen de portada
                        <?php endif; ?>
                    </div>
                </div>
                <div class="admin-field media-picker" data-media-picker data-multiple="false">
                    <label for="imagen_destacada">Imagen destacada</label>
                    <div class="media-picker__input">
                        <input type="text" id="imagen_destacada" name="imagen_destacada" value="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" data-media-input />
                        <button type="button" class="admin-button secondary" data-media-open>Seleccionar de la biblioteca</button>
                        <label class="admin-button secondary">
                            <span>Subir nueva</span>
                            <input type="file" accept="image/*" data-media-upload hidden />
                        </label>
                    </div>
                    <div class="media-picker__preview" data-media-preview data-empty-text="Sin imagen destacada" data-empty="<?= $datos['imagen_destacada'] === '' ? 'true' : 'false'; ?>">
                        <?php if ($datos['imagen_destacada'] !== ''): ?>
                            <img src="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" alt="Previsualización destacada" />
                        <?php else: ?>
                            Sin imagen destacada
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="admin-field media-picker" data-media-picker data-multiple="true" data-field="galeria">
                <span class="admin-field__label">Galería del circuito</span>
                <div class="media-picker__selected" data-media-selected data-field="galeria">
                    <?php foreach ($datos['galeria'] as $imagenGaleria): ?>
                        <?php $etiquetaGaleria = basename((string) $imagenGaleria) ?: $imagenGaleria; ?>
                        <div class="media-chip" data-media-item>
                            <input type="hidden" name="galeria[]" value="<?= htmlspecialchars($imagenGaleria, ENT_QUOTES, 'UTF-8'); ?>" />
                            <span class="media-chip__label" title="<?= htmlspecialchars($imagenGaleria, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($etiquetaGaleria, ENT_QUOTES, 'UTF-8'); ?></span>
                            <button type="button" class="media-chip__remove" data-media-remove aria-label="Quitar">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="media-picker__actions">
                    <button type="button" class="admin-button secondary" data-media-open>Agregar desde la biblioteca</button>
                    <label class="admin-button secondary">
                        <span>Subir imágenes</span>
                        <input type="file" accept="image/*" multiple data-media-upload hidden />
                    </label>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="puntos_interes">Puntos de interés</label>
                    <textarea id="puntos_interes" name="puntos_interes" rows="4"><?= htmlspecialchars($datos['puntos_interes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="servicios">Servicios incluidos</label>
                    <textarea id="servicios" name="servicios" rows="4"><?= htmlspecialchars($datos['servicios'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar cambios</button>
                <a class="admin-button secondary" href="circuitos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
