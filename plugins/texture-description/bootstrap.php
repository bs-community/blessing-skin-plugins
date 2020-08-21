<?php

namespace Blessing\TextureDescription;

use App\Events\RenderingFooter;
use App\Models\User;
use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Blessing\TextureDescription\Listeners\AddDescriptionOnUpload;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

return function (Filter $filter, Plugin $plugin, Dispatcher $events) {
    Hook::addRoute(function () {
        Route::namespace('Blessing\TextureDescription\Controllers')
            ->group(__DIR__.'/routes.php');
    });

    $events->listen('texture.uploaded', AddDescriptionOnUpload::class);

    $filter->add('grid:skinlib.show', function (array $grid) {
        $grid['widgets'][0][0][] = 'Blessing\TextureDescription::description';

        return $grid;
    });
    Hook::addScriptFileToPage($plugin->assets('Description.js'), ['skinlib/show/*']);

    $events->listen(RenderingFooter::class, function (RenderingFooter $event) {
        if (request()->is('skinlib/upload')) {
            $event->addContent(
                '<input id="description-limit" type="hidden" value="'.(int) option('textures_description_limit', 0).'">'
            );
        }
    });
    Hook::addScriptFileToPage($plugin->assets('UploadEditor.js'), ['skinlib/upload']);

    View::composer('Blessing\TextureDescription::description', function ($view) {
        $view->with('max_length', option('textures_description_limit', '0'));

        $texture = request()->route('texture');
        if (empty($texture)) {
            return $view;
        }

        /** @var User|null */
        $user = auth()->user();

        if ($user && ($user->uid === $texture->uploader || $user->isAdmin())) {
            $view->with('can_edit', 'true');
        }

        return $view;
    });
};
