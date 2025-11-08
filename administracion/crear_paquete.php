<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

require_once __DIR__ . '/includes/paquetes_util.php';

$errores = [];
$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';
$paquetesPredeterminados = require __DIR__ . '/../app/configuracion/paquetes_predeterminados.php';

$destinosDisponibles = paquetesCargarDestinos($destinosPredeterminados, $errores);
$circuitosDisponibles = paquetesCargarCircuitos($circuitosPredeterminados, $destinosDisponibles, $errores);
$paquetes = paquetesCargarPaquetes($paquetesPredeterminados, $errores);

$estadosPermitidos = [
    'publicado' => 'Publicado',
    'borrador' => 'Borrador',
    'agotado' => 'Agotado',
    'inactivo' => 'Inactivo',
];

$monedasPermitidas = [
    'PEN' => 'S/',
    'USD' => '$',
];

$datos = [
    'nombre' => '',
    'estado' => 'borrador',
    'duracion' => '',
    'moneda' => 'PEN',
    'precio_desde' => '',
    'descripcion_breve' => '',
    'descripcion_detallada' => '',
    'imagen_destacada' => '',
    'imagen_portada' => '',
    'galeria' => [],
    'video_destacado_url' => '',
    'beneficios' => '',
    'incluye' => '',
    'no_incluye' => '',
    'salidas' => '',
    'cupos_min' => '',
    'cupos_max' => '',
    'destinos' => [],
    'circuitos' => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
    $datos['duracion'] = trim((string) ($_POST['duracion'] ?? ''));
    $datos['moneda'] = strtoupper(trim((string) ($_POST['moneda'] ?? 'PEN')));
    $datos['precio_desde'] = trim((string) ($_POST['precio_desde'] ?? ''));
    $datos['descripcion_breve'] = trim((string) ($_POST['descripcion_breve'] ?? ''));
    $datos['descripcion_detallada'] = trim((string) ($_POST['descripcion_detallada'] ?? ''));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? ''));
    $datos['imagen_portada'] = trim((string) ($_POST['imagen_portada'] ?? ''));
    $datos['galeria'] = isset($_POST['galeria']) ? array_values(array_filter(array_map('trim', (array) $_POST['galeria']), static fn (string $valor): bool => $valor !== '')) : [];
    $datos['video_destacado_url'] = trim((string) ($_POST['video_destacado_url'] ?? ''));
    $datos['beneficios'] = trim((string) ($_POST['beneficios'] ?? ''));
    $datos['incluye'] = trim((string) ($_POST['incluye'] ?? ''));
    $datos['no_incluye'] = trim((string) ($_POST['no_incluye'] ?? ''));
    $datos['salidas'] = trim((string) ($_POST['salidas'] ?? ''));
    $datos['cupos_min'] = trim((string) ($_POST['cupos_min'] ?? ''));
    $datos['cupos_max'] = trim((string) ($_POST['cupos_max'] ?? ''));
    $datos['destinos'] = isset($_POST['destinos']) ? array_map('intval', (array) $_POST['destinos']) : [];
    $datos['circuitos'] = isset($_POST['circuitos']) ? array_map('intval', (array) $_POST['circuitos']) : [];

    if ($datos['nombre'] === '') {
        $errores[] = 'Debes indicar el nombre del paquete.';
    }

    if (!array_key_exists($datos['estado'], $estadosPermitidos)) {
        $errores[] = 'El estado seleccionado no es válido.';
    }

    if ($datos['moneda'] === '' || !array_key_exists($datos['moneda'], $monedasPermitidas)) {
        $datos['moneda'] = 'PEN';
    }

    if ($datos['duracion'] === '') {
        $errores[] = 'La duración del paquete es obligatoria.';
    }

    if (empty($datos['destinos'])) {
        $errores[] = 'Selecciona al menos un destino.';
    }

    if (empty($datos['circuitos'])) {
        $errores[] = 'Selecciona al menos un circuito asociado.';
    }

    $precioDesde = paquetesParsearPrecio($datos['precio_desde'], $errores);
    $cuposMin = paquetesParsearEntero($datos['cupos_min']);
    $cuposMax = paquetesParsearEntero($datos['cupos_max']);

    if ($cuposMin !== null && $cuposMin < 0) {
        $errores[] = 'El cupo mínimo debe ser un número positivo.';
    }

    if ($cuposMax !== null && $cuposMax < 0) {
        $errores[] = 'El cupo máximo debe ser un número positivo.';
    }

    if ($cuposMin !== null && $cuposMax !== null && $cuposMin > $cuposMax) {
        $errores[] = 'El cupo máximo debe ser mayor o igual al mínimo.';
    }

    $beneficios = convertirListado($datos['beneficios']);
    $incluye = convertirListado($datos['incluye']);
    $noIncluye = convertirListado($datos['no_incluye']);
    $salidas = convertirListado($datos['salidas']);

    if (empty($errores)) {
        $paquete = [
            'nombre' => $datos['nombre'],
            'estado' => $datos['estado'],
            'duracion' => $datos['duracion'],
            'precio_desde' => $precioDesde,
            'moneda' => $datos['moneda'],
            'descripcion_breve' => $datos['descripcion_breve'],
            'descripcion_detallada' => $datos['descripcion_detallada'],
            'imagen_portada' => $datos['imagen_portada'],
            'imagen_destacada' => $datos['imagen_destacada'],
            'galeria' => $datos['galeria'],
            'video_destacado_url' => $datos['video_destacado_url'],
            'beneficios' => $beneficios,
            'incluye' => $incluye,
            'no_incluye' => $noIncluye,
            'salidas' => $salidas,
            'cupos_min' => $cuposMin,
            'cupos_max' => $cuposMax,
            'destinos' => $datos['destinos'],
            'circuitos' => $datos['circuitos'],
        ];

        $nuevoId = paquetesCrearPaquete($paquete, $errores);
        if ($nuevoId !== null) {
            header('Location: paquetes.php?creado=1');
            exit;
        }
    }
}

$paginaActiva = 'paquetes_crear';
$tituloPagina = 'Nuevo paquete — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/media-picker.js'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Publicar un nuevo paquete</h1>
            <p>Combina circuitos, servicios y destinos para ofrecer experiencias completas a tus viajeros.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="paquetes.php">← Volver al listado</a>
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
        <form method="post" class="admin-grid">
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del paquete *</label>
                    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Escapada Oxapampa 3D/2N" />
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

            <div class="admin-grid three-columns">
                <div class="admin-field">
                    <label for="duracion">Duración *</label>
                    <input type="text" id="duracion" name="duracion" required value="<?= htmlspecialchars($datos['duracion'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="3 días / 2 noches" />
                </div>
                <div class="admin-field">
                    <label for="moneda">Moneda</label>
                    <select id="moneda" name="moneda">
                        <?php foreach ($monedasPermitidas as $codigo => $simbolo): ?>
                            <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['moneda'] === $codigo ? 'selected' : ''; ?>><?= htmlspecialchars($codigo . ' · ' . $simbolo, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="precio_desde">Precio desde</label>
                    <input type="text" id="precio_desde" name="precio_desde" value="<?= htmlspecialchars($datos['precio_desde'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="599.00" />
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="cupos_min">Cupo mínimo</label>
                    <input type="number" id="cupos_min" name="cupos_min" min="0" value="<?= htmlspecialchars($datos['cupos_min'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="6" />
                </div>
                <div class="admin-field">
                    <label for="cupos_max">Cupo máximo</label>
                    <input type="number" id="cupos_max" name="cupos_max" min="0" value="<?= htmlspecialchars($datos['cupos_max'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="18" />
                </div>
            </div>

            <div class="admin-field">
                <label for="descripcion_breve">Descripción breve</label>
                <textarea id="descripcion_breve" name="descripcion_breve" rows="3" placeholder="Resumen comercial para la ficha del paquete."><?= htmlspecialchars($datos['descripcion_breve'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="admin-field">
                <label for="descripcion_detallada">Descripción detallada</label>
                <textarea id="descripcion_detallada" name="descripcion_detallada" rows="5" placeholder="Detalla itinerario, servicios, recomendaciones y condiciones."><?= htmlspecialchars($datos['descripcion_detallada'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field media-picker" data-media-picker data-multiple="false">
                    <label for="imagen_portada">Imagen de portada</label>
                    <div class="media-picker__input">
                        <input type="text" id="imagen_portada" name="imagen_portada" value="<?= htmlspecialchars($datos['imagen_portada'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/paquete-portada.jpg" data-media-input />
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
                        <input type="text" id="imagen_destacada" name="imagen_destacada" value="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="/almacenamiento/medios/paquete-destacado.jpg" data-media-input />
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
                    <p class="admin-help">Se muestra en tarjetas y material promocional del paquete.</p>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="video_destacado_url">URL de video destacado</label>
                    <input type="url" id="video_destacado_url" name="video_destacado_url" value="<?= htmlspecialchars($datos['video_destacado_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=XXXX" />
                </div>
            </div>

            <div class="admin-field media-picker" data-media-picker data-multiple="true" data-field="galeria">
                <span class="admin-field__label">Galería del paquete</span>
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
                    <label for="beneficios">Beneficios destacados</label>
                    <textarea id="beneficios" name="beneficios" rows="4" placeholder="Atención personalizada&#10;Seguro de viaje&#10;Guía bilingüe"><?= htmlspecialchars($datos['beneficios'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="admin-help">Escribe un beneficio por línea.</p>
                </div>
                <div class="admin-field">
                    <label for="salidas">Fechas de salida</label>
                    <textarea id="salidas" name="salidas" rows="4" placeholder="2024-07-15&#10;2024-08-05"><?= htmlspecialchars($datos['salidas'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="admin-help">Formato sugerido AAAA-MM-DD. Una fecha por línea.</p>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="incluye">Incluye</label>
                    <textarea id="incluye" name="incluye" rows="5" placeholder="Transporte turístico&#10;Hospedaje con desayuno&#10;Circuito guiado"><?= htmlspecialchars($datos['incluye'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="no_incluye">No incluye</label>
                    <textarea id="no_incluye" name="no_incluye" rows="5" placeholder="Alimentación no mencionada&#10;Gastos personales"><?= htmlspecialchars($datos['no_incluye'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="destinos">Destinos asociados *</label>
                    <select id="destinos" name="destinos[]" multiple size="5">
                        <?php foreach ($destinosDisponibles as $destino): ?>
                            <option value="<?= (int) $destino['id']; ?>" <?= in_array((int) $destino['id'], $datos['destinos'], true) ? 'selected' : ''; ?>><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="admin-help">Mantén presionada la tecla Ctrl o Cmd para seleccionar múltiples opciones.</p>
                </div>
                <div class="admin-field">
                    <span class="admin-field__label">Circuitos incluidos *</span>
                    <?php if (empty($circuitosDisponibles)): ?>
                        <p class="admin-help">Aún no hay circuitos registrados. Crea al menos uno para asociarlo al paquete.</p>
                    <?php else: ?>
                        <div class="admin-checkbox-group">
                            <?php foreach ($circuitosDisponibles as $circuito): ?>
                                <label class="admin-checkbox">
                                    <input type="checkbox" name="circuitos[]" value="<?= (int) $circuito['id']; ?>" <?= in_array((int) $circuito['id'], $datos['circuitos'], true) ? 'checked' : ''; ?> />
                                    <span>
                                        <?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        <small><?= htmlspecialchars($circuito['duracion'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-button">Publicar paquete</button>
                <a class="admin-button secondary" href="paquetes.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
