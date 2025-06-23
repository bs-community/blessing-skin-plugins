<?php

use Illuminate\Support\Facades\Route;

Route::any('', 'ConfigController@hello');

Route::prefix('authserver')
    ->middleware(['LittleSkin\YggdrasilConnect\Middleware\CheckContentType', 'LittleSkin\YggdrasilConnect\Middleware\CheckIfAuthServerDisabled'])
    ->group(function () {
        // 防止暴力破解密码
        Route::middleware(['LittleSkin\YggdrasilConnect\Middleware\CheckLoginCredentials', 'LittleSkin\YggdrasilConnect\Middleware\Throttle'])
            ->group(function () {
                Route::post('authenticate', 'AuthController@authenticate');
                Route::post('signout', 'AuthController@signout');
            });

        Route::middleware(['LittleSkin\YggdrasilConnect\Middleware\CheckAccessToken'])
            ->group(function () {
                Route::post('refresh', 'AuthController@refresh');
                Route::post('validate', 'AuthController@validate');
                Route::post('invalidate', 'AuthController@invalidate');
            });
    });

Route::prefix('sessionserver/session/minecraft')->group(function () {
    Route::post('join', 'SessionController@joinServer')->middleware(['LittleSkin\YggdrasilConnect\Middleware\CheckAccessToken', 'LittleSkin\YggdrasilConnect\Middleware\CheckContentType']);
    Route::get('hasJoined', 'SessionController@hasJoinedServer');

    Route::get('profile/{uuid}', 'ProfileController@getProfileFromUuid');
});

Route::post('api/profiles/minecraft', 'ProfileController@searchMultipleProfiles');
Route::get('api/users/profiles/minecraft/{username}', 'ProfileController@searchSingleProfile');

Route::prefix('api/user/profile')
    ->middleware(['api', 'LittleSkin\YggdrasilConnect\Middleware\CheckBearerTokenYggdrasil'])
    ->group(function () {
        Route::put('{uuid}/{type}', 'ProfileController@uploadTexture');
        Route::delete('{uuid}/{type}', 'ProfileController@resetTexture');
    });
