<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioEquipo;

$repo = new RepositorioEquipo();

$categorias = [
    RepositorioEquipo::CATEGORIA_ASESOR_VENTAS => 'Asesor de ventas',
    RepositorioEquipo::CATEGORIA_GUIA => 'Guía',
    RepositorioEquipo::CATEGORIA_OPERACIONES => 'Operaciones',
    RepositorioEquipo::CATEGORIA_OTRO => 'Otro',
];

$errores = [];
$mensajeExito = null;

$crearNombre = '';
$crearCargo = '';
$crearTelefono = '';
$crearCorreo = '';
$crearCategoria = RepositorioEquipo::CATEGORIA_ASESOR_VENTAS;
$crearPrioridad = 0;
$crearActivo = 1;

$entradaNombre = '';
$entradaCargo = '';
$entradaTelefono = '';
$entradaCorreo = '';
$entradaCategoria = RepositorioEquipo::CATEGORIA_OTRO;
$entradaPrioridad = 0;
$entradaActivo = 1;

$accion = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entradaNombre = trim((string) ($_POST['nombre'] ?? ''));
    $entradaCargo = trim((string) ($_POST['cargo'] ?? ''));
    $entradaTelefono = trim((string) ($_POST['telefono'] ?? ''));
    $entradaCorreo = trim((string) ($_POST['correo'] ?? ''));
    $entradaCategoria = (string) ($_POST['categoria'] ?? RepositorioEquipo::CATEGORIA_OTRO);
    $entradaPrioridad = isset($_POST['prioridad']) ? (int) $_POST['prioridad'] : 0;
    if ($entradaPrioridad < 0) {
        $entradaPrioridad = 0;
    }
    if ($entradaPrioridad > 999) {
        $entradaPrioridad = 999;
    }
    $entradaActivo = isset($_POST['activo']) ? 1 : 0;

    if (!array_key_exists($entradaCategoria, $categorias)) {
        $entradaCategoria = RepositorioEquipo::CATEGORIA_OTRO;
    }

    if ($accion === 'create') {
        $crearNombre = $entradaNombre;
        $crearCargo = $entradaCargo;
        $crearTelefono = $entradaTelefono;
        $crearCorreo = $entradaCorreo;
        $crearCategoria = $entradaCategoria;
        $crearPrioridad = $entradaPrioridad;
        $crearActivo = $entradaActivo;
    }

    if ($accion === 'delete') {
        $miembroId = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
        if ($miembroId <= 0) {
            $errores[] = 'No se pudo identificar al integrante que deseas eliminar.';
        } else {
            if ($repo->eliminar($miembroId)) {
                $mensajeExito = 'Integrante eliminado correctamente.';
            } else {
                $errores[] = 'No se pudo eliminar al integrante. Intenta nuevamente.';
            }
        }
    } else {
        if ($entradaNombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($entradaCorreo !== '' && !filter_var($entradaCorreo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Debes ingresar un correo electrónico válido.';
        }

        if (empty($errores)) {
            $datos = [
                'nombre' => $entradaNombre,
                'cargo' => $entradaCargo !== '' ? $entradaCargo : null,
                'telefono' => $entradaTelefono !== '' ? $entradaTelefono : null,
                'correo' => $entradaCorreo !== '' ? $entradaCorreo : null,
                'categoria' => $entradaCategoria,
                'descripcion' => null,
                'prioridad' => $entradaPrioridad,
                'activo' => $entradaActivo,
            ];

            if ($accion === 'update') {
                $miembroId = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
                if ($miembroId <= 0) {
                    $errores[] = 'No se pudo identificar al integrante que deseas actualizar.';
                } elseif ($repo->actualizar($miembroId, $datos)) {
                    $mensajeExito = 'Integrante actualizado correctamente.';
                } else {
                    $errores[] = 'No se pudo actualizar el integrante. Intenta nuevamente.';
                }
            } else {
                $nuevoId = $repo->crear($datos);
                if ($nuevoId > 0) {
                    $mensajeExito = 'Integrante registrado correctamente.';
                    $crearNombre = $crearCargo = $crearTelefono = $crearCorreo = '';
                    $crearCategoria = RepositorioEquipo::CATEGORIA_ASESOR_VENTAS;
                    $crearPrioridad = 0;
                    $crearActivo = 1;
                } else {
                    $errores[] = 'No se pudo registrar al integrante. Intenta nuevamente.';
                }
            }
        }
    }
}

$integrantes = $repo->obtenerTodos();

$miembroSeleccionadoSolicitud = isset($_GET['miembro']) ? (int) $_GET['miembro'] : 0;
$integranteSeleccionado = null;
$integranteSeleccionadoId = 0;

if (!empty($integrantes)) {
    $buscadorSeleccion = static function (array $lista, int $id): ?array {
        foreach ($lista as $integrante) {
            if ((int) ($integrante['id'] ?? 0) === $id) {
                return $integrante;
            }
        }

        return null;
    };

    if ($miembroSeleccionadoSolicitud > 0) {
        $integranteSeleccionado = $buscadorSeleccion($integrantes, $miembroSeleccionadoSolicitud);
    }

    if ($integranteSeleccionado === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $miembroPost = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
        if ($miembroPost > 0) {
            $integranteSeleccionado = $buscadorSeleccion($integrantes, $miembroPost);
        }
    }

    if ($integranteSeleccionado === null) {
        $integranteSeleccionado = $integrantes[0];
    }

    $integranteSeleccionadoId = (int) ($integranteSeleccionado['id'] ?? 0);
}

$editarNombre = '';
$editarCargo = '';
$editarTelefono = '';
$editarCorreo = '';
$editarCategoria = RepositorioEquipo::CATEGORIA_ASESOR_VENTAS;
$editarPrioridad = 0;
$editarActivo = 1;

$mostrarModalEdicion = false;

if ($integranteSeleccionado !== null) {
    $editarNombre = (string) ($integranteSeleccionado['nombre'] ?? '');
    $editarCargo = (string) ($integranteSeleccionado['cargo'] ?? '');
    $editarTelefono = (string) ($integranteSeleccionado['telefono'] ?? '');
    $editarCorreo = (string) ($integranteSeleccionado['correo'] ?? '');
    $editarCategoria = (string) ($integranteSeleccionado['categoria'] ?? RepositorioEquipo::CATEGORIA_OTRO);
    if (!array_key_exists($editarCategoria, $categorias)) {
        $editarCategoria = RepositorioEquipo::CATEGORIA_OTRO;
    }
    $editarPrioridad = (int) ($integranteSeleccionado['prioridad'] ?? 0);
    $editarActivo = ((int) ($integranteSeleccionado['activo'] ?? 0) === 1) ? 1 : 0;
    $mostrarModalEdicion = isset($_GET['miembro']) && (int) $_GET['miembro'] > 0;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $accion === 'update'
    && isset($_POST['member_id'])
    && (int) $_POST['member_id'] === $integranteSeleccionadoId
) {
    $editarNombre = $entradaNombre;
    $editarCargo = $entradaCargo;
    $editarTelefono = $entradaTelefono;
    $editarCorreo = $entradaCorreo;
    $editarCategoria = $entradaCategoria;
    $editarPrioridad = $entradaPrioridad;
    $editarActivo = $entradaActivo;
    $mostrarModalEdicion = true;
}

if (!empty($errores) && $accion === 'update') {
    $mostrarModalEdicion = true;
}

$paginaActiva = 'equipo';
$tituloPagina = 'Equipo — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Equipo de Expediatravels</h1>
        <p>Gestiona a los asesores comerciales, guías y personal operativo que atienden a los viajeros.</p>
    </header>

    <?php if (!empty($errores)) : ?>
        <div class="admin-alert error">
            <p><strong>No se pudo completar la operación:</strong></p>
            <ul>
                <?php foreach ($errores as $error) : ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($mensajeExito !== null && empty($errores)) : ?>
        <div class="admin-alert">
            <?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Registrar integrante</h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="create" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nuevo-nombre">Nombre completo</label>
                    <input type="text" id="nuevo-nombre" name="nombre" required value="<?= htmlspecialchars($crearNombre ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="admin-field">
                    <label for="nuevo-cargo">Cargo o rol</label>
                    <input type="text" id="nuevo-cargo" name="cargo" value="<?= htmlspecialchars($crearCargo ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Especialista en circuitos" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nuevo-telefono">Teléfono o WhatsApp</label>
                    <input type="text" id="nuevo-telefono" name="telefono" value="<?= htmlspecialchars($crearTelefono ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. +51 999 888 777" />
                </div>
                <div class="admin-field">
                    <label for="nuevo-correo">Correo electrónico</label>
                    <input type="email" id="nuevo-correo" name="correo" value="<?= htmlspecialchars($crearCorreo ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="nombre@expediatravels.pe" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nuevo-categoria">Categoría</label>
                    <select id="nuevo-categoria" name="categoria">
                        <?php foreach ($categorias as $clave => $etiqueta) : ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= ($crearCategoria ?? RepositorioEquipo::CATEGORIA_ASESOR_VENTAS) === $clave ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="nuevo-prioridad">Prioridad</label>
                    <input type="number" id="nuevo-prioridad" name="prioridad" value="<?= (int) ($crearPrioridad ?? 0); ?>" min="0" max="999" />
                    <p class="admin-help">Mayor prioridad aparece primero en la web.</p>
                </div>
            </div>
            <label class="admin-checkbox">
                <input type="checkbox" name="activo" value="1" <?= ($crearActivo ?? 1) === 1 ? 'checked' : ''; ?> />
                <span>Mostrar como integrante activo</span>
            </label>
            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar integrante</button>
            </div>
        </form>
    </section>

    <section id="integrantes-registrados" class="admin-card admin-card--team">
        <header class="admin-card__header">
            <h2>Integrantes registrados</h2>
            <p class="admin-card__subtitle">Consulta el listado de tu equipo y actualiza sus datos desde un panel dedicado.</p>
        </header>
        <?php if (empty($integrantes)) : ?>
            <p class="admin-empty admin-empty--team">Aún no has registrado integrantes.</p>
        <?php else : ?>
            <div class="team-management">
                <div class="team-management__list">
                    <div class="admin-table-wrapper">
                        <table class="admin-table admin-table--users team-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Cargo</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($integrantes as $integranteResumen) : ?>
                                    <?php
                                    $miembroId = (int) ($integranteResumen['id'] ?? 0);
                                    $categoriaResumen = (string) ($integranteResumen['categoria'] ?? '');
                                    if ($categoriaResumen === '' || !array_key_exists($categoriaResumen, $categorias)) {
                                        $categoriaResumen = RepositorioEquipo::CATEGORIA_OTRO;
                                    }
                                    $etiquetaCategoria = $categorias[$categoriaResumen];
                                    $estaSeleccionado = $miembroId === $integranteSeleccionadoId;
                                    $nombreResumen = (string) ($integranteResumen['nombre'] ?? '');
                                    $cargoResumen = (string) ($integranteResumen['cargo'] ?? '');
                                    $telefonoResumen = (string) ($integranteResumen['telefono'] ?? '');
                                    $correoResumen = (string) ($integranteResumen['correo'] ?? '');
                                    $prioridadResumen = (int) ($integranteResumen['prioridad'] ?? 0);
                                    $activoResumen = ((int) ($integranteResumen['activo'] ?? 0) === 1) ? 1 : 0;
                                    ?>
                                    <tr class="team-table__row<?= $estaSeleccionado ? ' team-table__row--selected' : ''; ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($nombreResumen, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($etiquetaCategoria, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($cargoResumen, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($telefonoResumen, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($correoResumen, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= $prioridadResumen; ?></td>
                                        <td><?= $activoResumen === 1 ? 'Activo' : 'Oculto'; ?></td>
                                        <td class="team-table__actions">
                                            <a
                                                class="admin-button admin-button--secondary team-table__edit"
                                                href="?miembro=<?= $miembroId; ?>#integrantes-registrados"
                                                data-team-editor-open
                                                data-team-editor-id="<?= $miembroId; ?>"
                                                data-team-editor-nombre="<?= htmlspecialchars($nombreResumen, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-team-editor-cargo="<?= htmlspecialchars($cargoResumen, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-team-editor-telefono="<?= htmlspecialchars($telefonoResumen, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-team-editor-correo="<?= htmlspecialchars($correoResumen, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-team-editor-categoria="<?= htmlspecialchars($categoriaResumen, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-team-editor-prioridad="<?= $prioridadResumen; ?>"
                                                data-team-editor-activo="<?= $activoResumen; ?>"
                                            >
                                                Editar
                                            </a>
                                            <form method="post" class="team-table__delete" onsubmit="return confirm('¿Seguro que deseas eliminar este integrante?');">
                                                <input type="hidden" name="action" value="delete" />
                                                <input type="hidden" name="member_id" value="<?= $miembroId; ?>" />
                                                <button type="submit" class="admin-button admin-button--danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($integranteSeleccionado !== null) : ?>
                    <div
                        class="team-editor-modal<?= $mostrarModalEdicion ? ' team-editor-modal--open' : ''; ?>"
                        data-initial-open="<?= $mostrarModalEdicion ? '1' : '0'; ?>"
                        aria-hidden="<?= $mostrarModalEdicion ? 'false' : 'true'; ?>"
                    >
                        <div class="team-editor-modal__overlay" data-team-editor-close></div>
                        <div class="team-editor-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="team-editor-modal-title">
                            <button type="button" class="team-editor-modal__close" data-team-editor-close aria-label="Cerrar edición del integrante">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <div class="team-editor-card">
                                <header class="team-editor-card__header">
                                    <h3 id="team-editor-modal-title">Editar integrante</h3>
                                    <p>Modifica la información del integrante seleccionado y guarda los cambios.</p>
                                </header>
                                <form method="post" class="admin-form team-editor-card__form">
                                    <input type="hidden" name="action" value="update" />
                                    <input type="hidden" name="member_id" value="<?= $integranteSeleccionadoId; ?>" />
                                    <div class="admin-grid two-columns">
                                        <div class="admin-field">
                                            <label for="editar-nombre">Nombre</label>
                                            <input type="text" id="editar-nombre" name="nombre" required value="<?= htmlspecialchars($editarNombre, ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                        <div class="admin-field">
                                            <label for="editar-cargo">Cargo</label>
                                            <input type="text" id="editar-cargo" name="cargo" value="<?= htmlspecialchars($editarCargo, ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>
                                    <div class="admin-grid two-columns">
                                        <div class="admin-field">
                                            <label for="editar-telefono">Teléfono</label>
                                            <input type="text" id="editar-telefono" name="telefono" value="<?= htmlspecialchars($editarTelefono, ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                        <div class="admin-field">
                                            <label for="editar-correo">Correo</label>
                                            <input type="email" id="editar-correo" name="correo" value="<?= htmlspecialchars($editarCorreo, ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>
                                    <div class="admin-grid two-columns">
                                        <div class="admin-field">
                                            <label for="editar-categoria">Categoría</label>
                                            <select id="editar-categoria" name="categoria">
                                                <?php foreach ($categorias as $clave => $etiqueta) : ?>
                                                    <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $clave === $editarCategoria ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="admin-field">
                                            <label for="editar-prioridad">Prioridad</label>
                                            <input type="number" id="editar-prioridad" name="prioridad" value="<?= (int) $editarPrioridad; ?>" min="0" max="999" />
                                            <p class="admin-help">Mayor prioridad aparece primero en la web.</p>
                                        </div>
                                    </div>
                                    <label class="admin-checkbox" for="editar-activo">
                                        <input type="checkbox" id="editar-activo" name="activo" value="1" <?= $editarActivo === 1 ? 'checked' : ''; ?> />
                                        <span>Mostrar en la web</span>
                                    </label>
                                    <div class="admin-actions">
                                        <button type="submit" class="admin-button">Actualizar integrante</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
<?php endif; ?>
    </section>

</div>

<?php if ($integranteSeleccionado !== null) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('.team-editor-modal');
            if (!modal) {
                return;
            }

            const form = modal.querySelector('form');
            const overlay = modal.querySelector('.team-editor-modal__overlay');
            const closeButtons = modal.querySelectorAll('[data-team-editor-close]');
            const openButtons = document.querySelectorAll('[data-team-editor-open]');
            const bodyClass = 'team-editor-modal-open';

            const setAriaHidden = (hidden) => {
                modal.setAttribute('aria-hidden', hidden ? 'true' : 'false');
            };

            const removeRowSelection = () => {
                document.querySelectorAll('.team-table__row--selected').forEach((row) => {
                    row.classList.remove('team-table__row--selected');
                });
            };

            const openModal = (data, trigger) => {
                if (data && form) {
                    if (form.elements.member_id) {
                        form.elements.member_id.value = data.id || '';
                    }
                    if (form.elements.nombre) {
                        form.elements.nombre.value = data.nombre || '';
                    }
                    if (form.elements.cargo) {
                        form.elements.cargo.value = data.cargo || '';
                    }
                    if (form.elements.telefono) {
                        form.elements.telefono.value = data.telefono || '';
                    }
                    if (form.elements.correo) {
                        form.elements.correo.value = data.correo || '';
                    }
                    if (form.elements.categoria) {
                        form.elements.categoria.value = data.categoria || '';
                    }
                    if (form.elements.prioridad) {
                        form.elements.prioridad.value = data.prioridad || 0;
                    }
                    if (form.elements.activo) {
                        form.elements.activo.checked = data.activo === '1' || data.activo === 1;
                    }
                }

                modal.classList.add('team-editor-modal--open');
                document.body.classList.add(bodyClass);
                setAriaHidden(false);

                if (form) {
                    const firstField = form.querySelector('#editar-nombre');
                    if (firstField) {
                        firstField.focus();
                    }
                }

                if (trigger) {
                    const row = trigger.closest('tr');
                    if (row) {
                        removeRowSelection();
                        row.classList.add('team-table__row--selected');
                    }
                }
            };

            const closeModal = () => {
                modal.classList.remove('team-editor-modal--open');
                document.body.classList.remove(bodyClass);
                setAriaHidden(true);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    const data = {
                        id: button.dataset.teamEditorId || '',
                        nombre: button.dataset.teamEditorNombre || '',
                        cargo: button.dataset.teamEditorCargo || '',
                        telefono: button.dataset.teamEditorTelefono || '',
                        correo: button.dataset.teamEditorCorreo || '',
                        categoria: button.dataset.teamEditorCategoria || '',
                        prioridad: button.dataset.teamEditorPrioridad || '0',
                        activo: button.dataset.teamEditorActivo || '0',
                    };
                    openModal(data, button);
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    closeModal();
                });
            });

            if (overlay) {
                overlay.addEventListener('click', () => {
                    closeModal();
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('team-editor-modal--open')) {
                    closeModal();
                }
            });

            if (modal.dataset.initialOpen === '1') {
                openModal();
            } else {
                setAriaHidden(true);
            }
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/plantilla/pie.php'; ?>
