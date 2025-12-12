<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationModel extends Model
{
    protected $table      = 'reservations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ride_id',
        'passenger_id',
        'seats',
        'status',
        'created_at',
        'updated_at',
    ];

    public function createReservationForPassenger(int $passengerId, int $rideId, int $seats): array
    {
        if ($rideId <= 0 || $passengerId <= 0 || $seats <= 0) {
            return ['ok' => false, 'error' => 'Invalid parameters.'];
        }

        $db = $this->db;

        // verificar ride
        $ride = $db->table('rides')
            ->select('id, driver_id, status')
            ->where('id', $rideId)
            ->get()
            ->getRowArray();

        if (!$ride) {
            return ['ok' => false, 'error' => 'Ride not found.'];
        }
        if (($ride['status'] ?? '') !== 'active') {
            return ['ok' => false, 'error' => 'Ride is not active.'];
        }
        if ((int)$ride['driver_id'] === $passengerId) {
            return ['ok' => false, 'error' => 'You cannot request your own ride.'];
        }

        // evitar duplicados pending/accepted
        $dup = $db->table('reservations')
            ->select('id')
            ->where('ride_id', $rideId)
            ->where('passenger_id', $passengerId)
            ->whereIn('status', ['pending', 'accepted'])
            ->get()
            ->getRowArray();

        if ($dup) {
            return ['ok' => false, 'error' => 'You already have a request for this ride.'];
        }

        // insertar
        $id = $this->insert([
            'ride_id'       => $rideId,
            'passenger_id'  => $passengerId,
            'seats'         => $seats,
            'status'        => 'pending',
        ], true);

        if (!$id) {
            return ['ok' => false, 'error' => 'DB error: could not create reservation.'];
        }

        return ['ok' => true, 'reservation_id' => (int)$id];
    }

    public function getPendingReservationsGroupedByDriver(int $minutes): array
    {
        $sql = "
            SELECT
                r.id            AS reservation_id,
                r.ride_id,
                r.created_at,
                rd.driver_id,
                u.email         AS driver_email,
                u.first_name    AS driver_name,
                rd.origin,
                rd.destination
            FROM reservations r
            JOIN rides rd ON rd.id = r.ride_id
            JOIN users u  ON u.id = rd.driver_id
            WHERE r.status = 'pending'
              AND r.created_at <= (NOW() - INTERVAL ? MINUTE)
            ORDER BY rd.driver_id, r.created_at ASC
        ";

        $rows = $this->db->query($sql, [$minutes])->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $drivers = [];
        foreach ($rows as $row) {
            $driverId = (int) $row['driver_id'];

            if (!isset($drivers[$driverId])) {
                $drivers[$driverId] = [
                    'email'    => $row['driver_email'],
                    'name'     => $row['driver_name'],
                    'reservas' => [],
                ];
            }

            $drivers[$driverId]['reservas'][] = [
                'id'         => (int) $row['reservation_id'],
                'ride_id'    => (int) $row['ride_id'],
                'origin'     => $row['origin'],
                'destination'=> $row['destination'],
                'created_at' => $row['created_at'],
            ];
        }

        return array_values($drivers);
    }
}
