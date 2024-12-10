<?php

use Illuminate\Support\Facades\Route;
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

/// --- PARTNER --- ///
Route::group(['middleware' => ['throttle:120,1'], 'namespace' => 'API'], function () {
    Route::get('shows', [APIPartnerController::class, 'getTimetables']);
    Route::get('shows/{id}', [APIPartnerController::class, 'getTimetableDetails'])->middleware('apipartnershow');
    Route::get('shows/{id}/section/{sectionId?}', [APIPartnerController::class, 'getSectionDetails'])->middleware('apipartnershow');
    Route::post('shows/{id}/order', [APIPartnerController::class, 'initiateOrder'])->middleware('apipartnershow');
    Route::get('order/{id}', [APIPartnerController::class, 'getOrderDetails'])->middleware('apipartnerorder');
    Route::post('order/{id}/confirm', [APIPartnerController::class, 'confirmOrder'])->middleware('apipartnerorder');
    Route::delete('order/{id}/cancel', [APIPartnerController::class, 'cancelOrder'])->middleware('apipartnerorder');
    Route::delete('order/{id}/refund', [APIPartnerController::class, 'refundOrder'])->middleware('apipartnerorder');
    Route::get('venues/{venue_scheme_id}/svg', [APIPartnerController::class, 'getVenueSchemeSvg']);
    Route::get('venues/{venue_scheme_id}/data', [APIPartnerController::class, 'getVenueSchemeData']);
});
