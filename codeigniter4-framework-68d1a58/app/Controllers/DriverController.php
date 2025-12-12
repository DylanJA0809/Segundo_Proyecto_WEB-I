<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RideModel;
use App\Models\VehicleModel;
use App\Models\BookingModel;

class DriverController extends BaseController
{
    private function requireDriver()
    {
        if (!session('logged_in') || session('user_role') !== 'driver') {
            return false;
        }
        return true;
    }

    // LISTA "My Rides"
    public function myRides()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $driverId = (int) session('user_id');

        $rideModel = new RideModel();
        $rides     = $rideModel->getRidesByDriver($driverId);

        $request = $this->request;

        $data = [
            'rides' => $rides,
            'msg'   => $request->getGet('msg') ?? '',
            'error' => $request->getGet('error') ?? '',
        ];

        return view('driver/my_rides', $data);
    }

    public function vehicles()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $driverId = (int) session('user_id');

        $vehicleModel = new VehicleModel();
        $vehicles     = $vehicleModel->getVehiclesByUser($driverId);

        $request = $this->request;

        $data = [
            'vehicles' => $vehicles,
            'driverId' => $driverId,
            'msg'      => $request->getGet('msg') ?? '',
            'error'    => $request->getGet('error') ?? '',
        ];

        return view('driver/vehicles', $data);
    }

    // ELIMINAR RIDE
    public function deleteRide($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $rideId   = (int) $id;
        $driverId = (int) session('user_id');

        if ($rideId <= 0) {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid ride.')
            );
        }

        $rideModel = new RideModel();
        $result    = $rideModel->deleteRideForDriver($driverId, $rideId);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/my-rides') .
                '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
                site_url('driver/my-rides') .
                '?msg=' . urlencode('Ride deleted successfully.')
        );
    }


    // EDITAR RIDE
    public function editRide($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $rideId   = (int) $id;
        $driverId = (int) session('user_id');

        if ($rideId <= 0) {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid ride.')
            );
        }

        $rideModel    = new RideModel();
        $vehicleModel = new VehicleModel();

        // Ride del driver
        $ride = $rideModel->getRideForDriver($driverId, $rideId);
        if (!$ride) {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Ride not found.')
            );
        }

        // Vehículos del driver
        $vehicles = $vehicleModel->getVehiclesByUser($driverId);

        // Días seleccionados
        $selectedDays = [];
        if (!empty($ride['days_set'])) {
            $parts = explode(',', strtolower($ride['days_set']));
            $selectedDays = array_map('trim', $parts);
        }

        $dayLabels = [
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
            'sat' => 'Sat',
            'sun' => 'Sun',
        ];

        $request = $this->request;

        $data = [
            'ride'         => $ride,
            'vehicles'     => $vehicles,
            'selectedDays' => $selectedDays,
            'dayLabels'    => $dayLabels,
            'msg'          => $request->getGet('msg') ?? '',
            'error'        => $request->getGet('error') ?? '',
        ];

        return view('driver/edit_ride', $data);
    }



    // ACTUALIZAR RIDE
    public function updateRide($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $driverId = (int) session('user_id');
        $rideId   = (int) $id;

        if ($rideId <= 0 || $driverId <= 0) {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid request.')
            );
        }

        $input     = $this->request->getPost();
        $rideModel = new RideModel();

        $result = $rideModel->updateRideForDriver($driverId, $rideId, $input);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/rides/edit/' . $rideId) .
                '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
            site_url('driver/my-rides') .
            '?msg=' . urlencode('Ride updated successfully')
        );
    }


    // NUEVO RIDE (FORM)  - GET
    public function newRide()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $driverId = (int) session('user_id');

        $vehicleModel = new VehicleModel();
        $vehicles     = $vehicleModel->getVehiclesByUser($driverId);

        $request = $this->request;

        $data = [
            'vehicles' => $vehicles,
            'msg'      => $request->getGet('msg') ?? '',
            'error'    => $request->getGet('error') ?? '',
        ];

        return view('driver/new_ride', $data);
    }


    // GUARDAR NUEVO RIDE - POST
    public function storeRide()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('driver/my-rides') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $driverId = (int) session('user_id');

        if ($driverId <= 0) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Invalid session.')
            );
        }

        $input     = $this->request->getPost();
        $rideModel = new RideModel();

        $result = $rideModel->createRideForDriver($driverId, $input);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/rides/new') .
                '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
            site_url('driver/my-rides') .
            '?msg=' . urlencode('Ride created successfully')
        );
    }

    public function editVehicle($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $driverId  = (int) session('user_id');
        $vehicleId = (int) $id;

        if ($vehicleId <= 0) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid vehicle')
            );
        }

        $vehicleModel = new VehicleModel();
        $vehicle      = $vehicleModel->getVehicleForUser($driverId, $vehicleId);

        if (!$vehicle) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Vehicle not found')
            );
        }

        $request = $this->request;

        $data = [
            'vehicle' => $vehicle,
            'msg'     => $request->getGet('msg') ?? '',
            'error'   => $request->getGet('error') ?? '',
        ];

        return view('driver/vehicle_edit', $data);
    }

    public function updateVehicle($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $driverId  = (int) session('user_id');
        $vehicleId = (int) $id;

        if ($driverId <= 0 || $vehicleId <= 0) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid data')
            );
        }

        $vehicleModel = new VehicleModel();
        $vehicle      = $vehicleModel->getVehicleForUser($driverId, $vehicleId);

        if (!$vehicle) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Vehicle not found')
            );
        }

        // Campos del form
        $input = [
            'plate'         => $this->request->getPost('plate'),
            'color'         => $this->request->getPost('color'),
            'make'          => $this->request->getPost('make'),
            'model'         => $this->request->getPost('model'),
            'year'          => $this->request->getPost('year'),
            'seat_capacity' => $this->request->getPost('seat_capacity'),
        ];


        // Foto: mantener la actual salvo que se suba nueva
        $photoPath = $vehicle['photo_path'];
        $file      = $this->request->getFile('photo');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedMimes = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];

            $mime = $file->getMimeType();

            if (!in_array($mime, $allowedMimes, true)) {
                return redirect()->to(
                    site_url('driver/vehicles/edit/' . $vehicleId) .
                    '?error=' . urlencode('Invalid image')
                );
            }

            if ($file->getSize() > 3 * 1024 * 1024) {
                return redirect()->to(
                    site_url('driver/vehicles/edit/' . $vehicleId) .
                    '?error=' . urlencode('Image too large')
                );
            }

            $dir = FCPATH . 'Img/vehicles';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $ext     = $file->getExtension() ?: 'jpg';
            $newName = 'veh_' . $driverId . '_' . time() . '.' . $ext;

            if (!$file->move($dir, $newName)) {
                return redirect()->to(
                    site_url('driver/vehicles/edit/' . $vehicleId) .
                    '?error=' . urlencode('Cannot save image')
                );
            }

            $photoPath = 'Img/vehicles/' . $newName;
        } elseif ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return redirect()->to(
                site_url('driver/vehicles/edit/' . $vehicleId) .
                '?error=' . urlencode('Upload error')
            );
        }

        $input['photo_path'] = $photoPath;

        // Lógica de actualización en el modelo
        $result = $vehicleModel->updateVehicleForDriver($driverId, $vehicleId, $input);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/vehicles/edit/' . $vehicleId) .
                '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
            site_url('driver/vehicles') . '?msg=' . urlencode('Vehicle updated')
        );
    }

    public function storeVehicle()
    {
        // Validar que sea driver
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        // Validar método POST
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $driverId = (int) session('user_id');
        $vehicleModel = new VehicleModel();

        // Datos del formulario
        $plate         = trim($this->request->getPost('plate'));
        $color         = trim($this->request->getPost('color'));
        $make          = trim($this->request->getPost('make'));
        $model         = trim($this->request->getPost('model'));
        $year          = (int) $this->request->getPost('year');
        $seat_capacity = (int) $this->request->getPost('seat_capacity');

        // Validaciones mínimas
        if (!$plate || !$make || !$model || $year <= 0 || $seat_capacity <= 0) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Missing or invalid fields.')
            );
        }

        // Verificar placa duplicada
        if ($vehicleModel->where('plate', $plate)->first()) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Plate already exists.')
            );
        }

        // Procesar imagen
        $photoPath = null;
        $file = $this->request->getFile('photo');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validar tipo
            $validExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = $file->getExtension();

            if (!in_array(strtolower($ext), $validExt)) {
                return redirect()->to(
                    site_url('driver/vehicles') . '?error=' . urlencode('Invalid image type.')
                );
            }

            // Crear carpeta si no existe
            $uploadDir = FCPATH . 'Img/vehicles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Guardar imagen
            $newName = 'veh_' . $driverId . '_' . time() . '.' . $ext;
            $file->move($uploadDir, $newName);

            $photoPath = 'Img/vehicles/' . $newName;
        }

        // Insertar vehículo
        $vehicleModel->insert([
            'user_id'       => $driverId,
            'plate'         => $plate,
            'color'         => $color,
            'make'          => $make,
            'model'         => $model,
            'year'          => $year,
            'seat_capacity' => $seat_capacity,
            'photo_path'    => $photoPath,
        ]);

        return redirect()->to(
            site_url('driver/vehicles') . '?msg=' . urlencode('Vehicle added successfully.')
        );
    }

    public function deleteVehicle($id)
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        // Debe ser POST
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid request method.')
            );
        }

        $driverId  = (int) session('user_id');
        $vehicleId = (int) $id;

        if ($vehicleId <= 0) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode('Invalid vehicle.')
            );
        }

        $vehicleModel = new \App\Models\VehicleModel();

        // Borrado en el modelo
        $result = $vehicleModel->deleteVehicleForDriver($driverId, $vehicleId);

        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/vehicles') . '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(
            site_url('driver/vehicles') . '?msg=' . urlencode('Vehicle deleted successfully.')
        );
    }

    public function bookings()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be a driver to access this section.')
            );
        }

        $driverId     = (int) session('user_id');
        $bookingModel = new BookingModel();

        $reservations = $bookingModel->getReservationsForDriver($driverId);

        $request = $this->request;

        $data = [
            'reservations' => $reservations,
            'msg'          => $request->getGet('msg') ?? '',
            'error'        => $request->getGet('error') ?? '',
        ];

        return view('driver/bookings', $data);
    }

    public function changeBookingStatus()
    {
        if (!$this->requireDriver()) {
            return redirect()->to(site_url('login') . '?error=' . urlencode('Access denied.'));
        }

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to(site_url('driver/bookings') . '?error=Invalid request method');
        }

        $driverId      = (int)session('user_id');
        $reservationId = (int)$this->request->getPost('reservation_id');
        $action        = trim($this->request->getPost('action'));

        if ($reservationId <= 0 || !in_array($action, ['accept','reject'], true)) {
            return redirect()->to(site_url('driver/bookings') . '?error=Invalid request');
        }

        $bookingModel = new BookingModel();

        // Obtener reserva del driver
        $resData = $bookingModel->getReservationForDriver($reservationId, $driverId);

        if (!$resData) {
            return redirect()->to(site_url('driver/bookings') . '?error=Reservation not found');
        }

        if ($resData['status'] !== 'pending') {
            return redirect()->to(site_url('driver/bookings') . '?error=Only pending reservations can be changed');
        }

        // Acción
        if ($action === 'accept') {
            $result = $bookingModel->acceptReservation($resData);
            if (!$result['ok']) {
                return redirect()->to(
                    site_url('driver/bookings') . '?error=' . urlencode($result['error'])
                );
            }
            return redirect()->to(site_url('driver/bookings') . '?msg=Reservation accepted');
        }

        // Reject
        $result = $bookingModel->rejectReservation($reservationId);
        if (!$result['ok']) {
            return redirect()->to(
                site_url('driver/bookings') . '?error=' . urlencode($result['error'])
            );
        }

        return redirect()->to(site_url('driver/bookings') . '?msg=Reservation rejected');
    }


}
