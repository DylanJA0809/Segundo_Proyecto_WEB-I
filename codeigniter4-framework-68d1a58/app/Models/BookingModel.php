<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table      = 'reservations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ride_id',
        'passenger_id',
        'status',
        'seats',
        'created_at',
        'updated_at',
    ];

    /**
     * Trae todas las reservas de los rides de un driver.
     */
    public function getReservationsForDriver(int $driverId): array
    {
        $builder = $this->db->table('reservations rsv');

        $builder->select("
            rsv.id        AS res_id,
            rsv.status    AS res_status,
            rsv.seats     AS res_seats,
            rsv.created_at,
            rd.id         AS ride_id,
            rd.name       AS ride_name,
            rd.origin,
            rd.destination,
            rd.departure_time,
            rd.days_set,
            rd.total_seats,
            rd.available_seats,
            v.make,
            v.model,
            v.year,
            u.id          AS passenger_id,
            u.first_name,
            u.last_name,
            u.email
        ");

        $builder->join('rides rd', 'rd.id = rsv.ride_id');
        $builder->join('users u', 'u.id = rsv.passenger_id');
        $builder->join('vehicles v', 'v.id = rd.vehicle_id', 'left');

        $builder->where('rd.driver_id', $driverId);

        $builder->orderBy('rsv.created_at', 'DESC');
        $builder->orderBy('rsv.id', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Trae todas las reservas de los rides de un driver.
     */
    public function getReservationForDriver(int $reservationId, int $driverId): ?array
    {
        $builder = $this->db->table('reservations rsv');
        $builder->select("
            rsv.id,
            rsv.status,
            rsv.seats,
            rsv.ride_id,
            rd.driver_id,
            rd.available_seats
        ");
        $builder->join('rides rd', 'rd.id = rsv.ride_id');
        $builder->where('rsv.id', $reservationId);
        $builder->where('rd.driver_id', $driverId);
        $builder->limit(1);

        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    public function acceptReservation(array $resData): array
    {
        $db =& $this->db;

        $reservationId = (int)$resData['id'];
        $rideId        = (int)$resData['ride_id'];
        $seats         = (int)$resData['seats'];

        if ($resData['available_seats'] < $seats) {
            return ['ok' => false, 'error' => 'Not enough available seats'];
        }

        $db->transStart();

        // Actualizar reserva
        $db->table('reservations')
        ->where('id', $reservationId)
        ->update(['status' => 'accepted', 'updated_at' => date('Y-m-d H:i:s')]);

        // Actualizar asientos del ride
        $db->table('rides')
        ->set('available_seats', "available_seats - {$seats}", false)
        ->where('id', $rideId)
        ->where('available_seats >=', $seats)
        ->update();

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'error' => 'Database error'];
        }

        $db->transCommit();
        return ['ok' => true];
    }

    public function rejectReservation(int $reservationId): array
    {
        $builder = $this->db->table('reservations');

        $ok = $builder->where('id', $reservationId)
                    ->update(['status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')]);

        if (!$ok) {
            return ['ok' => false, 'error' => 'Database error'];
        }

        return ['ok' => true];
    }

    // Passenger: listar reservas
    public function getReservationsForPassenger(int $passengerId): array
    {
        return $this->db->table('reservations rsv')
            ->select("
                rsv.id        AS res_id,
                rsv.status    AS res_status,
                rsv.seats     AS res_seats,
                rsv.created_at,

                rd.id         AS ride_id,
                rd.name       AS ride_name,
                rd.origin,
                rd.destination,
                rd.departure_time,
                rd.days_set,
                rd.total_seats,
                rd.available_seats,

                v.make,
                v.model,
                v.year,

                du.id         AS driver_id,
                du.first_name AS driver_first_name,
                du.last_name  AS driver_last_name,
                du.email      AS driver_email
            ")
            ->join('rides rd', 'rd.id = rsv.ride_id')
            ->join('users du', 'du.id = rd.driver_id')
            ->join('vehicles v', 'v.id = rd.vehicle_id', 'left')
            ->where('rsv.passenger_id', $passengerId)
            ->orderBy('rsv.created_at', 'DESC')
            ->orderBy('rsv.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Passenger: cancelar pending/accepted
    // - si estaba accepted => devolver asientos
    public function cancelReservationForPassenger(int $passengerId, int $reservationId): array
{
    $db = $this->db;

    // Traer reserva + ride
    $row = $db->table('reservations rsv')
        ->select('rsv.id, rsv.ride_id, rsv.passenger_id, rsv.seats, rsv.status AS res_status, rd.total_seats, rd.available_seats')
        ->join('rides rd', 'rd.id = rsv.ride_id')
        ->where('rsv.id', $reservationId)
        ->limit(1)
        ->get()
        ->getRowArray();

    if (!$row) {
        return ['ok' => false, 'error' => 'Reservation not found.'];
    }

    if ((int)$row['passenger_id'] !== $passengerId) {
        return ['ok' => false, 'error' => 'You cannot cancel a reservation that is not yours.'];
    }

    $currentStatus = (string)$row['res_status'];
    if (in_array($currentStatus, ['rejected', 'cancelled', 'completed'], true)) {
        return ['ok' => false, 'error' => 'Reservation cannot be cancelled.'];
    }

    $seats  = (int)$row['seats'];
    $rideId = (int)$row['ride_id'];

    // Transacción
    $db->transStart();

    // Cambiar a cancelled
    $db->table('reservations')
        ->where('id', $reservationId)
        ->where('passenger_id', $passengerId)
        ->update([
            'status'     => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

    // Si estaba accepted, devolver asientos
    if ($currentStatus === 'accepted') {
        $db->table('rides')
            ->where('id', $rideId)
            ->set('available_seats', "LEAST(total_seats, available_seats + {$seats})", false)
            ->update();
    }

    $db->transComplete();

    if ($db->transStatus() === false) {
        return ['ok' => false, 'error' => 'DB error: could not cancel reservation.'];
    }

    return ['ok' => true];
}

}
