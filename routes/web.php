<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// route to show the login form
//Route::get('/', array('uses' => 'App\Http\Controllers\HomeController@showLogin'));

// Route::get('/', function () {
//     return view('admin');
// });
// Route::get('/manager', function () {
//     return view('manager');
// });
Route::get('/', function () {
    return view('app');
});