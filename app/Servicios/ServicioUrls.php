<?php

namespace Aplicacion\Servicios;

use function array_filter;
use function array_map;

/**
 * Genera URLs semánticas para contenidos (destinos, paquetes y circuitos)
 * y permite extraer segmentos cuando se reciben solicitudes amigables.
 */
class ServicioUrls
{
    private const UBICACION_FALLBACK = 'peru';

    /**
     * Obtiene la URL relativa para un destino.
     */
    public static function destino(array $destino): string
    {
        return self::buildContentPath('destino', $destino, 'nombre');
    }

    /**
     * Obtiene la URL relativa para un paquete.
     */
    public static function paquete(array $paquete): string
    {
        return self::buildContentPath('paquete', $paquete, 'nombre');
    }

    /**
     * Obtiene la URL relativa para un circuito.
     */
    public static function circuito(array $circuito): string
    {
        return self::buildContentPath('circuito', $circuito, 'nombre');
    }

    /**
     * Extrae el slug final de la ruta amigable solicitada.
     */
    public static function extraerSlugDesdeRequest(string $seccion): string
    {
        $slug = (string) ($_GET['slug'] ?? '');
        if ($slug !== '') {
            return trim($slug);
        }

        $segmentos = self::extraerSegmentosDesdeRequest($seccion);
        if (empty($segmentos)) {
            return '';
        }

        return trim(urldecode((string) end($segmentos)));
    }

    /**
     * Extrae el segmento de ubicación de la ruta amigable solicitada.
     */
    public static function extraerUbicacionDesdeRequest(string $seccion): ?string
    {
        $segmentos = self::extraerSegmentosDesdeRequest($seccion);
        if (empty($segmentos)) {
            return null;
        }

        return trim(urldecode((string) $segmentos[0]));
    }

    private static function buildContentPath(string $seccion, array $contenido, string $fallbackKey): string
    {
        $slug = self::resolveSlug($contenido, $fallbackKey);
        $ubicacion = self::resolveLocationSlug($contenido);

        return $seccion . '/' . rawurlencode($ubicacion) . '/' . rawurlencode($slug);
    }

    private static function resolveSlug(array $contenido, string $fallbackKey): string
    {
        $candidatos = [
            $contenido['slug'] ?? null,
            $contenido[$fallbackKey] ?? null,
            $contenido['nombre'] ?? null,
            $contenido['title'] ?? null,
        ];

        foreach ($candidatos as $candidato) {
            $slug = self::slugifySegment($candidato);
            if ($slug !== '') {
                return $slug;
            }
        }

        return 'contenido';
    }

    private static function resolveLocationSlug(array $contenido): string
    {
        $candidatos = [
            $contenido['region'] ?? null,
            $contenido['ubicacion'] ?? null,
            $contenido['location'] ?? null,
            $contenido['destino'] ?? null,
        ];

        foreach ($candidatos as $candidato) {
            if (!is_string($candidato)) {
                continue;
            }

            $candidato = trim($candidato);
            if ($candidato === '') {
                continue;
            }

            // Normaliza dividiendo por separadores comunes (—, -, ,)
            $partes = preg_split('/[—–−\-,]/u', $candidato);
            $partes = array_filter(array_map('trim', (array) $partes), static fn ($valor) => $valor !== '');

            foreach ($partes as $parte) {
                $slug = self::slugifySegment($parte);
                if ($slug !== '') {
                    return $slug;
                }
            }

            $slugCompleto = self::slugifySegment($candidato);
            if ($slugCompleto !== '') {
                return $slugCompleto;
            }
        }

        return self::UBICACION_FALLBACK;
    }

    private static function extraerSegmentosDesdeRequest(string $seccion): array
    {
        $segmentos = [];

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (is_string($requestUri) && $requestUri !== '') {
            $ruta = parse_url($requestUri, PHP_URL_PATH) ?? '';
            $segmentos = self::segmentarRuta($ruta, $seccion);
        }

        if (empty($segmentos)) {
            $pathInfo = $_SERVER['PATH_INFO'] ?? '';
            if (is_string($pathInfo) && $pathInfo !== '') {
                $segmentos = self::segmentarRuta($pathInfo, $seccion, false);
            }
        }

        return $segmentos;
    }

    private static function segmentarRuta(string $ruta, string $seccion, bool $ajustarBase = true): array
    {
        if ($ruta === '') {
            return [];
        }

        $rutaNormalizada = $ruta;
        if ($ajustarBase) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            if (is_string($scriptName) && $scriptName !== '') {
                $directorio = str_replace('\\', '/', dirname($scriptName));
                if ($directorio !== '/' && $directorio !== '\\') {
                    $directorio = rtrim($directorio, '/');
                }
                if ($directorio !== '' && strncmp($rutaNormalizada, $directorio, strlen($directorio)) === 0) {
                    $rutaNormalizada = substr($rutaNormalizada, strlen($directorio));
                }
            }
        }

        $rutaNormalizada = trim($rutaNormalizada, '/');
        if ($rutaNormalizada === '') {
            return [];
        }

        $segmentos = array_values(array_filter(explode('/', $rutaNormalizada), static fn ($valor) => $valor !== ''));
        if (empty($segmentos)) {
            return [];
        }

        $primero = $segmentos[0];
        if ($primero === $seccion . '.php') {
            array_shift($segmentos);
        }

        if (!empty($segmentos) && $segmentos[0] === $seccion) {
            array_shift($segmentos);
        }

        return $segmentos;
    }

    private static function slugifySegment($valor): string
    {
        if (!is_string($valor)) {
            return '';
        }

        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }

        $valor = str_replace(['/', '\\'], ' ', $valor);
        $valor = preg_replace('/[—–−]+/u', '-', $valor);
        if (!is_string($valor)) {
            $valor = '';
        }

        $normalizado = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if (!is_string($normalizado)) {
            $normalizado = $valor;
        }

        $normalizado = strtolower($normalizado);
        $normalizado = preg_replace('/[^a-z0-9]+/', '-', $normalizado);
        if (!is_string($normalizado)) {
            $normalizado = '';
        }

        return trim($normalizado, '-');
    }
}
