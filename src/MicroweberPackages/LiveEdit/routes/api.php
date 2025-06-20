<?php

use \Illuminate\Support\Facades\Route;


Route::name('api.live-edit.')
    ->prefix('api/live-edit')
    ->middleware(['admin'])
    ->group(function () {


        Route::get('get-top-right-menu', MicroweberPackages\LiveEdit\Http\Controllers\Api\LiveEditMenusApi::class . '@getTopRightMenu')
            ->name('get-top-right-menu');
        Route::get('get-website-info', MicroweberPackages\LiveEdit\Http\Controllers\Api\LiveEditSetupWizardApi::class . '@getWebsiteInfo')
            ->name('get-website-info');

    });
