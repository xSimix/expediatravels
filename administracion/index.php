<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioPanelControl;

$zonaHoraria = new \DateTimeZone('America/Lima');
$ahora = new \DateTimeImmutable('now', $zonaHoraria);

$repositorio = new RepositorioPanelControl();

$metricas = $repositorio->obtenerMetricas($ahora);
$metricas = array_merge([
    'reservasHoy' => 0,
    'reservasConfirmadasHoy' => 0,
    'consultasPendientes' => 0,
    'consultasNuevasSemana' => 0,
    'paquetesActivos' => 0,
    'paquetesNuevosSemana' => 0,
    'salidasProximas' => 0,
    'siguienteSalida' => null,
    'usuariosActivos' => 0,
], $metricas);

$reservasRecientes = $repositorio->obtenerReservasRecientes(5);
$adminPrincipal = $repositorio->obtenerAdministradorPrincipal();

$inicioMes = $ahora->modify('first day of this month');
$finMes = $ahora->modify('last day of this month');
$eventosCalendario = $repositorio->obtenerSalidasCalendario($inicioMes, $finMes);
$calendarioJson = json_encode($eventosCalendario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($calendarioJson === false) {
    $calendarioJson = '[]';
}

$proximaSalidaTexto = '—';
if (!empty($metricas['siguienteSalida'])) {
    try {
        $fechaSalida = new \DateTimeImmutable((string) $metricas['siguienteSalida'], $zonaHoraria);
        $proximaSalidaTexto = $fechaSalida->format('d/m');
    } catch (\Exception $exception) {
        $proximaSalidaTexto = (string) $metricas['siguienteSalida'];
    }
}

$nombreAdmin = trim(($adminPrincipal['nombre'] ?? '') . ' ' . ($adminPrincipal['apellidos'] ?? ''));
$correoAdmin = $adminPrincipal['correo'] ?? 'admin@expediatravels.pe';
$inicialesAdmin = 'AD';
if ($nombreAdmin !== '') {
    $partes = preg_split('/\s+/', $nombreAdmin);
    $primera = $partes[0] ?? '';
    $ultima = $partes[count($partes) - 1] ?? '';
    $iniciales = mb_substr($primera, 0, 1) . mb_substr($ultima, 0, 1);
    if ($iniciales !== '') {
        $inicialesAdmin = mb_strtoupper($iniciales);
    }
}

$statusMap = [
    'confirmada' => ['label' => 'Confirmada', 'class' => 'ok', 'icon' => '✔'],
    'pendiente' => ['label' => 'Pendiente', 'class' => 'warn', 'icon' => '⧗'],
    'cancelada' => ['label' => 'Cancelada', 'class' => 'danger', 'icon' => '✖'],
];

function formatearFechaHora(?string $valor, \DateTimeZone $zona): string
{
    if ($valor === null || $valor === '') {
        return '—';
    }

    try {
        $fecha = new \DateTimeImmutable($valor, $zona);
    } catch (\Exception $exception) {
        try {
            $fecha = new \DateTimeImmutable($valor);
        } catch (\Exception $exception) {
            return $valor;
        }
    }

    return $fecha->format('d/m/Y H:i');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Expediatravels · Panel de Control</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="recursos/dashboard.css" />
</head>
<body data-timezone="America/Lima">
  <div class="app">
    <aside class="sidebar" id="sidebar" aria-label="Barra lateral">
      <div class="brand" aria-label="Marca">
        <div class="logo" aria-hidden="true">Ex</div>
        <div>
          <b>Expediatravels</b><br>
          <small style="color:var(--muted)">Panel de Control</small>
        </div>
      </div>

      <div class="search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <input type="search" placeholder="Buscar…" aria-label="Buscar en el panel">
      </div>

      <div class="section">Gestión</div>
      <nav class="nav" role="navigation">
        <a class="active" href="index.php"><span>🏠</span> Inicio</a>
        <a href="destinos.php"><span>📍</span> Destinos</a>
        <a href="paquetes.php"><span>🎒</span> Paquetes</a>
        <a href="usuarios.php"><span>🧑‍💼</span> Usuarios</a>
      </nav>

      <div class="section">Operación</div>
      <nav class="nav">
        <a href="reportes.php"><span>📊</span> Reportes</a>
        <a href="ajustes.php"><span>⚙️</span> Ajustes</a>
        <a href="configuracion.php"><span>🛠️</span> Configuración</a>
        <a href="../web/autenticacion.php?logout=1"><span>⏻</span> Cerrar sesión</a>
      </nav>
    </aside>

    <main class="main">
      <header class="topbar" aria-label="Barra de estado">
        <div class="topbar-inner">
          <div class="left">
            <button id="btnMenu" class="hamburger" aria-label="Abrir menú lateral" aria-controls="sidebar" aria-expanded="false">☰</button>
            <div class="badge" id="today">—</div>
            <div class="badge" id="clock">—</div>
          </div>
          <div class="right">
            <div class="pill" title="Usuarios verificados">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="12" cy="7" r="3" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span id="online" data-target="<?= (int) $metricas['usuariosActivos']; ?>">0</span>
            </div>
            <div class="user">
              <div style="text-align:right">
                <div style="font-weight:600"><?= htmlspecialchars($nombreAdmin !== '' ? $nombreAdmin : 'Administrador'); ?></div>
                <div style="font-size:.8rem; color:var(--muted)"><?= htmlspecialchars($correoAdmin); ?></div>
              </div>
              <div class="avatar" aria-label="Icono de usuario"><?= htmlspecialchars($inicialesAdmin); ?></div>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="grid">
          <div class="card">
            <h4>Reservas de hoy</h4>
            <div class="metric"><?= number_format((int) $metricas['reservasHoy']); ?></div>
            <div class="trend"><?= number_format((int) $metricas['reservasConfirmadasHoy']); ?> confirmadas</div>
          </div>
          <div class="card">
            <h4>Consultas abiertas</h4>
            <div class="metric"><?= number_format((int) $metricas['consultasPendientes']); ?></div>
            <div class="trend"><?= number_format((int) $metricas['consultasNuevasSemana']); ?> nuevas esta semana</div>
          </div>
          <div class="card">
            <h4>Paquetes activos</h4>
            <div class="metric"><?= number_format((int) $metricas['paquetesActivos']); ?></div>
            <div class="trend"><?= number_format((int) $metricas['paquetesNuevosSemana']); ?> nuevos publicados</div>
          </div>
          <div class="card">
            <h4>Salidas próximas</h4>
            <div class="metric"><?= number_format((int) $metricas['salidasProximas']); ?></div>
            <div class="trend">Próx. <?= htmlspecialchars($proximaSalidaTexto); ?></div>
          </div>

          <div class="panel wide">
            <h3>Reservas recientes</h3>
            <table class="table<?= empty($reservasRecientes) ? ' table--empty' : ''; ?>" aria-label="Tabla de reservas recientes">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Servicio</th>
                  <th>Personas</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reservasRecientes)): ?>
                  <tr>
                    <td colspan="5">No hay reservas registradas.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($reservasRecientes as $reserva): ?>
                    <?php
                      $estado = strtolower((string) ($reserva['estado'] ?? 'pendiente'));
                      $datosEstado = $statusMap[$estado] ?? ['label' => ucfirst($estado), 'class' => 'warn', 'icon' => '⧗'];
                    ?>
                    <tr>
                      <td><?= htmlspecialchars(formatearFechaHora($reserva['fecha'] ?? null, $zonaHoraria)); ?></td>
                      <td><?= htmlspecialchars($reserva['cliente'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($reserva['servicio'] ?? ''); ?></td>
                      <td><?= (int) ($reserva['personas'] ?? 0); ?></td>
                      <td>
                        <span class="status <?= htmlspecialchars($datosEstado['class']); ?>">
                          <?= htmlspecialchars($datosEstado['icon']); ?> <?= htmlspecialchars($datosEstado['label']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="panel tall">
            <h3>Atajos</h3>
            <div class="quick-actions">
              <a class="quick" href="destinos.php"><span>📍</span> Gestionar destinos</a>
              <a class="quick" href="paquetes.php"><span>🎒</span> Crear paquete</a>
              <a class="quick" href="usuarios.php"><span>🧑‍💼</span> Administrar usuarios</a>
              <a class="quick" href="reportes.php"><span>📊</span> Ver reportes</a>
              <a class="quick" href="ajustes.php"><span>⚙️</span> Ajustes del sitio</a>
              <a class="quick" href="configuracion.php"><span>🛠️</span> Configuración avanzada</a>
            </div>
          </div>

          <div class="panel wide">
            <h3>Calendario de actividades</h3>
            <div id="calendar" aria-label="Calendario" data-events='<?= htmlspecialchars($calendarioJson, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>'></div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script src="recursos/dashboard.js"></script>
</body>
</html>
