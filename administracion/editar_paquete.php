<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/paquetes_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';
$archivoPaquetes = __DIR__ . '/../almacenamiento/paquetes.json';

$errores = [];
$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';
$paquetesPredeterminados = require __DIR__ . '/../app/configuracion/paquetes_predeterminados.php';

$destinosDisponibles = paquetesCargarDestinos($archivoDestinos, $destinosPredeterminados, $errores);
$circuitosDisponibles = paquetesCargarCircuitos($archivoCircuitos, $circuitosPredeterminados, $destinosDisponibles, $errores);
$paquetes = paquetesCargarPaquetes($archivoPaquetes, $paquetesPredeterminados, $errores);

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

$paqueteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paqueteId = isset($_POST['paquete_id']) ? (int) $_POST['paquete_id'] : $paqueteId;
}

$paqueteSeleccionado = null;
foreach ($paquetes as $paquete) {
    if ($paquete['id'] === $paqueteId) {
        $paqueteSeleccionado = $paquete;
        break;
    }
}

if ($paqueteSeleccionado === null) {
    http_response_code(404);
    $errores[] = 'No se encontró el paquete solicitado.';
    $paqueteSeleccionado = paquetesNormalizarPaquete([
        'id' => $paqueteId,
        'nombre' => '',
        'estado' => 'borrador',
        'duracion' => '',
        'moneda' => 'PEN',
        'precio_desde' => null,
        'descripcion_breve' => '',
        'descripcion_detallada' => '',
        'imagen_destacada' => '',
        'beneficios' => [],
        'incluye' => [],
        'no_incluye' => [],
        'salidas' => [],
        'cupos_min' => null,
        'cupos_max' => null,
        'destinos' => [],
        'circuitos' => [],
        'actualizado_en' => null,
    ]);
}

$datos = [
    'nombre' => $paqueteSeleccionado['nombre'] ?? '',
    'estado' => $paqueteSeleccionado['estado'] ?? 'borrador',
    'duracion' => $paqueteSeleccionado['duracion'] ?? '',
    'moneda' => $paqueteSeleccionado['moneda'] ?? 'PEN',
    'precio_desde' => $paqueteSeleccionado['precio_desde'] !== null ? (string) $paqueteSeleccionado['precio_desde'] : '',
    'descripcion_breve' => $paqueteSeleccionado['descripcion_breve'] ?? '',
    'descripcion_detallada' => $paqueteSeleccionado['descripcion_detallada'] ?? '',
    'imagen_destacada' => $paqueteSeleccionado['imagen_destacada'] ?? '',
    'beneficios' => implode(PHP_EOL, $paqueteSeleccionado['beneficios'] ?? []),
    'incluye' => implode(PHP_EOL, $paqueteSeleccionado['incluye'] ?? []),
    'no_incluye' => implode(PHP_EOL, $paqueteSeleccionado['no_incluye'] ?? []),
    'salidas' => implode(PHP_EOL, $paqueteSeleccionado['salidas'] ?? []),
    'cupos_min' => $paqueteSeleccionado['cupos_min'] !== null ? (string) $paqueteSeleccionado['cupos_min'] : '',
    'cupos_max' => $paqueteSeleccionado['cupos_max'] !== null ? (string) $paqueteSeleccionado['cupos_max'] : '',
    'destinos' => $paqueteSeleccionado['destinos'] ?? [],
    'circuitos' => $paqueteSeleccionado['circuitos'] ?? [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores)) {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? $datos['nombre']));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? $datos['estado'])));
    $datos['duracion'] = trim((string) ($_POST['duracion'] ?? $datos['duracion']));
    $datos['moneda'] = strtoupper(trim((string) ($_POST['moneda'] ?? $datos['moneda'])));
    $datos['precio_desde'] = trim((string) ($_POST['precio_desde'] ?? $datos['precio_desde']));
    $datos['descripcion_breve'] = trim((string) ($_POST['descripcion_breve'] ?? $datos['descripcion_breve']));
    $datos['descripcion_detallada'] = trim((string) ($_POST['descripcion_detallada'] ?? $datos['descripcion_detallada']));
    $datos['imagen_destacada'] = trim((string) ($_POST['imagen_destacada'] ?? $datos['imagen_destacada']));
    $datos['beneficios'] = trim((string) ($_POST['beneficios'] ?? $datos['beneficios']));
    $datos['incluye'] = trim((string) ($_POST['incluye'] ?? $datos['incluye']));
    $datos['no_incluye'] = trim((string) ($_POST['no_incluye'] ?? $datos['no_incluye']));
    $datos['salidas'] = trim((string) ($_POST['salidas'] ?? $datos['salidas']));
    $datos['cupos_min'] = trim((string) ($_POST['cupos_min'] ?? $datos['cupos_min']));
    $datos['cupos_max'] = trim((string) ($_POST['cupos_max'] ?? $datos['cupos_max']));
    $datos['destinos'] = isset($_POST['destinos']) ? array_map('intval', (array) $_POST['destinos']) : [];
    $datos['circuitos'] = isset($_POST['circuitos']) ? array_map('intval', (array) $_POST['circuitos']) : [];

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre del paquete es obligatorio.';
    }

    if (!array_key_exists($datos['estado'], $estadosPermitidos)) {
        $errores[] = 'El estado seleccionado no es válido.';
    }

    if ($datos['moneda'] === '' || !array_key_exists($datos['moneda'], $monedasPermitidas)) {
        $datos['moneda'] = 'PEN';
    }

    if ($datos['duracion'] === '') {
        $errores[] = 'La duración es obligatoria.';
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
        foreach ($paquetes as &$paquete) {
            if ($paquete['id'] === $paqueteId) {
                $paquete['nombre'] = $datos['nombre'];
                $paquete['estado'] = $datos['estado'];
                $paquete['duracion'] = $datos['duracion'];
                $paquete['moneda'] = $datos['moneda'];
                $paquete['precio_desde'] = $precioDesde;
                $paquete['descripcion_breve'] = $datos['descripcion_breve'];
                $paquete['descripcion_detallada'] = $datos['descripcion_detallada'];
                $paquete['imagen_destacada'] = $datos['imagen_destacada'];
                $paquete['beneficios'] = $beneficios;
                $paquete['incluye'] = $incluye;
                $paquete['no_incluye'] = $noIncluye;
                $paquete['salidas'] = $salidas;
                $paquete['cupos_min'] = $cuposMin;
                $paquete['cupos_max'] = $cuposMax;
                $paquete['destinos'] = $datos['destinos'];
                $paquete['circuitos'] = $datos['circuitos'];
                $paquete['actualizado_en'] = date('c');
                break;
            }
        }
        unset($paquete);

        $paquetes = paquetesOrdenarPaquetes($paquetes);

        try {
            ServicioAlmacenamientoJson::guardar($archivoPaquetes, $paquetes);
            header('Location: paquetes.php?actualizado=1');
            exit;
        } catch (RuntimeException $exception) {
            $errores[] = 'Los cambios se aplicaron, pero no se pudieron guardar.';
        }
    }
}

$paginaActiva = 'paquetes_editar';
$tituloPagina = 'Editar paquete — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Editar paquete</h1>
            <p>Actualiza la información comercial y operativa del paquete seleccionado.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="paquetes.php">← Volver al listado</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>No pudimos actualizar el paquete:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-grid">
            <input type="hidden" name="paquete_id" value="<?= (int) $paqueteId; ?>" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del paquete *</label>
                    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
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
                    <input type="text" id="duracion" name="duracion" required value="<?= htmlspecialchars($datos['duracion'], ENT_QUOTES, 'UTF-8'); ?>" />
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
                    <input type="text" id="precio_desde" name="precio_desde" value="<?= htmlspecialchars($datos['precio_desde'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="cupos_min">Cupo mínimo</label>
                    <input type="number" id="cupos_min" name="cupos_min" min="0" value="<?= htmlspecialchars($datos['cupos_min'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="admin-field">
                    <label for="cupos_max">Cupo máximo</label>
                    <input type="number" id="cupos_max" name="cupos_max" min="0" value="<?= htmlspecialchars($datos['cupos_max'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <div class="admin-field">
                <label for="descripcion_breve">Descripción breve</label>
                <textarea id="descripcion_breve" name="descripcion_breve" rows="3"><?= htmlspecialchars($datos['descripcion_breve'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="admin-field">
                <label for="descripcion_detallada">Descripción detallada</label>
                <textarea id="descripcion_detallada" name="descripcion_detallada" rows="5"><?= htmlspecialchars($datos['descripcion_detallada'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="admin-field">
                <label for="imagen_destacada">Imagen destacada</label>
                <input type="text" id="imagen_destacada" name="imagen_destacada" value="<?= htmlspecialchars($datos['imagen_destacada'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="beneficios">Beneficios destacados</label>
                    <textarea id="beneficios" name="beneficios" rows="4"><?= htmlspecialchars($datos['beneficios'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="salidas">Fechas de salida</label>
                    <textarea id="salidas" name="salidas" rows="4"><?= htmlspecialchars($datos['salidas'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="incluye">Incluye</label>
                    <textarea id="incluye" name="incluye" rows="5"><?= htmlspecialchars($datos['incluye'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="no_incluye">No incluye</label>
                    <textarea id="no_incluye" name="no_incluye" rows="5"><?= htmlspecialchars($datos['no_incluye'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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
                <button type="submit" class="admin-button">Guardar cambios</button>
                <a class="admin-button secondary" href="paquetes.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
