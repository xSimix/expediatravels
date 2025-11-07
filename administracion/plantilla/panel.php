<?php

declare(strict_types=1);

use Aplicacion\Repositorios\RepositorioPanelControl;

require_once __DIR__ . '/../../app/configuracion/arranque.php';

if (!function_exists('obtenerContextoPanel')) {
    /**
     * Obtiene información común para la barra lateral y superior del panel.
     *
     * @param RepositorioPanelControl|null $repositorio
     * @return array{
     *     zonaHoraria: DateTimeZone,
     *     metricas: array<string, mixed>,
     *     nombreAdmin: string,
     *     correoAdmin: string,
     *     inicialesAdmin: string
     * }
     */
    function obtenerContextoPanel(?RepositorioPanelControl $repositorio = null): array
    {
        $zonaHoraria = new DateTimeZone('America/Lima');
        $ahora = new DateTimeImmutable('now', $zonaHoraria);

        if ($repositorio === null) {
            $repositorio = new RepositorioPanelControl();
        }

        $metricasBase = [
            'reservasHoy' => 0,
            'reservasConfirmadasHoy' => 0,
            'consultasPendientes' => 0,
            'consultasNuevasSemana' => 0,
            'paquetesActivos' => 0,
            'paquetesNuevosSemana' => 0,
            'salidasProximas' => 0,
            'siguienteSalida' => null,
            'usuariosActivos' => 0,
        ];

        $metricas = $repositorio->obtenerMetricas($ahora);
        if (!is_array($metricas)) {
            $metricas = [];
        }
        $metricas = array_merge($metricasBase, $metricas);

        $adminPrincipal = $repositorio->obtenerAdministradorPrincipal();
        $nombreAdmin = '';
        $correoAdmin = 'admin@expediatravels.pe';
        $inicialesAdmin = 'AD';

        if (is_array($adminPrincipal)) {
            $nombre = trim((string) ($adminPrincipal['nombre'] ?? ''));
            $apellidos = trim((string) ($adminPrincipal['apellidos'] ?? ''));
            $correo = trim((string) ($adminPrincipal['correo'] ?? ''));

            if ($nombre !== '' || $apellidos !== '') {
                $nombreAdmin = trim($nombre . ' ' . $apellidos);
            }

            if ($correo !== '') {
                $correoAdmin = $correo;
            }

            $primera = $nombre !== '' ? $nombre : ($apellidos !== '' ? $apellidos : '');
            $ultima = $apellidos !== '' ? $apellidos : ($nombre !== '' ? $nombre : '');
            $inicial = mb_substr($primera, 0, 1) . mb_substr($ultima, 0, 1);

            if ($inicial !== '') {
                $inicialesAdmin = mb_strtoupper($inicial);
            }
        }

        return [
            'zonaHoraria' => $zonaHoraria,
            'metricas' => $metricas,
            'nombreAdmin' => $nombreAdmin !== '' ? $nombreAdmin : 'Administrador',
            'correoAdmin' => $correoAdmin,
            'inicialesAdmin' => $inicialesAdmin,
        ];
    }
}
