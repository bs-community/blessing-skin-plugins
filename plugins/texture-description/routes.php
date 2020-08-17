<?php

use Illuminate\Routing\Middleware\SubstituteBindings;

Route::middleware(SubstituteBindings::class)->group(function () {
    Route::get('', 'DescriptionController@read');
    Route::put('', 'DescriptionController@update')->middleware(['auth']);
    Route::get('raw', 'DescriptionController@raw')->middleware(['auth']);
});
