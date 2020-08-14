<?php

namespace Blessing\TextureDesc;

use App\Models\Texture;
use App\Models\User;
use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

return function (Filter $filter, Plugin $plugin) {
    Hook::addRoute(function () {
        Route::namespace('Blessing\TextureDesc\Controllers')
            ->group(function () {
                Route::prefix('textures/{texture}/desc')
                    ->middleware(['web'])
                    ->group(__DIR__.'/routes.php');

                Route::prefix('api/textures/{texture}/desc')
                    ->middleware(['api', 'throttle:60,1'])
                    ->group(__DIR__.'/routes.php');
            });
    });

    $filter->add('grid:skinlib.show', function (array $grid) {
        $grid['widgets'][0][0][] = 'Blessing\TextureDesc::description';

        return $grid;
    });
    Hook::addScriptFileToPage($plugin->assets('Description.js'), ['skinlib/show/*']);

    View::composer('Blessing\TextureDesc::description', function ($view) {
        $view->with('max_length', option('textures_desc_limit', 0));

        $tid = request()->route('tid');
        /** @var Texture */
        $texture = Texture::find($tid);
        if (empty($texture)) {
            return $view;
        }

        /** @var User */
        $user = auth()->user();

        if ($user->uid === $texture->uploader || $user->isAdmin()) {
            $view->with('can_edit', 'true');
        }

        return $view;
    });
};
