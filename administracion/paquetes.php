<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';
$archivoPaquetes = __DIR__ . '/../almacenamiento/paquetes.json';

$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';
$paquetesPredeterminados = require __DIR__ . '/../app/configuracion/paquetes_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($archivoDestinos, $destinosPredeterminados, $errores);
$circuitosDisponibles = cargarCircuitosDisponibles($archivoCircuitos, $circuitosPredeterminados, $destinosDisponibles, $errores);
$paquetes = cargarPaquetes($archivoPaquetes, $paquetesPredeterminados, $errores);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'create') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $estado = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $moneda = strtoupper(trim((string) ($_POST['moneda'] ?? 'PEN')));
        $precioDesde = parsearPrecio($_POST['precio_desde'] ?? null, $errores);
        $descripcionBreve = trim((string) ($_POST['descripcion_breve'] ?? ''));
        $descripcionDetallada = trim((string) ($_POST['descripcion_detallada'] ?? ''));
        $imagenDestacada = trim((string) ($_POST['imagen_destacada'] ?? ''));
        $beneficios = convertirListado($_POST['beneficios'] ?? '');
        $incluye = convertirListado($_POST['incluye'] ?? '');
        $noIncluye = convertirListado($_POST['no_incluye'] ?? '');
        $salidas = convertirListado($_POST['salidas'] ?? '');
        $cuposMin = parsearEntero($_POST['cupos_min'] ?? null);
        $cuposMax = parsearEntero($_POST['cupos_max'] ?? null);
        $destinosSeleccionados = convertirIds($_POST['destinos'] ?? []);
        $circuitosSeleccionados = convertirIds($_POST['circuitos'] ?? []);

        if ($nombre === '') {
            $errores[] = 'Debes indicar el nombre del paquete.';
        }

        if (!array_key_exists($estado, $estadosPermitidos)) {
            $errores[] = 'El estado seleccionado no es válido.';
        }

        if ($moneda === '' || !array_key_exists($moneda, $monedasPermitidas)) {
            $moneda = 'PEN';
        }

        if ($duracion === '') {
            $errores[] = 'La duración del paquete es obligatoria.';
        }

        if (empty($destinosSeleccionados)) {
            $errores[] = 'Selecciona al menos un destino.';
        }

        if (empty($circuitosSeleccionados)) {
            $errores[] = 'Selecciona al menos un circuito asociado.';
        }

        if ($cuposMin !== null && $cuposMin < 0) {
            $errores[] = 'El cupo mínimo debe ser un número positivo.';
        }

        if ($cuposMax !== null && $cuposMax < 0) {
            $errores[] = 'El cupo máximo debe ser un número positivo.';
        }

        if ($cuposMin !== null && $cuposMax !== null && $cuposMin > $cuposMax) {
            $errores[] = 'El cupo máximo debe ser mayor o igual al mínimo.';
        }

        if (empty($errores)) {
            $paquete = [
                'id' => obtenerSiguienteId($paquetes),
                'nombre' => $nombre,
                'estado' => $estado,
                'duracion' => $duracion,
                'precio_desde' => $precioDesde,
                'moneda' => $moneda,
                'descripcion_breve' => $descripcionBreve,
                'descripcion_detallada' => $descripcionDetallada,
                'beneficios' => $beneficios,
                'incluye' => $incluye,
                'no_incluye' => $noIncluye,
                'salidas' => $salidas,
                'cupos_min' => $cuposMin,
                'cupos_max' => $cuposMax,
                'destinos' => $destinosSeleccionados,
                'circuitos' => $circuitosSeleccionados,
                'imagen_destacada' => $imagenDestacada,
                'actualizado_en' => date('c'),
            ];

            $paquetes[] = normalizarPaquete($paquete);
            $paquetes = ordenarPaquetes($paquetes);

            try {
                ServicioAlmacenamientoJson::guardar($archivoPaquetes, $paquetes);
                $mensajeExito = 'Paquete creado correctamente.';
            } catch (\RuntimeException $exception) {
                $errores[] = 'El paquete se creó, pero no se pudo guardar en almacenamiento.';
            }
        }
    } elseif ($accion === 'update') {
        $paqueteId = isset($_POST['paquete_id']) ? (int) $_POST['paquete_id'] : 0;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $estado = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $moneda = strtoupper(trim((string) ($_POST['moneda'] ?? 'PEN')));
        $precioDesde = parsearPrecio($_POST['precio_desde'] ?? null, $errores);
        $descripcionBreve = trim((string) ($_POST['descripcion_breve'] ?? ''));
        $descripcionDetallada = trim((string) ($_POST['descripcion_detallada'] ?? ''));
        $imagenDestacada = trim((string) ($_POST['imagen_destacada'] ?? ''));
        $beneficios = convertirListado($_POST['beneficios'] ?? '');
        $incluye = convertirListado($_POST['incluye'] ?? '');
        $noIncluye = convertirListado($_POST['no_incluye'] ?? '');
        $salidas = convertirListado($_POST['salidas'] ?? '');
        $cuposMin = parsearEntero($_POST['cupos_min'] ?? null);
        $cuposMax = parsearEntero($_POST['cupos_max'] ?? null);
        $destinosSeleccionados = convertirIds($_POST['destinos'] ?? []);
        $circuitosSeleccionados = convertirIds($_POST['circuitos'] ?? []);

        if ($paqueteId <= 0) {
            $errores[] = 'No se pudo identificar el paquete a actualizar.';
        }

        if ($nombre === '') {
            $errores[] = 'El nombre del paquete es obligatorio.';
        }

        if (!array_key_exists($estado, $estadosPermitidos)) {
            $errores[] = 'El estado seleccionado no es válido.';
        }

        if ($moneda === '' || !array_key_exists($moneda, $monedasPermitidas)) {
            $moneda = 'PEN';
        }

        if ($duracion === '') {
            $errores[] = 'La duración es obligatoria.';
        }

        if (empty($destinosSeleccionados)) {
            $errores[] = 'Selecciona al menos un destino.';
        }

        if (empty($circuitosSeleccionados)) {
            $errores[] = 'Selecciona al menos un circuito asociado.';
        }

        if ($cuposMin !== null && $cuposMin < 0) {
            $errores[] = 'El cupo mínimo debe ser un número positivo.';
        }

        if ($cuposMax !== null && $cuposMax < 0) {
            $errores[] = 'El cupo máximo debe ser un número positivo.';
        }

        if ($cuposMin !== null && $cuposMax !== null && $cuposMin > $cuposMax) {
            $errores[] = 'El cupo máximo debe ser mayor o igual al mínimo.';
        }

        if (empty($errores)) {
            $actualizado = false;
            foreach ($paquetes as &$paquete) {
                if ($paquete['id'] === $paqueteId) {
                    $paquete['nombre'] = $nombre;
                    $paquete['estado'] = $estado;
                    $paquete['duracion'] = $duracion;
                    $paquete['moneda'] = $moneda;
                    $paquete['precio_desde'] = $precioDesde;
                    $paquete['descripcion_breve'] = $descripcionBreve;
                    $paquete['descripcion_detallada'] = $descripcionDetallada;
                    $paquete['imagen_destacada'] = $imagenDestacada;
                    $paquete['beneficios'] = $beneficios;
                    $paquete['incluye'] = $incluye;
                    $paquete['no_incluye'] = $noIncluye;
                    $paquete['salidas'] = $salidas;
                    $paquete['cupos_min'] = $cuposMin;
                    $paquete['cupos_max'] = $cuposMax;
                    $paquete['destinos'] = $destinosSeleccionados;
                    $paquete['circuitos'] = $circuitosSeleccionados;
                    $paquete['actualizado_en'] = date('c');
                    $actualizado = true;
                    break;
                }
            }
            unset($paquete);

            if ($actualizado) {
                $paquetes = ordenarPaquetes($paquetes);
                try {
                    ServicioAlmacenamientoJson::guardar($archivoPaquetes, $paquetes);
                    $mensajeExito = 'Paquete actualizado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'Los cambios se aplicaron, pero no se pudieron guardar.';
                }
            } else {
                $errores[] = 'No se encontró el paquete indicado.';
            }
        }
    } elseif ($accion === 'delete') {
        $paqueteId = isset($_POST['paquete_id']) ? (int) $_POST['paquete_id'] : 0;
        if ($paqueteId <= 0) {
            $errores[] = 'No se pudo identificar el paquete a eliminar.';
        } else {
            $cantidadInicial = count($paquetes);
            $paquetes = array_values(array_filter(
                $paquetes,
                static fn (array $paquete): bool => $paquete['id'] !== $paqueteId
            ));

            if ($cantidadInicial === count($paquetes)) {
                $errores[] = 'El paquete indicado ya no existe.';
            } else {
                try {
                    ServicioAlmacenamientoJson::guardar($archivoPaquetes, $paquetes);
                    $mensajeExito = 'Paquete eliminado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'El paquete se eliminó, pero no se pudo actualizar el almacenamiento.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if ($mensajeExito === null && empty($errores) && !is_file($archivoPaquetes)) {
    $mensajeInfo = 'Aún no tienes paquetes personalizados. Usa los circuitos y destinos creados para construir uno nuevo.';
}

$paginaActiva = 'paquetes';
$tituloPagina = 'Paquetes — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Gestión de paquetes turísticos</h1>
        <p>Combina circuitos, servicios y salidas programadas para generar productos listos para la venta.</p>
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
        <h2>Crear nuevo paquete</h2>
        <form method="post" class="admin-grid">
            <input type="hidden" name="action" value="create" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del paquete *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Escapada Oxapampa 3D/2N" />
                </div>
                <div class="admin-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="admin-grid three-columns">
                <div class="admin-field">
                    <label for="duracion">Duración *</label>
                    <input type="text" id="duracion" name="duracion" required placeholder="3 días / 2 noches" />
                </div>
                <div class="admin-field">
                    <label for="moneda">Moneda</label>
                    <select id="moneda" name="moneda">
                        <?php foreach ($monedasPermitidas as $codigo => $simbolo): ?>
                            <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($codigo . ' · ' . $simbolo, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="precio_desde">Precio desde</label>
                    <input type="text" id="precio_desde" name="precio_desde" placeholder="599.00" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="cupos_min">Cupo mínimo</label>
                    <input type="number" id="cupos_min" name="cupos_min" min="0" placeholder="6" />
                </div>
                <div class="admin-field">
                    <label for="cupos_max">Cupo máximo</label>
                    <input type="number" id="cupos_max" name="cupos_max" min="0" placeholder="18" />
                </div>
            </div>
            <div class="admin-field">
                <label for="descripcion_breve">Descripción breve</label>
                <textarea id="descripcion_breve" name="descripcion_breve" rows="3" placeholder="Resumen comercial para la ficha del paquete."></textarea>
            </div>
            <div class="admin-field">
                <label for="descripcion_detallada">Descripción detallada</label>
                <textarea id="descripcion_detallada" name="descripcion_detallada" rows="5" placeholder="Detalla itinerario, servicios, recomendaciones y condiciones."></textarea>
            </div>
            <div class="admin-field">
                <label for="imagen_destacada">Imagen destacada</label>
                <input type="text" id="imagen_destacada" name="imagen_destacada" placeholder="/web/imagenes/paquetes/oxapampa.jpg" />
                <p class="admin-help">Ruta relativa o URL completa para mostrar en la ficha comercial.</p>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="beneficios">Beneficios destacados</label>
                    <textarea id="beneficios" name="beneficios" rows="4" placeholder="Atención personalizada&#10;Seguro de viaje&#10;Guía bilingüe"></textarea>
                    <p class="admin-help">Escribe un beneficio por línea.</p>
                </div>
                <div class="admin-field">
                    <label for="salidas">Fechas de salida</label>
                    <textarea id="salidas" name="salidas" rows="4" placeholder="2024-07-15&#10;2024-08-05"></textarea>
                    <p class="admin-help">Formato sugerido AAAA-MM-DD. Una fecha por línea.</p>
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="incluye">Incluye</label>
                    <textarea id="incluye" name="incluye" rows="5" placeholder="Transporte turístico&#10;Hospedaje con desayuno&#10;Circuito guiado"></textarea>
                </div>
                <div class="admin-field">
                    <label for="no_incluye">No incluye</label>
                    <textarea id="no_incluye" name="no_incluye" rows="5" placeholder="Alimentación no mencionada&#10;Gastos personales"></textarea>
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="destinos">Destinos asociados *</label>
                    <select id="destinos" name="destinos[]" multiple size="5">
                        <?php foreach ($destinosDisponibles as $destino): ?>
                            <option value="<?= (int) $destino['id']; ?>"><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
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
                                    <input type="checkbox" name="circuitos[]" value="<?= (int) $circuito['id']; ?>" />
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
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Paquetes registrados</h2>
        <?php if (empty($paquetes)): ?>
            <p class="admin-help">Aún no hay paquetes disponibles. Combina circuitos y servicios para crear tu primer producto.</p>
        <?php else: ?>
            <div class="admin-grid" style="gap: 1.25rem;">
                <?php foreach ($paquetes as $paquete): ?>
                    <article class="admin-package">
                        <header class="admin-package__header">
                            <div>
                                <h3><?= htmlspecialchars($paquete['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="admin-package__meta">
                                    <?= htmlspecialchars($paquete['duracion'], ENT_QUOTES, 'UTF-8'); ?> ·
                                    <?= htmlspecialchars(formatearPrecioDesde($paquete['precio_desde'], $paquete['moneda'], $monedasPermitidas), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <span class="admin-package__badge admin-package__badge--<?= htmlspecialchars($paquete['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= estadoPaqueteEtiqueta($paquete['estado']); ?>
                            </span>
                        </header>
                        <?php if ($paquete['descripcion_breve'] !== ''): ?>
                            <p class="admin-package__description"><?= nl2br(htmlspecialchars($paquete['descripcion_breve'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php endif; ?>
                        <div class="admin-package__grid">
                            <section>
                                <h4>Destinos</h4>
                                <ul>
                                    <?php foreach ($paquete['destinos'] as $destinoId): ?>
                                        <li><?= htmlspecialchars($destinosDisponibles[$destinoId]['nombre'] ?? ('ID ' . $destinoId), ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                            <section>
                                <h4>Circuitos</h4>
                                <ul>
                                    <?php foreach ($paquete['circuitos'] as $circuitoId): ?>
                                        <li><?= htmlspecialchars($circuitosDisponibles[$circuitoId]['nombre'] ?? ('ID ' . $circuitoId), ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                            <?php if (!empty($paquete['beneficios'])): ?>
                                <section>
                                    <h4>Beneficios</h4>
                                    <ul>
                                        <?php foreach ($paquete['beneficios'] as $beneficio): ?>
                                            <li><?= htmlspecialchars($beneficio, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endif; ?>
                        </div>
                        <div class="admin-package__details">
                            <?php if (!empty($paquete['incluye'])): ?>
                                <div>
                                    <h5>Incluye</h5>
                                    <ul>
                                        <?php foreach ($paquete['incluye'] as $item): ?>
                                            <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($paquete['no_incluye'])): ?>
                                <div>
                                    <h5>No incluye</h5>
                                    <ul>
                                        <?php foreach ($paquete['no_incluye'] as $item): ?>
                                            <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($paquete['salidas'])): ?>
                                <div>
                                    <h5>Próximas salidas</h5>
                                    <p><?= htmlspecialchars(implode(' · ', $paquete['salidas']), ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5>Cupos</h5>
                                <p><?= htmlspecialchars(formatearCupos($paquete['cupos_min'], $paquete['cupos_max']), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                        <?php if ($paquete['descripcion_detallada'] !== ''): ?>
                            <details class="admin-package__accordion">
                                <summary>Ver descripción detallada</summary>
                                <div><?= nl2br(htmlspecialchars($paquete['descripcion_detallada'], ENT_QUOTES, 'UTF-8')); ?></div>
                            </details>
                        <?php endif; ?>
                        <p class="admin-package__timestamp">Actualizado <?= htmlspecialchars(formatearMarcaTiempo($paquete['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></p>

                        <details class="admin-package__editor">
                            <summary>Editar paquete</summary>
                            <form method="post" class="admin-grid">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="paquete_id" value="<?= (int) $paquete['id']; ?>" />
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Nombre</label>
                                        <input type="text" name="nombre" required value="<?= htmlspecialchars($paquete['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Estado</label>
                                        <select name="estado">
                                            <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                                                <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $paquete['estado'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="admin-grid three-columns">
                                    <div class="admin-field">
                                        <label>Duración</label>
                                        <input type="text" name="duracion" value="<?= htmlspecialchars($paquete['duracion'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Moneda</label>
                                        <select name="moneda">
                                            <?php foreach ($monedasPermitidas as $codigo => $simbolo): ?>
                                                <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" <?= $paquete['moneda'] === $codigo ? 'selected' : ''; ?>><?= htmlspecialchars($codigo . ' · ' . $simbolo, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="admin-field">
                                        <label>Precio desde</label>
                                        <input type="text" name="precio_desde" value="<?= htmlspecialchars($paquete['precio_desde'] !== null ? number_format((float) $paquete['precio_desde'], 2, '.', '') : '', ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Cupo mínimo</label>
                                        <input type="number" name="cupos_min" min="0" value="<?= $paquete['cupos_min'] !== null ? (int) $paquete['cupos_min'] : ''; ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Cupo máximo</label>
                                        <input type="number" name="cupos_max" min="0" value="<?= $paquete['cupos_max'] !== null ? (int) $paquete['cupos_max'] : ''; ?>" />
                                    </div>
                                </div>
                                <div class="admin-field">
                                    <label>Descripción breve</label>
                                    <textarea name="descripcion_breve" rows="3"><?= htmlspecialchars($paquete['descripcion_breve'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="admin-field">
                                    <label>Descripción detallada</label>
                                    <textarea name="descripcion_detallada" rows="5"><?= htmlspecialchars($paquete['descripcion_detallada'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="admin-field">
                                    <label>Imagen destacada</label>
                                    <input type="text" name="imagen_destacada" value="<?= htmlspecialchars($paquete['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Beneficios</label>
                                        <textarea name="beneficios" rows="4"><?= htmlspecialchars(implode(PHP_EOL, $paquete['beneficios']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="admin-field">
                                        <label>Fechas de salida</label>
                                        <textarea name="salidas" rows="4"><?= htmlspecialchars(implode(PHP_EOL, $paquete['salidas']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Incluye</label>
                                        <textarea name="incluye" rows="5"><?= htmlspecialchars(implode(PHP_EOL, $paquete['incluye']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="admin-field">
                                        <label>No incluye</label>
                                        <textarea name="no_incluye" rows="5"><?= htmlspecialchars(implode(PHP_EOL, $paquete['no_incluye']), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Destinos asociados</label>
                                        <select name="destinos[]" multiple size="5">
                                            <?php foreach ($destinosDisponibles as $destino): ?>
                                                <option value="<?= (int) $destino['id']; ?>" <?= in_array((int) $destino['id'], $paquete['destinos'], true) ? 'selected' : ''; ?>><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="admin-field">
                                        <label>Circuitos incluidos</label>
                                        <div class="admin-checkbox-group">
                                            <?php foreach ($circuitosDisponibles as $circuito): ?>
                                                <label class="admin-checkbox">
                                                    <input type="checkbox" name="circuitos[]" value="<?= (int) $circuito['id']; ?>" <?= in_array((int) $circuito['id'], $paquete['circuitos'], true) ? 'checked' : ''; ?> />
                                                    <span>
                                                        <?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                        <small><?= htmlspecialchars($circuito['duracion'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="admin-actions">
                                    <button type="submit" class="admin-button">Guardar cambios</button>
                                </div>
                            </form>
                        </details>

                        <form method="post" class="admin-package__delete" onsubmit="return confirm('¿Eliminar el paquete <?= htmlspecialchars($paquete['nombre'], ENT_QUOTES, 'UTF-8'); ?>?');">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="paquete_id" value="<?= (int) $paquete['id']; ?>" />
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
 * @param string $archivo
 * @param array<int, array<string, mixed>> $predeterminados
 * @param array<int, string> $errores
 * @return array<int, array<string, string|int>>
 */
function cargarDestinosDisponibles(string $archivo, array $predeterminados, array &$errores): array
{
    try {
        $destinos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
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
 * @param array<int, array<string, string|int>> $destinos
 * @param array<int, string> $errores
 * @return array<int, array<string, mixed>>
 */
function cargarCircuitosDisponibles(string $archivo, array $predeterminados, array $destinos, array &$errores): array
{
    try {
        $circuitos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (\RuntimeException $exception) {
        $errores[] = 'No se pudo cargar la lista de circuitos. Se usarán los valores de referencia.';
        $circuitos = $predeterminados;
    }

    $resultado = [];
    foreach ($circuitos as $circuito) {
        $id = (int) ($circuito['id'] ?? 0);
        $nombre = trim((string) ($circuito['nombre'] ?? ''));
        if ($id <= 0 || $nombre === '') {
            continue;
        }

        $destinoId = null;
        if (isset($circuito['destino']['id'])) {
            $destinoId = (int) $circuito['destino']['id'];
        } elseif (isset($circuito['destino_id'])) {
            $destinoId = (int) $circuito['destino_id'];
        }

        $resultado[$id] = [
            'id' => $id,
            'nombre' => $nombre,
            'duracion' => trim((string) ($circuito['duracion'] ?? '')),
            'destino' => $destinoId !== null ? ($destinos[$destinoId]['nombre'] ?? '') : '',
        ];
    }

    ksort($resultado);

    return $resultado;
}

/**
 * @param string $archivo
 * @param array<int, array<string, mixed>> $predeterminados
 * @param array<int, string> $errores
 * @return array<int, array<string, mixed>>
 */
function cargarPaquetes(string $archivo, array $predeterminados, array &$errores): array
{
    try {
        $paquetes = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (\RuntimeException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de paquetes. Se muestran los paquetes de referencia.';
        $paquetes = $predeterminados;
    }

    $normalizados = array_map(static fn (array $paquete): array => normalizarPaquete($paquete), $paquetes);

    return ordenarPaquetes($normalizados);
}

/**
 * @param array<string, mixed> $paquete
 * @return array<string, mixed>
 */
function normalizarPaquete(array $paquete): array
{
    $estado = strtolower(trim((string) ($paquete['estado'] ?? 'borrador')));
    if (!in_array($estado, ['publicado', 'borrador', 'agotado', 'inactivo'], true)) {
        $estado = 'borrador';
    }

    return [
        'id' => (int) ($paquete['id'] ?? 0),
        'nombre' => trim((string) ($paquete['nombre'] ?? '')),
        'estado' => $estado,
        'duracion' => trim((string) ($paquete['duracion'] ?? '')),
        'precio_desde' => isset($paquete['precio_desde']) ? (float) $paquete['precio_desde'] : null,
        'moneda' => strtoupper(trim((string) ($paquete['moneda'] ?? 'PEN'))),
        'descripcion_breve' => trim((string) ($paquete['descripcion_breve'] ?? '')),
        'descripcion_detallada' => trim((string) ($paquete['descripcion_detallada'] ?? '')),
        'beneficios' => array_values(array_filter(array_map('trim', (array) ($paquete['beneficios'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'incluye' => array_values(array_filter(array_map('trim', (array) ($paquete['incluye'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'no_incluye' => array_values(array_filter(array_map('trim', (array) ($paquete['no_incluye'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'salidas' => array_values(array_filter(array_map('trim', (array) ($paquete['salidas'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'cupos_min' => isset($paquete['cupos_min']) ? (int) $paquete['cupos_min'] : null,
        'cupos_max' => isset($paquete['cupos_max']) ? (int) $paquete['cupos_max'] : null,
        'destinos' => array_values(array_map('intval', (array) ($paquete['destinos'] ?? []))),
        'circuitos' => array_values(array_map('intval', (array) ($paquete['circuitos'] ?? []))),
        'imagen_destacada' => trim((string) ($paquete['imagen_destacada'] ?? '')),
        'actualizado_en' => $paquete['actualizado_en'] ?? null,
    ];
}

/**
 * @param array<int, array<string, mixed>> $paquetes
 * @return array<int, array<string, mixed>>
 */
function ordenarPaquetes(array $paquetes): array
{
    usort($paquetes, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($paquetes);
}

/**
 * @param array<int, array<string, mixed>> $paquetes
 */
function obtenerSiguienteId(array $paquetes): int
{
    $maximo = 0;
    foreach ($paquetes as $paquete) {
        $maximo = max($maximo, (int) ($paquete['id'] ?? 0));
    }

    return $maximo + 1;
}

/**
 * @param mixed $valor
 */
function parsearPrecio($valor, array &$errores): ?float
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

/**
 * @param mixed $valor
 */
function parsearEntero($valor): ?int
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        return (int) $valor;
    }

    if (is_string($valor)) {
        $limpio = trim($valor);
        if ($limpio === '') {
            return null;
        }
        if (!ctype_digit($limpio)) {
            return null;
        }
        return (int) $limpio;
    }

    return null;
}

/**
 * @param mixed $valor
 * @return array<int, int>
 */
function convertirIds($valor): array
{
    if (is_array($valor)) {
        return array_values(array_filter(array_map('intval', $valor), static fn (int $id): bool => $id > 0));
    }

    if ($valor === null || $valor === '') {
        return [];
    }

    $entero = (int) $valor;
    return $entero > 0 ? [$entero] : [];
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

function estadoPaqueteEtiqueta(string $estado): string
{
    return match ($estado) {
        'publicado' => 'Publicado',
        'agotado' => 'Agotado',
        'inactivo' => 'Inactivo',
        default => 'Borrador',
    };
}

function formatearPrecioDesde(?float $precio, string $moneda, array $monedasPermitidas): string
{
    if ($precio === null) {
        return 'Precio a consultar';
    }

    $simbolo = $monedasPermitidas[$moneda] ?? $monedasPermitidas['PEN'];
    return $simbolo . ' ' . number_format($precio, 2, '.', ',');
}

function formatearCupos(?int $minimo, ?int $maximo): string
{
    if ($minimo === null && $maximo === null) {
        return 'Cupos abiertos';
    }

    if ($minimo !== null && $maximo !== null) {
        return sprintf('%d - %d pasajeros', $minimo, $maximo);
    }

    if ($minimo !== null) {
        return sprintf('Desde %d pasajeros', $minimo);
    }

    return sprintf('Hasta %d pasajeros', (int) $maximo);
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
