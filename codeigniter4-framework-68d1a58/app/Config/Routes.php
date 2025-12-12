<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('ControllerWorkshop6');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'AuthController::login');

// Login
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::doLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('activate', 'AuthController::activate');

// Passwordless Login
$routes->post('passwordless/send-link', 'AuthController::sendPasswordlessLink');
$routes->get('passwordless/login/(:segment)', 'AuthController::loginWithToken/$1');

// Registro pasajero
$routes->get('register', 'AuthController::registerUser');
$routes->post('register', 'AuthController::doRegisterUser');

// Registro driver
$routes->get('register/driver', 'AuthController::registerDriver');
$routes->post('register/driver', 'AuthController::doRegisterDriver');

// Vista de búsqueda pública
$routes->get('public-rides', 'RidesController::publicSearch');
// Endpoint AJAX 
$routes->get('public-rides/search', 'RidesController::searchAjax');

// Rutas para admin
$routes->get('admin/users', 'AdminController::users');
$routes->post('admin/users/create', 'AdminController::createAdmin');
$routes->post('admin/users/change-status/(:num)', 'AdminController::changeUserStatus/$1');
$routes->get('admin/search-report', 'AdminController::searchReport');

// Rutas del driver
$routes->get('driver/my-rides', 'DriverController::myRides');
$routes->post('driver/rides/delete/(:num)', 'DriverController::deleteRide/$1');
$routes->get('driver/rides/edit/(:num)', 'DriverController::editRide/$1');
$routes->post('driver/rides/update/(:num)', 'DriverController::updateRide/$1');
$routes->get('driver/rides/new-ride', 'DriverController::newRide');
$routes->post('driver/rides/store', 'DriverController::storeRide');
// Rutas de vehículos del driver
$routes->get('driver/vehicles', 'DriverController::vehicles');
$routes->post('driver/vehicles/store', 'DriverController::storeVehicle');
$routes->get('driver/vehicles/edit/(:num)', 'DriverController::editVehicle/$1');
$routes->post('driver/vehicles/update/(:num)', 'DriverController::updateVehicle/$1');
$routes->post('driver/vehicles/delete/(:num)', 'DriverController::deleteVehicle/$1');
// Rutas de bookings del driver
$routes->get('driver/bookings', 'DriverController::bookings');
$routes->post('driver/bookings/change-status', 'DriverController::changeBookingStatus');

// Rutas del pasajero
$routes->get('passenger/search-rides', 'PassengerController::searchRides');
// API JSON para el JS
$routes->get('passenger/api/search-rides', 'PassengerController::apiSearchRides');
// Ride details (Passenger)
$routes->get('passenger/ride-details/(:num)', 'PassengerController::rideDetails/$1');
// API JSON
$routes->get('passenger/api/ride/(:num)', 'PassengerController::apiGetRide/$1');
$routes->post('passenger/api/reservations', 'PassengerController::apiCreateReservation');
// Passenger bookings
$routes->get('passenger/bookings', 'PassengerController::bookings');
$routes->post('passenger/bookings/cancel/(:num)', 'PassengerController::cancelBooking/$1');

// Profile (driver y passenger)
$routes->get('profile/configuration', 'ProfileController::configuration');
$routes->get('profile/edit', 'ProfileController::edit');
$routes->post('profile/update', 'ProfileController::update');
// API Bio (AJAX)
$routes->get('profile/api/bio', 'ProfileController::apiGetBio');
$routes->post('profile/api/bio', 'ProfileController::apiUpdateBio');

