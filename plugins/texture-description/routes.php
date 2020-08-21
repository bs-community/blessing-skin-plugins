<?php

use Illuminate\Routing\Middleware\SubstituteBindings;

Route::middleware(SubstituteBindings::class)->group(function () {
    Route::prefix('texture/{texture}/description')
        ->middleware(['web'])
        ->group(function () {
            Route::get('', 'DescriptionController@read');
            Route::put('', 'DescriptionController@update')->middleware(['auth']);
        });

    Route::prefix('api/textures/{texture}/description')
        ->middleware(['api', 'throttle:60,1'])
        ->group(function () {
            Route::get('', 'DescriptionController@read');
            Route::put('', 'DescriptionController@update')->middleware(['auth']);
        });
});
