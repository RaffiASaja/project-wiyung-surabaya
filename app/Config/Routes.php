<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/test', 'Test::index');
$routes->get('/antrian', 'Antrian::index');
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::prosesLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('master-data/belum-dipanggil', 'Dashboard::dataBelumDipanggil');
$routes->get('master-data/sudah-dipanggil', 'Dashboard::dataSudahDipanggil');
$routes->get('antrian/panggil/(:num)', 'Antrian::panggil/$1');
$routes->get('/display', 'Display::index');
$routes->get('/display/data', 'Display::data');
$routes->get('antrian/panggil-ulang/(:num)', 'Antrian::panggilUlang/$1');
$routes->get('antrian/selesai/(:num)', 'Antrian::selesai/$1');


