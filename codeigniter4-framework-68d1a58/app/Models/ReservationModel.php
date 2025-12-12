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
}
