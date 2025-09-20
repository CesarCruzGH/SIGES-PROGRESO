<?php
use App\Http\Controllers\PdfGeneratorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/medical-leaves/{medicalLeaveId}/download/{copyType}', [PdfGeneratorController::class, 'downloadMedicalLeave'])
        ->name('medical-leave.download');
});