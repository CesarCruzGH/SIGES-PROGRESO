<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patients/search', [ApiController::class, 'searchPatient']);
    Route::post('/appointments', [ApiController::class, 'storeAppointment']);
    Route::post('/patients/request', [ApiController::class, 'requestNewPatient']);
});


