<?php

use App\Events\RenderingHeader;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(RenderingHeader::class, function ($event) {
        $path = request()->path();
        $excludes = ['user/player', 'user/closet', 'skinlib'];
        if (!(in_array($path, $excludes) || explode('/', $path, 2)[0] == 'skinlib')) {
            $event->addContent('<style>html { filter: gray; -webkit-filter: grayscale(100%); }</style>');
        }
    });
};
