<?php

use Illuminate\Support\Arr;
use App\Events\RenderingHeader;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(RenderingHeader::class, function ($event) {
        $path = request()->path();
        $excludes = ['user/player', 'user/closet', 'skinlib', 'skinlib/*'];
        if (! in_array($path, $excludes)) {
            $event->addContent('<style>html { filter: gray; -webkit-filter: grayscale(100%); }</style>');
        }
    });
};
