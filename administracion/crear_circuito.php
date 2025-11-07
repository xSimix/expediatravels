<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

require_once __DIR__ . '/includes/circuitos_util.php';

$archivoDestinos = __DIR__ . '/../almacenamiento/destinos.json';
$archivoCircuitos = __DIR__ . '/../almacenamiento/circuitos.json';

$errores = [];
$destinosPredeterminados = require __DIR__ . '/../app/configuracion/destinos_predeterminados.php';
$circuitosPredeterminados = require __DIR__ . '/../app/configuracion/circuitos_predeterminados.php';

$destinosDisponibles = cargarDestinosDisponibles($archivoDestinos, $destinosPredeterminados, $errores);
$circuitos = cargarCircuitos($archivoCircuitos, $circuitosPredeterminados, $destinosDisponibles, $errores);

$categoriasPermitidas = [
    'naturaleza' => 'Naturaleza y aire libre',
    'cultural' => 'Cultural e histórico',
    'aventura' => 'Aventura y adrenalina',
    'gastronomico' => 'Gastronómico',
    'bienestar' => 'Bienestar y relajación',
];

$dificultadesPermitidas = [
    'relajado' => 'Relajado',
    'moderado' => 'Moderado',
    'intenso' => 'Intenso',
];

$estadosPermitidos = [
    'activo' => 'Activo',
    'borrador' => 'Borrador',
    'inactivo' => 'Inactivo',
];

$datos = [
    'nombre' => '',
    'destino_id' => 0,
    'destino_personalizado' => '',
    'duracion' => '',
    'categoria' => 'naturaleza',
    'dificultad' => 'relajado',
    'frecuencia' => '',
    'estado' => 'borrador',
    'descripcion' => '',
    'puntos_interes' => '',
    'servicios' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $datos['destino_id'] = isset($_POST['destino_id']) ? (int) $_POST['destino_id'] : 0;
    $datos['destino_personalizado'] = trim((string) ($_POST['destino_personalizado'] ?? ''));
    $datos['duracion'] = trim((string) ($_POST['duracion'] ?? ''));
    $datos['categoria'] = strtolower(trim((string) ($_POST['categoria'] ?? 'naturaleza')));
    $datos['dificultad'] = strtolower(trim((string) ($_POST['dificultad'] ?? 'relajado')));
    $datos['frecuencia'] = trim((string) ($_POST['frecuencia'] ?? ''));
    $datos['estado'] = strtolower(trim((string) ($_POST['estado'] ?? 'borrador')));
    $datos['descripcion'] = trim((string) ($_POST['descripcion'] ?? ''));
    $datos['puntos_interes'] = trim((string) ($_POST['puntos_interes'] ?? ''));
    $datos['servicios'] = trim((string) ($_POST['servicios'] ?? ''));

    if ($datos['nombre'] === '') {
        $errores[] = 'Debes indicar el nombre del circuito.';
    }

    if ($datos['destino_id'] <= 0 && $datos['destino_personalizado'] === '') {
        $errores[] = 'Selecciona un destino o escribe uno personalizado.';
    }

    if ($datos['duracion'] === '') {
        $errores[] = 'Define la duración del circuito (por ejemplo: Full day, 2 días / 1 noche).';
    }

    if (!array_key_exists($datos['categoria'], $categoriasPermitidas)) {
        $errores[] = 'La categoría seleccionada no es válida.';
    }

    if (!array_key_exists($datos['dificultad'], $dificultadesPermitidas)) {
        $errores[] = 'La dificultad seleccionada no es válida.';
    }

    if (!array_key_exists($datos['estado'], $estadosPermitidos)) {
        $errores[] = 'El estado seleccionado no es válido.';
    }

    $puntosInteres = convertirListado($datos['puntos_interes']);
    $serviciosIncluidos = convertirListado($datos['servicios']);

    $destinoNombre = $datos['destino_personalizado'];
    $destinoRegion = '';
    if ($datos['destino_id'] > 0) {
        $destinoNombre = $destinosDisponibles[$datos['destino_id']]['nombre'] ?? $destinoNombre;
        $destinoRegion = $destinosDisponibles[$datos['destino_id']]['region'] ?? '';
    }

    if (empty($errores)) {
        $nuevoCircuito = [
            'id' => obtenerSiguienteId($circuitos),
            'nombre' => $datos['nombre'],
            'destino' => [
                'id' => $datos['destino_id'] > 0 ? $datos['destino_id'] : null,
                'nombre' => $destinoNombre,
                'personalizado' => $datos['destino_personalizado'],
                'region' => $destinoRegion,
            ],
            'duracion' => $datos['duracion'],
            'categoria' => $datos['categoria'],
            'dificultad' => $datos['dificultad'],
            'frecuencia' => $datos['frecuencia'],
            'descripcion' => $datos['descripcion'],
            'puntos_interes' => $puntosInteres,
            'servicios' => $serviciosIncluidos,
            'estado' => $datos['estado'],
            'actualizado_en' => date('c'),
        ];

        $circuitos[] = normalizarCircuito($nuevoCircuito, $destinosDisponibles);
        $circuitos = ordenarCircuitos($circuitos);

        try {
            ServicioAlmacenamientoJson::guardar($archivoCircuitos, $circuitos);
            header('Location: circuitos.php?creado=1');
            exit;
        } catch (RuntimeException $exception) {
            $errores[] = 'El circuito se creó, pero no se pudo guardar en almacenamiento.';
        }
    }
}

$paginaActiva = 'circuitos_crear';
$tituloPagina = 'Nuevo circuito — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Registrar un nuevo circuito</h1>
            <p>Define los detalles operativos del recorrido para combinarlo luego en paquetes turísticos.</p>
        </div>
        <div class="admin-actions">
            <a class="admin-button secondary" href="circuitos.php">← Volver al listado</a>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>Revisa los siguientes puntos antes de continuar:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <form method="post" class="admin-grid">
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="nombre">Nombre del circuito *</label>
                    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Full day Pozuzo" />
                </div>
                <div class="admin-field">
                    <label for="duracion">Duración *</label>
                    <input type="text" id="duracion" name="duracion" required value="<?= htmlspecialchars($datos['duracion'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="2 días / 1 noche" />
                </div>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="destino_id">Destino asociado</label>
                    <select id="destino_id" name="destino_id">
                        <option value="0">Selecciona un destino del catálogo</option>
                        <?php foreach ($destinosDisponibles as $destinoId => $destino): ?>
                            <option value="<?= (int) $destinoId; ?>" <?= $datos['destino_id'] === (int) $destinoId ? 'selected' : ''; ?>><?= htmlspecialchars($destino['nombre'] . ' · ' . $destino['region'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="admin-help">Elige un destino existente o ingresa uno personalizado.</p>
                </div>
                <div class="admin-field">
                    <label for="destino_personalizado">Destino personalizado</label>
                    <input type="text" id="destino_personalizado" name="destino_personalizado" value="<?= htmlspecialchars($datos['destino_personalizado'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Reserva de biosfera" />
                </div>
            </div>

            <div class="admin-grid three-columns">
                <div class="admin-field">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categoriasPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['categoria'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="dificultad">Dificultad</label>
                    <select id="dificultad" name="dificultad">
                        <?php foreach ($dificultadesPermitidas as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['dificultad'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <?php foreach ($estadosPermitidos as $clave => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" <?= $datos['estado'] === $clave ? 'selected' : ''; ?>><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-field">
                <label for="frecuencia">Frecuencia de salida</label>
                <input type="text" id="frecuencia" name="frecuencia" value="<?= htmlspecialchars($datos['frecuencia'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Sábados y domingos" />
            </div>

            <div class="admin-field">
                <label for="descripcion">Descripción comercial</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe el itinerario y las experiencias destacadas."><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="puntos_interes">Puntos de interés</label>
                    <textarea id="puntos_interes" name="puntos_interes" rows="4" placeholder="Catarata Yulitunqui&#10;Plaza principal de Pozuzo"><?= htmlspecialchars($datos['puntos_interes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="admin-help">Escribe un punto de interés por línea.</p>
                </div>
                <div class="admin-field">
                    <label for="servicios">Servicios incluidos</label>
                    <textarea id="servicios" name="servicios" rows="4" placeholder="Transporte turístico&#10;Guía bilingüe"><?= htmlspecialchars($datos['servicios'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="admin-help">Escribe un servicio por línea.</p>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-button">Guardar circuito</button>
                <a class="admin-button secondary" href="circuitos.php">Cancelar</a>
            </div>
        </form>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
