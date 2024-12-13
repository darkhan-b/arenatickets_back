<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\APIEventController;
use App\Http\Controllers\API\APIOrderController;
use App\Http\Controllers\API\APIOrganizerController;
use App\Http\Controllers\API\APISettingsController;
use App\Http\Controllers\API\APIScannerController;
use App\Http\Controllers\API\APITourniquetController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\Admin\EloquentController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\PricegroupsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\API\APIPartnerController;

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

Route::group(['prefix' => 'auth', 'middleware' => ['auth:sanctum', 'apitoken']], function () {
    Route::get('/user', [AuthController::class, 'user']);
});

Route::group(['prefix' => 'auth', 'middleware' => ['throttle:20,1', 'widgetClientDetection', 'apitoken']], function () {
    Route::post('/login', [AuthController::class, 'login']); // also used by scanner app
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/password/reset', [AuthController::class, 'passwordReset']);
    Route::post('/password/set', [AuthController::class, 'setNewPassword']);
});

/// --- FOR ADMIN PANEL --- ///
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'clientDetection', 'admin', 'apitoken']], function () {

    Route::get('/eloquent/{model}', [EloquentController::class,'eloquentIndex']);
    Route::get('/eloquent/{model}/autocomplete/{field?}', [EloquentController::class,'eloquentAutocomplete']);
    Route::get('/eloquent/{model}/{id}/get', [EloquentController::class,'eloquentGet']);
    Route::get('/eloquent/{model}/create', [EloquentController::class,'eloquentAdd']);
    Route::get('/eloquent/{model}/config', [EloquentController::class,'eloquentConfig']);
    Route::post('/eloquent/{model}', [EloquentController::class,'eloquentStore']);
    Route::get('/eloquent/{model}/{id}/edit', [EloquentController::class,'eloquentEdit']);
    Route::post('/eloquent/{model}/{id}/clone', [EloquentController::class,'eloquentClone']);
    Route::put('/eloquent/{model}/{id}/', [EloquentController::class,'eloquentUpdate']);
    Route::patch('/eloquent/{model}/{id}/', [EloquentController::class,'eloquentUpdatePart']);
    Route::delete('/eloquent/{model}/{id}', [EloquentController::class,'eloquentDestroy']);
    Route::delete('/eloquent/{model}/bulk/delete', [EloquentController::class,'eloquentDestroyBulk']);
    Route::delete('/media/delete', [EloquentController::class,'mediaDelete']);
    Route::patch('/eloquent/{model}/{id}/media/move', [EloquentController::class,'mediaMove']);
    Route::post('/eloquent/{model}/excel/export', [EloquentController::class,'exportToExcel']);

    Route::get('/schemes',[VenueController::class, 'allSchemes']);
    Route::get('/scheme/{id}',[VenueController::class, 'getScheme']);
    Route::post('/scheme/{id}',[VenueController::class, 'save']);
    Route::delete('/scheme/{id}',[VenueController::class, 'deleteSector']);
//    Route::get('/section/{id}',[VenueController::class, 'getSeats']);
    Route::post('/section/{id}',[VenueController::class, 'saveSeats']);
    Route::post('/section/{id}/deleteSeats',[VenueController::class, 'deleteSeats']);
    Route::post('/schemes/{id}/duplicate',[VenueController::class, 'duplicateScheme']);
    Route::get('/schemes/show/{id}',[VenueController::class, 'getSchemesForShow']);

    Route::get('/timetable/{id}',[TimetableController::class, 'get']);
    Route::post('/timetable/{id}/type',[TimetableController::class, 'setType']);
    Route::get('/timetable/{timetable_id}/group/{group_id}', [TicketsController::class,'getTickets']);
    Route::post('/timetable/{timetable_id}/group/{group_id}/tickets', [TicketsController::class,'saveTickets']);
    Route::post('/timetable/{timetable_id}/group/{group_id}/tickets/delete', [TicketsController::class,'deleteTickets']);
    Route::post('/timetable/{timetable_id}/section/{section_id}/toggle/{action}', [TicketsController::class,'toggleClosedSection']);
    Route::post('/timetable/{timetable_id}/section/{section_id}/toggleSeatSelection/{action}', [TicketsController::class,'toggleSeatSelectionSection']);
    Route::post('/timetable/{id}/tickets/upload', [TicketsController::class,'uploadTicketsFromExcel']);

    Route::get('/timetable/{timetable_id}/pricegroups', [PricegroupsController::class,'get']);
    Route::post('/timetable/{timetable_id}/pricegroups', [PricegroupsController::class,'save']);
    Route::delete('/timetable/{timetable_id}/pricegroups/{id}', [PricegroupsController::class,'delete']);

    Route::post('/order/{id}/tickets/send',[TicketsController::class, 'sendTickets']);
//    Route::get('/order/{id}/details',[TicketsController::class, 'orderDetails']);
    Route::post('/report/sales/excel',[DashboardController::class, 'salesExcel']);
    Route::get('/dashboard',[DashboardController::class, 'dashboard']);
	Route::post('/shows/sync',[TimetableController::class, 'syncShows']);

	Route::get('/tourniquet/logs',[TicketsController::class, 'getTourniquetLogs']);
	Route::get('/tourniquet/get',[TicketsController::class, 'getTourniquetData']);
	Route::post('/tourniquet/sync',[TicketsController::class, 'syncTourniquetSettings']);
//    Route::get('/timetable/{timetable_id}/scans',[OrdersController::class, 'scansData']);
//    Route::post('/refund/application/{id}/approve',[OrdersController::class, 'approveRefundApp']);
//    Route::post('/organizer/show/{id}/{status}',[OrdersController::class, 'changeOrganizerShowStatus']);
});


Route::group(['middleware' => ['auth:sanctum', 'throttle:120,1', 'clientDetection', 'apitoken']], function () {
    Route::get('/profile', [AuthController::class, 'profile']); // also used by scanner app
    Route::get('/profile/orders', [AuthController::class, 'getMyOrders']);
    Route::post('/profile/password/change', [AuthController::class, 'changePassword']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/token/generate', [AuthController::class, 'getOneTimeToken']);
});

Route::group(['middleware' => ['auth:sanctum', 'throttle:120,1', 'clientDetection', 'apitoken']], function () {
    Route::post('/profile/organizer/events/report', [APIOrganizerController::class, 'generateExcelReport']);
    Route::get('/search/{model}/autocomplete/{field?}', [APIOrganizerController::class,'organizerAutocomplete']);
});

//Route::group(['middleware' => ['auth:api', 'throttle:120,1', 'clientDetection']], function () {
//    Route::get('/profile/organizer/shows', [APIOrganizerController::class, 'getMyOrganizerShows']);
//    Route::get('/profile/organizer/events', [APIOrganizerController::class, 'getMyOrganizerEvents']);
//    Route::get('/profile/organizer/events/{id}', [APIOrganizerController::class, 'getOrganizerEventData']);
//    Route::get('/profile/organizer/shows/{id}', [APIOrganizerController::class, 'getOrganizerShowData']);
//    Route::post('/profile/organizer/events/report/{id}', [APIOrganizerController::class, 'generateExcelReport']);
//    Route::post('/profile/organizer/events/add', [APIOrganizerController::class, 'addShow'])->middleware('organizer');
//    Route::get('/profile/charts', [APIOrganizerController::class, 'getMyCharts'])->middleware('organizerOrManager');;
//});


/// --- SCANNER --- ///
/// login is same as with scanner app
Route::group(['prefix' => 'scanner', 'middleware' => ['auth:sanctum', 'throttle:180,1', 'clientDetection', 'apitoken']], function () {
    Route::get('/timetables', [APIScannerController::class, 'getTimetables']);
    Route::get('/timetables/{id}', [APIScannerController::class, 'getTimetableCodes']);
    Route::get('/timetables/{id}/validated', [APIScannerController::class, 'getValidatedAmount']);
    Route::get('/barcode/{timetableId}/{barcode}/history', [APIScannerController::class, 'getBarcodeHistory']);
    Route::post('/upload/{id}', [APIScannerController::class, 'loadScans']);
});


/// --- OPENED BACKEND API --- ///
Route::group(['middleware' => ['throttle:120,1', 'widgetClientDetection', 'apitoken'], 'namespace' => 'API'], function () {
    Route::get('/settings', [APISettingsController::class, 'getSettings']);
    Route::post('/subscribe', [APISettingsController::class, 'subscribe']);
    Route::get('/search', [APISettingsController::class, 'search']);

    Route::get('/home', [APIEventController::class, 'home']);
    Route::get('/popular', [APIEventController::class, 'popular']);
    Route::get('/recommended', [APIEventController::class, 'recommended']);
    Route::get('/category/{slug}', [APIEventController::class, 'category']);
    Route::get('/carousel/{id}', [APIEventController::class, 'carousel']);
    Route::get('/city/{slug}', [APIEventController::class, 'city']);
    Route::get('/timetables', [APIEventController::class, 'timetables']);
    Route::get('/event/{slug}', [APIEventController::class, 'getEvent']);
    Route::get('/venues', [APIEventController::class, 'getVenues']);
    Route::get('/venues/{slug}', [APIEventController::class, 'venue']);
    Route::get('/page/{slug}', [APIEventController::class, 'page']);
    Route::get('/stories', [APISettingsController::class, 'stories']);

    Route::post('/refund/application', [APISettingsController::class, 'refundApplication']);

});

/// --- WIDGET --- ///
Route::group(['middleware' => ['widgetClientDetection', 'throttle:120,1', 'apitoken'], 'namespace' => 'API'], function () {
    Route::get('/widget/settings', [APISettingsController::class, 'getWidgetSettings']);
    Route::get('/timetable/{id}', [APIEventController::class, 'getTimetable']);
    Route::get('/timetable/{timetable_id}/section/{id}', [APIEventController::class, 'getSection']);
    Route::post('/order/init', [APIOrderController::class, 'initOrder']);
    Route::post('/forum/init', [APIOrderController::class, 'initForum']);
    Route::post('/token/auth', [AuthController::class, 'loginWithToken']);
    Route::get('/order/{id}/{hash}', [APIOrderController::class, 'getOrder']);
    Route::post('/order/{id}/{hash}/fill', [APIOrderController::class, 'fillOrder']);
    Route::post('/order/{id}/{hash}/email/confirm/generate', [APIOrderController::class, 'confirmEmailGenerate']);
    Route::post('/order/{id}/{hash}/email/confirm/check', [APIOrderController::class, 'confirmEmailCheck']);
    Route::post('/promocode', [APIOrderController::class, 'promocodeCheck']);
    Route::delete('/order/{id}/{hash}/item/{itemId}', [APIOrderController::class, 'deleteOrderItem']);
    Route::delete('/order/{id}/{hash}', [APIOrderController::class, 'cancelOrder']);
});

/// --- TOURNIQUET API --- ///
Route::group(['prefix' => 'acs', 'middleware' => ['throttle:300,1', 'tourniquetAccess']], function () {
	Route::get('/verify', [APITourniquetController::class, 'verifyBarcode']);
	Route::get('/register', [APITourniquetController::class, 'registerBarcode']);
});
