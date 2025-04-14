<?php

use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('absensi.index');
});
Route::post('/generate-pdf', [AbsensiController::class, 'generatePDF'])->name('generate.pdf');
