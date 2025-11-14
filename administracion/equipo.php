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

$nombre = '';
$cargo = '';
$telefono = '';
$correo = '';
$categoria = RepositorioEquipo::CATEGORIA_ASESOR_VENTAS;
$prioridad = 0;
$activo = 1;

$accion = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $cargo = trim((string) ($_POST['cargo'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $categoria = (string) ($_POST['categoria'] ?? RepositorioEquipo::CATEGORIA_OTRO);
    $prioridad = isset($_POST['prioridad']) ? (int) $_POST['prioridad'] : 0;
    if ($prioridad < 0) {
        $prioridad = 0;
    }
    if ($prioridad > 999) {
        $prioridad = 999;
    }
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!array_key_exists($categoria, $categorias)) {
        $categoria = RepositorioEquipo::CATEGORIA_OTRO;
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
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Debes ingresar un correo electrónico válido.';
        }

        if (empty($errores)) {
            $datos = [
                'nombre' => $nombre,
                'cargo' => $cargo !== '' ? $cargo : null,
                'telefono' => $telefono !== '' ? $telefono : null,
                'correo' => $correo !== '' ? $correo : null,
                'categoria' => $categoria,
                'descripcion' => null,
                'prioridad' => $prioridad,
                'activo' => $activo,
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
                    $nombre = $cargo = $telefono = $correo = '';
                    $categoria = RepositorioEquipo::CATEGORIA_ASESOR_VENTAS;
                    $prioridad = 0;
                    $activo = 1;
                } else {
                    $errores[] = 'No se pudo registrar al integrante. Intenta nuevamente.';
                }
            }
        }
    }
}

$integrantes = $repo->obtenerTodos();
$integrantesPorCategoria = [];
foreach ($integrantes as $integrante) {
    $clave = (string) ($integrante['categoria'] ?? '');
    if ($clave === '' || !array_key_exists($clave, $categorias)) {
        $clave = RepositorioEquipo::CATEGORIA_OTRO;
    }
    if (!array_key_exists($clave, $integrantesPorCategoria)) {
        $integrantesPorCategoria[$clave] = [];
    }
    $integrantesPorCategoria[$clave][] = $integrante;
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
                    <input type="text" id="nuevo-nombre" name="nombre" required value="<?= htmlspecialchars($nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="admin-field">
                    <label for="nuevo-cargo">Cargo o rol</label>
                    <input type="text" id="nuevo-cargo" name="cargo" value="<?= htmlspecialchars($cargo ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Especialista en circuitos" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nuevo-telefono">Teléfono o WhatsApp</label>
                    <input type="text" id="nuevo-telefono" name="telefono" value="<?= htmlspecialchars($telefono ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. +51 999 888 777" />
                </div>
                <div class="admin-field">
                    <label for="nuevo-correo">Correo electrónico</label>
                    <input type="email" id="nuevo-correo" name="correo" value="<?= htmlspecialchars($correo ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="nombre@expediatravels.pe" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nuevo-categoria">Categoría</label>
                    <select id="nuevo-categoria" name="categoria">
                        <?php foreach ($categorias as $clave => $etiqueta) : ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= ($categoria ?? RepositorioEquipo::CATEGORIA_ASESOR_VENTAS) === $clave ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="nuevo-prioridad">Prioridad</label>
                    <input type="number" id="nuevo-prioridad" name="prioridad" value="<?= (int) ($prioridad ?? 0); ?>" min="0" max="999" />
                    <p class="admin-help">Mayor prioridad aparece primero en la web.</p>
                </div>
            </div>
            <label class="admin-checkbox">
                <input type="checkbox" name="activo" value="1" <?= ($activo ?? 1) === 1 ? 'checked' : ''; ?> />
                <span>Mostrar como integrante activo</span>
            </label>
            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar integrante</button>
            </div>
        </form>
    </section>

    <section class="admin-card admin-card--flush">
        <h2>Integrantes registrados</h2>
        <?php if (empty($integrantes)) : ?>
            <p class="admin-empty">Aún no has registrado integrantes.</p>
        <?php else : ?>
            <div class="admin-table-wrapper">
                <table class="admin-table admin-table--users">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Cargo</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($integrantes as $integranteResumen) : ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars((string) ($integranteResumen['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $categoriaResumen = (string) ($integranteResumen['categoria'] ?? '');
                                    if ($categoriaResumen === '' || !array_key_exists($categoriaResumen, $categorias)) {
                                        $categoriaResumen = RepositorioEquipo::CATEGORIA_OTRO;
                                    }
                                    $etiquetaCategoria = $categorias[$categoriaResumen];
                                    ?>
                                    <?= htmlspecialchars($etiquetaCategoria, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($integranteResumen['cargo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars((string) ($integranteResumen['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars((string) ($integranteResumen['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= (int) ($integranteResumen['prioridad'] ?? 0); ?></td>
                                <td><?= ((int) ($integranteResumen['activo'] ?? 0) === 1) ? 'Activo' : 'Oculto'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php foreach ($categorias as $claveCategoria => $etiquetaCategoria) : ?>
        <?php $lista = $integrantesPorCategoria[$claveCategoria] ?? []; ?>
        <section class="admin-card admin-card--flush">
            <h2><?= htmlspecialchars($etiquetaCategoria, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php if (empty($lista)) : ?>
                <p class="admin-empty">No hay integrantes registrados en esta categoría.</p>
            <?php else : ?>
                <div class="admin-table-wrapper">
                    <table class="admin-table admin-table--users">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Gestión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista as $integrante) : ?>
                                <?php $miembroId = (int) ($integrante['id'] ?? 0); ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars((string) ($integrante['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <?php if (!empty($integrante['cargo'])) : ?>
                                            <p class="admin-table__meta"><?= htmlspecialchars((string) $integrante['cargo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($integrante['telefono'])) : ?>
                                            <p><?= htmlspecialchars((string) $integrante['telefono'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($integrante['correo'])) : ?>
                                            <p class="admin-table__meta"><?= htmlspecialchars((string) $integrante['correo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) ($integrante['prioridad'] ?? 0); ?></td>
                                    <td><?= ((int) ($integrante['activo'] ?? 0) === 1) ? 'Activo' : 'Oculto'; ?></td>
                                    <td>
                                        <form method="post" class="admin-table__form">
                                            <input type="hidden" name="action" value="update" />
                                            <input type="hidden" name="member_id" value="<?= $miembroId; ?>" />
                                            <div class="admin-grid two-columns">
                                                <div class="admin-field">
                                                    <label for="nombre-<?= $miembroId; ?>">Nombre</label>
                                                    <input type="text" id="nombre-<?= $miembroId; ?>" name="nombre" required value="<?= htmlspecialchars((string) ($integrante['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                                </div>
                                                <div class="admin-field">
                                                    <label for="cargo-<?= $miembroId; ?>">Cargo</label>
                                                    <input type="text" id="cargo-<?= $miembroId; ?>" name="cargo" value="<?= htmlspecialchars((string) ($integrante['cargo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                                </div>
                                            </div>
                                            <div class="admin-grid two-columns">
                                                <div class="admin-field">
                                                    <label for="telefono-<?= $miembroId; ?>">Teléfono</label>
                                                    <input type="text" id="telefono-<?= $miembroId; ?>" name="telefono" value="<?= htmlspecialchars((string) ($integrante['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                                </div>
                                                <div class="admin-field">
                                                    <label for="correo-<?= $miembroId; ?>">Correo</label>
                                                    <input type="email" id="correo-<?= $miembroId; ?>" name="correo" value="<?= htmlspecialchars((string) ($integrante['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                                </div>
                                            </div>
                                            <div class="admin-grid two-columns">
                                                <div class="admin-field">
                                                    <label for="categoria-<?= $miembroId; ?>">Categoría</label>
                                                    <?php
                                                    $categoriaActual = (string) ($integrante['categoria'] ?? '');
                                                    if ($categoriaActual === '' || !array_key_exists($categoriaActual, $categorias)) {
                                                        $categoriaActual = RepositorioEquipo::CATEGORIA_OTRO;
                                                    }
                                                    ?>
                                                    <select id="categoria-<?= $miembroId; ?>" name="categoria">
                                                        <?php foreach ($categorias as $clave => $etiqueta) : ?>
                                                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $clave === $categoriaActual ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="admin-field">
                                                    <label for="prioridad-<?= $miembroId; ?>">Prioridad</label>
                                                    <input type="number" id="prioridad-<?= $miembroId; ?>" name="prioridad" value="<?= (int) ($integrante['prioridad'] ?? 0); ?>" min="0" max="999" />
                                                </div>
                                            </div>
                                            <label class="admin-checkbox">
                                                <input type="checkbox" name="activo" value="1" <?= ((int) ($integrante['activo'] ?? 0) === 1) ? 'checked' : ''; ?> />
                                                <span>Mostrar en la web</span>
                                            </label>
                                            <div class="admin-table__actions">
                                                <button type="submit" class="admin-button">Actualizar</button>
                                            </div>
                                        </form>
                                        <form method="post" class="admin-table__form" onsubmit="return confirm('¿Seguro que deseas eliminar este integrante?');">
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
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/plantilla/pie.php'; ?>
