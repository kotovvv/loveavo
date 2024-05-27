<?php

use App\Http\Controllers\API\v1\loginController;
use App\Http\Controllers\API\v1\ProvidersController;
use App\Http\Controllers\API\v1\UsersController;
use App\Http\Controllers\API\v1\LidsController;
use App\Http\Controllers\API\v1\LogsController;
use App\Http\Controllers\API\v1\StatusesController;
use App\Http\Controllers\API\v1\ImportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [loginController::class, 'login']);

Route::resource('provider', ProvidersController::class);
Route::resource('statuses', StatusesController::class);
Route::resource('users', UsersController::class);
Route::resource('lids', LidsController::class);
Route::resource('imports/{from}/{to}/', ImportsController::class);

Route::get('statusall', [StatusesController::class, 'getall'])->name('stasusall');
Route::get('providerall', [ProvidersController::class, 'getall'])->name('providerall');
Route::post('status_provider', [ProvidersController::class, 'status_provider'])->name('status_provider');
Route::post('status_users', [UsersController::class, 'status_users'])->name('status_users');

Route::get('getusers', [UsersController::class, 'getusers'])->name('getusers');
Route::post('getusers', [UsersController::class, 'getrelatedusers'])->name('getrelatedusers');
Route::get('users/getroles', [UsersController::class, 'getroless'])->name('user.getroles');
Route::post('user/update', [UsersController::class, 'update'])->name('user.update');
Route::get('userlids/{id}', [LidsController::class, 'userLids'])->name('userlids');
Route::post('getLidsPost', [LidsController::class, 'getLidsPost'])->name('getLidsPost');
Route::post('getLids3', [LidsController::class, 'getLids3'])->name('getLids3');
Route::post('statuslids', [LidsController::class, 'statusLids'])->name('statuslids');
Route::get('getuserlids/{id}', [LidsController::class, 'getuserLids'])->name('getuserlids');
Route::get('todaylids/{id}', [LidsController::class, 'todaylids'])->name('todaylids');
Route::delete('provider/{id}', [ProvidersController::class, 'destroy']);
Route::delete('user/{id}', [UsersController::class, 'deleteuser']);
Route::delete('delDataUser/{id}', [UsersController::class, 'delDataUser']);
Route::post('setBalans', [UsersController::class, 'setBalans']);
Route::get('getBalansMonth/{id}', [UsersController::class, 'getBalansMonth']);
Route::get('getStatusesMonth/{id}', [UsersController::class, 'getStatusesMonth']);
Route::get('getDepozitsMonth/{id}', [UsersController::class, 'getDepozitsMonth']);
Route::get('getCallsMonth/{id}', [UsersController::class, 'getCallsMonth']);
Route::get('getDataDay/{id}', [UsersController::class, 'getDataDay']);

Route::post('Lid/newlids', [LidsController::class, 'newlids'])->name('Lid.newlids');
Route::post('Lid/updatelids', [LidsController::class, 'updatelids'])->name('Lid.updatelids');
Route::post('Lid/searchlids', [LidsController::class, 'searchlids'])->name('Lid.searchlids');
Route::post('Lid/searchlids3', [LidsController::class, 'searchlids3'])->name('searchlids3');
Route::get('getlidid', [LidsController::class, 'getlidid'])->name('getlidid');
Route::get('getlidsontime', [LidsController::class, 'getlidsontime'])->name('getlidsontime');
Route::post('getLidsOnDate', [LidsController::class, 'getLidsOnDate'])->name('getLidsOnDate');
Route::get('getlidonid/{id}', [LidsController::class, 'getlidonid'])->name('getlidonid');
Route::get('set_data', [LidsController::class, 'importlid'])->name('Lid.importlid');
Route::post('Lid/changelidsuser', [LidsController::class, 'changelidsuser'])->name('Lid.changelidsuser');
Route::post('Lid/ontime', [LidsController::class, 'ontime'])->name('Lid.ontime');
Route::post('Lid/deletelids', [LidsController::class, 'deletelids'])->name('Lid.deletelids');
Route::post('log/add', [LogsController::class, 'add'])->name('log.add');
Route::post('log/tellog', [LogsController::class, 'tellog'])->name('log.tellog');
Route::get('getlogonid/{id}', [LogsController::class, 'getlogonid'])->name('getlogonid');
Route::get('StasusesOfId/{id}', [LogsController::class, 'StasusesOfId'])->name('StasusesOfId');
Route::get('set_zaliv', [LidsController::class, 'set_zaliv'])->name('set_zaliv');
Route::post('set_zaliv_post', [LidsController::class, 'set_zaliv_post'])->name('set_zaliv_post');
Route::post('set_zalivjs', [LidsController::class, 'set_zalivjs'])->name('set_zalivjs');
Route::get('set_zalivDub', [LidsController::class, 'set_zalivDub'])->name('set_zalivDub');
Route::get('get_zaliv', [LidsController::class, 'get_zaliv'])->name('get_zaliv');
Route::get('get_zaliv_all', [LidsController::class, 'get_zaliv_all'])->name('get_zaliv_all');
Route::get('get_zaliv_p', [LidsController::class, 'get_zaliv_p'])->name('get_zaliv_p');
Route::get('get_zaliv_allTime', [LidsController::class, 'get_zaliv_allTime'])->name('get_zaliv_allTime');
Route::post('setDepozit', [LidsController::class, 'setDepozit'])->name('setDepozit');
Route::get('getHmLidsUser/{id}', [LidsController::class, 'getHmLidsUser'])->name('getHmLidsUser');
Route::get('InfoDeposit', [LidsController::class, 'InfoDeposit'])->name('InfoDeposit');
Route::get('AllDeposits', [LidsController::class, 'AllDeposits'])->name('AllDeposits');
Route::post('getBTC', [LidsController::class, 'getBTC'])->name('getBTC');
Route::post('qtytel', [LidsController::class, 'qtytel'])->name('qtytel');

Route::get('historyLid/{id}', [ProvidersController::class, 'historyLid'])->name('historyLid');
Route::get('pieAll/{id}', [ProvidersController::class, 'pieAll'])->name('pieAll');
Route::get('pieTime/{id}/{start_day}/{stop_day}', [ProvidersController::class, 'pieTime'])->name('pieTime');
Route::get('getDataTime/{id}/{start_day}/{stop_day}', [ProvidersController::class, 'getDataTime'])->name('getDataTime');
Route::get('getOffices', [UsersController::class, 'getOffices']);
Route::post('office/update', [UsersController::class, 'updateOffice']);
Route::get('usersTree', [UsersController::class, 'usersTree']);
Route::get('getServers/{id}', [UsersController::class, 'getServers']);
Route::post('session', [loginController::class, 'session']);
Route::post('putBTC', [ImportsController::class, 'putBTC']);
Route::post('getBTCsOnDate', [ImportsController::class, 'getBTCsOnDate']);
Route::post('getBTCotherOnDate', [ImportsController::class, 'getBTCotherOnDate']);
Route::get('deleteLoad/{load_key}', [ImportsController::class, 'deleteLoad']);
Route::post('redistribute', [ImportsController::class, 'redistribute']);
Route::post('redistributeLids', [ImportsController::class, 'redistributeLids']);
Route::post('getHistory', [ImportsController::class, 'getHistory']);
Route::post('changeDateBTC', [LidsController::class, 'changeDateBTC']);
Route::post('getAssignedBTC', [LidsController::class, 'getAssignedBTC']);
Route::post('provider_importlid', [LidsController::class, 'provider_importlid']);
Route::post('checkEmails', [LidsController::class, 'checkEmails']);
Route::post('deleteImportedLids', [LidsController::class, 'deleteImportedLids']);
Route::post('getlidsImportedProvider', [LidsController::class, 'getlidsImportedProvider']);
Route::post('clearLiads', [LidsController::class, 'clearLiads']);
Route::post('updateLiads', [LidsController::class, 'updateLiads']);
Route::get('ImportedProvLids/{from}/{to}/', [LidsController::class, 'ImportedProvLids']);
Route::get('onCdr', [LogsController::class, 'onCdr']);
Route::post('getCalls', [LogsController::class, 'getCalls']);