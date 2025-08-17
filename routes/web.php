<?php

use App\Http\Controllers\YourController;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'en|fr');

Route::get('/{locale?}', YourController::class . '@index');
