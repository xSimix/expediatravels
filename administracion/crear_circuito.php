<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

require_once __DIR__ . '/includes/circuitos_util.php';

$errores = [];
$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($destinosPredeterminados, $errores);
$circuitos = cargarCircuitos($circuitosPredeterminados, $destinosDisponibles, $errores);

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

$datos = [
    'nombre' => '',
    'destino_id' => 0,
    'destino_personalizado' => '',
    'duracion' => '',
    'categoria' => 'naturaleza',
    'dificultad' => 'relajado',
    'frecuencia' => '',
    'estado' => 'borrador',
    'descripcion' => '',
    'puntos_interes' => '',
    'servicios' => '',
    'imagen_portada' => '',
    'imagen_destacada' => '',
    'galeria' => [],
    'video_destacado_url' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $datos['destino_id'] = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
    $datos['destino_personalizado'] = trim((string) ($_POST['destino_personalizado'] ?? ''));
    $datos['duracion'] = trim((string) ($_POST['duracion'] ?? ''));
    $datos['categoria'] = strtolower(trim((string) ($_POST['categoria'] ?? 'naturaleza')));
    $datos['dificultad'] = strtolower(trim((string) ($_POST['dificultad'] ?? 'relajado')));
    $datos['frecuencia'] = trim((string) ($_POST['frecuencia'] ?? ''));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? ''));
    $datos['puntos_interes'] = trim((string) ($_POST['puntos_interes'] ?? ''));
    $datos['servicios'] = trim((string) ($_POST['servicios'] ?? ''));
    $datos['imagen_portada'] = trim((string) ($_POST['imagen_portada'] ?? ''));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? ''));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? ''));

    if ($datos['nombre'] === '') {
        $errores[] = 'Debes indicar el nombre del circuito.';
    }

    if ($datos['destino_id'] <= 0 && $datos['destino_personalizado'] === '') {
        $errores[] = 'Selecciona un destino o escribe uno personalizado.';
    }

    if ($datos['duracion'] === '') {
        $errores[] = 'Define la duración del circuito (por ejemplo: Full day, 2 días / 1 noche).';
    }

    if (!array_key_exists($datos['categoria'], $categoriasPermitidas)) {
        $errores[] = 'La categoría seleccionada no es válida.';
    }

    if (!array_key_exists($datos['dificultad'], $dificultadesPermitidas)) {
        $errores[] = 'La dificultad seleccionada no es válida.';
    }

    if (!array_key_exists($datos['estado'], $estadosPermitidos)) {
        $errores[] = 'El estado seleccionado no es válido.';
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
        $nuevoCircuito = [
            'nombre' => $datos['nombre'],
            'destino_id' => $datos['destino_id'],
            'destino_personalizado' => $datos['destino_personalizado'],
            'duracion' => $datos['duracion'],
            'categoria' => $datos['categoria'],
            'dificultad' => $datos['dificultad'],
            'frecuencia' => $datos['frecuencia'],
            'estado' => $datos['estado'],
            'descripcion' => $datos['descripcion'],
            'imagen_portada' => $datos['imagen_portada'],
            'imagen_destacada' => $datos['imagen_destacada'],
            'galeria' => $datos['galeria'],
            'video_destacado_url' => $datos['video_destacado_url'],
            'puntos_interes' => $puntosInteres,
            'servicios' => $serviciosIncluidos,
        ];

        $nuevoId = crearCircuitoCatalogo($nuevoCircuito, $errores);
        if ($nuevoId !== null) {
            header('Location: circuitos.php?creado=1');
            exit;
        }
    }
}

$paginaActiva = 'circuitos_crear';
$tituloPagina = 'Nuevo circuito — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Registrar un nuevo circuito</h1>
            <p>Define los detalles operativos del recorrido para combinarlo luego en paquetes turísticos.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="circuitos.php">← Volver al listado</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>Revisa los siguientes puntos antes de continuar:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-form">
            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">🧭</span>
                    <span>Información del circuito</span>
                </h2>
                <p class="admin-section__description">Completa los datos básicos para identificar el recorrido y su destino principal.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="nombre">Nombre del circuito *</label>
                            <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Full day Pozuzo" />
                        </div>
                        <div class="admin-field">
                            <label for="duracion">Duración *</label>
                            <input type="text" id="duracion" name="duracion" required value="<?= htmlspecialchars($datos['duracion'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="2 días / 1 noche" />
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
                            <p class="admin-help">Elige un destino existente o ingresa uno personalizado.</p>
                        </div>
                        <div class="admin-field">
                            <label for="destino_personalizado">Destino personalizado</label>
                            <input type="text" id="destino_personalizado" name="destino_personalizado" value="<?= htmlspecialchars($datos['destino_personalizado'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Reserva de biosfera" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">⚙️</span>
                    <span>Detalles operativos</span>
                </h2>
                <p class="admin-section__description">Configura categoría, dificultad y estado para organizar la oferta.</p>
                <div class="admin-section__content admin-grid">
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
                            <input type="text" id="frecuencia" name="frecuencia" value="<?= htmlspecialchars($datos['frecuencia'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Diario / fines de semana" />
                        </div>
                        <div class="admin-field">
                            <label for="video_destacado_url">URL de video destacado</label>
                            <input type="url" id="video_destacado_url" name="video_destacado_url" value="<?= htmlspecialchars($datos['video_destacado_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=XXXX" />
                            <p class="admin-help">Comparte el recorrido en formato audiovisual para inspirar a los viajeros.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">📝</span>
                    <span>Contenido del itinerario</span>
                </h2>
                <p class="admin-section__description">Describe la experiencia y detalla los atractivos y servicios incluidos.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-field">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Cuenta la historia del circuito, qué lugares cubre y la experiencia que ofrece."><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="puntos_interes">Puntos de interés</label>
                            <textarea id="puntos_interes" name="puntos_interes" rows="4" placeholder="Plaza principal&#10;Catarata El Tigre&#10;Reserva de Yanachaga"><?= htmlspecialchars($datos['puntos_interes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <p class="admin-help">Ingresa un punto por línea para mostrarlo como lista en la web.</p>
                        </div>
                        <div class="admin-field">
                            <label for="servicios">Servicios incluidos</label>
                            <textarea id="servicios" name="servicios" rows="4" placeholder="Transporte turístico&#10;Guía especializado&#10;Almuerzo típico"><?= htmlspecialchars($datos['servicios'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <p class="admin-help">Ingresa un servicio por línea para organizarlo automáticamente.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">🖼️</span>
                    <span>Recursos multimedia</span>
                </h2>
                <p class="admin-section__description">Añade imágenes y galerías para destacar los paisajes del circuito.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-grid two-columns">
                        <div class="admin-field media-picker" data-media-picker data-multiple="false">
                            <label for="imagen_portada">Imagen de portada</label>
                            <div class="media-picker__input">
                                <input type="text" id="imagen_portada" name="imagen_portada" value="<?= htmlspecialchars($datos['imagen_portada'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/circuito-portada.jpg" data-media-input />
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
                                <input type="text" id="imagen_destacada" name="imagen_destacada" value="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/circuito-destacado.jpg" data-media-input />
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
                            <p class="admin-help">Ideal para banners o módulos destacados en la web.</p>
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
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar circuito</button>
                <a class="admin-button secondary" href="circuitos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
