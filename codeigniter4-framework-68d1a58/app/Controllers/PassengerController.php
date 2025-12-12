<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RideModel;
use App\Models\ReservationModel;
use App\Models\BookingModel;

class PassengerController extends BaseController
{
    private function requirePassenger(): bool
    {
        if (!session('logged_in') || session('user_role') !== 'passenger') {
            return false;
        }
        return true;
    }

    public function searchRides()
    {
        if (!$this->requirePassenger()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a passenger to access this section.')
            );
        }

        return view('passenger/search_rides');
    }

    // API JSON para el JS
    public function apiSearchRides()
    {
        if (!$this->requirePassenger()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $from = trim((string)$this->request->getGet('from'));
        $to   = trim((string)$this->request->getGet('to'));
        $daysQ = trim((string)$this->request->getGet('days')); // "mon,wed,fri"

        $allowedDays = ['mon','tue','wed','thu','fri','sat','sun'];

        $days = [];
        if ($daysQ !== '') {
            $parts = explode(',', $daysQ);
            foreach ($parts as $d) {
                $d = strtolower(trim($d));
                if (in_array($d, $allowedDays, true)) {
                    $days[] = $d;
                }
            }
        }

        $rideModel = new RideModel();
        $rows = $rideModel->searchPublicRides(
            $from !== '' ? $from : null,
            $to   !== '' ? $to   : null,
            $days
        );

        // Adaptar al formato que espera JS
        $rides = array_map(function(array $r) {
            $daysArr = [];
            if (!empty($r['days_set'])) {
                $parts = explode(',', (string)$r['days_set']);
                foreach ($parts as $d) {
                    $d = trim($d);
                    if ($d !== '') {
                        $daysArr[] = $d;
                    }
                }
            }

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
                'days'      => $daysArr,
            ];
        }, $rows);

        return $this->response->setJSON([
            'ok'    => true,
            'rides' => $rides,
        ]);
    }

    public function rideDetails($id)
    {
        if (!$this->requirePassenger()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a passenger to access this section.')
            );
        }

        $rideId = (int)$id;
        if ($rideId <= 0) {
            return redirect()->to(site_url('passenger/search-rides') . '?error=' . urlencode('Invalid ride.'));
        }

        return view('passenger/ride_details', ['rideId' => $rideId]);
    }

    public function apiGetRide($id)
    {
        if (!$this->requirePassenger()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $rideId = (int)$id;
        if ($rideId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => 'Invalid id']);
        }

        $rideModel = new RideModel();
        $row = $rideModel->getPublicRideDetails($rideId);

        if (!$row) {
            return $this->response->setJSON(['ok' => true, 'ride' => null]);
        }

        $daysArr = [];
        if (!empty($row['days_set'])) {
            $parts = explode(',', (string)$row['days_set']);
            foreach ($parts as $d) {
                $d = trim($d);
                if ($d !== '') $daysArr[] = $d;
            }
        }

        $ride = [
            'id'        => (int)$row['id'],
            'from'      => $row['origin'],
            'to'        => $row['destination'],
            'time'      => !empty($row['departure_time']) ? substr((string)$row['departure_time'], 0, 5) : null,
            'days'      => $daysArr,
            'seats'     => (int)$row['available_seats'],
            'fee'       => $row['seat_price'] !== null ? (float)$row['seat_price'] : null,
            'userEmail' => $row['driver_email'],
            'vehicle'   => [
                'make'  => $row['vehicle_make'],
                'model' => $row['vehicle_model'],
                'year'  => $row['vehicle_year'] !== null ? (int)$row['vehicle_year'] : null,
            ],
        ];

        return $this->response->setJSON(['ok' => true, 'ride' => $ride]);
    }

    public function apiCreateReservation()
    {
        if (!$this->requirePassenger()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $rideId  = (int)($payload['ride_id'] ?? 0);
        $seats   = (int)($payload['seats'] ?? 1);
        $seats   = max(1, $seats);

        $passengerId = (int) session('user_id');

        $reservationModel = new ReservationModel();
        $result = $reservationModel->createReservationForPassenger($passengerId, $rideId, $seats);

        if (!$result['ok']) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => $result['error']]);
        }

        return $this->response->setJSON($result);
    }

    public function bookings()
    {
        if (!$this->requirePassenger()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a passenger to access this section.')
            );
        }

        $passengerId = (int) session('user_id');

        $bookingModel = new BookingModel();
        $rows = $bookingModel->getReservationsForPassenger($passengerId);

        return view('passenger/bookings', [
            'rows'  => $rows,
            'msg'   => $this->request->getGet('msg') ?? '',
            'error' => $this->request->getGet('error') ?? '',
        ]);
    }

    public function cancelBooking($id)
    {
        if (!$this->requirePassenger()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a passenger to access this section.')
            );
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('passenger/bookings') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $reservationId = (int) $id;
        $passengerId   = (int) session('user_id');

        if ($reservationId <= 0) {
            return redirect()->to(
                site_url('passenger/bookings') . '?error=' . urlencode('Invalid reservation.')
            );
        }

        $bookingModel = new BookingModel();
        $result = $bookingModel->cancelReservationForPassenger($passengerId, $reservationId);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('passenger/bookings') . '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
            site_url('passenger/bookings') . '?msg=' . urlencode('Reservation cancelled.')
        );
    }

}
