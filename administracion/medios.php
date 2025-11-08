<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioMedios;
use Aplicacion\Servicios\GestorMedios;

$repositorio = new RepositorioMedios();
$gestor = new GestorMedios();

$errores = [];
$mensajes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['action']) ? (string) $_POST['action'] : '';

    if ($accion === 'upload') {
        if (!isset($_FILES['archivo'])) {
            $errores[] = 'Selecciona una imagen para agregarla a la biblioteca.';
        } else {
            $archivo = $_FILES['archivo'];
            if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $errores[] = 'Selecciona un archivo de imagen válido.';
            } else {
                $hash = null;
                $rutaTemporal = $archivo['tmp_name'] ?? '';
                if (is_string($rutaTemporal) && $rutaTemporal !== '' && is_file($rutaTemporal)) {
                    $hash = sha1_file($rutaTemporal) ?: null;
                }

                if ($hash === null) {
                    $errores[] = 'No pudimos calcular la huella digital del archivo subido.';
                } else {
                    $registroExistente = $repositorio->buscarPorHash($hash);
                    if ($registroExistente !== null) {
                        $mensajes[] = sprintf(
                            'La imagen ya se encuentra registrada como "%s". Puedes reutilizarla sin subir un duplicado.',
                            $registroExistente['titulo'] !== '' ? $registroExistente['titulo'] : $registroExistente['nombre_original']
                        );
                    } else {
                        try {
                            $meta = $gestor->guardarArchivo($archivo, $hash);
                            $titulo = trim((string) ($_POST['titulo'] ?? ''));
                            if ($titulo === '') {
                                $titulo = pathinfo($meta['nombre_original'], PATHINFO_FILENAME) ?: 'Imagen sin título';
                            }

                            $datos = [
                                'titulo' => $titulo,
                                'descripcion' => convertirTextoOpcional($_POST['descripcion'] ?? null),
                                'texto_alternativo' => convertirTextoOpcional($_POST['texto_alternativo'] ?? null),
                                'creditos' => convertirTextoOpcional($_POST['creditos'] ?? null),
                                'ruta' => $meta['ruta_relativa'],
                                'nombre_archivo' => $meta['nombre_archivo'],
                                'nombre_original' => $meta['nombre_original'],
                                'tipo_mime' => $meta['mime_type'],
                                'extension' => $meta['extension'],
                                'tamano_bytes' => $meta['tamano_bytes'],
                                'ancho' => $meta['ancho'],
                                'alto' => $meta['alto'],
                                'sha1_hash' => $meta['sha1_hash'],
                            ];

                            $id = $repositorio->crear($datos);
                            if ($id > 0) {
                                $mensajes[] = 'La imagen se agregó correctamente a la biblioteca de medios.';
                            } else {
                                $rutaGuardada = dirname(__DIR__) . '/' . ltrim($meta['ruta_relativa'], '/');
                                if (is_file($rutaGuardada)) {
                                    @unlink($rutaGuardada);
                                }
                                $errores[] = 'Se guardó el archivo en el servidor, pero no se pudo registrar en la base de datos.';
                            }
                        } catch (RuntimeException $exception) {
                            $errores[] = $exception->getMessage();
                        }
                    }
                }
            }
        }
    } elseif ($accion === 'update') {
        $identificador = isset($_POST['media_id']) ? (int) $_POST['media_id'] : 0;
        if ($identificador <= 0) {
            $errores[] = 'No se pudo identificar el elemento de medios a actualizar.';
        } else {
            $titulo = trim((string) ($_POST['titulo'] ?? ''));
            if ($titulo === '') {
                $errores[] = 'El título no puede estar vacío.';
            } else {
                $datos = [
                    'titulo' => $titulo,
                    'descripcion' => convertirTextoOpcional($_POST['descripcion'] ?? null),
                    'texto_alternativo' => convertirTextoOpcional($_POST['texto_alternativo'] ?? null),
                    'creditos' => convertirTextoOpcional($_POST['creditos'] ?? null),
                ];

                if ($repositorio->actualizar($identificador, $datos)) {
                    $mensajes[] = 'La información del medio se actualizó correctamente.';
                } else {
                    $errores[] = 'No se pudo guardar la actualización solicitada.';
                }
            }
        }
    } elseif ($accion === 'delete') {
        $identificador = isset($_POST['media_id']) ? (int) $_POST['media_id'] : 0;
        if ($identificador <= 0) {
            $errores[] = 'No se pudo identificar el medio seleccionado.';
        } else {
            $registro = $repositorio->obtenerPorId($identificador);
            if ($registro === null) {
                $errores[] = 'El medio indicado ya no existe.';
            } else {
                $rutaFisica = dirname(__DIR__) . '/' . ltrim((string) $registro['ruta'], '/');
                $resultado = $repositorio->eliminar($identificador);
                if ($resultado) {
                    if (is_file($rutaFisica)) {
                        @unlink($rutaFisica);
                    }
                    $mensajes[] = 'El medio se eliminó correctamente.';
                } else {
                    $errores[] = 'No pudimos eliminar el registro del medio.';
                }
            }
        }
    } else {
        $errores[] = 'Acción no reconocida.';
    }
}

$medios = $repositorio->listar();
$totalMedios = count($medios);

$paginaActiva = 'medios';
$tituloPagina = 'Medios — Panel de Control';
$estilosExtra = ['recursos/medios.css'];
$scriptsExtra = ['recursos/medios.js'];

require __DIR__ . '/plantilla/cabecera.php';

/**
 * Convierte un valor opcional en texto limpio o null.
 *
 * @param mixed $valor
 */
function convertirTextoOpcional(mixed $valor): ?string
{
    if (!is_string($valor)) {
        return null;
    }

    $texto = trim($valor);

    return $texto !== '' ? $texto : null;
}

function formatearTamano(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }

    $unidades = ['B', 'KB', 'MB', 'GB'];
    $factor = min((int) floor(log($bytes, 1024)), count($unidades) - 1);
    $valor = $bytes / (1024 ** $factor);

    return sprintf('%.1f %s', $valor, $unidades[$factor]);
}

function formatearDimensiones(?int $ancho, ?int $alto): string
{
    if ($ancho === null || $alto === null) {
        return 'Dimensiones desconocidas';
    }

    return sprintf('%d × %d px', $ancho, $alto);
}

function formatearFecha(?string $marcaTiempo): string
{
    if ($marcaTiempo === null || $marcaTiempo === '') {
        return '—';
    }

    try {
        $fecha = new DateTimeImmutable($marcaTiempo, new DateTimeZone('America/Lima'));
    } catch (Exception $exception) {
        try {
            $fecha = new DateTimeImmutable($marcaTiempo);
        } catch (Exception $exception) {
            return $marcaTiempo;
        }
    }

    return $fecha->format('d/m/Y H:i');
}

function obtenerMarcaTiempoUnix(?string $marcaTiempo): int
{
    if ($marcaTiempo === null || $marcaTiempo === '') {
        return time();
    }

    try {
        $fecha = new DateTimeImmutable($marcaTiempo, new DateTimeZone('America/Lima'));
    } catch (Exception $exception) {
        try {
            $fecha = new DateTimeImmutable($marcaTiempo);
        } catch (Exception $exception) {
            return time();
        }
    }

    return (int) $fecha->format('U');
}
?>
<div class="media-app">
    <div class="media-app__header">
        <div class="media-title">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13A2.5 2.5 0 0 1 17.5 21h-11A2.5 2.5 0 0 1 4 18.5v-13Z" stroke="#2563eb" stroke-width="1.2"/><path d="M4 15.5 8.5 11l4 4 3-3L20 16" stroke="#2563eb" stroke-width="1.2"/><circle cx="9.2" cy="7.8" r="1.3" fill="#2563eb"/></svg>
            <div>
                <h1>Biblioteca de medios</h1>
                <p>Administra las imágenes y videos disponibles para todo el contenido de Expediatravels.</p>
            </div>
        </div>
        <div class="media-toolbar">
            <div class="media-toolbar__filters">
                <input class="media-input" id="q" type="search" placeholder="Buscar por título, alt, créditos… ( / )" autocomplete="off" />
                <select id="type" class="media-input">
                    <option value="">Todos los tipos</option>
                    <option value="foto">Fotos</option>
                    <option value="video">Videos</option>
                    <option value="otro">Otros</option>
                </select>
                <select id="sort" class="media-input">
                    <option value="new">Recientes primero</option>
                    <option value="old">Antiguos primero</option>
                    <option value="az">A → Z (título)</option>
                </select>
            </div>
            <button class="media-btn ghost" id="bulkSelectBtn" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="#2563eb" d="M3 7h18v2H3zm0 4h18v2H3zm0 4h18v2H3z"/></svg>
                Selección múltiple
            </button>
        </div>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="media-alert error" role="alert">
            <p><strong>Encontramos algunos inconvenientes:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensajes) && empty($errores)): ?>
        <div class="media-alert" role="status">
            <ul>
                <?php foreach ($mensajes as $mensaje): ?>
                    <li><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="media-grid-layout">
        <section class="media-panel">
            <div class="media-panel__body">
                <h2>Nueva imagen / video</h2>
                <p class="media-panel__sub">Arrastra y suelta o selecciona desde tu equipo. Los metadatos se completan en una ventana emergente.</p>

                <form id="uploadForm" method="post" enctype="multipart/form-data" class="media-upload-form">
                    <input type="hidden" name="action" value="upload" />
                    <input type="hidden" name="titulo" id="uploadTitulo" />
                    <input type="hidden" name="descripcion" id="uploadDescripcion" />
                    <input type="hidden" name="texto_alternativo" id="uploadAlt" />
                    <input type="hidden" name="creditos" id="uploadCreditos" />

                    <div id="drop" class="media-drop" tabindex="0" role="button" aria-label="Abrir selector de archivos para subir">
                        <input id="file" name="archivo" type="file" accept="image/*,video/*" />
                        <div>
                            <div class="media-drop__title">Suelta archivos aquí</div>
                            <div class="media-drop__hint">PNG, JPG, WEBP, MP4. Máx. 25MB</div>
                            <div class="media-drop__actions"><button class="media-btn ghost" id="browseBtn" type="button">Seleccionar archivo</button></div>
                            <div id="filename" class="media-drop__filename"></div>
                        </div>
                    </div>

                    <div class="media-upload-actions">
                        <button id="addBtn" type="button" class="media-btn primary">Agregar a la biblioteca</button>
                        <button id="clearFile" type="button" class="media-btn ghost">Limpiar</button>
                    </div>
                </form>

                <div class="media-tips">
                    <div><strong>Consejos rápidos</strong></div>
                    <ul>
                        <li>Reutiliza medios existentes antes de subir uno nuevo.</li>
                        <li>Completa <strong>texto alternativo</strong> y <strong>créditos</strong> en el editor modal.</li>
                        <li>Usa descripciones detalladas para búsquedas rápidas.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="media-panel media-library" aria-live="polite">
            <div class="media-library__toolbar">
                <div class="media-library__summary">
                    <span class="media-meta">Mostrando <strong id="pageRange">0</strong> de <strong id="totalCount"><?= $totalMedios; ?></strong> elementos</span>
                </div>
                <div class="media-library__summary">
                    <button class="media-btn ghost" id="clearFilters" type="button">Limpiar filtros</button>
                </div>
            </div>
            <div id="lib" class="media-library__grid" role="list">
                <?php foreach ($medios as $medio): ?>
                    <?php
                        $idMedio = (int) $medio['id'];
                        $titulo = (string) $medio['titulo'];
                        $textoAlternativo = (string) ($medio['texto_alternativo'] ?? '');
                        $descripcion = (string) ($medio['descripcion'] ?? '');
                        $creditos = (string) ($medio['creditos'] ?? '');
                        $tipoMime = (string) ($medio['tipo_mime'] ?? '');
                        $rutaRelativa = (string) $medio['ruta'];
                        $rutaPublica = '/' . ltrim($rutaRelativa, '/');
                        $dimensiones = formatearDimensiones($medio['ancho'], $medio['alto']);
                        $tamanoHumano = formatearTamano((int) $medio['tamano_bytes']);
                        $fechaCreacion = formatearFecha($medio['creado_en'] ?? null);
                        $marcaTiempo = obtenerMarcaTiempoUnix($medio['creado_en'] ?? null);
                        $tipoEtiqueta = str_starts_with($tipoMime, 'video/') ? 'video' : (str_starts_with($tipoMime, 'image/') ? 'foto' : 'otro');
                        $busquedaComponentes = [$titulo, $textoAlternativo, $descripcion, $creditos, $rutaRelativa, $tipoMime, $fechaCreacion];
                        $cadenaBusqueda = strtolower(trim(implode(' ', array_filter($busquedaComponentes, static fn ($valor) => is_string($valor) && $valor !== ''))));
                    ?>
                    <article class="media-card" role="listitem"
                        data-id="<?= $idMedio; ?>"
                        data-title="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?>"
                        data-alt="<?= htmlspecialchars($textoAlternativo, ENT_QUOTES, 'UTF-8'); ?>"
                        data-desc="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'); ?>"
                        data-credits="<?= htmlspecialchars($creditos, ENT_QUOTES, 'UTF-8'); ?>"
                        data-src="<?= htmlspecialchars($rutaPublica, ENT_QUOTES, 'UTF-8'); ?>"
                        data-kind="<?= htmlspecialchars($tipoEtiqueta, ENT_QUOTES, 'UTF-8'); ?>"
                        data-created="<?= (int) $marcaTiempo; ?>"
                        data-created-label="<?= htmlspecialchars($fechaCreacion, ENT_QUOTES, 'UTF-8'); ?>"
                        data-dimensions="<?= htmlspecialchars($dimensiones, ENT_QUOTES, 'UTF-8'); ?>"
                        data-size="<?= htmlspecialchars($tamanoHumano, ENT_QUOTES, 'UTF-8'); ?>"
                        data-mime="<?= htmlspecialchars($tipoMime, ENT_QUOTES, 'UTF-8'); ?>"
                        data-search="<?= htmlspecialchars($cadenaBusqueda, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="media-card__thumb">
                            <img src="<?= htmlspecialchars($rutaPublica, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($textoAlternativo !== '' ? $textoAlternativo : $titulo, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" />
                            <span class="media-badge"><?= strtoupper(htmlspecialchars($tipoEtiqueta, ENT_QUOTES, 'UTF-8')); ?></span>
                        </div>
                        <div class="media-card__body">
                            <div class="media-card__meta">
                                <span class="media-card__date"><?= htmlspecialchars($fechaCreacion, ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong class="media-card__title"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <ul class="media-card__details">
                                <li><?= htmlspecialchars($dimensiones, ENT_QUOTES, 'UTF-8'); ?></li>
                                <li><?= htmlspecialchars($tamanoHumano, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($tipoMime, ENT_QUOTES, 'UTF-8'); ?></li>
                                <li><code><?= htmlspecialchars($rutaRelativa, ENT_QUOTES, 'UTF-8'); ?></code></li>
                            </ul>
                            <div class="media-card__actions">
                                <button class="media-btn ghost" type="button" data-action="edit">Editar</button>
                                <button class="media-btn primary" type="button" data-action="preview">Vista previa</button>
                                <button class="media-btn danger" type="button" data-action="remove">Eliminar</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div id="empty" class="media-empty"<?= empty($medios) ? '' : ' style="display:none"'; ?>>La biblioteca está vacía. ¡Sube tu primer medio!</div>
            <nav id="pagination" class="media-pagination" aria-label="Paginación"></nav>
        </section>
    </div>

    <form id="mediaUpdateForm" method="post" class="sr">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="media_id" id="updateMediaId" />
        <input type="hidden" name="titulo" id="updateTitulo" />
        <input type="hidden" name="texto_alternativo" id="updateAlt" />
        <input type="hidden" name="descripcion" id="updateDescripcion" />
        <input type="hidden" name="creditos" id="updateCreditos" />
    </form>

    <form id="mediaDeleteForm" method="post" class="sr">
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="media_id" id="deleteMediaId" />
    </form>
</div>

<dialog id="modal" class="media-modal">
    <div class="media-modal__header">
        <strong id="modalTitle">Editar metadatos</strong>
        <button class="media-btn ghost" id="closeModal" type="button">Cerrar</button>
    </div>
    <div class="media-modal__body">
        <div class="media-modal__preview" id="modalPreview">Vista previa</div>
        <div class="media-modal__meta" id="modalMeta"></div>
        <form id="modalForm" class="media-modal__form" method="dialog">
            <div class="media-field full">
                <label for="m_title">Título</label>
                <input id="m_title" class="media-input" placeholder="Nombre amigable" autocomplete="off" />
            </div>
            <div class="media-field full">
                <label for="m_alt">Texto alternativo</label>
                <input id="m_alt" class="media-input" placeholder="Describe el contenido visual (accesibilidad)" autocomplete="off" />
            </div>
            <div class="media-field full">
                <label for="m_desc">Descripción</label>
                <textarea id="m_desc" class="media-input" placeholder="Notas o contexto"></textarea>
            </div>
            <div class="media-field full">
                <label for="m_credits">Créditos</label>
                <input id="m_credits" class="media-input" placeholder="Autor o fuente" autocomplete="off" />
            </div>
        </form>
        <div class="media-modal__actions">
            <button class="media-btn primary" id="saveCard" type="button">Guardar cambios</button>
            <button class="media-btn danger" id="deleteCard" type="button">Eliminar</button>
        </div>
    </div>
</dialog>

<?php require __DIR__ . '/plantilla/pie.php'; ?>
