<?php

declare(strict_types=1);

require_once __DIR__ . '/panel.php';

$paginaActiva = $paginaActiva ?? 'inicio';
$tituloPagina = $tituloPagina ?? 'Expediatravels · Panel de Control';
$estilosExtra = $estilosExtra ?? [];

$contextoPanel = null;
$requiereContexto = !isset($panelMetricas, $panelNombreAdmin, $panelCorreoAdmin, $panelInicialesAdmin) || !($panelZonaHoraria ?? null) instanceof DateTimeZone;

if ($requiereContexto) {
    /** @var DateTimeZone|null $panelZonaHoraria */
    $contextoPanel = obtenerContextoPanel($panelRepositorio ?? null);
    $panelMetricas = $contextoPanel['metricas'];
    $panelNombreAdmin = $contextoPanel['nombreAdmin'];
    $panelCorreoAdmin = $contextoPanel['correoAdmin'];
    $panelInicialesAdmin = $contextoPanel['inicialesAdmin'];
    $panelZonaHoraria = $contextoPanel['zonaHoraria'];
}

if (!isset($panelZonaHoraria) || !($panelZonaHoraria instanceof DateTimeZone)) {
    $panelZonaHoraria = new DateTimeZone('America/Lima');
}

$zonaHorariaNombre = $panelZonaHoraria->getName();

unset($contextoPanel, $panelRepositorio);

$procesarEstilos = static function (array $estilos): array {
    $salida = [];

    foreach ($estilos as $estilo) {
        if (is_string($estilo) && $estilo !== '') {
            if (str_starts_with($estilo, '<')) {
                $salida[] = $estilo;
            } else {
                $salida[] = sprintf('<link rel="stylesheet" href="%s" />', htmlspecialchars($estilo, ENT_QUOTES));
            }
        }
    }

    return $salida;
};

$estilosProcesados = $procesarEstilos(is_array($estilosExtra) ? $estilosExtra : []);

$navActivo = static function (string $clave) use ($paginaActiva): string {
    return $paginaActiva === $clave ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($tituloPagina); ?></title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="recursos/dashboard.css" />
  <?php foreach ($estilosProcesados as $estilo): ?>
    <?= $estilo . "\n"; ?>
  <?php endforeach; ?>
</head>
<body data-timezone="<?= htmlspecialchars($zonaHorariaNombre, ENT_QUOTES); ?>">
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
        <a class="<?= $navActivo('inicio'); ?>" href="index.php"><span>🏠</span> Inicio</a>
        <a class="<?= $navActivo('destinos'); ?>" href="destinos.php"><span>📍</span> Destinos</a>
        <a class="<?= $navActivo('paquetes'); ?>" href="paquetes.php"><span>🎒</span> Paquetes</a>
        <a class="<?= $navActivo('usuarios'); ?>" href="usuarios.php"><span>🧑‍💼</span> Usuarios</a>
      </nav>

      <div class="section">Operación</div>
      <nav class="nav">
        <a class="<?= $navActivo('reportes'); ?>" href="reportes.php"><span>📊</span> Reportes</a>
        <a class="<?= $navActivo('configuracion'); ?>" href="configuracion.php"><span>🛠️</span> Configuración</a>
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
              <span id="online" data-target="<?= (int) ($panelMetricas['usuariosActivos'] ?? 0); ?>">0</span>
            </div>
            <div class="user">
              <div style="text-align:right">
                <div style="font-weight:600"><?= htmlspecialchars((string) $panelNombreAdmin); ?></div>
                <div style="font-size:.8rem; color:var(--muted)"><?= htmlspecialchars((string) $panelCorreoAdmin); ?></div>
              </div>
              <div class="avatar" aria-label="Icono de usuario"><?= htmlspecialchars((string) $panelInicialesAdmin); ?></div>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
