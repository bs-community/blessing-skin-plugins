<?php

Route::prefix('texture/{texture}/description')
    ->middleware(['web', 'bindings'])
    ->group(function () {
        Route::get('', 'DescriptionController@read');
        Route::put('', 'DescriptionController@update')->middleware(['auth']);
    });

Route::prefix('api/textures/{texture}/description')
    ->middleware(['api', 'throttle:60,1', 'bindings'])
    ->group(function () {
        Route::get('', 'DescriptionController@read');
        Route::put('', 'DescriptionController@update')->middleware(['auth']);
    });
