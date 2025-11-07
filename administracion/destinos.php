<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/destinos_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';

$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

$destinosPredeterminados = obtenerDestinosPredeterminados();
$destinos = cargarDestinosCatalogo($archivoDestinos, $destinosPredeterminados, $errores);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'delete') {
        $identificador = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
        if ($identificador <= 0) {
            $errores[] = 'No se pudo identificar el destino a eliminar.';
        } else {
            $cantidadInicial = count($destinos);
            $destinos = array_values(array_filter(
                $destinos,
                static fn (array $destino): bool => $destino['id'] !== $identificador
            ));

            if ($cantidadInicial === count($destinos)) {
                $errores[] = 'El destino indicado ya no existe.';
            } else {
                try {
                    ServicioAlmacenamientoJson::guardar($archivoDestinos, $destinos);
                    $mensajeExito = 'Destino eliminado correctamente.';
                } catch (RuntimeException $exception) {
                    $errores[] = 'El destino se eliminó, pero no se pudo actualizar el almacenamiento.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if (isset($_GET['creado'])) {
    $mensajeExito = 'Destino agregado correctamente.';
}

if (isset($_GET['actualizado'])) {
    $mensajeExito = 'Destino actualizado correctamente.';
}

if ($mensajeExito === null && empty($errores) && !is_file($archivoDestinos)) {
    $mensajeInfo = 'Actualmente se utilizan los destinos de ejemplo. Agrega el primero para crear tu propio catálogo.';
}

$paginaActiva = 'destinos_registrados';
$tituloPagina = 'Destinos — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Destinos registrados</h1>
            <p>Consulta y administra los destinos base que se utilizan para armar circuitos y paquetes turísticos.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button" href="crear_destino.php">+ Nuevo destino</a>
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

    <section class="admin-card">
        <?php if (empty($destinos)): ?>
            <p class="admin-empty">Aún no hay destinos guardados. Crea el primero para comenzar a construir tus circuitos.</p>
        <?php else: ?>
            <div class="admin-table-wrapper">
                <table class="admin-table" aria-label="Listado de destinos registrados">
                    <thead>
                        <tr>
                            <th>Destino</th>
                            <th>Detalles</th>
                            <th>Etiquetas</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinos as $destino): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($destino['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <p class="admin-table__meta">Región: <?= htmlspecialchars($destino['region'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-badge admin-badge--<?= htmlspecialchars($destino['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars(estadoDestinoEtiqueta($destino['estado']), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </td>
                                <td>
                                    <?php if ($destino['descripcion'] !== ''): ?>
                                        <p><?= nl2br(htmlspecialchars($destino['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php else: ?>
                                        <p class="admin-table__meta">Sin descripción registrada.</p>
                                    <?php endif; ?>
                                    <ul class="admin-table__list">
                                        <?php if ($destino['tagline'] !== ''): ?>
                                            <li><strong>Frase:</strong> <?= htmlspecialchars($destino['tagline'], ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <?php if ($destino['imagen'] !== ''): ?>
                                            <li><strong>Imagen:</strong> <?= htmlspecialchars($destino['imagen'], ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endif; ?>
                                        <?php if ($destino['latitud'] !== null && $destino['longitud'] !== null): ?>
                                            <li><strong>Coordenadas:</strong> <?= htmlspecialchars((string) $destino['latitud']); ?>, <?= htmlspecialchars((string) $destino['longitud']); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php if (empty($destino['tags'])): ?>
                                        <span class="admin-table__meta">Sin etiquetas</span>
                                    <?php else: ?>
                                        <ul class="admin-tags">
                                            <?php foreach ($destino['tags'] as $tag): ?>
                                                <li><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(formatearMarcaTiempo($destino['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="admin-table__actions">
                                        <a class="admin-chip" href="editar_destino.php?id=<?= (int) $destino['id']; ?>">Editar</a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar el destino seleccionado?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="destino_id" value="<?= (int) $destino['id']; ?>" />
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
