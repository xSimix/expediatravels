<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use DateInterval;
use DateTimeInterface;
use PDO;
use PDOException;

class RepositorioPanelControl
{
    public function obtenerMetricas(DateTimeInterface $fechaActual): array
    {
        $metricas = [
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

        try {
            $pdo = Conexion::obtener();
            $hoy = $fechaActual->format('Y-m-d');
            $haceSieteDias = $fechaActual->sub(new DateInterval('P7D'))->format('Y-m-d');

            $metricas['reservasHoy'] = $this->contar($pdo, 'SELECT COUNT(*) FROM reservas WHERE fecha_reserva = :fecha', [
                ':fecha' => $hoy,
            ]);

            $metricas['reservasConfirmadasHoy'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM reservas WHERE fecha_reserva = :fecha AND estado = "confirmada"',
                [':fecha' => $hoy]
            );

            $metricas['consultasPendientes'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM consultas_contacto WHERE estado = "abierta"'
            );

            $metricas['consultasNuevasSemana'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM consultas_contacto WHERE DATE(creado_en) >= :desde',
                [':desde' => $haceSieteDias]
            );

            $metricas['paquetesActivos'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM paquetes WHERE estado = "publicado"'
            );

            $metricas['paquetesNuevosSemana'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM paquetes WHERE estado = "publicado" AND DATE(creado_en) >= :desde',
                [':desde' => $haceSieteDias]
            );

            $metricas['salidasProximas'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM salidas_programadas WHERE fecha_salida >= :hoy AND estado IN ("programada", "confirmada", "reprogramada")',
                [':hoy' => $hoy]
            );

            $siguienteSalida = $this->obtenerValor(
                $pdo,
                'SELECT fecha_salida FROM salidas_programadas WHERE fecha_salida >= :hoy AND estado IN ("programada", "confirmada", "reprogramada") ORDER BY fecha_salida ASC LIMIT 1',
                [':hoy' => $hoy]
            );
            $metricas['siguienteSalida'] = is_string($siguienteSalida) ? $siguienteSalida : null;

            $metricas['usuariosActivos'] = $this->contar(
                $pdo,
                'SELECT COUNT(*) FROM usuarios WHERE verificado_en IS NOT NULL'
            );
        } catch (PDOException $exception) {
            // Usa las métricas por defecto cuando la base de datos no responde.
        }

        return $metricas;
    }

    public function obtenerReservasRecientes(int $limite = 5): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT r.id, r.creado_en, r.fecha_reserva, r.cantidad_personas, r.estado, r.total, '
                . 'CONCAT(u.nombre, " ", u.apellidos) AS cliente, p.nombre AS paquete '
                . 'FROM reservas r '
                . 'INNER JOIN usuarios u ON u.id = r.usuario_id '
                . 'INNER JOIN paquetes p ON p.id = r.paquete_id '
                . 'ORDER BY r.creado_en DESC '
                . 'LIMIT :limite'
            );
            $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
            $statement->execute();

            $filas = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map(static function (array $fila): array {
                return [
                    'fecha' => $fila['creado_en'] ?? '',
                    'cliente' => trim((string) ($fila['cliente'] ?? '')),
                    'servicio' => $fila['paquete'] ?? '',
                    'personas' => (int) ($fila['cantidad_personas'] ?? 0),
                    'estado' => $fila['estado'] ?? 'pendiente',
                    'total' => isset($fila['total']) ? (float) $fila['total'] : 0.0,
                ];
            }, $filas ?: []);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function obtenerSalidasCalendario(DateTimeInterface $inicio, DateTimeInterface $fin): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT sp.fecha_salida, sp.estado, sp.aforo, sp.reservas_confirmadas, sp.notas, p.nombre AS paquete '
                . 'FROM salidas_programadas sp '
                . 'INNER JOIN paquetes p ON p.id = sp.paquete_id '
                . 'WHERE sp.fecha_salida BETWEEN :inicio AND :fin '
                . 'ORDER BY sp.fecha_salida ASC'
            );
            $statement->execute([
                ':inicio' => $inicio->format('Y-m-d'),
                ':fin' => $fin->format('Y-m-d'),
            ]);

            $filas = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map(static function (array $fila): array {
                return [
                    'date' => $fila['fecha_salida'] ?? '',
                    'estado' => $fila['estado'] ?? 'programada',
                    'paquete' => $fila['paquete'] ?? '',
                    'aforo' => isset($fila['aforo']) ? (int) $fila['aforo'] : null,
                    'reservas_confirmadas' => isset($fila['reservas_confirmadas']) ? (int) $fila['reservas_confirmadas'] : 0,
                    'notas' => $fila['notas'] ?? null,
                ];
            }, $filas ?: []);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function obtenerAdministradorPrincipal(): ?array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->query(
                'SELECT id, nombre, apellidos, correo '
                . 'FROM usuarios '
                . 'WHERE rol IN ("administrador", "moderador") '
                . 'ORDER BY id ASC '
                . 'LIMIT 1'
            );

            $admin = $statement->fetch(PDO::FETCH_ASSOC);

            return $admin !== false ? $admin : null;
        } catch (PDOException $exception) {
            return null;
        }
    }

    private function contar(PDO $pdo, string $consulta, array $parametros = []): int
    {
        $statement = $pdo->prepare($consulta);
        $statement->execute($parametros);
        $valor = $statement->fetchColumn();

        return $valor !== false && $valor !== null ? (int) $valor : 0;
    }

    private function obtenerValor(PDO $pdo, string $consulta, array $parametros = []): mixed
    {
        $statement = $pdo->prepare($consulta);
        $statement->execute($parametros);

        return $statement->fetchColumn();
    }
}
