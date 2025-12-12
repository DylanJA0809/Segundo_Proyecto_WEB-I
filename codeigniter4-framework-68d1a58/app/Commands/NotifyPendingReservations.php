<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ReservationModel;
use App\Libraries\Mailer;

class NotifyPendingReservations extends BaseCommand
{
    protected $group       = 'Aventones';
    protected $name        = 'reservations:notify-pending';
    protected $description = 'Notifica a choferes con reservas pending más antiguas que X minutos.';

    public function run(array $params)
    {
        // Uso: php spark reservations:notify-pending 30
        $minutes = isset($params[0]) ? (int) $params[0] : 0;

        if ($minutes <= 0) {
            CLI::error("Uso: php spark {$this->name} <minutos>");
            return;
        }

        CLI::write("Buscando reservas pendientes de hace más de {$minutes} minutos...");

        $model = new ReservationModel();
        $drivers = $model->getPendingReservationsGroupedByDriver($minutes);

        if (empty($drivers)) {
            CLI::write("No se encontraron reservas pendientes más antiguas de {$minutes} minutos.");
            return;
        }

        $mailer = new Mailer();
        $totalSent = 0;

        foreach ($drivers as $driver) {
            $to    = (string) ($driver['email'] ?? '');
            $name  = (string) ($driver['name'] ?? 'Driver');
            $count = isset($driver['reservas']) ? count($driver['reservas']) : 0;

            if ($to === '' || $count === 0) {
                continue;
            }

            $subject = 'Tienes solicitudes pendientes en Aventones';

            $itemsHtml = '';
            foreach ($driver['reservas'] as $r) {
                $rid   = (int)($r['id'] ?? 0);
                $o     = (string)($r['origin'] ?? '');
                $d     = (string)($r['destination'] ?? '');
                $fecha = '';

                if (!empty($r['created_at'])) {
                    $fecha = date('d/m/Y H:i', strtotime((string)$r['created_at']));
                }

                $itemsHtml .= "<li><b>Reserva #{$rid}</b>: {$o} → {$d}" . ($fecha ? " (creada el {$fecha})" : "") . "</li>";
            }

            $messageHtml = "
                <p>Hola <b>{$name}</b>,</p>
                <p>Tienes solicitudes de viaje pendientes por revisar en el sistema Aventones.</p>
                <p>Detalles de las solicitudes:</p>
                <ul>{$itemsHtml}</ul>
                <p>Por favor, inicia sesión y gestiona tus reservas pendientes.</p>
                <br>
                <p>Saludos,<br><b>Equipo Aventones</b></p>
            ";

            $messageText = "Hola {$name},\n"
                . "Tienes solicitudes de viaje pendientes por revisar en Aventones.\n\n"
                . "Por favor inicia sesión para gestionarlas.";

            // Enviar usando PHPMailer
            try {
                $ok = $mailer->send($to, $name, $subject, $messageHtml, $messageText);

                if ($ok) {
                    CLI::write("✔ Correo enviado a {$name} <{$to}> ({$count} reservas)", 'green');
                    $totalSent++;
                } else {
                    CLI::error("✖ Error al enviar correo a {$to}");
                    CLI::write("Revisa writable/logs para ver el ErrorInfo de PHPMailer.", 'yellow');
                }
            } catch (\Throwable $e) {
                CLI::error("✖ Excepción enviando a {$to}: " . $e->getMessage());
                CLI::write("Revisa writable/logs para ver detalles.", 'yellow');
            }
        }

        CLI::write("Proceso completado. Correos enviados: {$totalSent}");
    }
}
