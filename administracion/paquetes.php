<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/paquetes_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';
$archivoPaquetes = __DIR__ . '/../almacenamiento/paquetes.json';

$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'delete') {
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
                } catch (RuntimeException $exception) {
                    $errores[] = 'El paquete se eliminó, pero no se pudo actualizar el almacenamiento.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if (isset($_GET['creado'])) {
    $mensajeExito = 'Paquete creado correctamente.';
}

if (isset($_GET['actualizado'])) {
    $mensajeExito = 'Paquete actualizado correctamente.';
}

if ($mensajeExito === null && empty($errores) && !is_file($archivoPaquetes)) {
    $mensajeInfo = 'Aún no tienes paquetes personalizados. Usa los circuitos y destinos creados para construir uno nuevo.';
}

$paginaActiva = 'paquetes_registrados';
$tituloPagina = 'Paquetes — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Paquetes registrados</h1>
            <p>Consulta los paquetes vigentes y gestiona su composición, tarifas y estado de publicación.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button" href="crear_paquete.php">+ Nuevo paquete</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>No pudimos completar la acción solicitada:</strong></p>
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

    <section class="admin-card admin-card--flush">
        <?php if (empty($paquetes)): ?>
            <p class="admin-empty">Aún no hay paquetes disponibles. Combina circuitos y servicios para crear tu primer producto.</p>
        <?php else: ?>
            <div class="admin-table-wrapper">
                <table class="admin-table" aria-label="Listado de paquetes turísticos">
                    <thead>
                        <tr>
                            <th>Paquete</th>
                            <th>Componentes</th>
                            <th>Detalles</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paquetes as $paquete): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($paquete['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <p class="admin-table__meta">Duración: <?= htmlspecialchars($paquete['duracion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-table__meta">Tarifa desde: <?= htmlspecialchars(paquetesFormatearPrecioDesde($paquete['precio_desde'], $paquete['moneda'], $monedasPermitidas), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-badge admin-badge--<?= htmlspecialchars($paquete['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars(estadoPaqueteEtiqueta($paquete['estado']), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </td>
                                <td>
                                    <p class="admin-table__meta"><strong>Destinos:</strong></p>
                                    <ul class="admin-table__list">
                                        <?php foreach ($paquete['destinos'] as $destinoId): ?>
                                            <li><?= htmlspecialchars($destinosDisponibles[$destinoId]['nombre'] ?? ('ID ' . $destinoId), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="admin-table__meta"><strong>Circuitos:</strong></p>
                                    <ul class="admin-table__list">
                                        <?php foreach ($paquete['circuitos'] as $circuitoId): ?>
                                            <li><?= htmlspecialchars($circuitosDisponibles[$circuitoId]['nombre'] ?? ('ID ' . $circuitoId), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php if ($paquete['descripcion_breve'] !== ''): ?>
                                        <p><?= nl2br(htmlspecialchars($paquete['descripcion_breve'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php endif; ?>
                                    <ul class="admin-table__list">
                                        <li><strong>Cupos:</strong> <?= htmlspecialchars(paquetesFormatearCupos($paquete['cupos_min'], $paquete['cupos_max']), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php if (!empty($paquete['salidas'])): ?>
                                            <li><strong>Salidas:</strong> <?= htmlspecialchars(implode(' · ', $paquete['salidas']), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($paquete['beneficios'])): ?>
                                            <li><strong>Beneficios:</strong>
                                                <ul class="admin-table__list">
                                                    <?php foreach ($paquete['beneficios'] as $beneficio): ?>
                                                        <li><?= htmlspecialchars($beneficio, ENT_QUOTES, 'UTF-8'); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!empty($paquete['incluye'])): ?>
                                            <li><strong>Incluye:</strong>
                                                <ul class="admin-table__list">
                                                    <?php foreach ($paquete['incluye'] as $item): ?>
                                                        <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!empty($paquete['no_incluye'])): ?>
                                            <li><strong>No incluye:</strong>
                                                <ul class="admin-table__list">
                                                    <?php foreach ($paquete['no_incluye'] as $item): ?>
                                                        <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </td>
                                <td><?= htmlspecialchars(formatearMarcaTiempo($paquete['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="admin-table__actions">
                                        <a class="admin-chip" href="editar_paquete.php?id=<?= (int) $paquete['id']; ?>">Editar</a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar el paquete seleccionado?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="paquete_id" value="<?= (int) $paquete['id']; ?>" />
                                            <button type="submit" class="admin-chip admin-chip--danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
