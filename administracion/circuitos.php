<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

require_once __DIR__ . '/includes/circuitos_util.php';

use Aplicacion\Servicios\ServicioUrls;

$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($destinosPredeterminados, $errores);
$circuitos = cargarCircuitos($circuitosPredeterminados, $destinosDisponibles, $errores);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'delete') {
        $circuitoId = isset($_POST['circuito_id']) ? (int) $_POST['circuito_id'] : 0;
        if ($circuitoId <= 0) {
            $errores[] = 'No se pudo identificar el circuito a eliminar.';
        } else {
            $eliminado = eliminarCircuitoCatalogo($circuitoId, $errores);
            if ($eliminado) {
                $mensajeExito = 'Circuito eliminado correctamente.';
            } elseif (empty($errores)) {
                $errores[] = 'El circuito indicado ya no existe.';
            }

            $circuitos = cargarCircuitos($circuitosPredeterminados, $destinosDisponibles, $errores);
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if (isset($_GET['creado'])) {
    $mensajeExito = 'Circuito creado correctamente.';
}

if (isset($_GET['actualizado'])) {
    $mensajeExito = 'Circuito actualizado correctamente.';
}

if ($mensajeExito === null && empty($errores)) {
    $usaPredeterminados = !empty($circuitos)
        && array_reduce(
            $circuitos,
            static fn (bool $carry, array $circuito): bool => $carry && ($circuito['es_predeterminado'] ?? false),
            true
        );

    if ($usaPredeterminados) {
        $mensajeInfo = 'Actualmente se muestran circuitos de referencia. Agrega el primero para personalizar tu catálogo.';
    }
}

$paginaActiva = 'circuitos_registrados';
$tituloPagina = 'Circuitos — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Circuitos registrados</h1>
            <p>Revisa los circuitos disponibles y gestiona su información comercial y operativa.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button" href="crear_circuito.php">+ Nuevo circuito</a>
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
        <?php if (empty($circuitos)): ?>
            <p class="admin-empty">Aún no hay circuitos personalizados. Registra el primero para combinarlo en paquetes.</p>
        <?php else: ?>
            <div class="admin-table-wrapper">
                <table class="admin-table" aria-label="Listado de circuitos">
                    <thead>
                        <tr>
                            <th>Circuito</th>
                            <th>Programación</th>
                            <th>Contenido</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($circuitos as $circuito): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($circuito['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <p class="admin-table__meta">Destino: <?= htmlspecialchars(obtenerNombreDestinoCircuito($circuito), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-badge admin-badge--<?= htmlspecialchars($circuito['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars(estadoCircuitoEtiqueta($circuito['estado']), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </td>
                                <td>
                                    <ul class="admin-table__list">
                                        <li><strong>Duración:</strong> <?= htmlspecialchars(mostrarDuracionCircuito($circuito['duracion']), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <li><strong>Tarifa desde:</strong> <?= htmlspecialchars(circuitosFormatearPrecio($circuito['precio'] ?? null, $circuito['moneda'] ?? 'PEN'), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php if ($circuito['frecuencia'] !== ''): ?>
                                            <li><strong>Frecuencia:</strong> <?= htmlspecialchars($circuito['frecuencia'], ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <?php $tamanoGrupo = trim((string) ($circuito['tamano_grupo'] ?? '')); ?>
                                        <?php if ($tamanoGrupo !== ''): ?>
                                            <li><strong>Tamaño del grupo:</strong> <?= htmlspecialchars($tamanoGrupo, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <?php $idiomasTexto = circuitosIdiomasComoTexto($circuito['idiomas'] ?? []); ?>
                                        <?php if ($idiomasTexto !== ''): ?>
                                            <li><strong>Idiomas:</strong> <?= htmlspecialchars($idiomasTexto, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <li><strong>Categoría:</strong> <?= htmlspecialchars(categoriaCircuitoEtiqueta($circuito['categoria']), ENT_QUOTES, 'UTF-8'); ?></li>
                                        <li><strong>Dificultad:</strong> <?= htmlspecialchars(dificultadCircuitoEtiqueta($circuito['dificultad']), ENT_QUOTES, 'UTF-8'); ?></li>
                                    </ul>
                                </td>
                                <td>
                                    <?php if ($circuito['descripcion'] !== ''): ?>
                                        <p><?= nl2br(htmlspecialchars($circuito['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($circuito['itinerario'])): ?>
                                        <p class="admin-table__meta"><strong>Itinerario (resumen):</strong></p>
                                        <ol class="admin-table__list">
                                            <?php foreach (array_slice($circuito['itinerario'], 0, 3) as $paso): ?>
                                                <li>
                                                    <?php $momento = trim((string) ($paso['dia'] ?? '')); ?>
                                                    <?php $hora = trim((string) ($paso['hora'] ?? '')); ?>
                                                    <?php if ($momento !== ''): ?>
                                                        <strong><?= htmlspecialchars($momento, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <?php endif; ?>
                                                    <?php if ($hora !== ''): ?>
                                                        <span><?= htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($paso['titulo'])): ?>
                                                        — <?= htmlspecialchars((string) $paso['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                        <?php if (count($circuito['itinerario']) > 3): ?>
                                            <p class="admin-help">+<?= count($circuito['itinerario']) - 3; ?> actividades adicionales.</p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php
                                        $serviciosIncluidos = $circuito['servicios_incluidos_detalles'] ?? $circuito['servicios_incluidos'] ?? [];
                                        $serviciosIncluidos = is_array($serviciosIncluidos) ? $serviciosIncluidos : [];
                                    ?>
                                    <?php if (!empty($serviciosIncluidos)): ?>
                                        <p class="admin-table__meta"><strong>Incluye:</strong></p>
                                        <ul class="admin-table__list">
                                            <?php foreach ($serviciosIncluidos as $servicio):
                                                if (is_array($servicio)) {
                                                    $nombreServicio = trim((string) ($servicio['nombre'] ?? $servicio['label'] ?? ''));
                                                    $iconoServicio = trim((string) ($servicio['icono'] ?? $servicio['icon'] ?? ''));
                                                } else {
                                                    $nombreServicio = trim((string) $servicio);
                                                    $iconoServicio = '';
                                                }
                                                if ($nombreServicio === '') {
                                                    continue;
                                                }
                                            ?>
                                                <li class="admin-service-item">
                                                    <?php if ($iconoServicio !== ''): ?>
                                                        <span class="admin-service-item__icon"><?= htmlspecialchars($iconoServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($nombreServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php
                                        $serviciosExcluidos = $circuito['servicios_excluidos_detalles'] ?? $circuito['servicios_excluidos'] ?? [];
                                        $serviciosExcluidos = is_array($serviciosExcluidos) ? $serviciosExcluidos : [];
                                    ?>
                                    <?php if (!empty($serviciosExcluidos)): ?>
                                        <p class="admin-table__meta"><strong>No incluye:</strong></p>
                                        <ul class="admin-table__list">
                                            <?php foreach ($serviciosExcluidos as $servicio):
                                                if (is_array($servicio)) {
                                                    $nombreServicio = trim((string) ($servicio['nombre'] ?? $servicio['label'] ?? ''));
                                                    $iconoServicio = trim((string) ($servicio['icono'] ?? $servicio['icon'] ?? ''));
                                                } else {
                                                    $nombreServicio = trim((string) $servicio);
                                                    $iconoServicio = '';
                                                }
                                                if ($nombreServicio === '') {
                                                    continue;
                                                }
                                            ?>
                                                <li class="admin-service-item">
                                                    <?php if ($iconoServicio !== ''): ?>
                                                        <span class="admin-service-item__icon admin-service-item__icon--danger"><?= htmlspecialchars($iconoServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($nombreServicio, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(formatearMarcaTiempo($circuito['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="admin-table__actions">
                                        <?php
                                            $circuitoUrl = ServicioUrls::circuito([
                                                'slug' => $circuito['slug'] ?? '',
                                                'region' => $circuito['region'] ?? '',
                                                'destino' => $circuito['destino']['nombre'] ?? ($circuito['destino'] ?? ''),
                                                'nombre' => $circuito['nombre'] ?? '',
                                            ]);
                                            $circuitoUrl = '../web/' . ltrim($circuitoUrl, '/');
                                        ?>
                                        <a
                                            class="admin-chip"
                                            href="<?= htmlspecialchars($circuitoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >Ver</a>
                                        <a class="admin-chip" href="editar_circuito.php?id=<?= (int) $circuito['id']; ?>">Editar</a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar el circuito seleccionado?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="circuito_id" value="<?= (int) $circuito['id']; ?>" />
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
