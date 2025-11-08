<?php

declare(strict_types=1);

if (!function_exists('adminGenerarSlug')) {
    function adminGenerarSlug(string $texto): string
    {
        $base = trim($texto);
        if ($base === '') {
            return '';
        }

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $base);
        if (is_string($transliterated)) {
            $base = $transliterated;
        }

        $base = strtolower($base);
        $base = preg_replace('/[^a-z0-9]+/i', '-', $base) ?? '';
        $base = trim($base, '-');

        if ($base === '') {
            $fallback = preg_replace('/[^a-z0-9]+/i', '', strtolower($texto));
            if (is_string($fallback)) {
                $base = $fallback;
            }
        }

        return $base;
    }
}
