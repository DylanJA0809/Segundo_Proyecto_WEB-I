<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table      = 'vehicles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'plate',
        'color',
        'make',
        'model',
        'year',
        'seat_capacity',
        'photo_path',
    ];

    //  LISTA POR CONDUCTOR
    public function getVehiclesByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    //  OBTENER UN VEHÍCULO DEL DRIVER
    public function getVehicleForUser(int $userId, int $vehicleId): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('id', $vehicleId)
                    ->first();
    }


    //  ¿EXISTE PLACA?
    public function plateExists(string $plate, ?int $excludeId = null): bool
    {
        $builder = $this->where('plate', $plate);
        if ($excludeId !== null) {
            $builder->where('id <>', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }


    //  CREAR VEHÍCULO PARA DRIVER
    public function createVehicleForDriver(int $driverId, array $data): array
    {
        $plate        = trim($data['plate'] ?? '');
        $color        = trim($data['color'] ?? '');
        $make         = trim($data['make'] ?? '');
        $model        = trim($data['model'] ?? '');
        $year         = (int)($data['year'] ?? 0);
        $seatCapacity = (int)($data['seat_capacity'] ?? 0);
        $photoPath    = $data['photo_path'] ?? null;

        if ($driverId <= 0 ||
            $plate === '' ||
            $make === '' ||
            $model === '' ||
            $year <= 0 ||
            $seatCapacity <= 0
        ) {
            return ['ok' => false, 'error' => 'Missing or invalid fields'];
        }

        if ($year < 1950 || $year > 2100) {
            return ['ok' => false, 'error' => 'Year out of range'];
        }
        if ($seatCapacity < 1 || $seatCapacity > 9) {
            return ['ok' => false, 'error' => 'Seat capacity must be between 1 and 9'];
        }

        if ($this->plateExists($plate)) {
            return ['ok' => false, 'error' => 'Plate already exists'];
        }

        $insertData = [
            'user_id'       => $driverId,
            'plate'         => $plate,
            'color'         => $color !== '' ? $color : null,
            'make'          => $make,
            'model'         => $model,
            'year'          => $year,
            'seat_capacity' => $seatCapacity,
            'photo_path'    => $photoPath,
        ];

        $ok = $this->insert($insertData);

        if ($ok === false) {
            return ['ok' => false, 'error' => 'DB error: could not create vehicle'];
        }

        return ['ok' => true];
    }


    //  ACTUALIZAR VEHÍCULO
    public function updateVehicleForDriver(int $driverId, int $vehicleId, array $data): array
    {
        if ($driverId <= 0 || $vehicleId <= 0) {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        $vehicle = $this->getVehicleForUser($driverId, $vehicleId);
        if (!$vehicle) {
            return ['ok' => false, 'error' => 'Vehicle not found'];
        }

        $plate        = trim($data['plate'] ?? '');
        $color        = trim($data['color'] ?? '');
        $make         = trim($data['make'] ?? '');
        $model        = trim($data['model'] ?? '');
        $year         = (int)($data['year'] ?? 0);
        $seatCapacity = (int)($data['seat_capacity'] ?? 0);
        $photoPath    = $data['photo_path'] ?? $vehicle['photo_path'];

        if ($plate === '' || $make === '' || $model === '' || $year <= 0 || $seatCapacity <= 0) {
            return ['ok' => false, 'error' => 'Invalid data'];
        }

        if ($year < 1950 || $year > 2100) {
            return ['ok' => false, 'error' => 'Year out of range'];
        }
        if ($seatCapacity < 1 || $seatCapacity > 9) {
            return ['ok' => false, 'error' => 'Seat capacity must be between 1 and 9'];
        }

        // validar placa única excluyendo el mismo vehículo
        if ($this->plateExists($plate, $vehicleId)) {
            return ['ok' => false, 'error' => 'Plate already exists'];
        }

        $updateData = [
            'plate'         => $plate,
            'color'         => $color !== '' ? $color : null,
            'make'          => $make,
            'model'         => $model,
            'year'          => $year,
            'seat_capacity' => $seatCapacity,
            'photo_path'    => $photoPath,
        ];

        $ok = $this->where('id', $vehicleId)
                   ->where('user_id', $driverId)
                   ->set($updateData)
                   ->update();

        if ($ok === false) {
            return ['ok' => false, 'error' => 'DB error: could not update vehicle'];
        }

        return ['ok' => true];
    }


    //  BORRAR VEHÍCULO
    public function deleteVehicleForDriver(int $driverId, int $vehicleId): array
    {
        if ($driverId <= 0 || $vehicleId <= 0) {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        $vehicle = $this->where('id', $vehicleId)
                        ->where('user_id', $driverId)
                        ->first();

        if (!$vehicle) {
            return ['ok' => false, 'error' => 'Vehicle not found'];
        }

        $builder = $this->db->table('rides')
                            ->selectCount('id', 'c')
                            ->where('vehicle_id', $vehicleId)
                            ->where('status', 'active');

        $row   = $builder->get()->getRowArray();
        $count = (int)($row['c'] ?? 0);

        if ($count > 0) {
            return ['ok' => false, 'error' => 'Vehicle in use by active rides'];
        }

        $deleted = $this->where('id', $vehicleId)
                        ->where('user_id', $driverId)
                        ->delete();

        if ($deleted === false) {
            return ['ok' => false, 'error' => 'DB error: could not delete vehicle'];
        }

        return ['ok' => true];
    }
}
