<?php

namespace App\Controllers;

use App\Models\RideModel;

class RidesController extends BaseController
{
    // Vista pública
    public function publicSearch()
    {
        $data['title'] = 'Search Rides - Aventones';
        return view('rides/public_search', $data);
    }

    // AJAX: búsqueda de rides (GET, como en tu proyecto original)
    public function searchAjax()
    {
        $request = $this->request;

        // Parámetros por GET (como antes)
        $from  = trim($request->getGet('from') ?? '');
        $to    = trim($request->getGet('to')   ?? '');
        $daysQ = trim($request->getGet('days') ?? ''); // "mon,wed,fri"

        // Normalizar días permitidos
        $allowedDays = ['mon','tue','wed','thu','fri','sat','sun'];

        $days = array_values(array_filter(array_map(
            fn($d) => strtolower(trim($d)),
            explode(',', $daysQ)
        ), fn($d) => in_array($d, $allowedDays, true)));

        $rideModel = new RideModel();
        $rows = $rideModel->searchPublicRides($from, $to, $days);

        // Formato igual al del proyecto original
        $data = array_map(function($r) {
            return [
                'id'        => (int)$r['id'],
                'from'      => $r['origin'],
                'to'        => $r['destination'],
                'time'      => isset($r['departure_time']) ? substr($r['departure_time'], 0, 5) : null,
                'seats'     => (int)$r['available_seats'],
                'fee'       => isset($r['seat_price']) ? (float)$r['seat_price'] : null,
                'userEmail' => $r['driver_email'],
                'vehicle'   => [
                    'make'  => $r['vehicle_make'],
                    'model' => $r['vehicle_model'],
                    'year'  => isset($r['vehicle_year']) ? (int)$r['vehicle_year'] : null,
                ],
                'days'      => array_values(array_filter(array_map('trim', explode(',', (string)$r['days_set']))))
            ];
        }, $rows);

        return $this->response->setJSON([
            'ok'    => true,
            'rides' => $data,
        ]);
    }
}
