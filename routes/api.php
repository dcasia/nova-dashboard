<?php

use DigitalCreative\NovaBi\Http\Controllers\WidgetController;
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

Route::get('{resource}', [ WidgetController::class, 'resource' ]);
Route::post('/update/{resource}', [ WidgetController::class, 'updateWidget' ]);
Route::post('/delete/{resource}', [ WidgetController::class, 'deleteWidget' ]);
Route::post('/card/{resource}', [ WidgetController::class, 'resolveCardResource' ]);
Route::post('/action/{dashboard}/{action}', [ WidgetController::class, 'executeAction' ]);
Route::post('{resource}/{id}', [ WidgetController::class, 'fetch' ]);
Route::post('{resource}/{id}', [ WidgetController::class, 'fetch' ]);
