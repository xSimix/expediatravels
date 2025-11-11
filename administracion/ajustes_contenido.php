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
        $tipo = ($_POST['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido';
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $activo = isset($_POST['activo']);

        if ($nombre === '') {
            $errores[] = 'El nombre del servicio es obligatorio.';
        }

        if ($icono === '') {
            $errores[] = 'Debes asignar un icono al servicio (por ejemplo, un emoji o código).';
        }

        if (empty($errores)) {
            $servicioId = $repositorioServicios->crear($nombre, $tipo, $descripcion !== '' ? $descripcion : null, $icono, $activo);
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
        $tipo = ($_POST['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido';
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
                'tipo' => $tipo,
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
$scriptsExtra = ['recursos/servicios-iconos.js'];

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
        <form method="post" class="admin-grid two-columns">
            <input type="hidden" name="action" value="create" />
            <div class="admin-field">
                <label for="nuevo_nombre">Nombre del servicio *</label>
                <input type="text" id="nuevo_nombre" name="nombre" placeholder="Traslados turísticos" required />
            </div>
            <div class="admin-field">
                <label for="nuevo_icono">Icono *</label>
                <input type="text" id="nuevo_icono" name="icono" maxlength="64" placeholder="fa-solid fa-bus" required data-icon-preview-target="#nuevo_icono_preview" />
                <div class="icon-preview" id="nuevo_icono_preview" data-icon-preview data-icon-fallback="⦿" aria-hidden="true"></div>
                <p class="admin-help">Puedes usar emojis o clases de Font Awesome (por ejemplo: fa-solid fa-bus).</p>
                <div class="icon-picker-search" data-icon-search-container>
                    <input type="search" class="icon-picker-search__input" placeholder="Buscar iconos (ej. bus)" aria-label="Buscar iconos en Font Awesome" data-icon-search-input />
                    <button type="button" class="admin-button secondary icon-picker-search__button" data-icon-search-button data-icon-search-target="#nuevo_icono">Buscar en Font Awesome</button>
                </div>
                <p class="admin-help">La búsqueda se abrirá en una nueva pestaña en fontawesome.com.</p>
            </div>
            <div class="admin-field">
                <label for="nuevo_tipo">Se sugiere como</label>
                <select id="nuevo_tipo" name="tipo">
                    <option value="incluido">Servicio incluido</option>
                    <option value="excluido">Servicio no incluido</option>
                </select>
            </div>
            <div class="admin-field">
                <label for="nuevo_descripcion">Descripción</label>
                <textarea id="nuevo_descripcion" name="descripcion" rows="3" placeholder="Detalle opcional para el equipo comercial."></textarea>
            </div>
            <div class="content-service__availability">
                <label class="admin-checkbox">
                    <input type="checkbox" name="activo" value="1" checked />
                    <span>Servicio activo y disponible para selección</span>
                </label>
            </div>
            <div class="admin-actions admin-actions--full">
                <button type="submit" class="admin-button">Guardar servicio</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Catálogo de servicios disponibles</h2>
        <?php if (empty($servicios)): ?>
            <p class="admin-help">Aún no registras servicios. Agrega tus primeras opciones para mostrarlas en los circuitos.</p>
        <?php else: ?>
            <div class="content-services">
                <?php foreach ($servicios as $servicio):
                    $serviceId = (int) ($servicio['id'] ?? 0);
                    $serviceNombre = (string) ($servicio['nombre'] ?? '');
                    $serviceIcono = (string) ($servicio['icono'] ?? '');
                    $serviceTipo = ($servicio['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido';
                    $serviceDescripcion = (string) ($servicio['descripcion'] ?? '');
                    $serviceActivo = !empty($servicio['activo']);
                ?>
                    <?php
                        $iconInputId = 'icono_servicio_' . $serviceId;
                        $iconSearchId = 'icono_buscar_' . $serviceId;
                        $iconPreviewId = 'icono_preview_' . $serviceId;
                    ?>
                    <form method="post" class="content-service">
                        <input type="hidden" name="service_id" value="<?= $serviceId; ?>" />
                        <div class="content-service__header">
                            <div class="content-service__icon" id="<?= htmlspecialchars($iconPreviewId, ENT_QUOTES); ?>" data-icon-preview data-icon-fallback="⦿" aria-hidden="true">
                                <?php if ($serviceIcono !== '' && str_contains($serviceIcono, 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($serviceIcono, ENT_QUOTES); ?>" aria-hidden="true"></i>
                                <?php else: ?>
                                    <?= $serviceIcono !== '' ? htmlspecialchars($serviceIcono) : '⦿'; ?>
                                <?php endif; ?>
                            </div>
                            <div class="admin-field">
                                <label>Nombre</label>
                                <input type="text" name="nombre" value="<?= htmlspecialchars($serviceNombre, ENT_QUOTES); ?>" required />
                            </div>
                        </div>
                        <div class="content-service__grid">
                            <div class="admin-field">
                                <label for="<?= htmlspecialchars($iconInputId, ENT_QUOTES); ?>">Icono *</label>
                                <input type="text" id="<?= htmlspecialchars($iconInputId, ENT_QUOTES); ?>" name="icono" value="<?= htmlspecialchars($serviceIcono, ENT_QUOTES); ?>" maxlength="64" required data-icon-preview-target="#<?= htmlspecialchars($iconPreviewId, ENT_QUOTES); ?>" />
                                <p class="admin-help">Se muestra junto al nombre en el panel y en la web pública.</p>
                                <div class="icon-picker-search" data-icon-search-container>
                                    <input type="search" class="icon-picker-search__input" id="<?= htmlspecialchars($iconSearchId, ENT_QUOTES); ?>" placeholder="Buscar iconos (ej. bus)" aria-label="Buscar iconos en Font Awesome" data-icon-search-input />
                                    <button type="button" class="admin-button secondary icon-picker-search__button" data-icon-search-button data-icon-search-target="#<?= htmlspecialchars($iconInputId, ENT_QUOTES); ?>">Buscar en Font Awesome</button>
                                </div>
                                <p class="admin-help">La búsqueda se abrirá en una nueva pestaña en fontawesome.com.</p>
                            </div>
                            <div class="admin-field">
                                <label>Tipo sugerido</label>
                                <select name="tipo">
                                    <option value="incluido"<?= $serviceTipo === 'incluido' ? ' selected' : ''; ?>>Incluido</option>
                                    <option value="excluido"<?= $serviceTipo === 'excluido' ? ' selected' : ''; ?>>No incluido</option>
                                </select>
                            </div>
                            <div class="admin-field">
                                <label>Descripción</label>
                                <textarea name="descripcion" rows="2" placeholder="Resumen breve para el equipo."><?= htmlspecialchars($serviceDescripcion, ENT_QUOTES); ?></textarea>
                            </div>
                            <div class="admin-field content-service__status">
                                <label class="admin-checkbox">
                                    <input type="checkbox" name="activo" value="1"<?= $serviceActivo ? ' checked' : ''; ?> />
                                    <span>Servicio activo</span>
                                </label>
                            </div>
                        </div>
                        <div class="content-service__actions">
                            <button type="submit" name="action" value="update" class="admin-button">Actualizar</button>
                            <button type="submit" name="action" value="delete" class="admin-button secondary content-service__delete" onclick="return confirm('¿Eliminar el servicio seleccionado?');">Eliminar</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
