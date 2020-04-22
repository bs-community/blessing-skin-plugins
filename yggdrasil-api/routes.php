<?php

Route::prefix('authserver')
    ->middleware(['Yggdrasil\Middleware\CheckContentType'])
    ->group(function () {
        // 防止暴力破解密码
        Route::middleware(['Yggdrasil\Middleware\Throttle'])
            ->group(function () {
                Route::post('authenticate', 'AuthController@authenticate');
                Route::post('signout', 'AuthController@signout');
            });

        Route::post('refresh', 'AuthController@refresh');

        Route::post('validate', 'AuthController@validate');
        Route::post('invalidate', 'AuthController@invalidate');
});

Route::prefix('sessionserver/session/minecraft')->group(function () {
    Route::post('join', 'SessionController@joinServer');
    Route::get('hasJoined', 'SessionController@hasJoinedServer');

    Route::get('profile/{uuid}', 'ProfileController@getProfileFromUuid');
});

Route::post('api/profiles/minecraft', 'ProfileController@searchProfile');
