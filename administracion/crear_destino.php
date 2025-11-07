<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/destinos_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$destinosPredeterminados = obtenerDestinosPredeterminados();
$errores = [];
$mensajeExito = null;

$destinos = cargarDestinosCatalogo($archivoDestinos, $destinosPredeterminados, $errores);

$datos = [
    'nombre' => '',
    'region' => '',
    'descripcion' => '',
    'tagline' => '',
    'latitud' => '',
    'longitud' => '',
    'imagen' => '',
    'imagen_destacada' => '',
    'galeria' => [],
    'video_destacado_url' => '',
    'estado' => 'activo',
    'etiquetas' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $datos['region'] = trim((string) ($_POST['region'] ?? ''));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? ''));
    $datos['tagline'] = trim((string) ($_POST['tagline'] ?? ''));
    $datos['latitud'] = trim((string) ($_POST['latitud'] ?? ''));
    $datos['longitud'] = trim((string) ($_POST['longitud'] ?? ''));
    $datos['imagen'] = trim((string) ($_POST['imagen'] ?? ''));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? ''));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? ''));
    $datos['estado'] = normalizarEstado($_POST['estado'] ?? 'activo');
    $datos['etiquetas'] = trim((string) ($_POST['etiquetas'] ?? ''));

    if ($datos['nombre'] === '') {
        $errores[] = 'Debes indicar el nombre del destino.';
    }

    if ($datos['region'] === '') {
        $errores[] = 'La región o provincia es obligatoria.';
    }

    $latitud = normalizarCoordenada($datos['latitud'], 'latitud', $errores);
    $longitud = normalizarCoordenada($datos['longitud'], 'longitud', $errores);
    $etiquetas = convertirEtiquetas($datos['etiquetas']);

    if (empty($errores)) {
        $nuevoDestino = [
            'id' => obtenerSiguienteId($destinos),
            'nombre' => $datos['nombre'],
            'region' => $datos['region'],
            'descripcion' => $datos['descripcion'],
            'tagline' => $datos['tagline'],
            'latitud' => $latitud,
            'longitud' => $longitud,
            'imagen' => $datos['imagen'],
            'imagen_portada' => $datos['imagen'],
            'imagen_destacada' => $datos['imagen_destacada'],
            'galeria' => $datos['galeria'],
            'video_destacado_url' => $datos['video_destacado_url'],
            'tags' => $etiquetas,
            'estado' => $datos['estado'],
            'actualizado_en' => date('c'),
        ];

        $destinos[] = normalizarDestino($nuevoDestino);
        $destinos = ordenarDestinos($destinos);

        try {
            ServicioAlmacenamientoJson::guardar($archivoDestinos, $destinos);
            header('Location: destinos.php?creado=1');
            exit;
        } catch (RuntimeException $exception) {
            $errores[] = 'El destino se agregó, pero no se pudo guardar en almacenamiento.';
        }
    }
}

$paginaActiva = 'destinos_crear';
$tituloPagina = 'Nuevo destino — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Registrar un nuevo destino</h1>
            <p>Completa la información base del destino para utilizarlo en circuitos y paquetes turísticos.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="destinos.php">← Volver al listado</a>
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
    <?php elseif ($mensajeExito !== null): ?>
        <div class="admin-alert">
            <?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-grid">
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

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="tagline">Frase destacada</label>
                    <input type="text" id="tagline" name="tagline" value="<?= htmlspecialchars($datos['tagline'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Capital cafetalera de la Selva Central" />
                </div>
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
                    <p class="admin-help">Selecciona una imagen horizontal que represente al destino en listados y cabeceras.</p>
                </div>
            </div>

            <div class="admin-grid two-columns">
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
                <p class="admin-help">Las imágenes de la galería se muestran en la ficha del destino y en secciones inspiracionales.</p>
            </div>

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

            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar destino</button>
                <a class="admin-button secondary" href="destinos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
