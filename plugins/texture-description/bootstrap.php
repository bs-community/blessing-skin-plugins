<?php

namespace Blessing\TextureDesc;

use App\Services\Hook;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return function () {
    Hook::addRoute(function () {
        Route::namespace('Blessing\TextureDesc')
            ->middleware(SubstituteBindings::class)
            ->group(function () {
                Route::prefix('textures/{texture}/desc')
                    ->middleware(['web'])
                    ->group(__DIR__.'/routes.php');

                Route::prefix('api/textures/{texture}/desc')
                    ->middleware(['api', 'throttle:60,1'])
                    ->group(__DIR__.'/routes.php');
            });
    });
};
