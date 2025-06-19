<?php

use Illuminate\Support\Facades\Route;
use Modules\SetupWizard\Http\Controllers\SetupWizardController;

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

Route::middleware(['admin'])
    ->name('admin.setup-wizard.')
    ->group(function () {
        Route::get('/setup-wizard', [
            SetupWizardController::class,
            'index'
        ])->name('index');

        Route::post('/install-template', [
            SetupWizardController::class,
            'installTemplate'
        ])->name('install-template');
    });
