<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('master-data/belum-dipanggil', 'Dashboard::dataBelumDipanggil');
$routes->get('master-data/sudah-dipanggil', 'Dashboard::dataSudahDipanggil');
