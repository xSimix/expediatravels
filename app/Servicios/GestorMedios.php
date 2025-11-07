<?php

declare(strict_types=1);

namespace Aplicacion\Servicios;

use DateTimeImmutable;
use Exception;
use RuntimeException;

class GestorMedios
{
    private string $directorioBase;

    public function __construct(?string $directorioBase = null)
    {
        $this->directorioBase = $directorioBase ?? dirname(__DIR__, 2) . '/almacenamiento/medios';
        $this->asegurarDirectorio();
    }

    /**
     * Procesa un archivo subido y lo mueve al almacenamiento definitivo.
     *
     * @param array<string, mixed> $archivo Datos del archivo provenientes de $_FILES.
     * @param string $hash Hash SHA-1 calculado previamente para el archivo.
     *
     * @return array<string, mixed>
     */
    public function guardarArchivo(array $archivo, string $hash): array
    {
        if (!isset($archivo['tmp_name']) || !is_string($archivo['tmp_name']) || $archivo['tmp_name'] === '') {
            throw new RuntimeException('No se pudo identificar el archivo temporal subido.');
        }

        if (!isset($archivo['error']) || (int) $archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('El archivo no se subió correctamente.');
        }

        $rutaTemporal = $archivo['tmp_name'];
        if (!is_uploaded_file($rutaTemporal) && !is_file($rutaTemporal)) {
            throw new RuntimeException('El archivo temporal no está disponible para su procesamiento.');
        }

        $detallesImagen = @getimagesize($rutaTemporal);
        if ($detallesImagen === false) {
            throw new RuntimeException('El archivo cargado no es una imagen válida.');
        }

        $mimeType = isset($detallesImagen['mime']) ? (string) $detallesImagen['mime'] : 'application/octet-stream';
        if (!str_starts_with($mimeType, 'image/')) {
            throw new RuntimeException('Solo se permiten archivos de imagen.');
        }

        $extension = strtolower(pathinfo((string) ($archivo['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = $this->obtenerExtensionDesdeMime($mimeType);
        }
        if ($extension === '') {
            throw new RuntimeException('No se pudo determinar la extensión del archivo.');
        }

        $nombreGenerado = $this->generarNombreArchivo($extension);
        $rutaDestino = $this->directorioBase . '/' . $nombreGenerado;

        if (!@move_uploaded_file($rutaTemporal, $rutaDestino)) {
            if (!@rename($rutaTemporal, $rutaDestino)) {
                throw new RuntimeException('No se pudo mover el archivo subido al almacenamiento definitivo.');
            }
        }

        $tamano = isset($archivo['size']) ? (int) $archivo['size'] : filesize($rutaDestino);

        return [
            'nombre_archivo' => $nombreGenerado,
            'ruta_relativa' => $this->obtenerRutaPublica($nombreGenerado),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'tamano_bytes' => $tamano !== false ? $tamano : 0,
            'ancho' => isset($detallesImagen[0]) ? (int) $detallesImagen[0] : null,
            'alto' => isset($detallesImagen[1]) ? (int) $detallesImagen[1] : null,
            'sha1_hash' => $hash,
            'nombre_original' => is_string($archivo['name'] ?? null) ? (string) $archivo['name'] : $nombreGenerado,
        ];
    }

    private function asegurarDirectorio(): void
    {
        if (!is_dir($this->directorioBase)) {
            $creado = @mkdir($this->directorioBase, 0775, true);
            if (!$creado && !is_dir($this->directorioBase)) {
                throw new RuntimeException('No se pudo crear el directorio de medios.');
            }
        }
    }

    private function generarNombreArchivo(string $extension): string
    {
        $timestamp = (new DateTimeImmutable('now'))->format('Ymd_His');

        try {
            $aleatorio = bin2hex(random_bytes(4));
        } catch (Exception $exception) {
            $aleatorio = substr(sha1((string) microtime(true)), 0, 8);
        }

        return sprintf('media_%s_%s.%s', $timestamp, $aleatorio, $extension);
    }

    private function obtenerRutaPublica(string $nombreArchivo): string
    {
        return 'almacenamiento/medios/' . ltrim($nombreArchivo, '/');
    }

    private function obtenerExtensionDesdeMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            default => '',
        };
    }
}
