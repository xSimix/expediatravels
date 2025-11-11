<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

require_once __DIR__ . '/includes/destinos_util.php';

$destinosPredeterminados = obtenerDestinosPredeterminados();
$errores = [];

$destinoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinoId = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : $destinoId;
}

$destinoSeleccionado = obtenerDestinoPorId($destinoId, $destinosPredeterminados, $errores);

if ($destinoSeleccionado === null) {
    http_response_code(404);
    $errores[] = 'No se encontró el destino solicitado.';
    $destinoSeleccionado = [
        'id' => $destinoId,
        'nombre' => '',
        'region' => '',
        'descripcion' => '',
        'tagline' => '',
        'latitud' => null,
        'longitud' => null,
        'imagen' => '',
        'imagen_destacada' => '',
        'galeria' => [],
        'video_destacado_url' => '',
        'tags' => [],
        'estado' => 'activo',
        'mostrar_en_buscador' => true,
        'mostrar_en_explorador' => true,
    ];
}

$datos = [
    'nombre' => $destinoSeleccionado['nombre'] ?? '',
    'region' => $destinoSeleccionado['region'] ?? '',
    'descripcion' => $destinoSeleccionado['descripcion'] ?? '',
    'tagline' => $destinoSeleccionado['tagline'] ?? '',
    'latitud' => $destinoSeleccionado['latitud'] !== null ? (string) $destinoSeleccionado['latitud'] : '',
    'longitud' => $destinoSeleccionado['longitud'] !== null ? (string) $destinoSeleccionado['longitud'] : '',
    'imagen' => $destinoSeleccionado['imagen'] ?? '',
    'imagen_destacada' => $destinoSeleccionado['imagen_destacada'] ?? '',
    'galeria' => $destinoSeleccionado['galeria'] ?? [],
    'video_destacado_url' => $destinoSeleccionado['video_destacado_url'] ?? '',
    'estado' => $destinoSeleccionado['estado'] ?? 'activo',
    'etiquetas' => implode(', ', $destinoSeleccionado['tags'] ?? []),
    'mostrar_en_buscador' => (bool) ($destinoSeleccionado['mostrar_en_buscador'] ?? true),
    'mostrar_en_explorador' => (bool) ($destinoSeleccionado['mostrar_en_explorador'] ?? true),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores)) {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? $datos['nombre']));
    $datos['region'] = trim((string) ($_POST['region'] ?? $datos['region']));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? $datos['descripcion']));
    $datos['tagline'] = trim((string) ($_POST['tagline'] ?? $datos['tagline']));
    $datos['latitud'] = trim((string) ($_POST['latitud'] ?? $datos['latitud']));
    $datos['longitud'] = trim((string) ($_POST['longitud'] ?? $datos['longitud']));
    $datos['imagen'] = trim((string) ($_POST['imagen'] ?? $datos['imagen']));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? $datos['imagen_destacada']));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? $datos['video_destacado_url']));
    $datos['estado'] = normalizarEstado($_POST['estado'] ?? $datos['estado']);
    $datos['etiquetas'] = trim((string) ($_POST['etiquetas'] ?? $datos['etiquetas']));
    $datos['mostrar_en_buscador'] = isset($_POST['mostrar_en_buscador']);
    $datos['mostrar_en_explorador'] = isset($_POST['mostrar_en_explorador']);

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre del destino no puede estar vacío.';
    }

    if ($datos['region'] === '') {
        $errores[] = 'Debes indicar la región del destino.';
    }

    $latitud = normalizarCoordenada($datos['latitud'], 'latitud', $errores);
    $longitud = normalizarCoordenada($datos['longitud'], 'longitud', $errores);
    $etiquetas = convertirEtiquetas($datos['etiquetas']);

    if (empty($errores)) {
        $destinoActualizado = [
            'nombre' => $datos['nombre'],
            'region' => $datos['region'],
            'descripcion' => $datos['descripcion'],
            'tagline' => $datos['tagline'],
            'latitud' => $latitud,
            'longitud' => $longitud,
            'imagen' => $datos['imagen'],
            'imagen_destacada' => $datos['imagen_destacada'],
            'galeria' => $datos['galeria'],
            'video_destacado_url' => $datos['video_destacado_url'],
            'tags' => $etiquetas,
            'estado' => $datos['estado'],
            'mostrar_en_buscador' => $datos['mostrar_en_buscador'],
            'mostrar_en_explorador' => $datos['mostrar_en_explorador'],
        ];

        $esPredeterminado = (bool) ($destinoSeleccionado['es_predeterminado'] ?? false);

        if ($esPredeterminado) {
            $nuevoId = crearDestinoCatalogo($destinoActualizado, $errores);
            if ($nuevoId !== null) {
                header('Location: destinos.php?actualizado=1');
                exit;
            }
        } else {
            $actualizado = actualizarDestinoCatalogo($destinoId, $destinoActualizado, $errores);
            if ($actualizado) {
                header('Location: destinos.php?actualizado=1');
                exit;
            }
        }
    }
}

$paginaActiva = 'destinos_editar';
$tituloPagina = 'Editar destino — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Editar destino</h1>
            <p>Actualiza la información del destino para mantener coherencia en paquetes y circuitos.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="destinos.php">← Volver al listado</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>No pudimos actualizar el destino:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-form">
            <input type="hidden" name="destino_id" value="<?= (int) $destinoId; ?>" />

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">🧭</span>
                    <span>Información del destino</span>
                </h2>
                <p class="admin-section__description">Actualiza los datos principales que describen al destino en la web pública.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="nombre">Nombre del destino *</label>
                            <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Oxapampa" />
                        </div>
                        <div class="admin-field">
                            <label for="region">Región o provincia *</label>
                            <input type="text" id="region" name="region" required value="<?= htmlspecialchars($datos['region'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Pasco" />
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe la esencia del destino y su propuesta de valor."><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <p class="admin-help">Se muestra en la web pública y se utiliza en fichas de paquetes.</p>
                    </div>

                    <div class="admin-field">
                        <label for="tagline">Frase destacada</label>
                        <input type="text" id="tagline" name="tagline" value="<?= htmlspecialchars($datos['tagline'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Capital cafetalera de la Selva Central" />
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">🖼️</span>
                    <span>Recursos multimedia</span>
                </h2>
                <p class="admin-section__description">Gestiona las imágenes y materiales audiovisuales que acompañan al destino.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-grid two-columns">
                        <div class="admin-field media-picker" data-media-picker data-multiple="false">
                            <label for="imagen">Imagen de portada</label>
                            <div class="media-picker__input">
                                <input type="text" id="imagen" name="imagen" value="<?= htmlspecialchars($datos['imagen'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/portada.jpg" data-media-input />
                                <button type="button" class="admin-button secondary" data-media-open>Seleccionar de la biblioteca</button>
                                <label class="admin-button secondary">
                                    <span>Subir nueva</span>
                                    <input type="file" accept="image/*" data-media-upload hidden />
                                </label>
                            </div>
                            <div class="media-picker__preview" data-media-preview data-empty-text="Sin imagen seleccionada" data-empty="<?= $datos['imagen'] === '' ? 'true' : 'false'; ?>">
                                <?php if ($datos['imagen'] !== ''): ?>
                                    <img src="<?= htmlspecialchars($datos['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Previsualización de portada" />
                                <?php else: ?>
                                    Sin imagen seleccionada
                                <?php endif; ?>
                            </div>
                            <p class="admin-help">Selecciona una imagen horizontal que represente al destino.</p>
                        </div>
                        <div class="admin-field media-picker" data-media-picker data-multiple="false">
                            <label for="imagen_destacada">Imagen destacada</label>
                            <div class="media-picker__input">
                                <input type="text" id="imagen_destacada" name="imagen_destacada" value="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/destacado.jpg" data-media-input />
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
                            <p class="admin-help">Se utiliza en componentes promocionales y tarjetas de experiencias.</p>
                        </div>
                    </div>

                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="video_destacado_url">URL de video destacado</label>
                            <input type="url" id="video_destacado_url" name="video_destacado_url" value="<?= htmlspecialchars($datos['video_destacado_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=XXXX" />
                            <p class="admin-help">Enlace a un video promocional alojado en YouTube, Vimeo u otra plataforma.</p>
                        </div>
                    </div>

                    <div class="admin-field media-picker" data-media-picker data-multiple="true" data-field="galeria">
                        <span class="admin-field__label">Galería de imágenes</span>
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
                        <p class="admin-help">Las imágenes se muestran en la ficha del destino y módulos inspiracionales.</p>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">
                    <span class="admin-section__icon" aria-hidden="true">📍</span>
                    <span>Ubicación y visibilidad</span>
                </h2>
                <p class="admin-section__description">Configura coordenadas, etiquetas y estado para controlar su disponibilidad.</p>
                <div class="admin-section__content admin-grid">
                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="latitud">Latitud</label>
                            <input type="text" id="latitud" name="latitud" value="<?= htmlspecialchars($datos['latitud'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="-10.5756" />
                        </div>
                        <div class="admin-field">
                            <label for="longitud">Longitud</label>
                            <input type="text" id="longitud" name="longitud" value="<?= htmlspecialchars($datos['longitud'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="-75.4018" />
                        </div>
                    </div>

                    <div class="admin-grid two-columns">
                        <div class="admin-field">
                            <label for="etiquetas">Etiquetas</label>
                            <input type="text" id="etiquetas" name="etiquetas" value="<?= htmlspecialchars($datos['etiquetas'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Café, Naturaleza, Cultura" />
                            <p class="admin-help">Separa las etiquetas con comas o saltos de línea.</p>
                        </div>
                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="activo" <?= $datos['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="oculto" <?= $datos['estado'] === 'oculto' ? 'selected' : ''; ?>>Oculto</option>
                                <option value="borrador" <?= $datos['estado'] === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-grid two-columns">
                        <div class="admin-field admin-field--checkbox">
                            <input type="checkbox" id="mostrar_en_buscador" name="mostrar_en_buscador" value="1" <?= $datos['mostrar_en_buscador'] ? 'checked' : ''; ?> />
                            <label for="mostrar_en_buscador">Mostrar en el buscador inteligente</label>
                            <p class="admin-help">Si lo desactivas, el destino no aparecerá como filtro ni sugerencia de búsqueda.</p>
                        </div>
                        <div class="admin-field admin-field--checkbox">
                            <input type="checkbox" id="mostrar_en_explorador" name="mostrar_en_explorador" value="1" <?= $datos['mostrar_en_explorador'] ? 'checked' : ''; ?> />
                            <label for="mostrar_en_explorador">Mostrar en la página Explorar</label>
                            <p class="admin-help">Úsalo para pausar temporalmente la visibilidad del destino en el catálogo público.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar cambios</button>
                <a class="admin-button secondary" href="destinos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
