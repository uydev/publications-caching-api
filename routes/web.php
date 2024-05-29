<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicationController;

Route::get('/publications', [PublicationController::class, 'getPublication']);


