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

$paginaActiva = 'medios';
$tituloPagina = 'Medios — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css', 'recursos/medios.css'];

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
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Biblioteca de medios</h1>
            <p>Centraliza las imágenes subidas para reutilizarlas en distintos contenidos sin duplicados innecesarios.</p>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>Encontramos algunos inconvenientes:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensajes) && empty($errores)): ?>
        <div class="admin-alert">
            <ul>
                <?php foreach ($mensajes as $mensaje): ?>
                    <li><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="media-library">
        <div class="media-toolbar">
            <form class="media-toolbar__form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload" />
                <div>
                    <label for="archivo">Selecciona una imagen</label>
                    <input type="file" name="archivo" id="archivo" accept="image/*" required />
                </div>
                <div>
                    <label for="titulo">Título</label>
                    <input type="text" name="titulo" id="titulo" placeholder="Nombre amigable para identificar la imagen" />
                </div>
                <div>
                    <label for="texto_alternativo">Texto alternativo</label>
                    <input type="text" name="texto_alternativo" id="texto_alternativo" placeholder="Describe el contenido visual" />
                </div>
                <div>
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Notas o contexto adicional"></textarea>
                </div>
                <div>
                    <label for="creditos">Créditos</label>
                    <input type="text" name="creditos" id="creditos" placeholder="Autor o fuente" />
                </div>
                <button type="submit">Agregar a la biblioteca</button>
            </form>
            <div class="media-toolbar__info">
                <strong>Consejos para mantener tu biblioteca ordenada:</strong>
                <ul>
                    <li>Reutiliza imágenes existentes antes de subir una nueva.</li>
                    <li>Completa el texto alternativo para mejorar la accesibilidad.</li>
                    <li>Guarda créditos y descripciones para facilitar su búsqueda futura.</li>
                </ul>
            </div>
        </div>

        <?php if (empty($medios)): ?>
            <div class="media-empty">
                Aún no has agregado imágenes. Sube la primera para comenzar a construir tu biblioteca.
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($medios as $medio): ?>
                    <article class="media-card">
                        <figure>
                            <img src="../<?= htmlspecialchars($medio['ruta'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($medio['texto_alternativo'] ?? $medio['titulo'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" />
                        </figure>
                        <div class="media-card__body">
                            <div>
                                <strong><?= htmlspecialchars($medio['titulo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="media-card__meta">
                                <span><?= htmlspecialchars(formatearDimensiones($medio['ancho'], $medio['alto']), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span><?= htmlspecialchars(formatearTamano((int) $medio['tamano_bytes']), ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($medio['tipo_mime'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>Archivo: <code><?= htmlspecialchars($medio['ruta'], ENT_QUOTES, 'UTF-8'); ?></code></span>
                                <span>Agregada el <?= htmlspecialchars(formatearFecha($medio['creado_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <form method="post" class="media-card__form" id="media-form-<?= (int) $medio['id']; ?>">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="media_id" value="<?= (int) $medio['id']; ?>" />
                                <div>
                                    <label for="titulo-<?= (int) $medio['id']; ?>">Título</label>
                                    <input type="text" id="titulo-<?= (int) $medio['id']; ?>" name="titulo" value="<?= htmlspecialchars($medio['titulo'], ENT_QUOTES, 'UTF-8'); ?>" required />
                                </div>
                                <div>
                                    <label for="alt-<?= (int) $medio['id']; ?>">Texto alternativo</label>
                                    <input type="text" id="alt-<?= (int) $medio['id']; ?>" name="texto_alternativo" value="<?= htmlspecialchars((string) ($medio['texto_alternativo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div>
                                    <label for="desc-<?= (int) $medio['id']; ?>">Descripción</label>
                                    <textarea id="desc-<?= (int) $medio['id']; ?>" name="descripcion"><?= htmlspecialchars((string) ($medio['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div>
                                    <label for="creditos-<?= (int) $medio['id']; ?>">Créditos</label>
                                    <input type="text" id="creditos-<?= (int) $medio['id']; ?>" name="creditos" value="<?= htmlspecialchars((string) ($medio['creditos'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="media-card__actions">
                                    <button type="submit">Guardar cambios</button>
                                    <button type="submit" class="media-delete" form="delete-media-<?= (int) $medio['id']; ?>" onclick="return confirm('¿Eliminar este medio de forma permanente?');">🗑️ Eliminar</button>
                                </div>
                            </form>
                            <form method="post" id="delete-media-<?= (int) $medio['id']; ?>" style="display:none">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="media_id" value="<?= (int) $medio['id']; ?>" />
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
