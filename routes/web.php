<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'en|fr');

Route::get('/{locale?}', [WelcomeController::class, 'index']);
