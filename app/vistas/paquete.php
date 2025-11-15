<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}
$pageTitle = $title ?? ($detail['title'] ?? ($detail['nombre'] ?? $siteTitle));
$currentUser = $currentUser ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
</head>
<body class="page page--detail">
    <?php $activeNav = 'paquetes'; include __DIR__ . '/partials/site-header.php'; ?>
    <?php include __DIR__ . '/partials/detail-page.php'; ?>
    <?php include __DIR__ . '/partials/site-footer.php'; ?>
    <?php include __DIR__ . '/partials/auth-modal.php'; ?>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
