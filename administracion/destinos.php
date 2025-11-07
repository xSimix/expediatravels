<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

/** @var array<int, array<string, mixed>> $destinos */
$destinos = [];
$errores = [];
$mensajeExito = null;
$mensajeInfo = null;

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$destinosPredeterminados = obtenerDestinosPredeterminados();

try {
    $destinos = array_map('normalizarDestino', ServicioAlmacenamientoJson::leer($archivoDestinos, $destinosPredeterminados));
} catch (\RuntimeException $exception) {
    $errores[] = 'No se pudo cargar el catálogo desde almacenamiento. Se muestran los destinos de referencia.';
    $destinos = array_map('normalizarDestino', $destinosPredeterminados);
}

$destinos = ordenarDestinos($destinos);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'create') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $region = trim((string) ($_POST['region'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $tagline = trim((string) ($_POST['tagline'] ?? ''));
        $latitudEntrada = $_POST['latitud'] ?? '';
        $longitudEntrada = $_POST['longitud'] ?? '';
        $imagen = trim((string) ($_POST['imagen'] ?? ''));
        $estado = normalizarEstado($_POST['estado'] ?? 'activo');
        $etiquetas = convertirEtiquetas($_POST['etiquetas'] ?? '');

        if ($nombre === '') {
            $errores[] = 'Debes indicar el nombre del destino.';
        }

        if ($region === '') {
            $errores[] = 'La región o provincia es obligatoria.';
        }

        $latitud = normalizarCoordenada($latitudEntrada, 'latitud', $errores);
        $longitud = normalizarCoordenada($longitudEntrada, 'longitud', $errores);

        if (empty($errores)) {
            $nuevoDestino = [
                'id' => obtenerSiguienteId($destinos),
                'nombre' => $nombre,
                'region' => $region,
                'descripcion' => $descripcion,
                'tagline' => $tagline,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'imagen' => $imagen,
                'tags' => $etiquetas,
                'estado' => $estado,
                'actualizado_en' => date('c'),
            ];

            $destinos[] = normalizarDestino($nuevoDestino);
            $destinos = ordenarDestinos($destinos);

            try {
                ServicioAlmacenamientoJson::guardar($archivoDestinos, $destinos);
                $mensajeExito = 'Destino agregado correctamente.';
            } catch (\RuntimeException $exception) {
                $errores[] = 'El destino se agregó, pero no se pudo guardar en almacenamiento.';
            }
        }
    } elseif ($accion === 'update') {
        $identificador = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $region = trim((string) ($_POST['region'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $tagline = trim((string) ($_POST['tagline'] ?? ''));
        $latitudEntrada = $_POST['latitud'] ?? '';
        $longitudEntrada = $_POST['longitud'] ?? '';
        $imagen = trim((string) ($_POST['imagen'] ?? ''));
        $estado = normalizarEstado($_POST['estado'] ?? 'activo');
        $etiquetas = convertirEtiquetas($_POST['etiquetas'] ?? '');

        if ($identificador <= 0) {
            $errores[] = 'No se pudo identificar el destino a actualizar.';
        }

        if ($nombre === '') {
            $errores[] = 'El nombre del destino no puede estar vacío.';
        }

        if ($region === '') {
            $errores[] = 'Debes indicar la región del destino.';
        }

        $latitud = normalizarCoordenada($latitudEntrada, 'latitud', $errores);
        $longitud = normalizarCoordenada($longitudEntrada, 'longitud', $errores);

        if (empty($errores)) {
            $actualizado = false;
            foreach ($destinos as &$destino) {
                if ($destino['id'] === $identificador) {
                    $destino['nombre'] = $nombre;
                    $destino['region'] = $region;
                    $destino['descripcion'] = $descripcion;
                    $destino['tagline'] = $tagline;
                    $destino['latitud'] = $latitud;
                    $destino['longitud'] = $longitud;
                    $destino['imagen'] = $imagen;
                    $destino['tags'] = $etiquetas;
                    $destino['estado'] = $estado;
                    $destino['actualizado_en'] = date('c');
                    $actualizado = true;
                    break;
                }
            }
            unset($destino);

            if ($actualizado) {
                $destinos = ordenarDestinos($destinos);
                try {
                    ServicioAlmacenamientoJson::guardar($archivoDestinos, $destinos);
                    $mensajeExito = 'Destino actualizado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'Los cambios se aplicaron, pero no se pudieron guardar.';
                }
            } else {
                $errores[] = 'No se encontró el destino solicitado.';
            }
        }
    } elseif ($accion === 'delete') {
        $identificador = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
        if ($identificador <= 0) {
            $errores[] = 'No se pudo identificar el destino a eliminar.';
        } else {
            $cantidadInicial = count($destinos);
            $destinos = array_values(array_filter(
                $destinos,
                static fn (array $destino): bool => $destino['id'] !== $identificador
            ));

            if (count($destinos) === $cantidadInicial) {
                $errores[] = 'El destino indicado ya no existe.';
            } else {
                try {
                    ServicioAlmacenamientoJson::guardar($archivoDestinos, $destinos);
                    $mensajeExito = 'Destino eliminado correctamente.';
                } catch (\RuntimeException $exception) {
                    $errores[] = 'El destino se eliminó, pero no se pudo actualizar el almacenamiento.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

if ($mensajeExito === null && empty($errores) && !is_file($archivoDestinos)) {
    $mensajeInfo = 'Actualmente se utilizan los destinos de ejemplo. Agrega el primero para crear tu propio catálogo.';
}

$paginaActiva = 'destinos';
$tituloPagina = 'Destinos — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Gestión de destinos</h1>
        <p>Configura los destinos base que agrupan circuitos, paquetes y experiencias en la Selva Central.</p>
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
        <h2>Agregar nuevo destino</h2>
        <form method="post" class="admin-grid">
            <input type="hidden" name="action" value="create" />
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del destino *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Oxapampa" />
                </div>
                <div class="admin-field">
                    <label for="region">Región o provincia *</label>
                    <input type="text" id="region" name="region" required placeholder="Pasco" />
                </div>
            </div>
            <div class="admin-field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe la esencia del destino y su propuesta de valor."></textarea>
                <p class="admin-help">Se muestra en la web pública y se utiliza en fichas de paquetes.</p>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="tagline">Frase destacada</label>
                    <input type="text" id="tagline" name="tagline" placeholder="Capital cafetalera de la Selva Central" />
                </div>
                <div class="admin-field">
                    <label for="imagen">Imagen de portada</label>
                    <input type="text" id="imagen" name="imagen" placeholder="/web/imagenes/destinos/oxapampa.jpg" />
                    <p class="admin-help">Ruta relativa o URL completa. Úsala para ilustrar mapas o fichas.</p>
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="latitud">Latitud</label>
                    <input type="text" id="latitud" name="latitud" placeholder="-10.5756" />
                </div>
                <div class="admin-field">
                    <label for="longitud">Longitud</label>
                    <input type="text" id="longitud" name="longitud" placeholder="-75.4018" />
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="etiquetas">Etiquetas</label>
                    <input type="text" id="etiquetas" name="etiquetas" placeholder="café, naturaleza, cultura" />
                    <p class="admin-help">Separa cada etiqueta con coma. Se usan para filtros y colecciones.</p>
                </div>
                <div class="admin-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="activo">Activo · visible en la web</option>
                        <option value="oculto">Oculto · solo para borradores</option>
                        <option value="borrador">Borrador · aún sin publicar</option>
                    </select>
                </div>
            </div>
            <div class="admin-actions">
                <button type="submit" class="admin-button">Agregar destino</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Destinos registrados</h2>
        <?php if (empty($destinos)): ?>
            <p class="admin-help">Aún no hay destinos guardados. Crea el primero para comenzar a construir tus circuitos.</p>
        <?php else: ?>
            <div class="admin-grid" style="gap: 1.25rem;">
                <?php foreach ($destinos as $destino): ?>
                    <article class="admin-destination">
                        <header class="admin-destination__header">
                            <div>
                                <h3><?= htmlspecialchars($destino['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="admin-destination__meta">Región: <?= htmlspecialchars($destino['region'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="admin-destination__badge admin-destination__badge--<?= htmlspecialchars($destino['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= estadoDestinoEtiqueta($destino['estado']); ?>
                            </span>
                        </header>
                        <?php if ($destino['tagline'] !== ''): ?>
                            <p class="admin-destination__tagline">“<?= htmlspecialchars($destino['tagline'], ENT_QUOTES, 'UTF-8'); ?>”</p>
                        <?php endif; ?>
                        <?php if ($destino['descripcion'] !== ''): ?>
                            <p class="admin-destination__description"><?= nl2br(htmlspecialchars($destino['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php endif; ?>
                        <dl class="admin-destination__details">
                            <?php if ($destino['latitud'] !== null && $destino['longitud'] !== null): ?>
                                <div>
                                    <dt>Coordenadas</dt>
                                    <dd><?= htmlspecialchars((string) $destino['latitud']); ?>, <?= htmlspecialchars((string) $destino['longitud']); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($destino['imagen'] !== ''): ?>
                                <div>
                                    <dt>Imagen</dt>
                                    <dd><?= htmlspecialchars($destino['imagen'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt>Actualizado</dt>
                                <dd><?= htmlspecialchars(formatearMarcaTiempo($destino['actualizado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></dd>
                            </div>
                        </dl>
                        <?php if (!empty($destino['tags'])): ?>
                            <ul class="admin-destination__tags">
                                <?php foreach ($destino['tags'] as $tag): ?>
                                    <li><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <details class="admin-destination__editor">
                            <summary>Editar destino</summary>
                            <form method="post" class="admin-grid">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="destino_id" value="<?= (int) $destino['id']; ?>" />
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Nombre</label>
                                        <input type="text" name="nombre" required value="<?= htmlspecialchars($destino['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Región</label>
                                        <input type="text" name="region" required value="<?= htmlspecialchars($destino['region'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                </div>
                                <div class="admin-field">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="4"><?= htmlspecialchars($destino['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Frase destacada</label>
                                        <input type="text" name="tagline" value="<?= htmlspecialchars($destino['tagline'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Imagen de portada</label>
                                        <input type="text" name="imagen" value="<?= htmlspecialchars($destino['imagen'], ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Latitud</label>
                                        <input type="text" name="latitud" value="<?= $destino['latitud'] !== null ? htmlspecialchars((string) $destino['latitud'], ENT_QUOTES, 'UTF-8') : ''; ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Longitud</label>
                                        <input type="text" name="longitud" value="<?= $destino['longitud'] !== null ? htmlspecialchars((string) $destino['longitud'], ENT_QUOTES, 'UTF-8') : ''; ?>" />
                                    </div>
                                </div>
                                <div class="admin-grid two-columns">
                                    <div class="admin-field">
                                        <label>Etiquetas</label>
                                        <input type="text" name="etiquetas" value="<?= htmlspecialchars(implode(', ', $destino['tags']), ENT_QUOTES, 'UTF-8'); ?>" />
                                    </div>
                                    <div class="admin-field">
                                        <label>Estado</label>
                                        <select name="estado">
                                            <option value="activo" <?= $destino['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="oculto" <?= $destino['estado'] === 'oculto' ? 'selected' : ''; ?>>Oculto</option>
                                            <option value="borrador" <?= $destino['estado'] === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="admin-actions">
                                    <button type="submit" class="admin-button">Guardar cambios</button>
                                </div>
                            </form>
                        </details>

                        <form method="post" class="admin-destination__delete" onsubmit="return confirm('¿Eliminar el destino <?= htmlspecialchars($destino['nombre'], ENT_QUOTES, 'UTF-8'); ?>?');">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="destino_id" value="<?= (int) $destino['id']; ?>" />
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
 * @return array<int, array<string, mixed>>
 */
function obtenerDestinosPredeterminados(): array
{
    $ruta = __DIR__ . '/../app/configuracion/destinos_predeterminados.php';

    /** @var array<int, array<string, mixed>> $predeterminados */
    $predeterminados = require $ruta;

    return $predeterminados;
}

/**
 * @param array<string, mixed> $destino
 * @return array<string, mixed>
 */
function normalizarDestino(array $destino): array
{
    $latitud = $destino['latitud'] ?? null;
    $longitud = $destino['longitud'] ?? null;

    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
        'descripcion' => trim((string) ($destino['descripcion'] ?? '')),
        'tagline' => trim((string) ($destino['tagline'] ?? '')),
        'latitud' => $latitud !== null && $latitud !== '' ? (float) $latitud : null,
        'longitud' => $longitud !== null && $longitud !== '' ? (float) $longitud : null,
        'imagen' => trim((string) ($destino['imagen'] ?? '')),
        'tags' => array_values(array_filter(array_map('trim', is_array($destino['tags'] ?? null) ? $destino['tags'] : []), static fn (string $tag): bool => $tag !== '')),
        'estado' => normalizarEstado($destino['estado'] ?? 'activo'),
        'actualizado_en' => $destino['actualizado_en'] ?? null,
    ];
}

/**
 * @param array<int, array<string, mixed>> $destinos
 * @return array<int, array<string, mixed>>
 */
function ordenarDestinos(array $destinos): array
{
    usort($destinos, static function (array $a, array $b): int {
        return strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8'));
    });

    return array_values($destinos);
}

/**
 * @param array<int, array<string, mixed>> $destinos
 */
function obtenerSiguienteId(array $destinos): int
{
    $maximo = 0;
    foreach ($destinos as $destino) {
        $maximo = max($maximo, (int) ($destino['id'] ?? 0));
    }

    return $maximo + 1;
}

/**
 * @param string $estado
 */
function normalizarEstado(string $estado): string
{
    $estado = strtolower(trim($estado));
    $permitidos = ['activo', 'oculto', 'borrador'];

    return in_array($estado, $permitidos, true) ? $estado : 'activo';
}

/**
 * @param string $valor
 * @return array<int, string>
 */
function convertirEtiquetas(string $valor): array
{
    if (trim($valor) === '') {
        return [];
    }

    $partes = preg_split('/[,;\n]+/', $valor);
    if (!is_array($partes)) {
        return [];
    }

    $resultado = [];
    foreach ($partes as $parte) {
        $texto = trim((string) $parte);
        if ($texto !== '') {
            $resultado[] = $texto;
        }
    }

    return array_values(array_unique($resultado));
}

/**
 * @param string|float|null $valor
 * @param string            $campo
 * @param array<int, string> $errores
 */
function normalizarCoordenada($valor, string $campo, array &$errores): ?float
{
    if ($valor === null || trim((string) $valor) === '') {
        return null;
    }

    $valor = str_replace(',', '.', trim((string) $valor));
    if (!is_numeric($valor)) {
        $errores[] = sprintf('El campo %s debe contener un número válido.', $campo);
        return null;
    }

    return (float) $valor;
}

function estadoDestinoEtiqueta(string $estado): string
{
    return match ($estado) {
        'oculto' => 'Oculto',
        'borrador' => 'Borrador',
        default => 'Activo',
    };
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
