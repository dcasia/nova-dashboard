<?php

declare(strict_types = 1);

use DigitalCreative\NovaDashboard\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

Route::post('/widget/update/{resource?}', WidgetController::class);
