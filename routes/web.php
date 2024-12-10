<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ContentController;
use App\Http\Controllers\Web\EventsController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EloquentController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\PricegroupsController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Web\DevelController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\KaspiController;
use App\Http\Controllers\Web\FreedomController;
use Illuminate\Support\Facades\Config;

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

//Auth::routes();

///*****  ADMIN  *****///

Route::group(['middleware' => ['admin'], 'prefix' => 'admin', 'namespace' => 'Admin'], function () {

//    Route::get('/eloquent/{model}', [EloquentController::class,'eloquentIndex']);
//    Route::get('/eloquent/{model}/autocomplete/{field?}', [EloquentController::class,'eloquentAutocomplete']);
//    Route::get('/eloquent/{model}/{id}/get', [EloquentController::class,'eloquentGet']);
//    Route::get('/eloquent/{model}/create', [EloquentController::class,'eloquentAdd']);
//    Route::get('/eloquent/{model}/config', [EloquentController::class,'eloquentConfig']);
//    Route::post('/eloquent/{model}', [EloquentController::class,'eloquentStore']);
//    Route::get('/eloquent/{model}/{id}/edit', [EloquentController::class,'eloquentEdit']);
//    Route::post('/eloquent/{model}/{id}/clone', [EloquentController::class,'eloquentClone']);
//    Route::put('/eloquent/{model}/{id}/', [EloquentController::class,'eloquentUpdate']);
//    Route::delete('/eloquent/{model}/{id}', [EloquentController::class,'eloquentDestroy']);
//    Route::delete('/eloquent/{model}/bulk/delete', [EloquentController::class,'eloquentDestroyBulk']);
//    Route::delete('/media/delete', [EloquentController::class,'mediaDelete']);
//    Route::patch('/eloquent/{model}/{id}/media/move', [EloquentController::class,'mediaMove']);
    Route::get('/eloquent/json_configs/get', [EloquentController::class,'eloquentGetConfigs']);
    Route::get('/eloquent/json_configs/get/{file}', [EloquentController::class,'eloquentGetConfigFile']);
    Route::post('/eloquent/json_configs/save/{file}', [EloquentController::class,'eloquentStoreConfig']);
    Route::post('/eloquent/json_configs/delete/{file}', [EloquentController::class,'eloquentDeleteConfig']);
//    Route::post('/eloquent/json_configs/class', [EloquentController::class,'createClass']);

    Route::post('/cache/clear', [AdminController::class,'clearCache']);
    Route::get('/instructions', [AdminController::class,'getInstruction']);
    Route::get('/permissions', [AdminController::class,'getPermissions']);
    Route::get('/mypermissions', [AdminController::class,'getMyPermissions']);
    Route::post('/sitemap', [AdminController::class,'sitemap']);
    Route::get('/tags', [AdminController::class,'getTags']);
    Route::get('/page/{page_id}/blocks', [AdminController::class,'getPageBlocks']);
    Route::get('/env', [AdminController::class,'env']);
    Route::post('/quill/image', [AdminController::class,'quillSaveImage']);
//        Route::post('/env', [AdminController::class,'saveEnv');
    Route::get('/file/{attr}/{filename}', [AdminController::class,'getFile']);
    Route::get('/resumes/{filename}', [AdminController::class,'getResume']);

    Route::get('/shows/{show_id}/roles', [AdminController::class,'getShowRoles']);
    Route::get('/shows/{show_id}/afisha', [AdminController::class,'getShowAfisha']);


    Route::get('/language/{lang}',[TranslationController::class,'getLangJSON']);
    Route::post('/translation',[TranslationController::class,'saveTranslation']);
    Route::post('/translation/languages/refresh',[TranslationController::class,'manuallyAddKeysFromEnToOtherLangs']);

    Route::get('/dashboard/details',[DashboardController::class,'getDetails']);
    Route::get('/dashboard/update',[DashboardController::class, 'updateData']);

    Route::get('/timetable/{id}',[TimetableController::class, 'get']);
    Route::post('/timetable/{id}/type',[TimetableController::class, 'setType']);

    Route::get('/schemes',[VenueController::class, 'allSchemes']);
    Route::get('/scheme/{id}',[VenueController::class, 'getScheme']);
    Route::post('/scheme/{id}',[VenueController::class, 'save']);
    Route::delete('/scheme/{id}',[VenueController::class, 'deleteSector']);
    Route::get('/section/{id}',[VenueController::class, 'getSeats']);
    Route::post('/section/{id}',[VenueController::class, 'saveSeats']);
    Route::post('/section/{id}/deleteSeats',[VenueController::class, 'deleteSeats']);
    Route::post('/schemes/{id}/duplicate',[VenueController::class, 'duplicateScheme']);

    Route::get('/timetable/{timetable_id}/group/{group_id}', [TicketsController::class,'getTickets']);
    Route::post('/timetable/{timetable_id}/group/{group_id}/tickets', [TicketsController::class,'saveTickets']);
    Route::post('/timetable/{timetable_id}/group/{group_id}/tickets/delete', [TicketsController::class,'deleteTickets']);
    Route::post('/timetable/{timetable_id}/section/{section_id}/toggle/{action}', [TicketsController::class,'toggleClosedSection']);

    Route::get('/timetable/{timetable_id}/pricegroups', [PricegroupsController::class,'get']);
    Route::post('/timetable/{timetable_id}/pricegroups', [PricegroupsController::class,'save']);
    Route::delete('/timetable/{timetable_id}/pricegroups/{id}', [PricegroupsController::class,'delete']);

    Route::get('/order/{id}/ticket/send',[TicketsController::class, 'sendTickets']);
    Route::get('/order/{id}/details',[TicketsController::class, 'orderDetails']);
    Route::get('/report/sales/excel',[DashboardController::class, 'salesExcel']);

    Route::get('/timetable/{timetable_id}/scans',[OrdersController::class, 'scansData']);
    Route::post('/refund/application/{id}/approve',[OrdersController::class, 'approveRefundApp']);
    Route::post('/organizer/show/{id}/{status}',[OrdersController::class, 'changeOrganizerShowStatus']);

    Route::get('/test', [DevelController::class, 'test'])->name('test');
    Route::post('/test', [DevelController::class, 'testPost'])->name('test_post');

    Route::get('/{any?}', [AdminController::class, 'index'])->where('any', '.*');
});


///*****  COMMON  *****///

Route::get('/js/lang.js', [HomeController::class, 'langFile'])->name('assets.lang');
Route::get('/lang', [HomeController::class, 'langFileJson']);
Route::get('/doc/widget', [HomeController::class, 'widgetDocs']);
Route::get('/doc/partner', [HomeController::class, 'partnerDocs']);
Route::get('/doc/kaspi', [HomeController::class, 'kaspiDocs'])->middleware('basicauth');
Route::get('/doc/tourniquet', [HomeController::class, 'tourniquetDocs'])->middleware('basicauth');
Route::get('/ticket/design/{id}/example', [TicketsController::class, 'pdfticketExample'])->name('pdfticketExample');
Route::get('/test', [DevelController::class, 'test'])->name('test');

Route::group(['middleware' => ['web'], 'namespace' => 'Web'], function() {
    Route::get('/order/{order_id}/{hash}/pdf', [EventsController::class, 'pdfticket'])->name('pdfticket');
    Route::any('/passkit/{order_id}/{hash}/{order_item_id?}', [ContentController::class, 'passkit']);
});

///*****  USER PART  *****///

Route::group(['middleware' => [], 'namespace' => 'Web'], function() {

    Route::get('/widget/{event_id?}/{timetable_id?}', [ContentController::class, 'widget'])->name('widget');
    Route::post('/widget/auth', [ContentController::class, 'authWithToken']);
    Route::post('/jetpay/callback', [PaymentController::class, 'callback']);
    Route::post('/freedom/callback/{action}/{client_id}', [FreedomController::class, 'callback']);
    Route::get('/kaspi/webhook', [KaspiController::class, 'webhook']);
    Route::get('/payment/{order_id}/{hash}/kaspi', [PaymentController::class, 'kaspiPage']);
    Route::get('/payment/{order_id}/{hash}/{result}', [PaymentController::class, 'paymentResultRedirect']);
    Route::get('/excel/{filename}', [ReportController::class, 'excel']);

});


