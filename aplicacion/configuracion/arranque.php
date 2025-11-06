<?php
/**
 * Archivo de arranque básico para Expediatravels.
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'Aplicacion\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relativeClass) . '.php';

    $segments = explode('/', $relativePath);
    $lastIndex = count($segments) - 1;
    $lowercaseSegments = $segments;

    for ($i = 0; $i < $lastIndex; $i++) {
        $lowercaseSegments[$i] = strtolower($lowercaseSegments[$i]);
    }

    $candidates = array_unique([
        $relativePath,
        implode('/', $lowercaseSegments),
    ]);

    foreach ($candidates as $relativeFile) {
        $file = $baseDir . $relativeFile;
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Cargar la configuración de entorno si está disponible.
$envFile = __DIR__ . '/../../.env.php';
if (file_exists($envFile)) {
    require $envFile;
}
