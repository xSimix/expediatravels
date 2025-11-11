<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

require_once __DIR__ . '/includes/circuitos_util.php';

$errores = [];
$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';
$serviciosPredeterminados = require __DIR__ . '/../app/configuracion/servicios_circuito_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($destinosPredeterminados, $errores);
$circuitos = cargarCircuitos($circuitosPredeterminados, $destinosDisponibles, $errores);
$serviciosDisponibles = cargarServiciosDisponibles($serviciosPredeterminados, $errores);
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

$circuitoSeleccionado = obtenerCircuitoPorId($circuitoId, $destinosDisponibles, $circuitosPredeterminados, $errores);

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
        'itinerario' => [],
        'servicios_incluidos_ids' => [],
        'servicios_excluidos_ids' => [],
        'imagen_portada' => '',
        'imagen_destacada' => '',
        'galeria' => [],
        'video_destacado_url' => '',
        'precio' => null,
    ];
}

$datos = [
    'nombre' => $circuitoSeleccionado['nombre'] ?? '',
    'destino_id' => $circuitoSeleccionado['destino']['id'] ?? 0,
    'destino_personalizado' => $circuitoSeleccionado['destino']['personalizado'] ?? '',
    'duracion' => $circuitoSeleccionado['duracion'] ?? '',
    'precio' => $circuitoSeleccionado['precio'] !== null ? number_format((float) $circuitoSeleccionado['precio'], 2, '.', '') : '',
    'categoria' => $circuitoSeleccionado['categoria'] ?? 'naturaleza',
    'dificultad' => $circuitoSeleccionado['dificultad'] ?? 'relajado',
    'frecuencia' => $circuitoSeleccionado['frecuencia'] ?? '',
    'estado' => $circuitoSeleccionado['estado'] ?? 'borrador',
    'descripcion' => $circuitoSeleccionado['descripcion'] ?? '',
    'itinerario' => $circuitoSeleccionado['itinerario'] ?? [],
    'servicios_incluidos_ids' => $circuitoSeleccionado['servicios_incluidos_ids'] ?? [],
    'servicios_excluidos_ids' => $circuitoSeleccionado['servicios_excluidos_ids'] ?? [],
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
    $datos['precio'] = trim((string) ($_POST['precio'] ?? $datos['precio']));
    $datos['categoria'] = strtolower(trim((string) ($_POST['categoria'] ?? $datos['categoria'])));
    $datos['dificultad'] = strtolower(trim((string) ($_POST['dificultad'] ?? $datos['dificultad'])));
    $datos['frecuencia'] = trim((string) ($_POST['frecuencia'] ?? $datos['frecuencia']));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? $datos['estado'])));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? $datos['descripcion']));
    $datos['imagen_portada'] = trim((string) ($_POST['imagen_portada'] ?? $datos['imagen_portada']));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? $datos['imagen_destacada']));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? $datos['video_destacado_url']));
    $datos['itinerario'] = procesarItinerarioFormulario($_POST['itinerario'] ?? []);
    $datos['servicios_incluidos_ids'] = filtrarServiciosSeleccionados($serviciosDisponibles, $_POST['servicios_incluidos'] ?? $datos['servicios_incluidos_ids']);
    $datos['servicios_excluidos_ids'] = filtrarServiciosSeleccionados($serviciosDisponibles, $_POST['servicios_excluidos'] ?? $datos['servicios_excluidos_ids']);

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

    $precio = circuitosParsearPrecio($datos['precio'], $errores);

    $destinoNombre = $datos['destino_personalizado'];
    $destinoRegion = '';
    if ($datos['destino_id'] > 0) {
        $destinoNombre = $destinosDisponibles[$datos['destino_id']]['nombre'] ?? $destinoNombre;
        $destinoRegion = $destinosDisponibles[$datos['destino_id']]['region'] ?? '';
    }

    if (empty($errores)) {
        $circuitoActualizado = [
            'nombre' => $datos['nombre'],
            'destino_id' => $datos['destino_id'],
            'destino_personalizado' => $datos['destino_personalizado'],
            'duracion' => $datos['duracion'],
            'precio' => $precio,
            'categoria' => $datos['categoria'],
            'dificultad' => $datos['dificultad'],
            'frecuencia' => $datos['frecuencia'],
            'estado' => $datos['estado'],
            'descripcion' => $datos['descripcion'],
            'imagen_portada' => $datos['imagen_portada'],
            'imagen_destacada' => $datos['imagen_destacada'],
            'galeria' => $datos['galeria'],
            'video_destacado_url' => $datos['video_destacado_url'],
            'itinerario' => $datos['itinerario'],
            'servicios_incluidos_ids' => $datos['servicios_incluidos_ids'],
            'servicios_excluidos_ids' => $datos['servicios_excluidos_ids'],
        ];

        $esPredeterminado = (bool) ($circuitoSeleccionado['es_predeterminado'] ?? false);

        if ($esPredeterminado) {
            $nuevoId = crearCircuitoCatalogo($circuitoActualizado, $errores);
            if ($nuevoId !== null) {
                header('Location: circuitos.php?actualizado=1');
                exit;
            }
        } else {
            $actualizado = actualizarCircuitoCatalogo($circuitoId, $circuitoActualizado, $errores);
            if ($actualizado) {
                header('Location: circuitos.php?actualizado=1');
                exit;
            }
        }
    }
}

$paginaActiva = 'circuitos_editar';
$tituloPagina = 'Editar circuito — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js', 'recursos/circuitos-form.js'];

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
        <form method="post" class="admin-form">
            <input type="hidden" name="circuito_id" value="<?= (int) $circuitoId; ?>" />

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">🧭</span>
                    <span>Información del circuito</span>
                </h2>
                <p class="admin-section__description">Actualiza los datos esenciales del recorrido y el destino asociado.</p>
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
                <p class="admin-section__description">Configura categoría, dificultad, estado y tarifas para organizar la oferta.</p>
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
                            <label for="precio">Tarifa desde</label>
                            <input type="text" id="precio" name="precio" value="<?= htmlspecialchars($datos['precio'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="150.00" />
                            <p class="admin-help">Ingresa el monto referencial por viajero (ejemplo: 150.00).</p>
                        </div>
                        <div class="admin-field">
                            <label for="frecuencia">Frecuencia de salida</label>
                            <input type="text" id="frecuencia" name="frecuencia" value="<?= htmlspecialchars($datos['frecuencia'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Diario / fines de semana" />
                        </div>
                    </div>
                    <div class="admin-field">
                        <label for="video_destacado_url">URL de video destacado</label>
                        <input type="url" id="video_destacado_url" name="video_destacado_url" value="<?= htmlspecialchars($datos['video_destacado_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=XXXX" />
                        <p class="admin-help">Comparte el recorrido en formato audiovisual para inspirar a los viajeros.</p>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">📝</span>
                    <span>Contenido del itinerario</span>
                </h2>
                <p class="admin-section__description">Describe la experiencia e indica los atractivos y servicios incluidos.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-field">
                        <label for="descripcion">Descripción comercial</label>
                        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Cuenta la historia del circuito, qué lugares cubre y la experiencia que ofrece."><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="admin-field" data-itinerary-container>
                        <span class="admin-field__label">Itinerario del circuito</span>
                        <p class="admin-help">Actualiza cada bloque para mantener sincronizados los acordeones del recorrido.</p>
                        <div class="itinerary-editor" data-itinerary-list>
                            <?php
                                $itinerarioActual = $datos['itinerario'];
                                if (empty($itinerarioActual)) {
                                    $itinerarioActual[] = ['dia' => '', 'hora' => '', 'titulo' => '', 'descripcion' => '', 'ubicacion_maps' => ''];
                                }
                            ?>
                            <?php foreach ($itinerarioActual as $indice => $paso): ?>
                                <div class="itinerary-item" data-itinerary-item>
                                    <header class="itinerary-item__header">
                                        <span class="itinerary-item__index" data-itinerary-index><?= $indice + 1; ?></span>
                                        <button type="button" class="admin-chip admin-chip--danger" data-itinerary-remove aria-label="Eliminar bloque">×</button>
                                    </header>
                                    <div class="admin-grid two-columns">
                                        <div class="admin-field">
                                            <label>Dia / momento</label>
                                            <input type="text" name="itinerario[dia][]" value="<?= htmlspecialchars((string) ($paso['dia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Día 1 · Mañana" />
                                        </div>
                                        <div class="admin-field">
                                            <label>Hora</label>
                                            <input type="text" name="itinerario[hora][]" value="<?= htmlspecialchars((string) ($paso['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="08:30" />
                                        </div>
                                    </div>
                                    <div class="admin-field">
                                        <label>Título de la actividad *</label>
                                        <input type="text" name="itinerario[titulo][]" value="<?= htmlspecialchars((string) ($paso['titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Visita a Tunqui Cueva" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Descripción breve</label>
                                        <textarea name="itinerario[descripcion][]" rows="2" placeholder="Recorrido guiado con cascos y linternas."><?= htmlspecialchars((string) ($paso['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="admin-field">
                                        <label>Ubicación en Google Maps</label>
                                        <input type="url" name="itinerario[ubicacion_maps][]" value="<?= htmlspecialchars((string) ($paso['ubicacion_maps'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://maps.google.com/?q=-12.0464,-77.0428" />
                                        <p class="admin-help">Pega la URL compartible de Google Maps para esta actividad.</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="admin-button secondary" data-itinerary-add>+ Agregar bloque</button>
                    </div>

                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <span class="admin-field__label">Servicios incluidos</span>
                            <p class="admin-help">Selecciona los servicios confirmados para este circuito.</p>
                            <?php if (empty($serviciosDisponibles)): ?>
                                <p class="admin-help">No hay servicios configurados en el catálogo.</p>
                            <?php else: ?>
                                <ul class="admin-table__list admin-table__list--options" data-services-incluidos>
                                    <?php foreach ($serviciosDisponibles as $servicio): ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="servicios_incluidos[]" value="<?= (int) $servicio['id']; ?>" <?= in_array((int) $servicio['id'], $datos['servicios_incluidos_ids'], true) ? 'checked' : ''; ?> />
                                                <?php $iconoServicio = trim((string) ($servicio['icono'] ?? '')); ?>
                                                <?php if ($iconoServicio !== ''): ?>
                                                    <span class="service-option__icon"><?= htmlspecialchars($iconoServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </label>
                                            <?php if ($servicio['descripcion'] !== ''): ?>
                                                <p class="admin-help"><?= htmlspecialchars($servicio['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="admin-field">
                            <span class="admin-field__label">Servicios no incluidos</span>
                            <p class="admin-help">Marca los conceptos que se comunican como no incluidos al viajero.</p>
                            <?php if (empty($serviciosDisponibles)): ?>
                                <p class="admin-help">No hay opciones de "no incluye" registradas todavía.</p>
                            <?php else: ?>
                                <ul class="admin-table__list admin-table__list--options" data-services-excluidos>
                                    <?php foreach ($serviciosDisponibles as $servicio): ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="servicios_excluidos[]" value="<?= (int) $servicio['id']; ?>" <?= in_array((int) $servicio['id'], $datos['servicios_excluidos_ids'], true) ? 'checked' : ''; ?> />
                                                <?php $iconoServicio = trim((string) ($servicio['icono'] ?? '')); ?>
                                                <?php if ($iconoServicio !== ''): ?>
                                                    <span class="service-option__icon service-option__icon--danger"><?= htmlspecialchars($iconoServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </label>
                                            <?php if ($servicio['descripcion'] !== ''): ?>
                                                <p class="admin-help"><?= htmlspecialchars($servicio['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <template id="itinerary-item-template">
                <div class="itinerary-item" data-itinerary-item>
                    <header class="itinerary-item__header">
                        <span class="itinerary-item__index" data-itinerary-index></span>
                        <button type="button" class="admin-chip admin-chip--danger" data-itinerary-remove aria-label="Eliminar bloque">×</button>
                    </header>
                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label>Dia / momento</label>
                            <input type="text" name="itinerario[dia][]" placeholder="Día 1 · Mañana" />
                        </div>
                        <div class="admin-field">
                            <label>Hora</label>
                            <input type="text" name="itinerario[hora][]" placeholder="08:30" />
                        </div>
                    </div>
                    <div class="admin-field">
                        <label>Título de la actividad *</label>
                        <input type="text" name="itinerario[titulo][]" placeholder="Visita a Tunqui Cueva" />
                    </div>
                    <div class="admin-field">
                        <label>Descripción breve</label>
                        <textarea name="itinerario[descripcion][]" rows="2" placeholder="Recorrido guiado con cascos y linternas."></textarea>
                    </div>
                    <div class="admin-field">
                        <label>Ubicación en Google Maps</label>
                        <input type="url" name="itinerario[ubicacion_maps][]" placeholder="https://maps.google.com/?q=-12.0464,-77.0428" />
                        <p class="admin-help">Pega la URL compartible de Google Maps para esta actividad.</p>
                    </div>
                </div>
            </template>

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
                <button type="submit" class="admin-button">Guardar cambios</button>
                <a class="admin-button secondary" href="circuitos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
