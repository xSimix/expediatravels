<?php

declare(strict_types=1);

use Aplicacion\Repositorios\RepositorioServicios;

require __DIR__ . '/../app/configuracion/arranque.php';

$repositorioServicios = new RepositorioServicios();
$feedback = null;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'create') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $icono = trim((string) ($_POST['icono'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $activo = isset($_POST['activo']);

        if ($nombre === '') {
            $errores[] = 'El nombre del servicio es obligatorio.';
        }

        if ($icono === '') {
            $errores[] = 'Debes asignar un icono al servicio (por ejemplo, un emoji o código).';
        }

        if (empty($errores)) {
            $servicioId = $repositorioServicios->crear($nombre, $descripcion !== '' ? $descripcion : null, $icono, $activo);
            if ($servicioId > 0) {
                $feedback = ['type' => 'success', 'message' => 'Servicio agregado correctamente al catálogo.'];
            } else {
                $feedback = ['type' => 'error', 'message' => 'No se pudo guardar el servicio. Inténtalo nuevamente.'];
            }
        } else {
            $feedback = ['type' => 'error', 'message' => 'Revisa la información ingresada antes de guardar.'];
        }
    } elseif ($accion === 'update') {
        $id = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $icono = trim((string) ($_POST['icono'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $activo = isset($_POST['activo']);

        if ($id <= 0) {
            $errores[] = 'El servicio seleccionado no es válido.';
        }
        if ($nombre === '') {
            $errores[] = 'El nombre del servicio es obligatorio.';
        }
        if ($icono === '') {
            $errores[] = 'Debes mantener un icono para el servicio.';
        }

        if (empty($errores)) {
            $resultado = $repositorioServicios->actualizar($id, [
                'nombre' => $nombre,
                'icono' => $icono,
                'descripcion' => $descripcion,
                'activo' => $activo,
            ]);
            if ($resultado) {
                $feedback = ['type' => 'success', 'message' => 'Servicio actualizado correctamente.'];
            } else {
                $feedback = ['type' => 'error', 'message' => 'No se pudo actualizar el servicio seleccionado.'];
            }
        } else {
            $feedback = ['type' => 'error', 'message' => 'Revisa los datos del servicio antes de guardar.'];
        }
    } elseif ($accion === 'delete') {
        $id = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        if ($id > 0) {
            if ($repositorioServicios->eliminar($id)) {
                $feedback = ['type' => 'success', 'message' => 'Servicio eliminado del catálogo.'];
            } else {
                $feedback = ['type' => 'error', 'message' => 'No se pudo eliminar el servicio seleccionado.'];
            }
        } else {
            $feedback = ['type' => 'error', 'message' => 'El servicio seleccionado no es válido.'];
        }
    }
}

$servicios = $repositorioServicios->listar();

$paginaActiva = 'ajustes_contenido';
$tituloPagina = 'Ajustes de contenido — Expediatravels';
$estilosExtra = ['recursos/panel-admin.css'];
$scriptsExtra = ['recursos/servicios-iconos.js', 'recursos/servicios-admin.js'];

$paginaSolicitada = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1],
]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paginaSolicitada = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, [
        'options' => ['default' => $paginaSolicitada ?? 1, 'min_range' => 1],
    ]);
}

$serviciosPorPagina = 10;
$totalServicios = count($servicios);
$totalPaginas = max(1, (int) ceil($totalServicios / $serviciosPorPagina));
$paginaActual = min($paginaSolicitada ?? 1, $totalPaginas);
$offset = ($paginaActual - 1) * $serviciosPorPagina;
$serviciosPaginados = array_slice($servicios, $offset, $serviciosPorPagina);

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Ajustes de contenido</h1>
        <p>Administra el catálogo de servicios que se muestran como incluidos o no incluidos en los circuitos.</p>
    </header>

    <?php if (!empty($feedback)): ?>
        <div class="admin-alert<?= ($feedback['type'] ?? '') === 'error' ? ' error' : ''; ?>">
            <?= htmlspecialchars((string) ($feedback['message'] ?? '')); ?>
            <?php if (!empty($errores)): ?>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Agregar nuevo servicio</h2>
        <form method="post" class="service-create-form">
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="page" value="<?= $paginaActual; ?>" />
            <div class="service-create-form__grid">
                <div class="admin-field">
                    <label for="nuevo_nombre">Nombre del servicio *</label>
                    <input type="text" id="nuevo_nombre" name="nombre" placeholder="Traslados turísticos" required />
                </div>
                <div class="admin-field service-create-form__icon-field">
                    <label for="nuevo_icono">Icono *</label>
                    <div class="service-create-form__icon-input">
                        <input type="text" id="nuevo_icono" name="icono" maxlength="64" placeholder="fa-solid fa-bus" required data-icon-preview-target="#nuevo_icono_preview" />
                        <div class="icon-preview" id="nuevo_icono_preview" data-icon-preview data-icon-fallback="⦿" aria-hidden="true"></div>
                    </div>
                    <p class="admin-help">Puedes usar emojis o clases de Font Awesome (por ejemplo: fa-solid fa-bus).</p>
                    <div class="icon-picker-search" data-icon-search-container>
                        <input type="search" class="icon-picker-search__input" placeholder="Buscar iconos (ej. bus)" aria-label="Buscar iconos en Font Awesome" data-icon-search-input />
                        <button type="button" class="admin-button secondary icon-picker-search__button" data-icon-search-button data-icon-search-target="#nuevo_icono">Buscar en Font Awesome</button>
                    </div>
                    <p class="admin-help">La búsqueda se abrirá en una nueva pestaña en fontawesome.com.</p>
                </div>
                <div class="service-create-form__availability">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="activo" value="1" checked />
                        <span>Servicio activo y disponible para selección</span>
                    </label>
                </div>
                <div class="admin-field service-create-form__description">
                    <label for="nuevo_descripcion">Descripción</label>
                    <textarea id="nuevo_descripcion" name="descripcion" rows="3" placeholder="Detalle opcional para el equipo comercial."></textarea>
                </div>
            </div>
            <div class="service-create-form__actions">
                <button type="submit" class="admin-button">Guardar servicio</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Catálogo de servicios disponibles</h2>
        <?php if (empty($servicios)): ?>
            <p class="admin-help">Aún no registras servicios. Agrega tus primeras opciones para mostrarlas en los circuitos.</p>
        <?php else: ?>
            <?php $queryBase = $_GET; unset($queryBase['page']); ?>
            <div class="admin-table-wrapper">
                <table class="admin-table content-services-table">
                    <thead>
                        <tr>
                            <th scope="col">Icono</th>
                            <th scope="col">Servicio</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="content-services-table__actions-heading">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serviciosPaginados as $servicio):
                            $serviceId = (int) ($servicio['id'] ?? 0);
                            $serviceNombre = (string) ($servicio['nombre'] ?? '');
                            $serviceIcono = (string) ($servicio['icono'] ?? '');
                            $serviceDescripcion = (string) ($servicio['descripcion'] ?? '');
                            $serviceActivo = !empty($servicio['activo']);
                            $iconPreviewId = 'icono_preview_' . $serviceId;
                        ?>
                            <tr>
                                <td class="content-services-table__icon-cell">
                                    <span class="content-services-table__icon" id="<?= htmlspecialchars($iconPreviewId, ENT_QUOTES); ?>" data-icon-preview data-icon-fallback="⦿" aria-hidden="true">
                                        <?php if ($serviceIcono !== '' && str_contains($serviceIcono, 'fa-')): ?>
                                            <i class="<?= htmlspecialchars($serviceIcono, ENT_QUOTES); ?>" aria-hidden="true"></i>
                                        <?php else: ?>
                                            <?= $serviceIcono !== '' ? htmlspecialchars($serviceIcono) : '⦿'; ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="content-services-table__service">
                                        <strong class="content-services-table__name"><?= htmlspecialchars($serviceNombre, ENT_QUOTES); ?></strong>
                                        <?php if ($serviceDescripcion !== ''): ?>
                                            <p class="content-services-table__description"><?= nl2br(htmlspecialchars($serviceDescripcion, ENT_QUOTES)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="content-services-table__status<?= $serviceActivo ? ' is-active' : ' is-inactive'; ?>"><?= $serviceActivo ? 'Activo' : 'Inactivo'; ?></span>
                                </td>
                                <td>
                                    <div class="content-services-table__actions">
                                        <button type="button" class="admin-button" data-edit-service data-service-id="<?= $serviceId; ?>" data-service-nombre="<?= htmlspecialchars($serviceNombre, ENT_QUOTES); ?>" data-service-icono="<?= htmlspecialchars($serviceIcono, ENT_QUOTES); ?>" data-service-descripcion="<?= htmlspecialchars($serviceDescripcion, ENT_QUOTES); ?>" data-service-activo="<?= $serviceActivo ? '1' : '0'; ?>" data-service-icon-preview="#<?= htmlspecialchars($iconPreviewId, ENT_QUOTES); ?>">Editar</button>
                                        <form method="post" class="content-services-table__delete-form" onsubmit="return confirm('¿Eliminar el servicio seleccionado?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="service_id" value="<?= $serviceId; ?>" />
                                            <input type="hidden" name="page" value="<?= $paginaActual; ?>" />
                                            <button type="submit" class="admin-button secondary content-services-table__delete-button">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPaginas > 1): ?>
                <nav class="admin-pagination" aria-label="Paginación de servicios">
                    <?php if ($paginaActual > 1): ?>
                        <?php $prevQuery = $queryBase; $prevQuery['page'] = $paginaActual - 1; $prevHref = '?' . http_build_query($prevQuery); ?>
                        <a class="admin-pagination__link admin-pagination__link--prev" href="<?= htmlspecialchars($prevHref, ENT_QUOTES); ?>">&laquo; Anterior</a>
                    <?php endif; ?>
                    <ul class="admin-pagination__list">
                        <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++):
                            $pageQuery = $queryBase;
                            $pageQuery['page'] = $pagina;
                            $pageHref = '?' . http_build_query($pageQuery);
                        ?>
                            <li>
                                <a class="admin-pagination__link<?= $pagina === $paginaActual ? ' is-active' : ''; ?>" href="<?= htmlspecialchars($pageHref, ENT_QUOTES); ?>"<?= $pagina === $paginaActual ? ' aria-current="page"' : ''; ?>><?= $pagina; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <?php $nextQuery = $queryBase; $nextQuery['page'] = $paginaActual + 1; $nextHref = '?' . http_build_query($nextQuery); ?>
                        <a class="admin-pagination__link admin-pagination__link--next" href="<?= htmlspecialchars($nextHref, ENT_QUOTES); ?>">Siguiente &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <div class="service-edit-modal" data-service-modal data-state="hidden" aria-hidden="true" hidden>
        <div class="service-edit-modal__overlay" data-service-modal-close></div>
        <div class="service-edit-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="service-edit-modal-title">
            <form method="post" class="service-edit-modal__form" data-service-modal-form>
                <header class="service-edit-modal__header">
                    <h3 id="service-edit-modal-title">Editar servicio</h3>
                    <button type="button" class="service-edit-modal__close" data-service-modal-close aria-label="Cerrar ventana de edición">&times;</button>
                </header>
                <div class="service-edit-modal__body">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="service_id" data-service-field="id" />
                    <input type="hidden" name="page" value="<?= $paginaActual; ?>" />
                    <div class="service-edit-modal__grid">
                        <div class="admin-field">
                            <label for="modal_nombre">Nombre del servicio *</label>
                            <input type="text" id="modal_nombre" name="nombre" data-service-field="nombre" required />
                        </div>
                        <div class="admin-field service-edit-modal__icon-field">
                            <label for="modal_icono">Icono *</label>
                            <div class="service-edit-modal__icon-input">
                                <input type="text" id="modal_icono" name="icono" maxlength="64" data-service-field="icono" required data-icon-preview-target="#modal_icono_preview" />
                                <div class="icon-preview" id="modal_icono_preview" data-icon-preview data-icon-fallback="⦿" aria-hidden="true"></div>
                            </div>
                            <p class="admin-help">El icono aparece junto al nombre en la web y el panel.</p>
                            <div class="icon-picker-search" data-icon-search-container>
                                <input type="search" class="icon-picker-search__input" placeholder="Buscar iconos (ej. bus)" aria-label="Buscar iconos en Font Awesome" data-icon-search-input />
                                <button type="button" class="admin-button secondary icon-picker-search__button" data-icon-search-button data-icon-search-target="#modal_icono">Buscar en Font Awesome</button>
                            </div>
                        </div>
                        <div class="service-edit-modal__availability">
                            <label class="admin-checkbox">
                                <input type="checkbox" name="activo" value="1" data-service-field="activo" />
                                <span>Servicio activo y disponible</span>
                            </label>
                        </div>
                        <div class="admin-field service-edit-modal__description">
                            <label for="modal_descripcion">Descripción</label>
                            <textarea id="modal_descripcion" name="descripcion" rows="3" data-service-field="descripcion" placeholder="Resumen breve para el equipo."></textarea>
                        </div>
                    </div>
                </div>
                <footer class="service-edit-modal__footer">
                    <button type="submit" class="admin-button">Guardar cambios</button>
                    <button type="button" class="admin-button secondary" data-service-modal-close>Cancelar</button>
                </footer>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
