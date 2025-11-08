<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\BaseDatos\Conexion;

$errores = [];
$reservas = [];

try {
    $pdo = Conexion::obtener();
    $statement = $pdo->query(
        'SELECT r.id, r.fecha_reserva, r.cantidad_personas, r.total, r.estado, r.creado_en, '
        . 'p.nombre AS paquete, '
        . 'CONCAT(u.nombre, " ", u.apellidos) AS cliente, u.correo '
        . 'FROM reservas r '
        . 'INNER JOIN usuarios u ON u.id = r.usuario_id '
        . 'INNER JOIN paquetes p ON p.id = r.paquete_id '
        . 'ORDER BY r.creado_en DESC'
    );

    $reservas = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $exception) {
    $errores[] = 'No se pudo conectar con la base de datos de reservas. Inténtalo nuevamente más tarde.';
}

$statusEtiquetas = [
    'confirmada' => 'Confirmada',
    'pendiente' => 'Pendiente',
    'cancelada' => 'Cancelada',
];

$statusClases = [
    'confirmada' => 'admin-badge--activo',
    'pendiente' => 'admin-badge--borrador',
    'cancelada' => 'admin-badge--inactivo',
];

if (!function_exists('formatearMarcaTiempo')) {
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
}

$paginaActiva = 'reservas';
$tituloPagina = 'Reservas — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <div>
            <h1>Administración de reservas</h1>
            <p>Consulta el detalle de las reservas realizadas, su estado y la información de contacto del cliente.</p>
        </div>
    </header>

    <?php if (!empty($errores)): ?>
        <div class="admin-alert error">
            <p><strong>No fue posible obtener las reservas:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card admin-card--flush">
        <?php if (empty($reservas)): ?>
            <p class="admin-empty">Aún no hay reservas registradas en el sistema.</p>
        <?php else: ?>
            <div class="admin-table-wrapper">
                <table class="admin-table" aria-label="Listado de reservas">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Registrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                            <?php
                                $estado = strtolower((string) ($reserva['estado'] ?? 'pendiente'));
                                $etiquetaEstado = $statusEtiquetas[$estado] ?? ucfirst($estado);
                                $claseEstado = $statusClases[$estado] ?? 'admin-badge--borrador';
                                $fechaReserva = $reserva['fecha_reserva'] ?? null;
                                $fechaReservaTexto = '—';
                                if ($fechaReserva) {
                                    try {
                                        $fecha = new DateTimeImmutable((string) $fechaReserva);
                                        $fechaReservaTexto = $fecha->format('d/m/Y');
                                    } catch (Exception $exception) {
                                        $fechaReservaTexto = (string) $fechaReserva;
                                    }
                                }

                                $creadoEn = $reserva['creado_en'] ?? null;
                                $creadoEnTexto = formatearMarcaTiempo($creadoEn !== null ? (string) $creadoEn : null);
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?= (int) ($reserva['id'] ?? 0); ?></strong>
                                    <p class="admin-table__meta">Fecha de viaje: <?= htmlspecialchars($fechaReservaTexto, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-table__meta">Personas: <?= (int) ($reserva['cantidad_personas'] ?? 0); ?></p>
                                    <p class="admin-table__meta">Total: <?= htmlspecialchars(number_format((float) ($reserva['total'] ?? 0.0), 2), ENT_QUOTES, 'UTF-8'); ?></p>
                                </td>
                                <td>
                                    <p class="admin-table__user"><?= htmlspecialchars(trim((string) ($reserva['cliente'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="admin-table__meta"><?= htmlspecialchars((string) ($reserva['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                </td>
                                <td><?= htmlspecialchars((string) ($reserva['paquete'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="admin-badge <?= $claseEstado; ?>"><?= htmlspecialchars($etiquetaEstado, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td><?= htmlspecialchars($creadoEnTexto, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/plantilla/pie.php'; ?>
