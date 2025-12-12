<?php

namespace App\Models;

use CodeIgniter\Model;

class RideModel extends Model
{
    protected $table      = 'rides';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // AllowedFields porque actualizamos/insertamos
    protected $allowedFields = [
        'driver_id',
        'vehicle_id',
        'name',
        'origin',
        'destination',
        'departure_time',
        'days_set',
        'seat_price',
        'total_seats',
        'available_seats',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Busca rides públicos con filtros (origen, destino, días).
     * Devuelve un array 
     */
    public function searchPublicRides(?string $from, ?string $to, array $days): array
    {
        $builder = $this->db->table('rides r');

        $builder->select("
            r.id,
            r.origin,
            r.destination,
            r.departure_time,
            r.seat_price,
            r.available_seats,
            r.days_set,
            u.email AS driver_email,
            v.make  AS vehicle_make,
            v.model AS vehicle_model,
            v.year  AS vehicle_year
        ");

        $builder->join('users u', 'u.id = r.driver_id');
        $builder->join('vehicles v', 'v.id = r.vehicle_id', 'left');

        // Condiciones base
        $builder->where('r.status', 'active');
        $builder->where('r.available_seats >', 0);

        if ($from !== null && $from !== '') {
            $builder->where('r.origin', $from);
        }

        if ($to !== null && $to !== '') {
            $builder->where('r.destination', $to);
        }

        if (!empty($days)) {
            $builder->groupStart();
            foreach ($days as $day) {
                $day = strtolower(trim($day));
                if ($day === '') {
                    continue;
                }

                // escape devuelve el valor ya con comillas
                $escaped = $this->db->escape($day); // ej: 'mon'
                $builder->orWhere("FIND_IN_SET($escaped, r.days_set) >", 0, false);
            }
            $builder->groupEnd();
        }

        $builder->orderBy('r.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Lista de rides de un driver (para "My Rides"), con join a vehicles.
     */
    public function getRidesByDriver(int $driverId): array
    {
        return $this->select('
                rides.id,
                rides.name,
                rides.origin,
                rides.destination,
                rides.departure_time,
                rides.days_set,
                rides.seat_price,
                rides.total_seats,
                rides.available_seats,
                vehicles.make,
                vehicles.model,
                vehicles.year
            ')
            ->join('vehicles', 'vehicles.id = rides.vehicle_id AND vehicles.user_id = rides.driver_id', 'left')
            ->where('rides.driver_id', $driverId)
            ->orderBy('rides.id', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene un ride de un driver específico para edición.
     */
    public function getRideForDriver(int $driverId, int $rideId): ?array
    {
        return $this->where('id', $rideId)
            ->where('driver_id', $driverId)
            ->first();
    }

    /**
     * Actualiza un ride de un driver:
     * - valida datos
     * - normaliza días
     * - valida que el vehículo pertenezca al driver
     * - recalcula available_seats si es que se cambia total_seats
     */
    public function updateRideForDriver(int $driverId, int $rideId, array $input): array
    {
        $db = $this->db;

        // Verificar ride del driver
        $ride = $this->select('id, total_seats, available_seats, driver_id')
            ->where('id', $rideId)
            ->where('driver_id', $driverId)
            ->first();

        if (!$ride) {
            return ['ok' => false, 'error' => 'Ride not found.'];
        }

        // Parsear input
        $name          = trim($input['name'] ?? '');
        $vehicleId     = (int)($input['vehicle_id'] ?? 0);
        $origin        = trim($input['origin'] ?? '');
        $destination   = trim($input['destination'] ?? '');
        $days          = $input['days'] ?? [];
        $departureTime = trim($input['departure_time'] ?? '');
        $seatPriceRaw  = $input['seat_price'] ?? '';
        $totalSeats    = (int)($input['total_seats'] ?? 0);

        if (
            $vehicleId <= 0 ||
            $origin === '' ||
            $destination === '' ||
            $departureTime === '' ||
            $seatPriceRaw === '' ||
            $totalSeats <= 0
        ) {
            return ['ok' => false, 'error' => 'Missing required fields.'];
        }

        // Normalizar días
        $allowedDays = ['mon','tue','wed','thu','fri','sat','sun'];
        $daysNorm = [];
        if (is_array($days)) {
            foreach ($days as $d) {
                $d = strtolower(trim($d));
                if (in_array($d, $allowedDays, true)) {
                    $daysNorm[] = $d;
                }
            }
        }
        if (empty($daysNorm)) {
            return ['ok' => false, 'error' => 'Select at least one day.'];
        }
        $daysSet = implode(',', $daysNorm);

        // Seat price
        $seatPrice = (float)$seatPriceRaw;
        if (!is_numeric($seatPriceRaw) || $seatPrice < 0) {
            return ['ok' => false, 'error' => 'Seat price must be 0 or greater.'];
        }

        // Validar vehículo del driver
        $vehicle = $db->table('vehicles')
            ->select('id')
            ->where('id', $vehicleId)
            ->where('user_id', $driverId)
            ->get()
            ->getRowArray();

        if (!$vehicle) {
            return ['ok' => false, 'error' => 'Invalid vehicle.'];
        }

        // Recalcular available_seats
        $oldTotal = (int)$ride['total_seats'];
        $oldAvail = (int)$ride['available_seats'];
        $delta    = $totalSeats - $oldTotal;
        $newAvail = $oldAvail + $delta;

        if ($newAvail < 0) {
            $newAvail = 0;
        }
        if ($newAvail > $totalSeats) {
            $newAvail = $totalSeats;
        }

        // Update
        $dataUpdate = [
            'vehicle_id'      => $vehicleId,
            'name'            => ($name !== '') ? $name : null,
            'origin'          => $origin,
            'destination'     => $destination,
            'departure_time'  => $departureTime,
            'days_set'        => $daysSet,
            'seat_price'      => $seatPrice,
            'total_seats'     => $totalSeats,
            'available_seats' => $newAvail,
        ];

        $ok = $this->where('id', $rideId)
            ->where('driver_id', $driverId)
            ->set($dataUpdate)
            ->update();

        if (!$ok) {
            return ['ok' => false, 'error' => 'DB error: could not update the ride.'];
        }

        return ['ok' => true];
    }

    /**
     * Elimina un ride de un driver (validando que le pertenezca).
     */
    public function deleteRideForDriver(int $driverId, int $rideId): array
    {
        $ride = $this->select('id, driver_id')
            ->where('id', $rideId)
            ->where('driver_id', $driverId)
            ->first();

        if (!$ride) {
            return ['ok' => false, 'error' => 'Ride not found or you cannot delete this ride.'];
        }

        $ok = $this->where('id', $rideId)
            ->where('driver_id', $driverId)
            ->delete();

        if (!$ok) {
            return ['ok' => false, 'error' => 'Could not delete the ride.'];
        }

        return ['ok' => true];
    }

    public function createRideForDriver(int $driverId, array $input): array
    {
        $db = $this->db;
        // Parsear datos de entrada
        $name          = trim($input['name'] ?? '');
        $vehicleId     = isset($input['vehicle_id']) ? (int)$input['vehicle_id'] : 0;
        $origin        = trim($input['origin'] ?? '');
        $destination   = trim($input['destination'] ?? '');
        $departureTime = trim($input['departure_time'] ?? '');
        $seatPriceRaw  = $input['seat_price'] ?? null;
        $totalSeats    = isset($input['total_seats']) ? (int)$input['total_seats'] : 0;
        $days          = $input['days'] ?? []; // ['mon','tue',...]

        // Validaciones básicas
        if (
            !$driverId ||
            !$vehicleId ||
            $origin === '' ||
            $destination === '' ||
            $departureTime === '' ||
            $seatPriceRaw === null ||
            $totalSeats === 0
        ) {
            return ['ok' => false, 'error' => 'Missing required fields'];
        }

        // Normalizar días
        $allowed = ['mon','tue','wed','thu','fri','sat','sun'];
        if (!is_array($days)) {
            $days = [];
        }
        $days = array_values(array_intersect($days, $allowed));
        $daysSet = $days ? implode(',', $days) : null;

        if ($daysSet === null) {
            return ['ok' => false, 'error' => 'Select at least one day'];
        }

        // Validar vehículo del driver
        $vehicle = $db->table('vehicles')
            ->select('id')
            ->where('id', $vehicleId)
            ->where('user_id', $driverId)
            ->get()
            ->getRowArray();

        if (!$vehicle) {
            return ['ok' => false, 'error' => 'Invalid vehicle'];
        }

        // Normalizar hora (HH:MM -> HH:MM:00)
        if ($departureTime && strlen($departureTime) === 5) {
            // ej: "07:30" -> "07:30:00"
            $departureTime .= ':00';
        }

        // Validar y castear seat_price
        if (!is_numeric($seatPriceRaw)) {
            return ['ok' => false, 'error' => 'Seat price must be numeric'];
        }

        $seatPrice = (float)$seatPriceRaw;

        if ($seatPrice < 0) {
            return ['ok' => false, 'error' => 'Seat price must be 0 or greater'];
        }

        if ($totalSeats < 1) {
            return ['ok' => false, 'error' => 'Total seats must be at least 1'];
        }

        // available_seats al crear = total_seats
        $availableSeats = $totalSeats;

        // Insert
        $dataInsert = [
            'driver_id'       => $driverId,
            'vehicle_id'      => $vehicleId,
            'name'            => ($name !== '') ? $name : null,
            'origin'          => $origin,
            'destination'     => $destination,
            'departure_time'  => $departureTime,
            'days_set'        => $daysSet,
            'seat_price'      => $seatPrice,
            'total_seats'     => $totalSeats,
            'available_seats' => $availableSeats,
            'status'          => 'active',
        ];

        $ok = $this->insert($dataInsert);

        if ($ok === false) {
            return ['ok' => false, 'error' => 'DB error: could not create the ride.'];
        }

        return ['ok' => true];
    }

    public function getPublicRideDetails(int $rideId): ?array
    {
        $builder = $this->db->table('rides r');

        $builder->select("
            r.id,
            r.origin,
            r.destination,
            r.departure_time,
            r.days_set,
            r.seat_price,
            r.available_seats,
            r.status,
            u.email AS driver_email,
            v.make  AS vehicle_make,
            v.model AS vehicle_model,
            v.year  AS vehicle_year
        ");

        $builder->join('users u', 'u.id = r.driver_id');
        $builder->join('vehicles v', 'v.id = r.vehicle_id', 'left');

        $builder->where('r.id', $rideId);
        $row = $builder->get()->getRowArray();

        return $row ?: null;
    }

}
