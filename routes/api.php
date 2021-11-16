<?php

use DigitalCreative\NovaDashboard\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('{dashboardKey}', [ WidgetController::class, 'getDashboard' ]);
Route::post('/card/{resource}', [ WidgetController::class, 'resolveCardResource' ]);
Route::post('/execute/action', [ WidgetController::class, 'executeAction' ]);
Route::post('/fetch-widget-data', [ WidgetController::class, 'fetchWidgetData' ]);
Route::post('/widget/update', [ WidgetController::class, 'updateWidget' ]);
Route::post('/widget/delete', [ WidgetController::class, 'deleteWidget' ]);
Route::post('/widget/create', [ WidgetController::class, 'createWidget' ]);
Route::post('/widget/update-coordinates', [ WidgetController::class, 'updateCoordinates' ]);
Route::post('/widget/update-all-coordinates', [ WidgetController::class, 'updateAllCoordinates' ]);
Route::get('/widget/view', [ WidgetController::class, 'getViewData' ]);
